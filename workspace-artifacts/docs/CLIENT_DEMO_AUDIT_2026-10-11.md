# Client-demo audit — 11 October 2026

Branch: `variations`. Prior audit results are historical, not evidence for this pass.
Existing localization changes are preserved. Destructive checks use isolated data;
no real email or external AI requests are sent. A pass means the recorded cases
passed, not that all possible defects have been eliminated.

## Running checklist

| Section | Cases to execute | Status / evidence |
| --- | --- | --- |
| Inventory & baseline | Routes, controllers, services, models, views; suite; build; Blade compilation | Complete: baseline 193 passed / 8 failed; final 230 passed; production build and Blade compilation passed |
| First-run setup | Demo/production, all four practice types, default accounts/data, malformed inputs, duplicate email, setup lock | Passed recorded cases: SetupWizard/SetupWizardEdge suites; fresh pediatric demo launch without typing; full production wizard with clinic/mode/staff and no sample patients |
| Auth & roles | Login/logout, throttle, guest protection, doctor/receptionist restrictions, cross-doctor ownership | Passed Auth/ReleaseReadiness cases; browser login/logout for both roles; 22 read-only MySQL page/role checks |
| Dashboard/current patient | Populated/empty, search/date/doctor filters, current/next, stale and repeated actions | Passed DashboardAndStatistics/ClinicWorkflow/ClinicOperations cases; real browser start, follow-up and finish/advance |
| Patients & pediatrics | CRUD, search/pagination, optional demographics, future DOB, guardians, primary contact, specialty switching | Passed PatientManagement/PediatricPractice/SpecialtyAdaptation cases; browser two guardians, primary selection, remove/reindex, five-guardian cap, standard optional demographics, deletion accept/dismiss |
| Scheduled appointments | CRUD, search/status/date filters, hours, conflicts, past dates, slot API, check-in, transitions | Passed AppointmentManagement/AppointmentGuards/WorkingHoursStatus cases plus new regressions; browser create/edit/cancel/delete, autocomplete, filters, slot refresh |
| Queue & waitlist | Order, capacity, duplicates, movement, book/cancel/rebook/follow-up, repeated actions | Passed AppointmentMode/QueueRealism/ClinicOperations cases; browser queue mode, booking, date filter, up/down, check-in/current/next, follow-up, waitlist duplicate and cancel |
| Clinical records | CRUD, linking consistency, history, specialty fields, authorization, invalid data | Passed MedicalRecord/SpecialtyAdaptation and new ClientDemoAudit cases; browser create/edit and persisted clinical history/examination; patient history links |
| Prescriptions | Multiple/legacy medications, CRUD, patient linkage, dates, print, authorization | Passed Prescription cases; browser multi-item create/edit/show/print/delete; print and mobile screenshots visually inspected |
| Settings & locales | Hours, clinic, duration/mode/limits, specialty, EN/FR, failed form preservation | Passed SettingsAndExport/Language/WorkingHoursStatus cases; both-role EN/FR page matrix, actual hours/mode/specialty updates and guardian retention |
| Reports & exports | Timeframes, filters, empty data, date validation, CSV escaping, private data | Passed DashboardAndStatistics/SettingsAndExport/ReleaseReadiness cases; browser Week/Month/Year API interactions and downloaded patient CSV |
| Browser interactions | Actual navigation/clicks/autocomplete/forms/buttons, console/network errors, responsive screens | 143 final isolated browser checks passed; no page exceptions or observed server errors; mobile navigation and 390px dashboard checked |
| Operations & regression | Reminders/no-show, integration failure handling, final suite/build, report limitations | Passed NoShowCommand/ClinicOperations and mocked AI success/failure/malformed-body cases; additive migration applied to Laragon MySQL; limitations below |

## Findings and retests

