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
    SELECT s.*, u.username 
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
    
    if ($_POST['action'] === 'search_patient') {
        $search = trim($_POST['search']);
        
        $stmt = $pdo->prepare("
            SELECT p.*, b.barangay_name,
                   YEAR(CURDATE()) - YEAR(p.date_of_birth) as age,
                   CASE 
                       WHEN YEAR(CURDATE()) - YEAR(p.date_of_birth) >= 60 THEN 1
                       WHEN p.pwd_status = 'YES' THEN 1
                       ELSE 0
                   END as is_priority
            FROM patients p
            LEFT JOIN barangays b ON p.barangay_id = b.id
            WHERE p.is_active = 1 
            AND (p.patient_id LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR p.phone LIKE ?)
            ORDER BY p.first_name, p.last_name
            LIMIT 10
        ");
        $searchTerm = "%$search%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'patients' => $patients]);
        exit;
    }
    // Clean output buffer to prevent any extra output before JSON
    if (ob_get_length()) ob_clean();
    
    if ($_POST['action'] === 'check_in') {
        try {
            $patient_id = $_POST['patient_id'];
            $service_type = $_POST['service_type'];
            $priority = $_POST['priority'];
            $notes = $_POST['notes'] ?? '';
            
            // Check if patient already in queue today
            $today = date('Y-m-d');
            $checkStmt = $pdo->prepare("
                SELECT id FROM queue 
                WHERE patient_id = ? 
                AND queue_date = ? 
                AND status IN ('waiting', 'serving')
                AND service_type = ?
            ");
            $checkStmt->execute([$patient_id, $today, $service_type]);
            
            if ($checkStmt->fetch()) {
                throw new Exception("Patient is already in the queue for this service today!");
            }
            
            $pdo->beginTransaction();
            
            // Generate queue number - always increment, never reuse (resets daily)
            $stmt = $pdo->prepare("
                SELECT MAX(CAST(queue_number AS UNSIGNED)) FROM queue 
                WHERE queue_date = ? AND service_type = ?
            ");
            $stmt->execute([$today, $service_type]);
            $maxNumber = $stmt->fetchColumn();
            
            // If no patients today yet, start at 1, otherwise increment the highest number
            $queue_number = (string)(($maxNumber ?: 0) + 1);
            
            // Calculate position in queue
            $posStmt = $pdo->prepare("
                SELECT COUNT(*) FROM queue 
                WHERE queue_date = ? 
                AND service_type = ? 
                AND status = 'waiting'
            ");
            $posStmt->execute([$today, $service_type]);
            $position = $posStmt->fetchColumn() + 1;
            
            // Get average service time
            $settingsStmt = $pdo->prepare("
                SELECT average_service_time FROM queue_settings WHERE service_type = ?
            ");
            $settingsStmt->execute([$service_type]);
            $settings = $settingsStmt->fetch(PDO::FETCH_ASSOC);
            $avg_time = $settings['average_service_time'] ?? 15;
            
            // Calculate estimated wait time
            $estimated_wait = ($position - 1) * $avg_time;
            
            // Insert into queue
            $insertStmt = $pdo->prepare("
                INSERT INTO queue (
                    queue_number, patient_id, service_type, priority, status,
                    check_in_time, queue_date, position_in_queue, estimated_wait_minutes,
                    location_type, notes
                ) VALUES (?, ?, ?, ?, 'waiting', NOW(), ?, ?, ?, 'RHU', ?)
            ");
            $insertStmt->execute([
                $queue_number,
                $patient_id,
                $service_type,
                $priority,
                $today,
                $position,
                $estimated_wait,
                $notes
            ]);
            
            $queue_id = $pdo->lastInsertId();
            
            // Get patient details for response
            $patientStmt = $pdo->prepare("
                SELECT patient_id, CONCAT(first_name, ' ', last_name) as full_name 
                FROM patients WHERE id = ?
            ");
            $patientStmt->execute([$patient_id]);
            $patient = $patientStmt->fetch(PDO::FETCH_ASSOC);
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Patient checked in successfully!',
                'queue_id' => $queue_id,
                'queue_number' => $queue_number,
                'position' => $position,
                'estimated_wait' => $estimated_wait,
                'patient' => $patient
            ]);
            
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'get_queue_stats') {
        $today = date('Y-m-d');
        
        $stmt = $pdo->prepare("
            SELECT 
                service_type,
                COUNT(CASE WHEN status = 'waiting' THEN 1 END) as waiting_count,
                COUNT(CASE WHEN status = 'serving' THEN 1 END) as serving_count,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,
                AVG(CASE WHEN status = 'completed' THEN actual_wait_minutes END) as avg_wait
            FROM queue
            WHERE queue_date = ?
            GROUP BY service_type
        ");
        $stmt->execute([$today]);
        $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'stats' => $stats]);
        exit;
    }
}

// Handle AJAX request for stats
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_stats') {
    header('Content-Type: application/json');
    
    $today = date('Y-m-d');
    $statsStmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN status = 'waiting' THEN 1 END) as total_waiting,
            COUNT(CASE WHEN status = 'serving' THEN 1 END) as total_serving,
            COUNT(CASE WHEN status = 'completed' THEN 1 END) as total_completed,
            COUNT(*) as total_today
        FROM queue
        WHERE queue_date = ?
    ");
    $statsStmt->execute([$today]);
    $todayStats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'stats' => $todayStats]);
    exit;
}

