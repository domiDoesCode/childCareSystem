<?php
header('Content-Type: application/json');
require '../config/db.php';

if (isset($_GET['details']) && $_GET['details'] === 'true') {
    // Detailed view: Fetch all activity entries for today
    $stmt = $conn->prepare("
        SELECT 
            children.name AS child_name,
            activity_types.name AS activity_type,
            activity_definitions.name AS activity,
            activities.date_recorded 
        FROM activities
        JOIN children ON activities.child_id = children.id
        JOIN activity_types ON activities.activity_type_id = activity_types.id
        JOIN activities_activity ON activities.id = activities_activity.activities_id
        JOIN activity_definitions ON activities_activity.activity_id = activity_definitions.id
        WHERE DATE(activities.date_recorded) = CURDATE()
        ORDER BY activities.date_recorded DESC;
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $activitySummary = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'summary' => $activitySummary]);
    } else {
        echo json_encode(['status' => 'success', 'summary' => []]);
    }

    $stmt->close();
} else {
    // Summary view: Count of entries grouped by activity type
    $stmt = $conn->prepare("
        SELECT 
            activity_types.name AS activity_type, 
            COUNT(*) AS total_entries
        FROM activities
        JOIN activity_types ON activities.activity_type_id = activity_types.id
        WHERE DATE(activities.date_recorded) = CURDATE()
        GROUP BY activity_types.name;
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $activitySummary = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'summary' => $activitySummary]);
    } else {
        echo json_encode(['status' => 'success', 'summary' => []]);
    }

    $stmt->close();
}

$conn->close();
