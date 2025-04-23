FROM php:5.4-apache

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Actualizar los repositorios a archive.debian.org
RUN sed -i 's/httpredir.debian.org/archive.debian.org/g' /etc/apt/sources.list \
    && sed -i 's/security.debian.org/archive.debian.org\/debian-security/g' /etc/apt/sources.list \
    && sed -i '/jessie-updates/d' /etc/apt/sources.list

# Actualizar los repositorios
RUN apt-get update

# Instalar dependencias para GD
RUN apt-get install -y --force-yes \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev

# Instalar extensiones PHP (mysqli, pdo_mysql y gd)
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install mysqli pdo_mysql gd

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Exponer el puerto 80
EXPOSE 80



