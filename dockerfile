# Utiliser PHP 8.2 CLI
FROM php:8.2-cli

# Installer les extensions nécessaires (MySQL, PDO)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copier tout le projet dans le container
COPY . /app

# Définir le répertoire de travail
WORKDIR /app

# Exposer le port 8080 (par défaut utilisé par Railway)
EXPOSE 8080

# Démarrer le serveur PHP interne sur le port Railway
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/app/public"]
