# PETROVA — PRODUCTION ENV CHECKLIST

## PURPOSE

This document defines the required production environment configuration
for the PETROVA project before and immediately after first deployment.

It is a practical checklist, not a theory document.

Use it to verify that production is configured safely and correctly.

---

## 1. CORE APPLICATION

### Required
- APP_NAME is correct
- APP_ENV=production
- APP_DEBUG=false
- APP_URL uses the real public domain
- APP_TIMEZONE=Europe/Sofia
- APP_KEY is present and valid

### Verify
- no localhost URLs remain
- no local dev configuration remains active
- no debug output is visible publicly

---

## 2. DATABASE

### Required
- DB_CONNECTION is correct
- DB_HOST is correct
- DB_PORT is correct
- DB_DATABASE is correct
- DB_USERNAME is correct
- DB_PASSWORD is correct

### Verify
- application can connect successfully
- migrations run correctly
- reads/writes work
- production database is not the local database
- no test data is accidentally mixed into production unless intentional

---

## 3. SESSION / CACHE / QUEUE

### Required
- SESSION_DRIVER is intentionally chosen
- CACHE_STORE / CACHE_DRIVER is intentionally chosen
- QUEUE_CONNECTION is intentionally chosen

### Verify
- session persists correctly after login
- admin login does not break unexpectedly
- cache does not serve stale/broken content
- queue strategy is understood:
  - sync
  - database
  - redis
  - other

### Important
Do not leave these values unreviewed.
Bad session/cache/queue values cause confusing production-only bugs.

---

## 4. MAIL

### Required
- MAIL_MAILER is correct
- MAIL_HOST is correct
- MAIL_PORT is correct
- MAIL_USERNAME is correct
- MAIL_PASSWORD is correct
- MAIL_ENCRYPTION is correct if required
- MAIL_FROM_ADDRESS is correct
- MAIL_FROM_NAME is correct

### Verify
- client confirmation emails arrive
- admin notifications arrive
- no localhost links appear in emails
- no Mailtrap/test-only settings remain in production

---

## 5. FILE STORAGE

### Required
- filesystem/storage configuration is reviewed
- public storage access strategy is correct
- upload destination is correct

### Verify
- storage/app paths are usable
- public file URLs resolve correctly
- written consultation attachments upload successfully
- generated files can be accessed as intended
- no broken symlink exists

---

## 6. GOOGLE CALENDAR

### Required
- GOOGLE_CALENDAR_ENABLED has the intended value
- GOOGLE_CALENDAR_ID is correct
- GOOGLE_SERVICE_ACCOUNT_JSON points to the real production file
- GOOGLE_CALENDAR_TIMEZONE=Europe/Sofia

### Verify
- JSON credentials file exists on the server
- file path is valid on production server
- calendar is shared with the service account
- service account has correct permissions
- one real booking creates one real event

### Important
Do not reuse local paths blindly.
Production file paths are often different.

---

## 7. SECURITY / PUBLIC SAFETY

### Required
- APP_DEBUG=false
- production secrets are only in .env
- no private keys are committed to the repository
- service account JSON is NOT exposed publicly
- admin credentials are production-safe

### Verify
- .env is not publicly accessible
- storage/private files are not public unintentionally
- no stack traces are shown publicly
- no development test accounts remain active unless intentional

---

## 8. URL / DOMAIN

### Required
- APP_URL matches the real public domain exactly
- HTTPS is used if SSL is enabled
- route generation uses the real domain

### Verify
- success pages use the correct domain
- email links use the correct domain
- redirect flows do not point to localhost
- asset URLs load correctly

---

## 9. CRON / SCHEDULER

### Required
- cron availability is confirmed
- Laravel scheduler strategy is defined

### Verify
- scheduled tasks can run
- future expiration/reminder/archive logic can rely on scheduler safely

### Important
If the system depends on expiration/reminder flows later,
scheduler must be working before those features go live.

---

## 10. QUEUE WORKER (IF USED)

### Required
- queue strategy is documented
- worker strategy is documented if async jobs are used

### Verify
- jobs are processed correctly
- failed jobs are visible
- email/calendar-related jobs do not silently stall

---

## 11. ADMIN USERS

### Required
- production admin account exists
- password is production-safe
- access is tested

### Verify
- admin login works
- admin logout works
- protected routes are inaccessible publicly without login

---

## 12. LEGAL / CONTENT READINESS

### Required
- contact details are correct
- consultation descriptions are correct
- public consultation instructions are correct
- legal/informational pages are ready enough for public visibility

### Verify
- no placeholder text remains
- no fake phone/email remains
- no contradictory consultation instructions remain

---

## 13. FIRST POST-DEPLOY CHECK

Immediately after deployment verify:

- homepage loads
- consultation pages load
- admin login works
- booking submission works
- email sending works
- Google Calendar sync works
- file upload works
- no fatal production error appears

---

## 14. HARD BLOCKERS

Deployment is NOT acceptable if any of the following is true:

- APP_DEBUG=true
- APP_URL is incorrect
- mail is not working
- admin login fails
- Google Calendar sync is broken (if expected live)
- uploads are broken
- localhost links appear publicly
- production secrets are missing
- .env values are not reviewed

---

## 15. FINAL RULE

Production is considered ready for real operational testing only when:

- all critical env values are verified
- no local/test values remain unintentionally
- one full booking flow works publicly
- emails work
- Google Calendar works
- admin operations work