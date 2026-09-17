# Employee Lifecycle Audit & Implementation Report

## 1. Struktur Employee Saat Ini

- `App\Models\Employee` adalah model autentikasi tersendiri (`Authenticatable`) dan menggunakan Laravel Sanctum.
- Employee disimpan pada tabel `employees`, bukan pada tabel `users`.
- Kolom status account existing: `employees.is_active` (boolean).
- Employee terhubung langsung melalui `employee_id` ke attendance, leave, medical leave, overtime, business trip, payroll, payroll setting, performance, target, BPJS, balance, shift schedule, dan data operasional lain.
- Employee dapat menjadi anggota `teams` melalui tabel polymorphic `team_members`.
- Employee lifecycle terhubung melalui `employees -> employee_employments`.

## 2. Struktur User / Account Saat Ini

Project menggunakan account/model terpisah untuk role utama:

- `User` = Super Admin
- `Employee` = Employee
- `Finance` = Finance
- `Supervisor` = Supervisor

Tidak ditemukan foreign key langsung dari `employees` ke `users`. Authorization existing dilakukan melalui `auth:sanctum` + middleware berdasarkan instance model (`superadmin`, `employee`, `finance`, `supervisor`).

## 3. Status Employee yang Tersedia

### Account status

- `employees.is_active`

### Employment lifecycle status

Pada `employee_employments.current_status`:

- `ONBOARDING`
- `ACTIVE`
- `SUSPENDED`
- `NOTICE_PERIOD`
- `RESIGNED`
- `TERMINATED`

### Separation process status

Pada `employee_separations.process_status`:

- `SUBMITTED`
- `APPROVED`
- `REJECTED`
- `CANCELLED`
- `COMPLETED`

Offboarding tidak membutuhkan enum employee baru karena sudah dapat direpresentasikan oleh `process_status` serta `offboarding_started_at` / `offboarding_completed_at`.

## 4. Controller yang Relevan

Existing / digunakan kembali:

- `Admin/EmployeeController`
- `Api/EmployeeEmploymentController`
- `Api/EmployeeSeparationController`
- `Api/EmployeeLifecycleTaskController`
- `Api/EmployeeStatusHistoryController`
- `AuthController`
- `Finance/PayrollController`
- `AttendanceController`
- `Portal/AttendanceQrController`

Business logic lifecycle dipindahkan ke `App\Services\EmployeeLifecycleService` agar controller tidak menjadi tempat state transition dan transaction logic.

## 5. Model yang Relevan

- `Employee`
- `User`
- `EmployeeEmployment`
- `EmployeeSeparation`
- `EmployeeStatusHistorie`
- `EmployeeLifecycleTask`
- `Attendance`
- `Payroll`
- `EmployeePayrollSetting`
- `LeaveRequest`
- `MedicalLeave`
- `OvertimeRequest`
- `BusinessTrip`
- `Team`
- `TeamMember`

## 6. Tabel yang Relevan

Lifecycle:

- `employees`
- `employee_employments`
- `employee_status_histories`
- `employee_separations`
- `employee_lifecycle_tasks`

Operational history:

- `attendances`
- `leave_requests`
- `medical_leaves`
- `overtime_requests`
- `business_trips`
- `employee_payroll_settings`
- `payrolls`
- `payroll_corrections`
- `employee_targets`
- `employee_performances`
- `employee_shift_schedules`
- `team_members`
- dan tabel employee-related lain yang sudah ada.

## 7. Masalah yang Ditemukan

1. Lifecycle controller sudah ada tetapi sebelumnya tidak diregistrasikan pada `routes/api.php`.
2. Separation sebelumnya dapat di-approve, reject, start offboarding, atau complete berulang tanpa validasi state.
3. Lifecycle update sebelumnya tidak memakai transaction dan row locking sehingga rawan inconsistent state/race condition.
4. Employee yang `is_active = false` sebelumnya masih dapat lolos password login employee jika password benar.
5. `EmployeeMiddleware` sebelumnya hanya memeriksa tipe account, bukan `is_active`.
6. Penyelesaian separation sebelumnya tidak mengubah `employee_employments.current_status`, tidak menutup employment, tidak menonaktifkan employee, dan tidak mencabut token.
7. Status history sebelumnya belum otomatis ditulis saat lifecycle transition.
8. Employee baru dari API sebelumnya default aktif sebelum onboarding selesai.
9. Hard delete employee dapat menghilangkan atau memutus histori operasional.
10. Payroll generation sebelumnya hanya melihat `employee_payroll_settings.aktif` dan tidak mempertimbangkan periode employment, sehingga payroll periode setelah employee keluar berpotensi tetap diproses.
11. Lifecycle tasks sebelumnya tidak memastikan OFFBOARDING task terkait dengan separation/employment yang sama.
12. `completed_at`/`completed_by` lifecycle task dapat menjadi stale jika status task diubah kembali dari `COMPLETED`.

