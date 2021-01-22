FROM php:7.4-fpm
LABEL maintainer="Julien CROCHET <julien@crochet.me>"
ARG PHP_ENV=DEV

RUN apt-get update && \
    apt-get install -y zlib1g-dev libzip-dev libpq-dev unzip nano curl && \
    rm -rf /var/lib/apt/lists/* && \
    mkdir -p /home/user && \
    chmod 777 /home/user && \
    HOME=/home/user && \
    echo 'alias ll="ls -la"' >> /home/user/.bashrc && \
    PATH=$PATH:/usr/src/api/vendor/bin:bin

RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('sha384', 'composer-setup.php') === file_get_contents('https://composer.github.io/installer.sig')) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php --filename=composer && \
    php -r "unlink('composer-setup.php');" && \
    mv composer /usr/local/bin/composer

RUN pecl install xdebug-2.9.8 && \
    docker-php-ext-enable xdebug

RUN docker-php-ext-install pdo_pgsql && \
    docker-php-ext-enable pdo_pgsql

COPY docker/php/conf.d/php-*.ini $PHP_INI_DIR/conf.d/

WORKDIR /usr/src/api