<?php
  if(isset($_POST['name']) && isset($_POST['email'])){
    require '../../includes/db-config.php';
    session_start();

    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $name = ucwords($name);
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $contact = mysqli_real_escape_string($conn, trim($_POST['contact']));
    $reporting = intval($_POST['reporting']);
    $vertical = mysqli_real_escape_string($conn, trim($_POST['vertical'] ?? ""));
    $role = $_SESSION['Role'];
    
    if($role != 'Administrator') {
        $vertical = mysqli_real_escape_string($conn, trim($_SESSION['vertical'] ?? ""));
    }
    
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      echo json_encode(['status'=>400, "message"=>"Invalid email!"]);
      exit();
    }

    if(empty($name) || empty($email) || empty($contact)){
      echo json_encode(['status'=>403, 'message'=>'All fields are mandatory!']);
      exit();
    }

    if($_SESSION['Role'] == 'Administrator'){
      $university_id_query = $conn->query("SELECT University_ID FROM University_User WHERE `User_ID` = $reporting");
      $university_id_data = mysqli_fetch_assoc($university_id_query);
      $university_id = $university_id_data['University_ID'] ?? 0;
    }else{
      $university_id = $_SESSION['university_id'];
    }

    if(isset($_FILES["photo"]["name"]) && $_FILES["photo"]["name"] != ''){
      $temp = explode(".", $_FILES["photo"]["name"]);
      $filename = round(microtime(true)) . '.' . end($temp);
      $tempname = $_FILES["photo"]["tmp_name"];
      $folder = "../../assets/img/sub-counsellors/".$filename; 
      if(move_uploaded_file($tempname, $folder)){ 
        $filename = "/assets/img/sub-counsellors/".$filename;
      }else{
        echo json_encode(['status'=>400, 'message'=>'Unable to save photo!']);
        exit();
      }
    }else{
      $filename = "/assets/img/default-user.png";
    }

    $code = generateUsername($conn, $userNamePrefix);

    // 1. Added HEX() around AES_ENCRYPT and single quotes around '$vertical'
    $add_user = $conn->query("INSERT INTO `Users`(`Name`, `Code`, `Email`, `Mobile`, `Password`, `Photo`, `Role`, `Designation`, `Created_By`,`vertical`) VALUES ('$name', '$code', '$email', '$contact', (AES_ENCRYPT('$contact','60ZpqkOnqn0UQQ2MYTlJ')), '$filename', 'Sub-Counsellor', 'Sub-Counsellor', ".(int)$_SESSION['ID'].", '$vertical')");
    
    // Catch the correct inserted User ID immediately
    $new_user_id = $conn->insert_id;

    if($add_user && $new_user_id > 0){
      // 2. Used unique execution variables so they don't overwrite each other
      $add_relation = $conn->query("INSERT INTO University_User (`University_ID`, `User_ID`, `Reporting`) VALUES (".(int)$university_id.", ".(int)$new_user_id.", $reporting)");
      
      if($add_relation){
        echo json_encode(['status'=>200, 'message'=>'National Coordinator added successfully!']);
      }else{
        echo json_encode(['status'=>400, 'message'=>'User created, but mapping allocation failed!']);
      }
    }else{
      echo json_encode(['status'=>400, 'message'=>'Something went wrong while creating the profile!']);
    }
  }
?>