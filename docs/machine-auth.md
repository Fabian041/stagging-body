# Machine Authorization / RFID Machine Access

## Tujuan dan arsitektur

BELLA adalah source of truth untuk menentukan apakah sebuah RFID milik operator boleh menggunakan mesin tertentu.

```text
MIFARE Card
    ↓
USB RFID HID Reader
    ↓
Raspberry Pi
    ├── Wi-Fi → HTTPS API → BELLA
    └── Ethernet → Mitsubishi QJ71E71-100 → PLC Q-Series
```

BELLA **tidak pernah berkomunikasi langsung dengan PLC**. Raspberry Pi menangani pembacaan HID, cache SQLite, antrean event, dan komunikasi MC Protocol ke PLC.

Card UID adalah identifier, bukan password atau cryptographic credential. Keputusan akses tetap membutuhkan machine token, status card, status machine, dan permission.

## Data model

### `machines`

- `machine_code`: identifier unik Raspberry Pi, contoh `MC-001`.
- `line_id`: relasi nullable ke master `lines`; satu line dapat memiliki banyak machine.
- `machine_name`, `location`, dan `description`.
- `is_active`: menonaktifkan seluruh akses mesin tanpa menghapus master.
- Memiliki banyak Sanctum token, permission, access log, dan event.
- Response validate dan sync menyertakan objek `machine.line` (`id` dan `name`) jika machine sudah dipetakan.

### `rfid_cards`

- `user_id`: relasi ke `users` existing.
- `card_uid`: string unik maksimal 64 karakter. Tidak pernah dikonversi menjadi integer.
- `is_active`: card dapat dicabut tanpa menghapus data.
- Satu user dapat memiliki beberapa RFID card.

### `machine_permissions`

- Pasangan `user_id` dan `machine_id` bersifat unik.
- `is_allowed`: explicit allow atau deny.
- `valid_from` dan `valid_until`: periode berlaku opsional.

### `machine_access_logs`

- Satu record untuk setiap request authorization yang valid secara protokol.
- `transaction_id` adalah UUID unik dan sama dengan `request_id` dari Raspberry Pi.
- Menyimpan snapshot `card_uid` dan `machine_code` agar audit tetap terbaca setelah master berubah.
- `user_id` dan `machine_id` nullable dengan `ON DELETE SET NULL`.
- Hasil bisnis: `GRANTED` atau `DENIED`, disertai machine-readable reason code.

### `machine_events`

- Menyimpan event lifecycle sesudah authorization.
- `event_id` UUID unik untuk idempotensi outbox Raspberry Pi.
- Terhubung ke `machine_access_logs` melalui `machine_access_log_id` dan `transaction_id`.
- `metadata` disimpan sebagai JSON.

## Existing user

Employee menggunakan tabel dan model `User` existing: `id`, `npk`, dan `name`. BELLA saat ini tidak memiliki kolom status aktif user. Karena itu user dianggap valid selama record masih tersedia. Akses dapat dicabut melalui status RFID, status mesin, atau permission. Service otomatis akan menghormati `users.is_active` jika field tersebut ditambahkan pada masa mendatang.

## Machine authentication

Semua endpoint memakai Laravel Sanctum bearer token milik model `Machine` dengan ability:

```text
machine-auth
```

Middleware chain:

```text
api
→ auth:sanctum
→ machine.token
→ throttle:machine-auth
```

`machine.token` memastikan token owner adalah `Machine`, bukan human `User`, dan memeriksa ability `machine-auth`. Machine dari token selalu menjadi identitas yang dipercaya. `machine_code` request harus sama dengan machine pemilik token.

- Missing/invalid bearer token: HTTP 401.
- Token owner bukan Machine atau ability salah: HTTP 403.
- Token `MC-001` mengirim `machine_code=MC-002`: HTTP 403 dan attempt dicatat sebagai `INVALID_REQUEST`.
- Authorization denial yang valid secara bisnis: HTTP 200 dengan `authorized=false`.

Sanctum menyimpan hash SHA-256 token di `personal_access_tokens`. Plaintext hanya tersedia saat token dibuat.

## Provisioning dan rotation token

### Melalui UI

1. Login sebagai `admin` atau `super_admin`.
2. Buka **System → Machine Authorization**.
3. Buat atau edit machine.
4. Isi nama token pada bagian **Provisioning Token Raspberry Pi**.
5. Pilih opsi cabut token lama jika melakukan rotation.
6. Salin plaintext token yang muncul satu kali.

### Melalui Artisan

```bash
php artisan machine-auth:token MC-001 --name=raspberry-pi
```

Token dengan masa berlaku:

```bash
php artisan machine-auth:token MC-001 --name=raspberry-pi --expires="2027-08-28 23:59:59"
```

Rotation sekaligus mencabut semua token lama milik mesin:

