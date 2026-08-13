# Recomendação de Séries e Filmes

Aplicação web onde usuários publicam uma série ou filme que estão pensando em assistir e pedem a opinião das outras pessoas da plataforma. Cada publicação pode receber três interações: **recomendo**, **não recomendo** ou **apenas acompanhar**. O autor pode encerrar a publicação, e os resultados continuam disponíveis para consulta depois disso.

Construída com a TALL Stack: **Laravel 13**, **Livewire 4**, **Alpine.js**, **Tailwind CSS** e **MariaDB**.

---

## 1. Requisitos

O projeto roda inteiramente dentro do Docker via Laravel Sail. **Você não precisa de PHP, Composer, Node ou MySQL instalados na máquina.**

| Requisito | Versão usada no desenvolvimento |
|---|---|
| Docker Engine | 29.7.1 |
| Docker Compose | v5.3.1 (plugin `docker compose`) |
| Git | qualquer versão recente |
| `make` | opcional — há atalhos no `Makefile` |

Versões dentro dos containers (não precisam ser instaladas): PHP 8.5, Node 24, MariaDB 11.

> **Linux:** seu usuário precisa conseguir rodar Docker sem `sudo`. Se `docker ps` falhar com erro de permissão, rode `sudo usermod -aG docker $USER` e reabra a sessão.

---

## 2. Instalação

O primeiro `composer install` é o problema clássico do ovo e da galinha: o Sail vive dentro de `vendor/`, que ainda não existe. Por isso o passo 2 usa um container PHP temporário para instalar as dependências.

```bash
# 1. Clonar o repositório
git clone <url-do-repositorio>
cd recomendacao-de-series-e-filmes

# 2. Instalar as dependências PHP (container descartável, não exige PHP local)
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

# 3. Criar o arquivo .env
cp .env.example .env

# 4. Subir os containers (na primeira vez o build demora alguns minutos)
vendor/bin/sail up -d

# 5. Gerar a chave da aplicação
vendor/bin/sail artisan key:generate

# 6. Instalar dependências do frontend e compilar os assets
vendor/bin/sail npm install
vendor/bin/sail npm run build

# 7. Criar as tabelas e popular o banco com dados de exemplo
vendor/bin/sail artisan migrate --seed
```

Pronto: acesse **http://localhost:8000**.

> Todos os comandos são prefixados por `vendor/bin/sail`, que os executa **dentro** do container. Rodar `artisan` direto no host não funciona, porque o banco `mariadb` só existe na rede do Docker.
>
> Atalho opcional: `alias sail='vendor/bin/sail'`.

---

## 3. Configuração do `.env`

O `.env.example` já vem com todos os valores prontos para o Sail — **basta copiar, sem editar nada**. As variáveis que importam:

| Variável | Valor padrão | Por que é assim |
|---|---|---|
| `APP_PORT` | `8000` | Porta publicada no host. A **80 costuma estar ocupada** por Apache/Nginx local, então o padrão do projeto é 8000. |
| `APP_URL` | `http://localhost:8000` | Precisa incluir a porta, senão links gerados pelo `route()` apontam para o lugar errado. |
| `DB_CONNECTION` | `mariadb` | |
| `DB_HOST` | `mariadb` | É o **nome do serviço** no `compose.yaml`, não `localhost`. Dentro da rede Docker, é assim que a aplicação enxerga o banco. |
| `DB_DATABASE` | `laravel` | Criado automaticamente pelo container do MariaDB. |
| `DB_USERNAME` / `DB_PASSWORD` | `sail` / `password` | Credenciais de desenvolvimento definidas no `compose.yaml`. |
| `APP_KEY` | *(vazio)* | Preenchido pelo `key:generate` no passo 5. |

**Já usa a porta 8000 para outra coisa?** Altere `APP_PORT` e `APP_URL` no `.env`, e rode `vendor/bin/sail up -d --force-recreate`.

Outras portas publicadas no host: `3306` (MariaDB, útil para um cliente gráfico) e `5173` (Vite em modo dev). Ajustáveis via `FORWARD_DB_PORT` e `VITE_PORT`.

