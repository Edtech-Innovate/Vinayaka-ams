<?php
## Database configuration
include '../../includes/db-config.php';
session_start();
error_reporting(-1);

## Read values safely
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$row = isset($_POST['start']) ? intval($_POST['start']) : 0;
$rowperpage = isset($_POST['length']) ? intval($_POST['length']) : 25;

if (isset($_POST['order'])) {
  $columnIndex = $_POST['order'][0]['column'];
  $columnName = $_POST['columns'][$columnIndex]['data'];
  $columnSortOrder = $_POST['order'][0]['dir'];
}

$searchValue = mysqli_real_escape_string($conn, $_POST['search']['value']);

if (isset($columnSortOrder) && !empty($columnName)) {
  if ($columnName == 'Password') {
    $orderby = "ORDER BY Users.Password $columnSortOrder";
  } else {
    $orderby = "ORDER BY $columnName $columnSortOrder";
  }
} else {
  $orderby = "ORDER BY Users.ID DESC";
}

$current_user_id = intval($_SESSION['ID']);

// 1. Base Condition: Role MUST be Center
$center_query = " AND Users.Role = 'Center' ";

// 2. Additional Role Hierarchy Filters
if ($_SESSION['Role'] !== "Administrator") {
  
  // A. Process Counsellor and Sub-Counsellor Hierarchy
  if ($_SESSION['Role'] == 'Sub-Counsellor' || $_SESSION['Role'] == 'Counsellor') {
    $allowed_center_ids = [];

    // Mapped via University_User
    $getcenterids = $conn->query("SELECT User_ID FROM University_User WHERE Reporting = $current_user_id");
    if ($getcenterids && $getcenterids->num_rows > 0) {
      while ($r = $getcenterids->fetch_assoc()) {
        $allowed_center_ids[] = intval($r['User_ID']);
      }
    }

    // Sub-counsellors reporting to this user
    $sub_counsellors = [$current_user_id];
    $getSubCounsellors = $conn->query("SELECT User_ID FROM University_User WHERE Reporting = $current_user_id");
    if ($getSubCounsellors && $getSubCounsellors->num_rows > 0) {
      while ($sc = $getSubCounsellors->fetch_assoc()) {
        $sub_counsellors[] = intval($sc['User_ID']);
      }
    }
    
    // Centers created by this user OR downstream sub-counsellors
    $sub_counsellor_str = implode(',', array_map('intval', $sub_counsellors));
    $getCreatedCenters = $conn->query("SELECT ID FROM Users WHERE Role = 'Center' AND Created_By IN ($sub_counsellor_str)");
    if ($getCreatedCenters && $getCreatedCenters->num_rows > 0) {
      while ($c = $getCreatedCenters->fetch_assoc()) {
        $allowed_center_ids[] = intval($c['ID']);
      }
    }

    $allowed_center_ids = array_unique(array_filter($allowed_center_ids));
    if (!empty($allowed_center_ids)) {
      $center_ids_str = implode(',', $allowed_center_ids);
      $center_query .= " AND (Users.ID IN ($center_ids_str) OR Users.Created_By = $current_user_id) ";
    } else {
      $center_query .= " AND Users.Created_By = $current_user_id ";
    }
  }

  // B. Process University Head Hierarchy
  else if ($_SESSION['Role'] == 'University Head') {
    $counsellor_ids = [];

    $getCounsellors = $conn->query("SELECT User_ID FROM University_User WHERE Reporting = $current_user_id");
    if ($getCounsellors && $getCounsellors->num_rows > 0) {
      while ($c = $getCounsellors->fetch_assoc()) {
        $counsellor_ids[] = intval($c['User_ID']);
      }
    }

    if (!empty($counsellor_ids)) {
      $counsellors_str = implode(',', array_map('intval', $counsellor_ids));

      $getSubCounsellors = $conn->query("SELECT User_ID FROM University_User WHERE Reporting IN ($counsellors_str)");
      if ($getSubCounsellors && $getSubCounsellors->num_rows > 0) {
        while ($sc = $getSubCounsellors->fetch_assoc()) {
          $counsellor_ids[] = intval($sc['User_ID']);
        }
      }
      
      $all_downstream_users = implode(',', array_unique(array_map('intval', $counsellor_ids)));

      $allotted_centers = [];
      $getAllotted = $conn->query("SELECT User_ID FROM University_User WHERE Reporting IN ($all_downstream_users)");
      if ($getAllotted && $getAllotted->num_rows > 0) {
        while ($ac = $getAllotted->fetch_assoc()) {
          $allotted_centers[] = intval($ac['User_ID']);
        }
      }

      $created_centers = [];
      $getCreated = $conn->query("SELECT ID FROM Users WHERE Role = 'Center' AND (Created_By = $current_user_id OR Created_By IN ($all_downstream_users))");
      if ($getCreated && $getCreated->num_rows > 0) {
        while ($cc = $getCreated->fetch_assoc()) {
          $created_centers[] = intval($cc['ID']);
        }
      }

      $final_center_ids = array_unique(array_merge($allotted_centers, $created_centers));
      if (!empty($final_center_ids)) {
        $final_ids_str = implode(',', $final_center_ids);
        $center_query .= " AND (Users.ID IN ($final_ids_str) OR Users.Created_By = $current_user_id) ";
      } else {
        $center_query .= " AND Users.Created_By = $current_user_id ";
      }
    } else {
      $center_query .= " AND Users.Created_By = $current_user_id ";
    }
  }
  
  // C. Institutional Suffix Guard
  if (isset($_SESSION['university_id']) && !empty($_SESSION['university_id'])) {
    $check_has_unique_center_code = $conn->query("SELECT Center_Suffix FROM Universities WHERE ID = " . intval($_SESSION['university_id']) . " AND Has_Unique_Center = 1");
    if ($check_has_unique_center_code && $check_has_unique_center_code->num_rows > 0) {
      $center_suffix = mysqli_fetch_assoc($check_has_unique_center_code);
      $center_suffix = mysqli_real_escape_string($conn, $center_suffix['Center_Suffix']);
      $center_query .= " AND ((Users.Code LIKE '$center_suffix%' AND Users.Is_Unique = 1) OR Users.Created_By = $current_user_id)";
    }
  }
}