```bash
php artisan machine-auth:token MC-001 --name=raspberry-pi --rotate
```

Simpan token pada konfigurasi rahasia Raspberry Pi dengan permission file terbatas, misalnya mode `600`. Jangan commit token, menaruhnya pada source code, atau mencetaknya ke application log.

## API

Base path:

```text
/api/v1/machine-auth
```

Gunakan HTTPS pada production.

### POST `/validate`

Memvalidasi card terhadap mesin pemilik bearer token.

```bash
curl -X POST "https://bella.example.com/api/v1/machine-auth/validate" \
  -H "Authorization: Bearer <machine-token>" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "request_id": "55ff5bb2-ec29-4e7c-a5d5-cd0d34ed2e5a",
    "card_uid": "1667815478",
    "machine_code": "MC-001"
  }'
```

`request_id` wajib UUID dan harus dibuat oleh Raspberry Pi sebelum request dikirim.

Contoh authorized:

```json
{
  "authorized": true,
  "reason": "AUTHORIZED",
  "transaction_id": "55ff5bb2-ec29-4e7c-a5d5-cd0d34ed2e5a",
  "employee": {
    "id": 25,
    "npk": "123456",
    "name": "Budi"
  },
  "machine": {
    "id": 1,
    "code": "MC-001",
    "name": "Machine 001"
  },
  "cache": {
    "allowed": true,
    "expires_at": "2026-08-29T01:00:00.000000Z"
  }
}
```

Contoh business denial tetap HTTP 200:

```json
{
  "authorized": false,
  "reason": "PERMISSION_DENIED",
  "transaction_id": "55ff5bb2-ec29-4e7c-a5d5-cd0d34ed2e5a",
  "employee": {
    "id": 25,
    "npk": "123456",
    "name": "Budi"
  },
  "machine": {
    "id": 1,
    "code": "MC-001",
    "name": "Machine 001"
  },
  "cache": {
    "allowed": false,
    "expires_at": "2026-08-28T02:00:00.000000Z"
  }
}
```

### POST `/events`

Menerima lifecycle event dari outbox Raspberry Pi.

```bash
curl -X POST "https://bella.example.com/api/v1/machine-auth/events" \
  -H "Authorization: Bearer <machine-token>" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "event_id": "62e066b1-2458-4216-823d-a74d6d1b04e0",
    "transaction_id": "55ff5bb2-ec29-4e7c-a5d5-cd0d34ed2e5a",
    "event_type": "ACCESS_GRANTED",
    "card_uid": "1667815478",
    "machine_code": "MC-001",
    "occurred_at": "2026-08-28T13:30:00Z",
    "metadata": {
      "source": "raspberry-pi"
    }
  }'
```

Event types:

```text
CARD_SCAN
ACCESS_GRANTED
ACCESS_DENIED
PLC_AUTHORIZED
PLC_FAILED
MACHINE_START
MACHINE_STOP
LOGOUT
```

Event baru menghasilkan HTTP 201. Retry event yang sama menghasilkan HTTP 200 dengan `duplicate=true` tanpa record baru.

### GET `/sync`

Mengambil permission cache hanya untuk machine pemilik token.

```bash
curl "https://bella.example.com/api/v1/machine-auth/sync" \
  -H "Authorization: Bearer <machine-token>" \
  -H "Accept: application/json"
```

Contoh response:

```json
{
  "machine": {
    "id": 1,
    "code": "MC-001",
    "name": "Machine 001",
    "active": true
  },
  "generated_at": "2026-08-28T01:00:00.000000Z",
  "expires_at": "2026-08-29T01:00:00.000000Z",
  "permissions": [
    {
      "card_uid": "1667815478",
      "employee_id": 25,
      "npk": "123456",
      "name": "Budi",
      "allowed": true,
      "active": true,
      "reason": "AUTHORIZED",
      "valid_from": null,
      "valid_until": null,
      "expires_at": "2026-08-29T01:00:00.000000Z"
    }
  ]
}
```

Permission mesin lain tidak pernah dikirim ke token ini.

## Authorization decision flow

Urutan evaluasi:

1. Cocokkan `machine_code` dengan machine pemilik token.
2. Pastikan machine aktif.
3. Cari RFID berdasarkan UID string yang telah dinormalisasi.
4. Pastikan RFID aktif.
5. Pastikan user terkait masih valid menurut model existing.
6. Cari permission unik user dan machine.
7. Periksa `is_allowed`.
8. Periksa `valid_from` dan `valid_until`.
9. Simpan access log untuk hasil GRANTED maupun DENIED.
10. Hitung expiry offline cache.

Reason codes authorization:

