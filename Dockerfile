FROM php:8.2-alpine
# copy ph-puml sources into container
COPY . /app
WORKDIR /app
# install composer dependencies
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev
ENTRYPOINT ["./bin/ph-puml"]