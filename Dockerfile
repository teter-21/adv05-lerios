
FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli gd \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

COPY . /var/www/html/

RUN chown -R www-data:www-data \
    /var/www/html/images/users \
    /var/www/html/uploads

# Create group 1000 if it doesn't exist
RUN getent group 1000 || groupadd -g 1000 rendersecrets

# Give Apache/PHP access to Render Secret Files
RUN usermod -a -G 1000 www-data

EXPOSE 80

CMD ["apache2-foreground"]
