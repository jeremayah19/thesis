<?php
// Queue Diagnostic Tool
// Place this file in: Admin/Rural_Health_Unit/queue_diagnostic.php
// Access via: http://localhost/yourproject/Admin/Rural_Health_Unit/queue_diagnostic.php

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

$today = date('Y-m-d');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Diagnostic Tool</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background: #f8f9fa; }
        .diagnostic-card { background: white; border-radius: 10px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .status-ok { color: #28a745; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">🔍 Queue System Diagnostic Tool</h1>
        
        <!-- Database Connection -->
        <div class="diagnostic-card">
            <h3>✅ Database Connection</h3>
            <p class="status-ok">Connected successfully to: <?php echo $dbname; ?></p>
        </div>

        <!-- Today's Date Check -->
        <div class="diagnostic-card">
            <h3>📅 Date Check</h3>
            <p><strong>PHP Date:</strong> <?php echo $today; ?></p>
            <p><strong>MySQL Date:</strong> 
                <?php 
                $mysqlDate = $pdo->query("SELECT CURDATE()")->fetchColumn();
                echo $mysqlDate;
                if ($today === $mysqlDate) {
                    echo ' <span class="status-ok">✓ MATCH</span>';
                } else {
                    echo ' <span class="status-error">✗ MISMATCH - This could be your problem!</span>';
                }
                ?>
            </p>
        </div>

        <!-- Total Queue Entries Today -->
        <div class="diagnostic-card">
            <h3>📊 Queue Entries for Today (<?php echo $today; ?>)</h3>
            <?php
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM queue WHERE queue_date = ?");
            $stmt->execute([$today]);
            $totalCount = $stmt->fetchColumn();
            ?>
            <p><strong>Total entries:</strong> <?php echo $totalCount; ?></p>
            
            <?php if ($totalCount === 0): ?>
                <p class="status-error">⚠️ NO QUEUE ENTRIES FOUND FOR TODAY!</p>
                <p>Possible reasons:</p>
                <ul>
                    <li>No patients have been added to queue today</li>
                    <li>Date mismatch between systems</li>
                    <li>Queue_Checkin.php is not saving to database</li>
                </ul>
            <?php else: ?>
                <p class="status-ok">✓ Queue entries exist for today</p>
            <?php endif; ?>
        </div>

        <!-- Queue Breakdown by Status -->
        <div class="diagnostic-card">
            <h3>📋 Queue Status Breakdown</h3>
            <?php
            $stmt = $pdo->prepare("
                SELECT 
                    status,
                    COUNT(*) as count,
                    GROUP_CONCAT(queue_number SEPARATOR ', ') as queue_numbers
                FROM queue 
                WHERE queue_date = ?
                GROUP BY status
            ");
            $stmt->execute([$today]);
            $statusBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($statusBreakdown)):
            ?>
                <p class="status-warning">No queue entries found for today</p>
            <?php else: ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                            <th>Queue Numbers</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statusBreakdown as $row): ?>
                        <tr>
                            <td><strong><?php echo $row['status']; ?></strong></td>
                            <td><?php echo $row['count']; ?></td>
                            <td><code><?php echo $row['queue_numbers']; ?></code></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Queue Breakdown by Service Type -->
        <div class="diagnostic-card">
            <h3>🏥 Queue Service Type Breakdown</h3>
            <?php
            $stmt = $pdo->prepare("
                SELECT 
                    service_type,
                    COUNT(*) as count,
                    GROUP_CONCAT(queue_number SEPARATOR ', ') as queue_numbers
                FROM queue 
                WHERE queue_date = ?
                GROUP BY service_type
            ");
            $stmt->execute([$today]);
            $serviceBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($serviceBreakdown)):
            ?>
                <p class="status-warning">No queue entries found for today</p>
            <?php else: ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Service Type</th>
                            <th>Count</th>
                            <th>Queue Numbers</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($serviceBreakdown as $row): ?>
                        <tr>
                            <td><strong><?php echo $row['service_type']; ?></strong></td>
                            <td><?php echo $row['count']; ?></td>
                            <td><code><?php echo $row['queue_numbers']; ?></code></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Detailed Queue Entries -->
        <div class="diagnostic-card">
            <h3>📝 Detailed Queue Entries for Today</h3>
            <?php
            $stmt = $pdo->prepare("
                SELECT 
                    q.*,
                    CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                    p.patient_id as patient_number
                FROM queue q
                LEFT JOIN patients p ON q.patient_id = p.id
                WHERE q.queue_date = ?
                ORDER BY q.check_in_time DESC
                LIMIT 20
            ");
            $stmt->execute([$today]);
            $allQueue = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($allQueue)):
            ?>
                <div class="alert alert-warning">
                    <h5>⚠️ NO QUEUE ENTRIES FOUND!</h5>
                    <p><strong>This is the problem!</strong> Queue_Checkin.php is not successfully adding patients to the database.</p>
                    
                    <h6>Debug Steps:</h6>
                    <ol>
                        <li>Open Queue_Checkin.php in your browser</li>
                        <li>Open Browser Console (F12 → Console tab)</li>
                        <li>Try to add a patient</li>
                        <li>Check console for errors</li>
                        <li>Look for JSON response in Network tab</li>
                    </ol>
                </div>
            <?php else: ?>
                <div class="alert alert-success">
                    <strong>✓ Found <?php echo count($allQueue); ?> queue entries!</strong>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Queue #</th>
                                <th>Patient</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Check-in</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allQueue as $q): ?>
                            <tr>
                                <td><?php echo $q['id']; ?></td>
                                <td><strong><?php echo $q['queue_number']; ?></strong></td>
                                <td>
                                    <?php echo $q['patient_name']; ?><br>
                                    <small class="text-muted"><?php echo $q['patient_number']; ?></small>
                                </td>
                                <td><?php echo $q['service_type']; ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $q['status'] === 'waiting' ? 'warning' : 
                                             ($q['status'] === 'serving' ? 'primary' : 
                                             ($q['status'] === 'completed' ? 'success' : 'secondary')); 
                                    ?>">
                                        <?php echo $q['status']; ?>
                                    </span>
                                </td>
                                <td><?php echo $q['priority']; ?></td>
                                <td><?php echo date('h:i A', strtotime($q['check_in_time'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- What Queue_Dashboard.php is Looking For -->
        <div class="diagnostic-card">
            <h3>🔍 What Queue_Dashboard.php is Searching For</h3>
            <p>Queue_Dashboard.php is configured to show:</p>
            <ul>
                <li><strong>Service Type:</strong> <code>consultation</code></li>
                <li><strong>Status:</strong> <code>waiting</code></li>
                <li><strong>Date:</strong> <code><?php echo $today; ?></code></li>
                <li><strong>Location:</strong> <code>RHU</code></li>
            </ul>
            
            <?php
            // Check if there are any entries matching Queue_Dashboard filters
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count
                FROM queue q
                WHERE q.queue_date = ?
                AND q.service_type = 'consultation'
                AND q.status = 'waiting'
            ");
            $stmt->execute([$today]);
            $matchingCount = $stmt->fetchColumn();
            ?>
            
            <div class="alert alert-<?php echo $matchingCount > 0 ? 'success' : 'danger'; ?>">
                <?php if ($matchingCount > 0): ?>
                    <strong>✓ Found <?php echo $matchingCount; ?> entries matching Queue_Dashboard filters!</strong>
                    <p>The entries exist. If Queue_Dashboard.php is not showing them, the issue is with the JavaScript/AJAX loading.</p>
                <?php else: ?>
                    <strong>✗ No entries found matching Queue_Dashboard filters!</strong>
                    <p>Either:</p>
                    <ul>
                        <li>No patients have been added today with service_type='consultation' and status='waiting'</li>
                        <li>All patients have been moved to 'serving' or 'completed' status</li>
                        <li>Queue_Checkin.php is using different values</li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="diagnostic-card">
            <h3>🛠️ Quick Actions</h3>
            <div class="d-flex gap-2">
                <a href="Queue_Dashboard.php" class="btn btn-primary">
                    <i class="fas fa-list"></i> Go to Queue Dashboard
                </a>
                <a href="Queue_Checkin.php" class="btn btn-success">
                    <i class="fas fa-user-plus"></i> Go to Queue Check-in
                </a>
                <button onclick="location.reload()" class="btn btn-info">
                    <i class="fas fa-sync"></i> Refresh Diagnostic
                </button>
            </div>
        </div>
    </div>
</body>
</html>