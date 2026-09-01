FROM php:8.2

RUN apt-get update -y && apt-get install -y openssl zip unzip git libonig-dev libpq-dev libpng-dev libjpeg-dev libfreetype6-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring gd zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html
COPY . /var/www/html
RUN git config --global --add safe.directory /var/www/html

RUN composer install

# RUN php artisan key:generate 
RUN php artisan config:cache 
RUN php artisan storage:link

CMD php artisan serve --host=0.0.0.0 --port=8888
EXPOSE 8888
