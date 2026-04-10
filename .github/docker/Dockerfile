FROM php:8.5

ARG NODE_VERSION=22

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y --no-install-recommends \
      git curl zip unzip libpng-dev libjpeg-dev libfreetype6-dev \
      libzip-dev libicu-dev libonig-dev libexif-dev libmagickwand-dev libxslt1-dev \
      gnupg ca-certificates \
    && if [ "$(dpkg --print-architecture)" = "amd64" ]; then \
         curl -sL "https://dev.mysql.com/get/Downloads/MySQL-8.0/mysql-community-client-core_8.0.42-1debian12_amd64.deb" -o /tmp/mysql-client.deb \
         && dpkg -x /tmp/mysql-client.deb /tmp/mysql \
         && cp /tmp/mysql/usr/bin/mysql /usr/local/bin/mysql \
         && cp /tmp/mysql/usr/bin/mysqldump /usr/local/bin/mysqldump \
         && rm -rf /tmp/mysql /tmp/mysql-client.deb; \
       else \
         apt-get install -y --no-install-recommends default-mysql-client; \
       fi \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql gd zip bcmath intl exif pcntl sockets xsl \
    && pecl install imagick redis xdebug && docker-php-ext-enable imagick redis xdebug \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && curl -fsSL https://cli.github.com/packages/githubcli-archive-keyring.gpg | dd of=/usr/share/keyrings/githubcli-archive-keyring.gpg \
    && echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/githubcli-archive-keyring.gpg] https://cli.github.com/packages stable main" > /etc/apt/sources.list.d/github-cli.list \
    && apt-get update && apt-get install -y nodejs gh \
    && npx playwright install-deps \
    && apt-get -y autoremove && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN git config --global --add safe.directory '*'

# Disable SSL for MySQL client tools (CI MySQL service uses self-signed certs)
RUN printf "[client]\nssl-mode=DISABLED\n[mysqldump]\nssl-mode=DISABLED\ncolumn-statistics=0\n" > /etc/my.cnf
