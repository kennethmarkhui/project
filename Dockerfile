FROM php:8.4-apache

ENV APP_HOME /var/www
ARG HOST_UID
ARG HOST_GID
ENV USER=www-data

RUN apt-get update && apt-get install -y  \
    unzip \
    git \
    curl \
    libmagickwand-dev \
    --no-install-recommends \
    && pecl install imagick \
    && docker-php-ext-enable imagick opcache \
    && docker-php-ext-install pdo_mysql pcntl \
    && apt-get autoclean -y \
    && rm -rf /var/lib/apt/lists/*

# Install Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Create document root, fix permissions for www-data user and change owner to www-data 
RUN mkdir -p ${APP_HOME}/public \
    && mkdir -p /home/${USER} && chown ${USER}:${USER} /home/${USER} \
    && usermod -o -u ${HOST_UID} ${USER} -d /home/${USER} \
    && groupmod -o -g ${HOST_GID} ${USER} \
    && chown -R ${USER}:${USER} ${APP_HOME}    

# Update apache conf to point to application public directory
ENV APACHE_DOCUMENT_ROOT=${APP_HOME}/public
RUN sed -ri -e "s!${APP_HOME}/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf
RUN sed -ri -e "s!${APP_HOME}/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Update uploads config
RUN echo "file_uploads = On\n" \
         "memory_limit = 1024M\n" \
         "upload_max_filesize = 512M\n" \
         "post_max_size = 512M\n" \
         "max_execution_time = 1200\n" \
         > /usr/local/etc/php/conf.d/uploads.ini

# Enable headers module
RUN a2enmod rewrite headers 

# Set working directory
WORKDIR ${APP_HOME}

# Set user
USER ${USER}

   