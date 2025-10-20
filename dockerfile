
# Utiliser l'image PHP 8.2 avec Apache (standard pour une application web)
FROM php:8.2-apache

# Installer les dépendances système et les extensions PHP en une seule étape
# Cela garantit que le cache Docker ne saute pas la partie 'apt-get install'
# juste avant 'docker-php-ext-install'.
RUN apt-get update && apt-get install -y \
    libmariadb-dev \
    libzip-dev \
    # Nettoyer après l'installation
    && rm -rf /var/lib/apt/lists/* \
    \
    # Installer les extensions de base de données (mysqli et pdo_mysql)
    # -j$(nproc) utilise tous les cœurs pour accélérer la compilation
    && docker-php-ext-install -j$(nproc) mysqli pdo_mysql \
    \
    # Copier le contenu du projet dans le répertoire web d'Apache
    && COPY . /var/www/html

# Exposer le port 80 (standard pour Apache)
# Railway redirigera automatiquement le trafic depuis 443/80 vers ce port
EXPOSE 80

# La commande par défaut d'Apache (httpd-foreground) est utilisée
CMD ["apache2-foreground"]
