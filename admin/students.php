<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$smsConfigPath = __DIR__ . '/../config/sms.php';

if (is_file($smsConfigPath)) {
    require_once $smsConfigPath;
}

function normalize_import_header(array $row): array
{
    return array_map(static function ($value) {
        return strtolower(trim((string)$value));
    }, $row);
}

function normalize_programme_code(string $code): string
{
    $code = str_replace(['–', '—', '−'], '-', $code);
    $code = preg_replace('/\s*-\s*/', '-', $code);
    $code = preg_replace('/\s+/', ' ', trim($code));
    return strtoupper($code);
}

function academic_catalog(): array
{
    return [
        'FST' => [
            'name' => 'Faculty of Science and Technology',
            'programmes' => [
                ['code' => 'MSc.ITS', 'name' => 'Master of Science in Information Technology and Systems', 'years' => '2'],
                ['code' => 'MSc. AS', 'name' => 'Master of Science in Applied Statistics', 'years' => '1.5'],
                ['code' => 'BSc.ITS', 'name' => 'B.Sc in Information Technology and Systems', 'years' => '3'],
                ['code' => 'B.Edu(MICT)', 'name' => 'B.Edu in Mathematics and ICT', 'years' => '3'],
                ['code' => 'BSc.AS', 'name' => 'Bachelor of Science in Applied Statistics', 'years' => '3'],
                ['code' => 'BSc.ICT-B', 'name' => 'Bachelor of Science in ICT with Business', 'years' => '3'],
                ['code' => 'BSc.ICT-M', 'name' => 'Bachelor of Science in ICT with Management', 'years' => '3'],
                ['code' => 'BSc.IEM', 'name' => 'Bachelor of Science in Industrial Engineering Management', 'years' => '3'],
                ['code' => 'BSc.LIM', 'name' => 'Bachelor of Science in Library and Information Management', 'years' => '3'],
                ['code' => 'BSc.POM', 'name' => 'Bachelor of Science in Productions and Operations Management', 'years' => '3'],
                ['code' => 'DIT', 'name' => 'Diploma in Information Technology', 'years' => '2'],
                ['code' => 'DAS', 'name' => 'Diploma in Applied Statistics', 'years' => '2'],
                ['code' => 'CLIM', 'name' => 'Certificate in Library and Information Management', 'years' => '1'],
                ['code' => 'CIT', 'name' => 'Certificate in Information and Communication Technology', 'years' => '1'],
                ['code' => 'CAS', 'name' => 'Certificate in Applied Statistics', 'years' => '1'],
            ],
        ],
        'SOB' => [
            'name' => 'School of Business',
            'programmes' => [
                ['code' => 'MBA-CM', 'name' => 'Master of Business Administration in Corporate Management', 'years' => '1.5'],
                ['code' => 'MSc. A&F', 'name' => 'Master of Science in Accounting and Finance', 'years' => '1.5'],
                ['code' => 'MSc. PSCM', 'name' => 'Master of Science in Procurement and Supply Chain Management', 'years' => '1.5'],
                ['code' => 'MSc. Entrepreneurship', 'name' => 'Master of Science in Entrepreneurship', 'years' => '1.5'],
                ['code' => 'MSc. MM', 'name' => 'Master of Science in Marketing Management', 'years' => '1.5'],
                ['code' => 'BAF-BS', 'name' => 'Bachelor of Accounting and Finance - Business Sector', 'years' => '3'],
                ['code' => 'BAF-PS', 'name' => 'Bachelor of Accounting and Finance - Public Sector', 'years' => '3'],
                ['code' => 'BBA-(IEM)', 'name' => 'Bachelor of Business Administration in Innovation and Entrepreneurship Management', 'years' => '3'],
                ['code' => 'BBA-(MKT)', 'name' => 'Bachelor of Business Administration - Marketing Management', 'years' => '3'],
                ['code' => 'BPSCM', 'name' => 'Bachelor of Procurement and Supply Chain Management', 'years' => '3'],
                ['code' => 'DA', 'name' => 'Diploma in Accountancy', 'years' => '2'],
                ['code' => 'DBA', 'name' => 'Diploma in Business Administration', 'years' => '2'],
                ['code' => 'DLM', 'name' => 'Diploma in Procurement and Supply Chain Management', 'years' => '2'],
                ['code' => 'BMC', 'name' => 'Certificate in Business Management', 'years' => '1'],
                ['code' => 'CLM', 'name' => 'Certificate in Logistics Management', 'years' => '1'],
                ['code' => 'CA', 'name' => 'Certificate in Accountancy', 'years' => '1'],
            ],
        ],
        'FOL' => [
            'name' => 'Faculty of Law',
            'programmes' => [
                ['code' => 'LL.M-CL', 'name' => 'Master of Law - Commercial Law', 'years' => '1.5'],
                ['code' => 'LL.M-CA', 'name' => 'Master of Law - Constitutional and Administrative Law', 'years' => '1.5'],
                ['code' => 'LL.B', 'name' => 'Bachelor of Law', 'years' => '3'],
                ['code' => 'DL', 'name' => 'Diploma in Law', 'years' => '2'],
                ['code' => 'CL', 'name' => 'Certificate in Law', 'years' => '1'],
            ],
        ],
        'FSS' => [
            'name' => 'Faculty of Social Science',
            'programmes' => [
                ['code' => 'MSc.Econ', 'name' => 'Master of Science in Economics', 'years' => '1.5'],
                ['code' => 'MSc.PPM', 'name' => 'Master of Science in Project Planning and Management', 'years' => '1.5'],
                ['code' => 'MSc.EPP', 'name' => 'Master of Science in Economic Policy and Planning', 'years' => '1.5'],
                ['code' => 'MA Ed', 'name' => 'Master of Arts in Education', 'years' => '1.5'],
                ['code' => 'BAED-CA', 'name' => 'Bachelor of Arts with Education (Commerce and Accountancy)', 'years' => '3'],
                ['code' => 'BAED-EM', 'name' => 'Bachelor of Arts with Education (Economics and Mathematics)', 'years' => '3'],
                ['code' => 'BAED-EK', 'name' => 'Bachelor of Arts with Education (English and Kiswahili)', 'years' => '3'],
                ['code' => 'BAED-AM', 'name' => 'Bachelor of Arts with Education (Accountancy and Mathematics)', 'years' => '3'],
                ['code' => 'BAED-EC', 'name' => 'Bachelor of Arts with Education (Economics and Commerce)', 'years' => '3'],
                ['code' => 'P&D', 'name' => 'Bachelor of Science (Economics) - Population and Development', 'years' => '3'],
                ['code' => 'PPM', 'name' => 'Bachelor of Science (Economics) - Project Planning and Management', 'years' => '3'],
                ['code' => 'EPP', 'name' => 'Bachelor of Science in Economics - Economic Planning and Policy', 'years' => '3'],
            ],
        ],
        'IDS' => [
            'name' => 'Institute of Development Studies',
            'programmes' => [
                ['code' => 'MA.DPP', 'name' => 'Master of Arts in Development Policy and Planning', 'years' => '1.5'],
                ['code' => 'MEM', 'name' => 'Master of Environmental Management', 'years' => '1.5'],
                ['code' => 'BEM', 'name' => 'Bachelor of Environmental Management', 'years' => '3'],
            ],
        ],
        'SOPAM' => [
            'name' => 'School of Public Administration and Management',
            'programmes' => [
                ['code' => 'MPA', 'name' => 'Master of Public Administration', 'years' => '1.5'],
                ['code' => 'MHSM', 'name' => 'Master of Health Systems Management', 'years' => '1.5'],
                ['code' => 'MSc. HM&E', 'name' => 'Master of Science in Health Monitoring and Evaluation', 'years' => '2'],
                ['code' => 'MSc. HRM', 'name' => 'Master of Science in Human Resource Management', 'years' => '2'],
                ['code' => 'MRPP', 'name' => 'Master of Research and Public Policy', 'years' => '1.5'],
                ['code' => 'BHSM', 'name' => 'Bachelor of Health Systems Management', 'years' => '3'],
                ['code' => 'BHRM', 'name' => 'Bachelor of Human Resource Management', 'years' => '3'],
                ['code' => 'BLGM', 'name' => 'Bachelor of Local Government Management', 'years' => '3'],
                ['code' => 'BPA', 'name' => 'Bachelor of Public Administration', 'years' => '3'],
                ['code' => 'BPA-RAM', 'name' => 'Bachelor of Public Administration - Records and Archives Management', 'years' => '3'],
                ['code' => 'DHRM', 'name' => 'Diploma in Human Resource Management', 'years' => '2'],
                ['code' => 'HRMC', 'name' => 'Certificate in Human Resource Management', 'years' => '1'],
                ['code' => 'CLGF', 'name' => 'Certificate in Local Government Management', 'years' => '1'],
                ['code' => 'CLGA', 'name' => 'Certificate in Local Government Administration', 'years' => '1'],
            ],
        ],
    ];
}

