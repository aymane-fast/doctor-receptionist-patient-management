# Setup, demo & operating guide

Read sections 1–2 for installation, 3–8 for daily operation, and 9–12 for settings, maintenance and troubleshooting. Button labels below use English; the language menu also provides French. [Technology overview](TECHNOLOGY.md).

## 1. Install once (Windows / Laragon)

Have Git, PHP 8.2+, Composer, Node.js/npm and MySQL available in your terminal. Check with `git --version`, `php -v`, `composer --version`, `node -v` and `npm -v`. If PHP is missing, open Laragon's terminal/use its PHP executable. PHP needs MySQL and SQLite support (SQLite is used by tests).

Start MySQL in Laragon. Open its database manager, connect using your MySQL credentials and create an empty database called `doctor_apointment`. These instructions use Laravel's local server; Laragon Apache is not required for this URL.

```powershell
git clone --branch variations https://github.com/aymane-fast/doctor-receptionist-patient-management.git
cd doctor-receptionist-patient-management
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Already have the project? Open its folder and use `git pull origin variations` instead of cloning. Keep your existing `.env` and application key.

Edit `.env` to match your MySQL account:

```dotenv
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=Africa/Casablanca
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=doctor_apointment
DB_USERNAME=root
DB_PASSWORD=
```

Then:

```powershell
php artisan optimize:clear
php artisan migrate
npm ci
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Open **http://127.0.0.1:8000**. Keep that terminal running. Do **not** seed: the wizard creates its own demo data.

## 2. Choose the demo

On the setup screen, select a practice type → **Demo** → optionally check **Keep the demo open 24 hours, every day** → launch. No clinic or account details needed; you enter as the doctor.

| Role | Email | Password |
|---|---|---|
| Doctor | `doctor@example.com` | `password` |
| Receptionist | `receptionist@example.com` | `password` |

Use a separate browser profile/private window for the receptionist to keep both roles signed in. Two tabs in the same browser session share the same login.

**24-hour option:** Doctor → Settings → **Keep the demo open 24 hours, every day** → Save. Allows nights/weekends; switching it off restores saved working hours. Existing appointments stay unchanged. This controls opening hours, not the clock's display format.

## 3. Start the day and understand access

Start MySQL → open a terminal in the project → run `php artisan serve --port=8000` → open the site → log in. Keep the terminal running; stop with Ctrl+C when finished.

| Role | Main workspace |
|---|---|
| Receptionist | Patients, all doctors' appointments, arrivals, queue priorities, waitlist, rescheduling; language and appointment-duration settings. |
| Doctor | Shared patient management; own appointments/current patient, clinical history, prescriptions, examinations/documents, statistics/exports; clinic hours/type and staff creation. |

Dashboard shows today's work; **Today** opens today's appointments, **Waiting room** opens arrival/priority management. Receptionists can filter by doctor. Use date/status/mode filters to find other visits; clear filters before assuming an appointment is missing.

Both roles read the same database. After changing something in one browser, **refresh the other browser** to see it. There is no cross-browser live push.

## 4. Register, find and correct a patient

1. Open **Patients**, search by name, phone, email or identifier. In pediatrics, guardian details are also searchable. Check identity before creating a duplicate. Search-driven lists may initially ask for a filter rather than show all records.
2. Choose **Add New Patient**. Standard practice requires first/last name, phone and unique national ID; complete other relevant fields, allergies and conditions.
3. In **Pediatrics**, register the **child**, enter their birth date (newborns supported), then guardian name, relationship and phone. Guardian email is optional. Add up to five contacts; select exactly one primary contact. National ID is optional. Siblings can share guardian contact details.
4. Save. The patient profile confirms registration. To correct details, open the profile → **Edit** → change → Save. Update the primary guardian through that form; the child's identity remains separate.

The creation form also offers immediate appointment booking. Enable it, complete the offered booking fields and submit once. If booking fails, correct the form; this combined operation should not leave a separately created patient. For a deliberate outside-hours exception, use the full appointment form described below.

## 5. Book, receive and manage visits (both roles)

### Make a booking

