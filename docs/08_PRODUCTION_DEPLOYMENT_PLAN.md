# PETROVA — PRODUCTION DEPLOYMENT PLAN

## PURPOSE

This document defines the exact deployment sequence for moving the PETROVA project
from local development to the public server.

It MUST be followed strictly.

Goal:
- stable public deployment
- correct production configuration
- safe operational testing
- readiness for later payment integration

This document is NOT for feature development.
This document is for deployment, configuration, and verification only.

---

## 1. DEPLOYMENT PRINCIPLES

- Do NOT introduce new features during deployment
- Do NOT mix deployment with payment integration
- Do NOT change business logic unless a blocker is discovered
- Use repository-based deployment only
- Production must be configured through .env and admin data, not hardcoded values

---

## 2. PRE-DEPLOY CHECKLIST

Before first public deployment, confirm:

### Codebase
- main branch contains the stable working version
- all local changes are committed
- all critical consultation flows are already tested locally
- Google Calendar sync is already verified locally
- no unfinished experimental code remains

### Environment readiness
- production domain is available
- SSL can be enabled
- server has PHP version compatible with the project
- Composer is available
- Node/Vite build strategy is known
- database credentials are available
- mail credentials are available
- cron availability is confirmed
- queue strategy is known (sync or worker)

### Data readiness
- real admin user plan exists
- site settings data is prepared
- consultation instructions/content is prepared
- legal/public informational pages are ready or nearly ready

---

## 3. FIRST DEPLOYMENT GOAL

The first deployment is successful only if:

- public site loads correctly
- admin login works
- assets load correctly
- storage-dependent features work
- emails can be sent
- Google Calendar sync works
- database writes work
- uploads work
- no fatal production error occurs

The goal is NOT feature expansion.
The goal is stable public operation.

---

## 4. DEPLOYMENT SEQUENCE

### Step 1 — Server preparation
Prepare:
- domain/subdomain
- SSL
- web root / document root
- PHP version
- database
- writable directories
- repository access method

### Step 2 — Pull repository code
- deploy the stable branch from the repository
- do NOT upload random local files manually
- keep deployment reproducible

### Step 3 — Production .env setup
Configure:
- APP_ENV=production
- APP_DEBUG=false
- APP_URL=real public URL
- database connection
- mail connection
- cache/session/queue settings
- Google Calendar production values
- any other service credentials required

### Step 4 — Laravel setup
Run required production setup steps:
- composer install
- key generation if needed
- migrations
- storage link
- cache/config/view route optimizations if appropriate

### Step 5 — Assets
- build and publish frontend assets correctly
- verify Vite/Tailwind output is loading on production

### Step 6 — Permissions
Confirm:
- storage is writable
- bootstrap/cache is writable
- uploaded files can be saved correctly

### Step 7 — Initial smoke verification
Check immediately after deployment:
- homepage
- consultation pages
- admin login
- admin dashboard
- form submissions
- email dispatch
- Google Calendar sync
- uploads

---

## 5. PRODUCTION .ENV REQUIREMENTS

The production environment MUST be reviewed carefully.

Critical values include:

- APP_NAME
- APP_ENV=production
- APP_DEBUG=false
- APP_URL
- APP_TIMEZONE=Europe/Sofia

- DB_*
- MAIL_*
- SESSION_*
- CACHE_*
- QUEUE_*

- GOOGLE_CALENDAR_ENABLED=true/false as intended
- GOOGLE_CALENDAR_ID
- GOOGLE_SERVICE_ACCOUNT_JSON
- GOOGLE_CALENDAR_TIMEZONE=Europe/Sofia

Rules:
- never reuse local values blindly
- never leave development mail values in production
- never expose debug mode in production
- never hardcode production secrets in code

---

## 6. GOOGLE CALENDAR PRODUCTION CHECK

Production verification requires:

- real service account JSON file present on server
- correct file path in production .env
- correct calendar ID
- calendar shared to service account
- one real public booking test proving:
  - event creation works
  - sync status becomes synced
  - admin display is correct

---

## 7. MAIL PRODUCTION CHECK

Production verification requires:

- outgoing email is configured with real mail provider
- confirmation emails arrive correctly
- no localhost URLs appear in emails
- success links use the real public domain
- booking notifications work for both client and admin

---

## 8. FILES / UPLOAD CHECK

Verify:
- written consultation attachments upload correctly
- files are stored correctly
- file size limits behave correctly
- file visibility/privacy is correct
- no broken storage path exists

---

## 9. ADMIN / OPERATIONS CHECK

Verify:
- admin can view bookings
- admin can open show pages
- admin can complete bookings where applicable
- admin can archive records
- Google sync status is visible
- no production-only route/middleware issue blocks admin usage

---

## 10. REAL-WORLD DRY RUN

After deployment and initial configuration, perform a real-world dry run:

### Mandatory dry-run tests
- Phone booking
- Viber booking (30 min)
- Viber booking (60 min)
- Chat booking
- Written consultation submission

For each test, verify:
- booking created
- email received
- admin sees record
- Google event created where applicable
- success page works
- public links use correct domain
- status transitions work

The dry run must be completed BEFORE payment integration starts.

---

## 11. WHAT MUST NOT HAPPEN DURING THIS PHASE

Do NOT:
- implement payment providers during deployment
- refactor working architecture unless required
- change multiple subsystems at once
- push untested fixes directly to production without review
- treat production as the testing environment for unfinished logic

---

## 12. SUCCESS CRITERIA

Production deployment phase is complete only if:

- public site is stable
- admin works
- emails work
- uploads work
- Google Calendar works
- consultation flows work publicly
- real dry-run tests pass
- configuration is documented
- system is ready for payment-provider integration

---

## 13. NEXT PHASE

Only after this document is completed successfully may the project proceed to:

- ePay / EasyPay integration analysis
- payment transaction flow implementation
- callback / status handling
- payment testing