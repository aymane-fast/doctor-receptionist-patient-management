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

Open the app and choose a launch mode. **Demo** starts immediately with the
default doctor, receptionist, patients, and appointments — no form fields to
complete. It signs you in as `doctor@example.com` (password `password`); log
out and use `receptionist@example.com` with the same password to show the
front-desk experience. **Production** opens the full wizard for clinic
information, working hours, appointment mode, and staff accounts.

### Practice type (variations branch)

Choose **General Practice**, **Pediatrics**, **Dentistry**, or **Cardiology**
on the first setup screen. General practice is the default; choosing a specialty
also works with the one-click demo. Dentistry and cardiology keep the standard
workflow and existing optional clinical notes.

Pediatrics registers the **child as the patient**, with a required birth date
(newborns supported), optional national ID, and up to five parent/guardian
contacts. Select one primary contact for calls and reminder emails. Siblings
can share contact details. Search by child or guardian; the child remains the
owner of appointments, consultations, and prescriptions. Existing allergies,
conditions, weight/height, development and feeding notes remain shared.

For an existing installation, back up the database, switch to this branch, and
run `php artisan migrate` and `npm run build`. The additive migration imports
legacy guardian notes without removing clinical history. Guardian details stay
available if the practice later switches specialty. Do not use `migrate:fresh`
on a clinic database. The migration rollback removes the guardian table, so
back up before rolling back as well.

See [practice decisions and verification checklist](workspace-artifacts/docs/PRACTICE_VARIATIONS.md).

Generated presentations, diagrams, audit reports, captures and temporary files are consolidated in [workspace-artifacts](workspace-artifacts/README.md). This folder is separate from application/runtime files.

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

### Updating an existing clinic

Back up the database **and** `storage/app/private` (uploaded documents), then run:

```bash
php artisan optimize:clear
php artisan migrate --force
npm run build
```

Clearing old route caches is important after updating workflow routes. Recreate
production caches only after the update succeeds. Never use `migrate:fresh` on
a clinic installation. Keep `APP_DEBUG=false` outside local development.

The current-patient workspace saves observations without finishing the visit.
Use **Complete Visit & Next Patient** explicitly when ready. Prescriptions,
examination requests and private documents can be added after saving the
consultation. Historical temperature values without a known unit stay unchanged
until a doctor confirms their unit; changing that label does not convert them.

Manual scheduled-booking exceptions require the confirmation checkbox. They
retain the chosen date/time and record the authorizing staff member. Queue
priority can be reordered without changing scheduled booking times or queue
ticket identities. Each patient still has at most one active booking per
doctor/day; reschedule that booking instead of creating an accidental duplicate.

Medication autocomplete reuses the signed-in doctor's previous prescription
names, not a verified Moroccan medication catalogue. Manual names, strengths,
forms, dosage and frequency remain editable. A verified catalogue requires an
approved reference dataset and its reuse conditions.

Documents accept PDF/JPEG/PNG up to 10 MB and are served only through
doctor-authorized routes, never public storage URLs. Configure PHP/web-server
upload limits above 10 MB (for example `upload_max_filesize=12M`,
`post_max_size=16M`). Back up private files with the database and keep production
storage outside the public web root. This is document storage, not malware
scanning or advanced medical imaging.

- `APP_TIMEZONE` — working hours and "today" comparisons use this timezone;
  set it to the clinic's local zone in production.
- Working hours, clinic info, appointment duration, and language live in the
  `settings` table and are editable from the Settings page.
- Login attempts are rate-limited (5/minute per email + IP).
