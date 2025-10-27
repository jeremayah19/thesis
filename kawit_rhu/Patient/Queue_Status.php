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

// Get patient information
$stmt = $pdo->prepare("
    SELECT p.*, u.username 
    FROM patients p 
    JOIN users u ON p.user_id = u.id 
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    header('Location: ../login.php');
    exit;
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'get_my_queue') {
        $today = date('Y-m-d');
        
        // Get patient's active queue
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
        $myQueue = $queueStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($myQueue) {
            // Get position in queue (how many are ahead)
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
                $myQueue['service_type'],
                $myQueue['priority'],
                $myQueue['priority'],
                $myQueue['priority'],
                $myQueue['priority'],
                $myQueue['check_in_time']
            ]);
            $ahead = $positionStmt->fetchColumn();
            
            // Get currently serving for this service
            $servingStmt = $pdo->prepare("
                SELECT queue_number FROM queue
                WHERE queue_date = ?
                AND service_type = ?
                AND status = 'serving'
                ORDER BY called_time DESC
                LIMIT 1
            ");
            $servingStmt->execute([$today, $myQueue['service_type']]);
            $currentServing = $servingStmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'has_queue' => true,
                'queue' => $myQueue,
                'position' => $ahead + 1,
                'people_ahead' => $ahead,
                'current_serving' => $currentServing ? $currentServing['queue_number'] : null
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'has_queue' => false
            ]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'get_queue_stats') {
        $today = date('Y-m-d');
        
        $statsStmt = $pdo->prepare("
            SELECT 
                service_type,
                COUNT(CASE WHEN status = 'waiting' THEN 1 END) as waiting_count,
                AVG(CASE WHEN status = 'completed' THEN actual_wait_minutes END) as avg_wait_time
            FROM queue
            WHERE queue_date = ?
            GROUP BY service_type
        ");
        $statsStmt->execute([$today]);
        $stats = $statsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'stats' => $stats]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Status - Kawit RHU</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .my-queue-number {
            font-size: 4rem;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            animation: pulse 2s infinite;
        }
        
        .refresh-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 1000;
            border: none;
        }
        
        .priority-indicator {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-top: 10px;
        }
        
        .priority-urgent {
            background: #dc3545;
            color: white;
            animation: blink 1s infinite;
        }
        
        .priority-senior {
            background: #ffc107;
            color: #000;
        }
        
        .priority-pregnant {
            background: #17a2b8;
            color: white;
        }
        
        .priority-regular {
            background: #6c757d;
            color: white;
        }
        
        @keyframes blink {
            0%, 50%, 100% { opacity: 1; }
            25%, 75% { opacity: 0.7; }
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }
    </style>
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
                    <a href="Dashboard.php" class="nav-link">
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
                    <a href="Queue_Status.php" class="nav-link active">
                        <i class="fas fa-users"></i>
                        <span>Queue Status</span>
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
                <h1 class="page-title">Queue Status</h1>
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
                <!-- Page Header -->
                <div class="content-section" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="section-header">
                        <div class="section-header-left">
                            <div class="section-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <h3 class="section-title" style="margin-bottom: 0.3rem;">Queue Status</h3>
                                <p class="text-muted mb-0">Real-time queue information and position tracking</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Auto-refresh Notice -->
                <div class="alert alert-info" style="border-radius: 15px; background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%); border: none; margin-bottom: 1.5rem;">
                    <i class="fas fa-sync-alt fa-spin me-2"></i>This page auto-refreshes every 10 seconds
                </div>

                <!-- Queue Status Content -->
                <div id="queueContent">
                    <!-- Loading state -->
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                        <p class="mt-3">Loading your queue status...</p>
                    </div>
                </div>

                <!-- Refresh Button -->
                <button class="btn btn-primary refresh-btn" onclick="loadQueueStatus()" title="Refresh" style="background: var(--kawit-gradient); border: none;">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        let refreshInterval;
        
        const serviceLabels = {
            'consultation': 'Consultation',
            'laboratory': 'Laboratory',
            'pharmacy': 'Pharmacy',
            'certificate': 'Medical Certificate'
        };
        
        const priorityLabels = {
            'urgent': 'URGENT - Emergency',
            'senior_pwd': 'Priority: Senior Citizen / PWD',
            'pregnant': 'Priority: Pregnant',
            'regular': 'Regular Queue'
        };
        
        const statusLabels = {
            'waiting': 'Waiting',
            'serving': 'Being Served Now!'
        };
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadQueueStatus();
            
            // Auto-refresh every 10 seconds
            refreshInterval = setInterval(() => {
                loadQueueStatus();
            }, 10000);
        });
        
        function loadQueueStatus() {
            const formData = new FormData();
            formData.append('action', 'get_my_queue');
            
            fetch('Queue_Status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayQueueStatus(data);
                }
            })
            .catch(error => {
                console.error('Error loading queue status:', error);
            });
        }
        
        function displayQueueStatus(data) {
            const content = document.getElementById('queueContent');
            
            if (!data.has_queue) {
                // No active queue - use content-section style
                content.innerHTML = `
                    <div class="content-section" style="padding: 3rem;">
                        <div class="empty-state">
                            <i class="fas fa-ticket-alt"></i>
                            <h4>You're not in any queue</h4>
                            <p class="text-muted">Visit the RHU to check-in and get a queue number</p>
                            <a href="Dashboard.php" class="btn btn-primary mt-3" style="background: var(--kawit-gradient); border: none;">
                                <i class="fas fa-home me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                `;
                return;
            }
            
            const queue = data.queue;
            const priorityClass = 'priority-' + (queue.priority === 'senior_pwd' ? 'senior' : queue.priority);
            const statusClass = queue.status === 'serving' ? 'success' : 'warning';
            
            // Notification for your turn
            let notification = '';
            if (queue.status === 'serving') {
                notification = `
                    <div class="alert alert-danger" style="border-radius: 15px; border: none; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; margin-bottom: 1.5rem; animation: shake 0.5s;">
                        <h5><i class="fas fa-bell me-2"></i>IT'S YOUR TURN!</h5>
                        <p class="mb-0">Please proceed to the service counter now.</p>
                    </div>
                `;
            } else if (data.people_ahead <= 3 && data.people_ahead > 0) {
                notification = `
                    <div class="alert alert-warning" style="border-radius: 15px; border: none; background: #fff3cd; margin-bottom: 1.5rem;">
                        <h6><i class="fas fa-info-circle me-2"></i>Your turn is coming soon!</h6>
                        <p class="mb-0">Please stay nearby and watch the display.</p>
                    </div>
                `;
            }
            
            content.innerHTML = `
                ${notification}
                
                <!-- My Queue Number Card -->
                <div class="content-section" style="padding: 0; margin-bottom: 1.5rem; overflow: hidden;">
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; text-align: center;">
                        <div style="font-size: 0.9rem; margin-bottom: 10px; opacity: 0.9;">Your Queue Number</div>
                        <div class="my-queue-number">${queue.queue_number}</div>
                        <div style="margin-top: 15px;">
                            <span class="badge bg-${statusClass}" style="font-size: 1rem; padding: 10px 25px;">
                                <i class="fas fa-${queue.status === 'serving' ? 'user-clock' : 'clock'} me-2"></i>
                                ${statusLabels[queue.status]}
                            </span>
                        </div>
                        <div class="priority-indicator ${priorityClass}">
                            ${priorityLabels[queue.priority]}
                        </div>
                    </div>
                    <div class="row g-2 p-3">
                        <div class="col-6">
                            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center;">
                                <i class="fas fa-stethoscope" style="font-size: 2rem; color: #667eea; margin-bottom: 10px;"></i>
                                <div style="font-size: 0.85rem; color: #6c757d;">Service Type</div>
                                <div style="font-weight: bold; font-size: 1.1rem;">${serviceLabels[queue.service_type]}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center;">
                                <i class="fas fa-clock" style="font-size: 2rem; color: #667eea; margin-bottom: 10px;"></i>
                                <div style="font-size: 0.85rem; color: #6c757d;">Check-in Time</div>
                                <div style="font-weight: bold; font-size: 1.1rem;">${new Date(queue.check_in_time).toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'})}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center;">
                                <i class="fas fa-hourglass-half" style="font-size: 2rem; color: #667eea; margin-bottom: 10px;"></i>
                                <div style="font-size: 0.85rem; color: #6c757d;">Waiting Time</div>
                                <div style="font-weight: bold; font-size: 1.1rem;">${queue.wait_time_minutes} min</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center;">
                                <i class="fas fa-list-ol" style="font-size: 2rem; color: #667eea; margin-bottom: 10px;"></i>
                                <div style="font-size: 0.85rem; color: #6c757d;">Your Position</div>
                                <div style="font-weight: bold; font-size: 1.1rem;">#${data.position}</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                ${queue.status === 'waiting' ? `
                    <!-- People Ahead -->
                    <div class="content-section" style="padding: 2rem; margin-bottom: 1.5rem; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; text-align: center;">
                        <h5><i class="fas fa-users me-2"></i>People Ahead of You</h5>
                        <div style="font-size: 3.5rem; font-weight: bold; margin: 15px 0;">${data.people_ahead}</div>
                        <p class="mb-0">
                            ${data.people_ahead === 0 ? "You're next!" : 
                              data.people_ahead === 1 ? "Almost your turn!" :
                              data.people_ahead <= 3 ? "Your turn is near!" : "Please wait patiently"}
                        </p>
                    </div>
                    
                    ${data.current_serving ? `
                        <!-- Currently Serving -->
                        <div class="content-section" style="padding: 2rem; margin-bottom: 1.5rem; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; text-align: center;">
                            <h5><i class="fas fa-bell me-2"></i>Now Serving</h5>
                            <div style="font-size: 2.5rem; font-weight: bold; font-family: 'Courier New', monospace; margin: 15px 0;">${data.current_serving}</div>
                            <p class="mb-0">Current queue number being served</p>
                        </div>
                    ` : ''}
                ` : ''}
                
                <!-- Instructions -->
                <div class="content-section" style="padding: 1.5rem;">
                    <h6><i class="fas fa-info-circle me-2"></i>Important Reminders</h6>
                    <ul class="mb-0">
                        <li>Please stay in the waiting area</li>
                        <li>Watch the display screen for your queue number</li>
                        <li>When called, please proceed immediately to the service counter</li>
                        <li>This page auto-refreshes every 10 seconds</li>
                        ${queue.priority === 'urgent' ? '<li class="text-danger"><strong>Your case is marked as URGENT - you will be called soon</strong></li>' : ''}
                    </ul>
                </div>
            `;
        }
        
        // Cleanup
        window.addEventListener('beforeunload', function() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        });
        
        // Check session on page load
        window.onload = function() {
            <?php if (!isset($_SESSION['user_id'])): ?>
                window.location.href = '../login.php';
            <?php endif; ?>
        };
    </script>
</body>
</html>