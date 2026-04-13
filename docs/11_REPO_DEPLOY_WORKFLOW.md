# PETROVA — REPOSITORY DEPLOY WORKFLOW (SUPERHOSTING)

## PURPOSE

This document defines how the project is deployed to production
using cPanel Git Version Control.

This is the ONLY allowed deployment workflow.

No manual random file uploads are allowed.

---

## 1. DEPLOYMENT MODEL

We use:

- Git repository (GitHub)
- cPanel Git Version Control
- manual deployment trigger from cPanel

This ensures:
- reproducibility
- clean updates
- version control

---

## 2. INITIAL SETUP (ONE TIME)

### Step 1 — Open cPanel → Git Version Control

### Step 2 — Create repository

- Clone repository from GitHub
- Use HTTPS or SSH depending on access
- Select correct branch (main)

### Step 3 — Choose deploy directory

IMPORTANT:

- DO NOT deploy directly into public_html root blindly
- Project must be placed in a subdirectory

Example:

/home/username/petrova

---

## 3. PUBLIC ROOT CONFIGURATION

Laravel structure requirement:

- ONLY /public folder must be publicly accessible

### Correct structure:

/home/username/petrova       ← Laravel project  
/home/username/public_html   ← public root  

---

## 4. REQUIRED STRUCTURE FIX

Because shared hosting does not allow changing root easily:

### Option A (recommended):

Copy contents of:

/petrova/public

into:

/public_html

AND modify index.php paths:

```php
require __DIR__.'/../petrova/vendor/autoload.php';
$app = require_once __DIR__.'/../petrova/bootstrap/app.php';