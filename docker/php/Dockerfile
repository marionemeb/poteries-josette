FROM php:7.4-cli

# Debian bullseye is EOL: deb.debian.org no longer serves it (main repo moved to
# archive.debian.org, and the security suite isn't mirrored there at all), so
# rewrite sources.list to drop debian-security and point everything else at the archive.
RUN printf 'deb http://archive.debian.org/debian bullseye main\ndeb http://archive.debian.org/debian bullseye-updates main\n' > /etc/apt/sources.list \
    && echo 'Acquire::Check-Valid-Until "false";' > /etc/apt/apt.conf.d/no-check-valid-until

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
        libxml2-dev \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        intl \
        zip \
        xml \
        opcache \
    && rm -rf /var/lib/apt/lists/*

# composer.lock predates Composer 2 (pinned ocramius/package-versions + symfony/flex
# versions that require the Composer 1.x plugin API) — use Composer 1 to match it.
COPY --from=composer:1 /usr/bin/composer /usr/bin/composer

RUN git config --system --add safe.directory /var/www

WORKDIR /var/www
