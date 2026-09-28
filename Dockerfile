# syntax=docker/dockerfile:1

########################################
# 阶段 1：安装 Composer 依赖（排除开发包，优化自动加载）
########################################
FROM composer:2 AS vendor

WORKDIR /app

# 先复制依赖清单，充分利用构建缓存
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction \
    --no-progress

# 再复制业务代码并生成优化过的自动加载器
COPY app/ app/
COPY bootstrap/ bootstrap/
COPY database/ database/
COPY resources/ resources/
COPY routes/ routes/
COPY artisan ./

RUN composer dump-autoload --optimize --no-dev --no-scripts

########################################
# 阶段 2：运行镜像（PHP 8.2 + Apache）
########################################
FROM php:8.2-apache

# pdo_mysql 扩展（mbstring / openssl / fileinfo 官方镜像已内置）
RUN docker-php-ext-install pdo_mysql

# 启用伪静态，站点根目录指向 public/，消除 ServerName 告警
RUN a2enmod rewrite && \
    sed -ri 's!/var/www/html!/var/www/html/public!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf && \
    printf 'ServerName localhost\n' > /etc/apache2/conf-available/xtcms-servername.conf && \
    a2enconf xtcms-servername

WORKDIR /var/www/html

# 复制源代码，并用阶段 1 优化过的 vendor 覆盖本地目录
COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor

RUN chmod +x docker/entrypoint.sh && \
    chown -R www-data:www-data storage bootstrap/cache

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_LEVEL=warning

EXPOSE 80

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["apache2-foreground"]
