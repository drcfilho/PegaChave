FROM php:8.3-apache

# Habilitar mod_rewrite do Apache
RUN a2enmod rewrite

# Instalar extensões necessárias do PHP para conectar ao MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Copiar os arquivos do projeto para o diretório padrão do Apache
COPY . /var/www/html/

# Instalar dependências via Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --optimize-autoloader


# Ajustar permissões para o servidor web
RUN chown -R www-data:www-data /var/www/html/

# Expor a porta padrão HTTP do Apache
EXPOSE 80
