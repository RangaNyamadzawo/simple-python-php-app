FROM php:8.1-apache

# Enable Apache modules
RUN a2enmod rewrite proxy proxy_http

# Copy PHP files
COPY . /var/www/html/

# Create Apache configuration with proxy
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
    \n\
    # Proxy API calls to backend\n\
    ProxyPreserveHost On\n\
    ProxyPass /api/ http://backend:5000/api/\n\
    ProxyPassReverse /api/ http://backend:5000/api/\n\
    \n\
    # Serve static files\n\
    <Directory "/var/www/html">\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    \n\
    # Health check\n\
    <Location "/health">\n\
        ProxyPass http://backend:5000/health\n\
        ProxyPassReverse http://backend:5000/health\n\
    </Location>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

EXPOSE 80