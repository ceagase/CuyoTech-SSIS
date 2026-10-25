# CuyoTech Student Services Information System

## Purpose

CuyoTech SSIS is a role-based student services portal for managing common academic and administrative processes in one place. It gives students access to their academic information and service requests, while university offices can process the tasks and records relevant to their responsibilities.

## Features

### Student services

- View a dashboard with a summary of courses, document requests, and clearance progress.
- Review registered courses, class schedules, and posted grades.
- Submit document requests and track their processing status.
- View enrollment status and registered course information.
- Check clearance status across the required university offices.

### University office workflows

- **Registrar:** Process document requests, encode grades, manage enrollment, register courses, and update registrar clearance.
- **Cashier:** Record payments for enrollment fees, document requests, laboratory fees, and other charges; manage receipts and cashier clearance.
- **Department and service offices:** Review and update their own clearance items. Supported offices include the IT Department, Library, Registrar Office, Cashier, Guidance Office, Clinic, Student Affairs, Laboratory, and Property Office.
- **Administrator:** Create and manage user accounts for the system.

## Roles

The system provides separate role-based views for students, registrar staff, cashier staff, administrators, and clearance offices. Each role is presented with the functions needed for its workflow.

## Security

- Passwords are stored using bcrypt hashing.
- Database operations use prepared statements.
- Forms use CSRF tokens.
- Actions are restricted by role-based access checks.
- Displayed output is escaped.
- Login attempts are rate-limited, and session IDs are regenerated after login.
