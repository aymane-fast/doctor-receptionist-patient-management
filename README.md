# Setup & client demo

## 1. Install once (Windows / Laragon)

Have Git, PHP 8.2+, Composer, Node.js/npm and MySQL available in your terminal. Start MySQL in Laragon. Create an empty database called `doctor_apointment` using Laragon's database manager.

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

## 3. Present it in this order

| Step | Show |
|---|---|
| Reception | Search/create a patient; in pediatrics, enter the child and primary guardian. |
| Book | Choose doctor/date/time, create a visit, check arrival, reschedule or quick-reschedule. |
| Queue | Show waiting patients and priority controls. Doctor Settings selects Scheduled or Queue for new bookings. Old bookings keep their mode. |
| Doctor | Open Current Patient; save consultation notes, then use the workspace tabs for history, prescriptions and examinations. Diagnosis can be completed later. |
| Documents | Upload a PDF/JPEG/PNG (max 10 MB); optionally link it to an examination; view/download or print requests. |
| Finish | Schedule a follow-up; explicitly complete the visit and move to the next patient. Saving notes alone does not complete a visit. |
| Switching | Select another current patient and choose what happens to the previous visit. Keep waiting, complete, no-show, cancel or reschedule as offered. |
| Extra | Waitlist, patient/date filters, prescription printing, doctor statistics and CSV exports. Switch English/French in the language menu. |

- **Booking exception:** expand Manual scheduling exception and confirm to deliberately overlap or book outside hours. It keeps your chosen time.
- **Practice types:** General = shared workflow. Pediatrics = child + guardians/development notes. Dentistry/Cardiology = shared workflow with optional specialty notes, not dedicated charting/device systems.
- **Two browsers:** refresh the other browser after a change; cross-browser live push is not implemented.

## 4. Run again / reset / test

Every day: start Laragon MySQL, open the project terminal, run `php artisan serve --port=8000`. The compiled interface works without `npm run dev`. Rebuild with `npm run build` after frontend updates; `npm run dev` is for editing with live asset reload.

If you land on Login, setup is already complete (including after `migrate --seed`). To restart the wizard **on a disposable demo database only**:

```powershell
php artisan optimize:clear
php artisan migrate:fresh
```

**This deletes every table in the configured database.** Open the site again; do not add `--seed`. For a second demo/specialty, use another empty database or reset the disposable demo.

```powershell
php artisan test
```

Tests use an in-memory SQLite database. Latest verified run: **363 passed**.

## 5. Real clinic setup

Use a separate empty database → `php artisan migrate` → choose **Production** in the wizard → clinic/type → workflow/hours → doctor and receptionist accounts. No demo accounts/data are added. Configure real hosting separately before patient use; keep database + `storage/app/private` backups and preserve `APP_KEY`. Never fresh-migrate a real clinic.

Optional email reminders require working mail configuration and the scheduler; optional AI summaries require `GROQ_API_KEY`. Both are unnecessary for the core demo. Technical details: [TECHNOLOGY.md](TECHNOLOGY.md).
