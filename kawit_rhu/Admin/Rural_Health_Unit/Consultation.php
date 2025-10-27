<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    
    if ($_POST['action'] === 'create_consultation') {
        try {
            // Validate required fields
            if (empty($_POST['patient_id']) || empty($_POST['chief_complaint']) || empty($_POST['diagnosis'])) {
                throw new Exception("Please fill in all required fields.");
            }
            
            $pdo->beginTransaction();
            
            // Generate consultation number
            $year = date('Y');
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM consultations WHERE consultation_number LIKE ?");
            $stmt->execute(["CONS-$year-%"]);
            $count = $stmt->fetchColumn();
            $consultation_number = 'CONS-' . $year . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            
            // Prepare vital signs JSON
            $vital_signs = json_encode([
                'temperature' => $_POST['temperature'] ?? '',
                'blood_pressure' => $_POST['blood_pressure'] ?? '',
                'pulse_rate' => $_POST['pulse_rate'] ?? '',
                'respiratory_rate' => $_POST['respiratory_rate'] ?? '',
                'weight' => $_POST['weight'] ?? '',
                'height' => $_POST['height'] ?? '',
                'bmi' => $_POST['bmi'] ?? '',
                'oxygen_saturation' => $_POST['oxygen_saturation'] ?? ''
            ]);
            
            // Get Google Meet link
            $googleMeetLink = !empty($_POST['google_meet_link']) ? trim($_POST['google_meet_link']) : null;
            
            // Insert consultation with ALL FIELDS from Individual Treatment Record
            $stmt = $pdo->prepare("
                INSERT INTO consultations (
                consultation_number, patient_id, consultation_type,
                consultation_date, philhealth_no, osca_number, family_number,
                pwd_status, nhts_4ps_status, house_number, street, municipality,
                contact_number, occupation, educational_attainment, guardian_name,
                blood_type, chief_complaint, medication_taken, temperature, 
                blood_pressure_systolic, blood_pressure_diastolic, heart_rate, 
                respiratory_rate, oxygen_saturation, weight, height, bmi,
                current_smoker, alcohol_intake, illicit_drug_use, eat_unhealthy_foods,
                regular_exercise, with_hypertension, hypertension_meds_from_rhu,
                with_diabetes, diabetes_meds_from_rhu, history_of_present_illness,
                symptoms, vital_signs, physical_examination, assessment, diagnosis,
                treatment_plan, medications_prescribed, prescribed_by_name, 
                prescribed_by_signature, recommendations, follow_up_instructions, 
                priority, status, assigned_doctor, consultation_location, barangay_id, 
                google_meet_link, follow_up_date
            ) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed', ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $consultation_number,
                $_POST['patient_id'],
                $_POST['consultation_type'] ?? 'walk-in',
                $_POST['philhealth_no'] ?? '',
                $_POST['osca_number'] ?? '',
                $_POST['family_number'] ?? '',
                $_POST['pwd_status'] ?? 'NO',
                $_POST['nhts_4ps_status'] ?? 'NO',
                $_POST['house_number'] ?? '',
                $_POST['street'] ?? '',
                $_POST['municipality'] ?? 'Kawit',
                $_POST['contact_number'] ?? '',
                $_POST['occupation'] ?? '',
                $_POST['educational_attainment'] ?? '',
                $_POST['guardian_name'] ?? '',
                $_POST['blood_type'] ?? '',
                $_POST['chief_complaint'],
                $_POST['medication_taken'] ?? '',
                $_POST['temperature'] ?? null,
                $_POST['blood_pressure_systolic'] ?? null,
                $_POST['blood_pressure_diastolic'] ?? null,
                $_POST['heart_rate'] ?? null,
                $_POST['respiratory_rate'] ?? null,
                $_POST['oxygen_saturation'] ?? null,
                $_POST['weight'] ?? null,
                $_POST['height'] ?? null,
                $_POST['bmi'] ?? null,
                $_POST['current_smoker'] ?? 'NO',
                $_POST['alcohol_intake'] ?? 'NO',
                $_POST['illicit_drug_use'] ?? 'NO',
                $_POST['eat_unhealthy_foods'] ?? 'NO',
                $_POST['regular_exercise'] ?? 'NO',
                $_POST['with_hypertension'] ?? 'NO',
                $_POST['hypertension_meds_from_rhu'] ?? 'NO',
                $_POST['with_diabetes'] ?? 'NO',
                $_POST['diabetes_meds_from_rhu'] ?? 'NO',
                $_POST['history_of_present_illness'] ?? '',
                $_POST['symptoms'] ?? '',
                $vital_signs,
                $_POST['physical_examination'] ?? '',
                $_POST['assessment'] ?? '',
                $_POST['diagnosis'],
                $_POST['treatment_plan'] ?? '',
                $_POST['medications_prescribed'] ?? '',
                $_POST['prescribed_by_name'] ?? $staff['first_name'] . ' ' . $staff['last_name'],
                $_POST['prescribed_by_signature'] ?? '',
                $_POST['recommendations'] ?? '',
                $_POST['follow_up_instructions'] ?? '',
                $_POST['priority'] ?? 'medium',
                $staff['id'],
                $_POST['consultation_location'] ?? 'RHU',
                !empty($_POST['barangay_id']) ? $_POST['barangay_id'] : null,
                $googleMeetLink,
                !empty($_POST['follow_up_date']) ? $_POST['follow_up_date'] : null
            ]);
            
            $consultation_id = $pdo->lastInsertId();
            
            // Create prescriptions if provided
            $prescriptionWarnings = [];
            if (!empty($_POST['prescriptions'])) {
                // Get patient_id from the newly created consultation to ensure it's valid
                $getPatientId = $pdo->prepare("SELECT patient_id FROM consultations WHERE id = ?");
                $getPatientId->execute([$consultation_id]);
                $consultationData = $getPatientId->fetch(PDO::FETCH_ASSOC);
                $validPatientId = $consultationData['patient_id'];
                
                $prescriptions = json_decode($_POST['prescriptions'], true);
                if (is_array($prescriptions)) {
                    foreach ($prescriptions as $prescription) {
                        if (empty($prescription['medication_name']) || empty($prescription['quantity'])) {
                            continue;
                        }
                        
                        // Check medicine inventory
                        $checkMed = $pdo->prepare("
                            SELECT id, stock_quantity, medicine_name 
                            FROM medicines 
                            WHERE (medicine_name LIKE ? OR generic_name LIKE ?) 
                            AND is_active = 1
                            LIMIT 1
                        ");
                        $medSearch = '%' . $prescription['medication_name'] . '%';
                        $checkMed->execute([$medSearch, $medSearch]);
                        $medicine = $checkMed->fetch();
                        
                        if (!$medicine) {
                            $prescriptionWarnings[] = "{$prescription['medication_name']} not found in inventory";
                        } else {
                            if ($medicine['stock_quantity'] < $prescription['quantity']) {
                                $prescriptionWarnings[] = "{$prescription['medication_name']} - Insufficient stock (Available: {$medicine['stock_quantity']}, Requested: {$prescription['quantity']})";
                            }
                        }
                        
                        $rx_number = 'RX-' . $year . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                        
                        $rxStmt = $pdo->prepare("
                            INSERT INTO prescriptions (
                                prescription_number, patient_id, consultation_id, medication_name,
                                generic_name, dosage_strength, dosage_form, quantity_prescribed,
                                dosage_instructions, frequency, duration, special_instructions,
                                prescribed_by, prescription_date, status
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'pending')
                        ");
                        
                        $rxStmt->execute([
                            $rx_number,
                            $validPatientId,
                            $consultation_id,
                            $prescription['medication_name'],
                            $prescription['generic_name'] ?? '',
                            $prescription['dosage_strength'] ?? '',
                            $prescription['dosage_form'] ?? 'Tablet',
                            $prescription['quantity'],
                            $prescription['instructions'] ?? '',
                            $prescription['frequency'] ?? '',
                            $prescription['duration'] ?? '',
                            $prescription['special_instructions'] ?? '',
                            $staff['id']
                        ]);
                    }
                }
            }
            
            // Log the consultation
            $logStmt = $pdo->prepare("
                INSERT INTO system_logs (user_id, action, module, record_id, new_values) 
                VALUES (?, 'CONSULTATION_COMPLETED', 'Consultations', ?, ?)
            ");
            $logStmt->execute([
                $_SESSION['user_id'],
                $consultation_id,
                json_encode([
                    'consultation_number' => $consultation_number, 
                    'doctor' => $staff['first_name'] . ' ' . $staff['last_name'],
                    'patient_id' => $_POST['patient_id']
                ])
            ]);
            
            // Create notification for patient
            $notifStmt = $pdo->prepare("
                INSERT INTO notifications (user_id, type, title, message, data, priority)
                SELECT user_id, 'system', 'Consultation Completed', ?, ?, ?
                FROM patients WHERE id = ?
            ");
            $notifStmt->execute([
                'Your consultation has been completed. Consultation Number: ' . $consultation_number,
                json_encode(['consultation_id' => $consultation_id, 'consultation_number' => $consultation_number]),
                $_POST['priority'] ?? 'medium',
                $_POST['patient_id']
            ]);
            
            // If Google Meet link provided, send notification
            if ($googleMeetLink) {
                $getPatient = $pdo->prepare("SELECT user_id FROM patients WHERE id = ?");
                $getPatient->execute([$_POST['patient_id']]);
                $patient = $getPatient->fetch();
                
                if ($patient && $patient['user_id']) {
                    $meetNotif = $pdo->prepare("
                        INSERT INTO notifications (user_id, type, title, message, data, priority)
                        VALUES (?, 'consultation_ready', 'Online Consultation Ready', ?, ?, 'high')
                    ");
                    $meetNotif->execute([
                        $patient['user_id'],
                        'Your online consultation is ready! Click to join the video call. Consultation #: ' . $consultation_number,
                        json_encode([
                            'consultation_id' => $consultation_id,
                            'consultation_number' => $consultation_number,
                            'google_meet_link' => $googleMeetLink
                        ])
                    ]);
                }
            }
            
            $pdo->commit();
            
            $response = [
                'success' => true, 
                'message' => 'Consultation recorded successfully!', 
                'consultation_number' => $consultation_number
            ];
            
            if (!empty($prescriptionWarnings)) {
                $response['warnings'] = $prescriptionWarnings;
                $response['message'] .= ' Note: ' . implode(', ', $prescriptionWarnings);
            }
            
            echo json_encode($response);
            
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollback();
            }
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    // ACCEPT CONSULTATION AND SCHEDULE TIME
    if ($_POST['action'] === 'accept_consultation') {
        try {
            $consultation_id = intval($_POST['consultation_id']);
            $scheduled_date = $_POST['scheduled_date'];
            $scheduled_time = $_POST['scheduled_time'];
            
            // Combine date and time
            $consultation_datetime = $scheduled_date . ' ' . $scheduled_time;
            
            // Check if the time slot is already taken
            $checkStmt = $pdo->prepare("
                SELECT id, consultation_number FROM consultations 
                WHERE DATE(consultation_date) = ? 
                AND TIME(consultation_date) = ? 
                AND status IN ('pending', 'in_progress')
                AND id != ?
            ");
            $checkStmt->execute([$scheduled_date, $scheduled_time, $consultation_id]);
            $existing = $checkStmt->fetch();
            
            if ($existing) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'This time slot is already taken by consultation ' . $existing['consultation_number'] . '. Please choose another time.'
                ]);
                exit;
            }
            
            // Get Google Meet link if provided
            $googleMeetLink = !empty($_POST['google_meet_link']) ? trim($_POST['google_meet_link']) : null;

            // Get priority (default to medium if not provided)
            $priority = !empty($_POST['priority']) ? $_POST['priority'] : 'medium';

            // Update consultation to in_progress with scheduled time and priority
            $stmt = $pdo->prepare("
                UPDATE consultations SET
                    status = 'in_progress',
                    consultation_date = ?,
                    assigned_doctor = ?,
                    priority = ?,
                    google_meet_link = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([$consultation_datetime, $staff['id'], $priority, $googleMeetLink, $consultation_id]);
            
            // Get patient info for notification
            $patientStmt = $pdo->prepare("
                SELECT p.user_id, c.consultation_number 
                FROM consultations c
                JOIN patients p ON c.patient_id = p.id
                WHERE c.id = ?
            ");
            $patientStmt->execute([$consultation_id]);
            $consult = $patientStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($consult && $consult['user_id']) {
                // Send scheduling notification
                $notifStmt = $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, data, priority)
                    VALUES (?, 'consultation_ready', 'Consultation Scheduled', ?, ?, 'high')
                ");
                $notifStmt->execute([
                    $consult['user_id'],
                    'Your online consultation has been scheduled for ' . date('F j, Y @ g:i A', strtotime($consultation_datetime)) . '. Please be online 5 minutes early. Consultation #: ' . $consult['consultation_number'],
                    json_encode([
                        'consultation_id' => $consultation_id,
                        'consultation_number' => $consult['consultation_number'],
                        'scheduled_time' => $consultation_datetime,
                        'google_meet_link' => $googleMeetLink
                    ])
                ]);
                
                // If Google Meet link was provided, send additional notification
                if ($googleMeetLink) {
                    $meetNotif = $pdo->prepare("
                        INSERT INTO notifications (user_id, type, title, message, data, priority)
                        VALUES (?, 'consultation_ready', 'Google Meet Link Ready', ?, ?, 'high')
                    ");
                    $meetNotif->execute([
                        $consult['user_id'],
                        'Your Google Meet link is ready! You can now join your scheduled consultation. Consultation #: ' . $consult['consultation_number'],
                        json_encode([
                            'consultation_id' => $consultation_id,
                            'consultation_number' => $consult['consultation_number'],
                            'google_meet_link' => $googleMeetLink
                        ])
                    ]);
                }
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Consultation accepted and scheduled successfully!'
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($_POST['action'] === 'update_consultation') {
        try {
            // Validate required fields
            if (empty($_POST['consultation_id']) || empty($_POST['patient_id']) || empty($_POST['chief_complaint']) || empty($_POST['diagnosis'])) {
                throw new Exception("Please fill in all required fields.");
            }
            
            $consultation_id = intval($_POST['consultation_id']);
            
            // Prepare vital signs JSON
            $vital_signs = json_encode([
                'temperature' => $_POST['temperature'] ?? '',
                'blood_pressure' => $_POST['blood_pressure'] ?? '',
                'pulse_rate' => $_POST['pulse_rate'] ?? '',
                'respiratory_rate' => $_POST['respiratory_rate'] ?? '',
                'weight' => $_POST['weight'] ?? '',
                'height' => $_POST['height'] ?? '',
                'bmi' => $_POST['bmi'] ?? '',
                'oxygen_saturation' => $_POST['oxygen_saturation'] ?? ''
            ]);
            
            // Get Google Meet link - SIMPLE APPROACH like test_consultation.php
            $googleMeetLink = !empty($_POST['google_meet_link']) ? trim($_POST['google_meet_link']) : null;
            
            // SIMPLE UPDATE - Just like test_consultation.php
            $stmt = $pdo->prepare("
                UPDATE consultations SET
                    consultation_type = ?,
                    chief_complaint = ?,
                    history_of_present_illness = ?,
                    symptoms = ?,
                    vital_signs = ?,
                    physical_examination = ?,
                    assessment = ?,
                    diagnosis = ?,
                    treatment_plan = ?,
                    medications_prescribed = ?,
                    recommendations = ?,
                    follow_up_instructions = ?,
                    priority = ?,
                    consultation_location = ?,
                    barangay_id = ?,
                    google_meet_link = ?,
                    follow_up_date = ?,
                    assigned_doctor = ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $executeResult = $stmt->execute([
                $_POST['consultation_type'] ?? 'walk-in',
                $_POST['chief_complaint'],
                $_POST['history_of_present_illness'] ?? '',
                $_POST['symptoms'] ?? '',
                $vital_signs,
                $_POST['physical_examination'] ?? '',
                $_POST['assessment'] ?? '',
                $_POST['diagnosis'],
                $_POST['treatment_plan'] ?? '',
                $_POST['medications_prescribed'] ?? '',
                $_POST['recommendations'] ?? '',
                $_POST['follow_up_instructions'] ?? '',
                $_POST['priority'] ?? 'medium',
                $_POST['consultation_location'] ?? 'RHU',
                !empty($_POST['barangay_id']) ? $_POST['barangay_id'] : null,
                $googleMeetLink,
                !empty($_POST['follow_up_date']) ? $_POST['follow_up_date'] : null,
                $staff['id'],
                $consultation_id
            ]);
            
            if (!$executeResult) {
                throw new Exception("Failed to update consultation in database.");
            }
            
            // Delete old prescriptions and create new ones
            $deleteRx = $pdo->prepare("DELETE FROM prescriptions WHERE consultation_id = ?");
            $deleteRx->execute([$consultation_id]);
            
            $prescriptionWarnings = [];
            if (!empty($_POST['prescriptions'])) {
                // Get patient_id from the consultation to ensure it's valid
                $getPatientId = $pdo->prepare("SELECT patient_id FROM consultations WHERE id = ?");
                $getPatientId->execute([$consultation_id]);
                $consultationData = $getPatientId->fetch(PDO::FETCH_ASSOC);
                $validPatientId = $consultationData['patient_id'];
                
                $prescriptions = json_decode($_POST['prescriptions'], true);
                $year = date('Y');
                
                if (is_array($prescriptions)) {
                    foreach ($prescriptions as $prescription) {
                        if (empty($prescription['medication_name']) || empty($prescription['quantity'])) {
                            continue;
                        }
                        
                        // Check medicine inventory
                        $checkMed = $pdo->prepare("
                            SELECT id, stock_quantity, medicine_name 
                            FROM medicines 
                            WHERE (medicine_name LIKE ? OR generic_name LIKE ?) 
                            AND is_active = 1
                            LIMIT 1
                        ");
                        $medSearch = '%' . $prescription['medication_name'] . '%';
                        $checkMed->execute([$medSearch, $medSearch]);
                        $medicine = $checkMed->fetch();
                        
                        if (!$medicine) {
                            $prescriptionWarnings[] = "{$prescription['medication_name']} not found in inventory";
                        } else {
                            if ($medicine['stock_quantity'] < $prescription['quantity']) {
                                $prescriptionWarnings[] = "{$prescription['medication_name']} - Insufficient stock";
                            }
                        }
                        
                        $rx_number = 'RX-' . $year . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                        
                        $rxStmt = $pdo->prepare("
                            INSERT INTO prescriptions (
                                prescription_number, patient_id, consultation_id, medication_name,
                                generic_name, dosage_strength, dosage_form, quantity_prescribed,
                                dosage_instructions, frequency, duration, special_instructions,
                                prescribed_by, prescription_date, status
                            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'pending')
                        ");
                        
                        $rxStmt->execute([
                            $rx_number,
                            $validPatientId,
                            $consultation_id,
                            $prescription['medication_name'],
                            $prescription['generic_name'] ?? '',
                            $prescription['dosage_strength'] ?? '',
                            $prescription['dosage_form'] ?? 'Tablet',
                            $prescription['quantity'],
                            $prescription['instructions'] ?? '',
                            $prescription['frequency'] ?? '',
                            $prescription['duration'] ?? '',
                            $prescription['special_instructions'] ?? '',
                            $staff['id']
                        ]);
                    }
                }
            }
            
            // Log the update
            $logStmt = $pdo->prepare("
                INSERT INTO system_logs (user_id, action, module, record_id, new_values) 
                VALUES (?, 'CONSULTATION_UPDATED', 'Consultations', ?, ?)
            ");
            $logStmt->execute([
                $_SESSION['user_id'],
                $consultation_id,
                json_encode([
                    'updated_by' => $staff['first_name'] . ' ' . $staff['last_name'],
                    'google_meet_link' => $googleMeetLink
                ])
            ]);
            
            // Notify patient if Google Meet link was added/updated
            if ($googleMeetLink) {
                $getConsultation = $pdo->prepare("
                    SELECT p.user_id, c.consultation_number 
                    FROM consultations c
                    JOIN patients p ON c.patient_id = p.id
                    WHERE c.id = ?
                ");
                $getConsultation->execute([$consultation_id]);
                $consult = $getConsultation->fetch(PDO::FETCH_ASSOC);
                
                if ($consult && $consult['user_id']) {
                    // Check if notification already exists
                    $checkNotif = $pdo->prepare("
                        SELECT id FROM notifications 
                        WHERE user_id = ? 
                        AND type = 'consultation_ready' 
                        AND JSON_EXTRACT(data, '$.consultation_id') = ?
                        AND is_read = 0
                    ");
                    $checkNotif->execute([$consult['user_id'], $consultation_id]);
                    
                    if (!$checkNotif->fetch()) {
                        $notifStmt = $pdo->prepare("
                            INSERT INTO notifications (user_id, type, title, message, data, priority)
                            VALUES (?, 'consultation_ready', 'Online Consultation Ready', ?, ?, 'high')
                        ");
                        $notifStmt->execute([
                            $consult['user_id'],
                            'Your online consultation is ready! Click to join the video call. Consultation #: ' . $consult['consultation_number'],
                            json_encode([
                                'consultation_id' => $consultation_id,
                                'consultation_number' => $consult['consultation_number'],
                                'google_meet_link' => $googleMeetLink
                            ])
                        ]);
                    }
                }
            }
            
            $response = [
                'success' => true, 
                'message' => 'Consultation updated successfully!'
            ];
            
            if (!empty($prescriptionWarnings)) {
                $response['warnings'] = $prescriptionWarnings;
                $response['message'] .= ' Note: ' . implode(', ', $prescriptionWarnings);
            }
            
            echo json_encode($response);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'get_patient_info') {
        try {
            $stmt = $pdo->prepare("
                SELECT p.*, b.barangay_name, 
                       YEAR(CURDATE()) - YEAR(p.date_of_birth) as age,
                       u.username
                FROM patients p
                LEFT JOIN barangays b ON p.barangay_id = b.id
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.id = ?
            ");
            $stmt->execute([$_POST['patient_id']]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$patient) {
                throw new Exception("Patient not found");
            }
            
            // Get patient history
            $historyStmt = $pdo->prepare("
                SELECT c.consultation_date, c.diagnosis, c.chief_complaint,
                       CONCAT(s.first_name, ' ', s.last_name) as doctor_name
                FROM consultations c
                LEFT JOIN staff s ON c.assigned_doctor = s.id
                WHERE c.patient_id = ? AND c.status = 'completed'
                ORDER BY c.consultation_date DESC
                LIMIT 5
            ");
            $historyStmt->execute([$_POST['patient_id']]);
            $history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true, 
                'patient' => $patient,
                'history' => $history
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'search_patient') {
        try {
            $search = '%' . $_POST['search'] . '%';
            $stmt = $pdo->prepare("
                SELECT p.id, p.patient_id, CONCAT(p.first_name, ' ', p.last_name) as full_name,
                       p.date_of_birth, p.gender, p.phone, p.blood_type,
                       YEAR(CURDATE()) - YEAR(p.date_of_birth) as age
                FROM patients p
                WHERE (p.patient_id LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR p.phone LIKE ?)
                AND p.is_active = 1
                ORDER BY p.last_name, p.first_name
                LIMIT 10
            ");
            $stmt->execute([$search, $search, $search, $search]);
            $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'patients' => $patients]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($_POST['action'] === 'mark_completed') {
        try {
            $consultation_id = $_POST['consultation_id'];
            
            // Update status
            $stmt = $pdo->prepare("UPDATE consultations SET status = 'completed', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$consultation_id]);
            
            // Log the action
            $logStmt = $pdo->prepare("
                INSERT INTO system_logs (user_id, action, module, record_id, new_values) 
                VALUES (?, 'CONSULTATION_COMPLETED', 'Consultations', ?, ?)
            ");
            $logStmt->execute([
                $_SESSION['user_id'],
                $consultation_id,
                json_encode(['completed_by' => $staff['first_name'] . ' ' . $staff['last_name']])
            ]);
            
            // Get patient info for notification
            $patientStmt = $pdo->prepare("
                SELECT p.user_id, c.consultation_number 
                FROM consultations c
                JOIN patients p ON c.patient_id = p.id
                WHERE c.id = ?
            ");
            $patientStmt->execute([$consultation_id]);
            $patient = $patientStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($patient && $patient['user_id']) {
                $notifStmt = $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, data, priority)
                    VALUES (?, 'system', 'Consultation Completed', ?, ?, 'medium')
                ");
                $notifStmt->execute([
                    $patient['user_id'],
                    'Your consultation has been completed. Consultation Number: ' . $patient['consultation_number'],
                    json_encode(['consultation_id' => $consultation_id])
                ]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Consultation marked as completed']);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    
    if ($_POST['action'] === 'get_consultation') {
        try {
            $stmt = $pdo->prepare("
                SELECT c.*, 
                       p.patient_id, CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                       p.date_of_birth, p.gender, p.blood_type,
                       YEAR(CURDATE()) - YEAR(p.date_of_birth) as age,
                       CONCAT(s.first_name, ' ', s.last_name) as doctor_name,
                       b.barangay_name
                FROM consultations c
                JOIN patients p ON c.patient_id = p.id
                LEFT JOIN staff s ON c.assigned_doctor = s.id
                LEFT JOIN barangays b ON c.barangay_id = b.id
                WHERE c.id = ?
            ");
            $stmt->execute([$_POST['consultation_id']]);
            $consultation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$consultation) {
                throw new Exception("Consultation not found");
            }
            
            // Get prescriptions
            $rxStmt = $pdo->prepare("
                SELECT * FROM prescriptions 
                WHERE consultation_id = ?
                ORDER BY created_at
            ");
            $rxStmt->execute([$_POST['consultation_id']]);
            $prescriptions = $rxStmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'consultation' => $consultation,
                'prescriptions' => $prescriptions
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}

// Get parameters
$patient_id = $_GET['patient_id'] ?? null;
$filter_date = $_GET['date'] ?? date('Y-m-d');
$filter_status = $_GET['status'] ?? '';
$filter_type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Build consultation query
$consultationsQuery = "
    SELECT c.*, p.patient_id, CONCAT(p.first_name, ' ', p.last_name) as patient_name,
           p.date_of_birth, p.gender, CONCAT(s.first_name, ' ', s.last_name) as doctor_name,
           YEAR(CURDATE()) - YEAR(p.date_of_birth) as age
    FROM consultations c
    JOIN patients p ON c.patient_id = p.id
    LEFT JOIN staff s ON c.assigned_doctor = s.id
    WHERE 1=1
";

$params = [];

// Date filter
if ($filter_date) {
    $consultationsQuery .= " AND DATE(c.consultation_date) = ?";
    $params[] = $filter_date;
}

// Status filter
if ($filter_status) {
    $consultationsQuery .= " AND c.status = ?";
    $params[] = $filter_status;
}

// Search filter
if ($search) {
    $consultationsQuery .= " AND (p.patient_id LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR c.consultation_number LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

// Count total for pagination
$countQuery = str_replace("SELECT c.*, p.patient_id, CONCAT(p.first_name, ' ', p.last_name) as patient_name,
           p.date_of_birth, p.gender, CONCAT(s.first_name, ' ', s.last_name) as doctor_name,
           YEAR(CURDATE()) - YEAR(p.date_of_birth) as age", "SELECT COUNT(*)", $consultationsQuery);
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$total_consultations = $countStmt->fetchColumn();
$total_pages = ceil($total_consultations / $limit);

$consultationsQuery .= " ORDER BY 
    CASE 
        WHEN c.status = 'pending' THEN 1 
        WHEN c.status = 'in_progress' THEN 2 
        WHEN c.status = 'completed' THEN 3 
        ELSE 4 
    END,
    c.created_at DESC 
    LIMIT " . intval($limit) . " OFFSET " . intval($offset);

$stmt = $pdo->prepare($consultationsQuery);
$stmt->execute($params);
$consultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get patient info if patient_id provided
$selectedPatient = null;
if ($patient_id) {
    $stmt = $pdo->prepare("
        SELECT p.*, b.barangay_name, 
               YEAR(CURDATE()) - YEAR(p.date_of_birth) as age
        FROM patients p
        LEFT JOIN barangays b ON p.barangay_id = b.id
        WHERE p.id = ?
    ");
    $stmt->execute([$patient_id]);
    $selectedPatient = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get barangays
$barangaysStmt = $pdo->prepare("SELECT * FROM barangays WHERE is_active = 1 ORDER BY barangay_name");
$barangaysStmt->execute();
$barangays = $barangaysStmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$today = date('Y-m-d');
$statsStmt = $pdo->prepare("
    SELECT 
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_total,
        COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress_total,
        COUNT(CASE WHEN DATE(consultation_date) = ? THEN 1 END) as today_total,
        COUNT(CASE WHEN DATE(consultation_date) = ? AND status = 'completed' THEN 1 END) as today_completed,
        COUNT(CASE WHEN DATE(consultation_date) = ? AND priority IN ('urgent', 'high') THEN 1 END) as today_urgent,
        COUNT(CASE WHEN DATE(consultation_date) = ? AND consultation_type = 'online' THEN 1 END) as today_online,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_total,
        COUNT(*) as all_total
    FROM consultations
");
$statsStmt->execute([$today, $today, $today, $today]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultations - RHU Admin</title>
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

        * { box-sizing: border-box; }

        body {
            background: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
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
        }

        .logo-text {
            font-weight: 700;
            font-size: 1.3rem;
            line-height: 1.2;
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
            cursor: pointer;
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
            margin-right: 15px;
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
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 4px solid var(--kawit-pink);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.3rem;
            color: white;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-pink);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .content-section {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            border-left: 5px solid var(--kawit-pink);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-dark);
            display: flex;
            align-items: center;
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

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background: var(--light-pink);
            color: var(--text-dark);
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }

        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
        }

        .data-table tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-completed { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-in_progress { background: #d1ecf1; color: #0c5460; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        .priority-urgent { background: #f8d7da; color: #721c24; }
        .priority-high { background: #ffebee; color: #c62828; }
        .priority-medium { background: #fff3e0; color: #f57c00; }
        .priority-low { background: #e8f5e8; color: #2e7d32; }

        .patient-search-results {
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
            margin-top: 2px;
        }

        .patient-search-item {
            padding: 10px 15px;
            cursor: pointer;
            transition: background 0.2s;
            border-bottom: 1px solid #f0f0f0;
        }

        .patient-search-item:last-child {
            border-bottom: none;
        }

        .patient-search-item:hover {
            background: var(--light-pink);
        }

        .vital-signs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .prescription-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            position: relative;
            border: 1px solid #e9ecef;
        }

        .remove-prescription {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #dc3545;
            color: white;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .remove-prescription:hover {
            background: #c82333;
            transform: scale(1.1);
        }

        .modal-header {
            background: var(--kawit-gradient);
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .history-item {
            padding: 10px;
            background: #f8f9fa;
            border-left: 3px solid var(--kawit-pink);
            margin-bottom: 8px;
            border-radius: 5px;
        }

        .pagination {
            margin: 0;
        }

        .page-link {
            border-radius: 8px;
            margin: 0 2px;
            border: 1px solid #dee2e6;
            color: var(--text-dark);
        }

        .page-link:hover {
            background: var(--light-pink);
            border-color: var(--kawit-pink);
        }

        .page-item.active .page-link {
            background: var(--kawit-gradient);
            border-color: var(--kawit-pink);
        }

        /* Tab Navigation for Consultations */
        .nav-tabs {
            border-bottom: 2px solid var(--kawit-pink);
        }

        .nav-tabs .nav-link {
            color: var(--text-dark);
            font-weight: 500;
            border: 1px solid transparent;
            border-radius: 10px 10px 0 0;
            padding: 12px 20px;
            margin-right: 5px;
            transition: all 0.3s ease;
            background: #f8f9fa;
            cursor: pointer;
        }

        .nav-tabs .nav-link:hover {
            background: var(--light-pink);
            color: var(--dark-pink);
            border-color: var(--kawit-pink) var(--kawit-pink) transparent;
        }

        .nav-tabs .nav-link.active {
            background: white;
            color: var(--dark-pink);
            border-color: var(--kawit-pink) var(--kawit-pink) white;
            font-weight: 600;
        }

        .nav-tabs .nav-link i {
            margin-right: 5px;
        }

        .nav-tabs .nav-link .badge {
            font-size: 0.75rem;
            padding: 3px 8px;
        }

        /* Enhanced Search and Filter Bar */
        #filterForm {
            flex-wrap: wrap;
        }

        #filterForm .form-control:focus,
        #filterForm .form-select:focus {
            border-color: var(--kawit-pink);
            box-shadow: 0 0 0 0.2rem rgba(255, 166, 190, 0.25);
        }

        /* Compact Table for Better Space Usage */
        .data-table {
            font-size: 0.9rem;
        }

        .data-table th,
        .data-table td {
            padding: 10px 8px;
            vertical-align: middle;
        }

        .data-table .btn-group {
            white-space: nowrap;
        }

        .data-table .btn-sm {
            padding: 4px 8px;
            font-size: 0.8rem;
        }

        /* Priority Indicators */
        .priority-urgent::before {
            content: "🔴 ";
        }

        .priority-high::before {
            content: "🟠 ";
        }

        .priority-medium::before {
            content: "🟡 ";
        }

        .priority-low::before {
            content: "🟢 ";
        }

        /* Sticky Table Header for Long Lists */
        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
            position: relative;
        }

        .data-table thead {
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Highlight Urgent Cases */
        .data-table tr[data-priority="urgent"] {
            background: #fff5f5;
            border-left: 4px solid #dc3545;
        }

        .data-table tr[data-priority="high"] {
            background: #fff8f0;
            border-left: 4px solid #fd7e14;
        }

        /* Modal Prescription Display Styles */
        .prescription-display {
            background: #f8f9fa;
            border-left: 4px solid var(--kawit-pink);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .prescription-display h6 {
            color: var(--dark-pink);
            margin-bottom: 0.5rem;
        }

        .prescription-display .badge {
            font-size: 0.75rem;
            padding: 4px 8px;
        }

        /* Modal Alert Styling */
        #modalAlertMessage {
            margin: -1.5rem -1.5rem 1.5rem -1.5rem;
            padding: 1rem 1.5rem;
            border-radius: 0;
            position: sticky;
            top: -1.5rem;
            z-index: 1050;
            animation: slideDown 0.3s ease-out;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Ensure modal body allows scrolling */
        #consultationModal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
            position: relative;
            padding: 1.5rem;
        }

        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .main-content { margin-left: 0; }
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .vital-signs-grid { grid-template-columns: 1fr; }
        }

                /* Risk Assessment Radio Button Styling */
        .btn-check:checked + .btn-outline-success {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
        }
        
        .btn-check:checked + .btn-outline-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        
        .btn-check:checked + .btn-outline-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
        }
        
        /* Prescription Table Styling */
        #prescriptionList input.form-control-sm {
            font-size: 0.875rem;
        }
        
        #prescriptionList td {
            vertical-align: middle;
            padding: 0.5rem;
        }
        
        /* Vital Signs Grid */
        .vital-signs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        
        /* Form sections styling */
        .card-header.bg-primary {
            background-color: #0d6efd !important;
        }
        
        .card-header.bg-info {
            background-color: #0dcaf0 !important;
        }
        
        .card-header.bg-warning {
            background-color: #ffc107 !important;
        }
        
        .card-header.bg-success {
            background-color: #198754 !important;
        }
        
        .card-header.bg-secondary {
            background-color: #6c757d !important;
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
                    <a href="Consultation.php" class="nav-link active">
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
                    <a href="../../logout.php" class="nav-link">
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
                <h1 class="page-title">Consultations</h1>
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

                <!-- Quick Actions -->
                <div class="content-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-plus-circle"></i>New Consultation
                        </h3>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Search Patient</label>
                            <div style="position: relative;">
                                <input type="text" class="form-control" id="patientSearch" 
                                       placeholder="Enter patient ID, name, or phone..."
                                       value="<?php echo $selectedPatient ? htmlspecialchars($selectedPatient['patient_id'] . ' - ' . $selectedPatient['first_name'] . ' ' . $selectedPatient['last_name']) : ''; ?>">
                                <div class="patient-search-results" id="searchResults"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Consultation Type</label>
                            <select class="form-select" id="consultationType">
                                <option value="walk-in">Walk-in</option>
                                <option value="follow-up">Follow-up</option>
                                <option value="online">Online</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button class="btn btn-primary w-100" id="startConsultation">
                                <i class="fas fa-stethoscope me-2"></i>Start Consultation
                            </button>
                        </div>
                    </div>

                    <?php if ($selectedPatient): ?>
                    <div class="alert alert-info">
                        <strong>Selected Patient:</strong> 
                        <?php echo htmlspecialchars($selectedPatient['first_name'] . ' ' . $selectedPatient['last_name']); ?>
                        (ID: <?php echo htmlspecialchars($selectedPatient['patient_id']); ?>)
                        - Age: <?php echo $selectedPatient['age']; ?> years old
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Filters and Search -->
                <div class="content-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-list"></i>Consultations List
                        </h3>
                        <form method="GET" action="" class="d-flex gap-2" id="filterForm">
                            <input type="text" class="form-control" placeholder="Search patient name or consultation #..." 
                                name="search" value="<?php echo htmlspecialchars($search); ?>" style="width: 250px;">
                            <input type="date" class="form-control" value="<?php echo $filter_date; ?>" 
                                name="date" id="filterDate" style="width: auto;">
                            <select class="form-select" name="status" id="filterStatus" style="width: auto;">
                                <option value="">All Status</option>
                                <option value="completed" <?php echo $filter_status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="in_progress" <?php echo $filter_status == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            </select>
                            <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                                <i class="fas fa-filter me-1"></i>Apply
                            </button>
                            <a href="Consultation.php" class="btn btn-secondary" style="white-space: nowrap;">
                                <i class="fas fa-redo me-1"></i>Reset
                            </a>
                        </form>
                    </div>

                    <!-- Tab Navigation for Consultation Status -->
                    <ul class="nav nav-tabs mb-3" id="consultationTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $filter_status === 'pending' || (!$filter_status && $stats['pending_total'] > 0) ? 'active' : ''; ?>" 
                                    type="button" onclick="window.location.href='?status=pending&date=<?php echo $filter_date; ?>&search=<?php echo urlencode($search); ?>'">
                                <i class="fas fa-clock me-1"></i>Pending Requests
                                <span class="badge bg-warning text-dark ms-1"><?php echo $stats['pending_total']; ?></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $filter_status === 'in_progress' ? 'active' : ''; ?>" 
                                    type="button" onclick="window.location.href='?status=in_progress&date=<?php echo $filter_date; ?>&search=<?php echo urlencode($search); ?>'">
                                <i class="fas fa-calendar-check me-1"></i>Scheduled
                                <span class="badge bg-primary ms-1"><?php echo $stats['in_progress_total']; ?></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $filter_status === 'completed' ? 'active' : ''; ?>" 
                                    type="button" onclick="window.location.href='?status=completed&date=<?php echo $filter_date; ?>&search=<?php echo urlencode($search); ?>'">
                                <i class="fas fa-check-circle me-1"></i>Completed
                                <span class="badge bg-success ms-1"><?php echo $stats['today_completed']; ?></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo !$filter_status && $stats['pending_total'] === 0 ? 'active' : ''; ?>" 
                                    type="button" onclick="window.location.href='?date=<?php echo $filter_date; ?>&search=<?php echo urlencode($search); ?>'">
                                <i class="fas fa-list me-1"></i>All Consultations
                            </button>
                        </li>
                    </ul>

                    <!-- Sorting Options -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <strong>Sort by:</strong>
                            <div class="btn-group ms-2" role="group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="sortTable('priority')">
                                    <i class="fas fa-sort-amount-down me-1"></i>Priority
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="sortTable('date')">
                                    <i class="fas fa-calendar me-1"></i>Date
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="sortTable('patient')">
                                    <i class="fas fa-user me-1"></i>Patient
                                </button>
                            </div>
                        </div>
                        <div class="text-muted">
                            <?php if (!empty($consultations)): ?>
                                Showing <?php echo count($consultations); ?> of <?php echo $total_consultations; ?> consultations
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($consultations)): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Consultation #</th>
                                    <th>Requested</th>
                                    <th>Scheduled Time</th>
                                    <th>Patient</th>
                                    <th>Type</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Doctor</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($consultations as $consultation): ?>
                                <tr data-priority="<?php echo $consultation['priority']; ?>" 
                                    <?php if ($consultation['priority'] === 'urgent' || $consultation['priority'] === 'high'): ?>
                                        style="border-left: 4px solid <?php echo $consultation['priority'] === 'urgent' ? '#dc3545' : '#fd7e14'; ?>;"
                                    <?php endif; ?>>
                                    <td>
                                        <strong><?php echo htmlspecialchars($consultation['consultation_number']); ?></strong>
                                        <?php if ($consultation['priority'] === 'urgent'): ?>
                                            <i class="fas fa-exclamation-circle text-danger ms-1" title="Urgent Priority"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, g:i A', strtotime($consultation['created_at'])); ?></td>
                                    <td>
                                        <?php if ($consultation['consultation_date'] && $consultation['consultation_date'] != '0000-00-00 00:00:00' && $consultation['status'] != 'pending'): ?>
                                            <strong style="color: #2196F3;"><?php echo date('M j, g:i A', strtotime($consultation['consultation_date'])); ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">Not scheduled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($consultation['patient_name']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($consultation['patient_id']); ?></small>
                                    </td>
                                    <td><?php echo ucfirst($consultation['consultation_type']); ?></td>
                                    <td>
                                        <span class="status-badge priority-<?php echo $consultation['priority']; ?>">
                                            <?php echo ucfirst($consultation['priority']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $consultation['status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $consultation['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($consultation['doctor_name'] ?? 'Unassigned'); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewConsultation(<?php echo $consultation['id']; ?>)" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            <?php if ($consultation['status'] === 'pending' && $consultation['consultation_type'] === 'online'): ?>
                                                <button class="btn btn-sm btn-success" 
                                                        onclick="scheduleConsultation(<?php echo $consultation['id']; ?>, '<?php echo htmlspecialchars($consultation['consultation_number']); ?>', '<?php echo htmlspecialchars($consultation['patient_name']); ?>')" 
                                                        title="Accept & Schedule">
                                                    <i class="fas fa-calendar-check"></i> Schedule
                                                </button>
                                            <?php endif; ?>
                                            
                                                <?php if ($consultation['status'] === 'in_progress' || $consultation['status'] === 'completed'): ?>
                                                <button class="btn btn-sm btn-outline-info" 
                                                        onclick="startEditConsultation(<?php echo $consultation['id']; ?>)" 
                                                        title="Edit Consultation">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                
                                                <?php if ($consultation['status'] === 'in_progress'): ?>
                                                <button class="btn btn-sm btn-outline-success" 
                                                        onclick="markConsultationCompleted(<?php echo $consultation['id']; ?>)" 
                                                        title="Mark as Completed">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            
                                            <?php if ($consultation['status'] === 'completed'): ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Showing <?php echo (($page - 1) * $limit) + 1; ?> to <?php echo min($page * $limit, $total_consultations); ?> 
                            of <?php echo number_format($total_consultations); ?> consultations
                        </div>
                        <nav>
                            <ul class="pagination">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&date=<?php echo $filter_date; ?>&status=<?php echo $filter_status; ?>&type=<?php echo $filter_type; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                for ($i = $start_page; $i <= $end_page; $i++):
                                ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&date=<?php echo $filter_date; ?>&status=<?php echo $filter_status; ?>&type=<?php echo $filter_type; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&date=<?php echo $filter_date; ?>&status=<?php echo $filter_status; ?>&type=<?php echo $filter_type; ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>

                    <!-- Alternative: Load More Button for Long Lists -->
                    <?php if ($total_consultations > 20 && !isset($_GET['show_all'])): ?>
                        <div class="text-center mt-3">
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['show_all' => '1'])); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-chevron-down me-2"></i>Show All <?php echo $total_consultations; ?> Consultations
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No consultations for selected date</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Consultation Modal -->
    <div class="modal fade" id="consultationModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-stethoscope me-2"></i>Medical Consultation
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Modal Alert Area -->
                    <div id="modalAlertMessage" style="display: none;"></div>
                    
                        <form id="consultationForm">
                        <input type="hidden" id="patient_id" name="patient_id">
                        <input type="hidden" id="consultation_id" name="consultation_id">
                        
                        <!-- SECTION 1: PATIENT HEADER INFORMATION -->
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-id-card me-2"></i>INDIVIDUAL TREATMENT RECORD - Header</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">PhilHealth No.</label>
                                        <input type="text" class="form-control" name="philhealth_no" placeholder="00-000000000-0">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Date</label>
                                        <input type="date" class="form-control" name="consultation_date_display" value="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">OSCA</label>
                                        <input type="text" class="form-control" name="osca_number" placeholder="OSCA Number">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Family No.</label>
                                        <input type="text" class="form-control" name="family_number" placeholder="Family Number">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 2: PATIENT INFORMATION (Auto-filled from patient record) -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-user me-2"></i>Patient Information</h6>
                            </div>
                            <div class="card-body" id="patientInfo">
                                <p class="text-muted">Please select a patient first</p>
                            </div>
                        </div>

                        <!-- SECTION 3: ADDRESS & CONTACT DETAILS -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Address & Contact</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label class="form-label">House No.</label>
                                        <input type="text" class="form-control" name="house_number" placeholder="123">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Street</label>
                                        <input type="text" class="form-control" name="street" placeholder="Street Name">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Barangay</label>
                                        <select class="form-select" name="barangay_id">
                                            <option value="">Select Barangay</option>
                                            <?php foreach ($barangays as $barangay): ?>
                                                <option value="<?php echo $barangay['id']; ?>"><?php echo htmlspecialchars($barangay['barangay_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Municipality</label>
                                        <input type="text" class="form-control" name="municipality" value="Kawit" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Contact No.</label>
                                        <input type="tel" class="form-control" name="contact_number" placeholder="0917-123-4567">
                                    </div>
                                </div>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Occupation</label>
                                        <input type="text" class="form-control" name="occupation" placeholder="Occupation">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Educational Attainment</label>
                                        <input type="text" class="form-control" name="educational_attainment" placeholder="e.g., College Graduate">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Name of Patient or Guardian (if pediatric)</label>
                                        <input type="text" class="form-control" name="guardian_name" placeholder="Guardian Name">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 4: VITAL SIGNS -->
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-heartbeat me-2"></i>VITAL SIGNS</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label class="form-label">Temperature (°C)</label>
                                        <input type="number" step="0.1" class="form-control" name="temperature" placeholder="36.5">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">BP (Systolic)</label>
                                        <input type="number" class="form-control" name="blood_pressure_systolic" id="bp_sys" placeholder="120">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">BP (Diastolic)</label>
                                        <input type="number" class="form-control" name="blood_pressure_diastolic" id="bp_dia" placeholder="80">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">HR/PR (bpm)</label>
                                        <input type="number" class="form-control" name="heart_rate" placeholder="72">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">RR</label>
                                        <input type="number" class="form-control" name="respiratory_rate" placeholder="16">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">O2 Sat (%)</label>
                                        <input type="number" class="form-control" name="oxygen_saturation" placeholder="98">
                                    </div>
                                </div>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-2">
                                        <label class="form-label">Blood Type</label>
                                        <select class="form-select" name="blood_type">
                                            <option value="">Select</option>
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
                                    <div class="col-md-2">
                                        <label class="form-label">Weight (kg)</label>
                                        <input type="number" step="0.01" class="form-control" name="weight" id="weight" placeholder="65">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Height (cm)</label>
                                        <input type="number" step="0.01" class="form-control" name="height" id="height" placeholder="165">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">BMI</label>
                                        <input type="text" class="form-control" name="bmi" id="bmi" readonly placeholder="Auto">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">PWD?</label>
                                        <select class="form-select" name="pwd_status">
                                            <option value="NO">NO</option>
                                            <option value="YES">YES</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">NHTS (4Ps)?</label>
                                        <select class="form-select" name="nhts_4ps_status">
                                            <option value="NO">NO</option>
                                            <option value="YES">YES</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 5: RISK ASSESSMENT -->
                        <div class="card mb-3">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>RISK ASSESSMENT</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label"><strong>Current Smoker?</strong></label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="current_smoker" id="smoker_yes" value="YES">
                                            <label class="btn btn-outline-danger" for="smoker_yes">YES</label>
                                            <input type="radio" class="btn-check" name="current_smoker" id="smoker_no" value="NO" checked>
                                            <label class="btn btn-outline-success" for="smoker_no">NO</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label"><strong>Alcohol Intake?</strong></label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="alcohol_intake" id="alcohol_yes" value="YES">
                                            <label class="btn btn-outline-danger" for="alcohol_yes">YES</label>
                                            <input type="radio" class="btn-check" name="alcohol_intake" id="alcohol_no" value="NO" checked>
                                            <label class="btn btn-outline-success" for="alcohol_no">NO</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label"><strong>Illicit Drug Use?</strong></label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="illicit_drug_use" id="drugs_yes" value="YES">
                                            <label class="btn btn-outline-danger" for="drugs_yes">YES</label>
                                            <input type="radio" class="btn-check" name="illicit_drug_use" id="drugs_no" value="NO" checked>
                                            <label class="btn btn-outline-success" for="drugs_no">NO</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label"><strong>Eat Unhealthy Foods?</strong></label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="eat_unhealthy_foods" id="unhealthy_yes" value="YES">
                                            <label class="btn btn-outline-danger" for="unhealthy_yes">YES</label>
                                            <input type="radio" class="btn-check" name="eat_unhealthy_foods" id="unhealthy_no" value="NO" checked>
                                            <label class="btn btn-outline-success" for="unhealthy_no">NO</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-3">
                                        <label class="form-label"><strong>Nakakapag Exercise?</strong></label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="regular_exercise" id="exercise_yes" value="YES">
                                            <label class="btn btn-outline-success" for="exercise_yes">YES</label>
                                            <input type="radio" class="btn-check" name="regular_exercise" id="exercise_no" value="NO" checked>
                                            <label class="btn btn-outline-danger" for="exercise_no">NO</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label"><strong>With Hypertension?</strong></label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="with_hypertension" id="hyper_yes" value="YES">
                                            <label class="btn btn-outline-danger" for="hyper_yes">YES</label>
                                            <input type="radio" class="btn-check" name="with_hypertension" id="hyper_no" value="NO" checked>
                                            <label class="btn btn-outline-success" for="hyper_no">NO</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label"><strong>Nakakakuha ng Gamot sa RHU?</strong></label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="hypertension_meds_from_rhu" id="hyper_meds_yes" value="YES">
                                            <label class="btn btn-outline-success" for="hyper_meds_yes">YES</label>
                                            <input type="radio" class="btn-check" name="hypertension_meds_from_rhu" id="hyper_meds_no" value="NO" checked>
                                            <label class="btn btn-outline-secondary" for="hyper_meds_no">NO</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3"></div>
                                </div>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-3">
                                        <label class="form-label"><strong>With Diabetes?</strong></label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="with_diabetes" id="diabetes_yes" value="YES">
                                            <label class="btn btn-outline-danger" for="diabetes_yes">YES</label>
                                            <input type="radio" class="btn-check" name="with_diabetes" id="diabetes_no" value="NO" checked>
                                            <label class="btn btn-outline-success" for="diabetes_no">NO</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label"><strong>Nakakakuha ng Gamot sa RHU?</strong></label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="diabetes_meds_from_rhu" id="diabetes_meds_yes" value="YES">
                                            <label class="btn btn-outline-success" for="diabetes_meds_yes">YES</label>
                                            <input type="radio" class="btn-check" name="diabetes_meds_from_rhu" id="diabetes_meds_no" value="NO" checked>
                                            <label class="btn btn-outline-secondary" for="diabetes_meds_no">NO</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 6: CLINICAL INFORMATION -->
                        <div class="card mb-3">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>CLINICAL INFORMATION</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Chief Complaint *</label>
                                        <textarea class="form-control" name="chief_complaint" rows="3" required placeholder="Primary reason for visit"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Medication Taken</label>
                                        <textarea class="form-control" name="medication_taken" rows="3" placeholder="Medications patient has already taken"></textarea>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label fw-bold">Diagnosis *</label>
                                    <textarea class="form-control" name="diagnosis" rows="3" required placeholder="Primary diagnosis"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 7: MEDICATIONS GIVEN/PRESCRIBED -->
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="fas fa-pills me-2"></i>MEDICATIONS GIVEN / PRESCRIBED</h6>
                                <button type="button" class="btn btn-sm btn-light" id="addPrescription">
                                    <i class="fas fa-plus me-1"></i>Add Medicine
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 35%;">NAME OF DRUG / MEDICINE</th>
                                                <th style="width: 20%;">DOSE</th>
                                                <th style="width: 20%;">FREQUENCY</th>
                                                <th style="width: 15%;">DURATION</th>
                                                <th style="width: 10%;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="prescriptionList">
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No medications added yet. Click "Add Medicine" to add.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 8: FOLLOW-UP & PROVIDER INFO -->
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0"><i class="fas fa-calendar-check me-2"></i>FOLLOW-UP & MANAGEMENT</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">To Be Come Back On / Follow-up Check-up</label>
                                        <input type="date" class="form-control" name="follow_up_date">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Follow-up Instructions</label>
                                        <textarea class="form-control" name="follow_up_instructions" rows="2" placeholder="When to return, warning signs"></textarea>
                                    </div>
                                </div>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Management / Treatment Provided By - Name</label>
                                        <input type="text" class="form-control" name="prescribed_by_name" value="<?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Priority Level</label>
                                        <select class="form-select" name="priority">
                                            <option value="low">Low</option>
                                            <option value="medium" selected>Medium</option>
                                            <option value="high">High</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Settings -->
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label class="form-label">Consultation Type</label>
                                <select class="form-select" name="consultation_type" id="modalConsultationType">
                                    <option value="walk-in">Walk-in</option>
                                    <option value="follow-up">Follow-up</option>
                                    <option value="online">Online</option>
                                    <option value="emergency">Emergency</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Location</label>
                                <select class="form-select" name="consultation_location">
                                    <option value="RHU">RHU Main</option>
                                    <option value="BHS">Barangay Health Station</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Barangay (if BHS)</label>
                                <select class="form-select" name="barangay_id">
                                    <option value="">Select Barangay</option>
                                    <?php foreach ($barangays as $barangay): ?>
                                        <option value="<?php echo $barangay['id']; ?>"><?php echo htmlspecialchars($barangay['barangay_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Google Meet Link -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label class="form-label">
                                    <i class="fab fa-google me-2"></i>Google Meet Link (for online consultations)
                                </label>
                                <input type="url" class="form-control" name="google_meet_link" placeholder="https://meet.google.com/xxx-xxxx-xxx">
                                <small class="text-muted">Optional: Add Google Meet link for video consultation</small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveConsultation">
                        <i class="fas fa-save me-2"></i>Save Consultation
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Consultation Modal -->
    <div class="modal fade" id="scheduleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-check me-2"></i>Schedule Consultation
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="schedule_consultation_id">
                    <div id="schedulePatientInfo" class="alert alert-info mb-3"></div>
                    
                    <div class="mb-3">
                        <label class="form-label">Consultation Date *</label>
                        <input type="date" class="form-control" id="scheduled_date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Consultation Time *</label>
                        <input type="time" class="form-control" id="scheduled_time" required>
                        <small class="text-muted">Choose a time slot. System will check if it's available.</small>
                    </div>

                    <div class="mb-3">
                    <label class="form-label">Priority Level *</label>
                    <select class="form-select" id="scheduled_priority" required>
                        <option value="low">Low - Minor concern, routine care</option>
                        <option value="medium" selected>Medium - Standard consultation</option>
                        <option value="high">High - Needs prompt attention</option>
                        <option value="urgent">Urgent - Critical, schedule immediately</option>
                    </select>
                    <small class="text-muted">Set the medical priority based on patient's symptoms</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        <i class="fab fa-google me-2"></i>Google Meet Link (Optional)
                    </label>
                    <input type="url" class="form-control" id="scheduled_google_meet" placeholder="https://meet.google.com/xxx-xxxx-xxx">
                    <small class="text-muted">Add the video consultation link. Patient will be notified.</small>
                </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Patient will be notified of the scheduled time. They should be online 5 minutes early.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmSchedule">
                        <i class="fas fa-check me-2"></i>Accept & Schedule
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Consultation Modal -->
    <div class="modal fade" id="viewConsultationModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-medical me-2"></i>Consultation Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewConsultationContent">
                    <div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedPatientId = <?php echo $patient_id ? $patient_id : 'null'; ?>;
        let currentConsultationId = null;
        let searchTimeout;
        let prescriptionCount = 0;

        function showAlert(message, type = 'danger') {
            const alertDiv = document.getElementById('alertMessage');
            if (!alertDiv) {
                console.error('Alert div not found!');
                return;
            }
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertDiv.style.display = 'block';
            window.scrollTo({top: 0, behavior: 'smooth'});
            
            if (type === 'success') {
                setTimeout(() => { alertDiv.style.display = 'none'; }, 5000);
            }
        }

        // Modal-specific alert function
        function showModalAlert(message, type = 'danger') {
            const modalAlertDiv = document.getElementById('modalAlertMessage');
            if (!modalAlertDiv) {
                console.error('Modal alert div not found!');
                return;
            }
            
            modalAlertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            modalAlertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            modalAlertDiv.style.display = 'block';
            
            // Force scroll to top with multiple methods
            setTimeout(() => {
                const modalBody = document.querySelector('#consultationModal .modal-body');
                if (modalBody) {
                    // Method 1: Direct scrollTop
                    modalBody.scrollTop = 0;
                    
                    // Method 2: scrollTo with smooth behavior
                    modalBody.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                    
                    // Method 3: scrollIntoView on the alert itself
                    modalAlertDiv.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start',
                        inline: 'nearest'
                    });
                }
            }, 100); // Small delay to ensure DOM is updated
            
            // Auto-hide success messages
            if (type === 'success') {
                setTimeout(() => { 
                    modalAlertDiv.style.display = 'none'; 
                }, 5000);
            }
        }

        function selectPatient(id, patientId, name, age, gender, bloodType) {
            selectedPatientId = id;
            document.getElementById('patientSearch').value = `${patientId} - ${name}`;
            document.getElementById('searchResults').style.display = 'none';
        }

        function calculateBMI() {
            const weight = parseFloat(document.getElementById('weight').value);
            const height = parseFloat(document.getElementById('height').value);
            
            if (weight && height && weight > 0 && height > 0) {
                const bmi = weight / Math.pow(height / 100, 2);
                document.getElementById('bmi').value = bmi.toFixed(2);
            } else {
                document.getElementById('bmi').value = '';
            }
        }

        function removePrescription(btn) {
            btn.parentElement.remove();
            if (!document.getElementById('prescriptionList').querySelector('.prescription-item')) {
                document.getElementById('prescriptionList').innerHTML = '<p class="text-muted">No prescriptions added yet</p>';
            }
        }

        function viewConsultation(id) {
            currentConsultationId = id;
            const modal = new bootstrap.Modal(document.getElementById('viewConsultationModal'));
            const content = document.getElementById('viewConsultationContent');
            
            content.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading...</div>';
            modal.show();

            fetch('Consultation.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=get_consultation&consultation_id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const c = data.consultation;
                    const vitals = c.vital_signs ? JSON.parse(c.vital_signs) : {};
                    
                    let prescriptionsHtml = '';
                    if (data.prescriptions && data.prescriptions.length > 0) {
                        prescriptionsHtml = '<div class="card mt-3"><div class="card-header bg-light"><h6 class="mb-0">Prescriptions</h6></div><div class="card-body">';
                        data.prescriptions.forEach(rx => {
                            prescriptionsHtml += `
                                <div class="prescription-item">
                                    <strong>${rx.medication_name}</strong> ${rx.dosage_strength || ''}<br>
                                    <small>Generic: ${rx.generic_name || 'N/A'} | Form: ${rx.dosage_form}</small><br>
                                    <small>Quantity: ${rx.quantity_prescribed} | ${rx.frequency} for ${rx.duration}</small><br>
                                    <small>Instructions: ${rx.dosage_instructions}</small>
                                </div>
                            `;
                        });
                        prescriptionsHtml += '</div></div>';
                    }
                    
                    content.innerHTML = `
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <strong>Consultation #:</strong> ${c.consultation_number}<br>
                                    <strong>Date:</strong> ${new Date(c.consultation_date).toLocaleString()}<br>
                                    <strong>Patient:</strong> ${c.patient_name} (${c.patient_id})<br>
                                    <strong>Age/Gender:</strong> ${c.age} years / ${c.gender}<br>
                                    <strong>Blood Type:</strong> ${c.blood_type || 'Not specified'}<br>
                                    <strong>Type:</strong> ${c.consultation_type} | <strong>Priority:</strong> ${c.priority}
                                </div>
                                
                                ${c.patient_notes ? `
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Patient's Notes/Concern</h6>
                                    </div>
                                    <div class="card-body">${c.patient_notes}</div>
                                </div>` : ''}
                                
                                ${c.consultation_date && c.consultation_date != '0000-00-00 00:00:00' && c.status === 'in_progress' ? `
                                <div class="alert alert-primary">
                                    <i class="fas fa-calendar-check me-2"></i>
                                    <strong>Scheduled Time:</strong> ${new Date(c.consultation_date).toLocaleString('en-US', { 
                                        month: 'long', 
                                        day: 'numeric', 
                                        year: 'numeric', 
                                        hour: 'numeric', 
                                        minute: '2-digit',
                                        hour12: true 
                                    })}
                                </div>` : ''}

                                <div class="card mb-3">
                                    <div class="card-header bg-light"><h6 class="mb-0">Chief Complaint</h6></div>
                                    <div class="card-body">${c.chief_complaint}</div>
                                </div>
                                
                                ${c.history_of_present_illness ? `
                                <div class="card mb-3">
                                    <div class="card-header bg-light"><h6 class="mb-0">History of Present Illness</h6></div>
                                    <div class="card-body">${c.history_of_present_illness}</div>
                                </div>` : ''}
                                
                                ${c.symptoms ? `
                                <div class="card mb-3">
                                    <div class="card-header bg-light"><h6 class="mb-0">Symptoms</h6></div>
                                    <div class="card-body">${c.symptoms}</div>
                                </div>` : ''}
                                
                                ${Object.keys(vitals).filter(k => vitals[k]).length > 0 ? `
                                <div class="card mb-3">
                                    <div class="card-header bg-light"><h6 class="mb-0">Vital Signs</h6></div>
                                    <div class="card-body">
                                        <div class="row">
                                            ${vitals.temperature ? `<div class="col-md-3"><strong>Temp:</strong> ${vitals.temperature}°C</div>` : ''}
                                            ${vitals.blood_pressure ? `<div class="col-md-3"><strong>BP:</strong> ${vitals.blood_pressure}</div>` : ''}
                                            ${vitals.pulse_rate ? `<div class="col-md-3"><strong>Pulse:</strong> ${vitals.pulse_rate} bpm</div>` : ''}
                                            ${vitals.respiratory_rate ? `<div class="col-md-3"><strong>RR:</strong> ${vitals.respiratory_rate}</div>` : ''}
                                            ${vitals.weight ? `<div class="col-md-3"><strong>Weight:</strong> ${vitals.weight} kg</div>` : ''}
                                            ${vitals.height ? `<div class="col-md-3"><strong>Height:</strong> ${vitals.height} cm</div>` : ''}
                                            ${vitals.bmi ? `<div class="col-md-3"><strong>BMI:</strong> ${vitals.bmi}</div>` : ''}
                                            ${vitals.oxygen_saturation ? `<div class="col-md-3"><strong>O2 Sat:</strong> ${vitals.oxygen_saturation}%</div>` : ''}
                                        </div>
                                    </div>
                                </div>` : ''}
                                
                                ${c.physical_examination ? `
                                <div class="card mb-3">
                                    <div class="card-header bg-light"><h6 class="mb-0">Physical Examination</h6></div>
                                    <div class="card-body">${c.physical_examination}</div>
                                </div>` : ''}
                                
                                ${c.assessment ? `
                                <div class="card mb-3">
                                    <div class="card-header bg-light"><h6 class="mb-0">Assessment</h6></div>
                                    <div class="card-body">${c.assessment}</div>
                                </div>` : ''}
                                
                                <div class="card mb-3">
                                    <div class="card-header bg-success text-white"><h6 class="mb-0">Diagnosis</h6></div>
                                    <div class="card-body"><strong>${c.diagnosis}</strong></div>
                                </div>
                                
                                ${c.treatment_plan ? `
                                <div class="card mb-3">
                                    <div class="card-header bg-light"><h6 class="mb-0">Treatment Plan</h6></div>
                                    <div class="card-body">${c.treatment_plan}</div>
                                </div>` : ''}
                                
                                ${prescriptionsHtml}
                                
                                ${c.recommendations ? `
                                <div class="card mb-3">
                                    <div class="card-header bg-light"><h6 class="mb-0">Recommendations</h6></div>
                                    <div class="card-body">${c.recommendations}</div>
                                </div>` : ''}
                                
                                ${c.follow_up_instructions ? `
                                <div class="card mb-3">
                                    <div class="card-header bg-light"><h6 class="mb-0">Follow-up Instructions</h6></div>
                                    <div class="card-body">
                                        ${c.follow_up_instructions}
                                        ${c.follow_up_date ? `<br><strong>Follow-up Date:</strong> ${new Date(c.follow_up_date).toLocaleDateString()}` : ''}
                                    </div>
                                </div>` : ''}
                                
                                ${c.google_meet_link ? `
                                <div class="card mb-3 border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="fab fa-google me-2"></i>Online Consultation Link</h6>
                                    </div>
                                    <div class="card-body">
                                        <a href="${c.google_meet_link}" target="_blank" class="btn btn-success">
                                            <i class="fab fa-google me-2"></i>Join Google Meet
                                        </a>
                                        <p class="mt-2 mb-0 text-muted"><small>${c.google_meet_link}</small></p>
                                    </div>
                                </div>` : ''}

                                <div class="card">
                                    <div class="card-header bg-light"><h6 class="mb-0">Additional Information</h6></div>
                                    <div class="card-body">
                                    <strong>Doctor:</strong> ${c.doctor_name || 'Unassigned'}<br>
                                    <strong>Location:</strong> ${c.consultation_location} ${c.barangay_name ? '- ' + c.barangay_name : ''}<br>
                                    <strong>Status:</strong> <span class="status-badge status-${c.status}">${c.status}</span>
                                    <br><strong>Created:</strong> ${new Date(c.created_at).toLocaleString()}
                                    ${c.updated_at ? `<br><strong>Last Updated:</strong> ${new Date(c.updated_at).toLocaleString()}` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    content.innerHTML = '<div class="alert alert-danger">Error loading consultation details</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = '<div class="alert alert-danger">Error loading consultation details</div>';
            });
        }

        function markConsultationCompleted(id) {
            if (!confirm('Mark this consultation as completed?')) {
                return;
            }

            fetch('Consultation.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=mark_completed&consultation_id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred');
            });
        }

function startEditConsultation(id) {
            fetch('Consultation.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=get_consultation&consultation_id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const c = data.consultation;

                    selectedPatientId = c.patient_id;
                    document.getElementById('patient_id').value = c.patient_id;
                    document.getElementById('consultation_id').value = id;
                    
                    // Header Information
                    if (document.querySelector('[name="philhealth_no"]')) document.querySelector('[name="philhealth_no"]').value = c.philhealth_no || '';
                    if (document.querySelector('[name="osca_number"]')) document.querySelector('[name="osca_number"]').value = c.osca_number || '';
                    if (document.querySelector('[name="family_number"]')) document.querySelector('[name="family_number"]').value = c.family_number || '';
                    if (document.querySelector('[name="pwd_status"]')) document.querySelector('[name="pwd_status"]').value = c.pwd_status || 'NO';
                    if (document.querySelector('[name="nhts_4ps_status"]')) document.querySelector('[name="nhts_4ps_status"]').value = c.nhts_4ps_status || 'NO';
                    
                    // Address & Contact
                    if (document.querySelector('[name="house_number"]')) document.querySelector('[name="house_number"]').value = c.house_number || '';
                    if (document.querySelector('[name="street"]')) document.querySelector('[name="street"]').value = c.street || '';
                    if (document.querySelector('[name="municipality"]')) document.querySelector('[name="municipality"]').value = c.municipality || 'Kawit';
                    if (document.querySelector('[name="contact_number"]')) document.querySelector('[name="contact_number"]').value = c.contact_number || '';
                    if (document.querySelector('[name="occupation"]')) document.querySelector('[name="occupation"]').value = c.occupation || '';
                    if (document.querySelector('[name="educational_attainment"]')) document.querySelector('[name="educational_attainment"]').value = c.educational_attainment || '';
                    if (document.querySelector('[name="guardian_name"]')) document.querySelector('[name="guardian_name"]').value = c.guardian_name || '';
                    if (document.querySelector('[name="blood_type"]')) document.querySelector('[name="blood_type"]').value = c.blood_type || '';
                    
                    // Chief Complaint & Medication
                    if (document.querySelector('[name="chief_complaint"]')) document.querySelector('[name="chief_complaint"]').value = c.chief_complaint || '';
                    if (document.querySelector('[name="medication_taken"]')) document.querySelector('[name="medication_taken"]').value = c.medication_taken || '';
                    
                    // Vital Signs
                    if (document.querySelector('[name="temperature"]')) document.querySelector('[name="temperature"]').value = c.temperature || '';
                    if (document.querySelector('[name="blood_pressure_systolic"]')) document.querySelector('[name="blood_pressure_systolic"]').value = c.blood_pressure_systolic || '';
                    if (document.querySelector('[name="blood_pressure_diastolic"]')) document.querySelector('[name="blood_pressure_diastolic"]').value = c.blood_pressure_diastolic || '';
                    if (document.querySelector('[name="heart_rate"]')) document.querySelector('[name="heart_rate"]').value = c.heart_rate || '';
                    if (document.querySelector('[name="respiratory_rate"]')) document.querySelector('[name="respiratory_rate"]').value = c.respiratory_rate || '';
                    if (document.querySelector('[name="oxygen_saturation"]')) document.querySelector('[name="oxygen_saturation"]').value = c.oxygen_saturation || '';
                    if (document.querySelector('[name="weight"]')) document.querySelector('[name="weight"]').value = c.weight || '';
                    if (document.querySelector('[name="height"]')) document.querySelector('[name="height"]').value = c.height || '';
                    if (document.querySelector('[name="bmi"]')) document.querySelector('[name="bmi"]').value = c.bmi || '';
                    
                    // Risk Assessment - Radio Buttons
                    if (c.current_smoker) {
                        const smokerRadio = document.querySelector(`input[name="current_smoker"][value="${c.current_smoker}"]`);
                        if (smokerRadio) smokerRadio.checked = true;
                    }
                    if (c.alcohol_intake) {
                        const alcoholRadio = document.querySelector(`input[name="alcohol_intake"][value="${c.alcohol_intake}"]`);
                        if (alcoholRadio) alcoholRadio.checked = true;
                    }
                    if (c.illicit_drug_use) {
                        const drugRadio = document.querySelector(`input[name="illicit_drug_use"][value="${c.illicit_drug_use}"]`);
                        if (drugRadio) drugRadio.checked = true;
                    }
                    if (c.eat_unhealthy_foods) {
                        const unhealthyRadio = document.querySelector(`input[name="eat_unhealthy_foods"][value="${c.eat_unhealthy_foods}"]`);
                        if (unhealthyRadio) unhealthyRadio.checked = true;
                    }
                    if (c.regular_exercise) {
                        const exerciseRadio = document.querySelector(`input[name="regular_exercise"][value="${c.regular_exercise}"]`);
                        if (exerciseRadio) exerciseRadio.checked = true;
                    }
                    if (c.with_hypertension) {
                        const hyperRadio = document.querySelector(`input[name="with_hypertension"][value="${c.with_hypertension}"]`);
                        if (hyperRadio) hyperRadio.checked = true;
                    }
                    if (c.hypertension_meds_from_rhu) {
                        const hyperMedsRadio = document.querySelector(`input[name="hypertension_meds_from_rhu"][value="${c.hypertension_meds_from_rhu}"]`);
                        if (hyperMedsRadio) hyperMedsRadio.checked = true;
                    }
                    if (c.with_diabetes) {
                        const diabetesRadio = document.querySelector(`input[name="with_diabetes"][value="${c.with_diabetes}"]`);
                        if (diabetesRadio) diabetesRadio.checked = true;
                    }
                    if (c.diabetes_meds_from_rhu) {
                        const diabetesMedsRadio = document.querySelector(`input[name="diabetes_meds_from_rhu"][value="${c.diabetes_meds_from_rhu}"]`);
                        if (diabetesMedsRadio) diabetesMedsRadio.checked = true;
                    }
                    
                    // Clinical Information
                    if (document.querySelector('[name="diagnosis"]')) document.querySelector('[name="diagnosis"]').value = c.diagnosis || '';
                    if (document.querySelector('[name="history_of_present_illness"]')) document.querySelector('[name="history_of_present_illness"]').value = c.history_of_present_illness || '';
                    if (document.querySelector('[name="symptoms"]')) document.querySelector('[name="symptoms"]').value = c.symptoms || '';
                    if (document.querySelector('[name="assessment"]')) document.querySelector('[name="assessment"]').value = c.assessment || '';
                    if (document.querySelector('[name="physical_examination"]')) document.querySelector('[name="physical_examination"]').value = c.physical_examination || '';
                    if (document.querySelector('[name="treatment_plan"]')) document.querySelector('[name="treatment_plan"]').value = c.treatment_plan || '';
                    if (document.querySelector('[name="recommendations"]')) document.querySelector('[name="recommendations"]').value = c.recommendations || '';
                    if (document.querySelector('[name="follow_up_instructions"]')) document.querySelector('[name="follow_up_instructions"]').value = c.follow_up_instructions || '';
                    if (document.querySelector('[name="prescribed_by_name"]')) document.querySelector('[name="prescribed_by_name"]').value = c.prescribed_by_name || '';
                    
                    // Additional Settings
                    if (document.querySelector('[name="consultation_type"]')) document.querySelector('[name="consultation_type"]').value = c.consultation_type || 'walk-in';
                    if (document.querySelector('[name="priority"]')) document.querySelector('[name="priority"]').value = c.priority || 'medium';
                    if (document.querySelector('[name="consultation_location"]')) document.querySelector('[name="consultation_location"]').value = c.consultation_location || 'RHU';
                    if (document.querySelector('[name="google_meet_link"]')) document.querySelector('[name="google_meet_link"]').value = c.google_meet_link || '';
                    
                    if (c.follow_up_date && document.querySelector('[name="follow_up_date"]')) {
                        document.querySelector('[name="follow_up_date"]').value = c.follow_up_date;
                    }
                    
                    if (c.barangay_id && document.querySelector('[name="barangay_id"]')) {
                        document.querySelector('[name="barangay_id"]').value = c.barangay_id;
                    }
                    
                    // Patient Info Display
                    document.getElementById('patientInfo').innerHTML = `
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <strong><i class="fas fa-edit me-2"></i>Editing Consultation:</strong> ${c.consultation_number}<br>
                                    <strong>Patient:</strong> ${c.patient_name} (${c.patient_id})<br>
                                    <strong>Type:</strong> ${c.consultation_type} | <strong>Priority:</strong> ${c.priority}
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Load Prescriptions into NEW TABLE FORMAT
                    if (data.prescriptions && data.prescriptions.length > 0) {
                        const prescriptionList = document.getElementById('prescriptionList');
                        prescriptionList.innerHTML = '';
                        
                        data.prescriptions.forEach((rx, index) => {
                            prescriptionCount++;
                            const prescriptionRow = document.createElement('tr');
                            prescriptionRow.className = 'prescription-item';
                            prescriptionRow.innerHTML = `
                                <td>
                                    <input type="text" class="form-control form-control-sm" 
                                           name="prescriptions[${prescriptionCount}][medication_name]" 
                                           placeholder="Medicine Name" value="${rx.medication_name || ''}" required>
                                    <input type="text" class="form-control form-control-sm mt-1" 
                                           name="prescriptions[${prescriptionCount}][generic_name]" 
                                           placeholder="Generic Name" value="${rx.generic_name || ''}">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" 
                                           name="prescriptions[${prescriptionCount}][dosage_strength]" 
                                           placeholder="e.g., 500mg" value="${rx.dosage_strength || ''}">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" 
                                           name="prescriptions[${prescriptionCount}][frequency]" 
                                           placeholder="e.g., 3x a day" value="${rx.frequency || ''}">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" 
                                           name="prescriptions[${prescriptionCount}][duration]" 
                                           placeholder="e.g., 7 days" value="${rx.duration || ''}">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removePrescriptionRow(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            `;
                            prescriptionList.appendChild(prescriptionRow);
                        });
                    }
                    
                    const modal = new bootstrap.Modal(document.getElementById('consultationModal'));
                    modal.show();
                    
                } else {
                    showModalAlert('Error loading consultation details');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showModalAlert('Error loading consultation details');
            });
        }

        // DOM Ready
        document.addEventListener('DOMContentLoaded', function() {
            // Patient Search
            document.getElementById('patientSearch').addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                const search = e.target.value;
                
                if (search.length < 2) {
                    document.getElementById('searchResults').style.display = 'none';
                    return;
                }

                searchTimeout = setTimeout(() => {
                    fetch('Consultation.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'action=search_patient&search=' + encodeURIComponent(search)
                    })
                    .then(response => response.json())
                    .then(data => {
                        const resultsDiv = document.getElementById('searchResults');
                        if (data.success && data.patients.length > 0) {
                            resultsDiv.innerHTML = data.patients.map(p => `
                                <div class="patient-search-item" onclick="selectPatient(${p.id}, '${p.patient_id}', '${p.full_name.replace(/'/g, "\\'")}', ${p.age}, '${p.gender}', '${p.blood_type || 'N/A'}')">
                                    <strong>${p.patient_id}</strong> - ${p.full_name}<br>
                                    <small>${p.gender}, ${p.age} years | Blood Type: ${p.blood_type || 'Not specified'}</small>
                                </div>
                            `).join('');
                            resultsDiv.style.display = 'block';
                        } else {
                            resultsDiv.innerHTML = '<div class="p-3 text-muted">No patients found</div>';
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

            // Start Consultation
            document.getElementById('startConsultation').addEventListener('click', function() {
                if (!selectedPatientId) {
                    showAlert('Please select a patient first');
                    return;
                }

                document.getElementById('patient_id').value = selectedPatientId;
                document.getElementById('consultation_id').value = '';
                document.getElementById('modalConsultationType').value = document.getElementById('consultationType').value;

                fetch('Consultation.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=get_patient_info&patient_id=' + selectedPatientId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const patient = data.patient;
                        let historyHtml = '';
                        
                        if (data.history && data.history.length > 0) {
                            historyHtml = '<hr><h6 class="mb-2"><i class="fas fa-history me-2"></i>Recent History:</h6>';
                            data.history.slice(0, 3).forEach(h => {
                                historyHtml += `
                                    <div class="history-item">
                                        <small><strong>${new Date(h.consultation_date).toLocaleDateString()}</strong>: ${h.diagnosis}</small><br>
                                        <small class="text-muted">${h.chief_complaint.substring(0, 60)}...</small>
                                    </div>
                                `;
                            });
                        }
                        
                        document.getElementById('patientInfo').innerHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Patient:</strong> ${patient.first_name} ${patient.last_name} (${patient.patient_id})<br>
                                    <strong>Age/Gender:</strong> ${patient.age} years / ${patient.gender}<br>
                                    <strong>Blood Type:</strong> ${patient.blood_type || 'Not specified'}
                                </div>
                                <div class="col-md-6">
                                    <strong>Barangay:</strong> ${patient.barangay_name || 'Not specified'}<br>
                                    <strong>Phone:</strong> ${patient.phone || 'Not provided'}<br>
                                    <strong>Allergies:</strong> ${patient.allergies || 'None recorded'}
                                </div>
                            </div>
                            ${historyHtml}
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('patientInfo').innerHTML = '<p class="text-danger">Error loading patient information</p>';
                });

                const modal = new bootstrap.Modal(document.getElementById('consultationModal'));
                modal.show();
            });

            // BMI Calculator
            document.getElementById('weight').addEventListener('input', calculateBMI);
            document.getElementById('height').addEventListener('input', calculateBMI);

// Add prescription functionality - NEW TABLE FORMAT
        document.getElementById('addPrescription').addEventListener('click', function() {
            prescriptionCount++;
            const prescriptionList = document.getElementById('prescriptionList');
            
            // Remove "no medications" message if it exists
            const noMedsRow = prescriptionList.querySelector('td[colspan="5"]');
            if (noMedsRow) {
                noMedsRow.parentElement.remove();
            }
            
            const prescriptionRow = document.createElement('tr');
            prescriptionRow.className = 'prescription-item';
            prescriptionRow.innerHTML = `
                <td>
                    <input type="text" class="form-control form-control-sm" 
                           name="prescriptions[${prescriptionCount}][medication_name]" 
                           placeholder="Medicine Name" required>
                    <input type="text" class="form-control form-control-sm mt-1" 
                           name="prescriptions[${prescriptionCount}][generic_name]" 
                           placeholder="Generic Name">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" 
                           name="prescriptions[${prescriptionCount}][dosage_strength]" 
                           placeholder="e.g., 500mg">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" 
                           name="prescriptions[${prescriptionCount}][frequency]" 
                           placeholder="e.g., 3x a day">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" 
                           name="prescriptions[${prescriptionCount}][duration]" 
                           placeholder="e.g., 7 days">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removePrescriptionRow(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            `;
            prescriptionList.appendChild(prescriptionRow);
        });

        // Remove prescription row
        function removePrescriptionRow(btn) {
            const row = btn.closest('tr');
            row.remove();
            
            // Check if table is empty
            const prescriptionList = document.getElementById('prescriptionList');
            if (prescriptionList.children.length === 0) {
                prescriptionList.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-muted">No medications added yet. Click "Add Medicine" to add.</td>
                    </tr>
                `;
            }
        }

            // Clear form when modal is hidden
            document.getElementById('consultationModal').addEventListener('hidden.bs.modal', function() {
                document.getElementById('consultationForm').reset();
                document.getElementById('prescriptionList').innerHTML = '<p class="text-muted">No prescriptions added yet</p>';
                document.getElementById('patientInfo').innerHTML = '<p class="text-muted">Please select a patient first</p>';
                document.getElementById('consultation_id').value = '';
                document.getElementById('modalAlertMessage').style.display = 'none'; // Clear modal alert
                prescriptionCount = 0;
            });

            // Clear alert when modal opens
            document.getElementById('consultationModal').addEventListener('shown.bs.modal', function() {
                document.getElementById('modalAlertMessage').style.display = 'none';
                document.querySelector('[name="chief_complaint"]').focus();
            });

            // Auto-focus on chief complaint when modal opens
            document.getElementById('consultationModal').addEventListener('shown.bs.modal', function() {
                document.querySelector('[name="chief_complaint"]').focus();
            });
        });

        // FIXED SAVE CONSULTATION - Using simple approach like test_consultation.php
        document.body.addEventListener('click', function(e) {
            if (e.target && e.target.id === 'saveConsultation') {
                e.preventDefault();
                
                const form = document.getElementById('consultationForm');
                const patientId = document.getElementById('patient_id').value;
                const consultationId = document.getElementById('consultation_id').value;
                const chiefComplaint = form.querySelector('[name="chief_complaint"]').value;
                const diagnosis = form.querySelector('[name="diagnosis"]').value;
                
                if (!patientId) {
                    showModalAlert('Please select a patient');
                    return;
                }
                if (!chiefComplaint.trim()) {
                    showModalAlert('Please enter chief complaint');
                    return;
                }
                if (!diagnosis.trim()) {
                    showModalAlert('Please enter diagnosis');
                    return;
                }
                
                // SIMPLE FORMDATA - like test_consultation.php
                const formData = new FormData(form);
                
                // Set action based on whether we're creating or updating
                if (consultationId) {
                    formData.set('action', 'update_consultation');
                    formData.set('consultation_id', consultationId);
                } else {
                    formData.set('action', 'create_consultation');
                }
                
                // Collect prescriptions from table
                const prescriptions = [];
                document.querySelectorAll('#prescriptionList .prescription-item').forEach((row, index) => {
                    const medication_name = row.querySelector('input[name*="[medication_name]"]')?.value;
                    const generic_name = row.querySelector('input[name*="[generic_name]"]')?.value;
                    const dosage_strength = row.querySelector('input[name*="[dosage_strength]"]')?.value;
                    const frequency = row.querySelector('input[name*="[frequency]"]')?.value;
                    const duration = row.querySelector('input[name*="[duration]"]')?.value;
                    
                    if (medication_name && medication_name.trim() !== '') {
                        prescriptions.push({
                            medication_name: medication_name,
                            generic_name: generic_name || '',
                            dosage_strength: dosage_strength || '',
                            dosage_form: 'Tablet',
                            frequency: frequency || '',
                            duration: duration || '',
                            quantity: 1,
                            dosage_instructions: `${dosage_strength || ''} ${frequency || ''} for ${duration || ''}`.trim()
                        });
                    }
                });
                
                formData.set('prescriptions', JSON.stringify(prescriptions));
                formData.set('medications_prescribed', prescriptions.map(p => p.medication_name).join(', '));
                
                e.target.disabled = true;
                e.target.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';

                // SIMPLE FETCH - like test_consultation.php
                fetch('Consultation.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        
                    if (data.success) {
                        showModalAlert(data.message, 'success');
                        // Keep modal open to show success message
                        setTimeout(() => {
                            bootstrap.Modal.getInstance(document.getElementById('consultationModal')).hide();
                            location.reload();
                        }, 2000);
                    } else {
                        showModalAlert('Error: ' + data.message);
                    }
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        console.error('Response:', text);
                        showModalAlert('Server error. Please check console for details.');
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    showModalAlert('Network error: ' + error.message);
                })
                .finally(() => {
                    e.target.disabled = false;
                    e.target.innerHTML = '<i class="fas fa-save me-2"></i>Save Consultation';
                });
            }
        });

        function scheduleConsultation(id, consultationNumber, patientName) {
        document.getElementById('schedule_consultation_id').value = id;
        document.getElementById('schedulePatientInfo').innerHTML = `
            <strong>Consultation:</strong> ${consultationNumber}<br>
            <strong>Patient:</strong> ${patientName}
        `;
        
        document.getElementById('scheduled_date').value = '<?php echo date('Y-m-d'); ?>';
        
        const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
        modal.show();
    }

        document.getElementById('confirmSchedule').addEventListener('click', function() {
            const consultationId = document.getElementById('schedule_consultation_id').value;
            const scheduledDate = document.getElementById('scheduled_date').value;
            const scheduledTime = document.getElementById('scheduled_time').value;
            const priority = document.getElementById('scheduled_priority').value;
            
            if (!scheduledDate || !scheduledTime) {
                alert('Please select both date and time');
                return;
            }
            
            if (!priority) {
                alert('Please select priority level');
                return;
            }
        
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Scheduling...';
        
        const googleMeetLink = document.getElementById('scheduled_google_meet').value;

        const formData = new FormData();
        formData.append('action', 'accept_consultation');
        formData.append('consultation_id', consultationId);
        formData.append('scheduled_date', scheduledDate);
        formData.append('scheduled_time', scheduledTime);
        formData.append('priority', priority);
        if (googleMeetLink) {
            formData.append('google_meet_link', googleMeetLink);
        }
        
        fetch('Consultation.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('scheduleModal')).hide();
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred');
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-check me-2"></i>Accept & Schedule';
        });
    });

        document.getElementById('confirmSchedule').addEventListener('click', function() {
        // ... all the code above stays the same ...
    });

    // Clear schedule modal when hidden
    document.getElementById('scheduleModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('scheduled_date').value = '<?php echo date('Y-m-d'); ?>';
        document.getElementById('scheduled_time').value = '';
        document.getElementById('scheduled_priority').value = 'medium';
        document.getElementById('scheduled_google_meet').value = '';
    });

    // Table Sorting Function
    function sortTable(sortBy) {
        const table = document.querySelector('.data-table tbody');
        if (!table) return;
        
        const rows = Array.from(table.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            if (sortBy === 'priority') {
                const priorityOrder = { urgent: 1, high: 2, medium: 3, low: 4 };
                const aPriority = a.getAttribute('data-priority') || 'low';
                const bPriority = b.getAttribute('data-priority') || 'low';
                return priorityOrder[aPriority] - priorityOrder[bPriority];
            } else if (sortBy === 'date') {
                const aDate = a.cells[2].textContent.trim();
                const bDate = b.cells[2].textContent.trim();
                return new Date(bDate) - new Date(aDate);
            } else if (sortBy === 'patient') {
                const aName = a.cells[3].textContent.trim();
                const bName = b.cells[3].textContent.trim();
                return aName.localeCompare(bName);
            }
            return 0;
        });
        
        // Reorder rows
        rows.forEach(row => table.appendChild(row));
        
        // Visual feedback - NO SCROLL
        const alertDiv = document.getElementById('alertMessage');
        if (alertDiv) {
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>
                Table sorted by ${sortBy}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertDiv.style.display = 'block';
            setTimeout(() => { alertDiv.style.display = 'none'; }, 2000);
        }
    }

    // Load More Functionality (Optional - for very long lists)
    let currentPage = 1;
    const rowsPerPage = 20;

    function showMoreRows() {
        const table = document.querySelector('.data-table tbody');
        if (!table) return;
        
        const rows = Array.from(table.querySelectorAll('tr'));
        const start = currentPage * rowsPerPage;
        const end = start + rowsPerPage;
        
        for (let i = start; i < end && i < rows.length; i++) {
            rows[i].style.display = '';
        }
        
        currentPage++;
        
        if (end >= rows.length) {
            document.getElementById('loadMoreBtn')?.remove();
        }
    }
    </script>
</body>
</html>