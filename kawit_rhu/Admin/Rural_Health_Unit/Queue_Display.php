<?php
session_start();

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

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'get_display_data') {
        $today = date('Y-m-d');
        
        // Get currently serving
        $servingStmt = $pdo->prepare("
            SELECT q.*, p.patient_id, CONCAT(p.first_name, ' ', p.last_name) as patient_name
            FROM queue q
            JOIN patients p ON q.patient_id = p.id
            WHERE q.queue_date = ? AND q.status = 'serving'
            ORDER BY q.called_time DESC
        ");
        $servingStmt->execute([$today]);
        $serving = $servingStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get next 5 waiting
        $waitingStmt = $pdo->prepare("
            SELECT q.queue_number, q.service_type, q.priority
            FROM queue q
            WHERE q.queue_date = ? AND q.status = 'waiting'
            ORDER BY 
                CASE q.priority
                    WHEN 'urgent' THEN 1
                    WHEN 'senior_pwd' THEN 2
                    WHEN 'pregnant' THEN 3
                    WHEN 'regular' THEN 4
                END,
                q.check_in_time ASC
            LIMIT 5
        ");
        $waitingStmt->execute([$today]);
        $waiting = $waitingStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get statistics
        $statsStmt = $pdo->prepare("
            SELECT 
                COUNT(CASE WHEN status = 'waiting' THEN 1 END) as total_waiting,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as total_served
            FROM queue
            WHERE queue_date = ?
        ");
        $statsStmt->execute([$today]);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'serving' => $serving,
            'waiting' => $waiting,
            'stats' => $stats,
            'timestamp' => date('h:i:s A')
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Display - Kawit RHU</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #FFA6BE 0%, #FF7A9A 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
            overflow: hidden;
            height: 100vh;
        }
        
        .display-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 30px;
        }
        
        /* Header */
        .header {
            text-align: center;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .header .subtitle {
            font-size: 1.5rem;
            opacity: 0.9;
        }
        
        /* Now Serving Section */
        .now-serving {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 50px;
            margin-bottom: 30px;
        }
        
        .now-serving-label {
            font-size: 3rem;
            font-weight: 300;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 5px;
        }
        
        .queue-number-display {
            font-size: 12rem;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            text-shadow: 4px 4px 8px rgba(0,0,0,0.4);
            line-height: 1;
        }
        
        .service-type-display {
            font-size: 2.5rem;
            margin-top: 20px;
            padding: 15px 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50px;
            text-transform: uppercase;
        }
        
        .counter-number {
            font-size: 2rem;
            margin-top: 15px;
            opacity: 0.8;
        }
        
        /* Multiple Serving Display */
        .multiple-serving {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            width: 100%;
        }
        
        .serving-item {
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
        }
        
        .serving-item .queue-number {
            font-size: 5rem;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }
        
        .serving-item .service {
            font-size: 1.5rem;
            margin-top: 10px;
        }
        
        /* Waiting List */
        .waiting-section {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
        }
        
        .waiting-header {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .waiting-list {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .waiting-item {
            background: rgba(255,255,255,0.15);
            padding: 20px 30px;
            border-radius: 15px;
            min-width: 150px;
            text-align: center;
        }
        
        .waiting-number {
            font-size: 2.5rem;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }
        
        .waiting-service {
            font-size: 1rem;
            margin-top: 5px;
            opacity: 0.9;
        }
        
        /* Footer Stats */
        .footer-stats {
            display: flex;
            justify-content: space-around;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            margin-top: 20px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
        }
        
        .stat-label {
            font-size: 1.2rem;
            opacity: 0.8;
            text-transform: uppercase;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 100px;
        }
        
        .empty-state i {
            font-size: 8rem;
            opacity: 0.5;
            margin-bottom: 30px;
        }
        
        .empty-state h2 {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        .empty-state p {
            font-size: 1.5rem;
            opacity: 0.8;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .refresh-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(40, 167, 69, 0.95); /* More solid */
            padding: 8px 15px; /* Smaller padding */
            border-radius: 50px;
            font-size: 0.9rem; /* Slightly smaller text */
            animation: fadeIn 0.3s;
            backdrop-filter: blur(5px); /* Less blur */
            box-shadow: 0 2px 10px rgba(0,0,0,0.2); /* Cleaner shadow */
            z-index: 9999; /* Ensure it's on top */
        }
        
        /* Time Display - HIDDEN */
        .time-display {
            display: none; /* Completely hidden */
        }
        
        /* Priority Indicator */
        .priority-urgent {
            border: 3px solid #ff4444;
            box-shadow: 0 0 20px rgba(255, 68, 68, 0.5);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header h1 { font-size: 2rem; }
            .queue-number-display { font-size: 6rem; }
            .now-serving-label { font-size: 1.5rem; }
            .service-type-display { font-size: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="display-container">
        <!-- Time Display -->
        <div class="time-display" id="timeDisplay"></div>
        
        <!-- Refresh Indicator -->
        <div class="refresh-indicator" id="refreshIndicator" style="display: none;">
            <i class="fas fa-sync-alt fa-spin me-2"></i>Updating...
        </div>
        
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-hospital me-3"></i>KAWIT RHU</h1>
            <div class="subtitle">Queue Display</div>
        </div>
        
        <!-- Main Display Area -->
        <div id="mainDisplay">
            <!-- Content will be loaded here -->
        </div>
        
        <!-- Waiting List -->
        <div class="waiting-section" id="waitingSection">
            <div class="waiting-header">
                <i class="fas fa-clock me-2"></i>Next in Queue
            </div>
            <div class="waiting-list" id="waitingList">
                <!-- Waiting items will be loaded here -->
            </div>
        </div>
        
        <!-- Footer Stats -->
        <div class="footer-stats">
            <div class="stat-item">
                <div class="stat-number" id="totalWaiting">0</div>
                <div class="stat-label">Waiting</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="totalServed">0</div>
                <div class="stat-label">Served Today</div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        let refreshInterval;
        
        // Service type icons and labels
        const serviceIcons = {
            'consultation': 'fa-stethoscope',
            'laboratory': 'fa-flask',
            'pharmacy': 'fa-pills',
            'certificate': 'fa-certificate'
        };
        
        const serviceLabels = {
            'consultation': 'Consultation',
            'laboratory': 'Laboratory',
            'pharmacy': 'Pharmacy',
            'certificate': 'Medical Certificate'
        };
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateDisplay();
            updateTime();
            
            // Auto-refresh every 5 seconds
            refreshInterval = setInterval(() => {
                updateDisplay();
            }, 3000);
            
            // Update time every second
            setInterval(updateTime, 1000);
        });
        
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit',
                hour12: true 
            });
            const dateString = now.toLocaleDateString('en-US', { 
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            document.getElementById('timeDisplay').innerHTML = `
                <i class="fas fa-clock me-2"></i>${timeString}<br>
                <small style="font-size: 0.8rem;">${dateString}</small>
            `;
        }
        
        function updateDisplay() {
            // Show refresh indicator (if exists)
            const indicator = document.getElementById('refreshIndicator');
            if (indicator) {
                indicator.style.display = 'block';
            }
            
            const formData = new FormData();
            formData.append('action', 'get_display_data');
            
            fetch('Queue_Display.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayServing(data.serving);
                    displayWaiting(data.waiting);
                    displayStats(data.stats);
                }
                
                // Hide refresh indicator faster
                setTimeout(() => {
                    indicator.style.opacity = '0';
                    indicator.style.transition = 'opacity 0.3s';
                    setTimeout(() => {
                        indicator.style.display = 'none';
                        indicator.style.opacity = '1'; // Reset for next time
                    }, 300);
                }, 200); // Show for only 200ms
            })
            .catch(error => {
                console.error('Error updating display:', error);
                indicator.style.display = 'none';
            });
        }
        
        function displayServing(serving) {
            const mainDisplay = document.getElementById('mainDisplay');
            
            if (serving.length === 0) {
                // Empty state
                mainDisplay.innerHTML = `
                    <div class="now-serving">
                        <div class="empty-state">
                            <i class="fas fa-user-clock"></i>
                            <h2>No Patient Being Served</h2>
                            <p>Waiting for next patient...</p>
                        </div>
                    </div>
                `;
            } else if (serving.length === 1) {
                // Single patient display (large)
                const patient = serving[0];
                const serviceIcon = serviceIcons[patient.service_type] || 'fa-stethoscope';
                const serviceLabel = serviceLabels[patient.service_type] || patient.service_type;
                const priorityClass = patient.priority === 'urgent' ? 'priority-urgent' : '';
                
                mainDisplay.innerHTML = `
                    <div class="now-serving ${priorityClass}">
                        <div class="now-serving-label">
                            <i class="fas fa-bell me-3"></i>Now Serving
                        </div>
                        <div class="queue-number-display">${patient.queue_number}</div>
                        <div class="service-type-display">
                            <i class="fas ${serviceIcon} me-2"></i>${serviceLabel}
                        </div>
                        ${patient.priority === 'urgent' ? '<div class="counter-number" style="color: #ff4444; font-weight: bold;">⚠️ URGENT</div>' : ''}
                    </div>
                `;
            } else {
                // Multiple patients display
                let html = '<div class="now-serving"><div class="now-serving-label"><i class="fas fa-bell me-3"></i>Now Serving</div><div class="multiple-serving">';
                serving.slice(0, 4).forEach(patient => {
                    const serviceLabel = serviceLabels[patient.service_type] || patient.service_type;
                    html += `
                        <div class="serving-item">
                            <div class="queue-number">${patient.queue_number}</div>
                            <div class="service">${serviceLabel}</div>
                        </div>
                    `;
                });
                html += '</div></div>';
                mainDisplay.innerHTML = html;
            }
        }
        
        function displayWaiting(waiting) {
            const waitingList = document.getElementById('waitingList');
            const waitingSection = document.getElementById('waitingSection');
            
            if (waiting.length === 0) {
                waitingSection.style.display = 'none';
                return;
            }
            
            waitingSection.style.display = 'block';
            
            let html = '';
            waiting.forEach(item => {
                const serviceLabel = serviceLabels[item.service_type] || item.service_type;
                const priorityClass = item.priority === 'urgent' ? 'priority-urgent' : '';
                
                html += `
                    <div class="waiting-item ${priorityClass}">
                        <div class="waiting-number">${item.queue_number}</div>
                        <div class="waiting-service">${serviceLabel}</div>
                    </div>
                `;
            });
            
            waitingList.innerHTML = html;
        }
        
        function displayStats(stats) {
            document.getElementById('totalWaiting').textContent = stats.total_waiting || 0;
            document.getElementById('totalServed').textContent = stats.total_served || 0;
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