---

## 4. Migrations

```bash
vendor/bin/sail artisan migrate           # aplica as migrations pendentes
vendor/bin/sail artisan migrate:status    # mostra o que já rodou
vendor/bin/sail artisan migrate:fresh     # apaga tudo e recria (destrutivo)
```

São sete migrations. Além das padrão do Laravel (`users`, `cache`, `jobs`), o domínio da aplicação é:

| Migration | O que cria |
|---|---|
| `add_login_lockout_columns_to_users_table` | Colunas do bloqueio progressivo de login. |
| `create_posts_table` | Publicações. Índice composto em `(status, created_at)` para o feed. |
| `create_votes_table` | Votos. **Unique em `(post_id, user_id)`** + índice `(post_id, type)` para as contagens. |
| `create_follows_table` | Acompanhamentos. **Unique em `(post_id, user_id)`**. |

As duas *unique constraints* são o coração da modelagem: elas tornam voto e acompanhamento duplicados **impossíveis no banco**, não apenas improváveis no código.

---

## 5. Seeders

```bash
vendor/bin/sail artisan db:seed              # popula o banco
vendor/bin/sail artisan migrate:fresh --seed # recomeça do zero (destrutivo)
```

O seeder gera um cenário completo para explorar a aplicação sem cadastrar nada à mão: **6 usuários, 18 publicações** (14 abertas e 4 encerradas), **30 votos e 46 acompanhamentos**.

**Conta para login:**

| E-mail | Senha |
|---|---|
| `test@example.com` | `password` |

Os outros 5 usuários são gerados pela factory e usam a mesma senha (`password`).

Os votos do seeder são criados através do `VoteService` — o mesmo caminho usado pela interface. Assim os dados de exemplo respeitam as regras de negócio de verdade (todo voto gera acompanhamento automático), em vez de inserir linhas inconsistentes direto na tabela.

---

## 6. Executando a aplicação

Com os containers no ar (`vendor/bin/sail up -d`), a aplicação já responde em **http://localhost:8000** usando os assets compilados no passo 6 da instalação.

Para desenvolvimento no frontend, rode o Vite com *hot reload* em um terminal separado:

```bash
vendor/bin/sail npm run dev
```

Comandos do dia a dia:

```bash
vendor/bin/sail up -d      # sobe os containers
vendor/bin/sail stop       # para os containers
vendor/bin/sail down       # remove os containers (o banco sobrevive no volume)
vendor/bin/sail logs -f    # acompanha os logs
vendor/bin/sail npm run build   # recompila os assets
```

> Alterou um arquivo Blade/CSS/JS e a mudança não apareceu? Rode `vendor/bin/sail npm run build` (ou deixe o `npm run dev` rodando).

Há atalhos equivalentes no `Makefile` — `make up`, `make test`, `make fresh`. Rode `make help` para a lista.

### Roteiro rápido para avaliar

1. Entre com `test@example.com` / `password`.
2. **Publicações** — o feed com as publicações abertas, contagens e ações.
3. Vote em uma publicação de outro usuário e note que ela passa a ser acompanhada automaticamente.
4. **Acompanhadas** — todas as publicações que você votou ou escolheu seguir.
5. **Minhas publicações** — encerre ou exclua uma publicação sua (a exclusão só é oferecida quando ninguém interagiu).
6. **Nova publicação** — crie uma e veja-a aparecer no feed.

---

## 7. Executando os testes

```bash
vendor/bin/sail artisan test              # suíte completa
vendor/bin/sail artisan test --compact    # saída resumida
vendor/bin/sail artisan test --filter=PostManagementTest   # um arquivo
```

Estado atual: **49 testes, 131 assertions, todos passando.**

Os testes usam um banco separado (`testing`), criado automaticamente pelo container do MariaDB. Eles **não afetam os dados de desenvolvimento** — pode rodar à vontade sem perder o que o seeder gerou.

### As três regras de negócio protegidas

