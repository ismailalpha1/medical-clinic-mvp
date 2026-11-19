# Project structure

```text
medical-clinic-mvp/
├── README.md
├── docs/
│   ├── PROJECT_STRUCTURE.md
│   └── DATABASE.md
├── frontend/
│   ├── .env.example
│   ├── index.html
│   ├── package.json
│   ├── vite.config.js
│   └── src/
│       ├── App.jsx
│       ├── main.jsx
│       ├── styles.css
│       ├── components/
│       │   ├── LeadForm.jsx
│       │   └── SectionHeading.jsx
│       ├── lib/
│       │   └── api.js
│       └── pages/
│           ├── Home.jsx
│           ├── About.jsx
│           ├── Services.jsx
│           ├── Contact.jsx
│           └── ClinicRegistration.jsx
└── backend/
    ├── .env.example
    ├── app/
    │   ├── Http/Controllers/Api/
    │   │   ├── ClinicSubmissionController.php
    │   │   └── ContactInquiryController.php
    │   ├── Mail/
    │   │   ├── ClinicLeadNotification.php
    │   │   └── ContactInquiryNotification.php
    │   └── Models/
    │       ├── ClinicSubmission.php
    │       └── ContactInquiry.php
    ├── bootstrap/app.php
    ├── config/
    │   ├── cors.php
    │   └── mail.php
    ├── database/migrations/
    ├── resources/views/emails/
    └── routes/api.php
```

The `backend/` directory contains Laravel application files to merge into a fresh Laravel install. It intentionally excludes framework/vendor files, generated cache, secrets, and dependencies.
