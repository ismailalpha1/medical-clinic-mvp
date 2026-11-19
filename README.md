# Medical Clinic Website MVP

A lead-generation website built with React + Vite, Laravel API, and MySQL.

## Features
- Home page with Banner, About preview, Services preview, and Contact CTA
- Dedicated About, Services, Contact, and Clinic Registration pages
- Clinic business registration form with client-side and server-side validation
- Persists clinic submissions in MySQL
- Sends a notification email to a configured recipient
- Contact inquiry endpoint and database table
- Bilingual chat with optional Google Calendar appointment booking
- Basic API health endpoint

## Architecture
`frontend/` is a React SPA. `backend/` is a Laravel application. React communicates with Laravel through JSON REST endpoints.

## Requirements
- Node.js 20+
- PHP 8.2+
- Composer 2
- MySQL 8+
- A mail provider / SMTP credentials for email notifications

## 1. Create the Laravel application
This project contains the application-specific Laravel files, not the full Laravel framework or `vendor/` directory.

From the project root:
```bash
composer create-project laravel/laravel backend
```
Then copy/merge the contents of the supplied `backend/` folder into the generated Laravel `backend/` folder, replacing matching application files where appropriate. Keep Laravel's generated framework files and `vendor/`.

If you already have a Laravel app, simply merge the supplied files into it.

## 2. Configure MySQL
Create a database:
```sql
CREATE DATABASE medical_clinic_mvp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
Copy `backend/.env.example` to `backend/.env` (or update the generated `.env`) and set:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medical_clinic_mvp
DB_USERNAME=root
DB_PASSWORD=
```

Configure mail settings in `.env`. Example for SMTP:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
CLINIC_LEAD_NOTIFICATION_EMAIL="leads@example.com"
```
Use your actual SMTP provider values. Do not commit real credentials.

## 3. Run backend
```bash
cd backend
php artisan key:generate
php artisan migrate
php artisan serve
```
API base URL: `http://127.0.0.1:8000/api`

## 4. Run frontend
In another terminal:
```bash
cd frontend
cp .env.example .env
npm install
npm run dev
```
Frontend: `http://localhost:5173`

Set `VITE_API_BASE_URL=http://127.0.0.1:8000/api` in `frontend/.env`.

## API endpoints
| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/health` | API health check |
| GET | `/api/appointments/availability` | Return available calendar slots for a date and request type |
| POST | `/api/appointments` | Book an appointment in Google Calendar and notify the clinic |
| POST | `/api/clinic-submissions` | Validate, save and email a clinic lead |
| POST | `/api/contact-inquiries` | Save and email a contact inquiry |

## Google Calendar appointment booking

The chat can check live availability and create a Google Calendar event. Booking is disabled until the backend has valid Google OAuth credentials and clinic availability settings. Configure these values in `backend/.env`; never place them in the frontend or commit them:

```env
GOOGLE_CALENDAR_CLIENT_ID=
GOOGLE_CALENDAR_CLIENT_SECRET=
GOOGLE_CALENDAR_REFRESH_TOKEN=
GOOGLE_CALENDAR_ID=primary
APPOINTMENT_TIMEZONE=America/New_York
APPOINTMENT_WEEKDAYS=1,2,3,4,5
APPOINTMENT_OPEN_TIME=09:00
APPOINTMENT_CLOSE_TIME=17:00
APPOINTMENT_SLOT_INTERVAL=15
APPOINTMENT_BOOKING_WINDOW_DAYS=60
APPOINTMENT_DURATION_APPOINTMENT=30
APPOINTMENT_DURATION_SERVICES=15
APPOINTMENT_DURATION_LOCATION=15
APPOINTMENT_DURATION_CALLBACK=15
APPOINTMENT_DURATION_OTHER=30
```

In Google Cloud Console, enable the Google Calendar API, configure the OAuth consent screen, and create a Web application OAuth client with `https://developers.google.com/oauthplayground` as an authorized redirect URI. In OAuth 2.0 Playground settings, enable **Use your own OAuth credentials**, enter that client ID and secret, and authorize these scopes:

```text
https://www.googleapis.com/auth/calendar.events
https://www.googleapis.com/auth/calendar.freebusy
```

Exchange the authorization code for tokens and put the refresh token in `GOOGLE_CALENDAR_REFRESH_TOKEN`. Keep all credentials server-side and never paste them into chat or commit them. For long-term use, publish the OAuth consent screen; refresh tokens for an app left in external testing may expire. Set `GOOGLE_CALENDAR_ID` to `primary` or the target calendar ID, and use an IANA time zone such as `America/New_York`. Weekdays use ISO numbers (`1` = Monday, `7` = Sunday); opening and closing times use 24-hour local time. Each request type has a configurable appointment length.

Run `php artisan migrate` in `backend/` after deploying the new migration. Google Calendar credentials and refresh tokens must remain server-side. The calendar event intentionally excludes the optional free-text visit reason.

### Clinic registration payload
```json
{
  "clinic_name": "Northside Medical Clinic",
  "contact_person": "Jane Smith",
  "email": "jane@example.com",
  "phone": "+1 555 0100",
  "website": "https://example.com",
  "country": "United States",
  "city": "Los Angeles",
  "address": "123 Main Street",
  "clinic_type": "Multi-specialty clinic",
  "specialties": ["Family medicine", "Dermatology"],
  "services": "Consultations, diagnostics, preventive care",
  "number_of_doctors": "6-10",
  "number_of_locations": "1",
  "year_established": 2018,
  "description": "A short description of the clinic.",
  "goals": ["Improve online visibility", "Receive more patient inquiries"],
  "preferred_contact_method": "Email",
  "consent": true
}
```

## Form field rationale
The form collects business contact details, clinic location/type, specialties, services, approximate size, website, background, goals and preferred follow-up channel. It intentionally does **not** collect patient records, diagnoses, insurance details, or other patient health information.

## Production checklist
- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Use HTTPS and secure SMTP credentials.
- Configure exact frontend origin in `FRONTEND_URL` and review CORS.
- Add rate limiting, spam protection (e.g. CAPTCHA), and abuse monitoring.
- Add authentication/authorization before creating any admin dashboard or exposing submissions.
- Add privacy notice, retention policy, and consent wording reviewed for applicable laws.
- Consider queueing mail (`ShouldQueue`) and monitoring failed jobs for production.
- Add automated tests, logging, backups, and deployment health checks.
- Never send sensitive patient health information through this general lead form.

## Notes
This is an MVP starter, not a complete production compliance package. Email delivery depends on valid SMTP configuration. Database persistence happens before the email attempt; mail failure is logged and the API still confirms that the submission was saved.
