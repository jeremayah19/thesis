<?php
session_start();

header('Content-Type: application/json');

// Check if user is logged in and is a patient
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
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
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
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
    echo json_encode(['success' => false, 'message' => 'Patient not found']);
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

if (!$currentQueue) {
    echo json_encode([
        'success' => true,
        'has_queue' => false
    ]);
    exit;
}

// Get position
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

echo json_encode([
    'success' => true,
    'has_queue' => true,
    'queue' => $currentQueue,
    'position' => $peopleAhead + 1,
    'people_ahead' => $peopleAhead
]);
?>