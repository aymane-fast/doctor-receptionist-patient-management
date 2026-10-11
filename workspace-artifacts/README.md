# Workspace artifacts

Everything generated for presentations, documentation and audit work is collected here. No original file was deleted. The application source, dependencies, `.env`, runtime storage, database migrations/tests and main project README remain outside this folder.

## Quick access

- [English presentation](outputs/app-presentation-v4/deliverables/Clinic-Appointment-Manager-Complete-Showcase.pptx)
- [French presentation](outputs/app-presentation-fr/deliverables/Presentation-Complete-Cabinet-Medical-FR.pptx)
- [Draggable database diagram](outputs/platform-blueprint/index.html)
- [App flow / backlog](outputs/platform-blueprint/backlog.html)
- [Latest audit report](docs/CLIENT_DEMO_SECOND_PASS_2026-10-11.md)
- [First audit report](docs/CLIENT_DEMO_AUDIT_2026-10-11.md)

## Contents

| Folder | Contents |
| --- | --- |
| `outputs/` | All presentation versions, previews, generators, diagrams, audit scripts/results/screenshots, synthetic databases and SQL backups; existing structure retained |
| `output/` | Generated prescription test PDF |
| `docs/` | Practice notes, audit reports and historical checklist |
| `guides/` | AI setup, quick-start and visual guides |
| `diagrams/` | Former root-level database HTML files |
| `experiments/` | Former root-level modal test HTML |
| `tmp/` | Former temporary folder |
| `empty-original-folders/` | Empty original directories preserved after resuming the interrupted move |

`move-manifest.csv` records every original/new path, byte count and SHA-256 digest. All **1,060 preserved files** were verified against the move baseline, including files already relocated before the interrupted move was resumed. `organize-workspace.ps1` records the move procedure; it refuses file overwrites, supports resuming partially moved directories, and is not an application dependency.

Generated assets, databases, backups and the manifest remain ignored by Git. Previously tracked reports/guides/HTML remain tracked at their new paths. Removing this folder later does not remove application code; it does remove these local artifacts, so keep any presentation or backup you still need.

Historical reports and archived generator/audit scripts may mention original paths such as `outputs/client-audit/`. Their files now live under this folder's `outputs/client-audit/`; archived scripts may need path updates before being rerun. Relative links inside each preserved presentation/diagram folder retain their structure.
