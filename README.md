# CuyoTech SSIS (PHP + MySQL)

1. Copy the `ssis-php` folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. For a fresh setup, import `schema.sql` in phpMyAdmin; it creates the database and demo data. For an existing database, import `migration_course_schedules.sql` only. Do not import `schema.sql` over existing records because it recreates the demo database.
4. If your MySQL password is not empty, edit `config.php`.
5. Open http://localhost/ssis-php/

Demo logins (password for all: `password`): 260001 (student), registrar, cashier, dept (IT Department), admin. Admins can create separate clearance accounts for the Library, Registrar Office, Cashier, Guidance Office, Clinic, Student Affairs, Laboratory, and Property Office.

Security built in: bcrypt password hashing, prepared statements, CSRF tokens on every form, role checks on every action, output escaping, login attempt limit, session ID regeneration.
