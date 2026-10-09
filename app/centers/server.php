<?php
## Database configuration
include '../../includes/db-config.php';
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

## Read value
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$row = isset($_POST['start']) ? intval($_POST['start']) : 0;
$rowperpage = isset($_POST['length']) ? intval($_POST['length']) : 25; // Rows display per page

if(isset($_POST['order'])){
  $columnIndex = $_POST['order'][0]['column']; // Column index
  $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
  $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
}

$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value

if(isset($columnSortOrder) && !empty($columnName)){
  $orderby = "ORDER BY $columnName $columnSortOrder";
}else{
  $orderby = "ORDER BY Users.ID DESC, University_User.Level DESC";
}

// Session Query
$session_query = "";
$current_session = $conn->query("SELECT ID FROM Admission_Sessions WHERE University_ID = ".intval($_SESSION['university_id'])." AND Current_Status = 1");
if($current_session && $current_session->num_rows > 0){
  $current_session = mysqli_fetch_assoc($current_session);
  $session_query = " AND Admission_Session_ID = ".intval($current_session['ID']);
}

$center_query = "";
if($_SESSION['Role'] != "Administrator"){
  $check_has_unique_center_code = $conn->query("SELECT Center_Suffix FROM Universities WHERE ID = ".intval($_SESSION['university_id'])." AND Has_Unique_Center = 1");
  if($check_has_unique_center_code && $check_has_unique_center_code->num_rows > 0){
    $center_suffix = mysqli_fetch_assoc($check_has_unique_center_code);
    $center_suffix = mysqli_real_escape_string($conn, $center_suffix['Center_Suffix']);
    $center_query = " AND Users.Code LIKE '$center_suffix%' AND Users.Is_Unique = 1";
  }else{
    $center_query = " AND Users.Is_Unique = 0";
  }
}

if ($_SESSION['Role'] == 'Sub-Counsellor' || $_SESSION['Role'] == 'Counsellor') {
  $center_ids = [];
  $user_ids = [];
  $getcenterids = $conn->query("SELECT User_ID FROM University_User WHERE Reporting = " . intval($_SESSION['ID']));
  if ($getcenterids && $getcenterids->num_rows > 0) {
      $centerids = $getcenterids->fetch_all(MYSQLI_ASSOC);
      $center_ids = array_column($centerids, 'User_ID');
  }
  $getnotalloteduser = $conn->query("SELECT Users.ID FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID WHERE Users.Role = 'Center' AND University_User.User_ID IS NULL");
  if ($getnotalloteduser && $getnotalloteduser->num_rows > 0) {
      while ($dataArr = $getnotalloteduser->fetch_assoc()) {
          $user_ids[] = intval($dataArr['ID']);
      }
  }
  $centers = array_merge($center_ids, $user_ids);
  if (!empty($centers)) {
      $center_ids_str = implode(',', array_map('intval', $centers)); // Secure against SQL injection
      $center_query .= " AND Users.ID IN ($center_ids_str) ";
  }
}

