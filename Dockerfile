# Menggunakan base image PHP 8.2 dengan web server Apache
FROM php:8.2-apache

# Install ekstensi yang dibutuhkan Laravel & Database MySQL
RUN apt-get update && apt-get install -y libzip-dev zip unzip \
    && docker-php-ext-install pdo_mysql zip

# Aktifkan modul URL rewrite milik Apache
RUN a2enmod rewrite

# Set folder kerja di dalam server
WORKDIR /var/www/html

# Salin seluruh file proyek Laravel kita ke dalam server
COPY . .

# Install Composer dan dependensi Laravel
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Arahkan Document Root server langsung ke folder "public" Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Berikan hak akses agar Laravel bisa menyimpan gambar dan cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
