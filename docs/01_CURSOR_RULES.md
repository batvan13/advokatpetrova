# Cursor execution rules

## Role

**Cursor = executor.**  
Architecture and product trade-offs belong to the human architect / reviewer unless a task delegates them explicitly.

---

## Non-negotiable rules

- **No refactor** of code not mentioned in the task.  
- **No scope creep** (no “while we’re here” changes).  
- **No new Composer or NPM packages** without explicit approval in the task.  
- **No design / UX decisions** (colors, layout, copy) unless the task says so.  
- **Follow `docs/00_PROJECT_CONTEXT.md`** and numbered docs (`03`–`05`).

---

## Forbidden actions

- Inventing features not in the task.  
- Rewriting working patterns to a “cleaner” architecture without ask.  
- Touching gallery / inquiry / auth flows when the task does not include them.  
- Removing validation, security (CSRF, throttle), or persistence without explicit instruction.

---

## Required behavior

- Prefer **minimal diffs**; match existing project style.  
- If the task is **unclear** or requires an **architectural fork**, **stop** and ask for clarification before large edits.  
- After implementation, report in a **fixed structure** (see Output format).

---

## Output format (mandatory for completed tasks)

1. **Files changed** (paths).  
2. **What was implemented** (factual, concise).  
3. **Flow / logic** (how it behaves).  
4. **Risks** (leftovers, edge cases).  
5. **Manual test checklist** (concrete steps).

---

## Scope discipline

- Implement **only** what the task lists.  
- If a bug blocks progress, **report** it; do not expand scope to “fix the world.”

---

## When to stop and ask

- Ambiguous requirements.  
- Conflict between task and `docs/00_PROJECT_CONTEXT.md`.  
- Any decision that **changes stack**, **data model**, or **public API** without explicit approval.

---

## Commit discipline

- One logical change per commit when possible.  
- Commit message states **what** and **why** in plain language.  
- Do not mix unrelated fixes.

## ENFORCEMENT

Before any implementation:

1. Read:
   - docs/00_PROJECT_CONTEXT.md
   - docs/01_CURSOR_RULES.md

2. Confirm:
   - task is within scope
   - no architecture change is required

If violation is detected:
→ STOP and ask

Violation examples:
- adding new dependency
- changing data structure
- modifying existing flow outside task

## PAYMENT IMPLEMENTATION

Before implementing any payment logic:

1. Read:
   → docs/07_PAYMENT_RULES.md

2. DO NOT:
   - invent rules
   - change policies
   - assume behavior

3. MUST:
   - follow rules exactly
   - implement constraints as defined
