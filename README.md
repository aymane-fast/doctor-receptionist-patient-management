# Clinic Appointment Manager

A Laravel 12 application for running a small medical clinic: patient records,
appointment scheduling (fixed time slots or a walk-in queue), medical records,
prescriptions, statistics, and CSV exports. The interface is bilingual
(English / French).

## Roles

| Role | Capabilities |
|---|---|
| **Doctor** | Everything: own appointments and queue, medical records, prescriptions, statistics, exports, staff registration, clinic settings |
| **Receptionist** | Patients, appointments and queues for all doctors, appointment duration setting, dashboards |

Medical records, prescriptions, statistics, and exports are doctor-only.
Doctors only see their own appointments, records, and prescriptions.

## Appointment modes

Each doctor works in one of two modes (configurable in Settings, defaulted by
the setup wizard):

- **Scheduled** — patients book fixed time slots. Slot length comes from the
  *appointment duration* setting; bookings are validated against working
  hours, overlaps, and past times.
- **Queue** — patients take a number for the day. Positions can be reordered,
  estimated times are recalculated automatically, and an optional daily limit
  caps the queue.

## Getting started

```bash
composer install
cp .env.example .env          # then configure DB_* and APP_TIMEZONE
php artisan key:generate
php artisan migrate
npm install && npm run build
php artisan serve
```

Open the app and the **first-run setup wizard** walks you through clinic
information, working hours, appointment mode, and staff accounts. Choosing
*demo* mode seeds realistic sample data.

Alternatively, seed everything from the command line (creates
`doctor@example.com` / `receptionist@example.com`, password `password`):

```bash
php artisan migrate --seed
```

### Optional: AI patient summaries

Patient profiles can generate an AI clinical summary via the Groq API. Set
`GROQ_API_KEY` in `.env` (see `config/services.php`); the feature fails
gracefully when unset.

## Tests

Feature tests run against an in-memory SQLite database:

```bash
php artisan test
```

The suite covers authentication and throttling, role-based authorization,
patient management and search, scheduled and queue booking rules, medical
records, prescriptions, settings, CSV exports, dashboards, statistics, and
the setup wizard.

## Configuration notes

- `APP_TIMEZONE` — working hours and "today" comparisons use this timezone;
  set it to the clinic's local zone in production.
- Working hours, clinic info, appointment duration, and language live in the
  `settings` table and are editable from the Settings page.
- Login attempts are rate-limited (5/minute per email + IP).
