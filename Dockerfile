# syntax=docker/dockerfile:1
# ↑ 固定使用新版构建语法（第一行固定写法，不用管它）

########################################
# 【阶段 1】"装修车间"：在这个临时车间里把 PHP 依赖包下载好
# 为什么要单独一个阶段？因为下载依赖需要 composer 这个工具，
# 但网站真正运行时用不到它。车间用完就扔，不会把工具带进最终镜像，
# 最终镜像更干净、体积更小。（这就是"多阶段构建"的意思）
########################################
FROM composer:2 AS vendor
# ↑ FROM = 从官方现成的镜像开始。composer:2 是一个自带 PHP 依赖
#   下载工具（composer）的官方镜像。AS vendor 表示给它起名叫"vendor"，
#   方便阶段 2 来取东西。

WORKDIR /app
# ↑ WORKDIR = 进入容器里的 /app 文件夹，之后的命令都在这个文件夹里执行。
#   相当于"cd /app"。这个 /app 只是临时车间的目录，跟最终容器无关。

# 先复制依赖清单，充分利用构建缓存
COPY composer.json composer.lock ./
# ↑ COPY = 把文件从你的项目文件夹复制进容器。
#   composer.json / composer.lock 记录了项目需要哪些依赖包、什么版本。
#   只先复制这两个小文件是有技巧的：只要它们没变，下次重新构建时
#   下面的"下载依赖"步骤会直接用缓存结果，几秒钟就过，不用重新下载。

RUN composer install \
    --no-dev \             # 不装开发阶段才用的工具包（调试器等），生产环境用不上
    --no-scripts \         # 装完先不执行项目自带的脚本（这时业务代码还没复制进来，跑了会报错）
    --no-autoloader \      # 先不生成"类文件地图"，等代码复制完再统一生成
    --prefer-dist \        # 优先下载打包好的 zip 版本，比拉源码快
    --no-interaction \     # 全程自动应答，不弹任何提问（否则构建会卡住）
    --no-progress          # 不显示进度条，让构建日志干净些
# ↑ RUN = 在容器里执行一条命令。这条就是"把项目依赖的所有 PHP 包
#   下载安装到 vendor 目录"。

# 再复制业务代码并生成优化过的自动加载器
COPY app/ app/
COPY bootstrap/ bootstrap/
COPY database/ database/
COPY resources/ resources/
COPY routes/ routes/
COPY artisan ./
# ↑ 把项目的核心代码（控制器、模板、路由、数据库定义等）复制进来。
#   之所以放这一步，是因为这些代码改动频繁，放在依赖下载之后，
#   改代码重新构建时就不会触发耗时的大重新下载。

RUN composer dump-autoload --optimize --no-dev --no-scripts
# ↑ 生成优化过的"类文件地图"（PHP 找类文件用的索引表），
#   让生产环境加载类更快。

########################################
# 【阶段 2】"正式房子"：网站真正运行用的最终镜像
# 基础镜像 = PHP 8.2 + Apache 网页服务器，一个镜像全包了，
# 不需要再单独装 Nginx + PHP-FPM。
########################################
FROM php:8.2-apache
# ↑ 从 PHP 8.2 官方镜像开始（它内置了 Apache），之前的车间"vendor"
#   到此为止不再使用。

# pdo_mysql 扩展（mbstring / openssl / fileinfo 官方镜像已内置）
RUN docker-php-ext-install pdo_mysql
# ↑ 安装 PHP 连接 MySQL 必需的扩展 pdo_mysql。
#   官方镜像默认没开这个扩展，必须装，否则连不上你的数据库。

# 启用伪静态，站点根目录指向 public/，消除 ServerName 告警
RUN a2enmod rewrite && \
    sed -ri 's!/var/www/html!/var/www/html/public!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf && \
    printf 'ServerName localhost\n' > /etc/apache2/conf-available/xtcms-servername.conf && \
    a2enconf xtcms-servername
# ↑ 这一大段是配置 Apache（容器里的网页服务器），拆开看：
#   1) a2enmod rewrite        开启"伪静态"模块——Laravel 必需，
#                             没它的话访问 /admin 这类地址会 404；
#   2) sed -ri 's!...!...!g'  把 Apache 默认的网站根目录
#                             /var/www/html 改成 /var/www/html/public
#                             ——Laravel 规定只有 public/ 才能对外暴露，
#                             其余代码不能让访客直接下载到；
#   3) printf ...             写入一个服务器名配置，消除 Apache 启动时的
#                             一条无害但烦人的警告；
#   4) a2enconf ...           启用刚写入的那份配置。

WORKDIR /var/www/html
# ↑ 之后所有命令都切换到 /var/www/html（正式容器里的网站根目录）。

# 复制源代码，并用阶段 1 优化过的 vendor 覆盖本地目录
COPY --chown=www-data:www-data . .
# ↑ 把你的整个项目文件夹复制进容器的网站目录。
#   --chown=www-data:www-data 表示把这些文件的主人设为 www-data
#   （Apache 运行时用的账号），不然网页服务器没有权限读这些文件。
#   （注意：.env、vendor 等被 .dockerignore 挡住了，不会复制进来）

COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
# ↑ 关键一步：从阶段 1 的"车间"里，把下载好的依赖包目录 vendor
#   取过来放进最终镜像。这就是多阶段构建的"取货"动作。

RUN chmod +x docker/entrypoint.sh && \
    chown -R www-data:www-data storage bootstrap/cache
# ↑ 启动前收尾：
#   1) 给启动脚本加上"可执行"权限（Windows 下复制过来会丢失这个权限）；
#   2) 把 storage 和 bootstrap/cache 两个目录（Laravel 运行时要
#      不停写缓存、日志、上传文件的地方）也交给 www-data，否则网站会报
#      "没有写入权限"的错误。

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_LEVEL=warning
# ↑ ENV = 写死三个环境变量到镜像里：
#   生产模式、关闭调试信息（出错不暴露代码细节）、日志只记警告以上。
#   安全起见，这三个生产环境不能放开。

EXPOSE 80
# ↑ 声明本容器对外用 80 端口提供网页服务（HTTP 默认端口）。
#   注意这只是"声明"，真正映射到宿主机 8080 端口的，是
#   docker-compose.yml 里 ports 那两行。

ENTRYPOINT ["docker/entrypoint.sh"]
# ↑ 容器每次启动时，最先执行这个脚本。它会：等你数据库就绪 →
#   自动建表 → 建软链接 → 生成缓存。相当于"开机自动装修"。

CMD ["apache2-foreground"]
# ↑ 脚本跑完后，正式启动 Apache 网页服务器（前台模式运行，
#   这是容器的要求：必须有一个一直不退出的主进程，容器才活着）。
