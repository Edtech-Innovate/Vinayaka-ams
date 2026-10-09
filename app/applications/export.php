<?php
  // ini_set('error_reporting', E_ALL );

  ini_set('display_errors', 1 );
  session_start();
  require '../../includes/db-config.php';
  require ('../../extras/vendor/shuchkin/simplexlsxgen/src/SimpleXLSXGen.php');

  $search_value = "";
  if(isset($_GET['search'])){
    $search_value = mysqli_real_escape_string($conn,$_GET['search']); // Search value
  }
  
  if(isset($_SESSION['current_session'])){
    if($_SESSION['current_session']=='All'){
      $session_query = '';
    }else{
      $session_query = "AND Admission_Sessions.Name like '%".$_SESSION['current_session']."%'";
    }
  }else{
    $get_current_session = $conn->query("SELECT Name FROM Admission_Sessions WHERE Current_Status = 1 AND University_ID = '".$_SESSION['university_id']."'");
    if($get_current_session->num_rows>0){
      $gsc = mysqli_fetch_assoc($get_current_session);
      $session_query = "AND Admission_Sessions.Name like '%".$gsc['Name']."%'";
    }else{
      $session_query = '';
    }
  }
  
  $role_query = str_replace('{{ table }}', 'Students', $_SESSION['RoleQuery']);
  $role_query = str_replace('{{ column }}', 'Added_For', $role_query);
  
  $step_query = "";
  if (isset($_GET['steps_found'])) {
    $stepsfound = mysqli_real_escape_string($conn, $_GET['steps_found']); // Search value
    if ($stepsfound == "applications") {
      $step_query = "";
    } elseif ($stepsfound == "not_processed") {
      $step_query = " AND Step = 4 AND Process_By_Center IS NULL";
    } elseif ($stepsfound == "ready_for_verification") {
      $step_query = " AND Process_By_Center IS NOT NULL AND (Document_Verified IS NULL OR Payment_Received IS NULL)";
    } elseif ($stepsfound == "verified") {
      $step_query = " AND Document_Verified IS NOT NULL AND Processed_To_University IS NULL and Enrollment_No is null";
    } elseif ($stepsfound == "proccessed_to_university") {
      $step_query = "AND Processed_To_University IS NOT NULL AND Enrollment_No IS NULL";
    } elseif ($stepsfound == "enrolled") {
      $step_query = "AND Enrollment_No IS NOT NULL";
    }
  }
  
  // Set up base Headers
  $header = array(
    'Student_ID', 'Enrollment_No', 'Roll_Number', 'Step', 'Added On', 'Processed By Center',
    'Processed To University', 'Student Name', 'Father Name', 'Mother Name', 'Adm Type', 'Session', 'Duration', 
    'Mode', 'Course', 'Sub Course', 'Short Name', 'Email', 'Contact', 'Alternate Email', 'Alternate Contact', 
    'Aadhar Number', 'DOB', 'Employement Status', 'Gender', 'Category', 'Address', 'City', 'District', 'State',
    'Pincode', 'Nationality', 'High School', 'Subject', 'Year', 'Board/Institute', 'Marks Obtained', 
    'Maximum Marks', 'Total Marks', 'Intermediate', 'Subject', 'Year', 'Board/Institute', 'Marks Obtained',
    'Maximum Marks', 'Total Marks', 'UG', 'Subject', 'Year', 'Board/Institute', 'Marks Obtained', 
    'Maximum Marks', 'Total Marks', 'PG', 'Subject', 'Year', 'Board/Institute', 'Marks Obtained',
    'Maximum Marks', 'Total Marks', 'Other', 'Subject', 'Year', 'Board/Institute', 'Marks Obtained',
    'Maximum Marks', 'Total Marks', 'Code', 'Center Name', 'RM', 'Export Documents'
  );

  if($_SESSION['Role'] =='Administrator'){
    $fee_structures = $conn->query("SELECT ID, Name, Sharing FROM Fee_Structures WHERE University_ID = ".$_SESSION['university_id']." ORDER BY Fee_Applicable_ID");
    while($fee_structure = $fee_structures->fetch_assoc()){
      array_push($header, $fee_structure['Name']);
      array_push($header, $fee_structure['Name']." %");
    }
    //print_r($header);die;
    array_push($header, "Total");
  }
  
  if ($_SESSION['university_id'] == 20) {
    $get_max_duration = $conn->query("SELECT MAX(Min_Duration) AS semester FROM Sub_Courses WHERE Status= 1 and University_ID=" . $_SESSION['university_id']);
    if ($get_max_duration->num_rows > 0) {
      $max_duration_arr = $get_max_duration->fetch_assoc();
      $semester = json_decode($max_duration_arr['semester'], true);
    } else {
      $semester = 6;
    }
    for ($i = 1; $i <= $semester; $i++) {
      $sem_val = 'Semester-' . $i;
      array_push($header, $sem_val);
    }
    array_push($header, "Total (Paid Fee)");
  }

  ## Search 
  $searchQuery = " ";
  if ($search_value != '') {
    if (!empty(strpos($search_value, "="))) {
      $search = explode("=", $search_value);
      $searchBy = trim($search[0]);
      $values = array_key_exists(1, $search) && !empty($search[1]) ? explode(" ", $search[1]) : array();
      $values = array_filter($values);
      if (!empty($values)) {
        $student_id_column = $_SESSION['student_id'] == 1 ? 'Students.Unique_ID' : "RIGHT(CONCAT('000000', Students.ID), 6)";
        $column = strcasecmp($searchBy, 'student id') == 0 ? $student_id_column : (strcasecmp($searchBy, 'enrollment') == 0 ? 'Students.Enrollment_No' : (strcasecmp($searchBy, 'oa number') == 0 ? 'OA_Number' : ''));
        if (!empty($column)) {
          $values = "'" . implode("','", $values) . "'";
          $searchQuery = " AND $column IN ($values)";
        }
      }
    } elseif (strcasecmp($search_value, 'completed') == 0) {
      $searchQuery = " AND Step = 4 ";
    } else {
      $searchQuery = " AND (Students.ID like '%" . $search_value . "%' OR Students.Unique_ID like '%" . $search_value . "%' OR  Students.First_Name like '%" . $search_value . "%' OR Students.Middle_Name like '%" . $search_value . "%' OR Students.Last_Name like '%" . $search_value . "%' OR Admission_Sessions.Name like '%" . $search_value . "%' OR Admission_Types.Name like '%" . $search_value . "%' OR Students.Step like '%" . $search_value . "%' OR Students.Father_Name like '%" . $search_value . "%' OR Students.Email like '%" . $search_value . "%' OR Students.Contact like '%" . $search_value . "%' OR Sub_Courses.Short_Name like '%" . $search_value . "%')";
    }
  }

  $filterQueryUser = isset($_SESSION['filterByUser']) ? $_SESSION['filterByUser'] : "";
  $filterByDepartment = isset($_SESSION['filterByDepartment']) ? $_SESSION['filterByDepartment'] : "";
  $filterByDate = isset($_SESSION['filterByDate']) ? $_SESSION['filterByDate'] : "";
  $filterBySubCourse = isset($_SESSION['filterBySubCourses']) ? $_SESSION['filterBySubCourses'] : "";
  $filterByStatus = isset($_SESSION['filterByStatus']) ? $_SESSION['filterByStatus'] : "";
  $filterByVertical = isset($_SESSION['filterByVerticalType']) ? $_SESSION['filterByVerticalType'] : "";

  $searchQuery .= $filterByDepartment . $filterQueryUser . $filterByDate . $filterBySubCourse . $filterByStatus . $filterByVertical;

  ## Fetch records
  $result_record = "SELECT Students.ID, Students.Unique_ID, Students.Enrollment_No, Students.OA_Number, Students.Step, Students.Created_At, Students.Process_By_Center, Students.Processed_To_University, CONCAT(Students.First_Name, IF(Students.Middle_Name!='', CONCAT(' ', Students.Middle_Name), ''), ' ', Students.Last_Name) as Name, Students.Father_Name, Students.Mother_Name, Admission_Types.`Name` as Adm_Type, Admission_Sessions.`Name` as Session, Students.Duration, Modes.`Name` as Mode, Courses.`Name` as Course, Sub_Courses.`Name` AS Sub_Course, Sub_Courses.Short_Name as Short_Name, Students.Email, Students.Contact, Students.Alternate_Email, Students.Alternate_Contact, Students.Aadhar_Number, Students.DOB, Students.Employement_Status, Students.Gender, Students.Category, REPLACE(JSON_EXTRACT(Students.Address, '$.present_address'), '\"', '') as Address, REPLACE(JSON_EXTRACT(Students.Address, '$.present_city'), '\"', '') as City, REPLACE(JSON_EXTRACT(Students.Address, '$.present_district'), '\"', '') as District, REPLACE(JSON_EXTRACT(Students.Address, '$.present_state'), '\"', '') as State, REPLACE(JSON_EXTRACT(Students.Address, '$.present_pincode'), '\"', '') as Pincode, Students.Nationality, Students.Added_For, Students.Is_Transferred, Student_Pendencies.ID as Pendency, Student_Pendencies.Status, Students.Document_Verified as Pendency_Status FROM Students LEFT JOIN Student_Pendencies ON Students.ID = Student_Pendencies.Student_ID AND Student_Pendencies.Status != 1  LEFT JOIN Admission_Types ON Students.Admission_Type_ID = Admission_Types.ID LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID LEFT JOIN Modes ON Students.Mode_ID = Modes.ID LEFT JOIN Courses ON Students.Course_ID = Courses.ID LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID WHERE Students.University_ID = ".$_SESSION['university_id']." $searchQuery $role_query $step_query $session_query ORDER BY ID DESC";

  $empRecords = mysqli_query($conn, $result_record);
  
  if ($_SESSION['university_id'] == 41) {
      array_push($header,'Total Fee Received');
  }
  
  if($_SESSION['university_id']==20){
    array_push($header,'Course Name');
  } else {
    array_push($header,'Course Duration');
  }

  array_push($header,"Document Status");
  array_push($header,"Document Details");

  $data[] = $header;
  
  while ($row = mysqli_fetch_array($empRecords, MYSQLI_ASSOC)) {
    
    // Explicitly cache required DB variables from query safely before modifying array structures
    $raw_student_id   = $row['ID'];
    $unique_student_id= $row['Unique_ID'];
    $added_for_user_id= $row['Added_For'];
    $pendency_id      = $row['Pendency'];
    $pendency_status  = $row['Status'];
    $doc_verified_val = $row['Pendency_Status'];
    $course_name      = $row['Sub_Course']; 

    // Secure Redaction Parameter mapping
    $row['Aadhar_Number'] = '[Aadhaar Redacted]';

    // Users lookup
    if($_SESSION['Role']=='Center'){
      $user = $conn->query("SELECT ID, Code, Name FROM Users WHERE ID = ".$added_for_user_id);
    }else{
      $user = $conn->query("SELECT ID, Code, Name FROM Users WHERE ID = ".$added_for_user_id." AND Role = 'Center'");
      if($user->num_rows==0){
        $user = $conn->query("SELECT Users.ID, Code, Name FROM Users LEFT JOIN Center_SubCenter ON Users.ID = Center_SubCenter.Center WHERE `Sub_Center` = ".$added_for_user_id);
      }
    }
    
    if ($user && $user->num_rows > 0) {
        $user = mysqli_fetch_assoc($user);
    } else {
        $user = ['Name' => '', 'Code' => '', 'ID' => 0];
    }

    // RM Management 
    $rm['Name'] = "";
    if (!empty($user) || $_SESSION['Designation']!="University") {
      $rm = $conn->query("SELECT CONCAT(Users.Name, ' (', Users.Code, ')') as Name FROM University_User LEFT JOIN Users ON University_User.Reporting = Users.ID AND University_User.University_ID = " . $_SESSION['university_id'] . " WHERE University_User.`User_ID` = " . $user['ID'] . " AND University_User.University_ID = " . $_SESSION['university_id']);
      if ($rm && $rm->num_rows > 0) {
        $rm = mysqli_fetch_array($rm, MYSQLI_ASSOC);
      } else {
        $rm = $user;
      }
    }

    // Map ordered row array dynamically to mimic clean basic structure output
    $export_row = array(
      $raw_student_id,
      $row['Enrollment_No'],
      $row['Roll_Number'],
      $row['Step'],
      !empty($row['Created_At']) ? $row['Created_At'] : "",
      !empty($row['Process_By_Center']) ? date("d-m-Y H:i A", strtotime($row['Process_By_Center'])) : "",
      !empty($row['Processed_To_University']) ? date("d-m-Y H:i A", strtotime($row['Processed_To_University'])) : "",
      $row['Name'],
      $row['Father_Name'],
      $row['Mother_Name'],
      $row['Adm_Type'],
      $row['Session'],
      $row['Duration'],
      $row['Mode'],
      $row['Course'],
      $row['Sub_Course'],
      $row['Short_Name'],
      $row['Email'],
      $row['Contact'],
      $row['Alternate_Email'],
      $row['Alternate_Contact'],
      $row['Aadhar_Number'],
      $row['DOB'],
      $row['Employement_Status'],
      $row['Gender'],
      $row['Category'],
      $row['Address'],
      $row['City'],
      $row['District'],
      $row['State'],
      $row['Pincode'],
      $row['Nationality']
    );

    // Academics Insertion
    $courses_list = array('High School', 'Intermediate', 'UG', 'PG', 'Other');
    foreach($courses_list as $c_name){
      $academics = $conn->query("SELECT Type, Subject, `Year`, `Board/Institute`, Marks_Obtained, Max_Marks, Total_Marks FROM Student_Academics WHERE Student_ID = $raw_student_id AND Type = '$c_name'");
      if($academics && $academics->num_rows>0){
        $academic = mysqli_fetch_row($academics);
      }else{
        $academic = array($c_name, '', '', '', '', '', '');
      }
      $export_row = array_merge($export_row, $academic);
    }

    // Append Centers details
    array_push($export_row, $user['Code']);
    array_push($export_row, $user['Name']);
    array_push($export_row, $rm['Name']);

    // Application Export Link
    $encode = base64_encode($raw_student_id."W1Ebt1IhGN3ZOLplom9I");
    array_push($export_row, '<i><a href="https://'.$_SERVER['HTTP_HOST'].'/app/applications/zip?id='.$encode.'">Click Here</a></i>');

    // Fee Structures Calculation
    if($_SESSION['Role']!='Sub-Center'){
      $student_fee = $conn->query("SELECT Fee FROM Student_Ledgers WHERE Student_ID = $raw_student_id LIMIT 1");
      if($student_fee && $student_fee->num_rows>0){
        $student_fee_fetched = $student_fee->fetch_assoc();
        $student_fee_val = json_decode($student_fee_fetched['Fee'], true);
        
        $fee_structures = $conn->query("SELECT ID, Sharing FROM Fee_Structures WHERE University_ID = ".$_SESSION['university_id']." ORDER BY Fee_Applicable_ID");
        while($fee_structure = $fee_structures->fetch_assoc()){
          array_push($export_row, $student_fee_val[$fee_structure['ID']]??"");
          if($fee_structure['Sharing']==1){
            $sharing = $conn->query("SELECT Fee FROM Fee_Variables WHERE Code = ".$user['ID']." AND University_ID = ".$_SESSION['university_id']."");
            if($sharing && $sharing->num_rows>0){
              $sharing_data = $sharing->fetch_assoc();
              array_push($export_row, $sharing_data['Fee']);
            }else{
              array_push($export_row, 0);
            }
          } else {
              array_push($export_row, 0);
          }
        }
        array_push($export_row, array_sum($student_fee_val));
      } else {
        array_push($export_row, 0);
        array_push($export_row, 0);
        array_push($export_row, 0);
      }
    }

    // Formatting Student ID
    if(!empty($row['Enrollment_No'])){
      $export_row[0] = $row['Enrollment_No'];
    }else{
      $export_row[0] = '<b>'.sprintf("%'.06d\n", $raw_student_id).'</b>';
    }

    // Strip Subcenter elements from columns if required
    if($_SESSION['Role']=='Sub-Center'){
       // Unset Processed By Center, High School fields structural offsets to match header reductions
       unset($export_row[5]); 
    }

    // Determine Skill Duration Text
    $skillduration = '';
    if (stripos($course_name, '11') !== false && stripos($course_name, 'adv') !== false) {
        $skillduration = '2';
    } elseif (stripos($course_name, '11') !== false) {
        $skillduration = '1';
    } elseif (stripos($course_name, '6') !== false) {
        $skillduration = '6';
    } elseif (stripos($course_name, '3') !== false) {
        $skillduration = '3';
    }

    if ($skillduration == "3") {
        $skillDurations_text = "3/Certification";
    } elseif ($skillduration == "6") {
        $skillDurations_text = "6/Certified";
    } elseif ($skillduration == "1") {
        $skillDurations_text = "11/Advanced-Certified";
    } elseif ($skillduration == "2") {
        $skillDurations_text = "11/Certified";
    } else {
        $skillDurations_text = 'NA';
    }

    // Ledger handling matching current student lookup context ID safely
    $student_lookup_id = !empty($unique_student_id) ? $unique_student_id : $raw_student_id;
    $sem_fee = [];

    if ($_SESSION['university_id'] == 20) {
      for ($i = 1; $i <= $semester; $i++) {
        $query = "SELECT Student_Ledgers.Fee FROM Student_Ledgers LEFT JOIN Wallet_Payments ON Wallet_Payments.Transaction_ID = Student_Ledgers.Transaction_ID WHERE Student_Ledgers.Student_ID = '{$student_lookup_id}' AND Duration = '$i' AND Student_Ledgers.Transaction_ID IS NOT NULL AND Student_Ledgers.Status = 1 ORDER BY Student_Ledgers.Type, Student_Ledgers.Created_At";
        $ledgers = $conn->query($query);
        if ($ledgers->num_rows == 0) {
          $query = "SELECT Student_Ledgers.Fee FROM Student_Ledgers LEFT JOIN Payments ON Payments.Transaction_ID = Student_Ledgers.Transaction_ID WHERE Student_Ledgers.Student_ID = '{$student_lookup_id}' AND Duration = '$i' AND Student_Ledgers.Transaction_ID IS NOT NULL AND Student_Ledgers.Status = 1 ORDER BY Student_Ledgers.Type, Student_Ledgers.Created_At";
          $ledgers = $conn->query($query);
        }
        $ledgers_arr = $ledgers->fetch_assoc();
        if (!empty($ledgers_arr['Fee'])) {
          if (is_numeric($ledgers_arr['Fee'])) {
            $sem_fee[$i] = $ledgers_arr['Fee'];
          } else {
            $fee = json_decode($ledgers_arr['Fee'], true);
            $sem_fee[$i] = isset($fee['Paid']) ? abs($fee['Paid']) : '0';
          }
        } else {
          $sem_fee[$i] = '';
        }
      }
      foreach ($sem_fee as $fee_val) {
        array_push($export_row, $fee_val);
      }
 
      $sem_fee = array_map('floatval', $sem_fee);
      array_push($export_row, array_sum($sem_fee)); 
    } 

    if ($_SESSION['university_id'] == 41) {
      $total_sem_fee_sql = $conn->query("SELECT SUM(JSON_UNQUOTE(JSON_EXTRACT(Fee, '$.Paid'))) AS total_fee FROM Student_Ledgers WHERE Student_Ledgers.Student_ID = '{$student_lookup_id}' AND Student_Ledgers.Status = 1 ");
      if ($total_sem_fee_sql && $total_sem_fee_sql->num_rows > 0) {
        $total_sem_fee_arr = $total_sem_fee_sql->fetch_assoc();
        array_push($export_row, abs(array_sum($total_sem_fee_arr)));
      } else {
        array_push($export_row, 0);
      }
    }

    if($_SESSION['university_id']==20){
        array_push($export_row, $course_name);
    } else {
        array_push($export_row, $skillDurations_text);
    }
    
    // Process Pendency and Document status using safe variable states extracted earlier
    if($doc_verified_val == 0 && $doc_verified_val !== '' && $doc_verified_val !== null){
        $docStatus = "Pendency";
        $pendingData = $conn->query("SELECT * FROM Student_Pendencies WHERE ID = ".$pendency_id)->fetch_assoc();
        $pendingData = json_decode($pendingData['Pendency'], true);
        $pendingDataString = "";
        if(is_array($pendingData)){
          foreach($pendingData as $col=>$val){
             $pendingDataString .= "$col($val)/";
          }
        }
    }elseif($row['Processed_To_University'] != '' && $row['Processed_To_University'] != null){
        $docStatus = "Approved";
        $pendingDataString = "Documents are approved";
    }else{
         $docStatus = "Pending";
         $pendingDataString = "Documents are in review";
    }
    
    array_push($export_row, $docStatus);
    array_push($export_row, $pendingDataString);
    
    $data[] = $export_row;
  }

  $xlsx = SimpleXLSXGen::fromArray( $data )->downloadAs('Students.xlsx');