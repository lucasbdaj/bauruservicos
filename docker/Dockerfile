# Usando a imagem base do PHP com Apache
FROM php:8.3-apache

# Instalando extensões PHP necessárias para o Mysql
RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip

# Ativando mod_rewrite do Apache
RUN a2enmod rewrite

WORKDIR /var/www/html

# Copiando o arquivo de configuração do Apache
# COPY docker/apache-config.conf /etc/apache2/sites-available/000-default.conf
COPY . /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
# Reiniciando o Apache
# RUN service apache2 restart