
# Utiliser PHP 8.2 CLI
FROM php:8.2-cli

# Installer les extensions nécessaires (MySQL, PDO)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copier tout le projet dans le container
COPY . /app

# Définir le répertoire de travail
WORKDIR /app

# Exposer le port utilisé par Railway
EXPOSE 8080

# Démarrer le serveur PHP interne sur le bon port
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public"]






