FROM php:8.1-apache

# Enable Apache modules
RUN a2enmod rewrite

# Copy PHP files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

EXPOSE 80