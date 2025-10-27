<?php
session_start();

// Check if user is logged in and is a patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    header('Location: ../login.php');
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

// Get patient information with calculated age
$stmt = $pdo->prepare("
    SELECT p.*, u.username, b.barangay_name,
           YEAR(CURDATE()) - YEAR(p.date_of_birth) as age
    FROM patients p 
    JOIN users u ON p.user_id = u.id 
    LEFT JOIN barangays b ON p.barangay_id = b.id
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    header('Location: ../login.php');
    exit;
}

// Get current queue status
$today = date('Y-m-d');
$queueStmt = $pdo->prepare("
    SELECT q.*, 
           TIMESTAMPDIFF(MINUTE, q.check_in_time, NOW()) as wait_time_minutes
    FROM queue q
    WHERE q.patient_id = ?
    AND q.queue_date = ?
    AND q.status IN ('waiting', 'serving')
    ORDER BY q.check_in_time DESC
    LIMIT 1
");
$queueStmt->execute([$patient['id'], $today]);
$currentQueue = $queueStmt->fetch(PDO::FETCH_ASSOC);

// If patient has queue, get position
$queuePosition = null;
$peopleAhead = null;
if ($currentQueue) {
    $positionStmt = $pdo->prepare("
        SELECT COUNT(*) FROM queue
        WHERE queue_date = ?
        AND service_type = ?
        AND status = 'waiting'
        AND (
            (priority = 'urgent' AND ? != 'urgent') OR
            (priority = 'senior_pwd' AND ? NOT IN ('urgent', 'senior_pwd')) OR
            (priority = 'pregnant' AND ? = 'regular') OR
            (priority = ? AND check_in_time < ?)
        )
    ");
    $positionStmt->execute([
        $today,
        $currentQueue['service_type'],
        $currentQueue['priority'],
        $currentQueue['priority'],
        $currentQueue['priority'],
        $currentQueue['priority'],
        $currentQueue['check_in_time']
    ]);
    $peopleAhead = $positionStmt->fetchColumn();
    $queuePosition = $peopleAhead + 1;
}
// Get recent consultations
$consultationsStmt = $pdo->prepare("
    SELECT c.*, CONCAT(s.first_name, ' ', s.last_name) as doctor_name
    FROM consultations c 
    LEFT JOIN staff s ON c.assigned_doctor = s.id
    WHERE c.patient_id = ? AND c.status = 'completed'
    ORDER BY c.consultation_date DESC
    LIMIT 5
");
$consultationsStmt->execute([$patient['id']]);
$recentConsultations = $consultationsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent lab results
$labResultsStmt = $pdo->prepare("
    SELECT lr.*, CONCAT(s.first_name, ' ', s.last_name) as performed_by_name
    FROM laboratory_results lr 
    LEFT JOIN staff s ON lr.performed_by = s.id
    WHERE lr.patient_id = ? AND lr.status = 'completed'
    ORDER BY lr.test_date DESC
    LIMIT 3
");
$labResultsStmt->execute([$patient['id']]);
$recentLabResults = $labResultsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent prescriptions
$prescriptionsStmt = $pdo->prepare("
    SELECT pr.*, CONCAT(s.first_name, ' ', s.last_name) as prescribed_by_name
    FROM prescriptions pr 
    LEFT JOIN staff s ON pr.prescribed_by = s.id
    WHERE pr.patient_id = ? 
    ORDER BY pr.prescription_date DESC
    LIMIT 3
");
$prescriptionsStmt->execute([$patient['id']]);
$recentPrescriptions = $prescriptionsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get announcements
$announcementsStmt = $pdo->prepare("
    SELECT a.*, CONCAT(s.first_name, ' ', s.last_name) as author_name
    FROM announcements a 
    JOIN staff s ON a.author_id = s.id
    WHERE a.status = 'published' 
    AND (a.expiry_date IS NULL OR a.expiry_date > NOW())
    AND (a.target_audience IN ('all', 'patients'))
    ORDER BY a.priority DESC, a.publish_date DESC 
    LIMIT 5
");
$announcementsStmt->execute();
$announcements = $announcementsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending notifications count
$notificationsStmt = $pdo->prepare("
    SELECT COUNT(*) as unread_count 
    FROM notifications 
    WHERE user_id = ? AND is_read = 0 AND (expires_at IS NULL OR expires_at > NOW())
");
$notificationsStmt->execute([$_SESSION['user_id']]);
$notificationCount = $notificationsStmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kawit RHU - Patient Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Patient/style.css">

</head>
<body>
    <div class="main-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="../Pictures/logo2.png" alt="Logo 1" onerror="this.style.display='none'">
                    <img src="../Pictures/logo1.png" alt="Logo 2" onerror="this.style.display='none'">
                    <img src="../Pictures/logo3.png" alt="Logo 3" onerror="this.style.display='none'">
                </div>
                <div class="logo-circle">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="logo-text">
                    KAWIT<br>
                    <small style="font-size: 0.8rem; opacity: 0.8;">RHU</small>
                </div>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="Dashboard.php" class="nav-link active">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="Health_Record.php" class="nav-link">
                        <i class="fas fa-file-medical"></i>
                        <span>Health Records</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="Medical_Certificates.php" class="nav-link">
                        <i class="fas fa-certificate"></i>
                        <span>Medical Certificate</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="Consultation.php" class="nav-link">
                        <i class="fas fa-video"></i>
                        <span>Online Consultation</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="../logout.php" class="nav-link" onclick="return confirm('Are you sure you want to log out?')">
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
                <h1 class="page-title">Dashboard</h1>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php 
                        $initials = strtoupper(substr($patient['first_name'], 0, 1) . substr($patient['last_name'], 0, 1));
                        echo $initials;
                        ?>
                    </div>
                        <div class="user-details">
                            <div class="user-name"><?php echo htmlspecialchars($patient['first_name'] . ' ' . substr($patient['last_name'], 0, 1) . '.'); ?></div>
                            <div class="user-role">Patient</div>
                        </div>
                        <div class="dropdown">
                        <button class="btn btn-link text-dark p-0 ms-2" type="button" data-bs-toggle="dropdown" style="text-decoration: none;">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" style="border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.15);">
                            <li>
                                <a class="dropdown-item" href="Profile.php" style="border-radius: 8px; margin: 2px;">
                                    <i class="fas fa-user-edit me-2" style="color: var(--kawit-pink);"></i>Edit Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider" style="margin: 8px 0;"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="../logout.php" onclick="return confirm('Are you sure you want to log out?')" style="border-radius: 8px; margin: 2px;">
                                    <i class="fas fa-sign-out-alt me-2"></i>Log Out
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Welcome Section -->
                <div class="content-section" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="welcome-section">
                        <div style="display: flex; align-items: center;">
                            <div class="section-icon">
                                <i class="fas fa-home"></i>
                            </div>
                            <div>
                                <h3 class="section-title" style="margin-bottom: 0.3rem;">Welcome back, <?php echo htmlspecialchars($patient['first_name']); ?>!</h3>
                                <div class="welcome-info">
                                    <?php echo htmlspecialchars($patient['age']); ?> years old • 
                                    <?php echo htmlspecialchars($patient['barangay_name'] ?? 'No barangay assigned'); ?>
                                </div>
                            </div>
                        </div>
                        <span class="patient-id">ID: <?php echo htmlspecialchars($patient['patient_id']); ?></span>
                    </div>
                </div>

                <!-- Stats Overview -->
                <div class="stats-row">
                    <div class="stat-card" style="<?php echo $currentQueue ? 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;' : ''; ?>">
                        <div class="stat-number"><?php echo $currentQueue ? '1' : '0'; ?></div>
                        <div class="stat-label">Active Queue</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($recentConsultations); ?></div>
                        <div class="stat-label">Recent Consultations</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($recentPrescriptions); ?></div>
                        <div class="stat-label">Active Prescriptions</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($recentLabResults); ?></div>
                        <div class="stat-label">Recent Lab Results</div>
                    </div>
                </div>

                <!-- Grid Layout for All Sections -->
                <div class="row g-3 mb-4">
                <!-- Queue Status -->
                <div class="col-lg-6">
                    <div class="content-section" style="padding: 1.5rem; height: 100%;" id="queueSection">
                        <div class="section-header" style="margin-bottom: 1rem;">
                            <div class="section-header-left">
                                <div class="section-icon" style="width: 40px; height: 40px; font-size: 1.1rem;">
                                    <i class="fas fa-ticket-alt"></i>
                                </div>
                                <h4 class="section-title" style="font-size: 1.2rem;">My Queue Status</h4>
                            </div>
                        </div>
                        
                        <?php if ($currentQueue): ?>
                            <?php 
                            $serviceLabels = [
                                'consultation' => 'Consultation',
                                'laboratory' => 'Laboratory',
                                'pharmacy' => 'Pharmacy',
                                'certificate' => 'Medical Certificate'
                            ];
                            $priorityColors = [
                                'urgent' => '#dc3545',
                                'senior_pwd' => '#ffc107',
                                'pregnant' => '#17a2b8',
                                'regular' => '#6c757d'
                            ];
                            $priorityLabels = [
                                'urgent' => 'URGENT',
                                'senior_pwd' => 'Priority',
                                'pregnant' => 'Priority',
                                'regular' => 'Regular'
                            ];
                            ?>
                            
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; text-align: center; margin-bottom: 15px;">
                                <div style="font-size: 0.9rem; margin-bottom: 10px; opacity: 0.9;">Your Queue Number</div>
                                <div style="font-size: 3rem; font-weight: bold; font-family: 'Courier New', monospace; animation: pulse 2s infinite;">
                                    <?php echo htmlspecialchars($currentQueue['queue_number']); ?>
                                </div>
                                <div style="margin-top: 15px;">
                                    <span style="background: rgba(255,255,255,0.2); padding: 8px 20px; border-radius: 20px; display: inline-block;">
                                        <i class="fas fa-<?php echo $currentQueue['status'] === 'serving' ? 'user-clock' : 'clock'; ?> me-2"></i>
                                        <?php echo $currentQueue['status'] === 'serving' ? 'Being Served Now!' : 'Waiting'; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="row g-2">
                                <div class="col-6">
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center;">
                                        <i class="fas fa-stethoscope" style="font-size: 1.5rem; color: #667eea; margin-bottom: 5px;"></i>
                                        <div style="font-size: 0.85rem; color: #6c757d;">Service</div>
                                        <div style="font-weight: bold;"><?php echo $serviceLabels[$currentQueue['service_type']] ?? $currentQueue['service_type']; ?></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center;">
                                        <i class="fas fa-users" style="font-size: 1.5rem; color: #667eea; margin-bottom: 5px;"></i>
                                        <div style="font-size: 0.85rem; color: #6c757d;">People Ahead</div>
                                        <div style="font-weight: bold; font-size: 1.5rem;"><?php echo $peopleAhead; ?></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center;">
                                        <i class="fas fa-list-ol" style="font-size: 1.5rem; color: #667eea; margin-bottom: 5px;"></i>
                                        <div style="font-size: 0.85rem; color: #6c757d;">Position</div>
                                        <div style="font-weight: bold; font-size: 1.5rem;">#<?php echo $queuePosition; ?></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center;">
                                        <i class="fas fa-hourglass-half" style="font-size: 1.5rem; color: #667eea; margin-bottom: 5px;"></i>
                                        <div style="font-size: 0.85rem; color: #6c757d;">Waiting</div>
                                        <div style="font-weight: bold;"><?php echo $currentQueue['wait_time_minutes']; ?> min</div>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($currentQueue['status'] === 'serving'): ?>
                                <div style="background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 10px; margin-top: 15px; text-align: center;">
                                    <i class="fas fa-bell me-2"></i>
                                    <strong>It's your turn!</strong> Please proceed to the service counter.
                                </div>
                            <?php elseif ($peopleAhead <= 3 && $peopleAhead > 0): ?>
                                <div style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 10px; margin-top: 15px; text-align: center;">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Your turn is coming soon! Please stay nearby.
                                </div>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-ticket-alt"></i>
                                <p>You're not in any queue</p>
                                <small class="text-muted">Visit the RHU to check-in and get a queue number</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                    <!-- Recent Consultations -->
                    <div class="col-lg-6">
                        <div class="content-section" style="padding: 1.5rem; height: 100%;">
                            <div class="section-header" style="margin-bottom: 1rem;">
                                <div class="section-header-left">
                                    <div class="section-icon" style="width: 40px; height: 40px; font-size: 1.1rem;">
                                        <i class="fas fa-stethoscope"></i>
                                    </div>
                                    <h4 class="section-title" style="font-size: 1.2rem;">Recent Consultations</h4>
                                </div>
                            </div>
                            <a href="Health_Record.php" class="view-all-link" style="position: absolute; top: 1.5rem; right: 1.5rem;">
                                View All <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                            
                            <?php if (!empty($recentConsultations)): ?>
                                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <?php foreach (array_slice($recentConsultations, 0, 3) as $consultation): ?>
                                    <div class="item-card" style="border-left-color: #28a745; margin-bottom: 0;">
                                        <div class="item-date">
                                            <?php echo date('M j, Y', strtotime($consultation['consultation_date'])); ?>
                                        </div>
                                        <div class="item-title"><?php echo ucfirst($consultation['consultation_type']); ?> Consultation</div>
                                        <?php if ($consultation['doctor_name']): ?>
                                            <small style="color: #6c757d;">with Dr. <?php echo htmlspecialchars($consultation['doctor_name']); ?></small>
                                        <?php endif; ?>
                                        <?php if ($consultation['diagnosis']): ?>
                                            <br><small style="color: #28a745;"><?php echo htmlspecialchars($consultation['diagnosis']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state" style="padding: 1.5rem;">
                                    <i class="fas fa-stethoscope"></i>
                                    <h6>No Consultation History</h6>
                                    <p>Your consultation history will appear here.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Lab Results and Prescriptions Row -->
                <div class="row g-3 mb-4">
                    <!-- Recent Lab Results -->
                    <div class="col-lg-6">
                        <div class="content-section" style="padding: 1.5rem; height: 100%;">
                            <div class="section-header" style="margin-bottom: 1rem;">
                                <div class="section-header-left">
                                    <div class="section-icon" style="width: 40px; height: 40px; font-size: 1.1rem;">
                                        <i class="fas fa-flask"></i>
                                    </div>
                                    <h4 class="section-title" style="font-size: 1.2rem;">Recent Lab Results</h4>
                                </div>
                            </div>
                            <a href="Health_Record.php" class="view-all-link" style="position: absolute; top: 1.5rem; right: 1.5rem;">
                                View All <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                            
                            <?php if (!empty($recentLabResults)): ?>
                                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <?php foreach ($recentLabResults as $labResult): ?>
                                    <div class="item-card" style="border-left-color: #007bff; margin-bottom: 0;">
                                        <div class="item-date">
                                            <?php echo date('M j, Y', strtotime($labResult['test_date'])); ?>
                                        </div>
                                        <div class="item-title"><?php echo htmlspecialchars($labResult['test_type']); ?></div>
                                        <?php if ($labResult['test_category']): ?>
                                            <small style="color: #6c757d;"><?php echo htmlspecialchars($labResult['test_category']); ?></small><br>
                                        <?php endif; ?>
                                        <span class="item-status status-<?php echo $labResult['status']; ?>">
                                            <?php echo ucfirst($labResult['status']); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state" style="padding: 1.5rem;">
                                    <i class="fas fa-flask"></i>
                                    <h6>No Lab Results</h6>
                                    <p>Your lab test results will appear here.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Prescriptions -->
                    <div class="col-lg-6">
                        <div class="content-section" style="padding: 1.5rem; height: 100%;">
                            <div class="section-header" style="margin-bottom: 1rem;">
                                <div class="section-header-left">
                                    <div class="section-icon" style="width: 40px; height: 40px; font-size: 1.1rem;">
                                        <i class="fas fa-pills"></i>
                                    </div>
                                    <h4 class="section-title" style="font-size: 1.2rem;">Recent Prescriptions</h4>
                                </div>
                            </div>
                            <a href="Health_Record.php" class="view-all-link" style="position: absolute; top: 1.5rem; right: 1.5rem;">
                                View All <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                            
                            <?php if (!empty($recentPrescriptions)): ?>
                                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <?php foreach ($recentPrescriptions as $prescription): ?>
                                    <div class="item-card" style="border-left-color: #6f42c1; margin-bottom: 0;">
                                        <div class="item-date">
                                            <?php echo date('M j, Y', strtotime($prescription['prescription_date'])); ?>
                                        </div>
                                        <div class="item-title"><?php echo htmlspecialchars($prescription['medication_name']); ?></div>
                                        <small style="color: #6c757d;">
                                            <?php echo htmlspecialchars($prescription['dosage_strength'] . ' - ' . $prescription['frequency']); ?>
                                        </small><br>
                                        <span class="item-status status-<?php echo str_replace('_', '-', $prescription['status']); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $prescription['status'])); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state" style="padding: 1.5rem;">
                                    <i class="fas fa-pills"></i>
                                    <h6>No Prescriptions</h6>
                                    <p>Your prescribed medications will appear here.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Announcements -->
                <div class="content-section" style="padding: 1.5rem;">
                    <div class="section-header" style="margin-bottom: 1rem;">
                        <div class="section-header-left">
                            <div class="section-icon" style="width: 40px; height: 40px; font-size: 1.1rem;">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <h4 class="section-title" style="font-size: 1.2rem;">Announcements & Updates</h4>
                        </div>
                    </div>
                    
                    <?php if (!empty($announcements)): ?>
                        <div class="announcements-grid">
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="item-card" style="border-left-color: #ffc107;">
                                <div class="item-date">
                                    <?php echo date('M j, Y', strtotime($announcement['publish_date'])); ?>
                                    <?php if ($announcement['featured']): ?>
                                        <span class="badge bg-warning text-dark ms-2">Featured</span>
                                    <?php endif; ?>
                                </div>
                                <div class="item-title"><?php echo htmlspecialchars($announcement['title']); ?></div>
                                <p style="color: #6c757d; margin: 0.5rem 0; font-size: 0.9rem;">
                                    <?php echo htmlspecialchars(substr($announcement['content'], 0, 120)); ?>
                                    <?php if (strlen($announcement['content']) > 120): ?>...<?php endif; ?>
                                </p>
                                <span class="item-status priority-<?php echo $announcement['priority']; ?>">
                                    <?php echo ucfirst($announcement['priority']); ?> Priority
                                </span>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-bullhorn"></i>
                            <h6>No Announcements</h6>
                            <p>Check back later for updates from the RHU.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Check session on page load
        window.onload = function() {
            <?php if (!isset($_SESSION['user_id'])): ?>
                window.location.href = '../login.php';
            <?php endif; ?>
        };

        // Auto-refresh queue section every 10 seconds
        function refreshQueueStatus() {
            fetch('get_queue_status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateQueueDisplay(data);
                    }
                })
                .catch(error => {
                    console.error('Error refreshing queue:', error);
                });
        }

        function updateQueueDisplay(data) {
            const queueSection = document.getElementById('queueSection');
            
            if (!data.has_queue) {
                // No queue
                queueSection.innerHTML = `
                    <div class="section-header" style="margin-bottom: 1rem;">
                        <div class="section-header-left">
                            <div class="section-icon" style="width: 40px; height: 40px; font-size: 1.1rem;">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <h4 class="section-title" style="font-size: 1.2rem;">My Queue Status</h4>
                        </div>
                    </div>
                    <div class="empty-state">
                        <i class="fas fa-ticket-alt"></i>
                        <p>You're not in any queue</p>
                        <small class="text-muted">Visit the RHU to check-in and get a queue number</small>
                    </div>
                `;
                return;
            }

            const queue = data.queue;
            const serviceLabels = {
                'consultation': 'Consultation',
                'laboratory': 'Laboratory',
                'pharmacy': 'Pharmacy',
                'certificate': 'Medical Certificate'
            };

            let alertHtml = '';
            if (queue.status === 'serving') {
                alertHtml = `
                    <div style="background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 10px; margin-top: 15px; text-align: center;">
                        <i class="fas fa-bell me-2"></i>
                        <strong>It's your turn!</strong> Please proceed to the service counter.
                    </div>
                `;
            } else if (data.people_ahead <= 3 && data.people_ahead > 0) {
                alertHtml = `
                    <div style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 10px; margin-top: 15px; text-align: center;">
                        <i class="fas fa-info-circle me-2"></i>
                        Your turn is coming soon! Please stay nearby.
                    </div>
                `;
            }

            queueSection.innerHTML = `
                <div class="section-header" style="margin-bottom: 1rem;">
                    <div class="section-header-left">
                        <div class="section-icon" style="width: 40px; height: 40px; font-size: 1.1rem;">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <h4 class="section-title" style="font-size: 1.2rem;">My Queue Status</h4>
                    </div>
                </div>
                
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; text-align: center; margin-bottom: 15px;">
                    <div style="font-size: 0.9rem; margin-bottom: 10px; opacity: 0.9;">Your Queue Number</div>
                    <div style="font-size: 3rem; font-weight: bold; font-family: 'Courier New', monospace; animation: pulse 2s infinite;">
                        ${queue.queue_number}
                    </div>
                    <div style="margin-top: 15px;">
                        <span style="background: rgba(255,255,255,0.2); padding: 8px 20px; border-radius: 20px; display: inline-block;">
                            <i class="fas fa-${queue.status === 'serving' ? 'user-clock' : 'clock'} me-2"></i>
                            ${queue.status === 'serving' ? 'Being Served Now!' : 'Waiting'}
                        </span>
                    </div>
                </div>
                
                <div class="row g-2">
                    <div class="col-6">
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center;">
                            <i class="fas fa-stethoscope" style="font-size: 1.5rem; color: #667eea; margin-bottom: 5px;"></i>
                            <div style="font-size: 0.85rem; color: #6c757d;">Service</div>
                            <div style="font-weight: bold;">${serviceLabels[queue.service_type] || queue.service_type}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center;">
                            <i class="fas fa-users" style="font-size: 1.5rem; color: #667eea; margin-bottom: 5px;"></i>
                            <div style="font-size: 0.85rem; color: #6c757d;">People Ahead</div>
                            <div style="font-weight: bold; font-size: 1.5rem;">${data.people_ahead}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center;">
                            <i class="fas fa-list-ol" style="font-size: 1.5rem; color: #667eea; margin-bottom: 5px;"></i>
                            <div style="font-size: 0.85rem; color: #6c757d;">Position</div>
                            <div style="font-weight: bold; font-size: 1.5rem;">#${data.position}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center;">
                            <i class="fas fa-hourglass-half" style="font-size: 1.5rem; color: #667eea; margin-bottom: 5px;"></i>
                            <div style="font-size: 0.85rem; color: #6c757d;">Waiting</div>
                            <div style="font-weight: bold;">${queue.wait_time_minutes} min</div>
                        </div>
                    </div>
                </div>
                ${alertHtml}
            `;
        }

        // Start auto-refresh (every 10 seconds)
        setInterval(refreshQueueStatus, 10000);
    </script>
</body>
</html>