function programme_lookup(array $catalog): array
{
    $lookup = [];

    foreach ($catalog as $facultyCode => $faculty) {
        foreach ($faculty['programmes'] as $programme) {
            $lookup[normalize_programme_code($programme['code'])] = [
                'faculty_code' => $facultyCode,
                'faculty_name' => $faculty['name'],
                'code' => $programme['code'],
                'name' => $programme['name'],
                'years' => $programme['years'],
            ];
        }
    }

    return $lookup;
}

function find_programme(array $lookup, string $code): ?array
{
    $key = normalize_programme_code($code);
    return $lookup[$key] ?? null;
}

function max_study_year_for_programme(?array $programme): int
{
    if (!$programme || !isset($programme['years'])) {
        return 6;
    }

    return max(1, (int)ceil((float)$programme['years']));
}

function normalize_study_year($value, ?array $programme = null): ?int
{
    $year = filter_var($value, FILTER_VALIDATE_INT);
    $maxYear = max_study_year_for_programme($programme);

    if ($year === false || $year < 1 || $year > $maxYear) {
        return null;
    }

    return $year;
}

function build_programme_catalog_for_js(array $catalog): array
{
    $items = [];

    foreach ($catalog as $facultyCode => $faculty) {
        foreach ($faculty['programmes'] as $programme) {
            $items[] = [
                'faculty_code' => $facultyCode,
                'faculty_name' => $faculty['name'],
                'code' => $programme['code'],
                'name' => $programme['name'],
                'years' => $programme['years'],
                'search' => strtolower($facultyCode . ' ' . $faculty['name'] . ' ' . $programme['code'] . ' ' . $programme['name']),
            ];
        }
    }

    return $items;
}

function student_email_base(string $fullName): string
{
    $fullName = strtolower(trim($fullName));
    $fullName = preg_replace('/[^a-z0-9]+/i', '.', $fullName);
    $fullName = trim((string)$fullName, '.');
    $parts = array_values(array_filter(explode('.', $fullName)));

    if (count($parts) >= 2) {
        return $parts[0] . '.' . end($parts);
    }

    if (count($parts) === 1) {
        return $parts[0];
    }

    return 'student';
}

function generate_student_email(PDO $pdo, string $fullName): string
{
    $base = student_email_base($fullName);
    $email = $base . '@mzumbe.ac.tz';
    $counter = 2;

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');

    while (true) {
        $stmt->execute([$email]);

        if ((int)$stmt->fetchColumn() === 0) {
            return $email;
        }

        $email = $base . $counter . '@mzumbe.ac.tz';
        $counter++;
    }
}

function generate_student_email_for_update(PDO $pdo, string $fullName, int $studentId): string
{
    $base = student_email_base($fullName);
    $email = $base . '@mzumbe.ac.tz';
    $counter = 2;

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND id <> ?');

    while (true) {
        $stmt->execute([$email, $studentId]);

        if ((int)$stmt->fetchColumn() === 0) {
            return $email;
        }

        $email = $base . $counter . '@mzumbe.ac.tz';
        $counter++;
    }
}

function normalize_tanzania_phone(string $phone): ?string
{
    $phone = trim($phone);
    $phone = str_replace([' ', '-', '(', ')'], '', $phone);
    $phone = preg_replace('/[^0-9+]/', '', (string)$phone);

    if (substr($phone, 0, 4) === '+255') {
        $local = substr($phone, 4);
    } elseif (substr($phone, 0, 3) === '255') {
        $local = substr($phone, 3);
    } elseif (substr($phone, 0, 1) === '0') {
        $local = substr($phone, 1);
    } else {
        $local = $phone;
    }

    if (preg_match('/^[67][0-9]{8}$/', $local)) {
        return '+255' . $local;
    }

    return null;
}

function at_config_value(string $key, string $default = ''): string
{
    if (defined($key)) {
        return trim((string)constant($key));
    }

    $value = getenv($key);

    if ($value === false || trim((string)$value) === '') {
        return $default;
    }

    return trim((string)$value);
}

function africastalking_sms_endpoint(): string
{
    $environment = strtolower(at_config_value('AT_ENV', 'sandbox'));

    if ($environment === 'live' || $environment === 'production') {
        return 'https://api.africastalking.com/version1/messaging';
    }

    return 'https://api.sandbox.africastalking.com/version1/messaging';
}

function send_africastalking_sms(string $phone, string $message): array
{
    $username = at_config_value('AT_USERNAME', 'sandbox');
    $apiKey = at_config_value('AT_API_KEY');
    $senderId = at_config_value('AT_SENDER_ID');

    if ($username === '' || $apiKey === '') {
        return [
            'ok' => false,
            'error' => 'Africa\'s Talking username or API key is not configured.',
        ];
    }

    if (!function_exists('curl_init')) {
        return [
            'ok' => false,
            'error' => 'PHP cURL extension is not enabled in WAMP.',
        ];
    }

    $payload = [
        'username' => $username,
        'to' => $phone,
        'message' => $message,
    ];

    if ($senderId !== '') {
        $payload['from'] = $senderId;
    }

    $ch = curl_init(africastalking_sms_endpoint());

    if (!$ch) {
        return [
            'ok' => false,
            'error' => 'Could not initialize cURL.',
        ];
    }

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'apiKey: ' . $apiKey,
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
        ],
        CURLOPT_POSTFIELDS => http_build_query($payload),
        CURLOPT_TIMEOUT => 25,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);

    $responseBody = curl_exec($ch);
    $curlError = curl_error($ch);
    $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($responseBody === false) {
        return [
            'ok' => false,
            'error' => $curlError ?: 'Africa\'s Talking SMS request failed.',
        ];
    }

    $decoded = json_decode($responseBody, true);

    return [
        'ok' => $statusCode >= 200 && $statusCode < 300,
        'status' => $statusCode,
        'response' => $responseBody,
        'decoded' => $decoded,
    ];
}

function build_student_credentials_sms(string $fullName, string $studentNo, string $email, string $password): string
{
    return "Mzumbe Electoral Portal: Dear {$fullName}, your voting account has been created. Username: {$studentNo} or {$email}. Temporary Password: {$password}. Please change it after first login.";
}

function students_current_query_string(array $overrides = []): string
{
    $params = array_merge($_GET, $overrides);

    foreach ($params as $key => $value) {
        if ($value === null || $value === '' || $value === 'all') {
            unset($params[$key]);
        }
    }

    return http_build_query($params);
}

