# Multi-Role Face Login Bundle

Bundle ini menambahkan Face Login untuk empat tipe akun:

- `users` / Super Administrator -> `user_face_templates`
- `employees` -> `employee_face_templates` (dipertahankan dari sistem sebelumnya)
- `supervisors` -> `supervisor_face_templates`
- `finances` -> `finance_face_templates`

## Endpoint login

Semua role memakai endpoint publik yang sama:

- `POST /api/face-login` (`multipart/form-data`, field `image`)

Laravel menggabungkan seluruh embedding aktif, meminta FastAPI melakukan identifikasi 1:N sekali, lalu mengembalikan guard yang cocok: `user`, `employee`, `supervisor`, atau `finance`.

Proteksi request tetap aktif: satu request verifikasi pada satu waktu dan cooldown default 3000 ms.

## Endpoint enrollment per role

### Administrator / users
- `GET /api/admin/face/status`
- `POST /api/admin/face/enroll`
- `POST /api/admin/face/verify`
- `DELETE /api/admin/face`

### Employee
- `GET /api/employee/face/status`
- `POST /api/employee/face/enroll`
- `POST /api/employee/face/verify`
- `DELETE /api/employee/face`

### Supervisor
- `GET /api/supervisor/face/status`
- `POST /api/supervisor/face/enroll`
- `POST /api/supervisor/face/verify`
- `DELETE /api/supervisor/face`

### Finance
- `GET /api/finance/face/status`
- `POST /api/finance/face/enroll`
- `POST /api/finance/face/verify`
- `DELETE /api/finance/face`

Semua endpoint enrollment berada di balik `auth:sanctum` + middleware role masing-masing.

## Database

Untuk database yang sudah berjalan, cukup gunakan migration Laravel:

```bash
php artisan migrate
php artisan optimize:clear
```

Migration baru:

`2026_08_12_180000_create_role_face_templates_tables.php`

Jika ingin menggunakan SQL manual, tersedia `database/sql/face_multirole_tables.sql`. Jangan menjalankan SQL patch dan migration baru secara terpisah pada database yang sama; SQL patch sudah mencatat migration agar tidak dibuat dua kali.

Untuk instalasi database baru, tersedia `dump-hr-multirole-face.sql` yang sudah memuat tiga tabel face template baru beserta record migration-nya.

## .env dipertahankan

Sesuai kebutuhan project, file `.env` pada Laravel dan Face API TIDAK dihapus dari bundle. Nilai yang sudah ada tetap dipertahankan. Laravel hanya disesuaikan pada:

```env
FACE_API_CONNECT_TIMEOUT=5
FACE_API_TIMEOUT=60
FACE_LOGIN_COOLDOWN_MS=3000
```

`FACE_API_URL`, `FACE_API_KEY`, database credential, `APP_KEY`, dan konfigurasi lain tetap berasal dari `.env` project yang Anda kirim.

Penting: jangan mengganti `APP_KEY` pada database yang sudah berisi face template, karena field `embedding` memakai cast `encrypted:array` Laravel.

## Frontend

Login desktop diperbarui menjadi layout dua panel dengan:

- Face Login sebagai mode utama.
- Camera-only; tidak ada email atau upload foto pada mode Face Login.
- Role Administrator / Employee / Supervisor / Finance ditentukan otomatis dari hasil pengenalan wajah.
- Password login tetap menjadi fallback.
- Layout mobile tetap satu kolom dan responsif.

`FaceEnrollmentCard` sekarang role-aware dan digunakan pada halaman profile Admin, Employee, Supervisor, dan Finance.

## Menjalankan service

Laravel:

```bash
php artisan optimize:clear
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000
```

Face API (gunakan host/port sesuai `.env` Laravel):

```bash
uvicorn app.main:app --host 0.0.0.0 --port 7999
```

Frontend:

```bash
npm install
npm run dev
```

## Pemeriksaan yang dilakukan

- PHP syntax check: 135 file, 0 syntax error.
- Python compile check Face API: sukses.
- JS/JSX parser check: 181 file, 0 syntax error.
- Relative frontend imports: 275, 0 missing path.
- `custom.css`: parse sukses.

Full Vite build tidak dijalankan karena dependency npm lengkap tidak tersedia pada environment pemeriksaan offline.
