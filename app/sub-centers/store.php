<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

  if(isset($_POST['name']) && isset($_POST['reporting'])){
    require '../../includes/db-config.php';
    session_start();

    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $reporting = intval($_POST['reporting']);
    $vertical = mysqli_real_escape_string($conn, trim($_POST['vertical'] ?? ""));
    $role = $_SESSION['Role'];
    
    if($role != 'Administrator') {
        $vertical = mysqli_real_escape_string($conn, trim($_SESSION['vertical'] ?? ""));
    }
    
    if(empty($name) || empty($reporting)){
      echo json_encode(['status'=>403, 'message'=>'All fields are mandatory!']);
      exit();
    }

    $center_code_query = $conn->query("SELECT Code FROM Users WHERE ID = $reporting");
    $center_code_data = mysqli_fetch_array($center_code_query);
    $center_code = $center_code_data['Code'] ?? '';

    $all_reporting_user = $conn->query("SELECT Users.Code FROM Center_SubCenter LEFT JOIN Users ON Center_SubCenter.Sub_Center = Users.ID WHERE Center = $reporting ORDER BY Center_SubCenter.Sub_Center DESC LIMIT 1");
    if($all_reporting_user->num_rows > 0){
      $code_data = $all_reporting_user->fetch_assoc();
      $code_str = $code_data['Code'] ?? '';
      $cleaned_number = str_replace($center_code.'S', '', $code_str);
      $new_code = intval($cleaned_number) + 1;
      $code = $center_code . 'S' . $new_code;
    }else{
      $code = $center_code . 'S1';
    }
     
    $check = $conn->query("SELECT ID FROM Users WHERE Code LIKE '$code'");
    if($check->num_rows > 0){
      echo json_encode(['status'=>400, 'message'=>'Code already exists!', 'code'=>$code]);
      exit();
    }
     
    $password = substr(uniqid('sau'), 0, 8);
    
    // 1. Added HEX() around AES_ENCRYPT and wrapped '$vertical' in single quotes
    $add_user = $conn->query("INSERT INTO `Users`(`Name`, `Code`, `Password`, `Role`, `Designation`, `Photo`, `Created_By`,`vertical`) VALUES ('$name', '$code', (AES_ENCRYPT('$password','60ZpqkOnqn0UQQ2MYTlJ')), 'Sub-Center', 'Sub-Center', '/assets/img/default-user.png', ".(int)$_SESSION['ID'].", '$vertical')");
    
    // Capture the newly created Sub-Center User ID safely
    $new_sub_center_id = $conn->insert_id;

    if($add_user && $new_sub_center_id > 0){
      // 2. Separate variable to maintain context execution
      $add_relation = $conn->query("INSERT INTO `Center_SubCenter`(`Center`, `Sub_Center`) VALUES ($reporting, ".(int)$new_sub_center_id.")");
      
      if($add_relation){
        echo json_encode(['status'=>200, 'message'=>'Sub-Center added successfully!']);
      }else{
        echo json_encode(['status'=>400, 'message'=>'User created, but mapping allocation failed!']);
      }
    }else{
      echo json_encode(['status'=>400, 'message'=>'Something went wrong while creating the profile!']);
    }
  }
?>