function students_registry_url(array $overrides = []): string
{
    $query = students_current_query_string($overrides);
    return app_url('admin/students.php' . ($query !== '' ? '?' . $query : ''));
}

function redirect_students_registry(): void
{
    $query = trim((string)($_POST['return_query'] ?? ''));

    if ($query !== '' && preg_match('/^[A-Za-z0-9%=&_\-\.\+~]*$/', $query)) {
        redirect('admin/students.php?' . $query);
    }

    redirect('admin/students.php');
}

$catalog = academic_catalog();
$programmes = programme_lookup($catalog);
$programmesForJs = build_programme_catalog_for_js($catalog);

if (($_GET['download_template'] ?? '') === '1') {
    $filename = 'student_import_template.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['student_no', 'full_name', 'phone', 'programme_code', 'study_year', 'password']);
    fputcsv($output, ['BCS/2026/001', 'Amina John', '+255712345678', 'BSc.ITS', '1', 'amina123']);
    fputcsv($output, ['BBA/2026/002', 'Joseph Peter', '+255713456789', 'BBA-(MKT)', '2', '']);
    fclose($output);

    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $studentNo = trim((string)($_POST['student_no'] ?? ''));
        $fullName = trim((string)($_POST['full_name'] ?? ''));
        $phone = normalize_tanzania_phone((string)($_POST['phone_local'] ?? ($_POST['phone'] ?? '')));
        $password = (string)($_POST['password'] ?? '');
        $selectedProgramme = find_programme($programmes, trim((string)($_POST['programme_code'] ?? '')));
        $studyYear = normalize_study_year($_POST['study_year'] ?? null, $selectedProgramme);
        $email = $fullName !== '' ? generate_student_email($pdo, $fullName) : '';

        if ($studentNo === '' || $fullName === '' || $password === '') {
            set_flash('error', 'Student number, full name, and temporary password are required. Email is generated automatically.');
        } elseif (!$phone) {
            set_flash('error', 'Enter a valid Tanzania phone number after +255, for example 712345678.');
        } elseif (!$selectedProgramme) {
            set_flash('error', 'Search and select a valid programme.');
        } elseif ($studyYear === null) {
            set_flash('error', 'Select a valid study year for the selected programme.');
        } elseif (strlen($password) < 6) {
            set_flash('error', 'Temporary password must be at least 6 characters.');
        } else {
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO users
                     (student_no, full_name, email, phone, faculty_code, programme_code, study_year, password_hash, must_change_password, role, is_active)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, "voter", 1)'
                );

                $stmt->execute([
                    $studentNo,
                    $fullName,
                    $email,
                    $phone,
                    $selectedProgramme['faculty_code'],
                    $selectedProgramme['code'],
                    $studyYear,
                    password_hash($password, PASSWORD_DEFAULT),
                ]);

                $newStudentId = (int)$pdo->lastInsertId();

                log_activity($pdo, (int)$_SESSION['user_id'], 'create_student', 'users', $newStudentId);

                $sms = send_africastalking_sms(
                    $phone,
                    build_student_credentials_sms($fullName, $studentNo, $email, $password)
                );

                if ($sms['ok']) {
                    set_flash('success', 'Student voter account created successfully. Temporary login credentials were sent by SMS.');
                } else {
                    error_log("Africa's Talking SMS failed for student " . $studentNo . ': ' . json_encode($sms));
                    set_flash('success', "Student voter account created successfully, but SMS could not be sent. Check Africa's Talking Sandbox API key, WAMP cURL, or simulator number.");
                }
            } catch (PDOException $e) {
                set_flash('error', 'Student number or generated email already exists.');
            }
        }

        redirect_students_registry();
    }

    if ($action === 'toggle_status') {
        $studentId = (int)($_POST['student_id'] ?? 0);
        $newStatus = (int)($_POST['is_active'] ?? 0) === 1 ? 1 : 0;

        $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ? AND role = 'voter'");
        $stmt->execute([$newStatus, $studentId]);

        log_activity($pdo, (int)$_SESSION['user_id'], $newStatus ? 'activate_student' : 'deactivate_student', 'users', $studentId);

        set_flash('success', 'Student status updated.');
        redirect_students_registry();
    }

    if ($action === 'edit_student') {
        $studentId = (int)($_POST['student_id'] ?? 0);
        $studentNo = trim((string)($_POST['student_no'] ?? ''));
        $fullName = trim((string)($_POST['full_name'] ?? ''));
        $phone = normalize_tanzania_phone((string)($_POST['phone_local'] ?? ($_POST['phone'] ?? '')));
        $password = trim((string)($_POST['password'] ?? ''));
        $selectedProgramme = find_programme($programmes, trim((string)($_POST['programme_code'] ?? '')));
        $studyYear = normalize_study_year($_POST['study_year'] ?? null, $selectedProgramme);
        $email = $fullName !== '' ? generate_student_email_for_update($pdo, $fullName, $studentId) : '';

        if ($studentId <= 0 || $studentNo === '' || $fullName === '') {
            set_flash('error', 'Student number and full name are required.');
        } elseif (!$phone) {
            set_flash('error', 'Enter a valid Tanzania phone number after +255, for example 712345678.');
        } elseif (!$selectedProgramme) {
            set_flash('error', 'Search and select a valid programme.');
        } elseif ($studyYear === null) {
            set_flash('error', 'Select a valid study year for the selected programme.');
        } elseif ($password !== '' && strlen($password) < 6) {
            set_flash('error', 'Temporary password must be at least 6 characters when changing it.');
        } else {
            try {
                if ($password !== '') {
                    $stmt = $pdo->prepare(
                        "UPDATE users
                         SET student_no = ?,
                             full_name = ?,
                             email = ?,
                             phone = ?,
                             faculty_code = ?,
                             programme_code = ?,
                             study_year = ?,
                             password_hash = ?,
                             must_change_password = 1
                         WHERE id = ? AND role = 'voter'"
                    );

                    $stmt->execute([
                        $studentNo,
                        $fullName,
                        $email,
                        $phone,
                        $selectedProgramme['faculty_code'],
                        $selectedProgramme['code'],
                        $studyYear,
                        password_hash($password, PASSWORD_DEFAULT),
                        $studentId,
                    ]);
                } else {
                    $stmt = $pdo->prepare(
                        "UPDATE users
                         SET student_no = ?,
                             full_name = ?,
                             email = ?,
                             phone = ?,
                             faculty_code = ?,
                             programme_code = ?,
                             study_year = ?
                         WHERE id = ? AND role = 'voter'"
                    );

                    $stmt->execute([
                        $studentNo,
                        $fullName,
                        $email,
                        $phone,
                        $selectedProgramme['faculty_code'],
                        $selectedProgramme['code'],
                        $studyYear,
                        $studentId,
                    ]);
                }

                log_activity($pdo, (int)$_SESSION['user_id'], 'edit_student', 'users', $studentId);

                set_flash('success', $password !== ''
                    ? 'Student details updated. Student must change the temporary password on next login.'
                    : 'Student details updated successfully.'
                );
            } catch (PDOException $e) {
                set_flash('error', 'Could not update student. Student number or generated email may already exist.');
            }
        }

        redirect_students_registry();
    }

    if ($action === 'delete_student') {
        $studentId = (int)($_POST['student_id'] ?? 0);

        if ($studentId <= 0) {
            set_flash('error', 'Invalid student selected.');
            redirect_students_registry();
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'voter'");
            $stmt->execute([$studentId]);

            if ($stmt->rowCount() > 0) {
                log_activity($pdo, (int)$_SESSION['user_id'], 'delete_student', 'users', $studentId);
                set_flash('success', 'Student deleted successfully.');
            } else {
                set_flash('error', 'Student was not found.');
            }
        } catch (PDOException $e) {
            set_flash('error', 'Could not delete student because related records still exist.');
        }

        redirect_students_registry();
    }

    if ($action === 'import') {
        if (empty($_FILES['students_csv']['tmp_name']) || ($_FILES['students_csv']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            set_flash('error', 'Upload a valid CSV file.');
            redirect_students_registry();
        }

        $handle = fopen($_FILES['students_csv']['tmp_name'], 'r');

        if (!$handle) {
            set_flash('error', 'Could not read the uploaded CSV file.');
            redirect_students_registry();
        }

        $header = fgetcsv($handle);
        $requiredHeaders = ['student_no', 'full_name', 'phone', 'programme_code', 'study_year'];

        $imported = 0;
        $skipped = 0;
        $smsSent = 0;
        $smsFailed = 0;

        if (!$header) {
            fclose($handle);
            set_flash('error', 'CSV file is empty.');
            redirect_students_registry();
        }

        $headers = normalize_import_header($header);

        foreach ($requiredHeaders as $requiredHeader) {
            if (!in_array($requiredHeader, $headers, true)) {
                fclose($handle);
                set_flash('error', 'CSV must include: student_no, full_name, phone, programme_code, study_year. Optional: password.');
                redirect_students_registry();
            }
        }

        while (($row = fgetcsv($handle)) !== false) {
            $data = [];

            foreach ($headers as $index => $name) {
                $data[$name] = trim((string)($row[$index] ?? ''));
            }

            $studentNo = $data['student_no'] ?? '';
            $fullName = $data['full_name'] ?? '';
            $phone = normalize_tanzania_phone($data['phone'] ?? '');
            $password = $data['password'] ?? '';
            $selectedProgramme = find_programme($programmes, $data['programme_code'] ?? '');
            $studyYear = normalize_study_year($data['study_year'] ?? null, $selectedProgramme);
            $email = $fullName !== '' ? generate_student_email($pdo, $fullName) : '';

            if ($studentNo === '' || $fullName === '' || !$phone || !$selectedProgramme || $studyYear === null) {
                $skipped++;
                continue;
            }

            if ($password === '') {
                $password = $studentNo;
            }

            if (strlen($password) < 6) {
                $skipped++;
                continue;
            }

            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO users
                     (student_no, full_name, email, phone, faculty_code, programme_code, study_year, password_hash, must_change_password, role, is_active)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, "voter", 1)'
                );

                $stmt->execute([
                    $studentNo,
                    $fullName,
                    $email,
                    $phone,
                    $selectedProgramme['faculty_code'],
                    $selectedProgramme['code'],
                    $studyYear,
                    password_hash($password, PASSWORD_DEFAULT),
                ]);

                $imported++;

                $sms = send_africastalking_sms(
                    $phone,
                    build_student_credentials_sms($fullName, $studentNo, $email, $password)
                );

                if ($sms['ok']) {
                    $smsSent++;
                } else {
                    $smsFailed++;
                    error_log("Africa's Talking SMS failed for imported student " . $studentNo . ': ' . json_encode($sms));
                }
            } catch (PDOException $e) {
                $skipped++;
            }
        }

        fclose($handle);

        log_activity($pdo, (int)$_SESSION['user_id'], 'import_students', 'users', null);

        set_flash('success', "Import complete. Imported: {$imported}. Skipped: {$skipped}. SMS sent: {$smsSent}. SMS failed: {$smsFailed}.");

        redirect_students_registry();
    }
}

