FROM php:8.2-cli

# Instalar herramientas del sistema y extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    unzip \
    zip \
    libzip-dev \
    git \
    && docker-php-ext-install mysqli zip \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer desde imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar proyecto
COPY . .

# Instalar dependencias PHP
RUN composer install --no-interaction --prefer-dist

# Exponer puerto interno
EXPOSE 8000

# Correr servidor PHP apuntando a public
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]