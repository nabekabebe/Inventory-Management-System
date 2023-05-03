FROM laravelphp/sail:8.2-1.0

COPY . /var/www/html

USER root

RUN apt-get update && apt-get install -y postgresql

USER sail

RUN composer install --ignore-platform-reqs

RUN php artisan key:generate

EXPOSE 80
