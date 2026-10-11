# Platform Audit Checklist

> Historical checklist: these statuses describe the previous audit, not the
> current release. See [the 9 October 2026 audit](docs/RELEASE_AUDIT_2026-10-09.md)
> for fresh evidence and outstanding checks.

Scope: Laravel clinic appointment manager. Status is updated only after the
corresponding checks have been executed.

| Status | Area | Coverage to verify |
|---|---|---|
| Complete | 1. Baseline | 77 routes discovered; full suite and production asset build pass; Blade cache compiles |
| Complete | 2. First-run and public access | Demo/production wizard, setup lock, root redirects, language switching, and public language APIs covered |
| Complete | 3. Authentication and roles | Login/logout/throttling, registration, guest redirects, and doctor/receptionist boundaries covered |
| Complete | 4. Dashboards | Doctor/receptionist pages, filters, populated/empty states, current-patient flow, and invalid staff target covered |
| Complete | 5. Patients | CRUD/search/API/specialty paths checked; failed walk-in rollback and optional demographics fixed |
| Complete | 6. Appointments | Scheduled/queue creation, capacity, conflict, time, status, rebook, follow-up, and availability API checks pass |
| Complete | 7. Waitlist | Add/list/cancel/book, duplicate/role scoping, queue and scheduled bookings checked; sidebar navigation added |
| Complete | 8. Medical records | Doctor-only CRUD, appointment binding, specialty fields, and patient lookup API covered |
| Complete | 9. Prescriptions | Doctor-only CRUD/print, medication items, linked records, lookup API, and authorization covered |
| Complete | 10. Settings and specialty | Working hours, clinic info, duration, mode/limits, specialty defaults, and role restrictions covered |
| Complete | 11. Statistics and exports | Doctor-only pages/APIs, trends, CSV output, date validation, and performance data covered |
| Complete | 12. UI/UX and client interactions | Blade compilation/build pass; navigation reviewed; autocomplete and AI output now safely encoded |
| Complete | 13. Operational paths | Reminder/no-show commands, demo seeding, error paths, final regression, asset build, and Blade compilation pass |

## Confirmed findings fixed

1. Failed walk-in appointment requests created a patient before the booking
   error was known. Patient creation and booking are now transactional.
2. Patients created without a birth date or gender could not be edited because
   the update validation made those optional fields required. Rules now agree.
3. Patient autocomplete widgets and the AI summary could insert untrusted text
   as HTML. All autocomplete surfaces now encode returned patient values, and
   AI output is encoded before limited Markdown formatting.
4. Waitlist had no primary sidebar navigation entry, making it difficult to
   discover. It is now available to both roles.
5. Current-patient endpoints accepted any user ID as a doctor target. They now
   require a user with the doctor role.
