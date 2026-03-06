FROM php:8.3-fpm

ARG WWWUSER=1000
ARG WWWGROUP=1000

# Включаем non-free репозиторий и устанавливаем зависимости
RUN echo "deb http://deb.debian.org/debian bookworm main contrib non-free non-free-firmware" > /etc/apt/sources.list && \
    echo "deb http://deb.debian.org/debian-security bookworm-security main contrib non-free non-free-firmware" >> /etc/apt/sources.list && \
    echo "deb http://deb.debian.org/debian bookworm-updates main contrib non-free non-free-firmware" >> /etc/apt/sources.list && \
    apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libc-client2007e-dev \
    libkrb5-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Конфигурируем и устанавливаем все расширения
RUN docker-php-ext-configure intl && \
    docker-php-ext-configure imap --with-kerberos --with-imap-ssl && \
    docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    imap

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN groupadd --force -g $WWWGROUP sail && \
    useradd -ms /bin/bash --no-user-group -g $WWWGROUP -u $WWWUSER sail

WORKDIR /var/www/html
USER sail

EXPOSE 9000
CMD ["php-fpm"]
