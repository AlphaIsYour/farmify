"""
Smart Farming - Device Simulator
3 device: Zone-A (Lahan Padi), Zone-B (Lahan Sayur), Zone-C (Lahan Buah)
Setiap device berjalan di thread terpisah dan mengirim data sensor ke REST API.
"""

import requests
import threading
import time
import random
import logging
from datetime import datetime

# ── Konfigurasi ──────────────────────────────────────────────
API_BASE_URL = "http://localhost:8000/api"   # Ganti dengan IP server Laravel
API_KEY      = "smartfarm-secret-key-2024"          # Ganti dengan API key dari tabel api_clients
INTERVAL     = 10                           # Detik antar pengiriman data

HEADERS = {
    "Content-Type": "application/json",
    "X-API-Key": API_KEY,
}

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(name)s] %(message)s",
    datefmt="%H:%M:%S",
)

# ── Profil tiap device ───────────────────────────────────────
DEVICES = [
    {
        "device_code": "ZONE-A",
        "zone_name":   "Lahan Padi",
        "moisture_range": (20, 85),   # Simulasi rentang kelembapan
        "temp_range":     (26, 34),
        "humidity_range": (60, 85),
    },
    {
        "device_code": "ZONE-B",
        "zone_name":   "Lahan Sayur",
        "moisture_range": (15, 75),
        "temp_range":     (24, 32),
        "humidity_range": (55, 80),
    },
    {
        "device_code": "ZONE-C",
        "zone_name":   "Lahan Buah",
        "moisture_range": (25, 90),
        "temp_range":     (27, 36),
        "humidity_range": (50, 75),
    },
]


class DeviceSimulator:
    def __init__(self, profile: dict):
        self.device_code    = profile["device_code"]
        self.zone_name      = profile["zone_name"]
        self.moisture_range = profile["moisture_range"]
        self.temp_range     = profile["temp_range"]
        self.humidity_range = profile["humidity_range"]
        self.logger         = logging.getLogger(self.device_code)

        # State internal — moisture turun perlahan jika pompa mati
        self._moisture   = random.uniform(*self.moisture_range)
        self._pump_on    = False
        self._running    = True

    # ── Generate sensor reading ──────────────────────────────
    def _read_sensors(self) -> dict:
        # Simulasi perubahan moisture secara bertahap
        if self._pump_on:
            self._moisture = min(self._moisture + random.uniform(1.5, 4.0), self.moisture_range[1])
        else:
            self._moisture = max(self._moisture - random.uniform(0.5, 2.5), self.moisture_range[0])

        return {
            "device_code":   self.device_code,
            "soil_moisture": round(self._moisture, 2),
            "temperature":   round(random.uniform(*self.temp_range), 2),
            "humidity":      round(random.uniform(*self.humidity_range), 2),
        }

    # ── Kirim data sensor ke API ─────────────────────────────
    def _send_data(self, payload: dict) -> bool:
        try:
            res = requests.post(
                f"{API_BASE_URL}/ingest",
                json=payload,
                headers=HEADERS,
                timeout=5,
            )
            if res.status_code == 201:
                self.logger.info(
                    f"✓ Sent | moisture={payload['soil_moisture']}% "
                    f"temp={payload['temperature']}°C "
                    f"hum={payload['humidity']}%"
                )
                return True
            else:
                self.logger.warning(f"✗ API error {res.status_code}: {res.text}")
                return False
        except requests.exceptions.RequestException as e:
            self.logger.error(f"✗ Connection failed: {e}")
            return False

    # ── Loop utama device ────────────────────────────────────
    def run(self):
        self.logger.info(f"Device started — {self.zone_name}")
        while self._running:
            payload = self._read_sensors()
            self._send_data(payload)
            time.sleep(INTERVAL)

    def stop(self):
        self._running = False
        self.logger.info("Device stopped.")

    def set_pump(self, state: bool):
        self._pump_on = state
        self.logger.info(f"Pump {'ON' if state else 'OFF'}")


# ── Entry point ──────────────────────────────────────────────
def main():
    simulators = [DeviceSimulator(p) for p in DEVICES]
    threads    = []

    for sim in simulators:
        t = threading.Thread(target=sim.run, daemon=True)
        t.start()
        threads.append(t)

    print("\n[SmartFarming] 3 device simulator running. Ctrl+C to stop.\n")
    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        print("\n[SmartFarming] Stopping all devices...")
        for sim in simulators:
            sim.stop()


if __name__ == "__main__":
    main()