1. **Appointments → Schedule Appointment** (or the header's New Appointment shortcut).
2. Search for the patient and **click the matching suggestion**. Typing a name alone does not confirm a patient; changing the text requires selecting again. Verify the identity/guardian displayed.
3. Receptionist: choose the doctor. Doctor: the visit belongs to your account.
4. Select date, complete the mode-specific fields, reason and optional notes → Save. Verify the saved patient, doctor, date/time or queue number.

| Mode | How to operate it |
|---|---|
| Scheduled | Choose a time. Automatic suggestions respect working days/hours, duration and occupied slots. Accept a suggested replacement only if you want that date/time. |
| Queue | Book for the chosen day; the system assigns a ticket and estimated time. The ticket identifies the visit; the estimate is not a guaranteed fixed appointment. A configured daily limit can prevent further bookings. |

Each doctor changes **their own new-booking mode** in Settings → appointment mode → Scheduled/Queue → optional queue limit → Save. Existing visits retain their original mode. Thus the daily appointment list and today's waiting/priority view can show different subsets; changing mode does not migrate old visits.

**Intentional exception:** on the full booking/edit form expand **Manual scheduling exception**, tick its confirmation and optionally enter a reason → Save. This permits the supported overlap/outside-hours/manual walk-in exception while retaining your chosen time. It does not bypass patient identity or every validation rule. If the patient already has an active visit with that doctor that day, edit/reschedule that visit instead of duplicating it.

### Arrival, priority and status

1. Open today's appointment or **Queue / Waiting room**; receptionist selects the relevant doctor.
2. When the patient actually arrives, click **Check in**. Arrival applies to today's scheduled-status visits. It does not start the consultation.
3. Use priority up/down controls to change calling order. Arrived patients are normally called first. Reordering a scheduled visit changes priority, **not its booked time**; queue tickets keep their identities while estimates are recalculated.
4. Use **Set current / Switch to this patient** to begin a consultation. This makes that doctor's visit **In Progress**. Handle the previous current patient as described in section 6.

| Action | Use and result |
|---|---|
| Reschedule | Appointment → Reschedule → select the exact date/time in Edit → Save. Verify the resulting status too, especially when reopening a cancelled/no-show visit. |
| Quick reschedule | Scheduled-status, fixed-time visit → Quick reschedule → confirm. Finds the earliest eligible free slot from today, searching up to two weeks; it may move earlier or to another day. Read the resulting date/time. If already earliest, nothing changes. Use manual Reschedule for a specific date. |
| No Show | Patient did not attend a due visit: use the appointment's no-show/status action. Future visits cannot be marked absent through the dedicated action. Do not use this for a consultation that already happened. |
| Cancel | Appointment → Cancel → confirm. Retains the cancelled booking/history; it is different from deleting the record. |
| Correct status | Use the row's status control or appointment Edit, choose the intended status and Save. Clinical/transition checks still apply. Refresh related views afterward. |

### Waitlist is different from waiting room

**Waitlist** means “needs an appointment opening,” not “has arrived.” Open Waitlist → select the patient and doctor → optional preferred date/reason → Add. Use its booking action to create a visit: scheduled mode searches for an opening; queue mode allocates a ticket. Check the resulting booking details. Remove/cancel the waitlist entry if no longer needed. Adding someone to this list does not check them in or make them current.

## 6. Doctor: run a consultation from start to finish

1. Open **Current Patient**. If empty, use **Call next patient**, or pick a visit from today's queue. Verify the patient's name, age, allergies and guardian before entering notes.
2. **Consultation tab:** enter visit date/type, complaint, assessment/diagnosis, treatment and notes. Expand additional details for vitals, examination and specialty fields. Respect displayed units; assigning a unit to a historical temperature does not convert its number.
3. Click **Save consultation & stay**. Wait for the success/saved state. Diagnosis is optional at this stage. This saves the record, not the end of the visit.
4. **History tab:** review earlier visits; View opens the full record. Save current drafts before leaving. The return-to-active-patient link brings you back to the active consultation.
5. Use Prescription, Examinations and Documents tabs as needed (section 7). Switching tabs keeps current form values on that page, but **does not save them**. Saving one form does not save all the others. Use Review unfinished work when prompted; Discard explicitly abandons the named drafts.
6. **Schedule a follow-up:** enter an amount and days/weeks/months, optional reason → schedule. The app finds an eligible working date/slot (or queues the visit under your current mode). Read the actual date/time in the confirmation. This creates a future visit without completing today's one.
7. Save remaining work → **Complete Visit & Next Patient** → review the dialog → confirm. Today's visit becomes Completed and the next eligible visit becomes current. With nobody waiting, it simply completes the visit.

### Switching to a different patient before finishing

Click **Switch to this patient** on the upcoming list. The dialog asks what happens to the previous current visit:

| Choice | Effect |
|---|---|
| Completed | Close the previous visit. |
| Waiting | Return it to waiting; do not mark it done. |
| Reschedule | Enter the replacement date/time and confirm. |
| No Show | Mark absent; unavailable when the previous visit already has a medical record. |
| Cancelled | Cancel the previous visit. |

Confirm the intended choice to start the selected patient, or choose the stay/cancel option to keep the current one. Save your drafts before switching. These choices manage appointments; they are not a replacement for saving clinical notes.

## 7. Doctor: prescribe, request tests, attach results and correct work

### Prescription

1. Current Patient → **Prescription**. If the consultation is not saved yet, choose **Save notes & open prescription**, or return to review notes. You do not need a final diagnosis to proceed.
2. Enter prescribed date and at least one medication: **name, dosage and frequency are required**; duration in days and instructions are optional. Add another medication or remove an unwanted row.
3. Suggestions reuse your previous medication names; they are not a verified drug catalogue. Manual names and prescribing fields remain editable.
4. Click **Create Prescription**, then View/Print the saved prescription. In the print dialog select a printer or Save as PDF.
5. To correct it later: **Prescriptions** → patient and/or date-range filter → Search → View → Edit → change the medication rows/notes → Update → reopen/print the corrected version. An earlier exported/printed copy is not automatically updated.

You can also create a prescription from the separate Prescriptions page. Select the patient and, when linking a consultation, choose a record belonging to that same patient. A mismatch is rejected: correct the selection and resubmit; do not change patients merely to clear an error.

### Examination request and result

1. **Examinations** → Laboratory or Imaging/examination → choose a suggestion or type a name → optional instructions → Add examination request.
2. Print the saved request. Later select Requested, Completed or Cancelled beside it → **Save**. Uploading a result and changing request status are separate actions.
3. **Documents** → descriptive title → choose PDF/JPEG/PNG up to **10 MB** → optionally select the related examination → Upload.
4. Check the saved title/association; View opens the file, Download saves it. If preview fails, use Download. On a rejected upload, correct the file and select it again; browsers cannot restore a local file selection for you.

### Revisit a previous consultation without disturbing today's patient

**Medical Records** → enter patient and/or date range → Search → View → Edit → Save. For past requests/results, expand the record's **Examinations and documents** section or use View all examination requests/documents. Check the **Historical record** number/date before adding anything. The patient profile also provides clinical history/tools. Historical tools remain editable where supported; they do not make that patient current.

## 8. Reports, exports and deletion

- **Statistics (doctor):** open from the sidebar; review appointment/patient/clinical summaries, select the trend timeframe, and use Refresh for current figures.
- **Export (doctor):** select at least one dataset (patients, appointments, medical records, prescriptions or staff), optional start/end dates → Export → save the CSV. End must not precede start; export date filters cannot be in the future. Open/import the file as UTF-8 in your spreadsheet app. CSV is not a complete database/documents backup.
- **Deletion:** use Edit for corrections and Cancel for a cancelled appointment. Doctor patient deletion removes related appointments/clinical records/prescriptions through database relationships. There is no normal trash/undo flow to rely on. Verify identity and backups before any Delete confirmation. Receptionists cannot delete patient records.

## 9. Settings and a clean real-clinic installation

| Setting | Where / effect |
|---|---|
| Clinic information | Doctor → Settings → clinic name, address, phone, optional email/website → Save. Used on official/printed documents. |
| Working hours | Doctor → Settings → enable working days, start/end times → Save. Automatic booking suggestions follow them. Demo 24-hour override takes precedence until disabled. |
| Duration | Settings → appointment duration → Save. Used for booking slots and queue estimates. |
| Practice type | Doctor → Settings → select profile → Save. General keeps shared forms; pediatrics adds child/guardian and development data; dentistry/cardiology add optional notes. Review “apply defaults” before enabling it: it can change duration and your mode. Existing clinical history remains. |
| Staff | Doctor → Settings → Add User → name, unique email, role, password and confirmation → register. Doctors start with clinic default booking settings; receptionists manage all doctors' operational work. |
| Language | Header language menu or Settings → English/French. Staff changes also update the clinic default. |

For a **clean clinic**, use a separate empty database in `.env` → `php artisan optimize:clear` → `php artisan migrate` → open the site → choose **Production**. Complete Mode/practice, Clinic, Workflow (hours, duration, Scheduled/Queue and optional limit), then Team (primary doctor and optional extra doctors/receptionists) → Finish setup. You enter as the primary doctor. Use the credentials you created; demo accounts/data are not added. Production here is a setup choice, not an automatic secure hosting deployment.

## 10. Optional reminders and AI

Core patient/appointment/clinical work needs neither feature.

- **Reminders:** configure working SMTP values (`MAIL_MAILER=smtp`, host, port, username, password, sender and provider-required transport settings) in `.env`; run `php artisan optimize:clear`. Keep `php artisan schedule:work` running in another local terminal, or configure the host scheduler for deployment. At 17:00 it emails tomorrow's scheduled patients; pediatrics uses the primary guardian email. The default `MAIL_MAILER=log` only writes messages to logs, not inboxes.
- **Past-day cleanup:** the same scheduler runs at 00:30. Past unarrived scheduled visits without consultation records become no-show; past unfinished In Progress visits become Completed. Arrived-but-unseen patients remain for staff review. Without the scheduler, do not expect these automatic changes.
- **AI summary:** add `GROQ_API_KEY` in `.env` → clear configuration → doctor opens patient profile → AI Summary. It sends clinical context to Groq; use synthetic data for demonstration. Without a configured/reachable service, summary generation can fail while the rest of the app still works.

## 11. Updates, tests, backups and demo reset

**Normal daily startup:** start MySQL and `php artisan serve`; no migrations or reset needed. Compiled assets work without `npm run dev`. Use `npm run build` after frontend updates; `npm run dev` is for editing with live asset reload.

**Update an existing installation:** back up database and `storage/app/private`, keep `.env`/`APP_KEY`, then run in the project folder:

```powershell
git pull origin variations
composer install
npm ci
php artisan optimize:clear
php artisan migrate
npm run build
```

**Tests:** `php artisan test` uses in-memory SQLite; latest verified run was **363 passed**. For a quick browser acceptance run: receptionist creates a synthetic patient → book/check in → doctor saves notes → prescribe/print → request/upload a result → follow-up → complete/next → receptionist refreshes and verifies statuses. Also try one rejected booking and correct it. Automated tests do not replace that visible flow.

**Backup:** export the database using your database manager and copy `storage/app/private` to the same dated backup. Keep `.env`/application key securely. Restore both database and files to a separate test installation to verify recovery. CSV alone is insufficient.

**Restart the wizard on a disposable demo only:** confirm `.env` points to the demo database, then:

```powershell
php artisan optimize:clear
php artisan migrate:fresh
```

This **deletes all tables/data in that database**. Reopen the site, select practice/Demo/24-hour option and launch. Do not add `--seed`: seeding creates staff and closes setup, which takes you straight to Login. For another specialty/demo, reset this disposable database or point the installation at a different empty one. Never fresh-migrate a real clinic.

## 12. If something blocks you

| Symptom | What to do |
|---|---|
| Database connection refused / unknown database | Start MySQL, check `.env` host/port/name/credentials; create the named database, clear config and run migrations. |
| Site unavailable / port in use | Keep the PHP server terminal open. Stop an old server or use `php artisan serve --port=8001`; open that exact port and update `APP_URL` if keeping it. |
| Login instead of setup | Installation is already configured. Use existing credentials; reset only a disposable demo as above. `migrate` alone does not erase setup. |
| Plain/broken layout / Vite manifest error | Run `npm ci` and `npm run build`. If a stopped Vite dev server left `public/hot`, remove that file and refresh. |
| Page expired (419) | Session/form expired: reopen/login and retry. Unsaved drafts may need re-entry. |
| Unauthorized (403) | Check signed-in role and consultation ownership. Receptionists cannot open clinical/private-file pages. |
| Search displays nothing | Apply a patient/date filter, confirm suggestion selection, check date/status/doctor/mode filters. Medical-record/prescription lists start with search prompts. |
| No slot / clinic closed / queue full | Check hours, chosen doctor/mode and queue limit. Demo: enable 24-hour option. Choose another slot/day, or use the intentional manual exception where offered. |
| Patient changed but wrong suggestion remains | Select the intended patient again after editing search text; verify the displayed identity before saving. |
| Validation error | Read the message, correct the highlighted input, verify retained selections and submit once. Save each workspace form separately. |
| Saved change absent in another browser | Refresh that browser; verify it uses the same app URL/database and relevant filters. |
| Upload rejected | Use an allowed file under 10 MB; choose it again. Server upload limits must exceed the app limit (e.g. PHP `upload_max_filesize=12M`, `post_max_size=16M`); restart the relevant PHP server after changing its configuration. |
| Unexpected server error | Note the action/time; inspect `storage/logs/laravel.log`. After an update run `php artisan optimize:clear`. Do not reset the database to fix an ordinary error. |

For a short client demonstration, run the acceptance flow in section 11 with two separate browser sessions and the 24-hour demo option enabled. This shows a complete visit rather than a tour of disconnected screens.
