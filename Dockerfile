FROM php:8.2-cli

# Cài đặt các thư viện hệ thống và driver PostgreSQL (pdo_pgsql) cho Neon
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip opcache

# Enable PHP OPcache (including CLI for php artisan serve)
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.interned_strings_buffer=32'; \
    echo 'opcache.max_accelerated_files=20000'; \
    echo 'opcache.validate_timestamps=1'; \
    echo 'opcache.revalidate_freq=60'; \
    echo 'opcache.enable_file_override=1'; \
    echo 'opcache.jit=1255'; \
    echo 'opcache.jit_buffer_size=64M'; \
    echo 'realpath_cache_size=4096K'; \
    echo 'realpath_cache_ttl=600'; \
} > /usr/local/etc/php/conf.d/opcache.ini

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Đặt thư mục làm việc chuẩn xác
WORKDIR /var/www/html

# Copy toàn bộ source code vào trong container
COPY . .

# Cài đặt thư viện PHP (bỏ qua dev dependencies để nhẹ)
RUN composer install --no-dev --optimize-autoloader

# Phân quyền cho thư mục storage và cache của Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Mở cổng 10000 (cổng mặc định của Render)
EXPOSE 10000

# Chạy migrate database và khởi động server Laravel
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
