# Usar la imagen oficial de PHP versión 8.2 con el servidor Apache ya incluido.
FROM php:8.2-apache

# Instalar la extensión de PHP que necesitamos para conectar a la base de datos (mysqli).
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copiar todos los archivos de nuestro proyecto (el punto "." significa "todo lo de esta carpeta")
# a la carpeta pública del servidor web dentro del contenedor.
COPY . /var/www/html/

# (Opcional pero buena práctica) Asegurar que el servidor web tenga los permisos correctos sobre los archivos.
RUN chown -R www-data:www-data /var/www/html
