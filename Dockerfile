FROM php:8.3-apache
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli gd \
    && rm -rf /var/lib/apt/lists/*
RUN a2enmod rewrite && echo 'ServerName localhost' >> /etc/apache2/apache2.conf
COPY . /var/www/html/
RUN mkdir -p /var/www/html/images/users /var/www/html/uploads/instructors \
    && chown -R www-data:www-data /var/www/html/images/users /var/www/html/uploads \
    && (getent group 1000 >/dev/null || groupadd -g 1000 rendersecrets) \
    && usermod -aG 1000 www-data
EXPOSE 80
CMD ["apache2-foreground"]
