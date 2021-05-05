ARG PHP_VERSION=7.4
ARG NGINX_VERSION=1.18.0


# "php base" stage
FROM php:${PHP_VERSION}-fpm-alpine AS app_php_base

# persistent / runtime deps
RUN apk add --no-cache \
        acl \
        bash \
        fcgi \
        file \
        gettext \
        git \
        freetype \
        libjpeg-turbo \
        libpng \
        nano \
    ;

ARG APCU_VERSION=5.1.18
RUN set -eux; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev \
        postgresql-dev \
        zlib-dev \
    ; \
    \
    docker-php-ext-configure zip; \
    docker-php-ext-configure gd \
          --with-freetype=/usr/include/ \
          --with-jpeg=/usr/include/ \
    ; \
    docker-php-ext-install -j$(nproc) \
        gd \
        intl \
        mysqli \
        pdo \
        pdo_mysql \
        zip \
    ; \
    pecl install \
        apcu-${APCU_VERSION} \
    ; \
    pecl clear-cache; \
    docker-php-ext-enable \
        gd \
        apcu \
#        opcache \
        mysqli \
        pdo_mysql \
    ; \
    \
    runDeps="$( \
        scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
            | tr ',' '\n' \
            | sort -u \
            | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
    )"; \
    apk add --no-cache --virtual .api-phpexts-rundeps $runDeps; \
    \
    apk del .build-deps

COPY --from=composer:1.10 /usr/bin/composer /usr/bin/composer

RUN ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
COPY docker/php/conf.d/app.prod.ini $PHP_INI_DIR/conf.d/app.ini

RUN set -eux; \
    { \
        echo '[www]'; \
        echo 'ping.path = /ping'; \
        echo 'clear_env = no'; \
    } | tee /usr/local/etc/php-fpm.d/docker-config.conf

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
# install Symfony Flex globally to speed up download of Composer packages (parallelized prefetching)
RUN set -eux; \
    composer global require "symfony/flex" --prefer-dist --no-progress --no-suggest --classmap-authoritative; \
    composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /var/www/app

# "php prod" stage
FROM app_php_base AS app_php

# prevent the reinstallation of vendors at every changes in the source code
COPY composer.json composer.lock symfony.lock ./
RUN set -eux; \
    composer install --prefer-dist --no-dev --no-scripts --no-progress --no-suggest; \
    composer clear-cache

# do not use .env files in production
COPY .env ./
RUN composer dump-env prod; \
    rm .env

# copy only specifically what we need
COPY bin bin/
COPY config config/
COPY public public/
COPY src src/
COPY templates templates/
COPY translations translations/

RUN set -eux; \
    mkdir -p var/cache var/log; \
    composer dump-autoload --classmap-authoritative --no-dev; \
    composer run-script --no-dev post-install-cmd; \
    chmod +x bin/console; sync
VOLUME /var/www/app/var

COPY docker/php/docker-healthcheck.sh /usr/local/bin/docker-healthcheck
RUN chmod +x /usr/local/bin/docker-healthcheck

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["docker-healthcheck"]

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]


# "nginx" stage
# depends on the "php" stage above
FROM nginx:${NGINX_VERSION}-alpine AS app_nginx

ADD ./docker/nginx/nginx.conf /etc/nginx/
COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www/app/public

COPY --from=app_php /var/www/app/public ./

ARG PUID=1000
ARG PGID=1000

RUN if [[ -z $(getent group ${PGID}) ]] ; then \
      addgroup -g ${PGID} www-data; \
    else \
      addgroup www-data; \
    fi; \
    adduser -D -u ${PUID} -G www-data www-data
