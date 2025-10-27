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
    
    if ($_POST['action'] === 'get_queue_list') {
        $service_type = $_POST['service_type'] ?? 'all';
        $status = $_POST['status'] ?? 'waiting';
        $today = date('Y-m-d');
        
        $query = "
            SELECT q.*, 
                   p.patient_id, 
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   p.phone,
                   YEAR(CURDATE()) - YEAR(p.date_of_birth) as age,
                   CONCAT(s.first_name, ' ', s.last_name) as staff_name
            FROM queue q
            JOIN patients p ON q.patient_id = p.id
            LEFT JOIN staff s ON q.served_by = s.id
            WHERE q.queue_date = ?
        ";
        
        $params = [$today];
        
        if ($service_type !== 'all') {
            $query .= " AND q.service_type = ?";
            $params[] = $service_type;
        }
        
        if ($status !== 'all') {
            $query .= " AND q.status = ?";
            $params[] = $status;
        }
        
        $query .= " ORDER BY 
            CASE q.priority
                WHEN 'urgent' THEN 1
                WHEN 'senior_pwd' THEN 2
                WHEN 'pregnant' THEN 3
                WHEN 'regular' THEN 4
            END,
            q.check_in_time ASC
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $queue = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'queue' => $queue]);
        exit;
    }
    
    if ($_POST['action'] === 'call_next') {
        try {
            $service_type = $_POST['service_type'];
            $today = date('Y-m-d');
            
            $pdo->beginTransaction();
            
            // Get next patient in queue
            $stmt = $pdo->prepare("
                SELECT q.*, CONCAT(p.first_name, ' ', p.last_name) as patient_name
                FROM queue q
                JOIN patients p ON q.patient_id = p.id
                WHERE q.queue_date = ?
                AND q.service_type = ?
                AND q.status = 'waiting'
                ORDER BY 
                    CASE q.priority
                        WHEN 'urgent' THEN 1
                        WHEN 'senior_pwd' THEN 2
                        WHEN 'pregnant' THEN 3
                        WHEN 'regular' THEN 4
                    END,
                    q.check_in_time ASC
                LIMIT 1
            ");
            $stmt->execute([$today, $service_type]);
            $nextPatient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$nextPatient) {
                throw new Exception("No patients waiting in queue for this service.");
            }
            
            // Update queue status to serving
            $updateStmt = $pdo->prepare("
                UPDATE queue 
                SET status = 'serving',
                    called_time = NOW(),
                    served_by = ?
                WHERE id = ?
            ");
            $updateStmt->execute([$staff['id'], $nextPatient['id']]);
            
            // Update queue display
            $displayStmt = $pdo->prepare("
                INSERT INTO queue_display (counter_number, current_queue_id, current_queue_number, service_type, status, location_type, last_called_at)
                VALUES (1, ?, ?, ?, 'calling', 'RHU', NOW())
                ON DUPLICATE KEY UPDATE
                    current_queue_id = VALUES(current_queue_id),
                    current_queue_number = VALUES(current_queue_number),
                    status = VALUES(status),
                    last_called_at = VALUES(last_called_at)
            ");
            $displayStmt->execute([$nextPatient['id'], $nextPatient['queue_number'], $service_type]);
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Patient called successfully!',
                'patient' => $nextPatient
            ]);
            
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'start_serving') {
        try {
            $queue_id = $_POST['queue_id'];
            
            $stmt = $pdo->prepare("
                UPDATE queue 
                SET status = 'serving',
                    start_time = NOW(),
                    served_by = ?
                WHERE id = ?
            ");
            $stmt->execute([$staff['id'], $queue_id]);
            
            echo json_encode(['success' => true, 'message' => 'Started serving patient']);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'complete_queue') {
        try {
            $queue_id = $_POST['queue_id'];
            
            $pdo->beginTransaction();
            
            // Get queue info
            $queueStmt = $pdo->prepare("
                SELECT * FROM queue WHERE id = ?
            ");
            $queueStmt->execute([$queue_id]);
            $queue = $queueStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$queue) {
                throw new Exception("Queue entry not found");
            }
            
            // Calculate actual wait time
            $checkInTime = new DateTime($queue['check_in_time']);
            $now = new DateTime();
            $actualWait = $checkInTime->diff($now)->i; // minutes
            
            // Update queue
            $updateStmt = $pdo->prepare("
                UPDATE queue 
                SET status = 'completed',
                    end_time = NOW(),
                    actual_wait_minutes = ?
                WHERE id = ?
            ");
            $updateStmt->execute([$actualWait, $queue_id]);
            
            // Update analytics
            $analyticsStmt = $pdo->prepare("
                INSERT INTO queue_analytics (
                    analytics_date, service_type, location_type,
                    total_queued, total_served, avg_wait_time_minutes
                ) VALUES (CURDATE(), ?, 'RHU', 1, 1, ?)
                ON DUPLICATE KEY UPDATE
                    total_served = total_served + 1,
                    avg_wait_time_minutes = (avg_wait_time_minutes + VALUES(avg_wait_time_minutes)) / 2
            ");
            $analyticsStmt->execute([$queue['service_type'], $actualWait]);
            
            $pdo->commit();
            
            echo json_encode(['success' => true, 'message' => 'Queue completed successfully!']);
            
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'cancel_queue') {
        try {
            $queue_id = $_POST['queue_id'];
            $reason = $_POST['reason'] ?? 'No reason provided';
            
            $stmt = $pdo->prepare("
                UPDATE queue 
                SET status = 'cancelled',
                    cancellation_reason = ?
                WHERE id = ?
            ");
            $stmt->execute([$reason, $queue_id]);
            
            echo json_encode(['success' => true, 'message' => 'Queue cancelled']);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'mark_no_show') {
        try {
            $queue_id = $_POST['queue_id'];
            
            $stmt = $pdo->prepare("
                UPDATE queue 
                SET status = 'no_show'
                WHERE id = ?
            ");
            $stmt->execute([$queue_id]);
            
            echo json_encode(['success' => true, 'message' => 'Marked as no-show']);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($_POST['action'] === 'get_serving_queue') {
        $today = date('Y-m-d');
        
        $stmt = $pdo->prepare("
            SELECT q.*, 
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name
            FROM queue q
            JOIN patients p ON q.patient_id = p.id
            WHERE q.queue_date = ? AND q.status = 'serving'
            ORDER BY q.called_time DESC
        ");
        $stmt->execute([$today]);
        $servingQueue = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'serving' => $servingQueue,
            'count' => count($servingQueue)
        ]);
        exit;
    }
    
    if ($_POST['action'] === 'get_dashboard_stats') {
        $today = date('Y-m-d');
        
        $stmt = $pdo->prepare("
            SELECT 
                service_type,
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'waiting' THEN 1 END) as waiting,
                COUNT(CASE WHEN status = 'serving' THEN 1 END) as serving,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled,
                COUNT(CASE WHEN status = 'no_show' THEN 1 END) as no_show,
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

// Get current serving queue
$today = date('Y-m-d');
$servingStmt = $pdo->prepare("
    SELECT q.*, p.patient_id, CONCAT(p.first_name, ' ', p.last_name) as patient_name
    FROM queue q
    JOIN patients p ON q.patient_id = p.id
    WHERE q.queue_date = ? AND q.status = 'serving'
    ORDER BY q.called_time DESC
");
$servingStmt->execute([$today]);
$servingQueue = $servingStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Dashboard - RHU Admin</title>
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

        .btn-success {
            background: #28a745;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
        }

        .btn-warning {
            background: #ffc107;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
        }

        .btn-danger {
            background: #dc3545;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
        }

        .btn-secondary {
            background: #6c757d;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
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

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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

        /* Queue Dashboard Specific Styles */
        .service-tab {
            padding: 15px 25px;
            margin: 5px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .service-tab:hover {
            border-color: var(--kawit-pink);
            background-color: var(--light-pink);
        }

        .service-tab.active {
            background: var(--kawit-gradient);
            color: white;
            border-color: var(--dark-pink);
        }

        .service-tab i {
            font-size: 1.5rem;
            display: block;
            margin-bottom: 8px;
        }

        .queue-item {
            border: 1px solid #dee2e6;
            border-left: 4px solid var(--kawit-pink);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s;
            background: white;
        }

        .queue-item:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .queue-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--dark-pink);
        }

        .auto-refresh-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--kawit-gradient);
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 999;
            font-size: 0.85rem;
        }

        .serving-now-banner {
            background: var(--kawit-gradient);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .priority-urgent {
            border-left-color: #dc3545 !important;
        }

        .priority-senior_pwd {
            border-left-color: #ffc107 !important;
        }

        .priority-pregnant {
            border-left-color: #e91e63 !important;
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
        }

        .call-next-btn {
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            font-size: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            z-index: 998;
        }

        .call-next-btn:hover {
            transform: scale(1.1);
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
                    <a href="Queue_Checkin.php" class="nav-link">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Queue Check-in</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="Queue_Dashboard.php" class="nav-link active">
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
                <h1 class="page-title"><i class="fas fa-list-ol me-2"></i>Queue Management Dashboard</h1>
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
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="Profile.php">
                                    <i class="fas fa-user-cog me-2" style="color: var(--kawit-pink);"></i>Profile & Settings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="../../logout.php" onclick="return confirm('Are you sure you want to log out?')">
                                    <i class="fas fa-sign-out-alt me-2"></i>Log Out
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Auto-refresh indicator -->
            <div class="auto-refresh-indicator" id="autoRefreshIndicator">
                <i class="fas fa-sync-alt fa-spin me-2"></i>Auto-refreshing every 10s
            </div>

            <!-- Currently Serving (Dynamic) -->
            <div id="servingBannerContainer">
                <!-- Will be loaded via AJAX -->
            </div>

            <!-- Consultation Queue Only -->
            <div class="alert alert-info mb-4">
                <i class="fas fa-stethoscope me-2"></i>
                <strong>Queue Management: Consultation</strong>
            </div>

            <!-- Queue List -->
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Queue List</h5>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-success" id="autoRefreshBtn" onclick="toggleAutoRefresh()" title="Toggle Auto-Refresh">
                            <i class="fas fa-sync-alt"></i> Auto: ON
                        </button>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-light" onclick="filterStatus('waiting')">
                                <i class="fas fa-clock me-1"></i>Waiting
                            </button>
                            <button class="btn btn-sm btn-light" onclick="filterStatus('all')">
                                <i class="fas fa-list me-1"></i>All
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body" id="queueListContainer">
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                        <p class="mt-3">Loading queue...</p>
                    </div>
                </div>
            </div>

            <!-- Call Next Button (Floating) -->
            <button class="btn btn-success call-next-btn" onclick="callNextPatient()" id="callNextBtn" title="Call Next Consultation Patient">
                <i class="fas fa-bell"></i>
            </button>
        </div>
    </div>

    <!-- Cancel Queue Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cancel Queue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Cancellation Reason</label>
                    <textarea class="form-control" id="cancellationReason" rows="3" placeholder="Enter reason..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" onclick="confirmCancel()">Confirm Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentService = 'consultation';
        let currentStatus = 'waiting';
        let cancelQueueId = null;
        let refreshInterval;
        let autoRefreshEnabled = true;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadServingBanner();  // ← ADD THIS
            loadQueueList();
            updateDashboardStats();
            
            // Auto-refresh every 5 seconds (faster refresh)
            startAutoRefresh();
        });

        function startAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
            if (autoRefreshEnabled) {
                refreshInterval = setInterval(() => {
                    loadServingBanner();  // ← ADD THIS
                    loadQueueList();
                    updateDashboardStats();
                }, 5000); // 5 seconds for smoother updates
            }
        }

        function toggleAutoRefresh() {
            autoRefreshEnabled = !autoRefreshEnabled;
            const btn = document.getElementById('autoRefreshBtn');
            
            if (autoRefreshEnabled) {
                btn.innerHTML = '<i class="fas fa-sync-alt"></i> Auto: ON';
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-success');
                startAutoRefresh();
            } else {
                btn.innerHTML = '<i class="fas fa-sync-alt"></i> Auto: OFF';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
                if (refreshInterval) {
                    clearInterval(refreshInterval);
                }
            }
        }

        function loadServingBanner() {
            const formData = new FormData();
            formData.append('action', 'get_serving_queue');

            fetch('Queue_Dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayServingBanner(data.serving);
                }
            })
            .catch(error => {
                console.error('Error loading serving queue:', error);
            });
        }

        function displayServingBanner(serving) {
            const container = document.getElementById('servingBannerContainer');
            
            if (serving.length === 0) {
                container.innerHTML = ''; // Hide banner if no one is being served
                return;
            }

            let html = `
                <div class="serving-now-banner">
                    <h5 class="mb-3"><i class="fas fa-user-clock me-2"></i>Currently Being Served</h5>
                    <div class="row">
            `;

            serving.forEach(item => {
                html += `
                    <div class="col-md-6 mb-2">
                        <div class="d-flex justify-content-between align-items-center bg-white bg-opacity-25 rounded p-3">
                            <div>
                                <div class="queue-number text-white">${item.queue_number}</div>
                                <strong>${item.patient_name}</strong><br>
                                <small>${item.service_type.charAt(0).toUpperCase() + item.service_type.slice(1)}</small>
                            </div>
                            <button class="btn btn-light btn-sm" onclick="completeQueue(${item.id})">
                                <i class="fas fa-check me-1"></i>Complete
                            </button>
                        </div>
                    </div>
                `;
            });

            html += `
                    </div>
                </div>
            `;

            container.innerHTML = html;
        }


        function filterStatus(status) {
            currentStatus = status;
            loadQueueList();
        }

        function loadQueueList() {
            const formData = new FormData();
            formData.append('action', 'get_queue_list');
            formData.append('service_type', currentService);
            formData.append('status', currentStatus);

            fetch('Queue_Dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayQueueList(data.queue);
                }
            })
            .catch(error => {
                console.error('Error loading queue:', error);
            });
        }

        function displayQueueList(queue) {
            const container = document.getElementById('queueListContainer');
            
            if (queue.length === 0) {
                container.innerHTML = `
                    <div class="empty-queue">
                        <i class="fas fa-inbox fa-4x mb-3"></i>
                        <h5>No patients in queue</h5>
                        <p class="text-muted">Queue is empty for the selected filters</p>
                    </div>
                `;
                return;
            }

            let html = '';
            queue.forEach((item, index) => {
                const priorityColors = {
                    'urgent': 'danger',
                    'senior_pwd': 'warning',
                    'pregnant': 'info',
                    'regular': 'secondary'
                };
                
                const priorityLabels = {
                    'urgent': 'URGENT',
                    'senior_pwd': 'Senior/PWD',
                    'pregnant': 'Pregnant',
                    'regular': 'Regular'
                };

                const statusBadge = {
                    'waiting': '<span class="badge bg-warning">Waiting</span>',
                    'serving': '<span class="badge bg-info">Serving</span>',
                    'completed': '<span class="badge bg-success">Completed</span>',
                    'cancelled': '<span class="badge bg-secondary">Cancelled</span>',
                    'no_show': '<span class="badge bg-dark">No Show</span>'
                };

                const checkInTime = new Date(item.check_in_time);
                const waitTime = Math.floor((new Date() - checkInTime) / 60000); // minutes

                html += `
                    <div class="queue-item ${item.status} ${item.priority === 'urgent' ? 'urgent' : ''}">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <div class="queue-number">${item.queue_number}</div>
                                <small class="text-muted">#${index + 1} in line</small>
                            </div>
                            <div class="col-md-3">
                                <strong>${item.patient_name}</strong><br>
                                <small class="text-muted">${item.patient_id} | ${item.age} years old</small><br>
                                ${item.phone ? `<small class="text-muted"><i class="fas fa-phone me-1"></i>${item.phone}</small>` : ''}
                            </div>
                            <div class="col-md-2">
                                <small class="text-muted">Service:</small><br>
                                <strong>${item.service_type.replace('_', ' ').toUpperCase()}</strong>
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-${priorityColors[item.priority]} priority-badge">
                                    ${priorityLabels[item.priority]}
                                </span><br>
                                <small class="text-muted mt-1">Wait: ${waitTime} min</small>
                            </div>
                            <div class="col-md-1 text-center">
                                ${statusBadge[item.status]}
                            </div>
                            <div class="col-md-2 text-end">
                                ${getActionButtons(item)}
                            </div>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        function getActionButtons(item) {
            if (item.status === 'waiting') {
                return `
                    <button class="btn btn-sm btn-primary mb-1" onclick="startServing(${item.id})" title="Start Serving">
                        <i class="fas fa-play"></i>
                    </button>
                    <button class="btn btn-sm btn-danger mb-1" onclick="openCancelModal(${item.id})" title="Cancel">
                        <i class="fas fa-times"></i>
                    </button>
                    <button class="btn btn-sm btn-secondary mb-1" onclick="markNoShow(${item.id})" title="No Show">
                        <i class="fas fa-user-slash"></i>
                    </button>
                `;
            } else if (item.status === 'serving') {
                return `
                    <button class="btn btn-sm btn-success" onclick="completeQueue(${item.id})" title="Complete">
                        <i class="fas fa-check"></i> Complete
                    </button>
                `;
            } else {
                return `<small class="text-muted">-</small>`;
            }
        }

        function callNextPatient() {
            if (!confirm('Call next patient for consultation?')) return;

            const btn = document.getElementById('callNextBtn');
            btn.disabled = true;

            const formData = new FormData();
            formData.append('action', 'call_next');
            formData.append('service_type', 'consultation');

            fetch('Queue_Dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Patient called: ' + data.patient.queue_number, 'success');
                    loadServingBanner();  // ← ADD THIS
                    loadQueueList();
                    updateDashboardStats();
                } else {
                    showAlert(data.message, 'warning');
                }
                btn.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error calling next patient', 'danger');
                btn.disabled = false;
            });
        }

        function startServing(queueId) {
            const formData = new FormData();
            formData.append('action', 'start_serving');
            formData.append('queue_id', queueId);

            fetch('Queue_Dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Started serving patient', 'success');
                    loadQueueList();
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function completeQueue(queueId) {
            if (!confirm('Mark this queue as completed?')) return;

            const formData = new FormData();
            formData.append('action', 'complete_queue');
            formData.append('queue_id', queueId);

            fetch('Queue_Dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Queue completed!', 'success');
                    loadServingBanner();  // ← ADD THIS
                    loadQueueList();
                    updateDashboardStats();
                    // Remove location.reload() - no longer needed!
                } else {
                    showAlert(data.message, 'danger');
                }
            })
        }

        function openCancelModal(queueId) {
            cancelQueueId = queueId;
            const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
            modal.show();
        }

        function confirmCancel() {
            const reason = document.getElementById('cancellationReason').value;

            const formData = new FormData();
            formData.append('action', 'cancel_queue');
            formData.append('queue_id', cancelQueueId);
            formData.append('reason', reason);

            fetch('Queue_Dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Queue cancelled', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('cancelModal')).hide();
                    loadQueueList();
                    updateDashboardStats();
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function markNoShow(queueId) {
            if (!confirm('Mark this patient as no-show?')) return;

            const formData = new FormData();
            formData.append('action', 'mark_no_show');
            formData.append('queue_id', queueId);

            fetch('Queue_Dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Marked as no-show', 'info');
                    loadQueueList();
                    updateDashboardStats();
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function updateDashboardStats() {
            const formData = new FormData();
            formData.append('action', 'get_dashboard_stats');

            fetch('Queue_Dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let totalWaiting = 0;
                    data.stats.forEach(stat => {
                        const count = stat.waiting || 0;
                        document.getElementById('count-' + stat.service_type).textContent = count;
                        totalWaiting += count;
                    });
                    document.getElementById('count-all').textContent = totalWaiting;
                }
            })
            .catch(error => {
                console.error('Error updating stats:', error);
            });
        }

        function showAlert(message, type = 'info') {
            // Simple alert for now
            console.log(type + ': ' + message);
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        });
    </script>
</body>
</html>