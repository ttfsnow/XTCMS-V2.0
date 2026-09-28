# XTCMS V2.0

讯通个人微博系统 V2.0 —— 基于 **Laravel 11 + MySQL 8 + TinyMCE 6** 对 XTCMS V1.0（约 2010 年的 PHP 5 老站）的全面重构。

> 一个轻量级的个人博客 / 内容管理系统：前台浏览文章、后台管理分类和文章，无需复杂配置即可跑起来。

## 功能特性

- **前台**：首页（最新文章）、分类列表页（含子分类）、文章详情页（含相关文章、上/下一篇、阅读次数）、关键词搜索，响应式布局，手机可看
- **文章归档**：`/archive` 按年-月分组展示全部文章；侧边栏显示年份归档
- **文章标签**：独立标签体系（`tags` / `newstag` 表，与 SEO 关键词分离）。后台「标签管理」统一维护（新建/重命名/删除），写文章时直接勾选；前台可点击标签跳转 `/tag/{标签}` 聚合页；侧边栏标签云
- **后台**：仪表盘、无限级分类管理、文章增删改查（TinyMCE 6 富文本编辑器）、站点配置、修改密码
- **内容管理**：文章支持**草稿 / 发布**两种状态（草稿前台不可见）；支持手动摘要，留空自动截取正文
- **外观定制**：站点配置支持**自定义 CSS** 与**头部代码注入**（统计脚本、站长验证等），改字体/颜色无需改模板
- **图片上传**：编辑器内选择 / 粘贴 / 拖拽图片自动上传到 `storage/app/public/uploads`（不再 Base64 内嵌）
- **SEO 与订阅**：RSS 订阅（`/feed`）、站点地图（`/sitemap.xml`）、文章页动态 meta description / keywords
- **数据安全**：内置数据库备份命令 `php artisan xtcms:backup`（纯 PHP 导出，无需 mysqldump）
- **安全**：参数绑定防 SQL 注入、CSRF 防护、登录限流（5 次/分钟）、密码 bcrypt 加密、XSS 输出转义
- **迁移**：内置一键导入命令，可把 V1.0 老库数据平滑迁入

## 技术栈对比

| 层面         | V1.0（约 2010）           | V2.0                                       |
| ------------ | ------------------------- | ------------------------------------------ |
| 运行时       | PHP 5 +`mysql_*` 扩展   | PHP 8.2+                                   |
| 框架         | 无（过程式 PHP 页面）     | Laravel 11                                 |
| 数据库       | MySQL（MyISAM，无预处理） | MySQL 8（InnoDB，Eloquent ORM + 参数绑定） |
| 模板         | Smarty 2                  | Blade                                      |
| 富文本编辑器 | whizzywig                 | TinyMCE 6（CDN，中文界面）                 |
| 前端         | XHTML 表格布局            | HTML5 + Tailwind CSS（CDN），响应式        |
| 认证         | Session + MD5             | Session + bcrypt（自动升级旧 MD5 密码）    |

## 环境要求

| 软件     | 版本要求                     | 说明                                               |
| -------- | ---------------------------- | -------------------------------------------------- |
| PHP      | >= 8.2                       | 需启用扩展：pdo_mysql、mbstring、fileinfo、openssl |
| Composer | 2.x                          | PHP 依赖管理工具                                   |
| MySQL    | >= 8.0（或 MariaDB >= 10.6） | 数据库                                             |

