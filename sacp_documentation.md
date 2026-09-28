# SACP Platform Documentation

## Overview

SACP (Smart Aquarium Control Panel) is a Laravel dashboard for account-owned aquarium telemetry and actuator control. Each user owns one aquarium. The signed-in account determines the aquarium used by dashboard requests; callers cannot choose another owner's database aquarium ID.

## Implemented Features

- Registration creates an account and its aquarium. Migrations backfill existing accounts and assign each aquarium a unique 20-digit SAIN (Smart Aquarium Identification Number).
- SAIN is shown on the dashboard grouped as four groups of five digits. API requests use the raw 20 digits without separators.
- Telemetry is stored by aquarium. The dashboard fetches the latest reading and up to 50 historical readings.
- Dashboard actuator requests enqueue feeder, refill-pump, and drain-pump commands for that user's aquarium.
- ESP32 pairing uses an 8-digit, five-minute, single-use OTP. The ESP32-generated device key is stored as a server-side SHA-256 hash after the owner confirms the OTP.
- The dashboard includes dark/light themes, a local profile photo, account logout, and log out all devices.
- A public Terms and Conditions page identifies Victor Technology and links to WhatsApp support.

## New User Setup

1. Create an account on the sign-up page and log in.
2. Open the dashboard and note the aquarium's SAIN in Settings. The display separates it for readability; remove separators when configuring the ESP32.
3. Configure the ESP32 with Wi-Fi and the SACP server's HTTPS base URL.
4. During local device setup, have the ESP32 generate its device key and OTP. Save the device key in ESP32 NVS/Preferences; do not commit it to firmware source or print it during normal operation.
5. The ESP32 sends its SAIN, OTP, and device key to the pairing-start endpoint below.
6. Within five minutes, the aquarium owner enters the displayed OTP in Dashboard → Settings → Aquarium device and selects Pair.
7. After successful pairing, the ESP32 uses its device key and SAIN for telemetry, command polling, and command acknowledgements.

A new pairing replaces any pending pairing challenge for that aquarium. Confirming a new pairing replaces the previous device key.

## ESP32 Key and OTP

Generate 24 cryptographically random bytes on the ESP32 and encode them as unpadded Base64URL. This gives a 32-character key with 192 bits of randomness. Generate the OTP as exactly eight decimal digits, including leading zeroes, using the ESP32 hardware random generator. The pairing challenge expires five minutes after it is started.

The ESP32 retains the plaintext device key. The platform stores only its SHA-256 hash. SAIN is an identifier, not an authentication secret.

The README includes Arduino-oriented example functions for generating a 32-character Base64URL key and an eight-digit OTP. Use the raw SAIN in network requests.

## Pairing Request

Start pairing with:

`POST /api/device/pairing/start`

Content type: `application/json`

Example body:

```json
{
  "sain": "12345678901234567890",
  "otp": "04827316",
  "device_key": "0123456789abcdefghijklmnopqrstuv"
}
```

The API validates the SAIN, OTP, and key formats, stores hashes for the pending challenge, and responds with HTTP `201` and a `waiting_for_user` status. Do not log the OTP or key in production firmware.

The signed-in dashboard confirms pairing with:

`POST /api/aquarium/pair-device`

The browser sends the OTP as JSON and includes its Laravel session and CSRF token. A valid, unexpired OTP belonging to that account's aquarium is consumed once; the server then activates the associated device-key hash. Invalid, expired, already-used, or other-account codes are rejected.

## Device Authentication

After pairing, send these headers on every device API request:

```text
Authorization: Bearer <32-character-device-key>
X-Aquarium-SAIN: <raw-20-digit-sain>
Accept: application/json
Content-Type: application/json
```

Use HTTPS outside local development. The server must match both the SAIN and the SHA-256 hash of the bearer key before accepting a device request.

## Telemetry

Send readings to:

`POST /api/device/telemetry`

Example:

```json
{
  "temperature": 25.3,
  "ph": 7.4,
  "turbidity": 3.8,
  "water_level": 90
}
```

Current validation limits:

- `temperature`: required numeric value from -10 to 100.
- `ph`: required numeric value from 0 to 14.
- `turbidity`: required numeric value greater than or equal to 0.
- `water_level`: optional numeric value from 0 to 100.

Successful ingestion returns HTTP `201`. Dashboard reads use the authenticated user's aquarium and return its SAIN, latest reading, and recent history. Do not send another user's SAIN or rely on a browser-supplied aquarium database ID.

## Actuator Commands

The dashboard queues commands against the signed-in user's aquarium. Supported commands are:

- `feeder`
- `pump-refill`
- `pump-drain`

The ESP32 polls:

`GET /api/device/commands`

The response contains up to ten pending commands for the authenticated aquarium. After executing each command, acknowledge it with:

`POST /api/device/commands/{id}/ack`

Success body:

```json
{"status":"completed"}
```

Failure body:

```json
{"status":"failed","result":"short diagnostic message"}
```

Acknowledgements are scoped to the authenticated aquarium; a device cannot acknowledge another aquarium's command.

## Data Ownership and Storage

The schema contains:

- `aquariums`: owner relationship, internal ID, 20-digit SAIN, and device-key hash.
- `aquarium_telemetry`: numeric readings keyed to an aquarium.
- `aquarium_actuator_commands`: queued/dispatched/completed/failed commands keyed to an aquarium.
- `aquarium_device_pairings`: short-lived pairing OTP and device-key hashes, with expiry.

After pulling schema changes, run:

```powershell
php artisan migrate
```

For deployment, back up the database first and apply migrations during the normal release process (typically `php artisan migrate --force`).

## Security Review and Remaining Work

The reviewed dashboard inserts displayed user/API values with `textContent`; the authentication script's `innerHTML` use creates only fixed eye-icon markup. No direct user-controlled HTML injection was identified in the inspected paths. This was a static code review, not a penetration test or dependency audit.

Before public production deployment, address these items:

1. Set `APP_DEBUG=false`, serve only over HTTPS, and set `SESSION_SECURE_COOKIE=true`. A prior local runtime check showed debug enabled and the secure-cookie option unset; recheck the deployed environment.
2. The dashboard loads executable Tailwind and Chart.js scripts from public CDNs. Bundle scripts locally, and add a restrictive Content Security Policy. Third-party script compromise could act with the user's page privileges.
3. Login throttling currently reads an `email` field while the login form submits `identifier`; fix the limiter to use the actual field and normalize it consistently.
4. Device API throttling is primarily per IP (120 requests/minute), which can affect devices behind shared NAT and does not provide a per-aquarium quota. Pair-start is also limited to five requests/minute. Consider per-SAIN throttling and endpoint-specific actuator limits.
5. ESP32 NVS/Preferences does not automatically encrypt stored secrets. For deployed hardware, evaluate ESP32 flash encryption and Secure Boot, and provide a secure factory-reset/credential-erasure procedure.
6. Add production monitoring for repeated invalid pairing attempts, failed device authentication, unusual actuator volume, and telemetry validation failures.

## Verification Snapshot

At the time this document was prepared, the full test suite passed (13 tests, 78 assertions), Blade templates compiled, and the frontend build passed. Vite reported an optional `fontaine` font-optimization notice. The ESP32 firmware is not part of this repository, so firmware implementation and hardware/network integration still need to be completed and tested against this API contract.
