FROM php:8.2.17-fpm-alpine3.19

# Install packages
RUN apk add --no-cache fish \
    curl \
    git \
    build-base \
    zip libzip-dev \
    oniguruma-dev \
    autoconf \
    linux-headers \
    icu-dev \
    libxslt \
    libxslt-dev \
    libpng libpng-dev \
    libjpeg-turbo-dev libwebp-dev zlib-dev libxpm-dev \
    rabbitmq-c rabbitmq-c-dev

# Вместо bash поставил fish
# linux-headers - добавил потому что без него не устанавливался xdebug

# Xdebug
ARG INSTALL_XDEBUG=false
RUN if [ ${INSTALL_XDEBUG} = true ]; \
    then \
      pecl install xdebug && docker-php-ext-enable xdebug; \
    fi;

COPY ./docker/php-fpm/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Postgres
RUN apk add --no-cache libpq-dev \
    && docker-php-ext-configure intl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && pecl install amqp \
    && docker-php-ext-enable amqp \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && docker-php-ext-install pdo_pgsql intl xsl gd zip

# Configure non-root user.
ARG PUID=1000
ARG PGID=1000
RUN apk --no-cache add shadow && \
    groupmod -o -g ${PGID} www-data && \
    usermod -o -u ${PUID} -g www-data www-data

# Source code
COPY ./ /var/www
WORKDIR /var/www

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
#RUN composer install --no-interaction

# symfony-cli
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | su && apk --no-cache add symfony-cli

CMD php-fpm

EXPOSE 9000

