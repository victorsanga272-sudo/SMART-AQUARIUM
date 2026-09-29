<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Email delivery setup

The application sends a welcome email when a new user registers. The mailer is configured for SMTP instead of Laravel's default log transport so registration emails can actually be delivered.

For local development on Windows, install Mailpit and run it in a terminal:

```bash
winget install --id axllent.mailpit
mailpit --listen 127.0.0.1:8025 --smtp 127.0.0.1:1025
```

Then set these values in your `.env`:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="noreply@localhost"
MAIL_FROM_NAME="ViVo"
```

Open http://127.0.0.1:8025 to inspect outgoing messages in Mailpit. While signed in locally, visit `/debug/mail` to send a test message to your own account. This authenticated route is registered only in local and testing environments; it is not available in production.

For Gmail, use a Google App Password (not your account password) and enable 2-Step Verification:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME="your-address@gmail.com"
MAIL_PASSWORD="your-16-character-app-password"
MAIL_FROM_ADDRESS="your-address@gmail.com"
MAIL_FROM_NAME="ViVo"
```

For SendGrid, create an API key with mail-sending permissions and verify the sender identity:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME="apikey"
MAIL_PASSWORD="your-sendgrid-api-key"
MAIL_FROM_ADDRESS="your-verified-sender@example.com"
MAIL_FROM_NAME="ViVo"
```

Keep provider credentials in `.env` or your deployment's secret manager. Never commit real credentials. After changing mail settings, clear cached config and run the tests:

After updating the environment, clear the config cache:

```bash
php artisan config:clear
php artisan test
```

## Aquarium Device API

Each account owns one aquarium. Each aquarium has a unique 20-digit **SAIN** (Smart Aquarium Identification Number). Existing aquariums receive a SAIN through the SAIN migration; new aquariums receive one at account registration. SAIN is an identifier, not a secret. Run `php artisan migrate` after updating to create and backfill aquarium data.

Generate a 24-byte random device key on the ESP32 and encode it as unpadded Base64URL (32 characters, 192 bits of entropy). Generate an 8-digit OTP from the ESP32 hardware random generator. The OTP expires after five minutes. The ESP32 retains the key; the platform stores only its SHA-256 hash after the owner confirms the OTP.

For Arduino-based ESP32 firmware, the hardware random generator can create the device key:

```cpp
#include <esp_system.h>
#include <mbedtls/base64.h>

String generateDeviceKey() {
	uint8_t bytes[24];
	esp_fill_random(bytes, sizeof(bytes));

	unsigned char encoded[33];
	size_t encodedLength = 0;
	if (mbedtls_base64_encode(encoded, sizeof(encoded), &encodedLength, bytes, sizeof(bytes)) != 0
			|| encodedLength != 32) {
		return "";
	}

	encoded[encodedLength] = '\0';
	String key(reinterpret_cast<char*>(encoded));
	key.replace('+', '-');
	key.replace('/', '_');
	return key;
}

String generatePairingOtp() {
	constexpr uint64_t range = 100000000ULL;
	constexpr uint64_t limit = (1ULL << 32) / range * range;
	uint32_t randomValue;

	do {
		esp_fill_random(&randomValue, sizeof(randomValue));
	} while (static_cast<uint64_t>(randomValue) >= limit);

	char otp[9];
	snprintf(otp, sizeof(otp), "%08lu", static_cast<unsigned long>(randomValue % range));
	return String(otp);
}
```

The pairing flow is:

1. Show the aquarium's SAIN on the dashboard in four groups of five digits. Send the raw 20 digits to the ESP32 during its local setup.
2. Generate the device key and an 8-digit OTP on the ESP32. Store the key in NVS/Preferences.
3. The ESP32 starts pairing with `POST /api/device/pairing/start`, sending its SAIN, OTP, and key. The server stores keyed/hash digests and expires the challenge after five minutes.
4. The owner enters the OTP in **Dashboard → Settings → Aquarium device**. On a valid code, the server saves the key hash for that owner's aquarium and consumes the challenge. Re-pairing invalidates the previous key.

Never commit the real key to firmware source or print it during normal operation. The SAIN is an identifier, not an authentication secret.

Send device requests with these headers:

```text
Authorization: Bearer <device-key>
X-Aquarium-SAIN: <20-digit-sain-without-display-separators>
Content-Type: application/json
Accept: application/json
```

Post readings to `POST /api/device/telemetry`:

```json
{
	"temperature": 25.3,
	"ph": 7.4,
	"turbidity": 3.8,
	"water_level": 90
}
```

Poll `GET /api/device/commands` for that aquarium's queued commands. After executing a command, acknowledge it with `POST /api/device/commands/{id}/ack` and a JSON body containing `{"status":"completed"}` or `{"status":"failed","result":"reason"}`. Dashboard telemetry and actuator requests are scoped to the currently authenticated account; device requests are scoped to the matching aquarium ID and bearer key.

The pairing-start request body is:

```json
{
	"sain": "12345678901234567890",
	"otp": "48273165",
	"device_key": "0123456789abcdefghijklmnopqrstuv"
}
```

Use HTTPS for all pairing and device API requests.