$registryStats = $pdo->query(
    "SELECT
        COUNT(*) AS total_students,
        COALESCE(SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END), 0) AS active_students,
        COALESCE(SUM(CASE WHEN TRIM(COALESCE(programme_code, '')) <> '' THEN 1 ELSE 0 END), 0) AS assigned_students,
        COALESCE(SUM(CASE WHEN must_change_password = 1 THEN 1 ELSE 0 END), 0) AS temporary_passwords
     FROM users
     WHERE role = 'voter'"
)->fetch(PDO::FETCH_ASSOC) ?: [];

$totalStudents = (int)($registryStats['total_students'] ?? 0);
$activeStudents = (int)($registryStats['active_students'] ?? 0);
$assignedStudents = (int)($registryStats['assigned_students'] ?? 0);
$temporaryPasswords = (int)($registryStats['temporary_passwords'] ?? 0);

$candidates = (int)$pdo->query(
    "SELECT COUNT(*)
     FROM users u
     WHERE u.role = 'voter'
     AND EXISTS (
         SELECT 1
         FROM candidate_applications ca
         WHERE ca.user_id = u.id
         AND ca.status = 'approved'
     )"
)->fetchColumn();

$search = trim((string)($_GET['q'] ?? ''));
$facultyFilter = strtoupper(trim((string)($_GET['faculty'] ?? '')));
$statusFilter = strtolower(trim((string)($_GET['status'] ?? '')));
$candidateFilter = strtolower(trim((string)($_GET['candidate'] ?? '')));

$perPageOptions = [25, 50, 100, 200];
$perPage = (int)($_GET['per_page'] ?? 25);
$page = max(1, (int)($_GET['page'] ?? 1));

if (!in_array($perPage, $perPageOptions, true)) {
    $perPage = 25;
}

if ($facultyFilter !== '' && !isset($catalog[$facultyFilter])) {
    $facultyFilter = '';
}

if (!in_array($statusFilter, ['active', 'inactive'], true)) {
    $statusFilter = '';
}

if (!in_array($candidateFilter, ['yes', 'no'], true)) {
    $candidateFilter = '';
}

$candidateExistsSql = "EXISTS (
    SELECT 1
    FROM candidate_applications ca
    WHERE ca.user_id = u.id
    AND ca.status = 'approved'
)";

$where = ["u.role = 'voter'"];
$queryParams = [];

if ($search !== '') {
    $like = '%' . $search . '%';

    $where[] = "(u.full_name LIKE ? OR u.student_no LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR u.faculty_code LIKE ? OR u.programme_code LIKE ?)";
    array_push($queryParams, $like, $like, $like, $like, $like, $like);
}

if ($facultyFilter !== '') {
    $where[] = 'u.faculty_code = ?';
    $queryParams[] = $facultyFilter;
}

if ($statusFilter === 'active') {
    $where[] = 'u.is_active = 1';
} elseif ($statusFilter === 'inactive') {
    $where[] = 'u.is_active = 0';
}

if ($candidateFilter === 'yes') {
    $where[] = $candidateExistsSql;
} elseif ($candidateFilter === 'no') {
    $where[] = 'NOT ' . $candidateExistsSql;
}

$whereSql = implode(' AND ', $where);

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users u WHERE {$whereSql}");
$countStmt->execute($queryParams);

$filteredStudents = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($filteredStudents / $perPage));

if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $perPage;

$studentsSql = "
    SELECT
        u.*,
        CASE WHEN {$candidateExistsSql} THEN 1 ELSE 0 END AS is_candidate
    FROM users u
    WHERE {$whereSql}
    ORDER BY u.created_at DESC
    LIMIT ? OFFSET ?
";

$studentsStmt = $pdo->prepare($studentsSql);

foreach ($queryParams as $index => $value) {
    $studentsStmt->bindValue($index + 1, $value);
}

$studentsStmt->bindValue(count($queryParams) + 1, $perPage, PDO::PARAM_INT);
$studentsStmt->bindValue(count($queryParams) + 2, $offset, PDO::PARAM_INT);
$studentsStmt->execute();

