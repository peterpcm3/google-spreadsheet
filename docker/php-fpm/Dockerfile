FROM php:7.4-fpm-alpine

RUN apk --update --no-cache add git

RUN docker-php-ext-install pdo_mysql
RUN apk add libzip-dev
RUN apk add zip
RUN docker-php-ext-install zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install node and npm
RUN apk add nodejs-current
RUN apk add --update npm

WORKDIR /var/www

CMD composer install ; php-fpm

EXPOSE 9000