// Get today's queue statistics
$today = date('Y-m-d');
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(CASE WHEN status = 'waiting' THEN 1 END) as total_waiting,
        COUNT(CASE WHEN status = 'serving' THEN 1 END) as total_serving,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as total_completed,
        COUNT(*) as total_today
    FROM queue
    WHERE queue_date = ?
");
$statsStmt->execute([$today]);
$todayStats = $statsStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Check-in - RHU Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
                :root {
            --kawit-pink: #FFA6BE;
            --light-pink: #FFE4E6;
            --dark-pink: #FF7A9A;
            --text-dark: #2c3e50;
            --kawit-gradient: linear-gradient(135deg, #FFA6BE 0%, #FF7A9A 100%);
            --light-bg: #f8f9fc;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: var(--kawit-gradient);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logo-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 1rem;
        }

        .logo-container img {
            max-width: 50px;
            height: auto;
        }

        .logo-circle {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
        }

        .logo-text {
            font-weight: 700;
            font-size: 1.3rem;
            line-height: 1.2;
            color: white;
        }

        .sidebar-nav { padding: 1rem 0; }
        .nav-item { margin: 0.5rem 1rem; }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 1rem;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .nav-link i {
            width: 24px;
            margin-right: 12px;
            text-align: center;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
        }

        .top-navbar {
            background: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--kawit-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.95rem;
        }

        .user-role {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .dashboard-content {
            padding: 2rem;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 4px solid;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .waiting-card { border-left-color: #ffc107; }
        .serving-card { border-left-color: #0dcaf0; }
        .completed-card { border-left-color: #198754; }
        .total-card { border-left-color: #0d6efd; }

        .content-section {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            border-left: 5px solid var(--kawit-pink);
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title i {
            margin-right: 10px;
            color: var(--dark-pink);
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 12px 15px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--kawit-pink);
            box-shadow: 0 0 0 0.2rem rgba(255, 166, 190, 0.25);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .btn-primary {
            background: var(--kawit-gradient);
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: var(--dark-pink);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
            font-weight: 600;
        }

        .btn-success {
            background: #28a745;
            border: none;
            border-radius: 10px;
            padding: 12px 24px;
            font-weight: 600;
        }

        .btn-lg {
            padding: 15px 30px;
            font-size: 1.1rem;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .card-header {
            background: var(--kawit-gradient);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            font-weight: 600;
            padding: 15px 20px;
            border: none;
        }

        .card.border-primary .card-header {
            background: #0d6efd;
        }

        .card.border-success .card-header {
            background: #198754;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .check-in-section {
            display: none;
        }

        .check-in-section.active {
            display: block;
        }

        .service-type-btn {
            height: 100px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }

        .service-type-btn i {
            font-size: 2rem;
        }

        .service-type-btn:hover {
            border-color: var(--kawit-pink);
            background: var(--light-pink);
        }

        .service-type-btn.active {
            border-color: var(--dark-pink);
            background: var(--kawit-gradient);
            color: white;
        }

        #searchResults {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 10px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 5px;
        }

        .patient-search-item {
            padding: 12px 15px;
            cursor: pointer;
            transition: background 0.2s;
            border-bottom: 1px solid #f0f0f0;
        }

        .patient-search-item:hover {
            background: var(--light-pink);
        }

        .priority-badge {
            font-size: 0.75rem;
            padding: 3px 8px;
        }

        .ticket-preview {
            border: 2px dashed #6c757d;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 15px;
            text-align: center;
        }

        .queue-number-display {
            font-size: 4rem;
            font-weight: bold;
            color: var(--dark-pink);
            font-family: monospace;
            margin: 20px 0;
        }

        .dropdown-menu {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .dropdown-item {
            border-radius: 8px;
            padding: 10px 15px;
            margin: 2px 0;
        }

        .dropdown-item:hover {
            background: #f8f9fa;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
            }
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
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
                    <a href="Queue_Checkin.php" class="nav-link active">
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
                <h1 class="page-title"><i class="fas fa-ticket-alt me-2"></i>Queue Check-in</h1>
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

            <!-- Alert Messages -->
            <div id="alertMessage" style="display: none;"></div>

            <!-- Today's Statistics -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card queue-stats-card waiting-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Waiting</h6>
                                    <h2 class="mb-0"><?php echo $todayStats['total_waiting']; ?></h2>
                                </div>
                                <div class="text-warning" style="font-size: 2rem;">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card queue-stats-card serving-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Being Served</h6>
                                    <h2 class="mb-0"><?php echo $todayStats['total_serving']; ?></h2>
                                </div>
                                <div class="text-info" style="font-size: 2rem;">
                                    <i class="fas fa-user-clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card queue-stats-card completed-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Completed</h6>
                                    <h2 class="mb-0"><?php echo $todayStats['total_completed']; ?></h2>
                                </div>
                                <div class="text-success" style="font-size: 2rem;">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card queue-stats-card total-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Today</h6>
                                    <h2 class="mb-0"><?php echo $todayStats['total_today']; ?></h2>
                                </div>
                                <div class="text-primary" style="font-size: 2rem;">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Check-in Form -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Patient Check-in</h5>
                </div>
                <div class="card-body">
                    <!-- Step 1: Search Patient -->
                    <div id="step1" class="check-in-section active">
                        <h6 class="mb-3">Step 1: Search Patient</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Search by Patient ID, Name, or Phone</label>
                                <div style="position: relative;">
                                    <input type="text" class="form-control form-control-lg" id="patientSearch" 
                                           placeholder="Enter patient ID, name, or phone number..." autocomplete="off">
                                    <div id="searchResults"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Priority and Notes (shown after selecting patient) -->
                    <div class="row mt-4" id="prioritySection" style="display: none;">
                        <div class="col-md-6">
                            <label class="form-label">Priority Level</label>
                            <select class="form-select" id="prioritySelect">
                                <option value="regular">Regular</option>
                                <option value="senior_pwd">Senior Citizen / PWD</option>
                                <option value="pregnant">Pregnant</option>
                                <option value="urgent">Urgent / Emergency</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Notes (Optional)</label>
                            <input type="text" class="form-control" id="notesInput" placeholder="Additional notes...">
                        </div>
                    </div>

                    <div class="mt-4" id="continueButton" style="display: none;">
                        <button class="btn btn-primary btn-lg" onclick="goToStep(2)">
                            Continue to Confirmation<i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                <!-- Step 2: Confirm & Check-in -->
                <div id="step2" class="check-in-section">
                    <h6 class="mb-3">Step 2: Confirm Check-in</h6>

                    <!-- (Already replaced above - delete this old step3 div opening tag) -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-header bg-light">
                                        <strong>Patient Information</strong>
                                    </div>
                                    <div class="card-body" id="selectedPatientInfo">
                                        <!-- Patient info will be populated here -->
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-light">
                                        <strong>Queue Details</strong>
                                    </div>
                                    <div class="card-body" id="selectedQueueInfo">
                                        <!-- Queue info will be populated here -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-secondary me-2" onclick="goToStep(1)">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </button>
                            <button class="btn btn-success btn-lg" onclick="confirmCheckIn()">
                                <i class="fas fa-check me-2"></i>Confirm Check-in
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Success -->
                    <div id="step3" class="check-in-section">
                        <div class="text-center">
                            <div class="mb-4">
                                <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                                <h3 class="mt-3">Check-in Successful!</h3>
                                <p class="text-muted" id="successMessage">Patient has been added to the queue</p>
                            </div>

                            <div class="mt-4">
                                <button class="btn btn-success btn-lg" onclick="resetForm()">
                                    <i class="fas fa-plus me-2"></i>Check-in Another Patient
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedPatient = null;
        let selectedService = 'consultation'; // Default to consultation
        let queueResult = null;
        let searchTimeout;
        let statsRefreshInterval; // IMPORTANT: Declare here so it's available everywhere

        // Patient Search
        document.getElementById('patientSearch').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const search = e.target.value;
            
            if (search.length < 2) {
                document.getElementById('searchResults').style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                const formData = new FormData();
                formData.append('action', 'search_patient');
                formData.append('search', search);

                fetch('Queue_Checkin.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const resultsDiv = document.getElementById('searchResults');
                    if (data.success && data.patients.length > 0) {
                        resultsDiv.innerHTML = data.patients.map(p => `
                            <div class="patient-search-item" onclick='selectPatient(${JSON.stringify(p)})'>
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>${p.patient_id}</strong> - ${p.first_name} ${p.last_name}
                                        <br>
                                        <small class="text-muted">
                                            ${p.gender}, ${p.age} years old | ${p.barangay_name || 'N/A'}
                                            ${p.phone ? ' | ' + p.phone : ''}
                                        </small>
                                    </div>
                                    ${p.is_priority ? '<span class="badge bg-warning priority-badge">Priority</span>' : ''}
                                </div>
                            </div>
                        `).join('');
                        resultsDiv.style.display = 'block';
                    } else {
                        resultsDiv.innerHTML = '<div class="p-3 text-muted text-center">No patients found</div>';
                        resultsDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                });
            }, 300);
        });

        // Click outside to close search results
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#patientSearch') && !e.target.closest('#searchResults')) {
                document.getElementById('searchResults').style.display = 'none';
            }
        });

        function selectPatient(patient) {
            selectedPatient = patient;
            selectedService = 'consultation'; // Auto-select consultation
            document.getElementById('patientSearch').value = `${patient.patient_id} - ${patient.first_name} ${patient.last_name}`;
            document.getElementById('searchResults').style.display = 'none';
            
            // Auto-set priority if senior or PWD
            if (patient.is_priority) {
                document.getElementById('prioritySelect').value = 'senior_pwd';
            }
            
            // Show priority and continue button
            document.getElementById('prioritySection').style.display = 'flex';
            document.getElementById('continueButton').style.display = 'block';
        }
        // Service is automatically set to consultation - no button needed

        function goToStep(step) {
            console.log('🔄 goToStep called with step:', step);
            
            try {
                // Hide all sections
                const sections = document.querySelectorAll('.check-in-section');
                console.log('📦 Found sections:', sections.length);
                
                sections.forEach(section => {
                    section.classList.remove('active');
                    console.log('  - Hiding section:', section.id);
                });
                
                // Show target section
                const targetSection = document.getElementById('step' + step);
                console.log('🎯 Target section:', targetSection ? targetSection.id : 'NOT FOUND!');
                
                if (targetSection) {
                    targetSection.classList.add('active');
                    console.log('✅ Activated step', step);
                } else {
                    console.error('❌ Step element not found: step' + step);
                }
                
                if (step === 2) {
                    console.log('📋 Showing confirmation...');
                    showConfirmation();
                }
                
            } catch (error) {
                console.error('❌ Error in goToStep:', error);
            }
        }

        function showConfirmation() {
            const priority = document.getElementById('prioritySelect').value;
            const notes = document.getElementById('notesInput').value;
            
            const priorityLabels = {
                'regular': 'Regular',
                'senior_pwd': 'Senior Citizen / PWD',
                'pregnant': 'Pregnant',
                'urgent': 'Urgent / Emergency'
            };
            
            document.getElementById('selectedPatientInfo').innerHTML = `
                <p><strong>Patient ID:</strong> ${selectedPatient.patient_id}</p>
                <p><strong>Name:</strong> ${selectedPatient.first_name} ${selectedPatient.last_name}</p>
                <p><strong>Age:</strong> ${selectedPatient.age} years old</p>
                <p><strong>Gender:</strong> ${selectedPatient.gender}</p>
                <p><strong>Barangay:</strong> ${selectedPatient.barangay_name || 'N/A'}</p>
            `;
            
            document.getElementById('selectedQueueInfo').innerHTML = `
                <p><strong>Service Type:</strong> Consultation</p>
                <p><strong>Priority:</strong> <span class="badge bg-${priority === 'urgent' ? 'danger' : priority === 'senior_pwd' || priority === 'pregnant' ? 'warning' : 'secondary'}">${priorityLabels[priority]}</span></p>
                ${notes ? `<p><strong>Notes:</strong> ${notes}</p>` : ''}
            `;
        }

        function confirmCheckIn() {
            const btn = event.target;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            
            // CRITICAL: FORCE STOP auto-refresh during check-in
            if (statsRefreshInterval) {
                clearInterval(statsRefreshInterval);
                statsRefreshInterval = null; // Clear the reference
                console.log('⏸️ Auto-refresh STOPPED');
            }
            
            // Emergency: Clear ALL intervals to be absolutely sure
            const highestId = window.setTimeout(() => {}, 0);
            for (let i = 0; i < highestId; i++) {
                window.clearInterval(i);
            }
            console.log('🛑 All intervals cleared');
            
            // Check if selectedService is set
            if (!selectedService) {
                selectedService = 'consultation';
            }
            
            // Check if patient is selected
            if (!selectedPatient || !selectedPatient.id) {
                showAlert('Please select a patient first!', 'danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check me-2"></i>Confirm Check-in';
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'check_in');
            formData.append('patient_id', selectedPatient.id);
            formData.append('service_type', selectedService);
            formData.append('priority', document.getElementById('prioritySelect').value);
            formData.append('notes', document.getElementById('notesInput').value);

            console.log('Sending data:', {
                patient_id: selectedPatient.id,
                service_type: selectedService,
                priority: document.getElementById('prioritySelect').value,
                notes: document.getElementById('notesInput').value
            });

            // Add timeout protection (10 seconds)
            const controller = new AbortController();
            const timeoutId = setTimeout(() => {
                controller.abort();
                console.error('⏱️ Request timeout after 10 seconds');
            }, 10000);

            fetch('Queue_Checkin.php', {
                method: 'POST',
                body: formData,
                signal: controller.signal
            })
            .then(response => {
                clearTimeout(timeoutId);
                console.log('Response status:', response.status);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                return response.text();
            })
            .then(text => {
                console.log('Response received, length:', text.length);
                console.log('First 200 chars:', text.substring(0, 200));
                
                try {
                    const data = JSON.parse(text);
                    console.log('✅ Parsed JSON:', data);
                    
                    if (data.success) {
                        console.log('✅ Check-in successful!');
                        
                        try {
                            console.log('Step 1: Setting queueResult...');
                            queueResult = data;
                            
                            console.log('Step 2: Generating ticket...');
                            generateTicket();
                            
                            console.log('Step 3: Going to step 3...');
                            goToStep(3);
                            
                            console.log('Step 4: Updating stats...');
                            updateStats();
                            
                            console.log('✅ All steps completed successfully!');
                            
                            // Reset button (even though it's hidden, clean it up)
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-check me-2"></i>Confirm Check-in';
                            
                        } catch (error) {
                            console.error('❌ Error during success flow:', error);
                            showAlert('Error displaying success: ' + error.message, 'danger');
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-check me-2"></i>Confirm Check-in';
                        }
                    } else {
                        console.error('❌ Check-in failed:', data.message);
                        showAlert(data.message || 'An error occurred', 'danger');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-check me-2"></i>Confirm Check-in';
                        
                        // Restart auto-refresh even on error
                        startStatsAutoRefresh();
                    }
                } catch (e) {
                    console.error('❌ JSON parse error:', e);
                    console.error('❌ Raw response:', text);
                    
                    // Check if response is HTML (common PHP error)
                    if (text.includes('<!DOCTYPE') || text.includes('<html')) {
                        showAlert('Server returned HTML instead of JSON. Check PHP errors.', 'danger');
                    } else {
                        showAlert('Invalid server response. Check console for details.', 'danger');
                    }
                    
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check me-2"></i>Confirm Check-in';
                    
                    // Restart auto-refresh even on parse error
                    startStatsAutoRefresh();
                }
            })
            .catch(error => {
                clearTimeout(timeoutId);
                console.error('❌ Fetch error:', error);
                
                let errorMessage = 'Network error: ' + error.message;
                if (error.name === 'AbortError') {
                    errorMessage = 'Request timeout. Server took too long to respond. Please try again.';
                }
                
                showAlert(errorMessage, 'danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check me-2"></i>Confirm Check-in';
                
                // Restart auto-refresh even on network error
                startStatsAutoRefresh();
            });
        }

        function generateTicket() {
            // Just update the success message
            document.getElementById('successMessage').innerHTML = `
                <strong>${queueResult.patient.full_name}</strong> has been added to the queue<br>
                <span class="badge bg-primary" style="font-size: 1.2rem; padding: 10px 20px; margin-top: 10px;">
                    ${queueResult.queue_number}
                </span>
            `;
        }

        function resetForm() {
            // Reset variables
            selectedPatient = null;
            selectedService = 'consultation'; // Reset to default
            queueResult = null;
            
            // Clear form fields
            document.getElementById('patientSearch').value = '';
            document.getElementById('prioritySelect').value = 'regular';
            document.getElementById('notesInput').value = '';
            
            // Hide search results
            const searchResults = document.getElementById('searchResults');
            if (searchResults) {
                searchResults.style.display = 'none';
                searchResults.innerHTML = '';
            }
            
            // Hide priority section and continue button
            const prioritySection = document.getElementById('prioritySection');
            if (prioritySection) {
                prioritySection.style.display = 'none';
            }
            
            const continueButton = document.getElementById('continueButton');
            if (continueButton) {
                continueButton.style.display = 'none';
            }
            
            // Clear success message
            const successMessage = document.getElementById('successMessage');
            if (successMessage) {
                successMessage.innerHTML = 'Patient has been added to the queue';
            }
            
            // Update stats
            updateStats();
            
            // Go back to step 1
            goToStep(1);
        }

        function updateStats() {
            console.log('📊 Updating stats...');
            
            fetch('Queue_Checkin.php?ajax=get_stats')
            .then(response => {
                console.log('📡 Stats response:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('📊 Stats data received:', data);
                
                if (data.success && data.stats) {
                    // Update the stats cards using the correct classes and h2 tags
                    const waitingCard = document.querySelector('.waiting-card h2');
                    const servingCard = document.querySelector('.serving-card h2');
                    const completedCard = document.querySelector('.completed-card h2');
                    const todayCard = document.querySelector('.total-card h2');
                    
                    if (waitingCard) waitingCard.textContent = data.stats.total_waiting;
                    if (servingCard) servingCard.textContent = data.stats.total_serving;
                    if (completedCard) completedCard.textContent = data.stats.total_completed;
                    if (todayCard) todayCard.textContent = data.stats.total_today;
                    
                    console.log('✅ Stats updated successfully');
                } else {
                    console.error('❌ Invalid stats data:', data);
                }
            })
            .catch(error => {
                console.error('❌ Error updating stats:', error);
            });
        }

        function showAlert(message, type = 'danger') {
            const alertDiv = document.getElementById('alertMessage');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertDiv.style.display = 'block';
            window.scrollTo({top: 0, behavior: 'smooth'});
            
            if (type === 'success') {
                setTimeout(() => { alertDiv.style.display = 'none'; }, 5000);
            }
        }

        function startStatsAutoRefresh() {
            // IMPORTANT: Clear any existing interval first to prevent duplicates
            if (statsRefreshInterval) {
                clearInterval(statsRefreshInterval);
                statsRefreshInterval = null;
                console.log('🧹 Cleared existing auto-refresh');
            }
            
            // Update immediately
            updateStats();
            
            // Then update every 3 seconds
            statsRefreshInterval = setInterval(() => {
                updateStats();
            }, 3000);
            
            console.log('✅ Auto-refresh started with interval ID:', statsRefreshInterval);
        }

        // Start auto-refresh when page loads
        document.addEventListener('DOMContentLoaded', function() {
            startStatsAutoRefresh();
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (statsRefreshInterval) {
                clearInterval(statsRefreshInterval);
            }
        });
    </script>
</body>
</html>