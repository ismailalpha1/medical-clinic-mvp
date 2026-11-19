# Database documentation

## `clinic_submissions`
Stores business information submitted by clinic owners or authorized representatives.

| Column | Type | Required | Purpose |
|---|---|---:|---|
| id | BIGINT | Yes | Primary key |
| clinic_name | VARCHAR(180) | Yes | Clinic/business name |
| contact_person | VARCHAR(160) | Yes | Main point of contact |
| email | VARCHAR(180) | Yes | Business contact email |
| phone | VARCHAR(40) | Yes | Business phone |
| website | VARCHAR(255) | No | Existing website |
| country, city | VARCHAR | Yes | Clinic location |
| address | VARCHAR(255) | No | Street/business address |
| clinic_type | VARCHAR(120) | Yes | Type of medical business |
| specialties | JSON | No | Medical specialties |
| services | TEXT | No | Main services |
| number_of_doctors | VARCHAR(50) | No | Approximate clinic size |
| number_of_locations | VARCHAR(50) | No | Number of branches |
| year_established | SMALLINT | No | Year established |
| description | TEXT | No | Business overview |
| goals | JSON | No | Business goals / needs |
| preferred_contact_method | VARCHAR(30) | Yes | Email, Phone, WhatsApp |
| consent | BOOLEAN | Yes | Contact consent |
| timestamps | DATETIME | Yes | Created/updated dates |

## `contact_inquiries`
Stores general website contact form messages: name, email, optional phone, subject, message, consent and timestamps.

## Relationships
No relationships are required for this MVP. Each submission is an independent lead. A future CRM/admin feature can add users, statuses, notes, assignment and follow-up history.

## Data minimization
This is a clinic business lead form, not a patient intake form. Do not collect patient names, medical histories, diagnoses, treatment details, insurance data or medical records. Define retention/deletion rules and restrict database access before production.
