<?php
header('Content-Type: application/json');
require '../config/db.php';

if (isset($_GET['details']) && $_GET['details'] === 'true') {
    // Detailed view: Fetch all health entries for today
    $stmt = $conn->prepare("
        SELECT 
            children.name AS child_name,
            health_entries.temperature,
            GROUP_CONCAT(DISTINCT symptoms.name SEPARATOR ', ') AS symptoms,
            GROUP_CONCAT(DISTINCT medications.name SEPARATOR ', ') AS medications,
            health_entries.date_recorded 
        FROM health_entries
        JOIN children ON health_entries.child_id = children.id
        LEFT JOIN health_symptoms ON health_entries.id = health_symptoms.health_entry_id
        LEFT JOIN symptoms ON health_symptoms.symptom_id = symptoms.id
        LEFT JOIN health_medications ON health_entries.id = health_medications.health_entry_id
        LEFT JOIN medications ON health_medications.medication_id = medications.id
        WHERE DATE(health_entries.date_recorded) = CURDATE()
        GROUP BY health_entries.id
        ORDER BY health_entries.date_recorded DESC;
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $healthSummary = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'summary' => $healthSummary]);
    } else {
        echo json_encode(['status' => 'success', 'summary' => []]);
    }

    $stmt->close();
} else {
    // Summary view: Count of entries and temperature ranges
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) AS total_entries,
            MIN(temperature) AS min_temperature,
            MAX(temperature) AS max_temperature
        FROM health_entries
        WHERE DATE(date_recorded) = CURDATE();
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $healthSummary = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'summary' => $healthSummary]);
    } else {
        echo json_encode(['status' => 'success', 'summary' => []]);
    }

    $stmt->close();
}

$conn->close();
