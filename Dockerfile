FROM php:5.4-apache

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Instalar las extensiones mysqli y pdo_mysql
RUN docker-php-ext-install mysqli pdo_mysql

# Actualizar los repositorios a archive.debian.org
RUN sed -i 's/httpredir.debian.org/archive.debian.org/g' /etc/apt/sources.list \
    && sed -i 's/security.debian.org/archive.debian.org\/debian-security/g' /etc/apt/sources.list \
    && sed -i '/jessie-updates/d' /etc/apt/sources.list

# Actualizar los repositorios
RUN apt-get update

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Exponer el puerto 80
EXPOSE 80