## 8. Kekurangan Schema

Schema lifecycle inti sudah cukup untuk onboarding, resignation, termination, offboarding, dan audit status.

Namun tidak ditemukan struktur formal untuk:

- contract history / kontrak kerja,
- position / jabatan,
- department,
- supervisor assignment pada employment,
- asset inventory/asset return,
- onboarding document repository.

Karena project tidak menyediakan sumber data yang valid untuk hal tersebut, implementasi tidak membuat tabel/kolom baru secara spekulatif.

## 9. Kekurangan Controller / Logic Sebelum Implementasi

- Tidak ada orchestration service lifecycle.
- Tidak ada transaction atomic untuk perubahan lintas employee/employment/separation/history/token.
- Tidak ada guard transition.
- Tidak ada duplicate separation prevention.
- Tidak ada pending-task check sebelum onboarding/offboarding selesai.
- Tidak ada integrasi final lifecycle dengan account access dan payroll eligibility.

## 10. Kekurangan Routes Sebelum Implementasi

Tidak ditemukan route untuk controller lifecycle existing. Implementasi menambahkan route pada area role existing:

- Admin: onboarding, employment detail, separation management, termination, offboarding, lifecycle task, status history.
- Employee: submit dan melihat resignation sendiri.

## 11. Desain Lifecycle yang Diimplementasikan

### Onboarding

`Employee(inactive) -> ONBOARDING -> ACTIVE`

- Employee dibuat nonaktif melalui Admin Employee API.
- Admin memulai onboarding dengan `start_date`.
- Employment `ONBOARDING` dan status history dibuat dalam satu transaction.
- Onboarding tidak dapat selesai jika ada task ONBOARDING yang masih `PENDING` / `IN_PROGRESS`.
- Onboarding completion mengubah employment menjadi `ACTIVE` dan `employees.is_active = true`.
- Re-onboarding employee yang sudah memiliki employment history ditolak.

### Resignation

`ACTIVE -> SUBMITTED -> APPROVED / NOTICE_PERIOD -> OFFBOARDING -> RESIGNED`

- Employee hanya dapat mengajukan resignation untuk employment `ACTIVE`.
- Duplicate open separation ditolak.
- Approval oleh Super Admin mengubah employment menjadi `NOTICE_PERIOD`.
- Employee tetap aktif selama notice/offboarding sampai separation selesai.
- Completion mengubah status menjadi `RESIGNED`, mengisi `end_date`, menonaktifkan account, dan mencabut Sanctum token.

### Termination

`ACTIVE/SUSPENDED -> SUBMITTED -> APPROVED -> OFFBOARDING -> TERMINATED`

- Hanya Super Admin yang dapat membuat termination melalui route admin.
- Termination terhadap employee nonaktif/final ditolak.
- Mengikuti mekanisme approval existing pada `employee_separations` (tidak auto-approve).
- Completion mengubah status menjadi `TERMINATED`, mengisi `end_date`, menonaktifkan account, dan mencabut token.

### Offboarding

- Hanya separation `APPROVED` yang dapat memulai offboarding.
- Offboarding tidak dapat dimulai dua kali.
- Separation tidak dapat selesai sebelum offboarding dimulai.
- Separation tidak dapat selesai sebelum `effective_date`.
- Jika OFFBOARDING task tersedia, seluruh task harus `COMPLETED` atau `SKIPPED` sebelum completion.

## 12. Tabel / Kolom Baru

Tidak ada tabel atau kolom baru yang ditambahkan oleh implementasi ini karena project sudah memiliki migration:

- `2026_08_18_092702_create_employee_employments_table.php`
- `2026_08_18_093630_create_employee_status_histories_table.php`
- `2026_08_18_093956_create_employee_separations_table.php`
- `2026_08_18_094306_create_employee_lifecycle_tasks_table.php`

