<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '0');

require '../../includes/db-config.php';
session_start();

if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'Administrator') {
    echo json_encode(['status' => 403, 'message' => 'Unauthorized Access footprint verification failed.']);
    exit();
}

$university_id = isset($_POST['university_id']) ? intval($_POST['university_id']) : 0;
$permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];

if($university_id === 0) {
    echo json_encode(['status' => 400, 'message' => 'Malformed request parameters tracking context identity signature.']);
    exit();
}

$pages_res = $conn->query("SELECT ID FROM `Pages`");
$valid_page_ids = [];
while($p = $pages_res->fetch_assoc()) { $valid_page_ids[] = intval($p['ID']); }

$execution_error = false;

foreach($valid_page_ids as $page_id) {
    $inh = isset($permissions[$page_id]['inh']) ? 1 : 0;
    $cnt = isset($permissions[$page_id]['cnt']) ? 1 : 0;
    $sub = isset($permissions[$page_id]['sub']) ? 1 : 0;
    $std = isset($permissions[$page_id]['std']) ? 1 : 0;

    $check = $conn->query("SELECT ID FROM `Page_Access` WHERE `Page_ID` = $page_id AND `University_ID` = $university_id");
    
    if($check && $check->num_rows > 0) {
        $stmt = $conn->query("UPDATE `Page_Access` SET 
            `Inhouse` = $inh, 
            `Center` = $cnt, 
            `Sub_Center` = $sub, 
            `Student` = $std 
            WHERE `Page_ID` = $page_id AND `University_ID` = $university_id");
    } else {
        $stmt = $conn->query("INSERT INTO `Page_Access` (`Page_ID`, `University_ID`, `Inhouse`, `Center`, `Sub_Center`, `Student`) 
            VALUES ($page_id, $university_id, $inh, $cnt, $sub, $std)");
    }

    if(!$stmt) { $execution_error = true; }
}

echo json_encode([
    'status' => !$execution_error ? 200 : 400,
    'message' => !$execution_error ? 'Permissions layout configured successfully!' : 'Error executing matrix transactional items updates.'
]);