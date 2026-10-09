<?php
## Database configuration
include '../../includes/db-config.php';
session_start();
ini_set('display_errors', 1);

## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
if (isset($_POST['order'])) {
  $columnIndex = $_POST['order'][0]['column']; // Column index
  $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
  $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
}

if (isset($columnSortOrder)) {
  $orderby = "ORDER BY $columnName $columnSortOrder";
} else {
  $orderby = "ORDER BY trash_students.ID DESC";
}

## Role & Session variables
$role = isset($_SESSION['Role']) ? $_SESSION['Role'] : '';
$user_id = isset($_SESSION['ID']) ? (int)$_SESSION['ID'] : 0;
$university_id = isset($_SESSION['university_id']) ? (int)$_SESSION['university_id'] : 0;

## Base University filter
$where = " WHERE trash_students.University_ID = " . $university_id;

## Dynamic Hierarchical Role-Based Access Control
if ($role == 'Center') {
  // Center sees only their own added records
  $where .= " AND trash_students.Added_For = " . $user_id;
} elseif (in_array($role, ['Counsellor', 'University Head'])) {
  // Recursive CTE to fetch all downstream User_IDs reporting hierarchically under $user_id
  $where .= " AND trash_students.Added_For IN (
    WITH RECURSIVE Hierarchy AS (
      SELECT User_ID 
      FROM University_User 
      WHERE Reporting = " . $user_id . " AND University_ID = " . $university_id . "
      
      UNION ALL
      
      SELECT uu.User_ID 
      FROM University_User uu
      INNER JOIN Hierarchy h ON uu.Reporting = h.User_ID
      WHERE uu.University_ID = " . $university_id . "
    )
    SELECT User_ID FROM Hierarchy
  )";
}
// Note: Administrator automatically bypasses hierarchy restrictions and sees all records matching University_ID

## Total number of records (Filtered by Role Hierarchy and University_ID)
$all_count = $conn->query("SELECT COUNT(trash_students.ID) as allcount FROM trash_students " . $where);
$records = mysqli_fetch_assoc($all_count);
$totalRecords = $records['allcount'];
$totalRecordwithFilter = $totalRecords;

## Fetch records matching hierarchical filters
$result_record = "SELECT Student_Pendencies.ID as Pendency, Student_Pendencies.Status as Pendency_Status, 
  UPPER(DATE_FORMAT(trash_students.DOB, '%d%b%Y')) as DOB, 
  trash_students.Status, trash_students.ID, trash_students.Added_For, trash_students.Unique_ID, 
  CONCAT(trash_students.First_Name, ' ', trash_students.Middle_Name, ' ', trash_students.Last_Name) as First_Name, 
  trash_students.Father_Name, trash_students.Enrollment_No, trash_students.OA_Number, trash_students.Duration, trash_students.Step, 
  trash_students.Process_By_Center, trash_students.Payment_Received, trash_students.Document_Verified, trash_students.Processed_To_University, 
  Admission_Sessions.`Name` as Adm_Session, Admission_Types.`Name` as Adm_Type, 
  CONCAT(Courses.Short_Name, ' (', Sub_Courses.Name, ')') as Short_Name, Student_Documents.`Location`, 
  trash_students.ID_Card, trash_students.Admit_Card, trash_students.Exam,
  trash_students.Aadhar_Number,
  DATE_FORMAT(CONVERT_TZ(trash_students.Created_At, '+00:00', '+05:30'), '%d-%m-%Y %h:%i %p') as Created_At 
  FROM trash_students 
  LEFT JOIN Student_Pendencies ON trash_students.ID = Student_Pendencies.Student_ID AND Student_Pendencies.Status != 1 
  LEFT JOIN Admission_Sessions ON trash_students.Admission_Session_ID = Admission_Sessions.ID 
  LEFT JOIN Admission_Types ON trash_students.Admission_Type_ID = Admission_Types.ID 
  LEFT JOIN Courses ON trash_students.Course_ID = Courses.ID 
  LEFT JOIN Sub_Courses ON trash_students.Sub_Course_ID = Sub_Courses.ID 
  LEFT JOIN Student_Documents ON trash_students.ID = Student_Documents.Student_ID AND Student_Documents.`Type` = 'Photo' 
  " . $where . " 
  $orderby LIMIT " . $row . "," . $rowperpage;

