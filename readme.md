# Farmify — Smart Farming System

Sistem monitoring dan kontrol irigasi terdistribusi berbasis REST API. Memantau kelembapan tanah secara real-time dan mengontrol pompa air secara manual maupun otomatis.

---

## Arsitektur Sistem

```
[Python Device Simulator] ──POST /api/ingest──▶ [Laravel REST API]
                                                        │
                                                   [MySQL DB]
                                                        │
[Python Worker] ◀──GET /api/command/pending────────────┘
      │
      └──POST /api/status/update──▶ [Laravel REST API]
                                          │
                                    [Web Dashboard]
```

---

## Struktur Folder

```
farmify/
├── app/                        # Laravel application
│   └── Http/
│       ├── Controllers/
│       │   ├── Api/            # API controllers (device, command, status, dashboard)
│       │   └── Web/            # Web dashboard controllers
│       └── Middleware/
│           └── ApiKeyMiddleware.php
├── database/migrations/        # Database migrations
├── resources/views/dashboard/  # Blade views
├── routes/
│   ├── api.php                 # API routes
│   └── web.php                 # Web routes
├── public/
│   ├── css/dashboard.css
│   └── js/dashboard.js
├── python/
│   ├── device_simulator.py     # Simulator 3 device sensor
│   ├── worker.py               # Worker polling command
│   └── requirements.txt
├── Dockerfile
└── docker-compose.yml
```

---

## Prasyarat

- Docker & Docker Desktop
- Python 3.8+
- pip

---

## Instalasi & Menjalankan

### 1. Clone repository

```bash
git clone https://github.com/username/farmify.git
cd farmify
```

### 2. Jalankan Docker

```bash
docker compose up -d --build
```

Tunggu hingga semua container running:

```bash
docker compose ps
```

| Container   | Port                  |
| ----------- | --------------------- |
| farmify_app | http://localhost:8000 |
| farmify_db  | localhost:3307        |
| farmify_pma | http://localhost:8080 |

### 3. Migrasi database

```bash
docker exec -it farmify_app php artisan migrate
```

### 4. Buat API key

```bash
docker exec -it farmify_app php artisan tinker
```

```php
\App\Models\ApiClient::create([
    'name'      => 'Python Device Client',
    'api_key'   => 'smartfarm-secret-key-2024',
    'is_active' => 1,
]);
exit
```

### 5. Install dependensi Python

```bash
cd python
pip install -r requirements.txt
```

### 6. Konfigurasi Python

Edit `device_simulator.py` dan `worker.py`, sesuaikan:

```python
API_BASE_URL = "http://localhost:8000/api"
API_KEY      = "smartfarm-secret-key-2024"
```

### 7. Jalankan Device Simulator

Buka terminal baru:

```bash
cd python
python device_simulator.py
```

Output normal:

```
13:25:49 [ZONE-A] ✓ Sent | moisture=55.28% temp=26.57°C hum=78.12%
13:25:49 [ZONE-B] ✓ Sent | moisture=70.68% temp=29.84°C hum=55.5%
13:25:49 [ZONE-C] ✓ Sent | moisture=52.5% temp=33.34°C hum=56.18%
```

### 8. Jalankan Worker

Buka terminal baru:

```bash
cd python
python worker.py
```

Output normal:

```
13:29:52 [WORKER] Found 2 pending command(s).
13:29:52 [WORKER] Executing [start_pump] for device_id=1 (cmd_id=1)
13:29:57 [WORKER] ✓ Done: device_id=1 pump=running
```

### 9. Akses Dashboard

Buka browser: **http://localhost:8000/dashboard**

---

## API Endpoints

Semua endpoint memerlukan header:

```
X-API-Key: <api_key>
Content-Type: application/json
```

| Method | Endpoint                 | Deskripsi                    |
| ------ | ------------------------ | ---------------------------- |
| POST   | `/api/ingest`            | Device kirim data sensor     |
| GET    | `/api/devices`           | List semua device            |
| POST   | `/api/command/send`      | Kirim perintah pompa         |
| GET    | `/api/command/pending`   | Worker ambil command pending |
| PATCH  | `/api/command/{id}/done` | Worker update status command |
| POST   | `/api/status/update`     | Worker update status pompa   |
| GET    | `/api/dashboard/data`    | Data sensor untuk dashboard  |
| GET    | `/api/dashboard/log`     | Log aktivitas                |

### Contoh request — kirim data sensor

```bash
curl -X POST http://localhost:8000/api/ingest \
  -H "X-API-Key: smartfarm-secret-key-2024" \
  -H "Content-Type: application/json" \
  -d '{"device_code":"ZONE-A","soil_moisture":45.2,"temperature":28.5,"humidity":72.0}'
```

---

## Device Simulator

Tiga device zona lahan berjalan paralel via threading:

| Device | Zona        | Rentang Moisture |
| ------ | ----------- | ---------------- |
| ZONE-A | Lahan Padi  | 20–85%           |
| ZONE-B | Lahan Sayur | 15–75%           |
| ZONE-C | Lahan Buah  | 25–90%           |

Moisture turun secara bertahap saat pompa mati, naik saat pompa nyala.

---

## Auto Irrigation

Sistem otomatis membuat command `start_pump` ketika `soil_moisture < 30%`. Threshold dapat diubah di **Dashboard → Settings → Irrigation Threshold**.

---

## Environment Variables

| Variable             | Default | Deskripsi                          |
| -------------------- | ------- | ---------------------------------- |
| `THRESHOLD_MOISTURE` | 30      | Trigger auto-irrigation (%)        |
| `THRESHOLD_STOP`     | 70      | Stop irrigation (%)                |
| `POLL_INTERVAL`      | 5       | Interval worker polling (detik)    |
| `DEVICE_TIMEOUT`     | 60      | Timeout device offline (detik)     |
| `INGEST_INTERVAL`    | 10      | Interval kirim data sensor (detik) |

---

## Database

| Tabel          | Isi                         |
| -------------- | --------------------------- |
| `api_clients`  | API key management          |
| `devices`      | Daftar device/zona          |
| `sensor_data`  | Rekaman data sensor         |
| `commands`     | Antrian dan histori command |
| `pump_status`  | Status pompa real-time      |
| `activity_log` | Log seluruh aktivitas       |

---

## Troubleshooting

**API 404** — Pastikan `routes/api.php` terdaftar di `bootstrap/app.php`.

**Middleware error** — Pastikan `ApiKeyMiddleware` terdaftar di `bootstrap/app.php`:

```php
$middleware->alias(['auth.apikey' => \App\Http\Middleware\ApiKeyMiddleware::class]);
```

**Worker timeout** — Pastikan container running (`docker compose ps`) dan API key benar.

**migrate:fresh error** — Cek duplikasi migration file yang membuat tabel sama.

---

## Tech Stack

| Layer            | Teknologi                     |
| ---------------- | ----------------------------- |
| Backend API      | Laravel 13, PHP 8.4           |
| Database         | MySQL 8.0                     |
| Frontend         | Blade, Chart.js, Remixicon    |
| Device Simulator | Python 3, requests, threading |
| Worker           | Python 3, requests            |
| Infrastructure   | Docker, Apache                |
