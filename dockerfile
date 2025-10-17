
# Utiliser PHP 8.2 CLI
FROM php:8.2-cli

# Installer les extensions nécessaires (MySQL, PDO)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copier tout le projet dans le container
COPY . /app

# Définir le répertoire de travail
WORKDIR /app

# Exposer le port dynamique utilisé par Railway
EXPOSE 8080

# Lancer le serveur PHP interne sur le port fourni par Railway
CMD ["php", "-S", "0.0.0.0:${PORT}", "-t", "public"]





