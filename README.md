# Foto Gallery in Laravel

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/stekoe/foto-gallery-laravel/actions/workflows/deploy.yml/badge.svg" alt="Build Status"></a>
</p>

## Local Development

### Prerequisites

- PHP 8.4+
- Node.js
- Composer
- Docker (for the database)

### Setup

**1. Start the database**

```bash
docker compose up -d
```

**2. Install dependencies**

```bash
composer install
npm install
```

**3. Configure environment**

Create a `.env` file and set at minimum:

```env
APP_URL=http://localhost:8000
APP_KEY=                        # fill after next step
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=d03a89f3
DB_USERNAME=d03a89f3
DB_PASSWORD=password
APP_PUBLIC_GALLERY_TAG_ID=      # tag_id of the public tag in gallery_image_tags
```

Then generate the app key:

```bash
php artisan key:generate
```

**4. Run migrations and link storage**

```bash
php artisan migrate
php artisan storage:link
```

**5. Start the dev servers**

In two separate terminals:

```bash
# Terminal 1 — Laravel
php artisan serve

# Terminal 2 — Vite (CSS/JS hot reload)
npm run dev
```

The app is now available at **http://localhost:8000**.

### Loading images

Images are synced from Nextcloud. Add the relevant `NEXTCLOUD_*` credentials to `.env`, then trigger a sync via the admin panel at `/admin/sync`. Alternatively, import a copy of the production database to get existing image metadata without re-syncing.

