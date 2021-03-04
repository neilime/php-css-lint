FROM php:cli

WORKDIR /app

RUN apt-get -yqq update && \
    apt-get install -yqq --no-install-recommends libzip-dev zip && docker-php-ext-install zip \
    &&  pecl install pcov && docker-php-ext-enable pcov

RUN echo 'memory_limit = 512M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini;


COPY --from=composer /usr/bin/composer /usr/bin/composer

