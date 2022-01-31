FROM php:8.0-fpm

WORKDIR /usr

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        vim \
        sudo \
        libmemcached-dev \
        libz-dev \
        libpq-dev \
        libjpeg-dev \
        libpng-dev \
        libfreetype6-dev \
        libssl-dev \
        libmcrypt-dev \
        libzip-dev \
        unzip \
        zip \
    && docker-php-ext-configure gd \
    && docker-php-ext-configure zip \
    && docker-php-ext-install \
        gd \
        exif \
        opcache \
        pdo_mysql \
        pdo_pgsql \
        pcntl \
        zip \
    && rm -rf /var/lib/apt/lists/*;


# Install Mongdb Driver
RUN pecl install mongodb && docker-php-ext-enable mongodb


# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
COPY ./symfony.ini /usr/local/etc/php/conf.d/symfony.ini
RUN useradd --user-group --system --create-home  -u 1000 --no-log-init app
RUN adduser app sudo
RUN echo '%sudo ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers

WORKDIR /usr/src/app
RUN mkdir -p /vendor
RUN chown app /vendor
USER app
