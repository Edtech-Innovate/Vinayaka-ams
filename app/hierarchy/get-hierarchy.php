<?php
// /app/hierarchy/get-hierarchy.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

include_once '../../includes/db-config.php';
session_start();

if (!isset($_SESSION['ID']) || !isset($_SESSION['university_id'])) {
    echo json_encode(['status' => false, 'message' => 'Unauthorized session or university ID missing']);
    exit();
}

$university_id = intval($_SESSION['university_id']);
$current_user_id = intval($_SESSION['ID']);

function getChainUpward($conn, $userId, $universityId) {
    $chain = [];
    $currentId = $userId;
    $visited = [];
    
    while ($currentId && !isset($visited[$currentId])) {
        $visited[$currentId] = true;
        
        $stmt = $conn->prepare("SELECT u.ID, u.Name, u.Email, u.Mobile as Contact, u.Role, u.Photo, uu.Reporting 
                                FROM Users u 
                                LEFT JOIN University_User uu ON u.ID = uu.User_ID AND uu.University_ID = ? 
                                WHERE u.ID = ?");
        if (!$stmt) break;
        $stmt->bind_param("ii", $universityId, $currentId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $reporting = $row['Reporting'];
            unset($row['Reporting']);
            $chain[] = $row;
            if ($reporting == $currentId || !$reporting) break; 
            $currentId = $reporting;
        } else {
            break;
        }
    }
    return $chain;
}

function getSubordinates($conn, $userId, $universityId, &$visited = []) {
    $subs = [];
    if (isset($visited[$userId])) {
        return $subs;
    }
    $visited[$userId] = true;

    $stmt = $conn->prepare("SELECT u.ID, u.Name, u.Email, u.Mobile as Contact, u.Role, u.Photo 
                            FROM University_User uu 
                            JOIN Users u ON uu.User_ID = u.ID 
                            WHERE uu.Reporting = ? AND uu.University_ID = ?");
    if (!$stmt) return $subs;
    $stmt->bind_param("ii", $userId, $universityId);
    $stmt->execute();
    $res = $stmt->get_result();
    
    while ($row = $res->fetch_assoc()) {
        $subId = $row['ID'];
        $branchVisited = $visited; 
        $row['subordinates'] = getSubordinates($conn, $subId, $universityId, $branchVisited);
        $subs[] = $row;
    }
    return $subs;
}

$upwardChain = getChainUpward($conn, $current_user_id, $university_id);
$upwardChain = array_reverse($upwardChain);
$downwardTree = getSubordinates($conn, $current_user_id, $university_id);

echo json_encode([
    'status' => true,
    'hierarchy_path' => $upwardChain,
    'subordinates' => $downwardTree
]);
exit();