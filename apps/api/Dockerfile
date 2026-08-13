FROM composer:2 AS vendor
WORKDIR /app
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --optimize-autoloader

FROM php:8.4-cli-alpine
RUN apk add --no-cache libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && addgroup -S app \
    && adduser -S -G app app
WORKDIR /app
COPY --from=vendor --chown=app:app /app /app
COPY --chown=app:app docker/entrypoint.sh /usr/local/bin/app-entrypoint
USER app
EXPOSE 8000
ENTRYPOINT ["app-entrypoint"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
