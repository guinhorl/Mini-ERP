# Usa a imagem oficial do PHP com FPM (FastCGI Process Manager)
# Escolha uma versão que corresponda à sua "última versão do PHP" (ex: 8.3-fpm-alpine)
FROM php:8.3-fpm-alpine

# Instala extensões PHP e dependências.
# A Alpine Linux é uma imagem leve, então precisamos instalar as dependências de build primeiro.
RUN apk add --no-cache \
    autoconf \
    build-base \
    libtool \
    libzip-dev \
    # Outras extensões comuns, adapte conforme a necessidade do seu projeto
    oniguruma-dev \
    libpq \
    libpng-dev \
    jpeg-dev \
    # Dependências do Composer
    git \
    openssh-client \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    zip \
    gd \
    # Adicione outras extensões que você precise, ex:
    # exif \
    # intl \
    # soap \
    # bcmath \
    # opcache \ # Opcional, para performance
    # pdo_pgsql \ # Se usar PostgreSQL
    && docker-php-ext-enable pdo_mysql \
    # Opcional: Instala o Composer globalmente no contêiner
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /tmp/* /var/cache/apk/*

# Define o diretório de trabalho dentro do contêiner
WORKDIR /var/www/html

# Copia o arquivo php.ini personalizado (se existir)
# COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Expondo a porta padrão do PHP-FPM
EXPOSE 9000

# Comando padrão para iniciar o PHP-FPM
CMD ["php-fpm"]