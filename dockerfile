# Utiliser l'image PHP 8.2 avec Apache (pour un serveur web complet)
FROM php:8.2-apache

# 1. Installer les dépendances système nécessaires
RUN apt-get update && \
    apt-get install -y \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libwebp-dev \
        curl \
        zip \
        unzip \
    && rm -rf /var/lib/apt/lists/*

# 2. Installer et ACTIVER les extensions PHP
#    Les extensions de base de données (mysqli, pdo_mysql) doivent être installées
#    directement. J'ai ajouté l'installation explicite de mysqli ici.
RUN docker-php-ext-configure gd --with-webp --with-jpeg
RUN docker-php-ext-install -j$(nproc) \
    gd \
    opcache \
    zip \
    # Extensions de base de données :
    mysqli \
    pdo \
    pdo_mysql

# 3. Configurer le répertoire de travail d'Apache
#    La racine web d'Apache est par défaut /var/www/html
WORKDIR /var/www/html

# 4. Supprimer le fichier index.html par défaut d'Apache
RUN rm -rf /var/www/html/index.html

# 5. Copier le contenu de votre projet dans le répertoire de travail d'Apache
COPY . /var/www/html

# 6. Modifier la configuration d'Apache
#    Permet à Apache d'utiliser les fichiers .htaccess pour la réécriture d'URL
RUN a2enmod rewrite