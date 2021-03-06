FROM php:8.0-cli

# Install dependencies
RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    git \
  && rm -rf /var/lib/apt/lists/*

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PDO
# RUN docker-php-ext-install pdo_mysql
