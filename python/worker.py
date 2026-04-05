"""
Smart Farming - Worker System
Polling command pending dari API, eksekusi, update status pompa & log.
"""

import requests
import time
import logging
import threading

# ── Konfigurasi ──────────────────────────────────────────────
API_BASE_URL  = "http://localhost:8000/api"  # Ganti dengan IP server Laravel
API_KEY       = "smartfarm-secret-key-2024"         # Ganti dengan API key
POLL_INTERVAL = 5                            # Detik antar polling

HEADERS = {
    "Content-Type": "application/json",
    "X-API-Key": API_KEY,
}

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [WORKER] %(message)s",
    datefmt="%H:%M:%S",
)
logger = logging.getLogger("worker")

# State pompa per device (simulasi)
pump_states: dict[int, str] = {}  # { device_id: 'idle' | 'running' | 'stopped' }


# ── Ambil command pending ─────────────────────────────────────
def fetch_pending_commands() -> list:
    try:
        res = requests.get(
            f"{API_BASE_URL}/command/pending",
            headers=HEADERS,
            timeout=5,
        )
        if res.status_code == 200:
            return res.json()
        logger.warning(f"Fetch failed: {res.status_code}")
        return []
    except requests.exceptions.RequestException as e:
        logger.error(f"Connection error: {e}")
        return []


# ── Eksekusi command ──────────────────────────────────────────
def execute_command(command: dict) -> bool:
    device_id    = command["device_id"]
    command_id   = command["id"]
    command_type = command["command_type"]

    logger.info(f"Executing [{command_type}] for device_id={device_id} (cmd_id={command_id})")

    # Simulasi aksi fisik pompa
    if command_type == "start_pump":
        pump_states[device_id] = "running"
        new_status = "running"
    elif command_type == "stop_pump":
        pump_states[device_id] = "stopped"
        new_status = "stopped"
    else:
        logger.warning(f"Unknown command type: {command_type}")
        return False

    # Simulasi delay eksekusi (misal relay fisik butuh waktu)
    time.sleep(1)

    # Update status command → done
    _update_command_status(command_id, "done")

    # Update status pompa ke server
    _update_pump_status(device_id, new_status, command_id)

    logger.info(f"✓ Done: device_id={device_id} pump={new_status}")
    return True


# ── Update status command ─────────────────────────────────────
def _update_command_status(command_id: int, status: str):
    try:
        requests.patch(
            f"{API_BASE_URL}/command/{command_id}/done",
            json={"status": status},
            headers=HEADERS,
            timeout=5,
        )
    except requests.exceptions.RequestException as e:
        logger.error(f"Failed to update command status: {e}")


# ── Update status pompa ───────────────────────────────────────
def _update_pump_status(device_id: int, status: str, command_id: int = None):
    payload = {"device_id": device_id, "status": status}
    if command_id:
        payload["command_id"] = command_id
    try:
        requests.post(
            f"{API_BASE_URL}/status/update",
            json=payload,
            headers=HEADERS,
            timeout=5,
        )
    except requests.exceptions.RequestException as e:
        logger.error(f"Failed to update pump status: {e}")


# ── Loop polling ──────────────────────────────────────────────
def poll_loop():
    logger.info("Worker started. Polling every %ds...", POLL_INTERVAL)
    while True:
        commands = fetch_pending_commands()

        if commands:
            logger.info(f"Found {len(commands)} pending command(s).")
            for cmd in commands:
                # Eksekusi tiap command di thread terpisah agar tidak blocking
                t = threading.Thread(target=execute_command, args=(cmd,), daemon=True)
                t.start()
        else:
            logger.debug("No pending commands.")

        time.sleep(POLL_INTERVAL)


# ── Entry point ───────────────────────────────────────────────
if __name__ == "__main__":
    print("[SmartFarming Worker] Running. Ctrl+C to stop.\n")
    try:
        poll_loop()
    except KeyboardInterrupt:
        print("\n[SmartFarming Worker] Stopped.")