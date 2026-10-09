FROM php:8.5

ARG NODE_VERSION=22

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y --no-install-recommends \
      git curl zip unzip jq libpng-dev libjpeg-dev libfreetype6-dev \
      libzip-dev libicu-dev libonig-dev libexif-dev libmagickwand-dev libxslt1-dev \
      gnupg ca-certificates libncurses6 \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql gd zip bcmath intl exif pcntl sockets xsl \
    && pecl install imagick redis xdebug && docker-php-ext-enable imagick redis xdebug \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --2 \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && curl -fsSL https://cli.github.com/packages/githubcli-archive-keyring.gpg | dd of=/usr/share/keyrings/githubcli-archive-keyring.gpg \
    && echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/githubcli-archive-keyring.gpg] https://cli.github.com/packages stable main" > /etc/apt/sources.list.d/github-cli.list \
    && apt-get update && apt-get install -y nodejs gh \
    && PLAYWRIGHT_BROWSERS_PATH=/tmp/pw-browsers npx playwright install --with-deps chromium \
    && apt-get -y autoremove && apt-get clean && rm -rf /var/lib/apt/lists/* /var/tmp/*

# Oracle's MySQL client, not MariaDB's: the MariaDB client rejects the
# ssl-mode and column-statistics options in /etc/my.cnf. Oracle ships no
# Debian package for arm64, but the official mysql image is multi-arch and
# its client only needs libncurses6 on top of what this image has.
COPY --from=mysql:8.0 /usr/bin/mysql /usr/bin/mysqldump /usr/local/bin/

RUN git config --global --add safe.directory '*'

# Disable SSL for MySQL client tools (CI MySQL service uses self-signed certs)
RUN printf "[client]\nssl-mode=DISABLED\nget-server-public-key=1\n[mysqldump]\nssl-mode=DISABLED\ncolumn-statistics=0\n" > /etc/my.cnf
