# Utiliser l'image PHP 8.2 avec Apache (standard pour une application web)
FROM php:8.2-apache

# ÉTAPE 1: Installer les dépendances système et les extensions PHP
# Cela se fait en une seule commande RUN pour garantir que toutes les dépendances sont là.
RUN apt-get update && apt-get install -y \
    libmariadb-dev \
    libzip-dev \
    # Installer les extensions de base de données (mysqli et pdo_mysql)
    && docker-php-ext-install -j$(nproc) mysqli pdo_mysql \
    # Nettoyer après l'installation
    && rm -rf /var/lib/apt/lists/*

# ÉTAPE 2: Copier le contenu du projet dans le répertoire web d'Apache
# NOTE : COPY doit être une instruction séparée, pas dans le RUN
COPY . /var/www/html

# ÉTAPE 3: Configuration du serveur
# Exposer le port 80 (standard pour Apache)
EXPOSE 80

# La commande par défaut d'Apache (httpd-foreground) est utilisée
CMD ["apache2-foreground"]