| Reproduced defect | Fix / retest |
| --- | --- |
| English label lookup could return a language-group array on Windows, breaking patient details and CSV exports | Added English JSON labels; both locales render string labels; patient pages and real export download pass |
| Appointment edit contained nested deletion form; the browser could submit DELETE from Update | Separated forms and linked the independent Delete button; actual edit retains appointment and changed time/reason; independent deletion passes |
| Patient deletion inline submission bypassed cancellation confirmation | Removed premature submission; dismiss preserves patient, accept deletes in production-style browser checks |
| Medical record visit type, history and examination were exposed but discarded | Added nullable schema columns, model fields, validation and CSV export; create/update persistence tests and browser retest pass |
| Medical record edit date rendered with time and appeared blank in date input | Format date as Y-m-d; edit submits and persists correctly |
| Completing current appointment through other paths did not advance the waiting room consistently | Clinical record creation and status/edit completion now promote next inside transaction; endpoint and browser current/next tests pass |
| Clinical patient-history links were ignored by list controllers | Validate and apply patient_id and retain pagination filters; both clinical lists covered by regressions/browser checks |
| Appointment-mode-only filter did not activate listing | Treat mode as an active filter; regression verifies filtered results |
| Scheduled edits could move active appointments into the past; cancelled note edits could collide with reused slot | Enforce active rescheduling rules, skip inactive slot conflicts; regressions and scheduled form flow pass |
| Queue moves/reopening could bypass working-day or capacity checks, including full edit form | Check date/hours/capacity for moved or reopened active tickets; regression tests cover status and edit paths |
| False working-day values still enabled day; receptionist settings JavaScript dereferenced absent controls | Use boolean values and exclude closed-day time rules; guard missing controls; both-role settings rendering/save pass |
| Flash message JavaScript called an undefined legacy export helper | Use the existing notification helper; browser working-hours success flash has no exception |
| Walk-in slot refresh button had mismatched ID and no listener | Bind existing refresh control; browser verifies availability request and button recovery |
| Prescription deletion endpoint had no visible UI action | Added confirmation-protected show-page deletion form; real browser deletion passes |
| No-show appointments lacked listing filter option | Added status option; status/list coverage and no-show browser flow pass |
| Array-valued foreign keys reached model/database operations and caused 500 responses | Require integer IDs throughout booking/record/prescription/waitlist operations; six malformed POST regression cases return 422 |
| Receptionist doctor selectors accepted arrays or a receptionist as doctor | Validate integer and doctor-role existence; four malformed query regressions return 422 |
| Empty successful AI provider response was reported as a generated summary | Require nonempty string; mocked success, provider failure and malformed-body regressions pass; translated safe failure message |

## Evidence and reproducibility

- `php artisan test --compact`: **230 passed, 1184 assertions** (11.50s on this machine). The suite uses isolated in-memory SQLite, never the clinic database.
- **29 new regression cases** are retained in `tests/Feature/ClientDemoAuditTest.php`. Existing tests were rerun, not assumed correct because of prior audit reports.
- `npm run build`: passed; `php artisan view:cache`: passed; `git diff --check`: no whitespace errors.
- Final headless Chrome checks: page/localization matrix 84; operational/clinical workflow 27; production-style empty-state/patient flow 17; queue/current-patient/button flow 15. Fresh demo and production onboarding were separately executed at initial database creation; later reruns exercise setup lock/login rather than resetting data.
- Existing Laragon deployment: MySQL connection verified, setup complete in demo mode with one doctor and one receptionist. The one pending additive migration ran successfully. Read-only browser smoke: **22 passed**, including receptionist 403 responses on doctor-only pages. No patient data was reset or clinical test records inserted into this database.
- Browser scripts, synthetic SQLite databases, JSON results, CSV and screenshots are kept locally under ignored `outputs/client-audit/`. Failure screenshots there are historical debugging captures, not current failing results. Presentations remain ignored and unchanged.
- Framework coverage includes every exposed application module; the `orders` schema has no exposed CRUD page, so no nonexistent order interface is claimed as tested.

## Remaining release checks / limits

Follow-up: the [second-pass audit](CLIENT_DEMO_SECOND_PASS_2026-10-11.md) now covers real MySQL races, local SMTP transport, larger synthetic datasets, additional responsive/form checks and local synthetic backup/restore. The results below describe this original pass, not the later evidence.

No failures remain in the recorded checks. This is evidence for these cases, not a guarantee against every possible defect.

- Real SMTP delivery and a real Groq API call were deliberately not sent; provider responses/failures were simulated. Verify credentials, recipient delivery and consent/data handling before enabling external services.
- Concurrent booking/load under MySQL was not stress-tested. SQLite functional tests do not establish production concurrency behavior.
- Final hosting/TLS, backups and restore, production scheduler/worker deployment, large-data performance, and all browser/device combinations need deployment-specific verification. Desktop Chrome plus selected mobile views were exercised here.
- This is a functional/client-demo audit, not medical safety certification, regulatory compliance approval or a dedicated penetration test.
