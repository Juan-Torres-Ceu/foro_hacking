FROM php:8.2-apache

# Instalar extensiones necesarias para MySQL
RUN docker-php-ext-install mysqli pdo_mysql

# Habilitar mod_rewrite (opcional, útil si usas .htaccess)
RUN a2enmod rewrite

# El código PHP se monta con el volumen ./foro:/var/www/html
