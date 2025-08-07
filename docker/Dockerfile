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

WORKDIR /var/www/html/bauruservicos

# Copiando os arquivos da aplicação
COPY . /var/www/html/bauruservicos

# Copiar o script para esperar o MySQL
COPY docker/wait-for-it.sh /wait-for-it.sh
RUN chmod +x /wait-for-it.sh

EXPOSE 80

# Comando para esperar o MySQL e só depois iniciar o Apache
CMD ["/wait-for-it.sh", "mysql:3306", "--", "apache2-foreground"]
