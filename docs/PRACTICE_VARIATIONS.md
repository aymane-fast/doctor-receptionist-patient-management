# Practice variations

Work branch: `variations`. Existing uncommitted work is preserved.

## Product boundaries

- One patient, appointment, queue, consultation, prescription and reporting system.
- Setup offers general practice, pediatrics, dentistry and cardiology. Existing legacy specialty settings remain readable.
- Dentistry and cardiology retain the shared workflow and existing optional notes; no separate schedulers or clinical modules.
- A configured `guardians` capability enables pediatric registration. A persistent patient flag keeps the family information available after a practice switch.
- The child owns every clinical record. Date of birth is required (including newborns); national ID is optional. Internal patient IDs distinguish siblings without fabricated national IDs.
- One to five contacts, explicitly selected primary contact. The existing patient contact columns mirror the primary guardian so booking/reminders keep using the shared services. Secondary contacts do not automatically receive reminders.
- Allergies, conditions, consultation history, weight/height, development/feeding notes and prescriptions are reused. No automatic pediatric medication doses or growth interpretations.
- Existing guardian-name notes are migrated without deleting historical JSON. Backup before migrating; rollback removes structured guardians and deliberately leaves national IDs nullable.

## Inspection / regression checklist

- [x] Inspect routes, models, migrations, setup/settings, specialty config/services, registration/search, scheduling, current/next patient, clinical records, prescriptions, reminders, exports and seeders.
- [x] Baseline automated tests: 146 tests / 488 assertions passed before these changes.
- [x] Create `variations`; preserve existing dirty worktree.
- [x] Setup: four practice choices, default general, one-click specialty demo, production setup, invalid choice, validation-error navigation.
- [x] Patient forms: general unchanged; child/newborn, two guardians, primary selection, optional ID, siblings, update/remove guardian.
- [x] Validation: missing/future DOB, missing guardian, invalid email, malformed contacts, bad primary, too many contacts, atomic failures.
- [x] Role access: receptionist registration/scheduling; clinical records, prescriptions and clinical AI summaries restricted to doctor.
- [x] Scheduling: scheduled and queue bookings, current/next child identity, consultation completion, standard scheduler regressions.
- [x] Clinical workflow: child consultation/history, allergies/conditions, prescription patient identity and printable age/contact.
- [x] Search: child name, guardian name/phone/email, siblings disambiguated by child DOB.
- [x] Compatibility: legacy migration, practice switches retain guardians/history, shared exports and reminder addressing.
- [x] Full PHP regression suite, Blade compilation, frontend production build.
- [x] Browser smoke tests: setup, pediatric form add/remove/primary, profile, responsive layout.

## Verification evidence (2026-09-17)

- `php artisan test --compact`: 173 tests / 649 assertions, including 27 pediatric and migration-resume cases. SQLite in memory; external email faked, no live AI calls.
- `php artisan view:cache`, `npm run build`, `git diff --check`: passed (Git only reports existing CRLF normalization notices).
- Separate disposable SQLite browser installations on ports 8017 and 8018: fresh general and pediatric one-click setup; skipped production steps return to the first missing field; newborn registered with two guardians/no national ID; primary changed and persisted; added blank guardian removed; receptionist login/navigation and guardian-name search checked.
- Desktop and 390px-wide child-profile layouts checked. No browser console errors on the exercised forms. The edit-link confirmation was not controllable through the browser helper, so editing was verified using its observed destination URL.
- Test databases are under ignored `outputs/`; the previous isolated browser checks did not alter the clinic database.
- Live activation (2026-09-17): after Laragon was started, MySQL `doctor_apointment` was reachable. A pre-migration backup was created at `outputs/doctor_apointment-pre-pediatrics.sql` (46,263 bytes). `php artisan migrate --pretend` confirmed the additive SQL, then `php artisan migrate --force` applied `2026_09_17_000001_add_patient_guardians` in 245 ms. Migration status is fully ran; the live schema has the `patient_guardians` foreign-key cascade and nullable child national ID. Existing general-demo data was preserved: 36 patients, 0 pediatric flags, 0 guardians.
- Live HTTP smoke test: the Laragon-backed app returned the login page with the final production build assets. Configuration, route and view caches rebuilt successfully.
- Recovery note: an accidental cached-config test run briefly rebuilt the live schema. With the user’s explicit approval, the verified pre-test snapshot was restored, returning 36 patients, 2 users, and 21 appointments. The migration was then made resumable for this scenario, re-applied successfully as batch 2, and the recovered counts were rechecked.

This is feature-focused verification, not a claim that every platform page has been exhaustively browser-tested. MySQL execution, physical printer pagination, live email delivery and live AI integration were not tested here. Shared backend coverage comes from the existing regression suite.