$counselloer_query = "";
if ($_SESSION['Role'] == 'University Head') {
  $center_ids = [];
  $getcounselloer = $conn->query("SELECT User_ID FROM University_User WHERE Reporting = " . intval($_SESSION['ID']));
  if ($getcounselloer && $getcounselloer->num_rows > 0) {
      $centerids = $getcounselloer->fetch_all(MYSQLI_ASSOC);
      $center_ids = array_column($centerids, 'User_ID');
    if (!empty($center_ids)) {
      $center_ids_str = implode(',', array_map('intval', $center_ids)); 
      $counselloer_query = " AND Reporting IN ($center_ids_str) ";
      $getcounselloer = $conn->query("SELECT GROUP_CONCAT(University_User.User_ID) AS sc_ids FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID WHERE 1=1 AND Role = 'Center' $counselloer_query");
      if ($getcounselloer && $getcounselloer->num_rows > 0) {
        $sc_row = $getcounselloer->fetch_assoc()['sc_ids'];
        if (!empty($sc_row)) {
          $sc_ids_arr = explode(',', $sc_row);
          
          // NEW LOGIC: Ensure that if a center is retrieved for University Head, 
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
            $center_query .= " AND Users.ID IN(".$filtered_sc_str.")";
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
$searchQuery = " ";
if($searchValue != ''){
  $searchQuery = " AND (Users.Name like '%".$searchValue."%' OR Users.Code like '%".$searchValue."%' OR Users.Email like '%".$searchValue."%' OR Users.Mobile like '%%".$searchValue."%')";
}

## Total number of records without filtering
$all_count = $conn->query("SELECT Users.ID FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID LEFT JOIN Users as RM ON University_User.Reporting = RM.ID WHERE Users.Role = 'Center' AND University_ID = ".intval($_SESSION['university_id'])." $center_query GROUP BY Users.ID");
$totalRecords = ($all_count) ? $all_count->num_rows : 0;

## Total number of record with filtering
$filter_count = $conn->query("SELECT Users.ID FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID LEFT JOIN Users as RM ON University_User.Reporting = RM.ID WHERE Users.Role = 'Center' AND University_ID = ".intval($_SESSION['university_id'])." $center_query $searchQuery GROUP BY Users.ID");
$totalRecordwithFilter = ($filter_count) ? $filter_count->num_rows : 0;

## Fetch records
$result_record = "SELECT Users.ID, Users.Name, Users.CanCreateSubCenter, Users.Email, Users.Mobile, Users.Code, Users.Status, Users.Photo, CAST(AES_DECRYPT(Users.Password, '60ZpqkOnqn0UQQ2MYTlJ') AS CHAR(50)) password, University_User.Reporting, University_User.`Level`, RM.Name as RMName, RM.Code as RMCode FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID LEFT JOIN Users as RM ON University_User.Reporting = RM.ID WHERE Users.Role = 'Center' AND University_User.`Level` = (SELECT MAX(`Level`) FROM University_User WHERE University_User.User_ID = Users.ID AND University_User.University_ID = ".intval($_SESSION['university_id']).") AND University_ID = ".intval($_SESSION['university_id'])." $center_query $searchQuery GROUP BY User_ID $orderby LIMIT ".intval($row).",".intval($rowperpage);
$empRecords = mysqli_query($conn, $result_record);
$data = array();

if ($empRecords) {
  while ($row = mysqli_fetch_assoc($empRecords)) {

    // Admission Count
    $alloted_centers = array($row['ID']);
    $added_for = $alloted_centers;
    $alloted_sub_centers = [];
    $sub_centers = $conn->query("SELECT ID FROM Users LEFT JOIN University_User ON Users.ID = University_User.User_ID WHERE Users.Role = 'Sub-Center' AND Reporting = ".intval($row['ID'])."");
    if($sub_centers && $sub_centers->num_rows > 0){
      while($sub_center = $sub_centers->fetch_assoc()){
        $alloted_sub_centers[] = intval($sub_center['ID']);
      }
      $added_for = array_filter(array_merge($alloted_centers, $alloted_sub_centers));
    }

    $admissions = $conn->query("SELECT COUNT(ID) as Applications FROM Students WHERE Added_For IN (".implode(',', $added_for).") AND Step = 4 $session_query");
    $admissions = ($admissions) ? mysqli_fetch_assoc($admissions) : ['Applications' => 0];
    
    $data[] = array(  
      "Photo"=> $row['Photo'],
      "Name" => $row['Name'],
      "Email" => function_exists('stringToSecret') ? stringToSecret($row['Email']) : $row['Email'],
      "Mobile" => function_exists('stringToSecret') ? stringToSecret($row['Mobile']) : $row['Mobile'],
      "Code" => $row['Code'],
      "Password" => $row['password'],
      "Admission" => isset($admissions['Applications']) ? $admissions['Applications'] : 0,
      "RM" => $row['RMName'].' ('.$row['RMCode'].')',
      "CanCreateSubCenter" => $row['CanCreateSubCenter'],
      "Status" => $row["Status"],
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