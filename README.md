# recomendacao-de-series-e-filmes

## Autenticação

O login usa sessão (guard `web`), sem Sanctum ou token. Tentativas de senha incorreta escalam um bloqueio progressivo por conta:

| Tentativa nº | Consequência |
|---|---|
| 1ª e 2ª | Sem bloqueio |
| 3ª | Bloqueio de 1 hora |
| 4ª | Bloqueio de 3 horas |
| 5ª | Bloqueio de 8 horas |
| 6ª | Bloqueio permanente |

O contador só reseta em login bem-sucedido. Uma conta bloqueada nega acesso mesmo com a senha correta, para não revelar se a senha informada estaria certa.

### Limitações conhecidas

- **Sem perfil ADM nesta versão**: uma conta com bloqueio permanente só volta a autenticar via intervenção manual no banco (ex.: `vendor/bin/sail artisan tinker`, zerando `login_attempts`, `locked_until` e `login_locked_permanently` do usuário). Um fluxo de desbloqueio via interface (perfil ADM) fica como melhoria futura.
- **Sem fluxo de "esqueci a senha"** nesta versão.
