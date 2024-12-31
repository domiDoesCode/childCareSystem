<?php
header('Content-Type: application/json');
require '../config/db.php';

if (isset($_GET['details']) && $_GET['details'] === 'true') {
    // Fetch detailed diet entries
    $stmt = $conn->prepare("
        SELECT 
            children.name AS child_name,
            meal_types.name AS meal_type,
            food_items.name AS food_item, 
            diet_food_items.diet_id,
            diet.date_recorded 
        FROM diet
        JOIN children ON diet.child_id = children.id
        JOIN meal_types ON diet.meal_type_id = meal_types.id
        JOIN diet_food_items ON diet.id = diet_food_items.diet_id
        JOIN food_items ON diet_food_items.food_item_id = food_items.id
        WHERE DATE(diet.date_recorded) = CURDATE()
        ORDER BY diet.date_recorded DESC;

    ");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $dietSummary = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'summary' => $dietSummary]);
    } else {
        echo json_encode(['status' => 'success', 'summary' => []]);
    }

    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch summary diet entries (default behavior)
    $stmt = $conn->prepare("
        SELECT 
            meal_types.name AS meal_type, 
            COUNT(*) AS total_entries
        FROM 
            diet
        JOIN 
            meal_types ON diet.meal_type_id = meal_types.id
        WHERE 
            DATE(diet.date_recorded) = CURDATE()
        GROUP BY 
            meal_types.name
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $dietSummary = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'summary' => $dietSummary]);
    } else {
        echo json_encode(['status' => 'success', 'summary' => []]);
    }

    $stmt->close();
}

$conn->close();
?>