Catatan: tabel-tabel tersebut belum ditemukan pada file `dump-hr-multirole-face.sql`, sehingga migration existing tersebut harus dijalankan pada database target.

## 13. API Endpoint yang Ditambahkan

| Method | Endpoint | Role | Purpose |
|---|---|---|---|
| GET | `/api/admin/employee-employments` | Super Admin | Daftar employment |
| GET | `/api/admin/employee-employments/{employeeEmployment}` | Super Admin | Detail employment + history |
| POST | `/api/admin/employees/{employee}/onboarding` | Super Admin | Mulai onboarding |
| POST | `/api/admin/employee-employments/{employeeEmployment}/onboarding/complete` | Super Admin | Selesaikan onboarding |
| GET | `/api/employee/resignation` | Employee | Melihat resignation terakhir sendiri |
| POST | `/api/employee/resignation` | Employee | Mengajukan resignation |
| GET | `/api/admin/employee-separations` | Super Admin | Daftar separation |
| GET | `/api/admin/employee-separations/{employeeSeparation}` | Super Admin | Detail separation |
| POST | `/api/admin/employees/{employee}/termination` | Super Admin | Mengajukan termination |
| POST | `/api/admin/employee-separations/{employeeSeparation}/approve` | Super Admin | Approve resignation/termination |
| POST | `/api/admin/employee-separations/{employeeSeparation}/reject` | Super Admin | Reject separation |
| POST | `/api/admin/employee-separations/{employeeSeparation}/offboarding/start` | Super Admin | Mulai offboarding |
| POST | `/api/admin/employee-separations/{employeeSeparation}/offboarding/complete` | Super Admin | Selesaikan offboarding + separation |
| GET/POST | `/api/admin/employee-lifecycle-tasks` | Super Admin | List/create lifecycle task |
| GET/PUT | `/api/admin/employee-lifecycle-tasks/{employeeLifecycleTask}` | Super Admin | Detail/update lifecycle task |
| GET | `/api/admin/employee-status-histories` | Super Admin | List status history |
| GET | `/api/admin/employee-status-histories/{employeeStatusHistory}` | Super Admin | Detail status history |

## 14. Business Rules / State Transition

- Employee dengan employment history tidak dapat onboarding ulang.
- `ONBOARDING -> ACTIVE` hanya melalui onboarding completion.
- Employee nonaktif tidak dapat mengakses employee routes.
- Employee nonaktif tidak dapat password login.
- Resignation hanya dari `ACTIVE`.
- Termination hanya dari `ACTIVE` atau `SUSPENDED`.
- Satu employment tidak boleh memiliki lebih dari satu separation berstatus `SUBMITTED` / `APPROVED`.
- Separation hanya dapat di-approve/reject dari `SUBMITTED`.
- Resignation approval menghasilkan `NOTICE_PERIOD`.
- Offboarding hanya dari separation `APPROVED`.
- Offboarding completion hanya dapat dilakukan sekali.
- Final resignation menghasilkan `RESIGNED + is_active=false`.
- Final termination menghasilkan `TERMINATED + is_active=false`.
- Token employee dicabut saat onboarding dimulai dan saat separation selesai.
- Payroll hanya eligible untuk bulan yang overlap dengan periode employment; legacy employee tanpa employment tetap didukung selama masih `is_active=true`.
- Hard delete ditolak jika employee sudah memiliki historical/operational data.

## 15. Validation Penting

### Onboarding

- `start_date`: required date.
- `end_date`: nullable, tidak boleh sebelum `start_date`.
- Employee tidak boleh sudah memiliki employment history.
- Completion tidak boleh sebelum `start_date`.
- Seluruh onboarding tasks harus selesai/skip.

### Resignation

- Employee harus aktif.
- Employment harus `ACTIVE`.
- `reason` wajib, max 500.
- `last_working_date` wajib dan tidak boleh sebelum hari pengajuan.
- `effective_date` wajib dan tidak boleh sebelum `last_working_date`.
- Tidak boleh ada separation lain yang masih open.

### Termination

- Employee harus aktif.
- Employment harus `ACTIVE` / `SUSPENDED`.
- `reason` wajib.
- `effective_date` wajib dan tidak boleh sebelum hari pengajuan.
- `last_working_date`, jika diberikan, tidak boleh setelah `effective_date`.
- Tidak boleh ada separation lain yang masih open.

