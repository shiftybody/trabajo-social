FROM php:5.4-apache

# Configurar zona horaria del sistema ANTES de cualquier instalación
ENV TZ=America/Mexico_City
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Actualizar los repositorios a archive.debian.org
RUN sed -i 's/httpredir.debian.org/archive.debian.org/g' /etc/apt/sources.list \
    && sed -i 's/security.debian.org/archive.debian.org\/debian-security/g' /etc/apt/sources.list \
    && sed -i '/jessie-updates/d' /etc/apt/sources.list

# Actualizar los repositorios
RUN apt-get update

# Instalar dependencias necesarias incluyendo autotools
RUN apt-get install -y --force-yes \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    autoconf \
    automake \
    libtool \
    m4 \
    build-essential

# Instalar mysqli y pdo_mysql primero (sin problemas)
RUN docker-php-ext-install mysqli pdo_mysql

# Configurar e instalar GD con fix para autoconf
RUN cd /usr/src/php/ext/gd \
    && phpize \
    && ./configure \
    --with-freetype-dir=/usr/include/ \
    --with-jpeg-dir=/usr/include/ \
    --with-png-dir=/usr/include/ \
    && make && make install \
    && docker-php-ext-enable gd

# Configurar PHP timezone - IMPORTANTE para PHP 5.4
RUN echo "date.timezone = America/Mexico_City" > /usr/local/etc/php/conf.d/timezone.ini

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Exponer el puerto 80
EXPOSE 80

