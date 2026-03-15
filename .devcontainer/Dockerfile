FROM mcr.microsoft.com/devcontainers/php:1-8-bookworm

# Install MariaDB client
RUN rm -f /etc/apt/sources.list.d/yarn.list \
    && apt-get update -o APT::Get::AllowUnauthenticated=true && export DEBIAN_FRONTEND=noninteractive \
    && apt-get install -y mariadb-client libpng-dev --no-install-recommends \
    && apt-get clean -y && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install php-mysql driver
RUN docker-php-ext-install mysqli pdo pdo_mysql gd calendar

# [Optional] Uncomment this section to install additional OS packages.
# RUN apt-get update && export DEBIAN_FRONTEND=noninteractive \
#     && apt-get -y install --no-install-recommends <your-package-list-here>

# [Optional] Uncomment this line to install global node packages.
# RUN su vscode -c "source /usr/local/share/nvm/nvm.sh && npm install -g <your-package-here>" 2>&1

