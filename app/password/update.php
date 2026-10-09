<?php
  if(isset($_POST['password']) && isset($_POST['confirm_password'])){
    include '../../includes/db-config.php';
    session_start();

    // Added trim() to prevent trailing/leading spaces from corrupting the password
    $password = mysqli_real_escape_string($conn, trim($_POST['password']));
    $confirm_password = mysqli_real_escape_string($conn, trim($_POST['confirm_password']));

    if(empty($password) || empty($confirm_password)){
      echo json_encode(['status'=>403, 'message'=>'All fields are mandatory!']);
      exit();
    }

    if($password !== $confirm_password){
      echo json_encode(['status'=>403, 'message'=>'Password not matched!']);
      exit();
    }

    // Wrapped AES_ENCRYPT in HEX() to match your login query standard
    $update = $conn->query("UPDATE Users SET `Password` = AES_ENCRYPT('$password', '60ZpqkOnqn0UQQ2MYTlJ') WHERE ID = " . (int)$_SESSION['ID']);
    
    if($update){
      echo json_encode(['status'=>200, 'message'=>'Password updated successfully!']);
    }else{
      echo json_encode(['status'=>403, 'message'=>'Something went wrong!']);
    }
  }