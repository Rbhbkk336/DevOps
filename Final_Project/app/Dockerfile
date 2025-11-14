FROM php:8.3.2-fpm AS builder

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    ca-certificates \
    && docker-php-ext-install pdo pdo_pgsql intl mbstring zip opcache \
    && update-ca-certificates \
    && rm -rf /var/lib/apt/lists/*

RUN echo "curl.cainfo=/etc/ssl/certs/ca-certificates.crt" >> /usr/local/etc/php/conf.d/docker-php-ext-curl.ini \
    && echo "openssl.cafile=/etc/ssl/certs/ca-certificates.crt" >> /usr/local/etc/php/conf.d/docker-php-ext-openssl.ini

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

FROM php:8.3.2-fpm 

RUN apt-get update && apt-get install -y\
    tesseract-ocr tesseract-ocr-eng tesseract-ocr-rus\
    libicu-dev libpq-dev libonig-dev \
    && docker-php-ext-install intl pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . /app
COPY --from=builder /app /app

EXPOSE 8000
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]