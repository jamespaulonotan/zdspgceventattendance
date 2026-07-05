FROM php:8.2-apache
# Install PostgreSQL extension required to connect to Supabase
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql
# Copy your PHP site files into the server's public folder
COPY . /var/www/html/
EXPOSE 80
