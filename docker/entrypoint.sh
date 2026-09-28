#!/bin/sh
set -e

cd /var/www/html

# 等待数据库就绪（最多约 2 分钟）
if [ -n "$DB_HOST" ]; then
  echo "==> 等待数据库 ${DB_HOST}:${DB_PORT:-3306} 就绪 ..."
  attempt=0
  until php -r 'try { new PDO(sprintf("mysql:host=%s;port=%s", getenv("DB_HOST"), getenv("DB_PORT") ?: "3306"), getenv("DB_USERNAME") ?: "root", getenv("DB_PASSWORD") ?: ""); } catch (Throwable $e) { exit(1); }' >/dev/null 2>&1; do
    attempt=$((attempt + 1))
    if [ "$attempt" -ge 60 ]; then
      echo "数据库连接超时，请检查 DB_HOST / DB_PORT / DB_USERNAME / DB_PASSWORD 配置。" >&2
      exit 1
    fi
    sleep 2
  done
  echo "==> 数据库连接成功"
fi

# 未提供 APP_KEY 时自动生成（仅当前容器有效；重建容器会更换，建议通过环境变量固定）
if [ -z "$APP_KEY" ]; then
  export APP_KEY="$(php artisan key:generate --show)"
  echo "==> 未检测到 APP_KEY，已自动生成（建议通过环境变量固定）"
fi

# 执行数据库迁移；首次部署（admin 表为空）时写入默认数据
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  echo "==> 执行数据库迁移 ..."
  php artisan migrate --force
  php artisan tinker --execute='if (\App\Models\Admin::count() === 0) { (new \Database\Seeders\DatabaseSeeder)->run(); echo "Seeded initial data\n"; }'
fi

# 上传与备份目录，保证运行时权限
mkdir -p storage/app/public storage/app/backups
chown -R www-data:www-data storage bootstrap/cache

# 上传目录软链接 + 生产环境缓存
php artisan storage:link >/dev/null 2>&1 || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> XTCMS 启动完成，站点根目录 public/"
exec "$@"