### Offboarding

- Separation harus `APPROVED`.
- Tidak boleh start/complete dua kali.
- `effective_date` harus tersedia dan sudah tercapai pada saat completion.
- Semua OFFBOARDING tasks harus selesai/skip.

## 16. Risiko terhadap Fitur Existing dan Mitigasi

### Authentication

Mitigasi: `AuthController` kini mensyaratkan `employees.is_active=true` untuk password login. Face login existing sudah menggunakan `FaceIdentityManager::isAccountActive()`, yang membaca `is_active`.

### Attendance / Leave / Overtime / Employee Portal

Mitigasi: seluruh endpoint employee berada di `EmployeeMiddleware`, yang sekarang menolak employee nonaktif. Dengan demikian akses operasional berhenti setelah final separation.

### Payroll

Mitigasi: payroll generation menggunakan overlap periode employment sehingga employee yang sudah keluar masih dapat menerima final payroll pada bulan employment terakhir, tetapi tidak ikut payroll pada bulan setelah `end_date`.

### Historical data

Mitigasi: hard delete employee ditolak ketika ditemukan data historis atau operasional; lifecycle tables sendiri menggunakan `restrictOnDelete`.

## 17. NEEDS DECISION

### A. Existing / legacy employees belum memiliki `employee_employments`

Dump MySQL berisi employee existing, tetapi tidak berisi tabel/data lifecycle. Start date asli mereka tidak dapat ditentukan dari code/schema.

Pilihan:

1. Isi employment existing secara manual menggunakan tanggal mulai kerja yang benar dari HR (direkomendasikan).
2. Buat import/backfill dari sumber HR lain jika tersedia.
3. Jangan menggunakan `employees.created_at` sebagai start date tanpa keputusan bisnis, karena itu hanya timestamp record database dan belum tentu tanggal mulai kerja.

### B. Contract, position, department, dan supervisor history

Belum ada schema formal pada employment. Perlu keputusan apakah data tersebut:

- hanya snapshot saat onboarding,
- harus memiliki history,
- atau sudah dikelola oleh sistem lain.

Jangan menambah kolom sebelum source-of-truth ditentukan.

### C. Asset return

Tidak ditemukan asset management table. Offboarding saat ini dapat merekam task generik seperti "Return Laptop" tetapi belum dapat memverifikasi asset inventory secara relational.

## 18. File yang Diubah

- `app/Services/EmployeeLifecycleService.php` (baru)
- `app/Http/Controllers/Admin/EmployeeController.php`
- `app/Http/Controllers/Api/EmployeeEmploymentController.php`
- `app/Http/Controllers/Api/EmployeeSeparationController.php`
- `app/Http/Controllers/Api/EmployeeLifecycleTaskController.php`
- `app/Http/Controllers/Api/EmployeeStatusHistoryController.php`
- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/Finance/PayrollController.php`
- `app/Http/Middleware/EmployeeMiddleware.php`
- `app/Models/Employee.php`
- `app/Models/EmployeeEmployment.php`
- `app/Models/EmployeeSeparation.php`
- `app/Models/EmployeeStatusHistorie.php`
- `app/Models/EmployeeLifecycleTask.php`
- `routes/api.php`
- `tests/Feature/EmployeeLifecycleTest.php` (baru)
- `EMPLOYEE_LIFECYCLE_AUDIT.md` (baru)

## 19. Test Result

### Static PHP syntax validation

- Seluruh file PHP pada `app`, `routes`, `tests`, dan `database/migrations` telah diperiksa menggunakan `php -l`.
- Hasil: tidak ditemukan syntax error.

### Migration / route:list / PHPUnit

Belum dapat dieksekusi di sandbox karena ZIP tidak menyertakan directory `vendor/` dan executable Composer tidak tersedia pada environment sandbox.

Feature test yang disiapkan:

- lifecycle route registration,
- onboarding success,
- duplicate onboarding failure,
- resignation -> approval -> offboarding -> resigned,
- termination -> approval -> offboarding -> terminated,
- duplicate termination failure,
- inactive employee blocked by middleware.

Jalankan pada environment project:

```bash
composer install
php artisan migrate
php artisan route:list --path=api
php artisan test --filter=EmployeeLifecycleTest
```

