FROM php:8.3.1-fpm
RUN apt-get update && apt-get install apt-file -y && apt-file update && apt-get install vim -y --no-install-recommends \
        git \
        g++ \
        zlib1g-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install \
        zip \
        intl \
        mysqli \
        pdo pdo_mysql \
        ctype \
        iconv \
        session \
        simplexml

WORKDIR /var/www/symfony1/

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