## Search Filter Setup
$searchQuery = "";
if (!empty($searchValue)) {
  $searchQuery = " AND (Users.Name LIKE '%" . $searchValue . "%' OR Users.Code LIKE '%" . $searchValue . "%' OR Users.Email LIKE '%" . $searchValue . "%' OR Users.Mobile LIKE '%" . $searchValue . "%' OR CONCAT(Reporting.Name, ' (', Reporting.Code, ')') LIKE '%" . $searchValue . "%')";
}

## Total records without search filter
$all_count = $conn->query("SELECT COUNT(Users.ID) as allcount FROM Users LEFT JOIN Users as Reporting ON Users.Created_By = Reporting.ID WHERE 1=1 $center_query");
$records = $all_count ? mysqli_fetch_assoc($all_count) : ['allcount' => 0];
$totalRecords = isset($records['allcount']) ? intval($records['allcount']) : 0;

## Total records with search filter
$filter_count = $conn->query("SELECT COUNT(Users.ID) as filtered FROM Users LEFT JOIN Users as Reporting ON Users.Created_By = Reporting.ID WHERE 1=1 $center_query $searchQuery");
$records = $filter_count ? mysqli_fetch_assoc($filter_count) : ['filtered' => 0];
$totalRecordwithFilter = isset($records['filtered']) ? intval($records['filtered']) : 0;

