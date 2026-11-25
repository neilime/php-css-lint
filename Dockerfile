ARG VERSION=8.6

FROM php:${VERSION}-cli

# FIXME: Use a released PCOV package once https://github.com/krakjoe/pcov/issues/138 is resolved.
# This immutable source includes the merged PHP 8.6 fix: https://github.com/krakjoe/pcov/pull/134.
ADD --checksum=sha256:b5a38c69d062f31eb5774c89f1fb403f61e9ae3d979eb859f76aa8156ef0a32d \
    https://codeload.github.com/krakjoe/pcov/tar.gz/c96a5742073e1c42e1d519f5b64bcfce28b1c644 /usr/src/pcov.tar.gz

# PHP 8.6 images omit PECL and mbstring; build the coverage extension with PHP's native tools.
RUN set -eux; \
    export DEBIAN_FRONTEND=noninteractive; \
    apt-get update; \
    apt-get install -y --no-install-recommends unzip libonig-dev; \
    if ! php -r 'exit(extension_loaded("mbstring") ? 0 : 1);'; then \
        docker-php-ext-install mbstring; \
    fi; \
    mkdir /usr/src/pcov; \
    tar -xzf /usr/src/pcov.tar.gz --strip-components=1 -C /usr/src/pcov; \
    cd /usr/src/pcov; \
    phpize; \
    ./configure --enable-pcov; \
    make -j"$(nproc)"; \
    make install; \
    docker-php-ext-enable pcov; \
    cd /; \
    rm -rf /usr/src/pcov /usr/src/pcov.tar.gz /var/lib/apt/lists/*

RUN echo 'memory_limit=512M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini;

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Configure www-data user
ARG UID=1000
ARG GID=1000
RUN usermod --uid $UID www-data && groupmod --gid $GID www-data && chown www-data:www-data /var/www
USER www-data
WORKDIR /var/www/html
