# Current release audit — 9 October 2026

Branch: `variations`. The old AUDIT_CHECKLIST.md is historical, not current certification.
Only tests actually executed in this pass count as verified. No real patient data,
email, AI requests or production migrations are used for destructive tests.

## Test plan and running coverage

| Area | Test cases | Current evidence |
| --- | --- | --- |
| Isolation | Cached deployment config/routes; database guard | New regression passes with existing deployment caches present |
| Public/setup | Fresh demo and production; four practice types; duplicate accounts; malformed containers; locked setup | Existing setup suites rerun; malformed staff regression reproduced and fixed |
| Authentication | Login/logout/session renewal; throttling; guest access; staff roles | Existing AuthTest rerun |
| Every page | Doctor/receptionist navigation; empty/populated pages; detail/edit/create/print/reschedule | Populated page matrix passes for both roles across all four setup practice types; reschedule redirects checked |
| Patient registry | CRUD; optional demographics; duplicates; searches; malicious inputs; delete permissions | Existing suites and malformed search regressions pass |
| Pediatrics | Newborn age; 1–5 guardians; primary contact; edits; reminders; specialty switching | PediatricPracticeTest rerun |
| Scheduling | Scheduled/queue; past/closed/conflicting times; capacity; check-in; no-show; cancellation; rebooking; follow-up | Existing scheduling suites rerun; walk-in past time and false arrival regressions fixed |
| Consultation | Current/next patient; stale completion; cross-doctor access; consultation records | Existing guards/workflow suites rerun |
| Clinical | Medical records and prescriptions CRUD; linked appointments; clinical ownership; legacy prescription; printing | Existing suites and receptionist page denial matrix pass; no clinical marker leaked in shared receptionist pages |
| Waitlist | Add/duplicate/book/cancel; queue and scheduled; repeated requests | Existing operations suites and duplicate queue booking regression pass |
| Settings | Working hours; modes; duration; specialty; clinic; languages; role boundaries | Existing settings/language suites rerun |
| Reporting | Statistics/timeframes; exports/date ranges; confidential fields; CSV formula injection | Existing suites rerun; formula regression reproduced/fixed; legacy patient ID corrected |
| Performance | Availability query count; large patient histories; frontend build | Availability N+1 regression passes; production assets build; large-history/load measurements remain pending |
| Browser UI | Setup branches; real clicks/autocomplete/forms; mobile; print | Blocked: Windows browser tool stopped because URL-policy enforcement is unsupported; no current click or visual coverage claimed |
| Operations | Reminder/no-show commands; assets; Blade; dependency advisories; repository hygiene | Command suites pass; build/Blade/routes/config/package discovery pass; Composer and production npm advisory checks clean |

## Baseline

Fresh baseline: **173 tests, 649 assertions passed**. New release-readiness tests
initially reproduced five defects; after fixes **6 tests, 21 assertions passed**.
Final after compatible dependency updates: **201 tests, 1,119 assertions passed**.
Every protected route/method is exercised without authentication and returns 401.
Fresh demo launch and doctor/receptionist page matrices cover general practice,
pediatrics, dentistry and cardiology. This is not proof that every possible failure
is eliminated, nor a complete commercial-release sign-off.

## Findings fixed in this pass

- Deployment caches could direct tests to the clinic database. Test bootstrap now
  bypasses config and route caches, forces isolated services, and fails closed
  before database refresh unless testing/in-memory SQLite is confirmed.
- Manually entered past walk-in slots were accepted. They now fail atomically.
- Future walk-in appointments were marked arrived; future bookings were blocked
  merely because the clinic was closed at submission. Both states are corrected.
- CSV cells could be interpreted as spreadsheet formulas. User-controlled cells
  beginning with formula prefixes (including whitespace/control prefixes) are escaped.
- Legacy single-medication exports used a nonexistent patient ID attribute.
- Availability loaded appointments once per candidate slot; it now loads blockers once.
- Malformed setup staff containers caused type errors/500s before validation.
- Array-shaped search/filter inputs caused 500s; bounded typed validation now rejects them.
- Queue waitlist booking could create duplicate active patient appointments; the
  shared queue creator rejects duplicates while holding the existing doctor row lock.
- The summary Copy button relied on a browser-specific global event. It now uses
  an explicit button argument and shows an error if clipboard access is unavailable/denied.
- Composer reported 46 advisories across 14 installed packages. Updates remain
  within current project constraints (Laravel 12, Pest 3); final Composer audit
  reports zero advisories. Production npm audit also reports zero advisories.

## Release limitations (must not silently mark complete)

Pending items remain open until evidence is recorded. Browser/device coverage,
concurrent MySQL requests, sustained production load, hosting security/TLS/backups,
real delivery integrations, medical/legal compliance and external AI privacy require
their own evidence. The formal Codex Security scan could not be started because
this session does not expose its required scan-start capability; direct access-boundary
checks are not equivalent to that formal scan.

Before a sale, explicitly verify the deployment is production mode with debug
disabled, HTTPS/secure sessions, protected database backups, non-demo credentials,
and a reviewed policy for sending clinical information to the optional external AI
provider. Clinic-wide doctor exports/statistics and shared clinical history views
are existing behavior; their suitability for the customer's multi-doctor privacy
policy needs an explicit product decision, not an assumed security certification.
