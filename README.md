# PMB Pascasarjana - Backend API

Backend API untuk Sistem Penerimaan Mahasiswa Baru (PMB) Program Pascasarjana.

## Tech Stack

| Layer              | Teknologi                          |
| ------------------ | ---------------------------------- |
| **Framework**      | Laravel 12 (PHP 8.3+)              |
| **Database**       | PostgreSQL                         |
| **Authentication** | Laravel Sanctum                    |
| **Authorization**  | RBAC (Role-Based Access Control)   |
| **WhatsApp API**   | GOWA (go-whatsapp-web-multidevice) |

## Requirements

- PHP 8.3+
- Composer
- PostgreSQL / MySQL
- (Optional) GOWA untuk notifikasi WhatsApp

## Installation

### 1. Clone & Install Dependencies

```bash
cd backend
composer install
```

### 2. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file:

```env
# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pmb_pascasarjana
DB_USERNAME=postgres
DB_PASSWORD=your_password

# GOWA (WhatsApp notification)
# Production: https://wa.pmb-uin.web.id
# Docs: https://github.com/aldinokemal/go-whatsapp-web-multidevice
GOWA_API_URL=https://wa.pmb-uin.web.id
GOWA_DEVICE_ID=
GOWA_BASIC_AUTH_USER=
GOWA_BASIC_AUTH_PASSWORD=
```

### 3. Database Migration & Seeding

```bash
php artisan migrate --seed
```

### 4. Run Development Server

```bash
php artisan serve
```

Server akan berjalan di `http://localhost:8000`

## Default Users

Setelah seeding, tersedia akun berikut:

| Role       | Username | Password | Keterangan          |
| ---------- | -------- | -------- | ------------------- |
| Admin      | admin    | password | Full access         |
| Staf Prodi | prodi1   | password | Akses prodi pertama |
| Staf Prodi | prodi2   | password | Akses prodi kedua   |
| ...        | ...      | ...      | Sesuai jumlah prodi |

## API Endpoints

### Public (No Auth)

| Method | Endpoint             | Deskripsi                              |
| ------ | -------------------- | -------------------------------------- |
| GET    | `/api/prodi`         | Get list program studi                 |
| POST   | `/api/register`      | Registrasi awal pendaftar              |
| POST   | `/api/cek-kelulusan` | Cek kelulusan (no. daftar + tgl lahir) |

### Authentication

| Method | Endpoint                | Deskripsi              |
| ------ | ----------------------- | ---------------------- |
| POST   | `/api/auth/login`       | Login pendaftar        |
| POST   | `/api/auth/admin/login` | Login admin/staf prodi |
| POST   | `/api/auth/logout`      | Logout                 |
| GET    | `/api/auth/me`          | Get current user       |

### Pendaftar (Role: pendaftar)

| Method | Endpoint                      | Deskripsi                 |
| ------ | ----------------------------- | ------------------------- |
| GET    | `/api/pendaftar/dashboard`    | Dashboard status & hasil  |
| GET    | `/api/pendaftar/biodata`      | Get biodata               |
| PUT    | `/api/pendaftar/biodata`      | Update biodata            |
| POST   | `/api/pendaftar/dokumen`      | Upload dokumen            |
| POST   | `/api/pendaftar/foto`         | Upload foto               |
| GET    | `/api/pendaftar/jadwal`       | Get jadwal ujian tersedia |
| POST   | `/api/pendaftar/pilih-jadwal` | Pilih jadwal ujian        |
| GET    | `/api/pendaftar/kartu`        | Get kartu pendaftaran     |
| GET    | `/api/pendaftar/hasil`        | Get hasil ujian           |

### Staf Prodi (Role: prodi)

| Method | Endpoint                           | Deskripsi            |
| ------ | ---------------------------------- | -------------------- |
| GET    | `/api/prodi/pendaftar`             | List pendaftar       |
| GET    | `/api/prodi/pendaftar/{id}`        | Detail pendaftar     |
| PUT    | `/api/prodi/verifikasi/{id}`       | Verifikasi dokumen   |
| GET    | `/api/prodi/form-nilai`            | Template form nilai  |
| POST   | `/api/prodi/upload-nilai`          | Upload batch nilai   |
| PUT    | `/api/prodi/pendaftar/{id}/nilai`  | Input nilai          |
| PUT    | `/api/prodi/pendaftar/{id}/status` | Set status kelulusan |
| POST   | `/api/prodi/notifikasi/{id}`       | Kirim notifikasi WA  |

