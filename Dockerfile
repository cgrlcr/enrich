# FROM php:8.1-fpm
# RUN apt update && apt upgrade -y && apt autoremove -y \
#     && docker-php-ext-install bcmath && docker-php-ext-install pdo_mysql \
#     && docker-php-ext-install pdo \
#     && docker-php-ext-install libzip \
#     && apt-get install -y libzip-dev \
#     && apt-get install zip \
#     && docker-php-ext-install zip \
#     && docker-php-ext-configure zip --with-libzip

FROM php:7.4-fpm-alpine
WORKDIR /var/www

ENV APP_ENV=production
ENV APP_DEBUG=false

RUN docker-php-ext-configure opcache --enable-opcache

COPY . /var/www
COPY ./nginx/default.conf /etc/nginx/conf.d/default.conf 
RUN apk add --no-cache zip libzip-dev
RUN docker-php-ext-install zip
RUN docker-php-ext-configure zip
RUN docker-php-ext-install pdo pdo_mysql 
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === '"$(wget -q -O - https://composer.github.io/installer.sig)"') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer
RUN /usr/local/bin/composer install

RUN php artisan config:cache && \
    php artisan route:cache && \
    chmod 777 -R /var/www/storage/ && \
    chown -R www-data:www-data /var/www/  
EXPOSE 80