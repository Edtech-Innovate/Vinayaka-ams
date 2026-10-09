<?php
## Database configuration
include '../../includes/db-config.php';
session_start();

## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page

if (isset($_POST['order'])) {
  $columnIndex = $_POST['order'][0]['column']; // Column index
  $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
  $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
}

$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']); // Search value

if (isset($columnSortOrder)) {
  $orderby = "ORDER BY Users.ID ASC";
} else {
  $orderby = "ORDER BY Users.ID ASC";
}

// Role-based condition for Counsellor
$counsellorQuery = "";
if (isset($_SESSION['Role']) && $_SESSION['Role'] === 'Counsellor' && !empty($_SESSION['ID'])) {
    $counsellorId = intval($_SESSION['ID']);
    
    // Find all User_IDs where Reporting matches either directly OR inside a comma-separated string
    $counsellorQuery = " AND Users.ID IN (
        SELECT DISTINCT Reporting 
        FROM University_User 
        WHERE User_ID = '$counsellorId' 
           OR FIND_IN_SET('$counsellorId', User_ID) > 0
    )";
}

//print_r($counsellorQuery);die;

## Search 
$searchQuery = " ";
if ($searchValue != '') {
  $searchQuery = " AND (Users.Name LIKE '%" . $searchValue . "%' OR Users.Code LIKE '%" . $searchValue . "%' OR Users.Email LIKE '%" . $searchValue . "%' OR Users.Mobile LIKE '%" . $searchValue . "%')";
}

## Base query condition (matching University Head role)
$baseWhere = "WHERE Users.Role = 'University Head' $counsellorQuery $searchQuery";

## Total number of records without filtering
$all_count = $conn->query("SELECT COUNT(Users.ID) as allcount FROM Users WHERE Users.Role = 'University Head' $counsellorQuery");
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records ? $records['allcount'] : 0;

## Total number of record with filtering
$filter_count = $conn->query("SELECT COUNT(Users.ID) as filtered FROM Users $baseWhere");
$records = mysqli_fetch_assoc($filter_count);
$totalRecordwithFilter = $records ? $records['filtered'] : 0;

## Fetch records
$result_record = "SELECT Users.`ID`, Users.`Name`, Users.`Email`, Users.`Code`, Users.`Status`, Users.`Photo`, CAST(AES_DECRYPT(Users.Password, '60ZpqkOnqn0UQQ2MYTlJ') AS CHAR(50)) as password FROM Users $baseWhere $orderby LIMIT " . intval($row) . "," . intval($rowperpage);

$empRecords = mysqli_query($conn, $result_record);
$data = array();

if ($empRecords) {
  while ($row = mysqli_fetch_assoc($empRecords)) {
    $alloted = [];
    $alloted_universities = $conn->query("SELECT CONCAT(Universities.Short_Name, ' (', Universities.Vertical, ')') as University FROM University_User LEFT JOIN Universities ON University_User.University_ID = Universities.ID WHERE `User_ID` = " . intval($row['ID']));
    
    if ($alloted_universities && $alloted_universities->num_rows > 0) {
      while ($alloted_university = $alloted_universities->fetch_assoc()) {
        if (!empty($alloted_university['University'])) {
          $alloted[] = $alloted_university['University'];
        }
      }
    }

    $data[] = array( 
      "Photo"      => $row['Photo'],
      "Name"       => $row['Name'],
      "Email"      => $row['Email'],
      "Code"       => $row['Code'],
      "Password"   => $row['password'],
      "Status"     => $row["Status"],
      "University" => implode('<br>', $alloted),
      "ID"         => $row["ID"],
    );
  }
}

## Response
$response = array(
  "draw"                 => intval($draw),
  "iTotalRecords"        => intval($totalRecords),
  "iTotalDisplayRecords" => intval($totalRecordwithFilter),
  "aaData"               => $data
);

echo json_encode($response);