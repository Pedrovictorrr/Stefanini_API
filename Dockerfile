# Use the official PHP image with Apache
FROM php:8.4-apache
# If you want to use PHP 8.3, uncomment the next line and comment the above line:
# FROM php:8.3-apache

# Install system dependencies
# Added libicu-dev for intl extension
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache mod_rewrite and headers
RUN a2enmod rewrite headers

# Set working directory
WORKDIR /var/www

# Fix git "dubious ownership" error
RUN git config --global --add safe.directory /var/www

# Copy only necessary files for composer install (optimize build cache)
COPY composer.json composer.lock ./

# Install dependencies (excluding dev dependencies for production)
RUN composer install --no-interaction --optimize-autoloader --no-dev --no-scripts

# Copy application files (after composer install to optimize cache)
COPY . .

# Ensure required Laravel storage directories exist (after copying files)
RUN mkdir -p /var/www/storage/framework/views /var/www/storage/framework/cache /var/www/storage/framework/sessions \
    && chown -R www-data:www-data /var/www \
    && find /var/www -type d -exec chmod 755 {} \; \
    && find /var/www -type f -exec chmod 644 {} \; \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# Set document root to Laravel's public directory
ENV APACHE_DOCUMENT_ROOT /var/www/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Handle environment configuration
COPY .env.example .env
RUN sed -i 's/^DB_USERNAME=.*/DB_USERNAME=root/' .env && \
    sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=secret/' .env

# Inject OpenWeatherMap API key into .env
RUN echo "OPENWEATHERMAP_API_KEY=4351a63614c4ba37966a3faa03b72dd8" >> .env


# Run composer scripts after everything is set up
# RUN composer run-script post-install-cmd

# Set ServerName to suppress Apache warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Health check
HEALTHCHECK --interval=30s --timeout=3s \
    CMD curl -f http://localhost/ || exit 1

# Expose port 80
EXPOSE 80

# Start Apache with PHP-FPM (better performance)
CMD ["apache2-foreground"]