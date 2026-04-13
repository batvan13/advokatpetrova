# PETROVA — PAYMENT RULES (SOURCE OF TRUTH)

## PURPOSE

This document defines all business rules required before integrating payment providers.
It MUST be treated as the single source of truth for all payment-related logic.

No implementation should contradict this document.

---

## 1. PAYMENT STATUS MODEL

Payment statuses:

- pending
- paid
- failed
- refunded

Rules:

- booking is VALID only if payment_status = paid
- pending/failed bookings are NOT considered confirmed

---

## 2. BOOKING FLOW (PAYMENT-READY)

Flow:

1. user selects service / slot
2. system creates booking with payment_status = pending
3. user proceeds to payment
4. on success → payment_status = paid
5. on failure → payment_status = failed

---

## 3. PENDING EXPIRATION

- pending booking expires after 15 minutes
- expired bookings MUST release the slot
- expired bookings MUST NOT block availability

---

## 4. BOOKING VALIDITY

- ONLY paid bookings:
  - appear as confirmed
  - block calendar slots
  - trigger consultation logic

---

## 5. CANCEL POLICY

### Phone / Viber / Chat

- cancel allowed ≥ 24h before start
- cancel < 24h → no refund

### Written consultation

- no cancel after payment
- no refund once work is started

---

## 6. RESCHEDULE POLICY

Applies to: phone, viber, chat

- allowed only ≥ 24h before start
- max 1 reschedule per booking
- payment remains valid (no new payment)
- new slot must pass availability + conflict checks

Not allowed for written consultation

---

## 7. NO-SHOW POLICY

Definition:

Client does not appear within 10 minutes after start time.

Applies to:
- phone (no call)
- viber (no connection)
- chat (no join)

Result:

- booking_status = missed
- NO refund
- NO automatic reschedule

---

## 8. LAWYER-SIDE FAILURE

If consultation cannot be completed due to system/admin side:

Client options:
- reschedule (same payment)
- full refund

---

## 9. LATE ARRIVAL

### Client late:

- consultation runs in remaining time
- end time does NOT shift

### Lawyer late:

- ≤10 min delay → continue
- >10 min delay → client chooses:
  - reschedule
  - refund

---

## 10. BOOKING CONSTRAINTS

- booking horizon: 14 days
- minimum advance time: 24 hours

---

## 11. SLOT MODEL

- slot size: 30 minutes
- shared availability across all consultation types
- Google Calendar = source of truth
- no buffer between slots

---

## 12. CONSULTATION RULES

### Phone

- client initiates the call

### Viber

- client initiates the connection

### Chat

- system-controlled session
- 30 minutes
- read-only after completion

### Written

- async
- SLA: 48 hours
- max 5 files
- max 10MB per file
- allowed formats:
  - PDF
  - DOC
  - JPG
  - PNG

---

## 13. NOTIFICATIONS

After successful payment:

User receives:
- confirmation email
- instructions (based on consultation type)
- date/time details

Optional (recommended):
- reminder 24h before
- reminder 1h before

---

## 14. ADMIN STATUS MODEL

Booking status:

- scheduled
- completed
- missed
- cancelled
- archived

Payment status:

- pending
- paid
- failed
- refunded

---

## 15. IMPORTANT RULE

DO NOT IMPLEMENT PAYMENT PROVIDERS BEFORE:

- all rules in this document are enforced in code
- booking behavior matches these rules 100%

This document defines system behavior BEFORE payment integration.