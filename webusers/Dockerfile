FROM php:8.2-apache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install system dependencies needed for PHP extensions + Python
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    python3 \
    python3-pip \
    && pip3 install --break-system-packages openpyxl

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql curl

# Copy app files into Apache's web root
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80