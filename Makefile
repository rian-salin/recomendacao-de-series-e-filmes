SAIL = vendor/bin/sail

.PHONY: help up down stop restart build ps logs shell root db artisan test pint migrate migrate-fresh seed fresh npm-dev npm-build install

help:
	@echo "Comandos disponiveis:"
	@echo "  make up             - sobe os containers em background"
	@echo "  make down           - derruba os containers e remove volumes orfaos"
	@echo "  make stop           - para os containers"
	@echo "  make restart        - reinicia os containers"
	@echo "  make build          - builda as imagens dos containers"
	@echo "  make ps             - lista os containers em execucao"
	@echo "  make logs           - segue os logs dos containers"
	@echo "  make shell          - abre um shell no container da aplicacao"
	@echo "  make root           - abre um shell como root no container da aplicacao"
	@echo "  make db             - abre o console do MariaDB"
	@echo "  make artisan cmd='migrate' - executa um comando artisan"
	@echo "  make test           - roda a suite de testes (pest/phpunit)"
	@echo "  make pint           - formata o codigo PHP com o Pint"
	@echo "  make migrate        - roda as migrations"
	@echo "  make migrate-fresh  - reseta o banco e roda as migrations"
	@echo "  make seed           - roda os seeders"
	@echo "  make fresh          - migrate:fresh + seed"
	@echo "  make npm-dev        - roda o Vite em modo dev"
	@echo "  make npm-build      - builda os assets do frontend"
	@echo "  make install        - instala as dependencias composer e npm"

up:
	$(SAIL) up -d

down:
	$(SAIL) down

stop:
	$(SAIL) stop

restart:
	$(SAIL) restart

build:
	$(SAIL) build --no-cache

ps:
	$(SAIL) ps

logs:
	$(SAIL) logs -f

shell:
	$(SAIL) shell

root:
	$(SAIL) root-shell

db:
	$(SAIL) mariadb

artisan:
	$(SAIL) artisan $(cmd)

test:
	$(SAIL) artisan test --compact

pint:
	$(SAIL) bin pint --dirty --format agent

migrate:
	$(SAIL) artisan migrate

migrate-fresh:
	$(SAIL) artisan migrate:fresh

seed:
	$(SAIL) artisan db:seed

fresh:
	$(SAIL) artisan migrate:fresh --seed

npm-dev:
	$(SAIL) npm run dev

npm-build:
	$(SAIL) npm run build

install:
	$(SAIL) composer install
	$(SAIL) npm install