O enunciado pede testes para três regras importantes. Estas são as escolhidas, e o motivo de cada uma:

1. **Publicação encerrada não aceita novos votos nem acompanhamentos.** É a regra mais fácil de furar por fora da interface: o botão some da tela, mas a ação Livewire continua acessível pelo console do navegador. O teste garante que a proteção está no backend.

2. **O autor não pode excluir uma publicação que já recebeu interação de terceiros.** Protege dados de outras pessoas: apagar a publicação levaria junto, por *cascade*, votos e acompanhamentos de quem participou.

3. **Um usuário não pode encerrar nem excluir a publicação de outro.** É a fronteira de autorização do sistema, garantida pela `PostPolicy` e verificada no servidor.

A regra de **voto único por usuário/publicação não ocupa um dos três slots** porque não depende de código: a *unique constraint* em `votes(post_id, user_id)` a torna impossível no nível do banco.

A suíte tem 49 testes porque a cobertura cresceu naturalmente junto com as funcionalidades (feed, criação, acompanhamentos, autenticação). As três regras acima são as protegidas de forma **deliberada**; o restante é rede de segurança contra regressões.

---

## 8. Decisões técnicas importantes

### Livewire Component → Service → Eloquent

O componente Livewire cuida da tela (estado da UI, validação de entrada, feedback) e **delega toda regra de negócio a um Service**. Decisões como "pode votar?" ou "pode excluir?" vivem em `PostService` e `VoteService`.

O ganho é concreto: a regra de encerramento é a mesma sendo chamada pelo feed, por "minhas publicações" ou pelo seeder. Sem essa separação, ela estaria duplicada em cada componente — e as cópias divergiriam.

Optei por **não** criar Controllers, Form Requests ou Repositories: no fluxo Livewire não há requisição HTTP tradicional para um Controller tratar, e um Repository sobre o Eloquent seria uma camada a mais sem ganho real. O enunciado pede uma solução simples e explicável, não abstrações extras.

### Autorização em Policy, estado no Service

Uma separação que considero a decisão mais importante do projeto:

- A **Policy** responde *quem* pode agir — "é o autor?". É estável e barata de verificar.
- O **Service** responde *em que estado* está a publicação — "ainda está aberta?", "já tem interação de terceiros?". Isso muda a qualquer instante e precisa ser lido **sob trava**, dentro da transação.

Colocar a checagem de estado na Policy criaria uma janela entre a verificação e a escrita, onde outra requisição poderia encerrar a publicação. Foi por isso que separei.

### Concorrência: trava onde importa, `WHERE` onde basta

Duas abordagens diferentes, cada uma pelo motivo certo:

- **Encerrar** usa a condição dentro do próprio `UPDATE ... WHERE status = 'open'`. Um `UPDATE` já é atômico; a contagem de linhas afetadas distingue a primeira de duas tentativas simultâneas. Não precisa de transação.
- **Excluir** e **votar** usam `lockForUpdate()` dentro de uma transação. Aqui a decisão depende de ler o estado antes de escrever. Sem a trava, um voto registrado entre a verificação e o `delete` seria apagado pelo *cascade* sem deixar rastro.

### Voto e acompanhamento como tabelas separadas

Seria possível usar uma tabela só, com o voto nulo representando "apenas acompanhar". Preferi separar porque as duas coisas têm ciclos de vida distintos: **votar implica acompanhar, mas acompanhar não implica votar**, e um usuário pode trocar o voto mantendo o acompanhamento. Com tabelas separadas, "trocar de voto" é um `updateOrCreate` em `votes` que não toca em `follows`.

A troca de voto é **substituição**, não histórico: `updateOrCreate` sobrescreve o voto anterior. O enunciado exige um comportamento coerente e definido — este é simples de explicar e não distorce as contagens.

### Consultas: contagens agregadas, sem N+1

O feed usa `withInteractionCounts()` e `withCardRelations()` — scopes que carregam as contagens de votos e o autor em consultas agregadas. Sem isso, uma página com 10 publicações dispararia dezenas de consultas extras.

