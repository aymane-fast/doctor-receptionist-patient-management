# Client-demo audit: second pass — 11 October 2026

Branch: `variations`. This pass targets gaps in the [first audit](CLIENT_DEMO_AUDIT_2026-10-11.md), rather than treating its passing results as proof of exhaustive correctness.

## Plan and completed checklist

| Gap / section | Executed checks | Result |
| --- | --- | --- |
| Real MySQL concurrency | Two separate PHP/Laravel processes released together; overlapping scheduled bookings, duplicate patient bookings, queue numbering/limit, start/finish consultations, duplicate clinical saves, reopening and simultaneous edits | 11 scenarios passed after fixes; invariants verified in MySQL, not inferred from SQLite |
| Reminder transport | Actual Symfony/Laravel SMTP transport to a localhost capture server; French rendered message addressed to synthetic guardian contact; rerun suppression | One message delivered locally; reminder timestamp recorded; second invocation sends zero |
| Integration failures | SMTP exception on one recipient with batch continuation and subsequent retry; optional AI connection exception, plus previously retained malformed/body/provider tests | Regression cases passed; no real email recipient or external AI request used |
| Larger datasets | 5,008 synthetic patients and 1,000 clinical records; patient list, autocomplete, dashboard, clinical list, prescription creation | All rendered; measured timings/query counts below |
| Validation/storage limits | Clinical numeric/string bounds, prescription integer overflow, malformed old medication/patient inputs, optional follow-up reason | Valid inputs preserved; invalid submissions rejected without partial prescription creation or recovery-page crashes |
| Failed form recovery | Two prescription rows, doses/frequencies and selected patient after server rejection; add-row index after recovery; invalid hours retain toggle/start/end | Browser and durable regressions passed |
| Pagination/search | Patient and multi-section dashboard filters retained across page links; whitespace-only autocomplete | Regression cases passed |
| Operational commands | Invalid calendar/format dates; no-show command cannot target future visits | Regression cases passed; explicit current-day no-show operation retains existing behavior |
| Additional responsive screens | 360px and 768px: dashboard, child details, patient/appointment/record/prescription creation, appointment edit, queue, waitlist, settings, statistics and exports | 24 page checks passed with no body overflow; three actual interaction/recovery checks also passed |
| Backup/restore | `mysqldump --single-transaction` of synthetic audit DB; import into a new audit DB; compare deterministic row hashes and column definitions for each of its 19 tables | All 19 matched; dump approximately 902 KB |
| Existing workflows after changes | Pediatric contacts, scheduling/edit/cancel, clinical record create/edit, multi-item prescriptions/print/delete, waitlist, specialty settings, current/next, queue buttons, follow-up, chart buttons | 27 workflow checks plus 15 operational checks passed; no observed JavaScript exceptions/server errors |
| Full regression/build | Entire isolated Pest suite, Blade compilation, production Vite build, whitespace check | 260 tests passed / 1,272 assertions; build and templates compile; no whitespace errors |

## Reproduced issues and changes

