# Учет сертификатов и заявок на сертификаты

Ведение клиентской базы, а также доставки молочной продукции с фермы.

## Содержание

- [Описание](#описание)
- [Установка](#установка)
- [Настройка](#настройка)
- [Использование](#использование)

## Описание

WEB-приложение предназначено для учета сертификатов и заявок на сертификаты.

Позволяет:
- отображать статистику по сертификатам и заявкам по ним


## Установка

Шаги по установке проекта:

1. Клонируйте репозиторий:

   Скачайте архив с актуальным релизом и разархивируйте его

2. Добавить текущего пользователя в группу docker

   sudo usermod -aG docker $USER

3. Применить изменения группы

   Выйти и заново войти в систему

   logout

4. Запустить через docker:
    1) если через Laravel Sail:

   docker run --rm \
   -u "$(id -u):$(id -g)" \
   -v "$(pwd):/var/www/html" \
   -w /var/www/html \
   laravelsail/php84-composer:latest \
   composer install --ignore-platform-reqs

    2) если через docker-compose
       
   docker run --rm \
   -u "$(id -u):$(id -g)" \
   -v "$(pwd):/var/www/html" \
   -w /var/www/html \
   composer:latest \
   composer install --ignore-platform-reqs



5. Скопировать .env.example файл как .env и отредактировать его с учетом актуальных настроек
6. Перейти в директорию приложения и запустить контейнеры
    1) через Laravel Sail
   
   ./vendor/bin/sail up -d

    2) если через docker-compose

   docker-compose up -d

7. Подключится в контейнер:   
    1) через Laravel Sail

   ./vendor/bin/sail bash

    2) если через docker-compose

   docker-compose exec    

9. создать символическую ссылку на директорию с вложениями
   php artisan storage:link


## Настройка

1. Ограничения размеров загружаемых на сайт файлов
   
    **Шаг 1: Увеличиваем client_max_body_size в хост-Nginx**

    _sudo nano /etc/nginx/nginx.conf_

    Добавьте в секцию http {:

    _http {
    # Увеличиваем максимальный размер загружаемых файлов
    client_max_body_size 50M;  # Задайте нужный вам размер
    
        # Остальные настройки...
    }_

    **Шаг 2: Проверяем и перезагружаем Nginx**

    _# Проверяем синтаксис
    sudo nginx -t

    # Перезагружаем Nginx
    sudo systemctl reload nginx_

    **Шаг 3: Проверяем настройки контейнерного Nginx**

    Для Laravel также нужно проверить конфигурацию Nginx в контейнере:

    _cd ~/certificate-app
    nano docker/nginx/default.conf_

    Добавьте в секцию server {:

    _server {
    listen 80;
    server_name localhost;

    # Увеличиваем лимит для контейнерного Nginx
    client_max_body_size 50M;
    
    # Остальные настройки...
    }_

    # Перезапускаем контейнерный Nginx
    _docker-compose restart nginx_

    **Шаг 4: Проверяем настройки PHP**

    Также нужно убедиться, что PHP настроен на прием больших файлов:

    # Проверяем текущие настройки PHP
    _docker exec certificate-php php -i | grep -E "upload_max_filesize|post_max_size"_

    Если нужно увеличить, отредактируйте php.ini:

    _nano docker/php/php.ini_

    _upload_max_filesize = 50M
    post_max_size = 55M  # Должен быть больше upload_max_filesize
    memory_limit = 256M
    max_execution_time = 300_

    # Пересобираем образ PHP
    _docker-compose build php
    docker-compose up -d_


## Использование

WEB-приложение предназначено для 


