FROM php:8.3.30-apache

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
RUN a2enmod rewrite headers

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY ./docker/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
