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
    
    if ($_POST['action'] === 'create_referral') {
        try {
            $patient_id = $_POST['patient_id'];
            $consultation_id = !empty($_POST['consultation_id']) ? $_POST['consultation_id'] : null;
            $referred_to_facility = trim($_POST['referred_to_facility']);
            $referred_to_doctor = trim($_POST['referred_to_doctor'] ?? '');
            $referral_reason = trim($_POST['referral_reason']);
            $chief_complaint = trim($_POST['chief_complaint']);
            $diagnosis = trim($_POST['diagnosis']);
            $clinical_summary = trim($_POST['clinical_summary'] ?? '');
            $treatment_given = trim($_POST['treatment_given'] ?? '');
            $urgency_level = $_POST['urgency_level'];
            $referral_date = $_POST['referral_date'];
            $transportation_needed = isset($_POST['transportation_needed']) ? 1 : 0;
            
            // Get vital signs and COVID vaccination
            $temperature = trim($_POST['temperature'] ?? '');
            $blood_pressure = trim($_POST['blood_pressure'] ?? '');
            $pulse_rate = trim($_POST['pulse_rate'] ?? '');
            $respiratory_rate = trim($_POST['respiratory_rate'] ?? '');
            $oxygen_saturation = trim($_POST['oxygen_saturation'] ?? '');
            $covid_vaccination = $_POST['covid_vaccination'] ?? 'unknown';
            
            // Build clinical summary for backward compatibility
            $vital_signs = [];
            if ($temperature) $vital_signs[] = "Temp: {$temperature}°C";
            if ($blood_pressure) $vital_signs[] = "BP: {$blood_pressure}";
            if ($pulse_rate) $vital_signs[] = "PR: {$pulse_rate} bpm";
            if ($respiratory_rate) $vital_signs[] = "RR: {$respiratory_rate} cpm";
            if ($oxygen_saturation) $vital_signs[] = "O2 Sat: {$oxygen_saturation}%";
            
            $vital_signs_text = !empty($vital_signs) ? "\n\nVITAL SIGNS: " . implode(", ", $vital_signs) : "";
            $full_clinical_summary = $clinical_summary . $vital_signs_text;
            
            // Generate referral number
            $year = date('Y');
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM referrals WHERE referral_number LIKE ?");
            $stmt->execute(["REF-$year-%"]);
            $count = $stmt->fetchColumn();
            $referral_number = 'REF-' . $year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            
            // Insert referral
            $stmt = $pdo->prepare("
                INSERT INTO referrals (
                    referral_number, patient_id, consultation_id, referred_by, 
                    referred_to_facility, referred_to_doctor, referral_reason, 
                    clinical_summary, diagnosis, treatment_given, urgency_level, 
                    referral_date, transportation_needed, covid_vaccination_status,
                    temperature, blood_pressure, pulse_rate, respiratory_rate, oxygen_saturation,
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            
            $stmt->execute([
                $referral_number, $patient_id, $consultation_id, $staff['id'],
                $referred_to_facility, $referred_to_doctor, $referral_reason,
                $full_clinical_summary, $diagnosis, $treatment_given, $urgency_level,
                $referral_date, $transportation_needed, $covid_vaccination,
                $temperature, $blood_pressure, $pulse_rate, $respiratory_rate, $oxygen_saturation
            ]);
            
            $referral_id = $pdo->lastInsertId();
            
            // Log the referral
            $logStmt = $pdo->prepare("
                INSERT INTO system_logs (user_id, action, module, record_id, new_values, ip_address) 
                VALUES (?, 'REFERRAL_CREATED', 'Referrals', ?, ?, ?)
            ");
            $logStmt->execute([
                $_SESSION['user_id'], 
                $referral_id,
                json_encode(['referral_number' => $referral_number, 'patient_id' => $patient_id]),
                $_SERVER['REMOTE_ADDR']
            ]);
            
            // Create notification for patient
            $patientStmt = $pdo->prepare("SELECT user_id FROM patients WHERE id = ?");
            $patientStmt->execute([$patient_id]);
            $patient_user = $patientStmt->fetch();
            
            if ($patient_user) {
                $notificationStmt = $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, data, priority) 
                    VALUES (?, 'referral_update', 'Referral Created', ?, ?, ?)
                ");
                $notificationStmt->execute([
                    $patient_user['user_id'],
                    "You have been referred to $referred_to_facility. Referral Number: $referral_number",
                    json_encode(['referral_id' => $referral_id, 'referral_number' => $referral_number]),
                    $urgency_level === 'urgent' || $urgency_level === 'emergency' ? 'high' : 'medium'
                ]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => "Referral created successfully! Referral Number: $referral_number",
                'referral_id' => $referral_id,
                'referral_number' => $referral_number
            ]);
            exit;
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => "Error creating referral: " . $e->getMessage()
            ]);
            exit;
        }
    }
    
    if ($_POST['action'] === 'get_referral_details') {
        try {
            $referral_id = $_POST['referral_id'];
            
            $stmt = $pdo->prepare("
                SELECT r.*, 
                       p.patient_id, p.first_name, p.middle_name, p.last_name, p.suffix,
                       p.date_of_birth, p.gender, p.civil_status, p.address, p.phone,
                       p.blood_type, p.allergies, p.philhealth_number,
                       b.barangay_name,
                       YEAR(CURDATE()) - YEAR(p.date_of_birth) as age,
                       CONCAT(s.first_name, ' ', s.last_name) as referred_by_name,
                       s.position, s.license_number
                FROM referrals r
                JOIN patients p ON r.patient_id = p.id
                LEFT JOIN barangays b ON p.barangay_id = b.id
                LEFT JOIN staff s ON r.referred_by = s.id
                WHERE r.id = ?
            ");
            $stmt->execute([$referral_id]);
            $referral = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($referral) {
                echo json_encode([
                    'success' => true,
                    'referral' => $referral
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Referral not found'
                ]);
            }
            exit;
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
    
    if ($_POST['action'] === 'update_referral_status') {
        try {
            $referral_id = $_POST['referral_id'];
            $status = $_POST['status'];
            $feedback = $_POST['feedback'] ?? '';
            
            $stmt = $pdo->prepare("
                UPDATE referrals 
                SET status = ?, feedback = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$status, $feedback, $referral_id]);
            
            // Log the update
            $logStmt = $pdo->prepare("
                INSERT INTO system_logs (user_id, action, module, record_id, new_values, ip_address) 
                VALUES (?, 'REFERRAL_STATUS_UPDATED', 'Referrals', ?, ?, ?)
            ");
            $logStmt->execute([
                $_SESSION['user_id'],
                $referral_id,
                json_encode(['status' => $status]),
                $_SERVER['REMOTE_ADDR']
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Referral status updated successfully'
            ]);
            exit;
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
}

// Get all patients for dropdown
$patientsStmt = $pdo->prepare("
    SELECT p.*, 
           CONCAT(p.last_name, ', ', p.first_name, ' ', IFNULL(CONCAT(LEFT(p.middle_name, 1), '.'), '')) as full_name,
           YEAR(CURDATE()) - YEAR(p.date_of_birth) as age,
           b.barangay_name
    FROM patients p 
    LEFT JOIN barangays b ON p.barangay_id = b.id
    WHERE p.is_active = 1 
    ORDER BY p.last_name, p.first_name
");
$patientsStmt->execute();
$patients = $patientsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent consultations for selected patient (for linking)
$recentConsultationsStmt = $pdo->prepare("
    SELECT id, patient_id, consultation_number, consultation_date, diagnosis, status
    FROM consultations
    WHERE status IN ('completed', 'in_progress')
    ORDER BY consultation_date DESC
    LIMIT 100
");
$recentConsultationsStmt->execute();
$recentConsultations = $recentConsultationsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get filter parameter
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query based on filter
$query = "
    SELECT r.*, 
           p.patient_id,
           CONCAT(p.last_name, ', ', p.first_name) as patient_name,
           YEAR(CURDATE()) - YEAR(p.date_of_birth) as patient_age,
           p.gender,
           CONCAT(s.first_name, ' ', s.last_name) as referred_by_name
    FROM referrals r
    JOIN patients p ON r.patient_id = p.id
    LEFT JOIN staff s ON r.referred_by = s.id
    WHERE 1=1
";

if ($filter === 'urgent') {
    $query .= " AND r.urgency_level IN ('urgent', 'emergency')";
} elseif ($filter === 'pending') {
    $query .= " AND r.status = 'pending'";
} elseif ($filter === 'today') {
    $query .= " AND DATE(r.referral_date) = CURDATE()";
}

if (!empty($search)) {
    $query .= " AND (p.patient_id LIKE :search OR p.first_name LIKE :search OR p.last_name LIKE :search OR r.referral_number LIKE :search)";
}

$query .= " ORDER BY r.referral_date DESC, r.created_at DESC LIMIT 50";

$referralsStmt = $pdo->prepare($query);
if (!empty($search)) {
    $referralsStmt->execute(['search' => "%$search%"]);
} else {
    $referralsStmt->execute();
}
$referrals = $referralsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$statsStmt = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN urgency_level IN ('urgent', 'emergency') AND status IN ('pending', 'sent') THEN 1 ELSE 0 END) as urgent,
        SUM(CASE WHEN DATE(referral_date) = CURDATE() THEN 1 ELSE 0 END) as today
    FROM referrals
");
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Management - Kawit RHU</title>
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
                    <a href="Patients.php" class="nav-link">
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
                    <a href="Referral.php" class="nav-link active">
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
                <h1 class="page-title">Referral Management</h1>
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
                <!-- Quick Actions -->
                <div class="quick-actions">
                    <a href="blank_referral_form.php" target="_blank" class="quick-action-btn">
                        <i class="fas fa-file-alt"></i>Print Blank Form
                    </a>
                </div>

                <!-- Create Referral Form -->
                <div class="content-section" id="createReferralForm">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-file-medical"></i>Create New Referral
                        </h3>
                    </div>

                    <form id="referralForm">
                        <input type="hidden" name="action" value="create_referral">

                        <!-- Patient Selection -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Search Patient <span class="required">*</span></label>
                                <input type="text" 
                                    class="form-control" 
                                    id="patientSearch" 
                                    placeholder="Type patient name or ID to search..." 
                                    autocomplete="off">
                                <input type="hidden" name="patient_id" id="patientId" required>
                                
                                <!-- Search Results Dropdown -->
                                <div id="patientSearchResults" class="search-results"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Referral Date <span class="required">*</span></label>
                                <input type="date" class="form-control" name="referral_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Urgency Level <span class="required">*</span></label>
                                <select class="form-select" name="urgency_level" required>
                                    <option value="routine">Routine</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="emergency">Emergency</option>
                                </select>
                            </div>
                        </div>

                        <!-- Selected Patient Display -->
                        <div class="patient-info-card" id="patientInfoCard">
                            <a href="#" class="clear-selection" onclick="clearPatientSelection(); return false;" style="float: right; color: #dc3545; text-decoration: none;">
                                <i class="fas fa-times-circle"></i> Clear
                            </a>
                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <strong style="font-size: 1.1rem; color: var(--dark-pink);" id="selectedPatientName"></strong>
                                    <span class="ms-2 text-muted" id="selectedPatientId"></span>
                                </div>
                                <div class="col-md-2">
                                    <strong><i class="fas fa-birthday-cake me-1"></i>Age:</strong> <span id="displayAge">-</span>
                                </div>
                                <div class="col-md-2">
                                    <strong><i class="fas fa-venus-mars me-1"></i>Sex:</strong> <span id="displayGender">-</span>
                                </div>
                                <div class="col-md-2">
                                    <strong><i class="fas fa-tint me-1"></i>Blood Type:</strong> <span id="displayBloodType">-</span>
                                </div>
                                <div class="col-md-2">
                                    <strong><i class="fas fa-phone me-1"></i>Phone:</strong> <span id="displayPhone">-</span>
                                </div>
                                <div class="col-md-4">
                                    <strong><i class="fas fa-map-marker-alt me-1"></i>Address:</strong> <span id="displayAddress">-</span>
                                </div>
                            </div>
                        </div>

                        <div class="section-divider"></div>
                        <div class="section-subtitle">
                            <i class="fas fa-notes-medical"></i>Clinical Information
                        </div>

                        <!-- Chief Complaint & Diagnosis -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Chief Complaint <span class="required">*</span></label>
                                <textarea class="form-control" name="chief_complaint" rows="3" placeholder="Main reason for consultation/visit" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Initial Diagnosis <span class="required">*</span></label>
                                <textarea class="form-control" name="diagnosis" rows="3" placeholder="Preliminary diagnosis or findings" required></textarea>
                            </div>
                        </div>

                        <!-- Vital Signs -->
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-heartbeat me-2"></i>Vital Signs</label>
                            <div class="row">
                                <div class="col-md-2">
                                    <input type="text" class="form-control" name="temperature" placeholder="36.5">
                                    <small class="text-muted">Temperature (°C)</small>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="blood_pressure" placeholder="120/80">
                                    <small class="text-muted">Blood Pressure</small>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control" name="pulse_rate" placeholder="72">
                                    <small class="text-muted">Pulse Rate (bpm)</small>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="respiratory_rate" placeholder="18">
                                    <small class="text-muted">Respiratory Rate (cpm)</small>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="form-control" name="oxygen_saturation" placeholder="98">
                                    <small class="text-muted">O2 Saturation (%)</small>
                                </div>
                            </div>
                        </div>

                        <!-- Management Provided -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Management/Treatment Provided <span class="required">*</span></label>
                                <textarea class="form-control" name="treatment_given" rows="3" placeholder="Medications given, procedures done, treatments administered at RHU" required></textarea>
                            </div>
                        </div>

                        <!-- COVID-19 Vaccination Status -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label"><i class="fas fa-syringe me-2"></i>COVID-19 Vaccination Status</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="covid_vaccination" id="primarySeries" value="primary_series">
                                        <label class="form-check-label" for="primarySeries">
                                            Primary Series
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="covid_vaccination" id="booster" value="booster">
                                        <label class="form-check-label" for="booster">
                                            Booster
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="covid_vaccination" id="unvaccinated" value="unvaccinated">
                                        <label class="form-check-label" for="unvaccinated">
                                            Unvaccinated
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="section-divider"></div>
                        <div class="section-subtitle">
                            <i class="fas fa-notes-medical"></i>Clinical Information
                        </div>

                        <!-- Referral Destination -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Referred to Facility <span class="required">*</span></label>
                                <input type="text" class="form-control" name="referred_to_facility" placeholder="e.g., Cavite Provincial Hospital" required list="facilityList">
                                <datalist id="facilityList">
                                    <option value="Cavite Provincial Hospital">
                                    <option value="De La Salle University Medical Center">
                                    <option value="Medical Center Imus">
                                    <option value="Divine Grace Hospital">
                                    <option value="St. Dominic Medical Center">
                                    <option value="Bacoor Doctors Hospital">
                                </datalist>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Referred to Doctor/Department</label>
                                <input type="text" class="form-control" name="referred_to_doctor" placeholder="e.g., Dr. Juan Dela Cruz / Internal Medicine">
                            </div>
                        </div>

                        <!-- Reason for Referral -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Reason for Referral <span class="required">*</span></label>
                                <textarea class="form-control" name="referral_reason" rows="4" placeholder="Specific reason why patient needs to be referred (e.g., needs specialist evaluation, advanced diagnostic procedures, specialized treatment not available at RHU)" required></textarea>
                            </div>
                        </div>

                        <!-- Additional Options -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Link to Consultation (Optional)</label>
                                <select class="form-select" name="consultation_id" id="consultationSelect">
                                    <option value="">-- No Consultation Link --</option>
                                </select>
                                <small class="text-muted">Select patient first to see their consultations</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label d-block">Transportation</label>
                                <div class="form-check form-check-inline mt-2">
                                    <input class="form-check-input" type="checkbox" name="transportation_needed" id="transportCheck">
                                    <label class="form-check-label" for="transportCheck">
                                        <i class="fas fa-ambulance me-1"></i><strong>Transportation Assistance Needed</strong>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-end mt-4">
                            <button type="reset" class="btn btn-secondary me-2">
                                <i class="fas fa-redo me-2"></i>Clear Form
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>Create Referral
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Referrals List -->
                <div class="content-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-list"></i>
                            <?php 
                            if ($filter === 'urgent') echo 'Urgent Referrals';
                            elseif ($filter === 'pending') echo 'Pending Referrals';
                            elseif ($filter === 'today') echo "Today's Referrals";
                            else echo 'Recent Referrals';
                            ?>
                        </h3>
                        <div>
                            <input type="text" class="form-control form-control-sm" id="searchInput" placeholder="Search referrals..." style="width: 250px; display: inline-block;">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Referral #</th>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Referred To</th>
                                    <th>Diagnosis</th>
                                    <th>Urgency</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($referrals)): ?>
                                    <?php foreach ($referrals as $referral): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($referral['referral_number']); ?></strong></td>
                                            <td><?php echo date('M j, Y', strtotime($referral['referral_date'])); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($referral['patient_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($referral['patient_id']); ?> • <?php echo $referral['patient_age']; ?>y/o • <?php echo $referral['gender']; ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($referral['referred_to_facility']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($referral['diagnosis'], 0, 50)); ?><?php echo strlen($referral['diagnosis']) > 50 ? '...' : ''; ?></td>
                                            <td><span class="status-badge urgency-<?php echo $referral['urgency_level']; ?>"><?php echo ucfirst($referral['urgency_level']); ?></span></td>
                                            <td><span class="status-badge status-<?php echo $referral['status']; ?>"><?php echo ucfirst($referral['status']); ?></span></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="viewReferral(<?php echo $referral['id']; ?>)" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-success" onclick="downloadReferral(<?php echo $referral['id']; ?>)" title="Download PDF">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                                <button class="btn btn-sm btn-warning" onclick="updateStatus(<?php echo $referral['id']; ?>)" title="Update Status">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity: 0.3;"></i>
                                            <p>No referrals found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Referral Modal -->
    <div class="modal fade" id="viewReferralModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-medical me-2"></i>Referral Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="referralDetailsContent">
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-3x text-muted"></i>
                        <p class="mt-3">Loading referral details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="downloadCurrentReferral()">
                        <i class="fas fa-download me-2"></i>Download PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Update Referral Status
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="updateStatusForm">
                        <input type="hidden" id="update_referral_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="update_status" required>
                                <option value="pending">Pending</option>
                                <option value="sent">Sent</option>
                                <option value="accepted">Accepted</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Feedback / Notes</label>
                            <textarea class="form-control" id="update_feedback" rows="3" placeholder="Add any feedback or notes about the referral status"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveStatusUpdate()">
                        <i class="fas fa-save me-2"></i>Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Patient consultations data
        const patientConsultations = <?php echo json_encode($recentConsultations); ?>;

        // All patients data
        const allPatients = <?php echo json_encode($patients); ?>;

        // Patient Search Functionality
        const patientSearch = document.getElementById('patientSearch');
        const patientSearchResults = document.getElementById('patientSearchResults');
        const patientIdInput = document.getElementById('patientId');
        const patientInfoCard = document.getElementById('patientInfoCard');

        // Search patients
        patientSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            
            if (searchTerm.length < 2) {
                patientSearchResults.classList.remove('show');
                return;
            }
            
            // Filter patients
            const filteredPatients = allPatients.filter(patient => {
                const fullName = patient.full_name.toLowerCase();
                const patientId = patient.patient_id.toLowerCase();
                return fullName.includes(searchTerm) || patientId.includes(searchTerm);
            });
            
            // Display results
            if (filteredPatients.length > 0) {
                let html = '';
                filteredPatients.forEach(patient => {
                    html += `
                        <div class="search-result-item" onclick="selectPatient(${patient.id}, '${patient.full_name.replace(/'/g, "\\'")}', '${patient.patient_id}', ${patient.age}, '${patient.gender}', '${(patient.address || '').replace(/'/g, "\\'")}', '${(patient.barangay_name || '').replace(/'/g, "\\'")}', '${patient.phone || 'N/A'}', '${patient.blood_type || 'N/A'}')">
                            <div class="search-result-name">${patient.full_name}</div>
                            <div class="search-result-details">
                                ${patient.patient_id} • ${patient.age} y/o • ${patient.gender}
                            </div>
                        </div>
                    `;
                });
                patientSearchResults.innerHTML = html;
                patientSearchResults.classList.add('show');
            } else {
                patientSearchResults.innerHTML = '<div class="no-results">No patients found</div>';
                patientSearchResults.classList.add('show');
            }
        });

        // Select patient
        function selectPatient(id, name, patientId, age, gender, address, barangay, phone, bloodType) {
            // Set hidden input
            patientIdInput.value = id;
            
            // Update search input
            patientSearch.value = name;
            
            // Hide search results
            patientSearchResults.classList.remove('show');
            
            // Show selected patient info
            document.getElementById('selectedPatientName').textContent = name;
            document.getElementById('selectedPatientId').textContent = '(' + patientId + ')';
            document.getElementById('displayAge').textContent = age + ' years old';
            document.getElementById('displayGender').textContent = gender;
            document.getElementById('displayBloodType').textContent = bloodType;
            document.getElementById('displayPhone').textContent = phone;
            document.getElementById('displayAddress').textContent = address + ', ' + barangay;
            patientInfoCard.classList.add('show');
            
            // Update consultations dropdown
            updateConsultationsDropdown(id);
        }

        // Update consultations dropdown
        function updateConsultationsDropdown(patientId) {
            const consultationSelect = document.getElementById('consultationSelect');
            consultationSelect.innerHTML = '<option value="">-- No Consultation Link --</option>';
            
            // Filter consultations for THIS PATIENT ONLY
            const patientConsults = patientConsultations.filter(consult => 
                parseInt(consult.patient_id) === parseInt(patientId)
            );
            
            if (patientConsults.length > 0) {
                patientConsults.forEach(consult => {
                    const option = document.createElement('option');
                    option.value = consult.id;
                    const consultDate = new Date(consult.consultation_date).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    });
                    option.textContent = `${consult.consultation_number} - ${consultDate} - ${consult.diagnosis || 'No diagnosis'}`;
                    consultationSelect.appendChild(option);
                });
            } else {
                const option = document.createElement('option');
                option.disabled = true;
                option.textContent = '-- No consultations found for this patient --';
                consultationSelect.appendChild(option);
            }
        }

        // Clear patient selection
        function clearPatientSelection() {
            patientIdInput.value = '';
            patientSearch.value = '';
            patientInfoCard.classList.remove('show');
            patientSearchResults.classList.remove('show');
            
            // Reset consultations dropdown
            const consultationSelect = document.getElementById('consultationSelect');
            consultationSelect.innerHTML = '<option value="">-- No Consultation Link --</option>';
        }

        // Close search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!patientSearch.contains(e.target) && !patientSearchResults.contains(e.target)) {
                patientSearchResults.classList.remove('show');
            }
        });

        // Form submission
        document.getElementById('referralForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Referral...';
            
            const formData = new FormData(this);
            
            fetch('Referral.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success alert
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show';
                    alertDiv.innerHTML = `
                        <i class="fas fa-check-circle me-2"></i>${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    document.querySelector('.dashboard-content').insertBefore(alertDiv, document.querySelector('.dashboard-content').firstChild);
                    
                    // Reset form
                    this.reset();
                    document.getElementById('patientInfoCard').classList.remove('show');
                    
                    // Scroll to top
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    
                    // Reload page after 2 seconds to show new referral
                    setTimeout(() => location.reload(), 2000);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating the referral');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });

        // View referral details
        let currentReferralId = null;
        
        function viewReferral(id) {
            currentReferralId = id;
            const modal = new bootstrap.Modal(document.getElementById('viewReferralModal'));
            modal.show();
            
            const formData = new FormData();
            formData.append('action', 'get_referral_details');
            formData.append('referral_id', id);
            
            fetch('Referral.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const r = data.referral;
                    const content = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary"><i class="fas fa-info-circle me-2"></i>Referral Information</h6>
                                <table class="table table-sm">
                                    <tr><th width="40%">Referral Number:</th><td><strong>${r.referral_number}</strong></td></tr>
                                    <tr><th>Date:</th><td>${new Date(r.referral_date).toLocaleDateString()}</td></tr>
                                    <tr><th>Urgency:</th><td><span class="status-badge urgency-${r.urgency_level}">${r.urgency_level.toUpperCase()}</span></td></tr>
                                    <tr><th>Status:</th><td><span class="status-badge status-${r.status}">${r.status.toUpperCase()}</span></td></tr>
                                    <tr><th>Referred By:</th><td>Dr. ${r.referred_by_name}</td></tr>
                                    <tr><th>Transportation:</th><td>${r.transportation_needed ? '<i class="fas fa-check text-success"></i> Required' : '<i class="fas fa-times text-danger"></i> Not Required'}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary"><i class="fas fa-user me-2"></i>Patient Information</h6>
                                <table class="table table-sm">
                                    <tr><th width="40%">Patient ID:</th><td>${r.patient_id}</td></tr>
                                    <tr><th>Name:</th><td><strong>${r.first_name} ${r.middle_name || ''} ${r.last_name} ${r.suffix || ''}</strong></td></tr>
                                    <tr><th>Age/Sex:</th><td>${r.age} years old / ${r.gender}</td></tr>
                                    <tr><th>Civil Status:</th><td>${r.civil_status || 'N/A'}</td></tr>
                                    <tr><th>Blood Type:</th><td>${r.blood_type || 'N/A'}</td></tr>
                                    <tr><th>Address:</th><td>${r.address}, ${r.barangay_name || ''}</td></tr>
                                    <tr><th>Phone:</th><td>${r.phone || 'N/A'}</td></tr>
                                    <tr><th>PhilHealth:</th><td>${r.philhealth_number || 'N/A'}</td></tr>
                                </table>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-primary"><i class="fas fa-hospital me-2"></i>Referral Destination</h6>
                                <table class="table table-sm">
                                    <tr><th width="20%">Facility:</th><td><strong>${r.referred_to_facility}</strong></td></tr>
                                    <tr><th>Doctor/Dept:</th><td>${r.referred_to_doctor || 'Not specified'}</td></tr>
                                </table>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary"><i class="fas fa-stethoscope me-2"></i>Chief Complaint</h6>
                                <p class="border p-3 rounded">${r.referral_reason || 'N/A'}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary"><i class="fas fa-diagnoses me-2"></i>Diagnosis</h6>
                                <p class="border p-3 rounded">${r.diagnosis || 'N/A'}</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-primary"><i class="fas fa-notes-medical me-2"></i>Clinical Summary</h6>
                                <p class="border p-3 rounded" style="white-space: pre-line;">${r.clinical_summary || 'N/A'}</p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-primary"><i class="fas fa-pills me-2"></i>Treatment Given</h6>
                                <p class="border p-3 rounded" style="white-space: pre-line;">${r.treatment_given || 'N/A'}</p>
                            </div>
                        </div>
                        
                        ${r.feedback ? `
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-primary"><i class="fas fa-comment-medical me-2"></i>Feedback</h6>
                                <p class="border p-3 rounded bg-light" style="white-space: pre-line;">${r.feedback}</p>
                            </div>
                        </div>
                        ` : ''}
                    `;
                    document.getElementById('referralDetailsContent').innerHTML = content;
                } else {
                    document.getElementById('referralDetailsContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('referralDetailsContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Error loading referral details
                    </div>
                `;
            });
        }

        // Download referral PDF
        function downloadReferral(id) {
            window.open('download_referral.php?id=' + id, '_blank');
        }

        function downloadCurrentReferral() {
            if (currentReferralId) {
                downloadReferral(currentReferralId);
            }
        }

        // Update status
        function updateStatus(id) {
            currentReferralId = id;
            document.getElementById('update_referral_id').value = id;
            const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
            modal.show();
        }

        function saveStatusUpdate() {
            const referralId = document.getElementById('update_referral_id').value;
            const status = document.getElementById('update_status').value;
            const feedback = document.getElementById('update_feedback').value;
            
            const formData = new FormData();
            formData.append('action', 'update_referral_status');
            formData.append('referral_id', referralId);
            formData.append('status', status);
            formData.append('feedback', feedback);
            
            fetch('Referral.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                const search = this.value;
                window.location.href = `Referral.php?search=${encodeURIComponent(search)}${window.location.search.includes('filter=') ? '&filter=' + new URLSearchParams(window.location.search).get('filter') : ''}`;
            }
        });

        // Scroll to form
        function scrollToForm() {
            document.getElementById('createReferralForm').scrollIntoView({ behavior: 'smooth' });
        }

        // Form validation
        document.querySelectorAll('input[required], select[required], textarea[required]').forEach(field => {
            field.addEventListener('invalid', function() {
                this.classList.add('is-invalid');
            });
            field.addEventListener('input', function() {
                if (this.validity.valid) {
                    this.classList.remove('is-invalid');
                }
            });
        });

        // Auto-save draft to localStorage
        let autoSaveTimer;
        document.getElementById('referralForm').addEventListener('input', function(e) {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                const formData = new FormData(this);
                const data = {};
                formData.forEach((value, key) => {
                    if (value) { // Only save non-empty values
                        data[key] = value;
                    }
                });
                
                // Check if there's actual data to save
                const hasData = Object.keys(data).some(key => key !== 'action' && data[key]);
                
                if (hasData) {
                    localStorage.setItem('referral_draft', JSON.stringify(data));
                    console.log('Draft saved:', Object.keys(data).length, 'fields');
                }
            }, 2000);
        });

        // Load draft on page load
        window.addEventListener('load', function() {
            const draft = localStorage.getItem('referral_draft');
            if (draft) {
                try {
                    const data = JSON.parse(draft);
                    // Check if draft has actual data
                    const hasData = Object.values(data).some(val => val && val !== 'create_referral');
                    
                    if (hasData && confirm('Would you like to restore your unsaved referral draft?')) {
                        Object.keys(data).forEach(key => {
                            // Skip the action field
                            if (key === 'action') return;
                            
                            const field = document.querySelector(`[name="${key}"]`);
                            if (field) {
                                if (field.type === 'checkbox') {
                                    field.checked = data[key] === 'on' || data[key] === true;
                                } else if (field.type === 'radio') {
                                    if (field.value === data[key]) {
                                        field.checked = true;
                                    }
                                } else {
                                    field.value = data[key];
                                }
                                
                                // Trigger change event for selects and patient selection
                                if (field.tagName === 'SELECT' || field.id === 'patientSelect') {
                                    field.dispatchEvent(new Event('change'));
                                }
                            }
                        });
                    } else if (!hasData) {
                        // Clear empty draft
                        localStorage.removeItem('referral_draft');
                    }
                } catch (e) {
                    console.error('Error restoring draft:', e);
                    localStorage.removeItem('referral_draft');
                }
            }
        });

        // Clear draft after successful submission
        document.getElementById('referralForm').addEventListener('submit', function() {
            // Clear immediately on submit
            localStorage.removeItem('referral_draft');
        });

        // Session timeout check
        setInterval(function() {
            fetch('../../check_session.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.valid) {
                        alert('Your session has expired. You will be redirected to the login page.');
                        window.location.href = '../../login.php';
                    }
                });
        }, 300000); // Check every 5 minutes
    </script>
</body>
</html>