1. **Concurrent first bookings could deadlock.** The scheduled path locked an aggregate over an empty appointment day. A simultaneous MySQL test reproduced a serialization exception rather than a normal conflict response. Scheduled creation now locks the existing doctor row and rechecks slot and same-day patient availability inside the transaction. Booking, walk-in and scheduled follow-up creation share this path.
2. **Simultaneous appointment edits could double-book.** Two appointments initially at 08:00 and 09:00 were both moved to 10:00 by separate processes; both previously succeeded. Edits now acquire doctor locks in stable order and repeat conflict, patient and queue-limit checks under the lock. The retest leaves only one appointment at 10:00; the other receives validation feedback.
3. **Restoring cancelled/no-show visits bypassed normal booking rules.** Status actions could restore past/closed-day appointments or a duplicate active patient visit. Restoration now validates date, hours, same-day patient duplication and slot/capacity under the doctor lock. Status/current-patient mutations also refresh models under the lock to handle stale actions.
4. **Storage bounds were not validated.** Weight/height/temperature/heart rate, blood-pressure length and medication duration could exceed their MySQL column bounds. Clinical text fields now have a Unicode-safe 16,000-character limit for existing TEXT columns. These are storage constraints, not claims of clinically appropriate vital ranges. Oversized values now produce validation errors instead of database failures. Prescription item submissions are bounded to 100 rows.
5. **A visit could get duplicate linked consultation records.** The application uses a singular appointment-to-record relationship. Store/update validation prevents an already-linked appointment from being selected again, and linked record creation rechecks under the doctor lock. A simultaneous creation test now leaves one record. Existing records are not deleted or rewritten, and no destructive unique-index migration was imposed on existing data.
6. **An omitted optional follow-up reason could access an undefined key.** The default reason now works when the field is omitted, not just when the browser sends an empty value.
7. **Whitespace autocomplete could return patients.** Search trims before its minimum-length check and returns an empty collection for whitespace-only input.
8. **Patient/dashboard pagination lost filters.** Pagination now retains the query string, including the dashboard's other independent sections.
9. **Prescription drafts disappeared after server validation failures.** The creation form restores all submitted medication rows and selected patient, retains custom/localized frequency text, and continues new row numbering correctly. Malformed nested old values are safely handled. Working-hours validation failures also retain entered values and toggles.
10. **Client-side patient guards selected the first page form.** Three creation screens now bind their validation to their own action-specific form rather than an unrelated form in the layout. A missing-patient browser submission stays on the prescription form with a usable prompt.
11. **Invalid patient IDs could crash a redirected creation screen.** Hidden patient fields now use the safely resolved patient instead of printing an array-valued old input. All three creation-screen recovery regressions pass.
12. **Command date arguments lacked safeguards.** Reminder dates must be real YYYY-MM-DD dates. No-show processing also rejects future-day targets rather than prematurely marking those visits missed.

## Measured larger-data results

These are one local render/sample per path using MySQL and synthetic data, not production latency percentiles or HTTP load measurements. Views were rendered through Laravel controllers; the diagnostic renderer supplied the normal validation-error bag.

| Path | Elapsed | DB queries | Rendered/JSON bytes |
| --- | ---: | ---: | ---: |
| Patient search/list | 69 ms | 14 | 105,498 |
| Patient autocomplete | 17 ms | 2 | 4,990 |
| Doctor dashboard | 24 ms | 23 | 40,808 |
| Clinical search/list | 27 ms | 15 | 102,669 |
| Prescription creation | 50 ms | 14 | 29,457 |

## Evidence / safe execution

- New durable coverage: **30 cases** in `tests/Feature/SecondPassAuditTest.php`; the full suite now has **260 passing tests, 1,272 assertions**. Final recorded duration: 20.88 seconds, while browser checks were running.
- Eleven concurrent **controller/service** scenarios use real MySQL connections in independent PHP processes with synchronized starts. They test database invariants and error outcomes, not HTTP middleware under high load.
- Second-pass browser file: **27 passing checks** (24 viewport/page checks and three recovery/validation interactions). Existing workflow and operations scripts rerun: **42 passing checks**. No page exceptions or server errors recorded.
- Local SMTP fixture: one captured message, correct synthetic recipient, successful timestamp update, no duplicate on rerun. Regression mocks additionally cover batch continuation and retry after a send failure.
- Local artifacts/scripts/results, synthetic dump and SQLite databases remain ignored under `outputs/client-audit/`. The audit MySQL database and its synthetic restore copies use the `clinic_audit_pass2_` prefix and are retained for reproducibility. The final comparison explicitly restricts table enumeration to the audit schema.
- No clinic database was reset or edited in this pass. No new production schema migration is required. Presentations and generated schema artifacts remain ignored.

## Remaining boundaries

- Real SMTP credentials, inbox delivery/spam behavior and external Groq credentials/provider behavior still require checks with intended accounts and approved recipients/data. Local transport and mocked failures cannot certify those services.
- Two-process MySQL races are now covered; sustained HTTP load, many concurrent users, long-running sessions, cross-server cache/locking and all combinations of simultaneous edits are not comprehensively stress-tested.
- Backup/restore was verified locally on synthetic data, not the final production backup schedule, encryption, offsite storage, retention or disaster-recovery environment.
- Final hosting/TLS, scheduler/worker installation, clinic `APP_TIMEZONE`, production configuration, all browsers/devices and large-dataset edit dropdown ergonomics still need deployment-specific review. Do not treat local timing samples as a production performance guarantee.
- This remains a functional client-demo audit, not medical/regulatory certification or a dedicated security penetration test. Passing cases do not prove that every possible defect has been eliminated.
