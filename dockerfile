# Utilisation de l'image FPM Alpine, plus stable pour l'installation d'extensions
FROM php:8.2-fpm-alpine AS builder

# Étape 1: Installation des dépendances et compilation de mysqli
# - Installation de git et des dépendances nécessaires pour mysqli (mariadb-client-dev)
RUN apk add --no-cache git \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        mariadb-client-dev \
    # Configuration et installation de mysqli et pdo_mysql
    && docker-php-ext-configure mysqli --with-mysqli=mysql \
    && docker-php-ext-install -j$(nproc) mysqli pdo_mysql \
    # Nettoyage
    && apk del .build-deps

# Étape 2: Construction de l'image finale (avec FrankenPHP)
# Nous utilisons une image PHP avec FPM si vous n'avez pas de configuration Caddyfile complexe.
FROM dunglas/frankenphp:0.29.0-php8.2-alpine

# Copie des extensions compilées depuis l'étape précédente
COPY --from=builder /usr/local/lib/php/extensions/no-debug-non-zts-* \
    /usr/local/lib/php/extensions/no-debug-non-zts-20220829/

# Copie de la configuration PHP pour activer les extensions
COPY --from=builder /usr/local/etc/php/conf.d/docker-php-ext-mysqli.ini \
    /usr/local/etc/php/conf.d/
COPY --from=builder /usr/local/etc/php/conf.d/docker-php-ext-pdo_mysql.ini \
    /usr/local/etc/php/conf.d/

# Copie des fichiers de votre application
COPY . /app

# Définition de la commande de démarrage par défaut de FrankenPHP
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]

# Exposer le port par défaut (80) pour le web
EXPOSE 80 443 2019
