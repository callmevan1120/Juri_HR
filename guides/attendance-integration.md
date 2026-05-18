# Integrasi Mesin Absensi

Dokumen ini menjelaskan endpoint inbound generik untuk mesin absensi eksternal seperti Solution, SBG, atau gateway custom yang bisa mengirim HTTP POST.

## Ringkasan

- Endpoint: `POST /api/integrations/attendance-events`
- Proteksi: API key integrasi + HMAC SHA-256 dengan timestamp pendek
- Rate limit: `attendance-integrations`
- Mapping karyawan: `employee_code` dicocokkan ke `users.nip`
- Idempotency: kombinasi `source` + `idempotency_key` hanya diproses sekali
- Status event: `accepted`, `processed`, atau `failed`
- Event yang gagal tetap disimpan untuk investigasi operasional

Endpoint ini cocok untuk integrasi mesin yang bisa push data ke server PasPapan. Jika mesin hanya menyediakan export file, gunakan gateway kecil di sisi customer untuk membaca export tersebut lalu mengirim payload standar ke endpoint ini.

## Konfigurasi

Isi secret integrasi di environment server:

Generate API key dan HMAC secret sendiri. Keduanya boleh custom, tetapi production wajib random dan tidak dibagikan ke user biasa:

```bash
php -r 'echo "ATTENDANCE_INTEGRATION_API_KEY=".bin2hex(random_bytes(32)).PHP_EOL; echo "ATTENDANCE_INTEGRATION_SECRET=".bin2hex(random_bytes(32)).PHP_EOL;'
```

```dotenv
ATTENDANCE_INTEGRATION_API_KEY=change-with-random-api-key
ATTENDANCE_INTEGRATION_SECRET=change-with-random-long-secret
ATTENDANCE_INTEGRATION_SIGNATURE_TOLERANCE_SECONDS=300
ATTENDANCE_INTEGRATION_ALLOWED_SOURCES=solution,sbg
```

Jangan pakai API key/secret yang sama dengan `APP_KEY`, token API user, atau secret enterprise license. Rotasi `ATTENDANCE_INTEGRATION_API_KEY` jika gateway vendor/customer berganti.
`ATTENDANCE_INTEGRATION_ALLOWED_SOURCES` opsional; biarkan kosong jika gateway belum distandarkan, atau isi daftar `source` yang diizinkan agar vendor lain tidak bisa mengirim event walaupun format payload benar.

## Header API Key dan HMAC

Client wajib mengirim:

```http
Content-Type: application/json
Accept: application/json
X-PasPapan-Api-Key: <integration api key>
X-PasPapan-Timestamp: 1778659200
X-PasPapan-Signature: sha256=<hmac>
```

Format signature:

```text
sha256=<hmac_sha256(timestamp + "." + raw_json_body, ATTENDANCE_INTEGRATION_SECRET)>
```

Server menolak request tanpa signature, timestamp non-numeric, timestamp kadaluarsa, atau body yang berubah setelah signature dibuat.
Server juga menolak request tanpa `X-PasPapan-Api-Key` atau API key yang tidak cocok dengan konfigurasi server.

## Payload

```json
{
  "source": "solution",
  "idempotency_key": "solution-device-a-20260513-000001",
  "employee_code": "EMP-001",
  "event_type": "clock_in",
  "occurred_at": "2026-05-13 08:03:00",
  "device_id": "machine-a",
  "latitude": -6.2,
  "longitude": 106.8,
  "payload": {
    "pin": "001",
    "raw_event_id": "000001"
  }
}
```

Field utama:

- `source`: nama sumber, misalnya `solution`, `sbg`, atau `gateway-customer-a`.
- `idempotency_key`: ID unik dari mesin/gateway. Replay dengan key sama tidak membuat attendance dobel.
- `employee_code`: kode karyawan yang harus sama dengan `NIP` user di PasPapan.
- `event_type`: menerima `check_in`, `check_out`, `clock_in`, `clock_out`, `in`, atau `out`.
- `occurred_at`: waktu event dari mesin dalam timezone aplikasi.
- `device_id`: opsional, berguna untuk audit mesin.
- `latitude` dan `longitude`: opsional jika mesin/gateway punya lokasi.
- `payload`: opsional untuk raw metadata non-sensitif.

## Contoh Curl

Local smoke test bisa memakai `APP_URL=http://127.0.0.1:8000`. Pastikan `php artisan serve` berjalan dan `.env` sudah memiliki `ATTENDANCE_INTEGRATION_API_KEY`, `ATTENDANCE_INTEGRATION_SECRET`, serta `ATTENDANCE_INTEGRATION_ALLOWED_SOURCES` yang memuat `local-curl` jika allowlist diaktifkan.

```bash
BODY='{"source":"local-curl","idempotency_key":"local-curl-'"$(date +%s)"'","employee_code":"EMP-001","event_type":"clock_in","occurred_at":"2026-05-13 08:03:00","device_id":"local-terminal"}'
TS="$(date +%s)"
SIG="sha256=$(printf '%s.%s' "$TS" "$BODY" | openssl dgst -sha256 -hmac "$ATTENDANCE_INTEGRATION_SECRET" -binary | xxd -p -c 256)"

curl -X POST "${APP_URL:-http://127.0.0.1:8000}/api/integrations/attendance-events" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-PasPapan-Api-Key: $ATTENDANCE_INTEGRATION_API_KEY" \
  -H "X-PasPapan-Timestamp: $TS" \
  -H "X-PasPapan-Signature: $SIG" \
  --data "$BODY"
```

## Response

Berhasil diproses:

```json
{
  "success": true,
  "event_id": 123,
  "status": "processed",
  "attendance_id": 456,
  "error_message": null
}
```

Karyawan tidak ditemukan:

```json
{
  "success": false,
  "event_id": 124,
  "status": "failed",
  "attendance_id": null,
  "error_message": "Employee code was not found."
}
```

## Catatan Untuk Solution/SBG

- Pastikan kode karyawan di mesin sama dengan `NIP` di PasPapan.
- Jika mesin punya nomor transaksi unik, pakai nomor itu sebagai `idempotency_key`.
- Jika mesin hanya punya urutan log, gabungkan `source`, `device_id`, tanggal, dan nomor urut supaya unik.
- Kirim event asli satu per satu. Jangan gabungkan banyak event dalam satu request untuk MVP ini.
- Jangan mengirim biometric template, password mesin, atau secret vendor ke field `payload`.

## Batasan MVP

- Endpoint ini adalah inbound push API, bukan connector langsung ke semua model mesin.
- Tidak ada polling bawaan ke mesin lokal; gunakan gateway jika mesin hanya tersedia di LAN.
- Conflict attendance mengikuti logic attendance existing: check-in pertama dan check-out pertama pada hari itu dipertahankan.
- Event yang jatuh pada cuti/izin approved akan ditandai `failed`.

## Test Coverage

Coverage utama ada di `tests/Feature/AttendanceIntegrationApiTest.php`:

- API key integrasi wajib valid.
- HMAC signature wajib valid.
- `employee_code` memetakan event ke user.
- `source` + `idempotency_key` mencegah replay duplicate.
- Kode karyawan tidak dikenal disimpan sebagai failed event.
