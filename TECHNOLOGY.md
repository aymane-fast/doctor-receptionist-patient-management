# Technology & how it works

| Area | Actual implementation |
|---|---|
| Backend | PHP 8.2+, Laravel 12, Eloquent ORM, Blade server-rendered pages. |
| Database | MySQL for the Laragon installation; SQLite in automated tests and isolated audit previews. |
| Interface | Tailwind CSS 4 + custom CSS, plain JavaScript, Fetch/Axios, Font Awesome icons, Manrope/Newsreader fonts. |
| Assets | Vite 6 compiles/minifies CSS and JS with `npm run build`. Development reload uses `npm run dev`. |
| Charts | Chart.js; statistics trend data fetched from the app's JSON endpoint. |
| Authentication | Session login, hashed passwords, CSRF protection, login rate limiting. `users.role` stores `doctor` / `receptionist`; no separate roles table. |
| Clinical files | Private local storage, authorized doctor routes, PDF/JPEG/PNG uploads. Printing uses browser print views. |
| Tests | Pest/PHPUnit feature tests; latest verified run: 378 tests, 1,945 assertions. Browser QA is separate. |

## Dashboard updates

```text
Staff action → controller → database transaction/update
             → redirect → dashboard/workspace queries fresh database state
```

Doctor and receptionist read the same appointments table. A completed action updates the page returned to that user. **Another open browser/tab needs a refresh. There is no WebSocket, server-sent event or automatic dashboard polling implementation.** A “live” label does not mean cross-browser push.

## Search & responsiveness

- Internal JSON APIs: patient search, doctor-scoped clinical patient search, medication suggestions, next available slot, working hours and statistics trends.
- Patient search uses SQL `LIKE` conditions for names, phone, email, identifiers and guardians. The shared search service ranks up to 50 candidates and returns up to 20 suggestions.
- Autocomplete waits briefly before sending requests; shared clinical filters cancel superseded requests. Medication suggestions use the doctor's previous prescription names; manual entry stays available.
- Lists use pagination; dashboard queries eager-load related patients/doctors and limit recent/upcoming results.
- Database indexes cover doctor + appointment date, appointment date + status, doctor + record date, doctor + prescription date, and waitlist doctor + status. These are ordinary SQL indexes, not a full-text search engine.
- Settings are loaded once per request and invalidated when changed. Vite bundles local assets; workspace tabs switch without a new page request.

**No Elasticsearch, Algolia, Laravel Scout, external patient-search API or required Redis service.** Axios is bundled; much of the interactive code uses native Fetch.

## Services & triggers

| Service | Trigger / responsibility |
|---|---|
| `SetupService` | Wizard submission creates settings/staff; Demo also runs `SetupDemoSeeder`. |
| `SpecialtyService` | Setup/settings select the profile; forms read its fields/capabilities. Pediatrics adds guardians; common workflows stay shared. |
| `PatientProfileService` | Patient save updates identity/guardian data together in a transaction. |
| `PatientSearchService` | Search endpoint request runs SQL matching and relevance ranking. |
| `AppointmentSchedulingService` | Booking/queue/priority actions validate availability, allocate tickets and recalculate queue estimates; key mutations use transactions and doctor-row locks. |
| `DataExportService` | Doctor submits export filters; generates CSV data. |
| `AIService` | Doctor requests a patient summary; calls Groq if configured. Sends clinical context externally; use synthetic patients for demos. Core workflows do not depend on it. |

## Scheduled tasks (optional for local demo)

| Task | When scheduler is running |
|---|---|
| `appointments:mark-no-shows` | 00:30: unarrived past scheduled visits without records become no-show; past unfinished active visits are completed. Arrived waiting patients remain for staff review. |
| `appointments:send-reminders` | 17:00: emails tomorrow's scheduled patients with an email address; records sent time to avoid repeated sends. |

Local scheduler: `php artisan schedule:work` in a separate terminal. Deployment: invoke `php artisan schedule:run` every minute through the host scheduler. Times follow `APP_TIMEZONE`.

The default `.env.example` uses `MAIL_MAILER=log`: mail is written to logs, not delivered. Configure SMTP for actual reminders. These commands send synchronously; a queue worker is not required for these flows even though database queue support is configured.
