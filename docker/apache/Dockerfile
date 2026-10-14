FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers deflate expires

# Use the project root as the document root; the shipped .htaccess files
# forward requests into /public and protect the private folders.
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Dev-friendly PHP settings (errors visible). Production overrides via config env.
RUN echo "display_errors=On" > /usr/local/etc/php/conf.d/zz-dev.ini \
    && echo "error_reporting=E_ALL" >> /usr/local/etc/php/conf.d/zz-dev.ini \
    && echo "upload_max_filesize=8M" >> /usr/local/etc/php/conf.d/zz-dev.ini

WORKDIR /var/www/html
