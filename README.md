# PMB Pascasarjana - Backend API

Backend API untuk Sistem Penerimaan Mahasiswa Baru (PMB) Program Pascasarjana.

## Tech Stack

| Layer              | Teknologi                            |
| ------------------ | ------------------------------------ |
| **Framework**      | Laravel 12 (PHP 8.3+)                |
| **Database**       | PostgreSQL                           |
| **Authentication** | Laravel Sanctum                      |
| **Authorization**  | RBAC (Role-Based Access Control)     |
| **WhatsApp API**   | WAHA (Self-hosted WhatsApp HTTP API) |

## Requirements

- PHP 8.3+
- Composer
- PostgreSQL / MySQL
- (Optional) WAHA untuk notifikasi WhatsApp

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

# WAHA (Optional - untuk WhatsApp notification)
WAHA_API_URL=http://localhost:3000
WAHA_SESSION=default
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
│   ├── Controllers/Api/
│   │   ├── AuthController.php
│   │   ├── PublicController.php
│   │   ├── PendaftarController.php
│   │   ├── ProdiStafController.php
│   │   └── AdminController.php
│   ├── Middleware/
│   │   └── CheckRole.php
│   └── Requests/
│       ├── RegisterRequest.php
│       ├── LoginPendaftarRequest.php
│       └── ...
├── Models/
│   ├── User.php
│   ├── Pendaftar.php
│   ├── Prodi.php
│   ├── PeriodePendaftaran.php
│   ├── SesiUjian.php
│   ├── RuangUjian.php
│   ├── JadwalUjian.php
│   └── Dokumen.php
└── Services/
    ├── PendaftaranService.php
    ├── JadwalService.php
    ├── NotifikasiService.php
    ├── FileUploadService.php
    └── KelulusanService.php
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

## License

Proprietary - PT. GSP
