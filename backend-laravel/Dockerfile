FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Dependencias del sistema + nginx + supervisor
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    oniguruma-dev \
    nodejs \
    npm \
    nginx \
    supervisor

# Extensiones PHP
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-install pdo pdo_pgsql mbstring xml bcmath opcache gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar dependencias PHP
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Instalar dependencias Node y compilar frontend
COPY package.json package-lock.json ./
RUN npm ci

# Copiar la aplicacion
COPY . .

# Compilar assets
RUN npm run build

RUN php artisan package:discover --ansi

# Permisos de storage
RUN mkdir -p storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Redirigir el error log principal de nginx a stderr
RUN sed -i 's|error_log /var/log/nginx/error.log warn;|error_log /dev/stderr warn;|' /etc/nginx/nginx.conf || true

# Generar nginx config (sin CRLF de Windows)
RUN printf 'server {\n\
    listen %s;\n\
    root /var/www/html/public;\n\
    index index.php;\n\
    access_log off;\n\
    location /ping {\n\
        return 200 "pong";\n\
        add_header Content-Type text/plain;\n\
    }\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
    location ~ \\.php$ {\n\
        try_files $uri =404;\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_index index.php;\n\
        include fastcgi_params;\n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n\
    }\n\
}\n' "${PORT:-8000}" > /etc/nginx/http.d/default.conf

# Generar supervisord config (sin CRLF de Windows)
RUN mkdir -p /etc/supervisor/conf.d \
    && printf '[supervisord]\nnodaemon=true\nlogfile=/dev/null\nlogfile_maxbytes=0\n\n[program:php-fpm]\ncommand=php-fpm\nautostart=true\nautorestart=true\nstdout_logfile=/dev/stdout\nstdout_logfile_maxbytes=0\nstderr_logfile=/dev/stderr\nstderr_logfile_maxbytes=0\n\n[program:nginx]\ncommand=nginx -g "daemon off;"\nautostart=true\nautorestart=true\nstdout_logfile=/dev/stdout\nstdout_logfile_maxbytes=0\nstderr_logfile=/dev/stderr\nstderr_logfile_maxbytes=0\n' > /etc/supervisor/conf.d/supervisord.conf

# Generar startup script (sin CRLF de Windows)
RUN printf '#!/bin/sh\nset -e\necho "[start.sh] PORT=${PORT:-8000}"\nphp artisan migrate --force\nphp artisan db:seed --force\nphp artisan storage:link 2>/dev/null || true\nphp artisan config:cache\nphp artisan route:cache\nsed -i "s/listen [0-9]*/listen ${PORT:-8000}/" /etc/nginx/http.d/default.conf\necho "[start.sh] nginx config:"\ncat /etc/nginx/http.d/default.conf\nnginx -t 2>&1\nmkdir -p /run/nginx\nexec supervisord -c /etc/supervisor/conf.d/supervisord.conf\n' > /start.sh \
    && chmod +x /start.sh

EXPOSE 8000

CMD ["/bin/sh", "/start.sh"]
