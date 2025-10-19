# ÉTAPE 1 : Utiliser l'image PHP Apache
# Nous utilisons l'image 'apache' car elle est conçue pour servir des applications web.
FROM php:8.2-apache

# ÉTAPE 2 : Installer les dépendances système pour mysqli et PDO
# Ces librairies sont nécessaires pour compiler correctement les extensions de base de données.
RUN apt-get update && \
    apt-get install -y \
        libmariadb-dev \
        libzip-dev \
        && \
    rm -rf /var/lib/apt/lists/*

# ÉTAPE 3 : Installer et activer les extensions PHP
# mysqli et pdo_mysql pour la connexion à la base de données.
# zip est souvent nécessaire pour les outils de composer/PHP modernes.
RUN docker-php-ext-install -j$(nproc) mysqli pdo pdo_mysql zip

# ÉTAPE 4 : Copier l'application dans le répertoire web d'Apache
# Le répertoire par défaut d'Apache est /var/www/html
COPY . /var/www/html

# Le serveur web est déjà lancé par l'image de base (apache2),
# donc aucune commande CMD n'est nécessaire.
