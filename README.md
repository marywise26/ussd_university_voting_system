# University Voting System - Pure PHP

A pure PHP + MySQL university voting system for a project/demo. This version uses an AdminLTE-based dashboard layout with an Apple-style system font stack and a governed student registry.

## Current Features

- Admin login
- Admin-created student/voter accounts
- CSV student import
- Student activation/deactivation
- Student/voter login
- Election creation
- Position creation
- Candidate application form
- Admin vetting: approve or reject candidates
- Approved candidates appearing on the ballot
- One voter, one vote per position
- Results by election and position
- Basic audit logs
- AdminLTE sidebar/dashboard UI

## Important Access Rule

Students are **not allowed to self-register**.

Student voter accounts must be created by the admin from:

```text
Admin Dashboard > Students
```

or imported from a CSV file.

The old `register.php` file is intentionally disabled and redirects users back to login.

## Requirements

- PHP 8.0+
- MySQL/MariaDB
- XAMPP, WAMP, MAMP, or Laragon
- Browser
- Internet connection for AdminLTE, Font Awesome, Bootstrap, and jQuery CDN assets

## Installation on XAMPP

1. Copy the project folder to:

   ```text
   C:\xampp\htdocs\university-voting-system
   ```

2. Start Apache and MySQL from XAMPP Control Panel.

3. Open phpMyAdmin:

   ```text
   http://localhost/phpmyadmin
   ```

4. Import:

   ```text
   database/schema.sql
   ```

5. Check database credentials in:

   ```text
   config/database.php
   ```

   Default XAMPP values are already set:

   ```php
   DB_HOST = localhost
   DB_NAME = university_voting_system
   DB_USER = root
   DB_PASS = empty
   ```

6. Create the first admin account by visiting:

   ```text
   http://localhost/university-voting-system/setup_admin.php
   ```

7. After creating the admin account, delete or rename:

   ```text
   setup_admin.php
   ```

8. Login:

   ```text
   http://localhost/university-voting-system/login.php
   ```

## Demo Workflow

### Admin

1. Login as admin.
2. Open `Students`.
3. Add students manually or import them from CSV.
4. Create an election.
5. Add positions under the election.
6. Students login using admin-issued credentials.
7. Students apply as candidates from their portal.
8. Admin opens Candidate Vetting and approves/rejects applications.
9. Admin sets the election status to `open` during the voting period.
10. Students vote from the ballot page.
11. Admin views results.

### Student/Voter

1. Receive login credentials from admin.
2. Login with student number/email and password.
3. Apply as a candidate if desired.
4. Wait for admin approval.
5. Vote from the ballot page once the election is open.

## Student CSV Import

Use this template:

```text
database/student_import_template.csv
```

Required headers:

```text
student_no,full_name,email
```

Optional headers:

```text
phone,password
```

Full supported format:

```csv
student_no,full_name,email,phone,password
BCS/2026/001,Amina John,amina.john@example.com,+255700000001,amina123
BCS/2026/002,Baraka Ally,baraka.ally@example.com,+255700000002,baraka123
```

If the password column is empty, the system uses the student number as the first password for that account.

## Important Design Notes

- A candidate is still a voter.
- The `users.role` column only stores `admin` or `voter`.
- A voter becomes a candidate when their record in `candidate_applications` is approved.
- Only approved candidates appear on the ballot.
- Double voting is blocked by the database using:

  ```sql
  UNIQUE KEY unique_vote_per_position (voter_id, election_id, position_id)
  ```

## Free Hosting Notes

For InfinityFree or similar hosting:

1. Upload all files to `htdocs` or the web root.
2. Create a MySQL database in the hosting panel.
3. Import `database/schema.sql` using phpMyAdmin.
4. Update `config/database.php` with the hosting database credentials.
5. Run `setup_admin.php` once, then delete it.

## Suggested Next Features

- SMS OTP verification before voting
- USSD simulation endpoint
- Candidate application deadline
- Admin password reset
- PDF/printable results report
- More detailed audit trail
- Student password change screen