### Alpine.js: só onde o estado é do navegador

Três usos, todos de estado efêmero que o servidor não precisa conhecer:

- **Modal de confirmação** para encerrar/excluir (`confirm-action`);
- **"Ver mais"** na descrição longa (`post-card`);
- **Menu responsivo** da navegação.

Alpine **não** está no `package.json`: ele já vem embutido no bundle do Livewire 4. Não instale separadamente — duas instâncias entram em conflito.

### Segurança: a interface não é a proteção

Toda ação pública de um componente Livewire é chamável pelo console do navegador. Por isso `castVote` é **`protected`**: uma versão pública aceitaria um `VoteType` arbitrário vindo do cliente. As ações expostas (`recommend`, `notRecommend`) fixam o tipo no servidor.

Some-se a isso: rotas sob middleware `auth`, `authorize()` em toda ação sensível, e as regras de estado revalidadas no Service. Esconder um botão é usabilidade, não autorização.

### Enums nativos

`PostStatus`, `PostType` e `VoteType` são enums do PHP 8. O banco guarda strings legíveis (`open`, `closed`), e o código ganha segurança de tipo — `VoteType::Recommend` em vez de uma string solta sujeita a erro de digitação.

### Bloqueio progressivo de login

Tentativas de senha incorreta escalam um bloqueio por conta:

| Tentativa | Consequência |
|---|---|
| 1ª e 2ª | Sem bloqueio |
| 3ª | 1 hora |
| 4ª | 3 horas |
| 5ª | 8 horas |
| 6ª | Bloqueio permanente |

O contador só zera em login bem-sucedido. Uma conta bloqueada nega acesso **mesmo com a senha correta**, para não revelar que a senha estaria certa.

---

## 9. Melhorias com mais tempo

**Produto**

- **Desbloqueio de contas por um perfil ADM.** É a lacuna mais concreta hoje: uma conta com bloqueio permanente só volta via intervenção manual no banco (ver *Limitações* abaixo).
- **Recuperação de senha** ("esqueci minha senha"), ausente nesta versão.
- **Notificar o autor** quando a publicação recebe interação.

**Técnicas — segurança**

- **Rate limit por IP nas rotas de autenticação.** Hoje não existe nenhum `RateLimiter` ou middleware `throttle` no projeto: a única defesa contra força bruta é o bloqueio progressivo, que conta tentativas **por conta**. Isso deixa dois flancos abertos — um atacante pode tentar uma senha comum contra centenas de e-mails diferentes (*password spraying*) sem nunca chegar à 3ª tentativa de nenhuma conta, e pode usar o próprio bloqueio como arma, travando a conta de alguém de propósito. Um `throttle` por IP no login e no registro fecha os dois: limita o volume que uma origem consegue gerar, independentemente de quantas contas ela distribua as tentativas.

- **Exigência de senha forte no cadastro.** O registro usa `Password::defaults()` sem nenhuma customização, o que na prática significa **apenas 8 caracteres mínimos** — `12345678` passa. É a melhoria de melhor relação custo/benefício da lista: poucas linhas, e transforma o bloqueio progressivo de última linha de defesa em rede de segurança.

- **MFA (segundo fator) e reCAPTCHA no login.** Rate limit e senha forte encarecem o ataque, mas nenhum dos dois protege uma senha já vazada ou obtida por *phishing* — nesse caso o atacante acerta na primeira tentativa e nada no sistema atual o detém. O MFA por TOTP (app autenticador) quebra essa cadeia: saber a senha deixa de ser suficiente. O reCAPTCHA resolve um problema adjacente e mais barato — distinguir humano de script no formulário, cortando o tráfego automatizado antes que ele consuma a cota do rate limit.

- **Expiração e histórico de senhas.** A tabela `users` guarda apenas o hash atual — não há `password_changed_at` nem histórico, então uma senha comprometida hoje continua válida indefinidamente e o usuário que "troca" a senha pode simplesmente repetir a anterior. Uma coluna `password_changed_at` mais uma tabela `password_histories` (com os *N* últimos hashes) permitem exigir renovação periódica e recusar as 3 últimas senhas, comparando por `Hash::check` contra o histórico.