```text
AUTHORIZED
CARD_NOT_FOUND
CARD_INACTIVE
USER_INACTIVE
MACHINE_NOT_FOUND
MACHINE_INACTIVE
PERMISSION_NOT_FOUND
PERMISSION_DENIED
PERMISSION_NOT_YET_VALID
PERMISSION_EXPIRED
INVALID_REQUEST
```

Protocol/idempotency responses juga dapat menggunakan:

```text
INVALID_MACHINE_TOKEN
INVALID_MACHINE_TOKEN_ABILITY
TRANSACTION_NOT_FOUND
IDEMPOTENCY_CONFLICT
```

## Idempotency

### Validate

`request_id` menjadi `transaction_id` dan memiliki unique constraint.

- Retry `request_id` dengan UID dan machine yang sama mengembalikan keputusan asli.
- Retry tidak membuat duplicate `machine_access_logs`.
- Penggunaan UUID yang sama untuk UID atau machine berbeda menghasilkan HTTP 409 `IDEMPOTENCY_CONFLICT`.

Raspberry Pi harus menyimpan `request_id` bersama outbox request sampai response berhasil diterima.

### Events

`event_id` memiliki unique constraint.

- Retry logical event yang sama tidak membuat duplicate `machine_events`.
- Perbedaan format timestamp, presisi database, atau urutan key JSON metadata tidak menyebabkan conflict palsu.
- Penggunaan `event_id` yang sama untuk transaction, machine, event type, atau UID berbeda menghasilkan HTTP 409 `IDEMPOTENCY_CONFLICT`.

## Offline cache

Raspberry Pi tetap menjadi edge cache, bukan master database.

```text
Card scan
  ├── API tersedia → validate ke BELLA → simpan hasil dan expiry ke SQLite
  └── API offline   → gunakan SQLite hanya jika entry belum expired
```

- Cache allowed menggunakan TTL allowed.
- Cache denied menggunakan TTL denied yang lebih pendek.
- Cache allowed tidak pernah melewati `permission.valid_until`.
- Sync mempunyai TTL sendiri dan dapat dipendekkan oleh permission aktif yang lebih cepat kedaluwarsa.
- Cache expired, entry tidak ditemukan, atau data meragukan harus fail closed/deny.
- Event offline disimpan di outbox dan dikirim ulang menggunakan `event_id` yang sama.

## Environment configuration

```dotenv
MACHINE_AUTH_TOKEN_ABILITY=machine-auth
MACHINE_AUTH_ALLOWED_CACHE_TTL_HOURS=24
MACHINE_AUTH_DENIED_CACHE_TTL_MINUTES=60
MACHINE_AUTH_SYNC_CACHE_TTL_HOURS=24
MACHINE_AUTH_RATE_LIMIT_PER_MINUTE=60

# Hanya untuk seeder local/testing
MACHINE_AUTH_DEV_NPK=
MACHINE_AUTH_DEV_CARD_UID=1667815478
```

Setelah mengubah environment production:

```bash
php artisan config:clear
php artisan config:cache
```

## Admin UI

Halaman `/machine-authorization` hanya tersedia untuk role `admin` dan `super_admin` melalui gate `manage-machine-auth`.

Master line dikelola melalui `/dashboard/master/lines`. Line nonaktif tetap dipertahankan untuk histori, tetapi tidak dapat dipilih untuk machine baru. Machine lama yang belum dipetakan memiliki `line_id` kosong dan dapat dipetakan bertahap melalui form edit machine.

Fitur:

- assign/edit/activate/deactivate RFID;
- tambah/edit/activate/deactivate machine;
- membuat, merotasi, dan mencabut machine token;
- tambah/edit/hapus permission dan validity period;
- DataTables access log dengan filter machine, result, dan tanggal.

Master card dan machine tidak memiliki tombol hard delete; gunakan status nonaktif agar aman untuk audit.

## Development seed

Seeder tidak dijalankan otomatis dan hanya bekerja pada environment `local` atau `testing`.

```bash
php artisan db:seed --class=MachineAuthorizationDevelopmentSeeder
```

Seeder selalu menyiapkan `MC-001`. RFID dan permission dibuat hanya jika `MACHINE_AUTH_DEV_NPK` menunjuk user development yang sudah tersedia. Seeder tidak membuat atau menyimpan token.

## Deployment

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
```

Setelah machine dibuat, provision token dari UI atau Artisan lalu pasang token pada Raspberry Pi. Pastikan reverse proxy menggunakan HTTPS dan waktu Raspberry Pi tersinkronisasi melalui NTP.

Rollback migration terakhir bila deployment belum digunakan:

```bash
php artisan migrate:rollback --step=5
```

Jangan rollback setelah data production digunakan tanpa backup dan rencana migrasi data.

## Testing

Machine Authorization tests menggunakan SQLite in-memory dan tidak menyentuh database development:

```bash
php artisan test --filter=MachineAuthorizationApiTest
```

Full suite:

```bash
php artisan test
```
