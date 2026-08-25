# BM Forex Hub — Email Notification & Bulk Broadcast System

Production-grade, enterprise-scale **Email Notification & Bulk Broadcast Infrastructure** for **BM Forex Hub** (`bmforexhub.exchange`).

This extension adds real-time & bulk email communications, rate-limited OTP verification, multi-provider email abstraction, campaign scheduling, draft management, and asynchronous queue workers to BM Forex Hub while preserving 100% of the existing user interface, styling, routes, and trading features.

---

## Key Features

1. **Resend OTP System**:
   - 60-second client-side countdown timer on `register.php` and `forgot-password.php`.
   - Disables button during countdown and re-enables automatically after 60 seconds.
   - Enforces rate limits (maximum 5 resend requests per hour per email/IP).
   - Generates cryptographically secure 6-digit verification codes using `random_int(100000, 999999)`.
   - Immediately invalidates previous OTP codes and updates expiration (15 mins).
   - Returns clear user notifications (*"A new verification code has been sent to your email."*).

2. **Bulk Email Broadcast System**:
   - New **Bulk Notifications** tab added to the Admin Dashboard matching existing dark-theme aesthetics.
   - **Recipient Group Filtering**:
     - *All Users*
     - *Verified Users*
     - *Unverified Users*
     - *Active Users* (Logged in within last 7 days)
     - *Inactive Users*
     - *Selected Users* (Searchable multi-select modal)
   - **Dark-Theme WYSIWYG Editor**: Bold, Italic, Underline, Bullet Lists, Numbered Lists, Hyperlinks, Images, Alignment, Text Colors.
   - **Personalization Placeholders**: `{{firstName}}`, `{{lastName}}`, `{{email}}`, `{{username}}`, `{{registrationDate}}`, `{{unsubscribeLink}}`.
   - **Confirmation Modal**: Interactive prompt showing total recipient count before sending.

3. **Asynchronous Email Queue Worker**:
   - HTTP requests return instantly upon campaign dispatch.
   - Processes batch sizes of **50–100 emails** per execution cycle.
   - **Exponential Backoff**: Retries failed emails after 60s, 120s, 180s.
   - **Dead-Letter Handling**: Marks jobs permanently failed after 3 attempts.
   - **Memory Safe**: Automatic garbage collection (`gc_collect_cycles()`) after each batch.

4. **Multi-Provider Email Abstraction (`MailService`)**:
   - Single unified service configured via `.env`:
     - **SMTP / Hostinger Email** / cPanel / Gmail
     - **SendGrid** API (v3 `/mail/send`)
     - **Amazon SES** API (v2)
     - **Resend** API (`https://api.resend.com/emails`)
     - **Brevo** (Sendinblue) API (`https://api.brevo.com/v3/smtp/email`)
     - **Mailgun** API (`https://api.mailgun.net/v3/`)
     - Standard PHP `mail()` fallback.

5. **Responsive HTML Email Template**:
   - Branded dark/light template featuring BM Forex Hub logo, gold accents (`#D4A24C`), dark containers (`#10161F`), header, footer, support link, and HMAC-signed unsubscribe token.

6. **Drafts, Scheduling & History**:
   - **Drafts**: Save, Edit, Delete, Duplicate.
   - **Scheduled Campaigns**: Dispatch at a specified future date/time.
   - **Email History**: View past campaigns with delivered vs failed counts.
   - **Live Delivery Status**: Dynamic progress bar (0%–100%) with real-time statistics.

8. **Registered Contacts Directory & Export System**:
   - Sidebar link `Registered Contacts` located immediately below `Bulk Notifications`.
   - **Summary Stat Cards**: Total Registered Users, Verified Users, Users with Phone Numbers, Users with Email Addresses, Today's Registrations.
   - **Multi-Criteria Search & Filtering**: Filter by Full Name, Username, Email, Phone Number, Verification Status, Account Status, Date Range, Country.
   - **Bulk Selection & Actions**: Select individual, select current page, or select all matching filters (`482 Users Selected`).
   - **Quick Actions**: Copy Email, Copy Phone Number, Copy Both with toast notifications (`✓ Email copied!`).
   - **Export Engine**: Generates UTF-8 BOM CSV files and native Excel (.xlsx / XML Spreadsheet) files.
   - **Export Preview & Compliance Modal**: Record count preview, field selection checkboxes, and mandatory privacy compliance notice.
   - **Audit Trail**: Every export operation logs Admin ID, format, record count, timestamp, IP address, and User Agent in `contact_export_logs`. Viewable via the **Audit Logs** modal.