**Técnicas — se o projeto escalar**

- **CI/CD.** Não há `.github/` no repositório: os 49 testes e o Pint dependem de alguém lembrar de rodá-los antes do commit. Um workflow no GitHub Actions, rodando `artisan test` e `pint --test` a cada push transforma a suíte existente em barreira real de regressão — o valor dos testes que já estão escritos só se realiza quando a execução deixa de ser opcional. É o primeiro passo antes de qualquer outra melhoria de escala, porque é o que torna as demais seguras de implantar.

- **Load balancer, cache e filas dedicados.** A aplicação roda hoje em um único container, com `CACHE_STORE=database` e `QUEUE_CONNECTION=database` — as duas coisas competindo pelo mesmo MariaDB que serve o feed. Com volume, isso vira gargalo em três frentes de uma vez. A evolução natural: **Redis** para cache e sessão (tira leitura repetida do banco e permite escalar horizontalmente sem sessão presa a uma instância), **filas em Redis com workers separados** (as notificações ao autor, citadas em *Produto*, precisam ser assíncronas para não segurar a resposta do voto) e **load balancer** na frente de múltiplas instâncias da aplicação. A ordem importa: sessão e cache fora do banco são pré-requisito para o load balancer fazer sentido.

### Limitações conhecidas

- **Sem perfil ADM:** uma conta com bloqueio permanente precisa de intervenção manual no banco para voltar a autenticar:
  ```bash
  vendor/bin/sail artisan tinker --execute '
  App\Models\User::where("email", "usuario@exemplo.com")->update([
      "login_attempts" => 0,
      "locked_until" => null,
      "login_locked_permanently" => false,
  ]);'
  ```
- **Sem fluxo de "esqueci a senha"** nesta versão.
- **A interface não é responsiva por completo**, conforme permitido pelo enunciado. O menu de navegação se adapta, mas o foco foi consistência e clareza, não adaptação a telas pequenas.

---

## 10. Estrutura do projeto

```
app/
├── Enums/          PostStatus, PostType, VoteType
├── Exceptions/     Exceções de domínio (ex.: PostAlreadyClosedException)
├── Livewire/
│   ├── Auth/       Login, Register
│   ├── Concerns/   InteractsWithPosts (ações de voto compartilhadas)
│   ├── Follows/    Index — publicações acompanhadas
│   └── Posts/      Feed, Create, Mine
├── Models/         User, Post, Vote, Follow
├── Policies/       PostPolicy — quem pode encerrar/excluir/votar
└── Services/       PostService, VoteService, AuthService

database/
├── factories/      Geração de dados para testes e seeder
├── migrations/     Schema (constraints e índices)
└── seeders/        DatabaseSeeder — cenário de exemplo

tests/Feature/      Testes das regras de negócio
```

## 11. Solução de problemas

| Sintoma | Causa provável e solução |
|---|---|
| `port is already allocated` ao subir | Outro serviço na porta 8000 (ou 3306). Altere `APP_PORT` (ou `FORWARD_DB_PORT`) no `.env` e rode `vendor/bin/sail up -d --force-recreate`. |
| `Connection refused` no banco | O MariaDB ainda está inicializando. Aguarde alguns segundos e confira com `vendor/bin/sail ps` se está `healthy`. |
| `Unable to locate file in Vite manifest` | Assets não compilados: `vendor/bin/sail npm run build`. |
| Alteração no Blade/CSS não aparece | Mesmo caso acima — recompile ou deixe `vendor/bin/sail npm run dev` rodando. |
| `vendor/bin/sail: No such file or directory` | O `composer install` do passo 2 não rodou. |
| `Permission denied` em `storage/` (Linux) | `vendor/bin/sail root-shell -c "chown -R sail:sail /var/www/html/storage"`. |
| Quero recomeçar do zero | `vendor/bin/sail down -v` (apaga o volume do banco) e refaça a partir do passo 4. |