> **小白提示**：Windows 下推荐直接安装 [phpStudy（小皮面板）](https://www.xp.cn/) 或 XAMPP，它们自带 PHP 和 MySQL，装好后在其界面里把 PHP 版本切到 8.2+ 即可。Composer 单独去 [getcomposer.org](https://getcomposer.org/download/) 下载安装。

## 安装步骤（从零开始）

打开命令行（Windows 可在项目文件夹地址栏输入 `cmd` 回车），依次执行：

```bash
# 1. 安装 PHP 依赖包（第一次运行会下载，需要几分钟）
composer install

# 2. 创建环境配置文件
#    Windows CMD 用：copy .env.example .env
#    Windows PowerShell / macOS / Linux 用：
cp .env.example .env

# 3. 生成应用密钥
php artisan key:generate
```

然后**用记事本等编辑器打开 `.env` 文件**，找到数据库这几行，填上你自己的信息：

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=xtcms        # 数据库名，需要先在 MySQL 里建好（如 CREATE DATABASE xtcms;）
DB_USERNAME=root         # 数据库用户名
DB_PASSWORD=你的密码      # 数据库密码
```

继续执行：

```bash
# 4. 建表并写入默认数据（会创建默认管理员和 2 篇示例文章）
php artisan migrate --seed

# 5. 启动开发服务器
php artisan serve
```

看到 `Server running on [http://127.0.0.1:8000]` 即启动成功。

## 访问

| 入口 | 地址                                                      | 说明                                                           |
| ---- | --------------------------------------------------------- | -------------------------------------------------------------- |
| 前台 | [http://localhost:8000/](http://localhost:8000/)           | 浏览文章、搜索                                                 |
| 后台 | [http://localhost:8000/admin](http://localhost:8000/admin) | 默认账号`admin` / `admin123`，**上线前务必修改密码** |

后台首次使用建议：先到「站点配置」改站名，再到「分类管理」建自己的分类，然后就可以写文章了。

## 从 V1.0 迁移数据（可选）

前提：V1 的旧库仍可连接（V1 需运行在 PHP 5 环境导出，或将旧库 SQL 导入一个 MySQL 8 临时库）。

```bash
php artisan xtcms:import-v1 \
    --host=127.0.0.1 \
    --database=xtcms \
    --username=root \
    --password=旧库密码 \
    --prefix=xt_
```

该命令会导入：

- **管理员**：用户名与 MD5 密码原样迁移，首次用旧密码登录时自动升级为 bcrypt；
- **分类**：兼容 `p_id/name/path` 与 `f_id/cname` 两种历史表结构；
- **文章**：`date_time`（Unix 时间戳）自动转换为 DATETIME，兼容 `keyword`/`keywrod` 两种拼写；
- **站点配置**：旧的无键名配置行按顺序映射到 `site_name / site_subtitle / footer_about / contact_email`。

## 目录结构（关键部分）

```
app/
├── Console/Commands/ImportFromV1.php   # V1 数据导入
├── Http/
│   ├── Controllers/
│   │   ├── FrontController.php         # 前台：首页/分类/详情/搜索
│   │   └── Admin/                      # 后台：登录/仪表盘/分类/文章/配置
│   └── Middleware/AdminAuth.php        # 后台登录校验
└── Models/                             # Admin / NewsClass / NewsContent / SiteConfig
database/
├── migrations/                         # admin / newsclass / newscontent / config
└── seeders/DatabaseSeeder.php          # 默认管理员与示例数据
resources/views/
├── layouts/front.blade.php             # 前台布局（响应式）
├── layouts/admin.blade.php             # 后台布局
├── front/                              # 前台页面
└── admin/                              # 后台页面（文章编辑含 TinyMCE 6）
```

## 数据表说明

沿用 V1 表名，便于平滑迁移：

| 表              | 说明                                                                              |
| --------------- | --------------------------------------------------------------------------------- |
| `admin`       | 管理员（V1 的`uid` 更名为自增 `id`）                                          |
| `newsclass`   | 分类，`parent_id` + `path` 实现无限级，`concat(path,'-',id)` 排序即树的先序 |
| `newscontent` | 文章，`date_time` 由 V1 的 int 时间戳改为 DATETIME                              |
| `config`      | 站点配置 key-value（V1 的`values` 列更名为 `value`，避开 MySQL 8 保留字）     |
| `tags`        | 文章标签（V2.0 新增，读者端内容组织，与 SEO 关键词无关）                      |
| `newstag`     | 文章-标签多对多关联表（V2.0 新增）                                            |

## 生产部署提示

- Nginx 站点根目录指向 `public/`，配置 `try_files $uri $uri/ /index.php?$query_string;`
- `APP_ENV=production`、`APP_DEBUG=false`，并执行 `php artisan config:cache route:cache view:cache`
- TinyMCE 粘贴图片已支持自动上传到 `storage/app/public/uploads`（首次部署执行 `php artisan storage:link`；Windows 下若符号链接创建失败，可用管理员命令 `mklink /J public\storage storage\app\public` 建目录联接）
- 定期备份数据库：`php artisan xtcms:backup`（自动保留最近 10 份于 `storage/app/backups`，可用 `--keep=N` 调整）；建议配合计划任务每日执行
- 后台入口建议配合 IP 白名单或 HTTPS

## 与 V1 的功能对照

| V1 功能                           | V2 状态                       |
| --------------------------------- | ----------------------------- |
| 前台首页（最新 6 条）             | ✅ 保留，分页样式升级         |
| 分类列表页（含子分类，每页 6 条） | ✅ 保留                       |
| 文章详情页 + 相关文章             | ✅ 保留                       |
| 无限级分类管理                    | ✅ 保留                       |
| 文章增删改查                      | ✅ 保留，编辑器升级 TinyMCE 6 |
| 站点配置                          | ✅ 保留，字段语义化           |
| 一键安装向导                      | ✅ 由`migrate --seed` 替代  |
| 后台登录（MD5）                   | ✅ 升级为 bcrypt，兼容旧密码  |
| 搜索表单（V1 未实现）             | ✅ 已实现                     |

## 安全须知

- 安装后**请立即修改默认管理员密码**（后台右上角「修改密码」），默认账号密码仅用于首次登录
- `APP_KEY` 参与加密与 Session 签名，**请勿复用他人公开过的密钥**，用 `php artisan key:generate` 生成
- 上传接口仅限后台登录用户，且限制图片格式、单文件 ≤ 5MB
- 站点配置中的「自定义 CSS / 头部代码」会**原样注入**前台页面，请仅填写自己可信的内容

## 常见问题（FAQ）

<details>
<summary><b>点开查看常见问题</b></summary>

**Q：`composer install` 报错或很慢？**
先切换国内镜像：`composer config -g repos.packagist composer https://mirrors.aliyun.com/composer/`，再重新安装。

**Q：`php artisan migrate` 报连接数据库失败（SQLSTATE[HY000] [2002]）？**
检查 `.env` 里的 `DB_HOST / DB_PORT / DB_USERNAME / DB_PASSWORD` 是否正确，MySQL 服务是否已启动，数据库名是否已创建。改完 `.env` 后重新执行命令即可。

**Q：页面提示 "419 Page Expired"？**
CSRF 令牌过期，刷新页面重试即可；若编辑页长时间搁置后图片上传报 419，刷新页面再试。

**Q：登录时提示尝试次数过多？**
登录接口限流为每分钟 5 次，稍等 1 分钟再试。

**Q：`php artisan serve` 报端口被占用？**
换一个端口启动：`php artisan serve --port=8080`。

**Q：如何修改管理员密码？**
登录后台 → 侧边栏「修改密码」。

</details>

## 参与贡献

欢迎提交 Issue 与 Pull Request：

1. Fork 本仓库并创建特性分支：`git checkout -b feature/your-feature`
2. 提交改动：`git commit -m "feat: 简要描述"`
3. 推送分支并发起 PR

提交前请确保：代码符合项目现有风格（Laravel Pint 可用 `vendor/bin/pint` 格式化）、不引入新的依赖冲突、涉及数据库变更时提供可回滚的迁移文件。

## 开源协议

本项目基于 [MIT License](LICENSE) 开源，你可以自由使用、修改和分发（包括商用），只需保留原版权声明。

V1.0 原始代码与数据结构为本项目重写提供了基础，在此向前辈作者致谢。
