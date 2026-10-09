<?php
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
if (isset($_GET['student_id'])) {
  require '../../includes/db-config.php';
  session_start();

  if ($_SESSION['university_id'] != 23) {
    header('Location: /');
  }

  $id = mysqli_real_escape_string($conn, $_GET['student_id']);
  $id = base64_decode($id);
  $id = intval(str_replace('W1Ebt1IhGN3ZOLplom9I', '', $id));
  $student = $conn->query("SELECT Students.*, Courses.Name as Course,Courses.Short_Name as Course_Short_Name, Sub_Courses.Name as Sub_Course, Admission_Sessions.Name as Session, Admission_Types.Name as Type, Universities.Vertical, TIMESTAMPDIFF(YEAR, DOB, CURDATE()) AS Age FROM Students LEFT JOIN Universities ON Students.University_ID = Universities.ID LEFT JOIN Courses ON Students.Course_ID = Courses.ID LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID LEFT JOIN Admission_Types ON Students.Admission_Type_ID = Admission_Types.ID WHERE Students.ID = $id");
  $student = mysqli_fetch_assoc($student);
  $address = json_decode($student['Address'], true);


  require_once('../../extras/vendor/setasign/fpdf/fpdf.php');
  require_once('../../extras/vendor/setasign/fpdi/src/autoload.php');

  $pdf = new Fpdi();

  $pdf->SetTitle('Application Form');
  $pageCount = $pdf->setSourceFile('aiu-university.pdf');
  $pdf->AddFont('Hondo', '', 'hondo.php');
  $pdf->SetFont('Hondo', '', 10);

  // Tick Image
  $check = '../../assets/img/forms/checked.png';

  // Extensions
  $file_extensions = array('.png', '.jpg', '.jpeg');

  //this folder will have there images.
  $path = "photos/";

  // Photo
  $student_photo = "";
  $photo = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = $id AND Type = 'Photo'");
  if ($photo->num_rows > 0) {
    $photo = mysqli_fetch_assoc($photo);
    $photo = "../.." . $photo['Location'];
    $student_photo = base64_encode(file_get_contents($photo));
    $i = 0;
    $end = 3;
    while ($i < $end) {
      $data1 = base64_decode($student_photo);
      $filename1 = $id . "_Photo" . $file_extensions[$i]; //$file_extensions loops through the file extensions
      file_put_contents($path . $filename1, $data1); //we save our new images to the path above
      $i++;
    }
  } else {
    $photo = "";
  }

  // Signature
  $student_signature = "";
  $signature = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = $id AND Type = 'Student Signature'");
  if ($signature->num_rows > 0) {
    $signature = mysqli_fetch_assoc($signature);
    $signature = "../.." . $signature['Location'];
    $student_signature = base64_encode(file_get_contents($signature));
    $i = 0;
    $end = 3;
    while ($i < $end) {
      $data2 = base64_decode($student_signature);
      $filename2 = $id . "_Signature" . $file_extensions[$i]; //$file_extensions loops through the file extensions
      file_put_contents($path . $filename2, $data2); //we save our new images to the path above
      $i++;
    }
  } else {
    $signature = "";
  }

  // Page 1
  $pageId = $pdf->importPage(1, PdfReader\PageBoundaries::MEDIA_BOX);
  $pdf->addPage();
  $pdf->useImportedPage($pageId, 0, 0, 210);

  // Photo
  if (filetype($photo) === 'file' && file_exists($photo)) {
    try {
      $filename = $id . "_Photo" . $file_extensions[0];
      $image = $path . $filename;
      $pdf->Image($image, 158, 41.2, 27, 31.7);
      $photo = $image;
    } catch (Exception $e) {
      try {
        $filename = $id . "_Photo" . $file_extensions[1];
        $image = $path . $filename;
        $pdf->Image($image, 158, 41.2, 27, 31.7);
        $photo = $image;
      } catch (Exception $e) {
        try {
          $filename = $id . "_Photo" . $file_extensions[2];
          $image = $path . $filename;
          $pdf->Image($image, 158, 41.2, 27, 31.7);
          $photo = $image;
        } catch (Exception $e) {
          echo 'Message: ' . $e->getMessage();
        }
      }
    }
  }
  $form_completion_date = date("d-m-Y", strtotime($student['form_completion_date']?$student['form_completion_date']:$student['Created_At']));
//   echo('<pre>');print_r($student);die;
  $pdf->SetXY(54.5, 127);
  $pdf->Write(1, strtoupper($student['Course']));
  $pdf->SetXY(71.5, 137.8);
  $pdf->Write(1, strtoupper($student['Sub_Course']));
    $pdf->SetXY(44.5, 148);
$session = DateTime::createFromFormat('M-y', $student['Session'])->format('M-Y');
$pdf->Write(1, $session);
  if ($student['Gender'] == 'Male') {
      $pdf->Image($check, 50.5, 175.5, 3, 3);
    
  } else if ($student['Gender'] == 'Female') {
    $pdf->Image($check, 74, 175.5, 3, 3);
  }else{
      $pdf->Image($check, 97, 175.5, 3, 3);
  }
  
  $dob = date("dmy", strtotime($student['DOB']));
  $dobChars = str_split($dob);
  $xPositions = [
    147.2,
    155.2,
    166,
    173,
    183.5,
    191.5,

  ];
  $y = 177;
  foreach ($dobChars as $index => $char) {
    $pdf->SetXY($xPositions[$index], $y);
    $pdf->Write(1, $char);
  }
  $student_name = $student['First_Name'] . ' ' . $student['Middle_Name'] . ' ' . $student['Last_Name'];

 $x = 50.5;
  $y = 188;
  foreach (mb_str_split($student_name) as $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x += 7;
  }
  $x = 50.5;
  $y = 202;
  foreach (mb_str_split($student['Father_Name']) as $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x += 7;
  }
    $x = 50.5;
  $y = 211.5;
  foreach (mb_str_split($student['Mother_Name']) as $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x += 7;
  }
    if(!empty($student['Aadhar_Number']) && strtolower($student['Aadhar_Number']) !== "not available now"){

  $x = 50.5;
  $y = 220;
  $aadhar = str_replace('-', '', strtoupper($student['Aadhar_Number']));
  foreach (mb_str_split(strtoupper($aadhar)) as $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x += 7;
  }}
  
//     $pdf->SetXY(146, 195);
//   $pdf->Write(1, strtoupper($student['Marital_Status']));
    $x = 50.5;
  $y = 229.5;
  foreach (mb_str_split($student['Email']) as $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x += 7;
  }
     $x = 50.5;
  $y = 239;
  foreach (mb_str_split($student['Marital_Status']) as $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x += 7;
  }
//     $x = 47.3;
//   $y = 210;
//   foreach (mb_str_split($student['Contact']) as $char) {
//     $pdf->SetXY($x, $y);
//     $pdf->Write(1, $char);
//     $x += 5.4;
//   }
  if ($student['Category'] == 'SC') {
     $pdf->Image($check, 50.8, 247.5, 3, 3);
  }    else if ($student['Category'] == 'ST') {
    $pdf->Image($check, 66.8, 247.5, 3, 3);
  }  else if ($student['Category'] == 'OBC') {
     $pdf->Image($check, 83, 247.5, 3, 3);
  } else if ($student['Category'] == 'General') {
    $pdf->Image($check, 100.5, 247.5, 3, 3);
   
  } else{
      $pdf->Image($check, 113, 247.5, 3, 3);
  }
  $pdf->SetXY(55, 259.9);
  $pdf->Write(1, strtoupper($student['Nationality']));
  
//   if ($student['Religion'] == 'Hindu') {
//      $pdf->Image($check, 43.8, 222, 3, 3);
     
//   }    else if ($student['Religion'] == 'Muslim') {
//     $pdf->Image($check, 67, 222, 3, 3);
//   }  else if ($student['Religion'] == 'Sikh') {
//       $pdf->Image($check, 86, 222, 3, 3);
//   } else if ($student['Religion'] == 'Christian') {
//     $pdf->Image($check, 108, 222, 3, 3);
   
//   } else if ($student['Religion'] == 'Jain') {
//       $pdf->Image($check, 127.8, 222, 3, 3);
//   }
//   $form_completion_date = date("d-m-Y", strtotime($student['form_completion_date']?$student['form_completion_date']:$student['Created_At']));
//   $pdf->SetXY(18, 247);
//   $pdf->Write(1, $form_completion_date);
//   $pdf->SetXY(153.5, 247.7);
//   $pdf->Write(1, strtoupper($student['Unique_ID']));
//   $pdf->SetXY(160.5, 8);
//   $pdf->Write(1, strtoupper($student['Unique_ID']));
//   $pdf->SetXY(55.5, 8);
//   $pdf->Write(1, "Mode: OL");
// //   $pdf->SetXY(85.5, 30);
// //   $pdf->Write(1, $student['Session']);
//   $pdf->SetXY(16.5, 254.7);
//   $pdf->Write(1, strtoupper($student_name));
//   $pdf->SetXY(113.5, 254.7);
//   $pdf->Write(1, strtoupper($student['Father_Name']));
  
// //   echo('<pre>');print_r($student);die;
// $pdf->SetFont('Hondo', '', 8);
// // $pdf->SetXY(34.5, 263.7);
// //   $pdf->Write(1, strtoupper($student['Course_Short_Name'].'('.$student['Sub_Course'].')'));
// $pdf->SetXY(34.5, 261.7);
// $pdf->MultiCell(68, 5, strtoupper($student['Course_Short_Name'] . '(' . $student['Sub_Course'] . ')'), 0, 'L');
//   $pdf->SetFont('Hondo', '', 10);
//     if (filetype($signature) === 'file' && file_exists($signature)) {
//     try {
//       $filename = $id . "_Signature" . $file_extensions[0];
//       $image = $path . $filename;
//       $pdf->Image($image, 117, 257.7, 32, 8);
//       $student_signature = $image;
//     } catch (Exception $e) {
//       try {
//         $filename = $id . "_Signature" . $file_extensions[1];
//         $image = $path . $filename;
//         $pdf->Image($image, 117, 257.7, 32, 8);
//         $student_signature = $image;
//       } catch (Exception $e) {
//         try {
//           $filename = $id . "_Signature" . $file_extensions[2];
//           $image = $path . $filename;
//           $pdf->Image($image, 117, 257.7, 32, 8);
//           $student_signature = $image;
//         } catch (Exception $e) {
//           echo 'Message: ' . $e->getMessage();
//         }
//       }
//     }
//   }
//   $session = $student['Session']; // jan-25


  $pageId = $pdf->importPage(2, PdfReader\PageBoundaries::MEDIA_BOX);
  $pdf->SetMargins(0, 0, 0);  // Left, Top, Right margins = 0
$pdf->SetAutoPageBreak(false, 0); 
  $pdf->addPage();
  $pdf->useImportedPage($pageId, 0, 0, 210);
$presentAddress = strtoupper($address['present_address']);
$presentAddress = $presentAddress;

$x = 12;
$y = 26.8;
$lineHeight = 8.5;

$pdf->SetFont('Arial', '', 10);

// Split into characters
$chars = mb_str_split($presentAddress);
$line1 = '';
$line2 = '';

// First 60 characters for line 1
for ($i = 0; $i < count($chars); $i++) {
    if ($i < 100) {
        $line1 .= $chars[$i];
    } else {
        $line2 .= $chars[$i];
    }
}

// Print Line 1 (first 60 characters)
$pdf->SetXY($x, $y);
$pdf->Write($lineHeight, $line1);

// Print Line 2 (remaining characters)
if (!empty($line2)) {
    $pdf->SetXY($x, $y+1 + $lineHeight);
    $pdf->Write($lineHeight, $line2);
}

   $pdf->SetXY(20, 49.5);
  $pdf->Write(1, $address['present_city']);
  $pdf->SetXY(87,49.5);
  $pdf->Write(1, $address['present_state']);
  $pdf->SetXY(157, 49.5);
  $pdf->Write(1, $address['present_pincode']);
   $pdf->SetXY(120, 59.9);
  $pdf->Write(1, strtoupper($student['Contact']));
    $pdf->SetXY(45, 78.5);
  $pdf->Write(1, strtoupper($student['Father_Name']));

//   $pdf->SetXY(156, 111.8);
//   $pdf->Write(1, $address['present_district']);
//   $pdf->SetXY(20, 118.8);
//   $pdf->Write(1, $address['present_state']);
//   $pdf->SetXY(85, 118.8);
//   $pdf->Write(1, $address['present_pincode']);
 $academis = array('High School', 'Intermediate', 'Under Graduation', 'Post Graduation', 'Other');
$pdf->SetFont('Arial', '', 6);

// Manual Y positions for each academic level 98.7
$yPositions = array(
    'High School' => 127.7,
 'Intermediate' => 140.7,
 'Under Graduation' => 158.7,
    'Post Graduation' => 177.7,
);

foreach ($academis as $academic) {

    $x = 22;
    $y = $yPositions[$academic];

    // Type mapping
    $type = ($academic === 'Under Graduation') ? 'UG' :
            (($academic === 'Post Graduation') ? 'PG' : $academic);

    // $query = "
    //     SELECT 
    //         `Board/Institute` AS board,
    //         Year,
    //         Subject,
    //         Total_Marks
    //     FROM Student_Academics 
    //     WHERE Student_ID = $id AND Type = '$type'
    // ";
     $query = "
        SELECT 
           *
        FROM Student_Academics 
        WHERE Student_ID = $id AND Type = '$type'
    ";

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {

        $row = $result->fetch_assoc();
        // echo('<pre>');print_r($row);
        // Academic name
        // $pdf->SetXY($x, $y);
        // $pdf->Write(1, substr($academic, 0, 15));
       
        // Board / Institute
        $x += 27.5;
        $pdf->SetXY($x, $y);
        $pdf->Write(1, strlen($row['Board/Institute']) > 20 ? substr($row['Board/Institute'], 0, 18).'...' : $row['Board/Institute']);
         $x += 73.5;
         $pdf->SetXY($x, $y);
         $pdf->Write(1, substr($row['Max_Marks'], 0, 4));
          $x += 15.5;
         $pdf->SetXY($x, $y);
         $pdf->Write(1, substr($row['Marks_Obtained'], 0, 4));
         $x += 27;
           $pdf->SetXY($x, $y);
           $pdf->Write(1, substr($row['Year'], 0, 4));
            $x += 20;
           $pdf->SetXY($x, $y);
           $pdf->Write(1, substr($row['Subject'], 0, 4));
        // // Year
        //  $x += 16;

        // $pdf->SetXY($x, $y);
        // $pdf->Write(1, $row['Year']);
        // $x += 12.5;
        // // // Subject
        // $pdf->SetFont('Arial', '', 5);
        // $pdf->SetXY($x, $y);
        // $pdf->Write(1, strlen($row['Subject']) > 44 ? substr($row['Subject'], 0, 43).'...' : $row['Subject']);
        // $pdf->SetFont('Arial', '', 6);
       
        // $x += 27;
        // $pdf->SetXY($x, $y);
        // $pdf->Write(1, substr($row['Total_Marks'], 0, 4));
        // $x += 17.5;
        // $pdf->SetXY($x, $y);
        // $pdf->Write(1, strlen($row['board']) > 20 ? substr($row['board'], 0, 18).'...' : $row['board']);
        // $x += 29.5;

        // // Subject
        // $pdf->SetFont('Arial', '', 5);
        // $pdf->SetXY($x, $y);
        // $pdf->Write(1, strlen($row['Subject']) > 44 ? substr($row['Subject'], 0, 43).'...' : $row['Subject']);
        // $pdf->SetFont('Arial', '', 6);
        // $x += 55;

        // // Marks
        // $pdf->SetXY($x, $y);
        // $pdf->Write(1, substr($row['Total_Marks'], 0, 4));
    }
}
   if (filetype($signature) === 'file' && file_exists($signature)) {
    try {
      $filename = $id . "_Signature" . $file_extensions[0];
      $image = $path . $filename;
      $pdf->Image($image, 45, 256.7, 52, 10);
      $student_signature = $image;
    } catch (Exception $e) {
      try {
        $filename = $id . "_Signature" . $file_extensions[1];
        $image = $path . $filename;
        $pdf->Image($image, 45, 256.7, 52, 10);
        $student_signature = $image;
      } catch (Exception $e) {
        try {
          $filename = $id . "_Signature" . $file_extensions[2];
          $image = $path . $filename;
          $pdf->Image($image, 45, 256.7, 52, 10);
          $student_signature = $image;
        } catch (Exception $e) {
          echo 'Message: ' . $e->getMessage();
        }
      }
    }
  }
  $parent_signature = "";
$p_signature = "";
$p_signature = $conn->query("SELECT Location FROM Student_Documents
WHERE Student_ID = $id AND Type = 'Parent Signature'");
if ($p_signature->num_rows > 0) {
    $p_signature = mysqli_fetch_assoc($p_signature);
    $p_signature = "../.." . $p_signature['Location'];

    $parent_signature = base64_encode(file_get_contents($p_signature));
    $i = 0;
    $end = 3;
    while ($i < $end) {
        $data2 = base64_decode($parent_signature);
        $filename2 = $id . "_parent_Signature" . $file_extensions[$i];
        //$file_extensions loops through the file extensions
        file_put_contents($filename2, $data2); //we save our new images to the path above
        $i++;
    }
} else {
    $p_signature = "";
}
  if (filetype($p_signature) === 'file' && file_exists($p_signature)) {
    try {
        $filename = $id . "_parent_Signature" . $file_extensions[0];
        $image = $filename;
        $pdf->Image($image, 52, 270.7, 48, 10);
        $parent_signature = $image;
    } catch (Exception $e) {
        try {
            $filename = $id . "_parent_Signature" . $file_extensions[1];
            $image = $filename;
            $pdf->Image($image, 52, 270.7, 48, 10);
            $parent_signature = $image;
        } catch (Exception $e) {
            try {
                $filename = $id . "_Signature" . $file_extensions[2];
                $image = $filename;
                $pdf->Image($image, 52, 270.7, 48, 10);
                $parent_signature = $image;
            } catch (Exception $e) {
                echo 'Message: ' . $e->getMessage();
            }
        }
    }
}
  $pdf->SetFont('Arial', '', 10);
   $pdf->SetXY(138.7, 265);
  $pdf->Write(1, $student['Father_Name']);
  $pdf->SetXY(109,279.5);
  $pdf->Write(1, $address['present_state']);
   $pdf->SetXY(165, 279.5);
  $pdf->Write(1, $form_completion_date);
$documents = $conn->query("SELECT Type, Location FROM Student_Documents WHERE Student_ID = $id and Type!='Student Signature' and Type!='Photo'");
      while ($document = $documents->fetch_assoc()) {
        $files = explode("|", $document['Location']);
        foreach ($files as $file) {
          $pdf->AddPage();
          $pdf->SetMargins(10, 10, 10);
          // print_r(pathinfo("../..".$file));die;
          // print_r(mime_content_type("../..".$file));die;
          if (!file_exists("../.." . $file)) {
            die('Error: File not found at ' . "../.." . $file);
          }
          // if (mime_content_type("../..".$file) !== 'image/jpeg') {
          //     die('Error: File is not a valid PNG.');
          // }

          $width = $document['Type'] == 'Photo' ? 30.5 : ($document['Type'] == 'Student Signature' ? 30.2 : 190);
          $height = $document['Type'] == 'Photo' ? 35.9 : ($document['Type'] == 'Student Signature' ? 11.3 : 270);

          $pdf->image("../.." . $file, 10, 10, $width, $height);
        }
      }

$pdf->Output('I', 'AIU.pdf');
$i = 0;
$end = 3;
while ($i < $end) {
    // Delete Photos
    if (!empty($student_photo)) {
        $filename = $id . "_Photo" . $file_extensions[$i];
        //$file_extensions loops through the file extensions
        unlink($filename);
    }
    if (!empty($Parent_signature)) {
        $filename = $id . "_Parent_Signature" . $file_extensions[$i];
        //$file_extensions loops through the file extensions
        unlink($filename);
    }
    // Delete Signatures
    if (!empty($student_signature)) {
        $filename = $id . "_Student_Signature" . $file_extensions[$i];
        //$file_extensions loops through the file extensions
        unlink($filename);
    }    
    $i++;
}}