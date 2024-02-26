# Stage 1: Build PHP-FPM image
FROM php:7.4-fpm AS php

# Install additional PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    && docker-php-ext-install zip

# Stage 2: Build Nginx image
FROM nginx:latest

# Copy Nginx configuration file
COPY nginx.conf /etc/nginx/nginx.conf

# Copy PHP-FPM configuration file
COPY --from=php /usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf

# Copy PHP files to Nginx web root directory
COPY --from=php /var/www/html /usr/share/nginx/html

# Expose ports
EXPOSE 80

# Start Nginx
CMD ["nginx", "-g", "daemon off;"]
