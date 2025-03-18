FROM php:8.2-apache

# 安装系统依赖
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    git \
    curl \
    gnupg \
    libicu-dev \
    libmemcached-dev \
    default-mysql-client \
    vim \
    screen \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 配置 PHP 扩展
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql \
    mysqli \
    zip \
    gd \
    mbstring \
    exif \
    pcntl \
    bcmath \
    xml \
    intl \
    opcache

# 设置 PHP 配置文件
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# 安装 Node.js 和 npm
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get update \
    && apt-get install -y nodejs \
    && npm install -g npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 安装 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 启用 Apache 模块和配置
RUN a2enmod rewrite headers expires \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# 设置工作目录
WORKDIR /var/www/html

# 设置适当的权限
RUN chown -R www-data:www-data /var/www/html

# 暴露端口
EXPOSE 80 443 5173 8000

# 启动Apache
CMD ["apache2-foreground"]