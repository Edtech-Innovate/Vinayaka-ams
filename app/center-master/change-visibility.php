<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id']) && isset($_POST['Visibility'])) {
  

//print_r($_SERVER['REQUEST_METHOD']);die;
    require '../../includes/db-config.php';
    session_start();

    header('Content-Type: application/json');

    // Logged-in user must be Administrator
    if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'Administrator') {
        echo json_encode([
            'status' => 403,
            'message' => 'You are not authorized to change visibility!'
        ]);
        exit;
    }

    // Get values
    $id = (int) $_POST['id'];
    $visibility = (int) $_POST['Visibility'];
 

    // Visibility can only be 0 or 1
    if ($visibility !== 0 && $visibility !== 1) {
        echo json_encode([
            'status' => 400,
            'message' => 'Invalid visibility value!'
        ]);
        exit;
    }

    // Check whether center exists and was created by admin
    $check = $conn->query("
        SELECT ID, Created_By, visibility
        FROM Users
        WHERE ID = $id
        LIMIT 1
    ");

    if (!$check || $check->num_rows == 0) {
        echo json_encode([
            'status' => 302,
            'message' => 'Center does not exist!'
        ]);
        exit;
    }

    $row = $check->fetch_assoc();
//  print_r($row['Created_By']);die;

    // Normalize Created_By
    $createdBy = (int)$row['Created_By'] ?? '';

    // Only centers created by admin can have visibility changed
    if ($createdBy !== 1) {
        echo json_encode([
            'status' => 403,
            'message' => 'Visibility can only be changed for centers created by admin!'
        ]);
        exit;
    }

    // Update visibility
   $update = $conn->query("
    UPDATE Users
    SET visibility = $visibility
    WHERE ID = $id
    AND Created_By = 1
");


    if ($update) {

        echo json_encode([
            'status' => 200,
            'message' => $visibility === 1
                ? 'Center is now visible!'
                : 'Center is now not visible!',
            'visibility' => $visibility
        ]);

    } else {

        echo json_encode([
            'status' => 302,
            'message' => 'Unable to update visibility!'
        ]);
    }
}
