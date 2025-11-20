FROM php:8.2-apache

# Installa estensioni necessarie per MariaDB/MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Abilita mod_rewrite (utile per routing)
RUN a2enmod rewrite