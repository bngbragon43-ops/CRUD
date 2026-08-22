#  pour prendre une image PHP avec apache
FROM php:8.2-apache
#  Ajoute les extensions necessaires
RUN apt-get update && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql

RUN a2enmod rewrite

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

#  Vhost pointant sur Public/ (front controller)
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer  /usr/bin/composer

COPY composer.json composer.lock ./

#  install les dependances 
RUN composer install --no-interaction --prefer-dist --no-scripts --no-autoloader

#  copy le projet dans le conteneur
COPY . .

RUN composer dump-autoload --optimize \
    && chown -R www-data:www-data /var/www/html
EXPOSE 80
CMD ["apache2-foreground"]