$students = $studentsStmt->fetchAll();

$showingFrom = $filteredStudents > 0 ? $offset + 1 : 0;
$showingTo = min($offset + $perPage, $filteredStudents);
$paginationStart = max(1, $page - 2);
$paginationEnd = min($totalPages, $page + 2);
$hasRegistryFilters = $search !== '' || $facultyFilter !== '' || $statusFilter !== '' || $candidateFilter !== '';

$page_title = 'Students';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .students-page {
        --primary: #2563eb;
        --success: #16a34a;
        --danger: #dc2626;
        --warning: #f59e0b;
        --ink: #0f172a;
        --muted: #64748b;
        --line: #e2e8f0;
        --card: #ffffff;
        --radius: 20px;
        color: var(--ink);
    }

    .students-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        padding: 2rem;
        margin-bottom: 1.5rem;
        border-radius: 28px;
        background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 58%, #38bdf8 100%);
        color: #fff;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .18);
    }

    .students-hero h1 {
        margin: 0;
        font-size: clamp(1.8rem, 4vw, 3rem);
    }

    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        margin: 0 0 .65rem;
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, .78);
    }

    .stats-grid,
    .forms-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .forms-grid {
        grid-template-columns: minmax(0, 1.25fr) minmax(320px, .75fr);
        align-items: start;
    }

    .ui-card {
        background: var(--card);
        border: 1px solid var(--line);
        border-radius: var(--radius);
        box-shadow: 0 18px 45px rgba(15, 23, 42, .07);
    }

    .stat-card {
        padding: 1.2rem;
    }

    .stat-label {
        margin: 0;
        color: var(--muted);
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .stat-number {
        margin-top: .45rem;
        font-size: 2rem;
        line-height: 1;
        font-weight: 900;
    }

    .panel-header {
        padding: 1.35rem 1.35rem 0;
    }

    .panel-header h2 {
        margin: 0;
        font-size: 1.15rem;
    }

    .modern-form {
        padding: 1.35rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .field.full {
        grid-column: 1 / -1;
    }

    .field label {
        display: block;
        margin-bottom: .4rem;
        font-size: .86rem;
        font-weight: 800;
        color: #334155;
    }

    .field input,
    .field select {
        width: 100%;
        min-height: 46px;
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: .75rem .9rem;
        color: var(--ink);
        background: #fff;
        outline: none;
    }

    .field input:focus,
    .field select:focus,
    .search-box input:focus,
    .registry-filters select:focus {
        border-color: rgba(37, 99, 235, .55);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
    }

    .field input[readonly] {
        background: #f8fafc;
        color: #475569;
        cursor: not-allowed;
    }

    .phone-control {
        display: flex;
        align-items: center;
        border: 1px solid var(--line);
        border-radius: 14px;
        background: #fff;
        overflow: hidden;
    }

    .phone-prefix {
        display: inline-flex;
        align-items: center;
        min-height: 46px;
        padding: 0 .9rem;
        background: #eff6ff;
        color: #1d4ed8;
        font-weight: 900;
        border-right: 1px solid var(--line);
    }

    .phone-control input {
        border: 0;
        border-radius: 0;
    }

    .selected-programme-card {
        display: none;
        margin-top: .65rem;
        padding: .8rem;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid var(--line);
        color: #334155;
        font-size: .86rem;
        line-height: 1.45;
    }

    .selected-programme-card.is-visible {
        display: block;
    }

    .form-actions,
    .table-toolbar,
    .import-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .85rem;
        flex-wrap: wrap;
    }

    .form-actions {
        margin-top: 1.15rem;
    }

    .btn-modern {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        min-height: 42px;
        border: 0;
        border-radius: 999px;
        padding: .75rem 1rem;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
    }

    .btn-primary-modern {
        background: linear-gradient(135deg, var(--primary), #38bdf8);
        color: #fff;
    }

    .btn-light-modern {
        background: rgba(255, 255, 255, .16);
        border: 1px solid rgba(255, 255, 255, .28);
        color: #fff;
    }

    .btn-muted-modern {
        background: #eef2ff;
        color: #1d4ed8;
    }

    .btn-danger-modern {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-success-modern {
        background: #dcfce7;
        color: #166534;
    }

    .btn-edit-modern {
        background: #dbeafe;
        color: #1e40af;
    }

    .import-box {
        margin-bottom: 1rem;
        padding: 1.15rem;
        border: 1px dashed #93c5fd;
        border-radius: 18px;
        background: linear-gradient(180deg, #eff6ff, #fff);
    }

    .file-picker input {
        width: 100%;
        margin-top: .7rem;
    }

    .registry-card {
        margin-top: 1rem;
        overflow: hidden;
    }

    .table-toolbar {
        padding: 1.2rem 1.35rem;
        border-bottom: 1px solid var(--line);
        background: #fff;
    }

    .registry-heading h2 {
        margin: 0;
        font-size: 1.15rem;
    }

    .registry-heading p {
        margin: .35rem 0 0;
        color: var(--muted);
        font-size: .86rem;
    }

    .registry-filters {
        display: grid;
        grid-template-columns: minmax(230px, 1.4fr) minmax(130px, .7fr) minmax(130px, .7fr) minmax(130px, .7fr) minmax(95px, .45fr) auto auto;
        align-items: center;
        gap: .55rem;
        width: 100%;
        max-width: 1050px;
    }

    .registry-filters select {
        width: 100%;
        min-height: 42px;
        border: 1px solid var(--line);
        border-radius: 999px;
        padding: .62rem .8rem;
        background: #fff;
        color: var(--ink);
        outline: none;
    }

    .search-box {
        position: relative;
        min-width: 280px;
    }

    .search-box i {
        position: absolute;
        left: .95rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }

    .search-box input {
        width: 100%;
        min-height: 42px;
        border: 1px solid var(--line);
        border-radius: 999px;
        padding: .65rem .95rem .65rem 2.4rem;
        outline: none;
    }

    .table-scroll {
        overflow-x: auto;
    }

    .student-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 920px;
        font-size: .82rem;
    }

    .student-table th {
        background: #f8fafc;
        color: #475569;
        font-size: .68rem;
        letter-spacing: .06em;
        text-transform: uppercase;
        text-align: left;
        padding: .65rem .55rem;
        border-bottom: 1px solid var(--line);
    }

    .student-table td {
        padding: .7rem .55rem;
        border-bottom: 1px solid var(--line);
        vertical-align: middle;
        color: #334155;
    }

    .student-name {
        display: flex;
        flex-direction: column;
        gap: .25rem;
    }

    .student-name strong {
        color: var(--ink);
    }

    .muted-text {
        color: var(--muted);
        font-size: .78rem;
    }

    .programme-code {
        display: inline-flex;
        width: fit-content;
        margin-bottom: .35rem;
        border-radius: 999px;
        padding: .25rem .55rem;
        background: #e0f2fe;
        color: #075985;
        font-size: .75rem;
        font-weight: 900;
    }

    .badge-soft {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        border-radius: 999px;
        padding: .38rem .65rem;
        font-size: .75rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .badge-active {
        background: #dcfce7;
        color: #166534;
    }

    .badge-inactive {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-candidate {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-plain {
        background: #f1f5f9;
        color: #475569;
    }

    .badge-temp {
        background: #ffedd5;
        color: #9a3412;
    }

    .inline-form {
        margin: 0;
    }

    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: .35rem;
    }

    .action-buttons .btn-modern {
        width: 100%;
        min-height: 30px;
        padding: .38rem .5rem;
        border-radius: 10px;
        font-size: .72rem;
    }

    .pagination-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.35rem;
        border-top: 1px solid var(--line);
        background: #fff;
        flex-wrap: wrap;
    }

    .pagination-info {
        color: var(--muted);
        font-size: .86rem;
    }

    .pagination-links {
        display: flex;
        align-items: center;
        gap: .35rem;
        flex-wrap: wrap;
    }

    .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        min-height: 36px;
        padding: .45rem .7rem;
        border-radius: 12px;
        border: 1px solid var(--line);
        background: #fff;
        color: #334155;
        text-decoration: none;
        font-size: .82rem;
        font-weight: 800;
    }

    .page-link.is-current {
        background: #1d4ed8;
        border-color: #1d4ed8;
        color: #fff;
    }

    .page-link.is-disabled {
        opacity: .45;
        pointer-events: none;
    }

    .modal-backdrop[hidden] {
        display: none;
    }

    .modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 1000;
        display: grid;
        place-items: center;
        padding: 1rem;
        background: rgba(15, 23, 42, .58);
        backdrop-filter: blur(8px);
    }

    .edit-modal {
        width: min(760px, 100%);
        max-height: 92vh;
        overflow-y: auto;
    }

    .modal-titlebar {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.25rem 1.35rem 0;
    }

    .modal-titlebar h2 {
        margin: 0;
        font-size: 1.15rem;
    }

    .modal-titlebar p {
        margin: .35rem 0 0;
        color: var(--muted);
    }

    .modal-close {
        width: 36px;
        height: 36px;
        border: 0;
        border-radius: 999px;
        background: #f1f5f9;
        color: #0f172a;
        cursor: pointer;
        font-weight: 900;
    }

    body.modal-open {
        overflow: hidden;
    }

    .empty-state {
        padding: 2.5rem 1rem !important;
        text-align: center;
        color: var(--muted);
    }

    @media (max-width: 1100px) {
        .stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .forms-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 720px) {
        .students-hero,
        .table-toolbar,
        .form-actions,
        .import-actions,
        .pagination-footer {
            align-items: stretch;
            flex-direction: column;
        }

        .stats-grid,
        .form-grid,
        .registry-filters {
            grid-template-columns: 1fr;
        }

        .students-hero {
            padding: 1.4rem;
        }

        .search-box {
            min-width: 100%;
        }
    }
</style>

<div class="students-page">
    <section class="students-hero">
        <div>
            <p class="eyebrow"><i class="fas fa-users"></i> Student Registry</p>
            <h1>Manage student voters.</h1>
        </div>

        <div>
            <a class="btn-modern btn-light-modern" href="<?= e(app_url('admin/students.php?download_template=1')) ?>">
                <i class="fas fa-download"></i> CSV Template
            </a>
        </div>
    </section>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <section class="stats-grid">
        <div class="ui-card stat-card">
            <p class="stat-label">Total Students</p>
            <div class="stat-number"><?= $totalStudents ?></div>
        </div>

        <div class="ui-card stat-card">
            <p class="stat-label">Active Voters</p>
            <div class="stat-number"><?= $activeStudents ?></div>
        </div>

        <div class="ui-card stat-card">
            <p class="stat-label">Approved Candidates</p>
            <div class="stat-number"><?= $candidates ?></div>
        </div>

        <div class="ui-card stat-card">
            <p class="stat-label">Assigned Programmes</p>
            <div class="stat-number"><?= $assignedStudents ?></div>
        </div>

        <div class="ui-card stat-card">
            <p class="stat-label">Temporary Passwords</p>
            <div class="stat-number"><?= $temporaryPasswords ?></div>
        </div>
    </section>

    <section class="forms-grid">
        <div class="ui-card">
            <div class="panel-header">
                <h2>Add Student Voter</h2>
            </div>

            <form method="POST" class="modern-form">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="return_query" value="<?= e(students_current_query_string()) ?>">

                <div class="form-grid">
                    <div class="field">
                        <label for="student_no">Student Number</label>
                        <input id="student_no" type="text" name="student_no" placeholder="e.g. BCS/2026/001" required>
                    </div>

                    <div class="field">
                        <label for="full_name">Full Name</label>
                        <input id="full_name" type="text" name="full_name" placeholder="e.g. Amina John" required>
                    </div>

                    <div class="field">
                        <label for="email_preview">Generated Email</label>
                        <input id="email_preview" type="text" value="student@mzumbe.ac.tz" readonly aria-readonly="true">
                    </div>

                    <div class="field">
                        <label for="phone_local">Phone Number</label>
                        <div class="phone-control">
                            <span class="phone-prefix">+255</span>
                            <input id="phone_local" type="tel" name="phone_local" placeholder="712345678" pattern="[67][0-9]{8}" maxlength="9" inputmode="numeric" required>
                        </div>
                    </div>

                    <div class="field full">
                        <label for="programme_search">Programme / Course</label>
                        <input id="programme_search" list="programme_options" type="text" autocomplete="off" placeholder="Search programme code or name, e.g. BSc.ITS, Law, Accounting" required>
                        <input id="programme_code" type="hidden" name="programme_code">

                        <datalist id="programme_options">
                            <?php foreach ($programmesForJs as $programme): ?>
                                <option value="<?= e($programme['code'] . ' — ' . $programme['name']) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>

                        <div id="selected_programme" class="selected-programme-card"></div>
                    </div>

                    <div class="field">
                        <label for="study_year">Study Year</label>
                        <select id="study_year" name="study_year" required>
                            <option value="">Select programme first</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="password">Temporary Password</label>
                        <input id="password" type="text" name="password" placeholder="minimum 6 characters" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-modern btn-primary-modern">
                        <i class="fas fa-user-plus"></i> Create Student
                    </button>
                </div>
            </form>
        </div>

        <div class="ui-card">
            <div class="panel-header">
                <h2>Import Students</h2>
            </div>

            <form method="POST" enctype="multipart/form-data" class="modern-form">
                <input type="hidden" name="action" value="import">
                <input type="hidden" name="return_query" value="<?= e(students_current_query_string()) ?>">

                <div class="import-box">
                    <label class="file-picker" for="students_csv">
                        <strong><i class="fas fa-cloud-upload-alt"></i> Choose CSV file</strong>
                        <input id="students_csv" type="file" name="students_csv" accept=".csv,text/csv" required>
                    </label>
                </div>

                <div class="import-actions">
                    <a class="btn-modern btn-muted-modern" href="<?= e(app_url('admin/students.php?download_template=1')) ?>">
                        <i class="fas fa-download"></i> Template
                    </a>

                    <button type="submit" class="btn-modern btn-primary-modern">
                        <i class="fas fa-file-import"></i> Import CSV
                    </button>
                </div>
            </form>
        </div>
    </section>

    <section class="ui-card registry-card">
        <div class="table-toolbar">
            <div class="registry-heading">
                <h2>Student Registry</h2>
                <p>
                    Showing <?= $showingFrom ?>–<?= $showingTo ?> of <?= $filteredStudents ?><?= $hasRegistryFilters ? ' matching' : '' ?> students.
                    Total registered: <?= $totalStudents ?>.
                </p>
            </div>

            <form method="GET" action="<?= e(app_url('admin/students.php')) ?>" class="registry-filters">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search name, no, email, phone...">
                </div>

                <select name="faculty" aria-label="Filter by faculty">
                    <option value="">All faculties</option>
                    <?php foreach ($catalog as $facultyCodeOption => $facultyOption): ?>
                        <option value="<?= e($facultyCodeOption) ?>" <?= $facultyFilter === $facultyCodeOption ? 'selected' : '' ?>>
                            <?= e($facultyCodeOption) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="status" aria-label="Filter by status">
                    <option value="">All status</option>
                    <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $statusFilter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>

                <select name="candidate" aria-label="Filter by candidate status">
                    <option value="">All candidates</option>
                    <option value="yes" <?= $candidateFilter === 'yes' ? 'selected' : '' ?>>Candidates</option>
                    <option value="no" <?= $candidateFilter === 'no' ? 'selected' : '' ?>>Non-candidates</option>
                </select>

                <select name="per_page" aria-label="Students per page">
                    <?php foreach ($perPageOptions as $option): ?>
                        <option value="<?= $option ?>" <?= $perPage === $option ? 'selected' : '' ?>>
                            <?= $option ?>/page
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn-modern btn-primary-modern">
                    <i class="fas fa-filter"></i> Filter
                </button>

                <a class="btn-modern btn-muted-modern" href="<?= e(app_url('admin/students.php')) ?>">
                    Clear
                </a>
            </form>
        </div>

        <div class="table-scroll">
            <table class="student-table">
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Student</th>
                        <th>Student No</th>
                        <th>Programme</th>
                        <th>Status</th>
                        <th>Password</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                <?php $serial = $offset + 1; foreach ($students as $student): ?>
                    <?php
                        $programme = find_programme($programmes, (string)($student['programme_code'] ?? ''));
                        $facultyCode = $programme['faculty_code'] ?? ($student['faculty_code'] ?? '');
                        $facultyName = $programme['faculty_name'] ?? ($catalog[$facultyCode]['name'] ?? 'Not assigned');
                        $programmeLabel = $programme ? $programme['name'] : 'Not assigned';
                        $programmeCode = $programme['code'] ?? ($student['programme_code'] ?? '');
                        $phoneLocal = '';

                        if (preg_match('/^\+255([67][0-9]{8})$/', (string)($student['phone'] ?? ''), $phoneMatch)) {
                            $phoneLocal = $phoneMatch[1];
                        }
                    ?>

                    <tr>
                        <td><strong><?= $serial++ ?></strong></td>

                        <td>
                            <div class="student-name">
                                <strong><?= e($student['full_name']) ?></strong>
                                <span class="muted-text"><?= e($student['email']) ?></span>
                                <span class="muted-text"><?= e($student['phone'] ?: 'No phone') ?></span>
                            </div>
                        </td>

                        <td>
                            <strong><?= e($student['student_no']) ?></strong>
                        </td>

                        <td>
                            <?php if ($programmeCode): ?>
                                <span class="programme-code"><?= e($programmeCode) ?></span>
                            <?php endif; ?>

                            <div><?= e($programmeLabel) ?></div>
                            <span class="muted-text"><?= e($facultyCode ?: '—') ?> · <?= e($facultyName) ?></span><br>
                            <span class="muted-text">Year <?= (int)($student['study_year'] ?? 1) ?></span>
                        </td>

                        <td>
                            <span class="badge-soft <?= (int)$student['is_active'] === 1 ? 'badge-active' : 'badge-inactive' ?>">
                                <i class="fas <?= (int)$student['is_active'] === 1 ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                                <?= (int)$student['is_active'] === 1 ? 'Active' : 'Inactive' ?>
                            </span>

                            <br>

                            <span class="badge-soft <?= (int)$student['is_candidate'] === 1 ? 'badge-candidate' : 'badge-plain' ?>" style="margin-top: .35rem;">
                                <?= (int)$student['is_candidate'] === 1 ? 'Candidate' : 'Voter' ?>
                            </span>
                        </td>

                        <td>
                            <?php if ((int)($student['must_change_password'] ?? 0) === 1): ?>
                                <span class="badge-soft badge-temp">
                                    <i class="fas fa-key"></i> Temporary
                                </span>
                            <?php else: ?>
                                <span class="badge-soft badge-active">
                                    <i class="fas fa-lock"></i> Changed
                                </span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <div class="action-buttons">
                                <button
                                    type="button"
                                    class="btn-modern btn-edit-modern"
                                    data-edit-student
                                    data-id="<?= (int)$student['id'] ?>"
                                    data-student-no="<?= e($student['student_no']) ?>"
                                    data-full-name="<?= e($student['full_name']) ?>"
                                    data-phone-local="<?= e($phoneLocal) ?>"
                                    data-programme-code="<?= e($programmeCode) ?>"
                                    data-study-year="<?= (int)($student['study_year'] ?? 1) ?>"
                                >
                                    <i class="fas fa-edit"></i> Edit
                                </button>

                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="return_query" value="<?= e(students_current_query_string()) ?>">
                                    <input type="hidden" name="student_id" value="<?= (int)$student['id'] ?>">
                                    <input type="hidden" name="is_active" value="<?= (int)$student['is_active'] === 1 ? 0 : 1 ?>">

                                    <button type="submit" class="btn-modern <?= (int)$student['is_active'] === 1 ? 'btn-danger-modern' : 'btn-success-modern' ?>">
                                        <?= (int)$student['is_active'] === 1 ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>

                                <form method="POST" class="inline-form" data-delete-student-form>
                                    <input type="hidden" name="action" value="delete_student">
                                    <input type="hidden" name="return_query" value="<?= e(students_current_query_string()) ?>">
                                    <input type="hidden" name="student_id" value="<?= (int)$student['id'] ?>">

                                    <button type="submit" class="btn-modern btn-danger-modern">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$students): ?>
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="fas fa-user-graduate"></i><br>
                            No students registered yet. Add one manually or import a CSV file.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($filteredStudents > 0): ?>
            <div class="pagination-footer">
                <div class="pagination-info">
                    Page <?= $page ?> of <?= $totalPages ?> · <?= $perPage ?> students per page
                </div>

                <div class="pagination-links" aria-label="Student registry pagination">
                    <a class="page-link <?= $page <= 1 ? 'is-disabled' : '' ?>" href="<?= e(students_registry_url(['page' => 1])) ?>">First</a>
                    <a class="page-link <?= $page <= 1 ? 'is-disabled' : '' ?>" href="<?= e(students_registry_url(['page' => max(1, $page - 1)])) ?>">Prev</a>

                    <?php for ($pageNumber = $paginationStart; $pageNumber <= $paginationEnd; $pageNumber++): ?>
                        <a class="page-link <?= $pageNumber === $page ? 'is-current' : '' ?>" href="<?= e(students_registry_url(['page' => $pageNumber])) ?>">
                            <?= $pageNumber ?>
                        </a>
                    <?php endfor; ?>

                    <a class="page-link <?= $page >= $totalPages ? 'is-disabled' : '' ?>" href="<?= e(students_registry_url(['page' => min($totalPages, $page + 1)])) ?>">Next</a>
                    <a class="page-link <?= $page >= $totalPages ? 'is-disabled' : '' ?>" href="<?= e(students_registry_url(['page' => $totalPages])) ?>">Last</a>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <div id="editStudentModal" class="modal-backdrop" hidden>
        <div class="edit-modal ui-card" role="dialog" aria-modal="true" aria-labelledby="editStudentTitle">
            <div class="modal-titlebar">
                <div>
                    <h2 id="editStudentTitle">Edit Student</h2>
                    <p>Leave password empty to keep the current password. If you enter a new password, the student must change it on next login.</p>
                </div>

                <button type="button" class="modal-close" data-close-edit aria-label="Close edit form">
                    &times;
                </button>
            </div>

            <form method="POST" class="modern-form" id="editStudentForm">
                <input type="hidden" name="action" value="edit_student">
                <input type="hidden" name="return_query" value="<?= e(students_current_query_string()) ?>">
                <input id="edit_student_id" type="hidden" name="student_id">

                <div class="form-grid">
                    <div class="field">
                        <label for="edit_student_no">Student Number</label>
                        <input id="edit_student_no" type="text" name="student_no" required>
                    </div>

                    <div class="field">
                        <label for="edit_full_name">Full Name</label>
                        <input id="edit_full_name" type="text" name="full_name" required>
                    </div>

                    <div class="field">
                        <label for="edit_email_preview">Generated Email</label>
                        <input id="edit_email_preview" type="text" readonly aria-readonly="true">
                    </div>

                    <div class="field">
                        <label for="edit_phone_local">Phone Number</label>
                        <div class="phone-control">
                            <span class="phone-prefix">+255</span>
                            <input id="edit_phone_local" type="tel" name="phone_local" placeholder="712345678" pattern="[67][0-9]{8}" maxlength="9" inputmode="numeric" required>
                        </div>
                    </div>

                    <div class="field full">
                        <label for="edit_programme_search">Programme / Course</label>
                        <input id="edit_programme_search" list="programme_options" type="text" autocomplete="off" required>
                        <input id="edit_programme_code" type="hidden" name="programme_code">
                        <div id="edit_selected_programme" class="selected-programme-card"></div>
                    </div>

                    <div class="field">
                        <label for="edit_study_year">Study Year</label>
                        <select id="edit_study_year" name="study_year" required>
                            <option value="">Select programme first</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="edit_password">New Temporary Password</label>
                        <input id="edit_password" type="text" name="password" placeholder="leave empty to keep current password">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-modern btn-primary-modern">
                        <i class="fas fa-save"></i> Save Changes
                    </button>

                    <button type="button" class="btn-modern btn-muted-modern" data-close-edit>
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const PROGRAMMES = <?= json_encode($programmesForJs, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

function studentEmailBase(fullName) {
    fullName = String(fullName || '').toLowerCase().trim();
    fullName = fullName.replace(/[^a-z0-9]+/gi, '.').replace(/^\.+|\.+$/g, '');

    const parts = fullName.split('.').filter(Boolean);

    if (parts.length >= 2) {
        return parts[0] + '.' + parts[parts.length - 1];
    }

    if (parts.length === 1) {
        return parts[0];
    }

    return 'student';
}

function previewEmail(fullName) {
    return studentEmailBase(fullName) + '@mzumbe.ac.tz';
}

function normalizeProgrammeCode(code) {
    return String(code || '')
        .replace(/[–—−]/g, '-')
        .replace(/\s*-\s*/g, '-')
        .replace(/\s+/g, ' ')
        .trim()
        .toUpperCase();
}

function findProgrammeFromValue(value) {
    const raw = String(value || '').trim();
    const codePart = raw.split('—')[0].trim();
    const normalized = normalizeProgrammeCode(codePart);

    return PROGRAMMES.find(item => normalizeProgrammeCode(item.code) === normalized) || null;
}

function renderStudyYears(select, programme, selectedYear) {
    select.innerHTML = '';

    if (!programme) {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'Select programme first';
        select.appendChild(option);
        return;
    }

    const maxYear = Math.max(1, Math.ceil(parseFloat(programme.years || '1')));

    for (let year = 1; year <= maxYear; year++) {
        const option = document.createElement('option');
        option.value = String(year);
        option.textContent = 'Year ' + year;

        if (String(selectedYear || '') === String(year)) {
            option.selected = true;
        }

        select.appendChild(option);
    }
}

function renderProgrammeCard(card, programme) {
    if (!programme) {
        card.classList.remove('is-visible');
        card.innerHTML = '';
        return;
    }

    card.innerHTML =
        '<strong>' + programme.code + '</strong><br>' +
        programme.name + '<br>' +
        '<span>' + programme.faculty_code + ' · ' + programme.faculty_name + ' · ' + programme.years + ' years</span>';

    card.classList.add('is-visible');
}

function bindProgrammePicker(searchInput, hiddenInput, yearSelect, card, selectedYear) {
    function sync() {
        const programme = findProgrammeFromValue(searchInput.value);

        if (programme) {
            hiddenInput.value = programme.code;
            renderStudyYears(yearSelect, programme, selectedYear);
            renderProgrammeCard(card, programme);
        } else {
            hiddenInput.value = '';
            renderStudyYears(yearSelect, null, null);
            renderProgrammeCard(card, null);
        }
    }

    searchInput.addEventListener('input', function () {
        selectedYear = null;
        sync();
    });

    searchInput.addEventListener('change', function () {
        selectedYear = null;
        sync();
    });

    sync();
}

const fullNameInput = document.getElementById('full_name');
const emailPreviewInput = document.getElementById('email_preview');

if (fullNameInput && emailPreviewInput) {
    fullNameInput.addEventListener('input', function () {
        emailPreviewInput.value = previewEmail(fullNameInput.value);
    });
}

bindProgrammePicker(
    document.getElementById('programme_search'),
    document.getElementById('programme_code'),
    document.getElementById('study_year'),
    document.getElementById('selected_programme'),
    null
);

const editModal = document.getElementById('editStudentModal');
const editStudentId = document.getElementById('edit_student_id');
const editStudentNo = document.getElementById('edit_student_no');
const editFullName = document.getElementById('edit_full_name');
const editEmailPreview = document.getElementById('edit_email_preview');
const editPhoneLocal = document.getElementById('edit_phone_local');
const editProgrammeSearch = document.getElementById('edit_programme_search');
const editProgrammeCode = document.getElementById('edit_programme_code');
const editStudyYear = document.getElementById('edit_study_year');
const editSelectedProgramme = document.getElementById('edit_selected_programme');
const editPassword = document.getElementById('edit_password');

function openEditModal(button) {
    const programmeCode = button.dataset.programmeCode || '';
    const programme = PROGRAMMES.find(item => normalizeProgrammeCode(item.code) === normalizeProgrammeCode(programmeCode));

    editStudentId.value = button.dataset.id || '';
    editStudentNo.value = button.dataset.studentNo || '';
    editFullName.value = button.dataset.fullName || '';
    editEmailPreview.value = previewEmail(editFullName.value);
    editPhoneLocal.value = button.dataset.phoneLocal || '';
    editPassword.value = '';

    if (programme) {
        editProgrammeSearch.value = programme.code + ' — ' + programme.name;
        editProgrammeCode.value = programme.code;
        renderStudyYears(editStudyYear, programme, button.dataset.studyYear || '1');
        renderProgrammeCard(editSelectedProgramme, programme);
    } else {
        editProgrammeSearch.value = '';
        editProgrammeCode.value = '';
        renderStudyYears(editStudyYear, null, null);
        renderProgrammeCard(editSelectedProgramme, null);
    }

    editModal.hidden = false;
    document.body.classList.add('modal-open');
}

function closeEditModal() {
    editModal.hidden = true;
    document.body.classList.remove('modal-open');
}

document.querySelectorAll('[data-edit-student]').forEach(button => {
    button.addEventListener('click', function () {
        openEditModal(button);
    });
});

document.querySelectorAll('[data-close-edit]').forEach(button => {
    button.addEventListener('click', closeEditModal);
});

editModal.addEventListener('click', function (event) {
    if (event.target === editModal) {
        closeEditModal();
    }
});

if (editFullName && editEmailPreview) {
    editFullName.addEventListener('input', function () {
        editEmailPreview.value = previewEmail(editFullName.value);
    });
}

bindProgrammePicker(
    editProgrammeSearch,
    editProgrammeCode,
    editStudyYear,
    editSelectedProgramme,
    null
);

document.querySelectorAll('[data-delete-student-form]').forEach(form => {
    form.addEventListener('submit', function (event) {
        if (!confirm('Delete this student? This action cannot be undone.')) {
            event.preventDefault();
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>