<?php
header('Content-Type: application/json');
require '../config/db.php';

if (isset($_GET['details']) && $_GET['details'] === 'true') {
    // Detailed view: Fetch all nappy entries for today
    $stmt = $conn->prepare("
        SELECT 
            children.name AS child_name,
            nappy_types.name AS nappy_type,
            nappy_entries.date_recorded 
        FROM nappy_entries
        JOIN children ON nappy_entries.child_id = children.id
        JOIN nappy_types ON nappy_entries.nappy_type_id = nappy_types.id
        WHERE DATE(nappy_entries.date_recorded) = CURDATE()
        ORDER BY nappy_entries.date_recorded DESC;
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $nappySummary = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'summary' => $nappySummary]);
    } else {
        echo json_encode(['status' => 'success', 'summary' => []]);
    }

    $stmt->close();
} else {
    // Summary view: Count of entries grouped by nappy type
    $stmt = $conn->prepare("
        SELECT 
            nappy_types.name AS nappy_type, 
            COUNT(*) AS total_entries
        FROM nappy_entries
        JOIN nappy_types ON nappy_entries.nappy_type_id = nappy_types.id
        WHERE DATE(nappy_entries.date_recorded) = CURDATE()
        GROUP BY nappy_types.name;
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $nappySummary = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'summary' => $nappySummary]);
    } else {
        echo json_encode(['status' => 'success', 'summary' => []]);
    }

    $stmt->close();
}

$conn->close();
