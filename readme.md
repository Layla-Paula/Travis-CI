<p><center><img src="https://assets.website-files.com/5cf95301995e8c48a8880a69/5ec524104ef2ed3e912b5348_COLORIDA-p-500.png"></center></p>
<br>

## PHP Analyzer

Esta é uma ferramenta desenvolvida como trabalho de conclusão de curso do curso de Sistemas de Informação no Centro Universitário Academia (UniAcademia) - Juiz de Fora, MG. <br>
Aluno: Jonas Antônio Gomes Vicente <br>
Professor Orientador: Tassio Ferenzini Martins Sirqueira <br>

## Preparando o ambiente de desenvolvimento
Este sistema foi desenvolvido em PHP (7.3) com o framework [Laravel (5.8)](https://laravel.com/docs/5.8/releases).
Ao baixar o projeto, certifique-se de ter o PHP 7.3 instalado em sua máquina com o módulo do PostgreSQL habilitado. Também é necessário ter o [Composer](https://getcomposer.org/) instalado para baixar as dependências do projeto.
Abra a pasta do projeto em um terminal e execute os seguintes comandos:

 1.  composer install ou composer update
 2. cp .env.example .env -> configure as conexões com o banco de dados no arquivo .env
 3. php artisan key:generate
 4. php artisan migrate
 5. php artisan db:seed --class=UserTableSeeder
 6. php artisan db:seed --class=TermTypesTableSeeder
 7. php artisan db:seed --class=TermsTableSeeder

Com isso, é possível executar o projeto com o comando: php artisan serve.

## Melhorias para a Disciplina Manutenção e Evolução de Software:
Adição do Docker, SonarQube e Travis CI

Este projeto Laravel 5.8 com PHP 7.3 está configurado para ser executado via Docker, com análise estática de código pelo SonarQube e integração contínua pelo Travis CI.

:whale: Executando com Docker

1. Clonar o repositório

git clone https://seu-repositorio.git
cd Travis-CI

2. Subir os containers

docker compose up -d --build

3. Acessar o container do Laravel

docker exec -it laravel_app bash

4. Instalar dependências (se não estiverem instaladas)

composer install

5. Gerar chave da aplicação

php artisan key:generate

6. Rodar as migrations e seeders

php artisan migrate
php artisan db:seed --class=UserTableSeeder
php artisan db:seed --class=TermTypesTableSeeder
php artisan db:seed --class=TermsTableSeeder

A aplicação estará disponível em: http://localhost:8000

:bar_chart: Análise com SonarQube

1. Subir o SonarQube (caso ainda não esteja)

docker compose up -d sonarqube

Acesse: http://localhost:9000

Usuário padrão: adminSenha padrão: admin

2. Criar um token de autenticação

Navegue até "My Account > Security"

Gere um token e copie

3. Rodar o scanner:

sonar-scanner \
  -Dsonar.projectKey=travisTeste \
  -Dsonar.sources=. \
  -Dsonar.host.url=http://localhost:9000 \
  -Dsonar.login=SEU_TOKEN_AQUI

:construction_worker: Integração Contínua com Travis CI

1. Arquivo .travis.yml

language: php

php:
  - 7.3

services:
  - postgresql

before_install:
  - sudo apt-get update
  - sudo apt-get install -y unzip git zip libzip-dev libpq-dev

install:
  - composer install --no-interaction --prefer-dist --optimize-autoloader

before_script:
  - psql -c "CREATE DATABASE travis_ci_laravel;" -U postgres
  - cp .env.example .env
  - php artisan key:generate
  - php artisan migrate
  - php artisan db:seed --class=UserTableSeeder
  - php artisan db:seed --class=TermTypesTableSeeder
  - php artisan db:seed --class=TermsTableSeeder

script:
  - php artisan test || true
  - sonar-scanner \
    -Dsonar.projectKey=travisTeste \
    -Dsonar.sources=. \
    -Dsonar.host.url=http://localhost:9000 \
    -Dsonar.login=$SONAR_TOKEN

env:
  global:
    - DB_CONNECTION=pgsql
    - DB_HOST=127.0.0.1
    - DB_PORT=5432
    - DB_DATABASE=travis_ci_laravel
    - DB_USERNAME=postgres
    - DB_PASSWORD=

addons:
  postgresql: "13"

cache:
  directories:
    - $HOME/.composer/cache

Configure o repositório no Travis CI e adicione a variável SONAR_TOKEN no ambiente.
