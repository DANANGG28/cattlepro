# ============================================================
# CattlePro - Dockerfile untuk Dokploy
# Base: PHP 8.2 + Apache
# ============================================================

FROM php:8.2-apache

# Install ekstensi PHP yang dibutuhkan
RUN apt-get update && apt-get install -y \
        libcurl4-openssl-dev \
        libssl-dev \
        libonig-dev \
        unzip \
    && docker-php-ext-install \
        curl \
        mbstring \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Aktifkan Apache mod_rewrite
RUN a2enmod rewrite

# Konfigurasi Apache — set DocumentRoot ke folder project
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html|g' \
        /etc/apache2/sites-available/000-default.conf

# Izinkan .htaccess override
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

# Konfigurasi PHP untuk production
RUN echo "display_errors = Off" >> /usr/local/etc/php/php.ini && \
    echo "log_errors = On" >> /usr/local/etc/php/php.ini && \
    echo "error_log = /var/log/apache2/php_error.log" >> /usr/local/etc/php/php.ini && \
    echo "max_execution_time = 60" >> /usr/local/etc/php/php.ini && \
    echo "memory_limit = 128M" >> /usr/local/etc/php/php.ini && \
    echo "upload_max_filesize = 10M" >> /usr/local/etc/php/php.ini && \
    echo "post_max_size = 12M" >> /usr/local/etc/php/php.ini && \
    echo "session.gc_maxlifetime = 3600" >> /usr/local/etc/php/php.ini

# Konfigurasi OPcache untuk performa
RUN echo "opcache.enable=1" >> /usr/local/etc/php/php.ini && \
    echo "opcache.memory_consumption=128" >> /usr/local/etc/php/php.ini && \
    echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/php.ini && \
    echo "opcache.revalidate_freq=60" >> /usr/local/etc/php/php.ini

# Copy project files ke container
COPY . /var/www/html/

# Set permission yang benar
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Buat direktori cache/logs dengan permission write
RUN mkdir -p /var/www/html/cache /var/www/html/logs \
    && chmod -R 775 /var/www/html/cache /var/www/html/logs \
    && chown -R www-data:www-data /var/www/html/cache /var/www/html/logs

# Copy dan set entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
