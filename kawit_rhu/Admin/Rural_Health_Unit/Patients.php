<?php
session_start();

// Check if user is logged in and is RHU admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'rhu_admin') {
    header('Location: ../../login.php');
    exit;
}

// Database configuration
$host = 'localhost';
$dbname = 'kawit_rhu';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get staff information
$stmt = $pdo->prepare("
    SELECT s.*, u.username, u.last_login 
    FROM staff s 
    JOIN users u ON s.user_id = u.id 
    WHERE u.id = ? AND s.department = 'RHU'
");
$stmt->execute([$_SESSION['user_id']]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$staff) {
    header('Location: ../../login.php');
    exit;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'add_patient') {
        try {
            // Validate required fields
            $required_fields = ['username', 'password', 'first_name', 'last_name', 'date_of_birth', 'gender', 'address', 'civil_status'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Please fill in all required fields.");
                }
            }
            
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$_POST['username']]);
            if ($stmt->fetch()) {
                throw new Exception("Username already exists. Please choose another.");
            }
            
            // Check if email exists (if provided)
            if (!empty($_POST['email'])) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$_POST['email']]);
                if ($stmt->fetch()) {
                    throw new Exception("Email address already exists.");
                }
            }
            
            $pdo->beginTransaction();
            
            // Generate new patient ID
            $year = date('Y');
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM patients WHERE patient_id LIKE ?");
            $stmt->execute(["P-$year-%"]);
            $count = $stmt->fetchColumn();
            $patient_id = 'P-' . $year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            
            // Create user account
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (username, password, email, role) 
                VALUES (?, ?, ?, 'patient')
            ");
            $stmt->execute([
                $_POST['username'], 
                $hashed_password, 
                $_POST['email'] ?: null
            ]);
            $user_id = $pdo->lastInsertId();
            
            // Create patient record with NEW FIELDS
            $stmt = $pdo->prepare("
                INSERT INTO patients (
                    user_id, patient_id, first_name, middle_name, last_name, suffix,
                    date_of_birth, gender, civil_status, address, barangay_id, phone, email,
                    blood_type, allergies, philhealth_number, osca_number, family_number,
                    pwd_status, four_ps_status, occupation, educational_attainment,
                    religion, emergency_contact_name, emergency_contact_phone, 
                    emergency_contact_relationship, guardian_name, guardian_relationship
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id, $patient_id, $_POST['first_name'], $_POST['middle_name'], $_POST['last_name'], $_POST['suffix'],
                $_POST['date_of_birth'], $_POST['gender'], $_POST['civil_status'], $_POST['address'], 
                $_POST['barangay_id'] ?: null, $_POST['phone'], $_POST['email'],
                $_POST['blood_type'], $_POST['allergies'], $_POST['philhealth_number'], 
                $_POST['osca_number'] ?: null, $_POST['family_number'] ?: null,
                $_POST['pwd_status'] ?: 'NO', $_POST['four_ps_status'] ?: 'NO',
                $_POST['occupation'], $_POST['educational_attainment'], $_POST['religion'], 
                $_POST['emergency_contact_name'], $_POST['emergency_contact_phone'], 
                $_POST['emergency_contact_relationship'],
                $_POST['guardian_name'] ?: null, $_POST['guardian_relationship'] ?: null
            ]);
            
            // Log the action
            $logStmt = $pdo->prepare("
                INSERT INTO system_logs (user_id, action, module, record_id, new_values) 
                VALUES (?, 'PATIENT_REGISTERED', 'Patients', ?, ?)
            ");
            $logStmt->execute([
                $_SESSION['user_id'], 
                $pdo->lastInsertId(),
                json_encode(['patient_id' => $patient_id, 'registered_by' => $staff['first_name'] . ' ' . $staff['last_name']])
            ]);
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Patient registered successfully! Patient ID: ' . $patient_id]);
            
        } catch (Exception $e) {
            $pdo->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'update_patient') {
        try {
            $patient_id = $_POST['patient_id'];
            
            $pdo->beginTransaction();
            
            // Update patient record with NEW FIELDS
            $stmt = $pdo->prepare("
                UPDATE patients 
                SET civil_status = ?, address = ?, barangay_id = ?, phone = ?, email = ?,
                    blood_type = ?, allergies = ?, philhealth_number = ?, osca_number = ?, 
                    family_number = ?, pwd_status = ?, four_ps_status = ?,
                    occupation = ?, educational_attainment = ?, religion = ?, 
                    emergency_contact_name = ?, emergency_contact_phone = ?, 
                    emergency_contact_relationship = ?, guardian_name = ?, guardian_relationship = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([
                $_POST['civil_status'], $_POST['address'], $_POST['barangay_id'] ?: null,
                $_POST['phone'], $_POST['email'], $_POST['blood_type'], $_POST['allergies'],
                $_POST['philhealth_number'], $_POST['osca_number'] ?: null, 
                $_POST['family_number'] ?: null, $_POST['pwd_status'] ?: 'NO', 
                $_POST['four_ps_status'] ?: 'NO', $_POST['occupation'], 
                $_POST['educational_attainment'], $_POST['religion'], 
                $_POST['emergency_contact_name'], $_POST['emergency_contact_phone'],
                $_POST['emergency_contact_relationship'], $_POST['guardian_name'] ?: null, 
                $_POST['guardian_relationship'] ?: null, $patient_id
            ]);
            
            // Log the action
            $logStmt = $pdo->prepare("
                INSERT INTO system_logs (user_id, action, module, record_id, new_values) 
                VALUES (?, 'PATIENT_UPDATED', 'Patients', ?, ?)
            ");
            $logStmt->execute([
                $_SESSION['user_id'], 
                $patient_id,
                json_encode(['updated_by' => $staff['first_name'] . ' ' . $staff['last_name']])
            ]);
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Patient information updated successfully!']);
            
        } catch (Exception $e) {
            $pdo->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
        if ($_POST['action'] === 'get_patient') {
        $stmt = $pdo->prepare("
            SELECT p.*, b.barangay_name, u.username, u.email as user_email,
                   YEAR(CURDATE()) - YEAR(p.date_of_birth) as age
            FROM patients p 
            LEFT JOIN barangays b ON p.barangay_id = b.id
            LEFT JOIN users u ON p.user_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$_POST['patient_id']]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($patient) {
            // Get consultation history with all fields including risk assessment
            $consultStmt = $pdo->prepare("
                SELECT c.*, CONCAT(s.first_name, ' ', s.last_name) as doctor_name,
                       b2.barangay_name as consultation_location_name
                FROM consultations c
                LEFT JOIN staff s ON c.assigned_doctor = s.id
                LEFT JOIN barangays b2 ON c.barangay_id = b2.id
                WHERE c.patient_id = ?
                ORDER BY c.consultation_date DESC
                LIMIT 10
            ");
            $consultStmt->execute([$patient['id']]);
            $consultations = $consultStmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true, 
                'patient' => $patient,
                'consultations' => $consultations
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Patient not found']);
        }
        exit;
    }

    if ($_POST['action'] === 'deactivate_patient') {
        try {
            $patient_id = $_POST['patient_id'];
            
            $pdo->beginTransaction();
            
            // Deactivate patient record
            $stmt = $pdo->prepare("UPDATE patients SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$patient_id]);
            
            // Deactivate user account
            $stmt = $pdo->prepare("
                UPDATE users SET is_active = 0, updated_at = CURRENT_TIMESTAMP 
                WHERE id = (SELECT user_id FROM patients WHERE id = ?)
            ");
            $stmt->execute([$patient_id]);
            
            // Log the action
            $logStmt = $pdo->prepare("
                INSERT INTO system_logs (user_id, action, module, record_id, new_values) 
                VALUES (?, 'PATIENT_DEACTIVATED', 'Patients', ?, ?)
            ");
            $logStmt->execute([
                $_SESSION['user_id'], 
                $patient_id,
                json_encode(['deactivated_by' => $staff['first_name'] . ' ' . $staff['last_name']])
            ]);

            // Get user_id for notification
            $userStmt = $pdo->prepare("SELECT user_id FROM patients WHERE id = ?");
            $userStmt->execute([$patient_id]);
            $userResult = $userStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($userResult && $userResult['user_id']) {
                $notifStmt = $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, data, priority)
                    VALUES (?, 'system', 'Account Deactivated', ?, ?, 'high')
                ");
                $notifStmt->execute([
                    $userResult['user_id'],
                    "Your account has been deactivated. Please contact RHU for assistance.",
                    json_encode(['patient_id' => $patient_id])
                ]);
            }
            
            $pdo->commit();
            
            echo json_encode(['success' => true, 'message' => 'Patient account deactivated successfully.']);
            
        } catch (Exception $e) {
            $pdo->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($_POST['action'] === 'activate_patient') {
        try {
            $patient_id = $_POST['patient_id'];
            
            $pdo->beginTransaction();
            
            // Activate patient record
            $stmt = $pdo->prepare("UPDATE patients SET is_active = 1, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$patient_id]);
            
            // Activate user account
            $stmt = $pdo->prepare("
                UPDATE users SET is_active = 1, updated_at = CURRENT_TIMESTAMP 
                WHERE id = (SELECT user_id FROM patients WHERE id = ?)
            ");
            $stmt->execute([$patient_id]);
            
            // Log the action
            $logStmt = $pdo->prepare("
                INSERT INTO system_logs (user_id, action, module, record_id, new_values) 
                VALUES (?, 'PATIENT_ACTIVATED', 'Patients', ?, ?)
            ");
            $logStmt->execute([
                $_SESSION['user_id'], 
                $patient_id,
                json_encode(['activated_by' => $staff['first_name'] . ' ' . $staff['last_name']])
            ]);
            
            // Get user_id for notification
            $userStmt = $pdo->prepare("SELECT user_id FROM patients WHERE id = ?");
            $userStmt->execute([$patient_id]);
            $userResult = $userStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($userResult && $userResult['user_id']) {
                $notifStmt = $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, data, priority)
                    VALUES (?, 'system', 'Account Activated', ?, ?, 'medium')
                ");
                $notifStmt->execute([
                    $userResult['user_id'],
                    "Your account has been activated. You can now log in.",
                    json_encode(['patient_id' => $patient_id])
                ]);
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Patient account activated successfully.']);
            
        } catch (Exception $e) {
            $pdo->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($_POST['action'] === 'change_password') {
        try {
            $patient_id = $_POST['patient_id'];
            $new_password = $_POST['new_password'];
            
            // Validate password
            if (strlen($new_password) < 6) {
                throw new Exception("Password must be at least 6 characters long.");
            }
            
            $pdo->beginTransaction();
            
            // Get user_id
            $stmt = $pdo->prepare("SELECT user_id, patient_id, CONCAT(first_name, ' ', last_name) as patient_name FROM patients WHERE id = ?");
            $stmt->execute([$patient_id]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$patient) {
                throw new Exception("Patient not found.");
            }
            
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$hashed_password, $patient['user_id']]);
            
            // Log the action
            $logStmt = $pdo->prepare("
                INSERT INTO system_logs (user_id, action, module, record_id, new_values) 
                VALUES (?, 'PASSWORD_CHANGED', 'Patients', ?, ?)
            ");
            $logStmt->execute([
                $_SESSION['user_id'], 
                $patient_id,
                json_encode([
                    'changed_by' => $staff['first_name'] . ' ' . $staff['last_name'],
                    'patient_name' => $patient['patient_name']
                ])
            ]);
            
            // Send notification to patient
            $notifStmt = $pdo->prepare("
                INSERT INTO notifications (user_id, type, title, message, data, priority)
                VALUES (?, 'system', 'Password Changed', ?, ?, 'high')
            ");
            $notifStmt->execute([
                $patient['user_id'],
                "Your password has been changed by RHU admin. If you did not request this change, please contact the RHU immediately.",
                json_encode(['patient_id' => $patient['patient_id']])
            ]);
            
            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Password changed successfully!']);
            
        } catch (Exception $e) {
            $pdo->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$barangay_filter = $_GET['barangay'] ?? '';
$gender_filter = $_GET['gender'] ?? '';
$age_filter = $_GET['age'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 15;
$offset = ($page - 1) * $limit;

// Build query conditions
$conditions = ['1=1'];
$params = [];

if ($search) {
    $conditions[] = "(p.patient_id LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR CONCAT(p.first_name, ' ', p.last_name) LIKE ? OR p.phone LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
}

if ($barangay_filter) {
    $conditions[] = "p.barangay_id = ?";
    $params[] = $barangay_filter;
}

if ($gender_filter) {
    $conditions[] = "p.gender = ?";
    $params[] = $gender_filter;
}

if ($age_filter) {
    switch ($age_filter) {
        case 'child':
            $conditions[] = "YEAR(CURDATE()) - YEAR(p.date_of_birth) < 18";
            break;
        case 'adult':
            $conditions[] = "YEAR(CURDATE()) - YEAR(p.date_of_birth) BETWEEN 18 AND 59";
            break;
        case 'senior':
            $conditions[] = "YEAR(CURDATE()) - YEAR(p.date_of_birth) >= 60";
            break;
    }
}

if ($status_filter) {
    if ($status_filter === 'active') {
        $conditions[] = "p.is_active = 1";
    } else {
        $conditions[] = "p.is_active = 0";
    }
}

$where_clause = implode(' AND ', $conditions);

// Get total count for pagination
$countStmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM patients p 
    LEFT JOIN barangays b ON p.barangay_id = b.id
    WHERE $where_clause
");
$countStmt->execute($params);
$total_patients = $countStmt->fetchColumn();
$total_pages = ceil($total_patients / $limit);

// Get patients
$stmt = $pdo->prepare("
    SELECT p.*, b.barangay_name, u.username,
           YEAR(CURDATE()) - YEAR(p.date_of_birth) as age,
           (SELECT COUNT(*) FROM consultations WHERE patient_id = p.id AND status = 'completed') as total_consultations,
           (SELECT MAX(consultation_date) FROM consultations WHERE patient_id = p.id) as last_consultation
    FROM patients p 
    LEFT JOIN barangays b ON p.barangay_id = b.id
    LEFT JOIN users u ON p.user_id = u.id
    WHERE $where_clause
    ORDER BY p.created_at DESC
    LIMIT " . intval($limit) . " OFFSET " . intval($offset)
);
$stmt->execute($params);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get barangays for filters and forms
$barangaysStmt = $pdo->prepare("SELECT * FROM barangays WHERE is_active = 1 ORDER BY barangay_name");
$barangaysStmt->execute();
$barangays = $barangaysStmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_patients,
        COUNT(CASE WHEN p.is_active = 1 THEN 1 END) as active_patients,
        COUNT(CASE WHEN YEAR(CURDATE()) - YEAR(p.date_of_birth) < 18 THEN 1 END) as pediatric_patients,
        COUNT(CASE WHEN YEAR(CURDATE()) - YEAR(p.date_of_birth) >= 60 THEN 1 END) as senior_patients,
        COUNT(CASE WHEN DATE(p.created_at) = CURDATE() THEN 1 END) as new_today
    FROM patients p
");
$statsStmt->execute();
$statistics = $statsStmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients Management - RHU Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../Admin/style.css">
</head>
<body>
    <div class="main-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="../../Pictures/logo2.png" alt="Logo 1" onerror="this.style.display='none'">
                    <img src="../../Pictures/logo1.png" alt="Logo 2" onerror="this.style.display='none'">
                    <img src="../../Pictures/logo3.png" alt="Logo 3" onerror="this.style.display='none'">
                </div>
                <div class="logo-circle">
                    <i class="fas fa-hospital"></i>
                </div>
                <div class="logo-text">
                    RHU ADMIN<br>
                    <small style="font-size: 0.8rem; opacity: 0.8;">Kawit RHU</small>
                </div>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="Dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="Patients.php" class="nav-link active">
                        <i class="fas fa-users"></i>
                        <span>Patients</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="Consultation.php" class="nav-link">
                        <i class="fas fa-user-md"></i>
                        <span>Consultations</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="Queue_Checkin.php" class="nav-link">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Queue Check-in</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="Queue_Dashboard.php" class="nav-link">
                        <i class="fas fa-list-ol"></i>
                        <span>Queue Management</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="Queue_Display.php" class="nav-link">
                        <i class="fas fa-tv"></i>
                        <span>Queue Display</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="Referral.php" class="nav-link">
                        <i class="fas fa-share-alt"></i>
                        <span>Referrals</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="Laboratory.php" class="nav-link">
                        <i class="fas fa-flask"></i>
                        <span>Laboratory</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="Medical_Certificates.php" class="nav-link">
                        <i class="fas fa-certificate"></i>
                        <span>Medical Certificates</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="../../logout.php" class="nav-link" onclick="return confirm('Are you sure you want to log out?')">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Log out</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <nav class="top-navbar">
                <h1 class="page-title">Patients Management</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php 
                        $initials = strtoupper(substr($staff['first_name'], 0, 1) . substr($staff['last_name'], 0, 1));
                        echo $initials;
                        ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars($staff['position']); ?></div>
                    </div>
                    <!-- User Menu Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-link text-dark p-0 ms-2" type="button" data-bs-toggle="dropdown" style="text-decoration: none;">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" style="border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.15);">
                            <li>
                                <a class="dropdown-item" href="Profile.php" style="border-radius: 8px; margin: 2px;">
                                    <i class="fas fa-user-cog me-2" style="color: var(--kawit-pink);"></i>Profile & Settings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider" style="margin: 8px 0;"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="../../logout.php" onclick="return confirm('Are you sure you want to log out?')" style="border-radius: 8px; margin: 2px;">
                                    <i class="fas fa-sign-out-alt me-2"></i>Log Out
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Alert Messages -->
                <div id="alertMessage" style="display: none;"></div>

                <!-- Search and Filter Section -->
                <div class="content-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-search"></i>Search & Filter Patients
                        </h3>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPatientModal">
                            <i class="fas fa-plus me-2"></i>Add New Patient
                        </button>
                    </div>

                    <div class="search-filter-section">
                        <form method="GET" action="" id="filterForm">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Search</label>
                                    <input type="text" name="search" class="form-control search-input" 
                                           placeholder="Patient ID, name, or phone..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Barangay</label>
                                    <select name="barangay" class="form-select filter-select">
                                        <option value="">All Barangays</option>
                                        <?php foreach ($barangays as $barangay): ?>
                                            <option value="<?php echo $barangay['id']; ?>" 
                                                    <?php echo $barangay_filter == $barangay['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($barangay['barangay_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-select filter-select">
                                        <option value="">All Genders</option>
                                        <option value="Male" <?php echo $gender_filter == 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo $gender_filter == 'Female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo $gender_filter == 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Age Group</label>
                                    <select name="age" class="form-select filter-select">
                                        <option value="">All Ages</option>
                                        <option value="child" <?php echo $age_filter == 'child' ? 'selected' : ''; ?>>Under 18</option>
                                        <option value="adult" <?php echo $age_filter == 'adult' ? 'selected' : ''; ?>>18-59</option>
                                        <option value="senior" <?php echo $age_filter == 'senior' ? 'selected' : ''; ?>>60+</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select filter-select">
                                        <option value="">All Status</option>
                                        <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12 text-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-filter me-1"></i>Apply Filters
                                    </button>
                                    <a href="Patients.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Clear
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Patients List -->
                <div class="content-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-list"></i>Patients List 
                            <span class="badge bg-secondary ms-2"><?php echo number_format($total_patients); ?> total</span>
                        </h3>
                    </div>

                    <?php if (!empty($patients)): ?>
                        <!-- Desktop Table View -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Patient ID</th>
                                        <th>Name</th>
                                        <th>Age/Gender</th>
                                        <th>Contact</th>
                                        <th>Barangay</th>
                                        <th>Last Visit</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($patients as $patient): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($patient['patient_id']); ?></strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></strong>
                                                    <?php if ($patient['middle_name']): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($patient['middle_name']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="gender-<?php echo strtolower($patient['gender']); ?>">
                                                    <?php echo $patient['age']; ?> years / <?php echo $patient['gender']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($patient['phone']): ?>
                                                    <a href="tel:<?php echo $patient['phone']; ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($patient['phone']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No phone</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($patient['barangay_name'] ?? 'Not specified'); ?></td>
                                            <td>
                                                <?php if ($patient['last_consultation']): ?>
                                                    <?php echo date('M j, Y', strtotime($patient['last_consultation'])); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No visits</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $patient['is_active'] ? 'active' : 'inactive'; ?>">
                                                    <?php echo $patient['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="viewPatient(<?php echo $patient['id']; ?>)"
                                                            title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-secondary" 
                                                            onclick="editPatient(<?php echo $patient['id']; ?>)"
                                                            title="Edit Patient">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="Consultation.php?patient_id=<?php echo $patient['id']; ?>" 
                                                       class="btn btn-sm btn-outline-success"
                                                       title="New Consultation">
                                                        <i class="fas fa-user-md"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-warning" 
                                                            onclick="changePassword(<?php echo $patient['id']; ?>, '<?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>')"
                                                            title="Change Password">
                                                        <i class="fas fa-key"></i>
                                                    </button>
                                                    <?php if ($patient['is_active']): ?>
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="deactivatePatient(<?php echo $patient['id']; ?>)"
                                                                title="Deactivate Patient">
                                                            <i class="fas fa-user-slash"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-outline-success" 
                                                                onclick="activatePatient(<?php echo $patient['id']; ?>)"
                                                                title="Activate Patient">
                                                            <i class="fas fa-user-check"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <?php foreach ($patients as $patient): ?>
                            <div class="patient-card d-block d-md-none">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong><?php echo htmlspecialchars($patient['patient_id']); ?></strong>
                                        <span class="status-badge status-<?php echo $patient['is_active'] ? 'active' : 'inactive'; ?> ms-2">
                                            <?php echo $patient['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </div>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewPatient(<?php echo $patient['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="editPatient(<?php echo $patient['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" 
                                                onclick="changePassword(<?php echo $patient['id']; ?>, '<?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>')">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <?php if ($patient['is_active']): ?>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deactivatePatient(<?php echo $patient['id']; ?>)"
                                                    title="Deactivate Patient">
                                                <i class="fas fa-user-slash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-success" 
                                                    onclick="activatePatient(<?php echo $patient['id']; ?>)"
                                                    title="Activate Patient">
                                                <i class="fas fa-user-check"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <h5 class="mb-2"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></h5>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Age/Gender:</small><br>
                                        <span class="gender-<?php echo strtolower($patient['gender']); ?>">
                                            <?php echo $patient['age']; ?> / <?php echo $patient['gender']; ?>
                                        </span>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Contact:</small><br>
                                        <?php echo htmlspecialchars($patient['phone'] ?? 'No phone'); ?>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-6">
                                        <small class="text-muted">Barangay:</small><br>
                                        <?php echo htmlspecialchars($patient['barangay_name'] ?? 'Not specified'); ?>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Last Visit:</small><br>
                                        <?php if ($patient['last_consultation']): ?>
                                            <?php echo date('M j, Y', strtotime($patient['last_consultation'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">No visits</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination-container">
                                <div>
                                    Showing <?php echo (($page - 1) * $limit) + 1; ?> to <?php echo min($page * $limit, $total_patients); ?> 
                                    of <?php echo number_format($total_patients); ?> patients
                                </div>
                                <nav>
                                    <ul class="pagination">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $barangay_filter ? '&barangay=' . $barangay_filter : ''; ?><?php echo $gender_filter ? '&gender=' . $gender_filter : ''; ?><?php echo $age_filter ? '&age=' . $age_filter : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">First</a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $barangay_filter ? '&barangay=' . $barangay_filter : ''; ?><?php echo $gender_filter ? '&gender=' . $gender_filter : ''; ?><?php echo $age_filter ? '&age=' . $age_filter : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">Previous</a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php
                                        $start_page = max(1, $page - 2);
                                        $end_page = min($total_pages, $page + 2);
                                        for ($i = $start_page; $i <= $end_page; $i++):
                                        ?>
                                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $barangay_filter ? '&barangay=' . $barangay_filter : ''; ?><?php echo $gender_filter ? '&gender=' . $gender_filter : ''; ?><?php echo $age_filter ? '&age=' . $age_filter : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $barangay_filter ? '&barangay=' . $barangay_filter : ''; ?><?php echo $gender_filter ? '&gender=' . $gender_filter : ''; ?><?php echo $age_filter ? '&age=' . $age_filter : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">Next</a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $barangay_filter ? '&barangay=' . $barangay_filter : ''; ?><?php echo $gender_filter ? '&gender=' . $gender_filter : ''; ?><?php echo $age_filter ? '&age=' . $age_filter : ''; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">Last</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5>No Patients Found</h5>
                            <p class="text-muted">
                                <?php if ($search || $barangay_filter || $gender_filter || $age_filter): ?>
                                    No patients match your search criteria. Try adjusting your filters.
                                <?php else: ?>
                                    There are no patients registered in the system yet.
                                <?php endif; ?>
                            </p>
                            <?php if (!$search && !$barangay_filter): ?>
                                <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addPatientModal">
                                    <i class="fas fa-plus me-2"></i>Add First Patient
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Patient Modal -->
    <div class="modal fade" id="addPatientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>Add New Patient
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addPatientForm">
                        <!-- Account Information -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">Account Information</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Username *</label>
                                    <input type="text" class="form-control" name="username" autocomplete="off" required>
                                    <small class="text-muted">Used for patient login</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password *</label>
                                    <input type="password" class="form-control" name="password" autocomplete="new-password" required>
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">Personal Information</h6>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">First Name *</label>
                                    <input type="text" class="form-control" name="first_name" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Middle Name</label>
                                    <input type="text" class="form-control" name="middle_name">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" name="last_name" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Suffix</label>
                                    <input type="text" class="form-control" name="suffix" placeholder="Jr., Sr., III">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Date of Birth *</label>
                                    <input type="date" class="form-control" name="date_of_birth" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Gender *</label>
                                    <select class="form-select" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Civil Status *</label>
                                    <select class="form-select" name="civil_status" required>
                                        <option value="">Select Status</option>
                                        <option value="Single">Single</option>
                                        <option value="Married">Married</option>
                                        <option value="Widowed">Widowed</option>
                                        <option value="Divorced">Divorced</option>
                                        <option value="Separated">Separated</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">Contact & Address Information</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" name="phone" placeholder="09XX-XXX-XXXX">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" name="email">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Barangay</label>
                                    <select class="form-select" name="barangay_id">
                                        <option value="">Select Barangay</option>
                                        <?php foreach ($barangays as $barangay): ?>
                                            <option value="<?php echo $barangay['id']; ?>">
                                                <?php echo htmlspecialchars($barangay['barangay_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Complete Address *</label>
                                    <textarea class="form-control" name="address" rows="2" required placeholder="House number, street, subdivision, etc."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">Additional Information</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Occupation</label>
                                    <input type="text" class="form-control" name="occupation">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Educational Attainment</label>
                                    <select class="form-select" name="educational_attainment">
                                        <option value="">Select Education Level</option>
                                        <option value="Elementary">Elementary</option>
                                        <option value="High School">High School</option>
                                        <option value="Senior High School">Senior High School</option>
                                        <option value="Vocational">Vocational/Technical</option>
                                        <option value="College Undergraduate">College Undergraduate</option>
                                        <option value="College Graduate">College Graduate</option>
                                        <option value="Masteral">Masteral</option>
                                        <option value="Doctoral">Doctoral</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Religion</label>
                                <input type="text" class="form-control" name="religion" placeholder="e.g. Roman Catholic, Protestant, Islam">
                            </div>
                        </div>

                        <!-- Government Programs & IDs - NEW SECTION -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">Government Programs & Identification</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">PhilHealth Number</label>
                                    <input type="text" class="form-control" name="philhealth_number" placeholder="XX-XXXXXXXXX-X">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">OSCA Number (Senior Citizen)</label>
                                    <input type="text" class="form-control" name="osca_number" placeholder="For 60+ years old">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Family Number</label>
                                    <input type="text" class="form-control" name="family_number" placeholder="Family ID Number">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">PWD Status (Person with Disability)</label>
                                    <select class="form-select" name="pwd_status">
                                        <option value="NO">No</option>
                                        <option value="YES">Yes</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">NHTS 4Ps Member</label>
                                    <select class="form-select" name="four_ps_status">
                                        <option value="NO">No</option>
                                        <option value="YES">Yes</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Medical Information -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">Medical Information</h6>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Blood Type</label>
                                    <select class="form-select" name="blood_type">
                                        <option value="">Select Blood Type</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Known Allergies</label>
                                <textarea class="form-control" name="allergies" rows="2" placeholder="List any known allergies or write 'None'"></textarea>
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">Emergency Contact</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Contact Name</label>
                                    <input type="text" class="form-control" name="emergency_contact_name">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Contact Phone</label>
                                    <input type="tel" class="form-control" name="emergency_contact_phone">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Relationship</label>
                                    <select class="form-select" name="emergency_contact_relationship">
                                        <option value="">Select Relationship</option>
                                        <option value="Spouse">Spouse</option>
                                        <option value="Parent">Parent</option>
                                        <option value="Child">Child</option>
                                        <option value="Sibling">Sibling</option>
                                        <option value="Guardian">Guardian</option>
                                        <option value="Friend">Friend</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Guardian Information - NEW SECTION -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">Guardian Information (For Pediatric/Dependent Patients)</h6>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Fill this section if patient is a minor or requires a guardian
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Guardian Name</label>
                                    <input type="text" class="form-control" name="guardian_name" placeholder="Full name of guardian">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Relationship to Patient</label>
                                    <select class="form-select" name="guardian_relationship">
                                        <option value="">Select Relationship</option>
                                        <option value="Parent">Parent</option>
                                        <option value="Legal Guardian">Legal Guardian</option>
                                        <option value="Grandparent">Grandparent</option>
                                        <option value="Sibling">Sibling</option>
                                        <option value="Other Relative">Other Relative</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="savePatient">
                        <i class="fas fa-save me-2"></i>Register Patient
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Patient Modal -->
    <div class="modal fade" id="editPatientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-edit me-2"></i>Edit Patient Information
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editPatientForm">
                        <input type="hidden" name="patient_id" id="edit_patient_id">
                        
                        <!-- Personal Information (Non-editable) -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">Personal Information (System Record)</h6>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Basic personal information cannot be edited. Contact system administrator for changes.
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-muted">Patient ID</label>
                                    <input type="text" class="form-control" id="edit_patient_id_display" readonly>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-muted">First Name</label>
                                    <input type="text" class="form-control" id="edit_first_name" readonly>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-muted">Last Name</label>
                                    <input type="text" class="form-control" id="edit_last_name" readonly>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-muted">Date of Birth</label>
                                    <input type="text" class="form-control" id="edit_date_of_birth" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Government Programs & IDs - NEW SECTION -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">Government Programs & Identification</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">PhilHealth Number</label>
                                    <input type="text" class="form-control" name="philhealth_number" id="edit_philhealth_number">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">OSCA Number</label>
                                    <input type="text" class="form-control" name="osca_number" id="edit_osca_number">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Family Number</label>
                                    <input type="text" class="form-control" name="family_number" id="edit_family_number">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">PWD Status</label>
                                    <select class="form-select" name="pwd_status" id="edit_pwd_status">
                                        <option value="NO">No</option>
                                        <option value="YES">Yes</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">NHTS 4Ps Member</label>
                                    <select class="form-select" name="four_ps_status" id="edit_four_ps_status">
                                        <option value="NO">No</option>
                                        <option value="YES">Yes</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Editable Information -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">Contact & Address Information</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Civil Status</label>
                                    <select class="form-select" name="civil_status" id="edit_civil_status">
                                        <option value="Single">Single</option>
                                        <option value="Married">Married</option>
                                        <option value="Widowed">Widowed</option>
                                        <option value="Divorced">Divorced</option>
                                        <option value="Separated">Separated</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" name="phone" id="edit_phone">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" name="email" id="edit_email">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Barangay</label>
                                    <select class="form-select" name="barangay_id" id="edit_barangay_id">
                                        <option value="">Select Barangay</option>
                                        <?php foreach ($barangays as $barangay): ?>
                                            <option value="<?php echo $barangay['id']; ?>">
                                                <?php echo htmlspecialchars($barangay['barangay_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Complete Address</label>
                                <textarea class="form-control" name="address" id="edit_address" rows="2"></textarea>
                            </div>
                        </div>

                        <!-- Medical Information -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">Medical Information</h6>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Blood Type</label>
                                    <select class="form-select" name="blood_type" id="edit_blood_type">
                                        <option value="">Select Blood Type</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Known Allergies</label>
                                <textarea class="form-control" name="allergies" id="edit_allergies" rows="2"></textarea>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">Additional Information</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Occupation</label>
                                    <input type="text" class="form-control" name="occupation" id="edit_occupation">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Educational Attainment</label>
                                    <select class="form-select" name="educational_attainment" id="edit_educational_attainment">
                                        <option value="">Select Education Level</option>
                                        <option value="Elementary">Elementary</option>
                                        <option value="High School">High School</option>
                                        <option value="Senior High School">Senior High School</option>
                                        <option value="Vocational">Vocational/Technical</option>
                                        <option value="College Undergraduate">College Undergraduate</option>
                                        <option value="College Graduate">College Graduate</option>
                                        <option value="Masteral">Masteral</option>
                                        <option value="Doctoral">Doctoral</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Religion</label>
                                <input type="text" class="form-control" name="religion" id="edit_religion">
                            </div>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">Emergency Contact</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Contact Name</label>
                                    <input type="text" class="form-control" name="emergency_contact_name" id="edit_emergency_contact_name">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Contact Phone</label>
                                    <input type="tel" class="form-control" name="emergency_contact_phone" id="edit_emergency_contact_phone">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Relationship</label>
                                    <select class="form-select" name="emergency_contact_relationship" id="edit_emergency_contact_relationship">
                                        <option value="">Select Relationship</option>
                                        <option value="Spouse">Spouse</option>
                                        <option value="Parent">Parent</option>
                                        <option value="Child">Child</option>
                                        <option value="Sibling">Sibling</option>
                                        <option value="Guardian">Guardian</option>
                                        <option value="Friend">Friend</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Guardian Information - NEW SECTION -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">Guardian Information (For Pediatric/Dependent Patients)</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Guardian Name</label>
                                    <input type="text" class="form-control" name="guardian_name" id="edit_guardian_name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Relationship to Patient</label>
                                    <select class="form-select" name="guardian_relationship" id="edit_guardian_relationship">
                                        <option value="">Select Relationship</option>
                                        <option value="Parent">Parent</option>
                                        <option value="Legal Guardian">Legal Guardian</option>
                                        <option value="Grandparent">Grandparent</option>
                                        <option value="Sibling">Sibling</option>
                                        <option value="Other Relative">Other Relative</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="updatePatient">
                        <i class="fas fa-save me-2"></i>Update Information
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Patient Modal -->
    <div class="modal fade" id="viewPatientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user me-2"></i>Patient Information
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="viewPatientContent">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="editFromView">
                        <i class="fas fa-edit me-2"></i>Edit Patient
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                    <h5 class="modal-title">
                        <i class="fas fa-key me-2"></i>Change Patient Password
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Security Notice:</strong> The patient should change this password after logging in.
                    </div>
                    
                    <!-- Alert container for validation messages -->
                    <div id="passwordChangeAlert" style="display: none;"></div>
                    
                    <form id="changePasswordForm">
                        <input type="hidden" id="change_password_patient_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Patient Name</label>
                            <input type="text" class="form-control" id="change_password_patient_name" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">New Password *</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" required minlength="6" placeholder="Minimum 6 characters">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="text-muted">Minimum 6 characters required</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password *</label>
                            <input type="password" class="form-control" id="confirm_password" required minlength="6" placeholder="Re-enter password">
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="patientRequestedChange">
                            <label class="form-check-label" for="patientRequestedChange">
                                Patient requested this password change in person
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmChangePassword">
                        <i class="fas fa-check me-2"></i>Change Password
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Check session on page load
        window.onload = function() {
            <?php if (!isset($_SESSION['user_id'])): ?>
                window.location.href = '../../login.php';
            <?php endif; ?>
        };

        function showAlert(message, type = 'danger') {
            const alertDiv = document.getElementById('alertMessage');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertDiv.style.display = 'block';
            
            // Scroll to top to show alert
            window.scrollTo({top: 0, behavior: 'smooth'});
            
            if (type === 'success') {
                setTimeout(() => {
                    alertDiv.style.display = 'none';
                }, 5000);
            }
        }

        // Save new patient
        document.getElementById('savePatient').addEventListener('click', function() {
            const form = document.getElementById('addPatientForm');
            const formData = new FormData(form);
            formData.append('action', 'add_patient');

            // Validate required fields
            const requiredFields = ['username', 'password', 'first_name', 'last_name', 'date_of_birth', 'gender', 'civil_status', 'address'];
            let isValid = true;
            
            requiredFields.forEach(field => {
                const input = form.querySelector(`[name="${field}"]`);
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                showAlert('Please fill in all required fields marked with *');
                return;
            }

            // Validate password length
            if (formData.get('password').length < 6) {
                showAlert('Password must be at least 6 characters long');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Registering...';

            fetch('Patients.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('addPatientModal')).hide();
                    form.reset();
                    
                    // Reload page after short delay
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while registering the patient. Please try again.');
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-save me-2"></i>Register Patient';
            });
        });

        // Edit patient
        function editPatient(patientId) {
            const modal = new bootstrap.Modal(document.getElementById('editPatientModal'));
            
            const formData = new FormData();
            formData.append('action', 'get_patient');
            formData.append('patient_id', patientId);

            fetch('Patients.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const patient = data.patient;
                    
                    // Fill non-editable fields
                    document.getElementById('edit_patient_id').value = patient.id;
                    document.getElementById('edit_patient_id_display').value = patient.patient_id;
                    document.getElementById('edit_first_name').value = patient.first_name;
                    document.getElementById('edit_last_name').value = patient.last_name;
                    document.getElementById('edit_date_of_birth').value = new Date(patient.date_of_birth).toLocaleDateString();
                    
                    // Fill editable fields - WITH NEW FIELDS
                    document.getElementById('edit_civil_status').value = patient.civil_status || '';
                    document.getElementById('edit_phone').value = patient.phone || '';
                    document.getElementById('edit_email').value = patient.email || '';
                    document.getElementById('edit_barangay_id').value = patient.barangay_id || '';
                    document.getElementById('edit_address').value = patient.address || '';
                    document.getElementById('edit_blood_type').value = patient.blood_type || '';
                    document.getElementById('edit_philhealth_number').value = patient.philhealth_number || '';
                    document.getElementById('edit_osca_number').value = patient.osca_number || '';
                    document.getElementById('edit_family_number').value = patient.family_number || '';
                    document.getElementById('edit_pwd_status').value = patient.pwd_status || 'NO';
                    document.getElementById('edit_four_ps_status').value = patient.four_ps_status || 'NO';
                    document.getElementById('edit_allergies').value = patient.allergies || '';
                    document.getElementById('edit_guardian_name').value = patient.guardian_name || '';
                    document.getElementById('edit_guardian_relationship').value = patient.guardian_relationship || '';
                    document.getElementById('edit_occupation').value = patient.occupation || '';
                    document.getElementById('edit_educational_attainment').value = patient.educational_attainment || '';
                    document.getElementById('edit_religion').value = patient.religion || '';
                    document.getElementById('edit_emergency_contact_name').value = patient.emergency_contact_name || '';
                    document.getElementById('edit_emergency_contact_phone').value = patient.emergency_contact_phone || '';
                    document.getElementById('edit_emergency_contact_relationship').value = patient.emergency_contact_relationship || '';
                    
                    modal.show();
                } else {
                    showAlert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error loading patient information.');
            });
        }

        // Update patient
        document.getElementById('updatePatient').addEventListener('click', function() {
            const form = document.getElementById('editPatientForm');
            const formData = new FormData(form);
            formData.append('action', 'update_patient');

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';

            fetch('Patients.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('editPatientModal')).hide();
                    
                    // Reload page after short delay
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while updating patient information.');
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-save me-2"></i>Update Information';
            });
        });

        // View patient (simplified version - you can expand this)
        function viewPatient(patientId) {
            const modal = new bootstrap.Modal(document.getElementById('viewPatientModal'));
            const content = document.getElementById('viewPatientContent');
            
            content.innerHTML = '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary"></i><br><p class="mt-3">Loading patient information...</p></div>';
            modal.show();

            const formData = new FormData();
            formData.append('action', 'get_patient');
            formData.append('patient_id', patientId);

            fetch('Patients.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const patient = data.patient;
                    
let html = `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-0 mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="fas fa-user me-2"></i>Personal Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <strong>Patient ID:</strong> ${patient.patient_id}<br>
                                        <strong>Full Name:</strong> ${patient.first_name} ${patient.middle_name || ''} ${patient.last_name} ${patient.suffix || ''}<br>
                                        <strong>Date of Birth:</strong> ${new Date(patient.date_of_birth).toLocaleDateString()}<br>
                                        <strong>Age:</strong> ${patient.age} years old<br>
                                        <strong>Gender:</strong> ${patient.gender}<br>
                                        <strong>Civil Status:</strong> ${patient.civil_status}<br>
                                        <strong>Blood Type:</strong> <span class="badge bg-danger">${patient.blood_type || 'Not specified'}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 mb-3">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Contact & Address</h6>
                                    </div>
                                    <div class="card-body">
                                        <strong>Phone:</strong> ${patient.phone || 'Not provided'}<br>
                                        <strong>Email:</strong> ${patient.email || 'Not provided'}<br>
                                        <strong>Address:</strong> ${patient.address}<br>
                                        <strong>Barangay:</strong> ${patient.barangay_name || 'Not specified'}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 mb-3">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="fas fa-id-card me-2"></i>Government Programs</h6>
                                    </div>
                                    <div class="card-body">
                                        <strong>PhilHealth:</strong> ${patient.philhealth_number || 'Not provided'}<br>
                                        <strong>OSCA Number:</strong> ${patient.osca_number || 'Not provided'}<br>
                                        <strong>Family Number:</strong> ${patient.family_number || 'Not provided'}<br>
                                        <strong>PWD Status:</strong> <span class="badge bg-${patient.pwd_status === 'YES' ? 'warning' : 'secondary'}">${patient.pwd_status || 'NO'}</span><br>
                                        <strong>4Ps Member:</strong> <span class="badge bg-${patient.four_ps_status === 'YES' ? 'warning' : 'secondary'}">${patient.four_ps_status || 'NO'}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 mb-3">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="fas fa-user-shield me-2"></i>Guardian Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <strong>Guardian Name:</strong> ${patient.guardian_name || 'Not provided'}<br>
                                        <strong>Relationship:</strong> ${patient.guardian_relationship || 'Not specified'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Add Medical History Section
                    if (data.consultations && data.consultations.length > 0) {
                        html += `
                            <div class="card border-0 mb-3">
                                <div class="card-header bg-danger text-white">
                                    <h6 class="mb-0"><i class="fas fa-notes-medical me-2"></i>Recent Medical History (Last 10 Consultations)</h6>
                                </div>
                                <div class="card-body">
                        `;
                        
                        data.consultations.forEach((consult, index) => {
                            const consultDate = new Date(consult.consultation_date).toLocaleDateString('en-US', { 
                                year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' 
                            });
                            
                            html += `
                                <div class="consultation-history-item" style="border-left: 4px solid #dc3545; padding-left: 15px; margin-bottom: 20px;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong style="color: #dc3545;">${consult.consultation_number}</strong>
                                            <span class="badge bg-${consult.status === 'completed' ? 'success' : consult.status === 'in_progress' ? 'primary' : 'warning'} ms-2">
                                                ${consult.status.replace('_', ' ').toUpperCase()}
                                            </span>
                                        </div>
                                        <small class="text-muted">${consultDate}</small>
                                    </div>
                                    
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-6">
                                            <strong>Chief Complaint:</strong> ${consult.chief_complaint || 'N/A'}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Diagnosis:</strong> ${consult.diagnosis || 'N/A'}
                                        </div>
                                    </div>
            `;
                            
                            // Risk Assessment Section
                            const hasRiskData = consult.current_smoker || consult.alcohol_intake || consult.illicit_drug_use || 
                                              consult.eat_unhealthy_foods || consult.regular_exercise || consult.with_hypertension || 
                                              consult.with_diabetes;
                            
                            if (hasRiskData) {
                                html += `
                                    <div class="alert alert-warning mb-2" style="padding: 10px;">
                                        <strong><i class="fas fa-exclamation-triangle me-2"></i>Risk Assessment:</strong>
                                        <div class="row g-2 mt-2">
                                `;
                                
                                // Current Smoker
                                if (consult.current_smoker === 'YES') {
                                    html += '<div class="col-auto"><span class="badge bg-danger"><i class="fas fa-times me-1"></i>Smoker</span></div>';
                                } else if (consult.current_smoker === 'NO') {
                                    html += '<div class="col-auto"><span class="badge bg-success"><i class="fas fa-check me-1"></i>Non-Smoker</span></div>';
                                }
                                
                                // Alcohol
                                if (consult.alcohol_intake === 'YES') {
                                    html += '<div class="col-auto"><span class="badge bg-danger"><i class="fas fa-times me-1"></i>Alcohol</span></div>';
                                } else if (consult.alcohol_intake === 'NO') {
                                    html += '<div class="col-auto"><span class="badge bg-success"><i class="fas fa-check me-1"></i>No Alcohol</span></div>';
                                }
                                
                                // Drugs
                                if (consult.illicit_drug_use === 'YES') {
                                    html += '<div class="col-auto"><span class="badge bg-danger"><i class="fas fa-times me-1"></i>Drug Use</span></div>';
                                } else if (consult.illicit_drug_use === 'NO') {
                                    html += '<div class="col-auto"><span class="badge bg-success"><i class="fas fa-check me-1"></i>No Drugs</span></div>';
                                }
                                
                                // Diet
                                if (consult.eat_unhealthy_foods === 'YES') {
                                    html += '<div class="col-auto"><span class="badge bg-warning text-dark"><i class="fas fa-times me-1"></i>Unhealthy Diet</span></div>';
                                } else if (consult.eat_unhealthy_foods === 'NO') {
                                    html += '<div class="col-auto"><span class="badge bg-success"><i class="fas fa-check me-1"></i>Healthy Diet</span></div>';
                                }
                                
                                // Exercise
                                if (consult.regular_exercise === 'YES') {
                                    html += '<div class="col-auto"><span class="badge bg-success"><i class="fas fa-check me-1"></i>Exercise</span></div>';
                                } else if (consult.regular_exercise === 'NO') {
                                    html += '<div class="col-auto"><span class="badge bg-danger"><i class="fas fa-times me-1"></i>No Exercise</span></div>';
                                }
                                
                                // Hypertension
                                if (consult.with_hypertension === 'YES') {
                                    html += '<div class="col-auto"><span class="badge bg-danger"><i class="fas fa-times me-1"></i>Hypertension</span>';
                                    if (consult.hypertension_meds_from_rhu === 'YES') html += ' <small>(RHU Meds)</small>';
                                    html += '</div>';
                                } else if (consult.with_hypertension === 'NO') {
                                    html += '<div class="col-auto"><span class="badge bg-success"><i class="fas fa-check me-1"></i>No Hypertension</span></div>';
                                }
                                
                                // Diabetes
                                if (consult.with_diabetes === 'YES') {
                                    html += '<div class="col-auto"><span class="badge bg-danger"><i class="fas fa-times me-1"></i>Diabetes</span>';
                                    if (consult.diabetes_meds_from_rhu === 'YES') html += ' <small>(RHU Meds)</small>';
                                    html += '</div>';
                                } else if (consult.with_diabetes === 'NO') {
                                    html += '<div class="col-auto"><span class="badge bg-success"><i class="fas fa-check me-1"></i>No Diabetes</span></div>';
                                }
                                
                                html += '</div></div>';
                            }
                            
                            // Doctor and Location
                            html += `
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-user-md me-1"></i><strong>Doctor:</strong> ${consult.doctor_name || 'Not assigned'} | 
                                            <i class="fas fa-map-marker-alt me-1"></i><strong>Location:</strong> ${consult.consultation_location || 'RHU'}
                                        </small>
                                    </div>
                                </div>
                            `;
                            
                            if (index < data.consultations.length - 1) {
                                html += '<hr style="margin: 15px 0;">';
                            }
                        });
                        
                        html += `
                                </div>
                            </div>
                        `;
                    } else {
                        html += `
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>No consultation history found for this patient.
                            </div>
                        `;
                    }
                    
                    content.innerHTML = html;
                    
                    // Store patient ID for edit button
                    document.getElementById('editFromView').setAttribute('data-patient-id', patient.id);
                } else {
                    content.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = '<div class="alert alert-danger">Error loading patient information.</div>';
            });
        }

        // Edit from view modal
        document.getElementById('editFromView').addEventListener('click', function() {
            const patientId = this.getAttribute('data-patient-id');
            bootstrap.Modal.getInstance(document.getElementById('viewPatientModal')).hide();
            setTimeout(() => editPatient(patientId), 300);
        });

        // Deactivate patient
        function deactivatePatient(patientId) {
            if (!confirm('Are you sure you want to deactivate this patient account? They will not be able to log in.')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'deactivate_patient');
            formData.append('patient_id', patientId);
            
            fetch('Patients.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred. Please try again.');
            });
        }

        // Activate patient
        function activatePatient(patientId) {
            if (!confirm('Are you sure you want to activate this patient account?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'activate_patient');
            formData.append('patient_id', patientId);
            
            fetch('Patients.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred. Please try again.');
            });
        }

        // Change Password Function
        function changePassword(patientId, patientName) {
            document.getElementById('change_password_patient_id').value = patientId;
            document.getElementById('change_password_patient_name').value = patientName;
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
            document.getElementById('patientRequestedChange').checked = false;
            
            const modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
            modal.show();
        }

        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('new_password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Confirm change password
        document.getElementById('confirmChangePassword').addEventListener('click', function() {
            const patientId = document.getElementById('change_password_patient_id').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const patientRequested = document.getElementById('patientRequestedChange').checked;
            
            // Clear previous alerts
            document.getElementById('passwordChangeAlert').style.display = 'none';
            
            // Validation
            if (!newPassword || !confirmPassword) {
                showModalAlert('Please fill in all password fields.');
                return;
            }
            
            if (newPassword.length < 6) {
                showModalAlert('Password must be at least 6 characters long.');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                showModalAlert('Passwords do not match. Please try again.');
                return;
            }
            
            if (!patientRequested) {
                showModalAlert('Please confirm that the patient requested this change in person.');
                return;
            }
            
            if (!confirm('Are you sure you want to change this patient\'s password? The patient will be notified of this change.')) {
                return;
            }
            
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Changing...';
            
            const formData = new FormData();
            formData.append('action', 'change_password');
            formData.append('patient_id', patientId);
            formData.append('new_password', newPassword);
            
            fetch('Patients.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showModalAlert(data.message, 'success');
                    
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
                        alert('Password changed successfully!\n\nPlease inform the patient to:\n1. Use the new password to log in\n2. Change their password immediately after logging in\n3. Keep their password secure');
                        location.reload();
                    }, 1500);
                } else {
                    showModalAlert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showModalAlert('An error occurred while changing the password.');
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-check me-2"></i>Change Password';
            });
        });

        // Show alert inside modal
        function showModalAlert(message, type = 'danger') {
            const alertDiv = document.getElementById('passwordChangeAlert');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertDiv.style.display = 'block';
            
            document.querySelector('#changePasswordModal .modal-body').scrollTop = 0;
            
            if (type === 'success') {
                setTimeout(() => {
                    alertDiv.style.display = 'none';
                }, 5000);
            }
        }

        // Clear password fields when modal closes
        document.getElementById('changePasswordModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('changePasswordForm').reset();
            document.getElementById('new_password').type = 'password';
            document.getElementById('passwordChangeAlert').style.display = 'none';
            const icon = document.querySelector('#togglePassword i');
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        });

        // Phone number formatting
        function formatPhoneNumber(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length > 11) value = value.slice(0, 11);
            
            if (value.length >= 4) {
                value = value.slice(0, 4) + '-' + value.slice(4);
            }
            if (value.length >= 9) {
                value = value.slice(0, 9) + '-' + value.slice(9);
            }
            
            input.value = value;
        }

        // Add phone formatting to all phone inputs
        document.querySelectorAll('input[type="tel"]').forEach(input => {
            input.addEventListener('input', function() {
                formatPhoneNumber(this);
            });
        });

        // PhilHealth number formatting
        function formatPhilHealth(input) {
            let value = input.value.replace(/\D/g, '');
            if (value.length > 12) value = value.slice(0, 12);
            
            if (value.length >= 2) {
                value = value.slice(0, 2) + '-' + value.slice(2);
            }
            if (value.length >= 12) {
                value = value.slice(0, 12) + '-' + value.slice(12);
            }
            
            input.value = value;
        }

        // Add PhilHealth formatting
        document.querySelectorAll('input[name="philhealth_number"]').forEach(input => {
            input.addEventListener('input', function() {
                formatPhilHealth(this);
            });
        });

        // Clear form validation on input
        document.querySelectorAll('.form-control, .form-select').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
        });

        // Auto-submit filter form on change (only for select dropdowns)
        document.querySelectorAll('#filterForm select').forEach(select => {
            select.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
        });

        // Username validation
        const usernameInput = document.querySelector('input[name="username"]');
        if (usernameInput) {
            usernameInput.addEventListener('input', function() {
                const username = this.value.toLowerCase().replace(/[^a-z0-9._-]/g, '');
                this.value = username;
            });
        }
    </script>
</body>
</html>