## Fetch Records Query
$result_record = "SELECT Users.ID, Users.Name, Users.CanCreateSubCenter, Users.Email, Users.Code, Users.Status, Users.Photo, Users.Internal_ID, Users.Created_By as Creator_ID, CAST(AES_DECRYPT(Users.Password, '60ZpqkOnqn0UQQ2MYTlJ') AS CHAR(50)) as password, CONCAT(Reporting.Name, ' (', Reporting.Code, ')') as Direct_Creator FROM Users LEFT JOIN Users as Reporting ON Users.Created_By = Reporting.ID WHERE 1=1 $center_query $searchQuery $orderby LIMIT " . intval($row) . "," . intval($rowperpage);

$empRecords = mysqli_query($conn, $result_record);
$data = array();

if ($empRecords) {
  while ($row = mysqli_fetch_assoc($empRecords)) {

    // 1. Creator Display (Direct creator only)
    $createdByString = !empty($row['Direct_Creator']) ? $row['Direct_Creator'] : 'N/A';

    // 2. Fetch Allotted Universities with Short_Name and Vertical (Unique by Name)
    $allotted_unis = [];
    $seen_uni_names = [];
    
    $uniQuery = $conn->query("SELECT Universities.Name, Universities.Short_Name, Universities.Vertical 
                            FROM University_User 
                            LEFT JOIN Universities ON University_User.University_ID = Universities.ID 
                            WHERE University_User.User_ID = " . intval($row['ID']) . " 
                            AND Universities.Name IS NOT NULL");
    if ($uniQuery && $uniQuery->num_rows > 0) {
      while ($u = $uniQuery->fetch_assoc()) {
        $uniName = trim($u['Name']);
        if (!empty($uniName) && !in_array($uniName, $seen_uni_names)) {
          $seen_uni_names[] = $uniName;
          $allotted_unis[] = array(
            'Name' => $uniName,
            'Short_Name' => !empty($u['Short_Name']) ? $u['Short_Name'] : $uniName,
            'Vertical' => !empty($u['Vertical']) ? $u['Vertical'] : ''
          );
        }
      }
    }

    // 3. Wallet Credit/Debit Calculations
    $credit_query = $conn->query("SELECT SUM(Amount) AS totalamount FROM Wallets WHERE Added_By = '" . intval($row['ID']) ."' AND Status=1");
    $creditamount = $credit_query ? mysqli_fetch_assoc($credit_query) : null;
    $total_credit_amount = isset($creditamount['totalamount']) ? floatval($creditamount['totalamount']) : 0;
    
    $debit_query = $conn->query("SELECT SUM(Amount) AS totalamount FROM Wallet_Payments WHERE Added_By = '" . intval($row['ID']) ."' AND Status=1");
    $debit_amount = $debit_query ? mysqli_fetch_assoc($debit_query) : null;
    $total_debit_amount = isset($debit_amount['totalamount']) ? floatval($debit_amount['totalamount']) : 0;
    
    $current_amount = $total_credit_amount - $total_debit_amount;
    
    $data[] = array(
      "Photo" => !empty($row['Photo']) ? $row['Photo'] : '/assets/img/default-user.png',
      "Name" => $row['Name'],
      "Email" => $row['Email'],
      "Code" => $row['Code'],
      "Password" => !empty($row['password']) ? $row['password'] : '********',
      "CanCreateSubCenter" => $row['CanCreateSubCenter'],
      "Status" => $row["Status"],
      "Created_By" => $createdByString,
      "Allotted_Universities" => !empty($allotted_unis) ? $allotted_unis : [],
      "ID" => $row["ID"],
      "totalAmount" => $current_amount,
      "Internal_ID" => isset($row["Internal_ID"]) ? $row["Internal_ID"] : ''
    );
  }
}

## Response Output
$response = array(
  "draw" => intval($draw),
  "iTotalRecords" => intval($totalRecords),
  "iTotalDisplayRecords" => intval($totalRecordwithFilter),
  "aaData" => $data
);

echo json_encode($response);
exit();