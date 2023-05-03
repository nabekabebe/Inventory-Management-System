FROM laravelphp/sail:latest

COPY . /var/www/html

USER root

RUN apt-get update && apt-get install -y postgresql

USER root

RUN composer install --ignore-platform-reqs

RUN php artisan key:generate

EXPOSE 80
