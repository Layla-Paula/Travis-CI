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