---

## File Structure

```
app/
 ├── Config/
 │      mail.php                    # Provider config & env loader
 ├── Services/
 │      MailService.php             # Unified provider abstraction
 │      EmailQueueService.php       # Asynchronous queue & campaign dispatching
 │      OtpService.php              # Cryptographically secure OTP generation & rate limiting
 │      EmailTemplateService.php    # Dynamic HTML template rendering & HMAC unsubscribe tokens
 ├── Workers/
 │      EmailWorker.php             # Background queue worker (batch: 50-100, exponential backoff)
 ├── Templates/
 │      notification.html           # HTML template for bulk broadcasts
 │      otp.html                    # HTML template for verification codes
admin/
 ├── api/
 │      notifications.php           # Admin notifications REST endpoint
 │      otp.php                     # Public/Admin OTP API handler
 ├── css/
 │      admin-email.css             # Supplementary dark-mode styles for WYSIWYG editor & modals
 ├── js/
 │      admin-email.js              # Frontend logic for Bulk Notifications UI, editor, drafts & history
 ├── index.php                      # Updated Admin Dashboard with Bulk Notifications tab
api/
 ├── admin/
 │   └── notifications/
 │       └── bulk-send.php          # Route bridge for POST /api/admin/notifications/bulk-send
unsubscribe.php                     # Public unsubscribe handler supporting HMAC tokens
supabase/
 └── email_system_schema.sql        # Database schema for Supabase / MySQL / SQLite
.env.example                        # Environment variables template
```

---

## Installation & Setup

### 1. Environment Configuration

Copy `.env.example` to `.env` in the root project directory:

```bash
cp .env.example .env
```

Edit `.env` to configure your email provider:

```ini
# Mail Driver: smtp, sendgrid, ses, resend, brevo, mailgun
MAIL_DRIVER=smtp

# Default Sender
MAIL_FROM_ADDRESS=info@admin.bmforexhub.exchange
MAIL_FROM_NAME="BM Forex Hub"

# Hostinger / Standard SMTP Settings
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=info@admin.bmforexhub.exchange
MAIL_PASSWORD=YourPasswordHere
MAIL_ENCRYPTION=ssl

# Enterprise API Keys (Optional based on MAIL_DRIVER)
SENDGRID_API_KEY=
RESEND_API_KEY=
BREVO_API_KEY=
MAILGUN_API_KEY=
```

### 2. Database Setup

- For **Supabase / PostgreSQL / MySQL**: Run the SQL statements in `supabase/email_system_schema.sql` via SQL editor or phpMyAdmin.
- **Out-of-the-Box SQLite Auto-Initialization**: `EmailQueueService` automatically creates and manages SQLite tables in `storage/db/email_system.sqlite` without requiring manual database setup.

### 3. Queue Worker & Cron Job Setup

#### Automatic Background Execution
When an admin dispatches a bulk campaign, `EmailQueueService::triggerWorkerAsync()` automatically launches `app/Workers/EmailWorker.php` in a background process.

#### Production Cron Job Setup
For continuous processing of scheduled campaigns and retries, add the following cron entry to your server:

```cron
* * * * * php /var/www/html/app/Workers/EmailWorker.php > /dev/null 2>&1
```

Or run via CLI daemon / Supervisor:

```bash
php app/Workers/EmailWorker.php
```

---

## API Reference

### 1. Resend OTP
```http
POST /admin/api/otp.php
Content-Type: application/json

{
  "action": "resend",
  "email": "user@example.com",
  "type": "signup"
}
```

### 2. Bulk Broadcast Dispatch
```http
POST /api/admin/notifications/bulk-send
Content-Type: application/json

{
  "subject": "Important Market Update",
  "message": "Hello {{firstName}}, check out our latest trading signals!",
  "recipient_group": "all",
  "scheduled_at": null
}
```

### 3. Campaign Status
```http
GET /admin/api/notifications.php?action=status&campaign_id=cmp_123456
```

---

## Security Features

- **Rate Limiting**: Maximum 5 OTP resend requests per hour.
- **CSRF & Auth Verification**: Admin endpoints enforce active session validation (`sb_admin_required()`).
- **HMAC Unsubscribe Tokens**: Prevents unauthorized email unsubscribes via SHA-256 HMAC signatures.
- **Prepared Statements & Sanitization**: Protects against SQL Injection and XSS attacks.
