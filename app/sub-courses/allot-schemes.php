<?php
  if(isset($_POST['schemes']) && isset($_POST['id'])){
    require '../../includes/db-config.php';

    $id = intval($_POST['id']);
    $schemes = is_array($_POST['schemes']) ? array_filter($_POST['schemes']) : array();
    $fee = isset($_POST['fee']) && is_array($_POST['fee']) ? array_filter($_POST['fee']) : array();
    $applicable_in = isset($_POST['applicable_in']) && is_array($_POST['applicable_in']) ? array_filter($_POST['applicable_in']) : array();
    $discount = isset($_POST['discount']) && is_array($_POST['discount']) ? $_POST['discount'] : array();

    $duration = $conn->query("SELECT Min_Duration FROM Sub_Courses WHERE ID = $id");
    $duration = $duration->fetch_assoc();
    $duration = $duration['Min_Duration'];

    foreach($schemes as $scheme_id){
      // Alloted Fee Structure
      $scheme_query = $conn->query("SELECT ID, Name, Fee_Structure, University_ID FROM Schemes WHERE ID = ".$scheme_id);
      $scheme_data = $scheme_query->fetch_assoc();

      $feeStructureIds = implode(",", json_decode($scheme_data['Fee_Structure'], true));
      $structures = $conn->query("SELECT ID, Name, Fee_Applicable_ID FROM Fee_Structures WHERE Is_Constant = 1 AND ID IN ($feeStructureIds) AND University_ID = ".$scheme_data['University_ID']);
      while($structure = $structures->fetch_assoc()){
        if(empty($fee[$scheme_data['ID']][$structure['ID']])){
          exit(json_encode(['status'=>403, 'message'=>'Please enter Fee!']));
        }

        if(in_array($structure['Fee_Applicable_ID'], [2,3])){
          if(empty(array_filter($applicable_in[$scheme_data['ID']][$structure['ID']][$structure['Fee_Applicable_ID']]))){
            exit(json_encode(['status'=>403, 'message'=>'Please select Fee Applicable!']));
          }
        }
      }
    }

    // Allot
    $conn->query("DELETE FROM Scheme_Sub_Courses WHERE Sub_Course_ID = $id");
    $conn->query("DELETE FROM Fee_Constant WHERE Sub_Course_ID = $id");

    // Extract and update discount on Sub_Courses if coming from frontend
    $sub_course_discount = 0;
    foreach($discount as $s_id => $structs){
      foreach($structs as $st_id => $val){
        if($val !== '' && $val !== null){
          $sub_course_discount = intval($val);
          break 2;
        }
      }
    }
    $conn->query("UPDATE Sub_Courses SET discount = $sub_course_discount WHERE ID = $id");

    foreach($schemes as $scheme_id){
      $update = $conn->query("INSERT INTO Scheme_Sub_Courses (Scheme_ID, Sub_Course_ID) VALUES ($scheme_id, $id)");
      if($update){
        $scheme_query = $conn->query("SELECT ID, Name, Fee_Structure, University_ID FROM Schemes WHERE ID = ".$scheme_id);
        $scheme_data = $scheme_query->fetch_assoc();
  
        $feeStructureIds = implode(",", json_decode($scheme_data['Fee_Structure'], true));
        $structures = $conn->query("SELECT ID, Name, Fee_Applicable_ID FROM Fee_Structures WHERE Is_Constant = 1 AND ID IN ($feeStructureIds) AND University_ID = ".$scheme_data['University_ID']);
        while($structure = $structures->fetch_assoc()){
          if($structure['Fee_Applicable_ID']==1){
            $applicable = json_encode([$structure['Fee_Applicable_ID'] => range(1, $duration)]);
          }elseif($structure['Fee_Applicable_ID']==4){
            $applicable = json_encode([$structure['Fee_Applicable_ID'] => []]);
          }elseif(in_array($structure['Fee_Applicable_ID'], [2,3])){
            $applicable = json_encode([$structure['Fee_Applicable_ID'] => $applicable_in[$scheme_data['ID']][$structure['ID']][$structure['Fee_Applicable_ID']]]);
          }

          $update = $conn->query("INSERT INTO Fee_Constant (`Fee_Structure_ID`, `Scheme_ID`, `University_ID`, `Fee`, `Sub_Course_ID`, `Applicable_In`) VALUES (".$structure['ID'].", ".$scheme_data['ID'].", ".$scheme_data['University_ID'].", ".$fee[$scheme_data['ID']][$structure['ID']].", $id, '$applicable')");
        } 
      }
    }

    if($update){
      echo json_encode(['status'=>200, 'message'=>'Scheme alloted successfully!']);
    }else{
      echo json_encode(['status'=>403, 'message'=>'Something went wrong!']);
    }

  }else{
    echo json_encode(['status'=>403, 'message'=>'Please select Scheme(s)']);
  }
?>