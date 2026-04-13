# PETROVA — PRODUCTION SMOKE TEST

## PURPOSE

This document defines the exact smoke-test sequence to run immediately after
production deployment and configuration.

It is used to verify that the public system works end-to-end before:
- real operational usage
- real client testing
- payment-provider integration

This is not a full QA plan.
This is the minimum required production verification.

---

## 1. TEST RULES

- Run tests on the real public domain
- Use production-like data only
- Do NOT mix tests with unfinished development
- Record PASS / FAIL for every test
- If a critical test fails, stop and fix before continuing

Critical failures include:
- booking creation fails
- admin cannot access records
- emails do not arrive
- Google Calendar sync fails
- uploads fail
- wrong public URLs appear
- status logic breaks

---

## 2. PRE-TEST CONDITIONS

Before starting smoke tests, confirm:

- deployment is complete
- production .env is reviewed
- admin account works
- mail is configured
- Google Calendar is configured
- storage is working
- public domain is correct
- SSL works if expected

---

## 3. CORE PUBLIC SITE TEST

### Test 3.1 — Homepage
Verify:
- homepage loads
- layout is correct
- assets load
- no broken styling
- no fatal error

### Test 3.2 — Main consultation page
Verify:
- consultation page loads
- all consultation options are visible
- no broken links
- no placeholder/local/dev text remains

PASS only if both pages work correctly.

---

## 4. ADMIN ACCESS TEST

### Test 4.1 — Admin login
Verify:
- admin login works
- session persists
- dashboard loads

### Test 4.2 — Protected access
Verify:
- admin pages are not publicly accessible without login

PASS only if admin auth works correctly.

---

## 5. PHONE BOOKING TEST

Create one real public phone booking.

Verify:
- booking form loads
- submission succeeds
- success page loads correctly
- public URL is correct
- booking exists in admin
- client email is received
- admin notification is received (if expected)
- Google Calendar event is created
- google_event_id is stored
- google_sync_status = synced

PASS only if the full flow works end-to-end.

---

## 6. VIBER 30-MIN BOOKING TEST

Create one real public Viber booking (30 min).

Verify:
- duration selection works
- slot selection works
- submission succeeds
- success page works
- booking appears in admin
- email arrives
- Google Calendar event is created correctly
- event timing is correct
- sync status is correct

PASS only if the full flow works end-to-end.

---

## 7. VIBER 60-MIN BOOKING TEST

Create one real public Viber booking (60 min).

Verify:
- duration selection works
- booking saves correctly
- end time/duration is correct
- admin shows correct data
- email arrives
- Google Calendar event is correct

PASS only if duration-specific logic is correct.

---

## 8. CHAT BOOKING TEST

Create one real public chat booking.

Verify:
- booking succeeds
- success page works
- booking exists in admin
- email is received
- Google Calendar event is created
- chat session bootstrap exists
- chat page is reachable as intended
- initial chat state is correct

PASS only if booking + session bootstrap both work.

---

## 9. WRITTEN CONSULTATION TEST

Create one written consultation with attachments.

Verify:
- form loads
- upload works
- allowed files work
- booking/submission succeeds
- success page works if applicable
- admin record exists
- client email is received
- admin can review submission
- files are retrievable correctly in admin flow

PASS only if attachments and admin review both work.

---

## 10. ADMIN LIFECYCLE TEST

Using test bookings, verify:

### Phone / Viber / Chat
- show page opens
- complete action works where applicable
- archive action works where applicable
- archived records no longer appear in index if that is expected
- direct show page access still works for archived record if designed so

### Written
- admin can open and review submission
- archive behavior works if applicable

PASS only if status transitions behave correctly.

---

## 11. EMAIL CONTENT TEST

Verify emails for at least:
- phone booking
- viber booking
- chat booking
- written consultation

Check:
- subject is correct
- no localhost links appear
- date/time is correct
- instructions are correct for consultation type
- public URLs are correct
- message content is readable and appropriate

PASS only if email content is production-appropriate.

---

## 12. GOOGLE CALENDAR TEST

Verify directly in Google Calendar:

- phone event exists
- viber 30 event exists
- viber 60 event exists
- chat event exists
- event times are correct
- calendar is the intended one
- no duplicate event is created unexpectedly

PASS only if calendar behavior matches admin/system data.

---

## 13. URL / DOMAIN TEST

Verify:
- all public pages use real domain
- success pages use real domain
- email links use real domain
- redirects do not point to localhost
- no mixed environment URL appears

PASS only if no local/dev URL leakage exists.

---

## 14. FILE / STORAGE TEST

Verify:
- uploaded written-consultation files exist correctly
- file links work as intended
- private/public access behavior is correct
- no storage path error appears

PASS only if storage works safely and correctly.

---

## 15. FAILURE CHECKS

At minimum, verify these negative cases:

### Test 15.1 — Invalid form submission
- validation errors appear correctly
- no broken state occurs

### Test 15.2 — Protected admin route without login
- access is denied correctly

### Test 15.3 — Wrong/nonexistent public token page
- response is handled safely
- no fatal error occurs
- no sensitive data leaks

PASS only if failure handling is safe.

---

## 16. SMOKE TEST RESULT

Production smoke test is PASS only if:

- public pages work
- admin works
- phone booking works
- viber 30 works
- viber 60 works
- chat booking works
- written consultation works
- emails work
- Google Calendar works
- uploads work
- lifecycle actions work
- no localhost leakage exists
- no fatal production error appears

If any critical test fails:
- stop
- fix the issue
- re-run affected tests

Do NOT proceed to payment integration before smoke test PASS.