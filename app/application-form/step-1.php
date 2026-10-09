<?php

if (isset($_POST['center'])) {
  require '../../includes/db-config.php';
  ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
  // ⚠️ CRITICAL: If generateStudentLedger, addLog, or generateStudentID are inside helpers.php, 
  // you MUST uncomment the line below, otherwise the server will throw a 500 error.
  // require '../../includes/helpers.php'; 
  session_start();

  $id = array_key_exists('inserted_id', $_POST) ? intval($_POST['inserted_id']) : 0;

  // Required Basic Information
  $center = intval($_POST['center']);
  $admission_session = intval($_POST['admission_session']);
  $admission_type = intval($_POST['admission_type']);
  $course = intval($_POST['course']);
  $sub_course = intval($_POST['sub_course']);
  
  $duration = intval($_POST['duration']);
  $abcid = mysqli_real_escape_string($conn, $_POST['abc_id']);
  
  // FIX: Extract lead_id from $_POST if sent, otherwise default to 0
  $lead_id = isset($_POST['lead_id']) ? intval($_POST['lead_id']) : 0; 

  if (empty($center) || empty($admission_session) || empty($admission_type) || empty($course) || empty($sub_course) || empty($duration)) {
    exit(json_encode(['status' => 400, 'message' => 'All fields are required']));
  }
  
  // --- START: CONDITIONAL ELECTIVE VALIDATION ---
  if (isset($_POST['subjects_elective']) && is_array($_POST['subjects_elective']) && !empty($_POST['subjects_elective'])) {
    
    $group_counts = [];
    foreach ($_POST['subjects_elective'] as $subject_id => $group_id) {
      $grp_id = intval($group_id);
      if (!isset($group_counts[$grp_id])) {
        $group_counts[$grp_id] = 0;
      }
      $group_counts[$grp_id]++;
    }

    if (!isset($group_counts[1]) || $group_counts[1] !== 2) {
      exit(json_encode([
          'status' => 400, 
          'message' => 'Validation Error: For Group 1, English is mandatory and you must select exactly 1 additional subject (Total 2).'
      ]));
    }

    // --- GROUP 2 BACKEND VALIDATION ---
    if (isset($group_counts[2]) && $group_counts[2] > 5) {
      exit(json_encode([
          'status' => 400, 
          'message' => 'Validation Error: For Group 2, you can select a maximum of 5 subjects.'
      ]));
    }
  }
  // --- END: CONDITIONAL ELECTIVE VALIDATION ---

  $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
  $full_name = str_replace('  ', ' ', $full_name);
  $full_name = explode(' ', $full_name, 3);
  $count = count($full_name);

  if ($count == 2) {
    $first_name = trim($full_name[0]);
    $first_name = strtoupper(strtolower($first_name));
    $middle_name = NULL;
    $last_name = trim($full_name[1]);
    $last_name = strtoupper(strtolower($last_name));
  } elseif ($count > 2) {
    $first_name = trim($full_name[0]);
    $first_name = strtoupper(strtolower($first_name));
    $middle_name = trim($full_name[1]);
    $middle_name = strtoupper(strtolower($middle_name));
    $last_name = trim($full_name[2]);
    $last_name = strtoupper(strtolower($last_name));
  } else {
    $first_name = trim($full_name[0]);
    $first_name = strtoupper(strtolower($first_name));
    $middle_name = NULL;
    $last_name = NULL;
  }

  if ($_SESSION['university_id'] == 20 && $admission_session == 70) {
      $duration = 6;
  }    
  if ($admission_session == 73) {
      $duration = 2;
  }    
       
  $father_name = mysqli_real_escape_string($conn, $_POST['father_name']);
  $father_name = strtoupper(strtolower($father_name));
  $mother_name = mysqli_real_escape_string($conn, $_POST['mother_name']);
  $mother_name = strtoupper(strtolower($mother_name));

  $dob = mysqli_real_escape_string($conn, $_POST['dob']);
  $dob = date('Y-m-d', strtotime($dob));

  $gender = mysqli_real_escape_string($conn, $_POST['gender']);
  $category = mysqli_real_escape_string($conn, $_POST['category']);
  $employment_status = mysqli_real_escape_string($conn, $_POST['employment_status']);
  $marital_status = mysqli_real_escape_string($conn, $_POST['marital_status']);
  $religion = mysqli_real_escape_string($conn, $_POST['religion']);
  
  // AADHAAR HANDLING
  $aadhar = isset($_POST['aadhar']) ? trim(mysqli_real_escape_string($conn, $_POST['aadhar'])) : '';
  if (empty($aadhar)) {
    $aadhar = 'not available now';
  }

  $nationality = mysqli_real_escape_string($conn, $_POST['nationality']);

  if (!empty($id)) {
        // 1. Get old student data
        $old_query = $conn->query("SELECT * FROM Students WHERE ID = $id");
        $old_data   = $old_query->fetch_assoc();
    
        // 2. Prepare new data
        $new_data = [
            'abc_id'               => $abcid,
            'Admission_Type_ID'    => $admission_type,
            'Admission_Session_ID' => $admission_session,
            'Course_ID'            => $course,
            'Sub_Course_ID'        => $sub_course,
            'Duration'             => $duration,
            'First_Name'           => $first_name,
            'Middle_Name'          => $middle_name,
            'Last_Name'            => $last_name,
            'Father_Name'          => $father_name,
            'Mother_Name'          => $mother_name,
            'DOB'                  => $dob,
            'Aadhar_Number'        => $aadhar,
            'Category'             => $category,
            'Gender'               => $gender,
            'Nationality'          => $nationality,
            'Employement_Status'   => $employment_status,
            'Marital_Status'       => $marital_status,
            'Religion'             => $religion,
        ];

        // 3. Detect changed fields
        $changes_old = [];
        $changes_new = [];

        foreach ($new_data as $field => $value) {
            if ($old_data[$field] != $value) {
                $changes_old[$field] = $old_data[$field];
                $changes_new[$field] = $value;
            }
        }

    $add_student = $conn->query("UPDATE Students SET abc_id = '$abcid', Admission_Type_ID = $admission_type, Admission_Session_ID = $admission_session, Course_ID = $course, Sub_Course_ID = $sub_course, Duration = $duration, First_Name = '$first_name', Middle_Name = '$middle_name', Last_Name = '$last_name', Father_Name = '$father_name', Mother_Name = '$mother_name', DOB = '$dob', Aadhar_Number = '$aadhar', Category = '$category', Gender = '$gender', Nationality = '$nationality', Employement_Status = '$employment_status', Marital_Status = '$marital_status', Religion = '$religion' WHERE ID = $id");
    
    if ($add_student) {
      
      $conn->query("DELETE FROM Student_Subjects WHERE Student_ID = $id");
      
      if (isset($_POST['language_subjects']) && is_array($_POST['language_subjects'])) {
        foreach ($_POST['language_subjects'] as $subject_id => $group_id) {
          $sub_id = intval($subject_id);
          $grp_id = intval($group_id);
          $conn->query("INSERT INTO Student_Subjects (Student_ID, Subject_ID, Group_ID, Type) VALUES ($id, $sub_id, $grp_id, 'Compulsory')");
        }
      }

      if (isset($_POST['subjects_elective']) && is_array($_POST['subjects_elective']) && !empty($_POST['subjects_elective'])) {
        foreach ($_POST['subjects_elective'] as $subject_id => $group_id) {
          $sub_id = intval($subject_id);
          $grp_id = intval($group_id);
          $conn->query("INSERT INTO Student_Subjects (Student_ID, Subject_ID, Group_ID, Type) VALUES ($id, $sub_id, $grp_id, 'Elective')");
        }
      }

      generateStudentLedger($conn, $id);
      
      if (!empty($changes_new)) {
          addLog($conn, $_SESSION['university_id'], $_SESSION['ID'], 'update', 'Students', $id, 'Student updated', json_encode($changes_old), json_encode($changes_new));
      }
      echo json_encode(['status' => 200, 'message' => 'Step 1 details saved successfully!', 'id' => $id]);
    } else {
      echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    }
  } else {
    // AADHAAR DUPLICATE CHECK (Only check if a valid number is provided, skip if 'not available now')
    if (strtolower($aadhar) !== 'not available now') {
      $aadhar_check = $conn->query("SELECT ID FROM Students WHERE Aadhar_Number = '$aadhar' AND University_ID = " . $_SESSION['university_id']);
      if ($aadhar_check->num_rows > 0) {
        echo json_encode(['status' => 400, 'message' => 'Aadhaar number already exists!']);
        exit();
      }
    }

    $student_check = $conn->query("SELECT ID FROM Students WHERE First_Name = '$first_name' AND Father_Name = '$father_name' AND Mother_Name = '$mother_name' AND DOB = '$dob' AND University_ID = " . $_SESSION['university_id'] . " AND Course_ID = $course AND Added_For = $center");
    if ($student_check->num_rows > 0) {
      echo json_encode(['status' => 400, 'message' => 'Student with same details already exists!']);
      exit();
    }

    $added_by = $_SESSION['Role'] == 'Student' ? $center : $_SESSION['ID'];

    // String version assignment for SQL execution security
    $sql_lead_id = empty($lead_id) ? 'NULL' : $lead_id;

    $add_student = $conn->query("INSERT INTO Students (Added_By, Added_For, Lead_Status_ID, University_ID, Admission_Type_ID, Admission_Session_ID, Course_ID, Sub_Course_ID, Duration, Admission_Duration, First_Name, Middle_Name, Last_Name, Father_Name, Mother_Name, DOB, Aadhar_Number, Category, Gender, Nationality, Employement_Status, Marital_Status, Religion, Step) VALUES(" . $added_by . ", $center, $sql_lead_id, " . $_SESSION['university_id'] . ", $admission_type, $admission_session, $course, $sub_course, $duration, $duration, '$first_name', '$middle_name', '$last_name', '$father_name', '$mother_name', '$dob', '$aadhar', '$category', '$gender', '$nationality', '$employment_status', '$marital_status', '$religion', 1)");
    
    if ($add_student) {
      $student_id = $conn->insert_id;
     
      if (empty($lead_id)) {
        $has_unique_student_id = $conn->query("SELECT ID_Suffix, Max_Character FROM Universities WHERE ID = " . $_SESSION['university_id'] . " AND Has_Unique_StudentID = 1");
        if ($has_unique_student_id->num_rows > 0) {
          $has_unique_student_id = $has_unique_student_id->fetch_assoc();
          $suffix = $has_unique_student_id['ID_Suffix'];
          $characters = $has_unique_student_id['Max_Character'];
          $unique_id = generateStudentID($conn, $suffix, $characters, $_SESSION['university_id']);
          $conn->query("UPDATE Students SET Unique_ID = '$unique_id' WHERE ID = $student_id");
          if (isset($_POST['abc_id'])) {
            $abcid = mysqli_real_escape_string($conn, $_POST['abc_id']);
            $conn->query("UPDATE Students SET abc_id = '$abcid' WHERE ID = $student_id");
          }
        }
      } else {
        $unique_id = $conn->query("SELECT Unique_ID FROM Lead_Status WHERE ID = $lead_id");
        $unique_id = $unique_id->fetch_assoc();
        $conn->query("UPDATE Students SET Unique_ID = '" . $unique_id['Unique_ID'] . "' WHERE ID = $student_id");

        $final_stage = $conn->query("SELECT ID FROM Stages WHERE Name = 'Step 1 Completed'");
        if ($final_stage->num_rows > 0) {
          $final_stage = $final_stage->fetch_assoc();
          $final_stage = $final_stage['ID'];
        } else {
          $final_stage = $conn->query("INSERT INTO Stages (`Name`, Is_Last) VALUES ('Step 1 Completed', 1)");
          $final_stage = $conn->insert_id;
        }

        $conn->query("UPDATE Lead_Status SET Admission = 1, Stage_ID = $final_stage, Reason_ID = NULL WHERE ID = $lead_id");
      }

      if (isset($_POST['language_subjects']) && is_array($_POST['language_subjects'])) {
        foreach ($_POST['language_subjects'] as $subject_id => $group_id) {
          $sub_id = intval($subject_id);
          $grp_id = intval($group_id);
          $conn->query("INSERT INTO Student_Subjects (Student_ID, Subject_ID, Group_ID, Type) VALUES ($student_id, $sub_id, $grp_id, 'Compulsory')");
        }
      }

      if (isset($_POST['subjects_elective']) && is_array($_POST['subjects_elective']) && !empty($_POST['subjects_elective'])) {
        foreach ($_POST['subjects_elective'] as $subject_id => $group_id) {
          $sub_id = intval($subject_id);
          $grp_id = intval($group_id);
          $conn->query("INSERT INTO Student_Subjects (Student_ID, Subject_ID, Group_ID, Type) VALUES ($student_id, $sub_id, $grp_id, 'Elective')");
        }
      }

      generateStudentLedger($conn, $student_id);
      
      $new_data = [
            'Added_By'             => $added_by,
            'Added_For'            => $center,
            'Lead_Status_ID'       => $lead_id,
            'University_ID'        => $_SESSION['university_id'],
            'Admission_Type_ID'    => $admission_type,
            'Admission_Session_ID' => $admission_session,
            'Course_ID'            => $course,
            'Sub_Course_ID'        => $sub_course,
            'Duration'             => $duration,
            'First_Name'           => $first_name,
            'Middle_Name'          => $middle_name,
            'Last_Name'            => $last_name,
            'Father_Name'          => $father_name,
            'Mother_Name'          => $mother_name,
            'DOB'                  => $dob,
            'Aadhar_Number'        => $aadhar,
            'Category'             => $category,
            'Gender'               => $gender,
            'Nationality'          => $nationality,
            'Employement_Status'   => $employment_status,
            'Marital_Status'       => $marital_status,
            'Religion'             => $religion,
      ];

      addLog($conn, $_SESSION['university_id'], $_SESSION['ID'], 'add', 'Students', $student_id, 'Student added', null, json_encode($new_data));
      echo json_encode(['status' => 200, 'message' => 'Step 1 details saved successfully!', 'id' => $student_id]);
    } else {
      echo json_encode(['status' => 400, 'message' => 'Something went wrong!']);
    }
  }
}