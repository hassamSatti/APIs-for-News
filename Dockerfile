# Step 1: Choose the PHP image
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    zip \
    git \
    unzip \
    cron \
    libicu-dev \
    libxml2-dev \
    libonig-dev \
    netcat-openbsd \
    msmtp \
    libssl-dev \
    openssl \
    && docker-php-ext-configure pdo_mysql \
    && docker-php-ext-install pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Step 3: Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Step 4: Set working directory inside container
WORKDIR /var/www

# Step 5: Copy the existing application directory to the container
COPY . .

# Step 6: Install Laravel's PHP dependencies
RUN composer install --no-interaction --optimize-autoloader

# Step 7: Set file permissions (optional)
RUN chown -R www-data:www-data /var/www && chmod -R 775 /var/www/storage /var/www/bootstrap/cache 

# Copy the entrypoint script into the container
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

# Make the entrypoint script executable
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set the entrypoint script
ENTRYPOINT ["docker-entrypoint.sh"]
# Step 8: Expose the port the app will run on
EXPOSE 9000

# Step 9: Start the PHP-FPM server
CMD ["php-fpm"]