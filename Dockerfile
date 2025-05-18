FROM php:7.3-apache

# Define a pasta pública do Laravel como raiz do Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
ENV COMPOSER_ALLOW_SUPERUSER=1

# Instala dependências do sistema e extensões do PHP necessárias para Laravel
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    zip \
    libzip-dev \
    libpq-dev \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip

# Instala o Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Habilita o mod_rewrite do Apache necessário para URLs amigáveis no Laravel
RUN a2enmod rewrite

# Configura o Apache para apontar para a pasta /public do Laravel
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf && \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia o código da aplicação para o container
COPY . .

# Instala as dependências do Laravel
RUN composer install --no-scripts --no-interaction --optimize-autoloader

# Ajusta permissões para o Apache escrever nas pastas necessárias
RUN chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && git config --global --add safe.directory /var/www/html

# Expõe a porta 80 do container
EXPOSE 80

# Inicia o Apache no modo foreground
CMD ["apache2-foreground"]
