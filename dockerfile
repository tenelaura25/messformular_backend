# Utiliser l'image PHP 8.2 avec Apache (pour un serveur web complet)
FROM php:8.2-apache

# 1. Installer les extensions nécessaires (MySQL, PDO)
#    Nous utilisons docker-php-ext-install et les dépendances nécessaires.
#    Ajout de curl et zip pour les besoins classiques des applications PHP/Composer.
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

# Installer les extensions PHP
RUN docker-php-ext-configure gd --with-webp --with-jpeg
RUN docker-php-ext-install -j$(nproc) gd mysqli pdo pdo_mysql opcache zip

# 2. Configurer le répertoire de travail d'Apache
#    La racine web d'Apache est par défaut /var/www/html
WORKDIR /var/www/html

# 3. Supprimer le fichier index.html par défaut d'Apache
RUN rm -rf /var/www/html/index.html

# 4. Copier le contenu de votre projet dans le répertoire de travail d'Apache
#    Assurez-vous que votre fichier index.php est à la racine de votre dossier de projet.
COPY . /var/www/html

# 5. Modifier la configuration d'Apache si nécessaire (facultatif, mais recommandé)
#    Permet à Apache d'utiliser les fichiers .htaccess pour la réécriture d'URL
RUN a2enmod rewrite

# 6. EXPOSE 8080 n'est pas strictement nécessaire pour l'image Apache car elle écoute déjà
#    sur le port standard 80, et Railway gère la redirection du port 8080 au port 80.
# EXPOSE 8080 

# Le CMD par défaut de l'image 'php:*-apache' lance Apache, pas besoin de le spécifier.
# Apache écoutera automatiquement sur le port 80 et Railway le fera correspondre au port 8080.
