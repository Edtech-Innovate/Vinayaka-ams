<?php
ini_set('display_errors', 1);

## Database configuration
include '../../includes/db-config.php';
session_start();

## Read value
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$row = isset($_POST['start']) ? intval($_POST['start']) : 0;
$rowperpage = isset($_POST['length']) ? intval($_POST['length']) : 25; // Rows display per page
if (isset($_POST['order'])) {
  $columnIndex = $_POST['order'][0]['column']; // Column index
  $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
  $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
}
$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value

if (isset($columnSortOrder) && !empty($columnName)) {
  $orderby = "ORDER BY $columnName $columnSortOrder";
} else {
  $orderby = "ORDER BY Users.ID ASC";
}

$session_query = "";
$university_query = '';

if ($_SESSION['Role'] == 'Counsellor') {
  $university_query = " AND University_User.University_ID = " . intval($_SESSION['university_id']) . " AND University_User.Reporting = " . intval($_SESSION['ID']);
  $current_session = $conn->query("SELECT ID FROM Admission_Sessions WHERE University_ID = " . intval($_SESSION['university_id']) . " AND Current_Status = 1");
  if ($current_session && $current_session->num_rows > 0) {
    $current_session = mysqli_fetch_assoc($current_session);
    $session_query = " AND Admission_Session_ID = " . intval($current_session['ID']);
  }
} 

if ($_SESSION['Role'] != 'Administrator' && $_SESSION['Role'] != 'Counsellor') {
  $university_query = " AND University_User.University_ID = " . intval($_SESSION['university_id']);
  $current_session = $conn->query("SELECT ID FROM Admission_Sessions WHERE University_ID = " . intval($_SESSION['university_id']) . " AND Current_Status = 1");
  if ($current_session && $current_session->num_rows > 0) {
    $current_session = mysqli_fetch_assoc($current_session);
    $session_query = " AND Admission_Session_ID = " . intval($current_session['ID']);
  }
}

$center_query = '';
$counselloer_query = "";
if ($_SESSION['Role'] == 'University Head') {
  $center_ids = [];
  $getcounselloer = $conn->query("SELECT User_ID FROM University_User WHERE Reporting = " . intval($_SESSION['ID']));
  if ($getcounselloer && $getcounselloer->num_rows > 0) {
    $centerids = $getcounselloer->fetch_all(MYSQLI_ASSOC);
    $center_ids = array_column($centerids, 'User_ID');
    if (!empty($center_ids)) {
      $center_ids_str = implode(',', array_map('intval', $center_ids)); 
      $counselloer_query = " AND University_User.Reporting IN ($center_ids_str) ";
      
      $getcounselloer = $conn->query("SELECT GROUP_CONCAT(University_User.User_ID) AS sc_ids FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID WHERE 1=1 AND Role = 'Sub-Counsellor' $counselloer_query");
        
      if ($getcounselloer && $getcounselloer->num_rows > 0) {
        $sc_row = $getcounselloer->fetch_assoc()['sc_ids'] ?? 0;
        if (!empty($sc_row) && $sc_row != 0) {
          $sc_ids_arr = explode(',', $sc_row);
          
          // NEW LOGIC: Ensure that if a sub-counsellor is retrieved, 
          // it must also be allotted to a university that the University Head has access to.
          $uni_ids = [];
          $getUnis = $conn->query("SELECT University_ID FROM University_User WHERE User_ID = " . intval($_SESSION['ID']));
          if ($getUnis && $getUnis->num_rows > 0) {
            while ($u = $getUnis->fetch_assoc()) {
              $uni_ids[] = intval($u['University_ID']);
            }
          }

          $filtered_sc_ids = [];
          $sc_ids_str = implode(',', array_map('intval', $sc_ids_arr));

          if (!empty($uni_ids)) {
            $uni_ids_str = implode(',', $uni_ids);
            $validateSc = $conn->query("SELECT DISTINCT User_ID FROM University_User WHERE User_ID IN ($sc_ids_str) AND University_ID IN ($uni_ids_str)");
          } else {
            $validateSc = $conn->query("SELECT DISTINCT User_ID FROM University_User WHERE User_ID IN ($sc_ids_str)");
          }

          if ($validateSc && $validateSc->num_rows > 0) {
            while ($vc = $validateSc->fetch_assoc()) {
              $filtered_sc_ids[] = intval($vc['User_ID']);
            }
          }

          if (!empty($filtered_sc_ids)) {
            $filtered_sc_str = implode(',', array_unique($filtered_sc_ids));
            $center_query .= " AND Users.ID IN(" . $filtered_sc_str . ")";
          } else {
            $center_query .= " AND 1 = 0";
          }
        } else {
          $center_query .= " AND 1 = 0";
        }
      } 
    }
  }
}

## Search 
$searchQuery = " " . $center_query;
if ($searchValue != '') {
  $searchQuery .= " AND (Users.Name like '%" . $searchValue . "%' OR Users.Code like '%" . $searchValue . "%' OR Users.Email like '%" . $searchValue . "%' OR Users.Mobile like '%" . $searchValue . "%')";
}

## Total number of records without filtering
$all_count = $conn->query("SELECT Users.ID FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID WHERE Role = 'Sub-Counsellor' $university_query $center_query GROUP BY Users.ID");
$totalRecords = ($all_count) ? $all_count->num_rows : 0;

## Total number of record with filtering
$filter_count = $conn->query("SELECT Users.ID FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID WHERE Users.Role = 'Sub-Counsellor' $university_query $searchQuery GROUP BY Users.ID");
$totalRecordwithFilter = ($filter_count) ? $filter_count->num_rows : 0;

## Fetch records
$result_record = "SELECT Users.`ID`, Users.`Name`, Users.`Email`, Users.`Mobile`, Users.`Code`, Users.`Status`, Users.`Photo`, CONCAT(Users.Name, ' (', Users.Code, ')') AS `Reporting`, CAST(AES_DECRYPT(Users.Password, '60ZpqkOnqn0UQQ2MYTlJ') AS CHAR(50)) password FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID WHERE Users.Role = 'Sub-Counsellor' $university_query $searchQuery GROUP BY Users.ID $orderby LIMIT " . intval($row) . "," . intval($rowperpage);
$empRecords = mysqli_query($conn, $result_record);
$data = array();

if ($empRecords) {
  while ($row = mysqli_fetch_assoc($empRecords)) {
    $alloted = [];
    $alloted_universities = $conn->query("SELECT CONCAT(Universities.Short_Name, ' (', Universities.Vertical, ')') as University FROM University_User LEFT JOIN Universities ON University_User.University_ID = Universities.ID WHERE `User_ID` = " . intval($row['ID']) . "");
    if ($alloted_universities && $alloted_universities->num_rows > 0) {
      while ($alloted_university = $alloted_universities->fetch_assoc()) {
        $alloted[] = $alloted_university['University'];
      }
    }

    $data[] = array(
      "Photo" => $row['Photo'],
      "Name" => $row['Name'],
      "Email" => $row['Email'],
      "Mobile" => $row['Mobile'],
      "Code" => $row['Code'],
      "Reporting" => $row['Reporting'],
      "Password" => $row['password'],
      "Status" => $row["Status"],
      "University" => implode(', ', $alloted),
      "ID" => $row["ID"],
    );
  }
}

## Response
$response = array(
  "draw" => intval($draw),
  "iTotalRecords" => intval($totalRecords),
  "iTotalDisplayRecords" => intval($totalRecordwithFilter),
  "aaData" => $data
);

echo json_encode($response);
exit();