### Admin (Role: admin)

| Method | Endpoint               | Deskripsi            |
| ------ | ---------------------- | -------------------- |
| GET    | `/api/admin/dashboard` | Dashboard statistik  |
| CRUD   | `/api/admin/prodi`     | Manajemen prodi      |
| CRUD   | `/api/admin/periode`   | Manajemen periode    |
| CRUD   | `/api/admin/sesi`      | Manajemen sesi ujian |
| CRUD   | `/api/admin/ruang`     | Manajemen ruang      |
| CRUD   | `/api/admin/jadwal`    | Manajemen jadwal     |
| CRUD   | `/api/admin/users`     | Manajemen users      |
| GET    | `/api/admin/pendaftar` | List semua pendaftar |

## API Response Format

### Success Response

```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": { ... }
}
```

### Error Response

```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "field_name": ["Error message"]
    }
}
```

### Pagination Response

```json
{
  "success": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php
│   │   └── Api/
│   │       ├── AdminController.php      # Admin CRUD operations
│   │       ├── AuthController.php       # Authentication (login/logout)
│   │       ├── PendaftarController.php  # Pendaftar dashboard & forms
│   │       ├── ProdiStafController.php  # Staf prodi operations
│   │       └── PublicController.php     # Public endpoints
│   └── Middleware/
│       └── CheckRole.php                # RBAC middleware
├── Models/
│   ├── Dokumen.php
│   ├── JadwalUjian.php
│   ├── Pendaftar.php
│   ├── PeriodePendaftaran.php
│   ├── Prodi.php
│   ├── RuangUjian.php
│   ├── SesiUjian.php
│   └── User.php
└── Services/
    ├── FileUploadService.php      # File/document upload (S3 support)
    ├── JadwalService.php          # Jadwal ujian management
    ├── KelulusanService.php       # Kelulusan processing
    ├── NotifikasiService.php      # WhatsApp notifications (GOWA)
    └── PendaftaranService.php     # Registration logic

routes/
└── api.php                        # API route definitions
```

## Testing

```bash
php artisan test
```

## Artisan Commands

```bash
# Database
php artisan migrate        # Run migrations
php artisan migrate:fresh  # Fresh migration
php artisan db:seed        # Run seeders

# Development
php artisan serve          # Start dev server
php artisan route:list     # List all routes
php artisan tinker         # Interactive console
```

## Docker Deployment

### Quick Start dengan Docker Compose

```bash
# Build dan jalankan semua services
docker compose up -d

# Dengan WhatsApp integration (GOWA)
docker compose --profile whatsapp up -d
```

### Build Docker Image

```bash
# Build image
docker build -t pmb-backend:latest .

# Run container
docker run -d \
  --name pmb-backend \
  -p 8000:80 \
  -e DB_HOST=host.docker.internal \
  -e DB_DATABASE=pmb_pascasarjana \
  -e DB_USERNAME=postgres \
  -e DB_PASSWORD=secret \
  pmb-backend:latest
```

### Docker Compose Services

| Service    | Port | Deskripsi                         |
| ---------- | ---- | --------------------------------- |
| `backend`  | 8000 | Laravel API                       |
| `postgres` | 5432 | PostgreSQL Database               |
| `redis`    | 6379 | Redis Cache                       |
| `gowa`     | 3000 | GOWA WhatsApp (profile: whatsapp) |

### Environment Variables untuk Docker

| Variable       | Default                     | Deskripsi           |
| -------------- | --------------------------- | ------------------- |
| `APP_ENV`      | `production`                | Environment mode    |
| `APP_DEBUG`    | `false`                     | Debug mode          |
| `DB_HOST`      | `postgres`                  | Database host       |
| `REDIS_HOST`   | `redis`                     | Redis host          |
| `AUTO_MIGRATE` | `false`                     | Auto-run migrations |
| `GOWA_API_URL` | `https://wa.pmb-uin.web.id` | GOWA API endpoint   |

### File Structure Docker

```
docker/
├── nginx.conf        # Nginx configuration
├── supervisord.conf  # Supervisor config (php-fpm, nginx, queue)
└── entrypoint.sh     # Container initialization script
```

## License

Proprietary - PT. Reagtive
