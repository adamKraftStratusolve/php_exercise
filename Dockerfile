# Use the official PHP 8.2 image with Apache
FROM php:8.2-apache

# Install system dependencies required for PHP extensions
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure and install the GD extension for image processing
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Install the PDO MySQL extension for database connectivity
RUN docker-php-ext-install pdo_mysql

# Enable Apache's mod_rewrite for clean URLs (good practice)
RUN a2enmod rewrite

# Set the working directory to the web root
WORKDIR /var/www/html

# Copy the application source code into the container
COPY . /var/www/html/