$empRecords = mysqli_query($conn, $result_record);
$data = array();

while ($row = mysqli_fetch_assoc($empRecords)) {

  // Added_For User Details
  if (isset($_SESSION['Role']) && $_SESSION['Role'] == 'Center') {
    $user = $conn->query("SELECT ID, Code, Name FROM Users WHERE ID = " . $row['Added_For']);
  } else {
    $user = $conn->query("SELECT ID, Code, Name FROM Users WHERE ID = " . $row['Added_For']);
    if ($user && $user->num_rows == 0) {
      $user = $conn->query("SELECT Users.ID, Code, Name FROM Users LEFT JOIN Center_SubCenter ON Users.ID = Center_SubCenter.Center WHERE `Sub_Center` = " . $row['Added_For']);
    }
  }

  $user = $user ? mysqli_fetch_array($user) : null;

  // Reporting Manager (RM)
  $rm['Name'] = "";
  if (!empty($user) && !empty($university_id)) {
    $rm = $conn->query("SELECT CONCAT(Users.Name, ' (', Users.Code, ')') as Name FROM University_User LEFT JOIN Users ON University_User.Reporting = Users.ID AND University_User.University_ID = " . $university_id . " WHERE University_User.`User_ID` = " . $user['ID'] . " AND University_User.University_ID = " . $university_id);
    if ($rm && $rm->num_rows > 0) {
      $rm = mysqli_fetch_array($rm);
    } else {
      $rm = $user;
    }
  }

  $data[] = array(
    "Photo" => empty($row['Location']) ? '/assets/img/default-user.png' : $row['Location'],
    "First_Name" => $row['First_Name'],
    "Father_Name" => $row['Father_Name'],
    "Unique_ID" => empty($row['Unique_ID']) ? sprintf("%'.06d\n", $row['ID']) : $row['Unique_ID'],
    "Enrollment_No" => !empty($row['Enrollment_No']) ? $row['Enrollment_No'] : '',
    "OA_Number" => !empty($row['OA_Number']) ? $row['OA_Number'] : '',
    "Duration" => $row['Duration'],
    "Step" => $row['Step'],
    "Process_By_Center" => !empty($row['Process_By_Center']) ? date("d-m-Y", strtotime($row['Process_By_Center'])) : "1",
    "Payment_Received" => !empty($row['Payment_Received']) ? date("d-m-Y", strtotime($row['Payment_Received'])) : "1",
    "Document_Verified" => !empty($row['Document_Verified']) ? date("d-m-Y", strtotime($row['Document_Verified'])) : "1",
    "Processed_To_University" => !empty($row['Processed_To_University']) ? date("d-m-Y", strtotime($row['Processed_To_University'])) : '1',
    "Adm_Session" => $row['Adm_Session'],
    "Adm_Type" => $row['Adm_Type'],
    "Short_Name" => $row['Short_Name'],
    "Center_Code" => isset($user['Code']) ? $user['Code'] : '',
    "Center_Name" => isset($user['Name']) ? $user['Name'] : '',
    "RM" => isset($rm['Name']) ? $rm['Name'] : '',
    "Status" => $row['Status'],
    "DOB" => $row['DOB'],
    "Aadhar_Number" => !empty($row['Aadhar_Number']) ? $row['Aadhar_Number'] : '',
    "Created_At" => !empty($row['Created_At']) ? $row['Created_At'] : '',
    "ID_Card" => $row['ID_Card'],
    "Admit_Card" => $row['Admit_Card'],
    "Exam" => $row['Exam'],
    "Pendency" => empty($row['Pendency']) ? 0 : (int)$row['Pendency'],
    "Pendency_Status" => empty($row['Pendency_Status']) ? 0 : (int)$row['Pendency_Status'],
    "ID" => base64_encode($row['ID'] . 'W1Ebt1IhGN3ZOLplom9I'),
  );
}

## Response
$response = array(
  "draw" => intval($draw),
  "iTotalRecords" => $totalRecords,
  "iTotalDisplayRecords" => $totalRecordwithFilter,
  "aaData" => $data
);

echo json_encode($response);