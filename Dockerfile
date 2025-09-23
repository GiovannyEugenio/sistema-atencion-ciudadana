# Usar la imagen oficial de PHP versión 8.2 con el servidor Apache ya incluido.
FROM php:8.2-apache

# PASO 1: Instalar las dependencias del sistema que necesita PostgreSQL (libpq-dev).
# Primero actualizamos la lista de paquetes y luego instalamos.
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# PASO 2: Ahora que las dependencias están listas, instalamos las extensiones de PHP.
# El comando docker-php-ext-install ya se encarga de habilitarlas también.
RUN docker-php-ext-install mysqli pdo pdo_pgsql

# PASO 3: Copiar todos los archivos de nuestro proyecto a la carpeta del servidor.
COPY . /var/www/html/

# PASO 4: Asegurar que el servidor web tenga los permisos correctos.
RUN chown -R www-data:www-data /var/www/html
