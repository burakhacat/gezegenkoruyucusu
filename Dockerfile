FROM php:8.2-apache

# SQLite veritabanı desteğini aktifleştirin
RUN apt-get update && apt-get install -y libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite

# Apache mod_rewrite (htaccess kullanımı) iznini açın
RUN a2enmod rewrite

# Proje dosyalarını sunucuya kopyalayın
COPY . /var/www/html/

# Çalışma portunu belirleyin
EXPOSE 80
