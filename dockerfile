# Image officielle FrankenPHP avec PHP 8.2 (Alpine)
FROM dunglas/frankenphp:1.3.0-php8.2-alpine

# Installation des dépendances nécessaires et compilation des extensions ZTS
RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        mariadb-dev \
    && docker-php-ext-install mysqli pdo_mysql \
    && docker-php-ext-enable mysqli pdo_mysql \
    && apk del .build-deps

# Vérification (affiche les extensions compilées pendant le build)
RUN php -m | grep -E "mysqli|pdo_mysql" || true

# Copie du code applicatif
WORKDIR /app
COPY . /app

# Optionnel : définir le fuseau horaire
RUN echo "date.timezone=Europe/Paris" > /usr/local/etc/php/conf.d/timezone.ini

# Commande de démarrage FrankenPHP
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]

EXPOSE 80 443 2019
