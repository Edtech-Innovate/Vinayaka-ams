<?php
require '../../includes/db-config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$student_id = $_GET['student_id'];
$id = base64_decode($student_id);
$id = intval(str_replace('W1Ebt1IhGN3ZOLplom9I', '', $id));

// $get_record = $conn->query("SELECT Students.*,   Admission_Sessions.Name AS Addmission_session,Courses.Name AS Course_name FROM Students LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID=Admission_Sessions.ID LEFT JOIN Students.Course_ID = Courses.ID  WHERE Students.ID= $id ");
$get_record = $conn->query("SELECT Students.*,Admission_Sessions.Name AS Admission_session, Courses.Short_Name AS Course_name, Sub_Courses.Name AS SubCourses FROM Students LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID LEFT JOIN Courses ON Students.Course_ID = Courses.ID LEFT JOIN Sub_Courses ON Students.Sub_Course_ID= Sub_Courses.ID WHERE Students.ID = $id");
$student = mysqli_fetch_assoc($get_record);



$student_name = $student['First_Name'] . ' ' . $student['Middle_Name'] . ' ' . $student['Last_Name'];
$dob = $student['DOB'];
list($year, $month, $day) = explode('-', $dob);
$address = json_decode($student['Address'], true);
// print_r( $address['present_city']);die;
$present_address = str_split($address['present_address'], 18);
$file_extensions = array('.png', '.jpg', '.jpeg');
 $check = '../../assets/img/forms/checked.png';
//this folder will have there images.


use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

ob_end_clean();

require_once('../../extras/TCPDF/tcpdf.php');
require_once('../../extras/vendor/setasign/fpdf/fpdf.php');
require_once('../../extras/vendor/setasign/fpdi/src/autoload.php');

$pdf = new Fpdi();
$pdf->SetMargins(0, 0, 0);  // Left, Top, Right margins = 0
$pdf->SetAutoPageBreak(false, 0);  // Disable auto page break with 0 bottom margin
$pdf->AddPage();
$pdf->setSourceFile('../../forms/18/mts-student-form.pdf');
$tplId = $pdf->importPage(1);
$pdf->useTemplate($tplId, 0, 0, 210);

// Set a standard font (Arial in this case)
$pdf->SetFont('Arial', '', 10);

$path = "photos/";

// Photo
$student_photo = "";
$photo = "";
$photo = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = $id AND Type = 'Photo'");
if ($photo->num_rows > 0) {
    $photo = mysqli_fetch_assoc($photo);
    // echo "<pre>";
    // print_r($photo);die;
    $photo = "../.." . $photo['Location'];
    // echo $photo['Location'];die;
    $student_photo = base64_encode(file_get_contents($photo));
    $i = 0;
    $end = 3;
    while ($i < $end) {
        $data1 = base64_decode($student_photo);

        $filename1 = $id . "_Photo" . $file_extensions[$i];
        file_put_contents($filename1, $data1); //we save our new images to the path above
        $i++;
    }
} else {
    $photo = "";
}

// Set position and add content
if (filetype($photo) === 'file' && file_exists($photo)) {
    try {
        $filename = $id . "_Photo" . $file_extensions[0];
        $image = $filename;
        $pdf->Image($image, 163.5, 57.5, 37, 41);
        $photo = $image;
    } catch (Exception $e) {
        try {
            $filename = $id . "_Photo" . $file_extensions[1];
            $image = $filename;
            $pdf->Image($image, 163.5, 57.5, 37, 41);
            $photo = $image;
        } catch (Exception $e) {
            try {
                $filename = $id . "_Photo" . $file_extensions[2];
                $image = $filename;
                $pdf->Image($image, 163.5, 57.5, 37, 41);
                $photo = $image;
            } catch (Exception $e) {
                echo 'Message: ' . $e->getMessage();
            }
        }
    }
}
// echo('<pre>');print_r($student);die;
// $student['Enrollment_No']='TEM123456T';
$x = 145;
  $y = 49.9;
  foreach (mb_str_split($student['Enrollment_No']) as $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x += 5;
  }
  $x = 99;
  $y = 88;
  foreach (mb_str_split($student['Enrollment_No']) as $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x += 5;
  }
    $x = 12;
  $y = 163;
  foreach (mb_str_split($student_name) as $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x += 6.3;
  }
  $x = 12;
  $y = 177;
  foreach (mb_str_split($student['Father_Name']) as $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x += 6.1;
  }
  if(!empty($student['Aadhar_Number']) && strtolower($student['Aadhar_Number']) !== "not available now"){
  $aadhar = str_replace('-', '', strtoupper($student['Aadhar_Number']));
    $x = 12;
  $y = 189;
  foreach (mb_str_split($aadhar) as $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x += 6.1;
  }}
    $x = 12;
  $y = 203;
  foreach (mb_str_split($student['Contact']) as $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x += 6.1;
  }
      $x = 12;
  $y = 216.5;
  foreach (mb_str_split($student['Email']) as $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x += 6.3;
  }
  if(!empty($student['abc_id'])) {
       $x = 12;
  $y = 229;
  foreach (mb_str_split($student['abc_id']) as $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x += 6.3;
  }   
  }
    $dob = date("dmY", strtotime($student['DOB']));
  $dobChars = str_split($dob);
  $xPositions = [
    11.2,
    18,
    24,
    30.5,
    36.5,
    42.5,
    49.5,
    55.5
  ];
  $y = 100.3;
  foreach ($dobChars as $index => $char) {
    $pdf->SetXY($xPositions[$index], $y);
    $pdf->Write(1, $char);
  }
//   echo('<pre>');print_r($student['Nationality']);die;
   if($student['Nationality']=='Indian'){
    // $pdf->SetXY(33, 29.5);
    // $pdf->Cell(0, 0, $student['Nationality'], 0, 0, 'L', 0);
    $pdf->Image($check, 72, 99.1, 2, 2);
}else{
    $pdf->Image($check, 72, 106.1, 2, 2);
// $pdf->SetXY(33, 129.5);
// $pdf->Cell(0, 0, $student['Nationality'], 0, 0, 'L', 0);
    
}
  $student_signature = "";
$signature = "";
$signature = $conn->query("SELECT Location FROM Student_Documents
WHERE Student_ID = $id AND Type = 'Student Signature'");
if ($signature->num_rows > 0) {
    $signature = mysqli_fetch_assoc($signature);
    $signature = "../.." . $signature['Location'];
    $student_signature = base64_encode(file_get_contents($signature));
    $i = 0;
    $end = 3;
    while ($i < $end) {
        $data2 = base64_decode($student_signature);
        $filename2 = $id . "_Student_Signature" . $file_extensions[$i];
       
        file_put_contents($filename2, $data2); 
        $i++;
    }
} else {
    $signature = "";
}
if (filetype($signature) === 'file' && file_exists($signature)) {
    try {
        $filename = $id . "_Student_Signature" . $file_extensions[0];
        $image = $filename;
        $pdf->Image($image, 145, 99.5, 55.2, 9.3);
        $student_signature = $image;
    } catch (Exception $e) {
        try {
            $filename = $id . "_Student_Signature" . $file_extensions[1];
            $image = $filename;
            $pdf->Image($image, 145, 99.5, 55.2, 9.3);
            $student_signature = $image;
        } catch (Exception $e) {
            try {
                $filename = $id . "_Signature" . $file_extensions[2];
                $image = $filename;
                $pdf->Image($image, 145, 99.5, 55.2, 9.3);
                $student_signature = $image;
            } catch (Exception $e) {
                echo 'Message: ' . $e->getMessage();
            }
        }
    }
}
  if ($student['Gender'] == 'Male') {
      $pdf->Image($check, 26.5, 111.5, 3, 3);
     
        // $pdf->Image($check, 26.5, 118.5, 3, 3);
    
  } else if ($student['Gender'] == 'Female') {
        $pdf->Image($check, 26.5, 118.5, 3, 3);
  }else{
    $pdf->Image($check, 26.5, 124.5, 3, 3);
  }
  if ($student['Marital_Status'] == 'Married') {
        $pdf->Image($check, 159, 121.5, 3, 3);
       
  }else{
       $pdf->Image($check, 159, 129.5, 3, 3);
  }
    
  if ($student['Religion'] == 'Hindu') {
     $pdf->Image($check, 13, 140, 2, 2);
    
  }  else if ($student['Religion'] == 'Muslim') {
       $pdf->Image($check, 13, 145, 2, 2);
  }  else if ($student['Religion'] == 'Sikh') {
       $pdf->Image($check, 34, 139.5, 2, 2);
  } else if ($student['Religion'] == 'Christian') {
       $pdf->Image($check, 13, 150, 2, 2);
   
  } else if ($student['Religion'] == 'Jain') {
       $pdf->Image($check, 34, 145, 2, 2);
  }
     $pageId = $pdf->importPage(2, PdfReader\PageBoundaries::MEDIA_BOX);
  $pdf->addPage();
  $pdf->useImportedPage($pageId, 0, 0, 210);
  
     $pageId = $pdf->importPage(3, PdfReader\PageBoundaries::MEDIA_BOX);
  $pdf->addPage();
  $pdf->useImportedPage($pageId, 0, 0, 210);
$presentAddress = strtoupper($address['present_address']);
$pdf->SetFont('Arial', '', 10);

// ---- LINE 1 CONFIG ----
$x1 = 10;
$y1 = 80.8;
$boxWidth1 = 7.6;
$rowWidth1 = 185;  // total width available for the row (measure from form: last box right-edge - x1)
$boxesLine1 = floor($rowWidth1 / $boxWidth1); // auto-calculated, no more hardcoding

// ---- LINE 2 CONFIG ----
$x2 = 10;
$y2 = 87;
$boxWidth2 = 7.6;
$rowWidth2 = 210;
$boxesLine2 = floor($rowWidth2 / $boxWidth2);

// ------------------------------------------------
$maxChars = $boxesLine1 + $boxesLine2;
$presentAddress = mb_substr($presentAddress, 0, $maxChars);

$chars = mb_str_split($presentAddress);
$line1 = array_slice($chars, 0, $boxesLine1);
$line2 = array_slice($chars, $boxesLine1, $boxesLine2);

function printBoxedLine($pdf, $charsArray, $x, $y, $boxWidth, $lineHeight) {
    $cx = $x;
    foreach ($charsArray as $char) {
        $charWidth = $pdf->GetStringWidth($char);
        $offset = ($boxWidth - $charWidth) / 2;
        $pdf->SetXY($cx + $offset, $y);
        $pdf->Write($lineHeight, $char);
        $cx += $boxWidth;
    }
}

printBoxedLine($pdf, $line1, $x1 - 1, $y1, $boxWidth1, 5);
if (!empty($line2)) {
    printBoxedLine($pdf, $line2, $x2, $y2 + 5.5, $boxWidth2, 5);
}
       $x = 12;
  $y = 108.5;
  foreach (mb_str_split($address['present_city']) as $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x += 7.6;
  } 
       $x = 12;
  $y = 123.5;
  foreach (mb_str_split($address['present_state']) as $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x += 6.3;
  } 
  $x = 103.3;
  $y = 109.5;
  foreach (mb_str_split($address['present_district']) as $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x += 6.3;
  } 
   $x = 104.3;
  $y = 124.5;
  foreach (mb_str_split($address['present_pincode']) as $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x += 6.3;
  } 
     $x = 22.3;
  $y = 175.5;
  $formattedDate = date('d-m-Y', strtotime($student['form_completion_date']));
  foreach (mb_str_split($formattedDate) as $char) {
    $pdf->SetXY($x, $y);
    $pdf->Write(1, $char);
    $x += 6.3;
  } 
  if (filetype($signature) === 'file' && file_exists($signature)) {
    try {
        $filename = $id . "_Student_Signature" . $file_extensions[0];
        $image = $filename;
        $pdf->Image($image, 113.2, 167, 85.2, 12);
        $student_signature = $image;
    } catch (Exception $e) {
        try {
            $filename = $id . "_Student_Signature" . $file_extensions[1];
            $image = $filename;
            $pdf->Image($image, 113.2, 167, 85.2, 12);
            $student_signature = $image;
        } catch (Exception $e) {
            try {
                $filename = $id . "_Signature" . $file_extensions[2];
                $image = $filename;
                $pdf->Image($image, 113.2, 167, 85.2, 12);
                $student_signature = $image;
            } catch (Exception $e) {
                echo 'Message: ' . $e->getMessage();
            }
        }
    }
}
//   echo('<pre>');print_r($student);die;
// $pdf->SetXY(155, 39);
// $pdf->Cell(0, 0, $student['Unique_ID'], 0, 0, 'L', 0);
// $pdf->SetXY(18.5,64);
// $pdf->Cell(0, 0, $student_name, 0, 0, 'C', 0);
// $pdf->SetXY(46.5, 72.5);
// $pdf->Cell(0, 0, $student['Course_name'].' ('.$student['SubCourses'].')', 0, 0, 'L', 0);
//   if ($student['Gender'] == 'Male') {
//       $pdf->Image($check, 53, 97.5, 3, 3);
     
    
//   } else if ($student['Gender'] == 'Female') {
//     $pdf->Image($check, 83, 97.5, 3, 3);
//   }else{
//       $pdf->Image($check, 110, 97.5, 3, 3);
//   }
  
//   if ($student['Category'] == 'SC') {
//       $pdf->Image($check, 74, 105.6, 3, 3);
//   }    else if ($student['Category'] == 'ST') {
//  $pdf->Image($check, 91, 105.6, 3, 3);
//   }  else if ($student['Category'] == 'OBC') {
//      $pdf->Image($check, 130, 105.6, 3, 3);
//   } else if ($student['Category'] == 'General') {
//     $pdf->Image($check, 53, 105.6, 3, 3);
    
  
   
//   } else{
//       $pdf->Image($check, 60, 105.6, 3, 3);
//   }
//      $dob = date("dmY", strtotime($student['DOB']));
//   $dobChars = str_split($dob);
//   $xPositions = [
//     43.2,
//     47.2,
//     53.5,
//     58.5,
//     65,
//     70,
//     75,
//     80
//   ];
//   $y = 136.3;
//   foreach ($dobChars as $index => $char) {
//     $pdf->SetXY($xPositions[$index], $y);
//     $pdf->Write(1, $char);
//   }
// //   echo('<pre>');print_r($student['Aadhar_Number']);die;
// if(!empty($student['Aadhar_Number']) && strtolower($student['Aadhar_Number']) !== "not available now"){
//   $aadhar = str_replace('-', '', strtoupper($student['Aadhar_Number']));
//   $studentadhaar = str_split($aadhar);
//     $xPositions = [
//     43.2,
//     47.2,
//     52.5,
//     57,
//     64,
//     69,
//     74,
//     79,
//     85,
//     90,
//     95,
//     100
//   ];
//   $y = 143.8;
//   foreach ($studentadhaar as $index => $char) {
//     $pdf->SetXY($xPositions[$index], $y);
//     $pdf->Write(1, $char);
//   }}
 
// $pdf->SetXY(34, 159.5);
// $pdf->Cell(0, 0, $student['Father_Name'], 0, 0, 'L', 0);
// $pdf->SetXY(35, 175);
// $pdf->Cell(0, 0, $student['Mother_Name'], 0, 0, 'L', 0);
// if($student['Nationality']=="NRI"){
//     $pdf->SetXY(33, 189.5);
//     $pdf->Cell(0, 0, $student['Nationality'], 0, 0, 'L', 0);
//     $pdf->Image($check, 109.5, 194, 3, 3);
// }else{
// $pdf->SetXY(33, 189.5);
// $pdf->Cell(0, 0, $student['Nationality'], 0, 0, 'L', 0);
    
// }
// $pdf->SetXY(47.5, 203.5);
// $pdf->Cell(0, 0, $address['present_address'], 0, 0, 'L', 0);
// $pdf->SetXY(41.5, 209.5);
// $pdf->Cell(0, 0, $student['Contact'], 0, 0, 'L', 0);
// $pdf->SetXY(114.5, 209.5);
// $pdf->Cell(0, 0, $student['Email'], 0, 0, 'L', 0);
// $doc = $conn->query("SELECT * FROM Student_Academics WHERE Student_Academics.Student_ID = $id");
// $x_cor = 55;
// $y_cor = 253;
// $pdf->SetXY($x_cor, $y_cor);
// $pdf->SetFont('Arial', '', 7);
// while ($row = $doc->fetch_assoc()) {
//     // echo('<pre>');print_r($row);
//     $pdf->SetX($x_cor);
//     $cellHeight = 7;
//     $pdf->Cell(17, $cellHeight, $row['Year'], 0, 0, 'C');
//         $pdf->SetX($x_cor + 16);
//       $pdf->Cell(12, $cellHeight, $row['Marks_Obtained'], 0, 0, 'L');
//     $pdf->Cell(10, $cellHeight, $row['Max_Marks'], 0, 0, 'L');
//     $pdf->Cell(8, $cellHeight, $row['Total_Marks'], 0, 0, 'L');
//     $boardy = $y_cor ;
//     $boardx = $x_cor +46;
//     $pdf->SetXY($boardx, $boardy + 1.3);
//      $pdf->MultiCell(40, $cellHeight-4.5, $row['Board/Institute'].'dcccc  cccasdas dasdas dasdas',0, 0, 0, 'L');
  
//     $subjectY = $y_cor - 4;
//     $subjectX = $x_cor +122 ;
    
//     if (strlen($row['Subject']) > 30) {
//         $pdf->SetXY($subjectX, $subjectY + 3);
//         $pdf->SetFont('Arial', '', 6);
//         $nameParts = explode("\n", wordwrap($row['Subject'], 40));
//         $pdf->MultiCell(20, 3, $nameParts[0] . (isset($nameParts[1]) ? chr(10) . $nameParts[1] : ''), 0, 'L');
//     } else {
//         $pdf->SetFont('Arial', '', 6);
//           $pdf->SetXY($subjectX, $subjectY + 3);
//         $pdf->Cell(21, 8, $row['Subject'], 0, 0, 'L');
//     }
//     // $pdf->SetFont('Arial', '', 8);
//     // // echo('<pre>');print_r($row);die;

//     $y_cor += $cellHeight;
//     $pdf->SetXY($x_cor, $y_cor);
// }
//   $pageId = $pdf->importPage(2, PdfReader\PageBoundaries::MEDIA_BOX);
//   $pdf->addPage();
//   $pdf->useImportedPage($pageId, 0, 0, 210);
//   $student_signature = "";
// $signature = "";
// $signature = $conn->query("SELECT Location FROM Student_Documents
// WHERE Student_ID = $id AND Type = 'Student Signature'");
// if ($signature->num_rows > 0) {
//     $signature = mysqli_fetch_assoc($signature);
//     $signature = "../.." . $signature['Location'];
//     $student_signature = base64_encode(file_get_contents($signature));
//     $i = 0;
//     $end = 3;
//     while ($i < $end) {
//         $data2 = base64_decode($student_signature);
//         $filename2 = $id . "_Student_Signature" . $file_extensions[$i];
       
//         file_put_contents($filename2, $data2); 
//         $i++;
//     }
// } else {
//     $signature = "";
// }
// if (filetype($signature) === 'file' && file_exists($signature)) {
//     try {
//         $filename = $id . "_Student_Signature" . $file_extensions[0];
//         $image = $filename;
//         $pdf->Image($image, 170, 62, 30.2, 7.3);
//         $student_signature = $image;
//     } catch (Exception $e) {
//         try {
//             $filename = $id . "_Student_Signature" . $file_extensions[1];
//             $image = $filename;
//             $pdf->Image($image, 170, 62, 30.2, 7.3);
//             $student_signature = $image;
//         } catch (Exception $e) {
//             try {
//                 $filename = $id . "_Signature" . $file_extensions[2];
//                 $image = $filename;
//                 $pdf->Image($image, 170, 62, 30.2, 7.3);
//                 $student_signature = $image;
//             } catch (Exception $e) {
//                 echo 'Message: ' . $e->getMessage();
//             }
//         }
//     }
// }
// if (filetype($signature) === 'file' && file_exists($signature)) {
//     try {
//         $filename = $id . "_Student_Signature" . $file_extensions[0];
//         $image = $filename;
//         $pdf->Image($image, 170, 138, 30.2, 7.3);
//         $student_signature = $image;
//     } catch (Exception $e) {
//         try {
//             $filename = $id . "_Student_Signature" . $file_extensions[1];
//             $image = $filename;
//             $pdf->Image($image, 170, 138, 30.2, 7.3);
//             $student_signature = $image;
//         } catch (Exception $e) {
//             try {
//                 $filename = $id . "_Signature" . $file_extensions[2];
//                 $image = $filename;
//                 $pdf->Image($image, 170, 138, 30.2, 7.3);
//                 $student_signature = $image;
//             } catch (Exception $e) {
//                 echo 'Message: ' . $e->getMessage();
//             }
//         }
//     }
// }
// $parent_signature = "";
// $p_signature = "";
// $p_signature = $conn->query("SELECT Location FROM Student_Documents
// WHERE Student_ID = $id AND Type = 'Parent Signature'");
// if ($p_signature->num_rows > 0) {
//     $p_signature = mysqli_fetch_assoc($p_signature);
//     $p_signature = "../.." . $p_signature['Location'];

//     $parent_signature = base64_encode(file_get_contents($p_signature));
//     $i = 0;
//     $end = 3;
//     while ($i < $end) {
//         $data2 = base64_decode($parent_signature);
//         $filename2 = $id . "_parent_Signature" . $file_extensions[$i];
//         //$file_extensions loops through the file extensions
//         file_put_contents($filename2, $data2); //we save our new images to the path above
//         $i++;
//     }
// } else {
//     $p_signature = "";
// }
// if (filetype($p_signature) === 'file' && file_exists($p_signature)) {
//     try {
//         $filename = $id . "_parent_Signature" . $file_extensions[0];
//         $image = $filename;
//         $pdf->Image($image, 170, 204.5, 30.2, 7.3);
//         $parent_signature = $image;
//     } catch (Exception $e) {
//         try {
//             $filename = $id . "_parent_Signature" . $file_extensions[1];
//             $image = $filename;
//             $pdf->Image($image, 170, 204.5, 30.2, 7.3);
//             $parent_signature = $image;
//         } catch (Exception $e) {
//             try {
//                 $filename = $id . "_Signature" . $file_extensions[2];
//                 $image = $filename;
//                 $pdf->Image($image, 170, 204.5, 30.2, 7.3);
//                 $parent_signature = $image;
//             } catch (Exception $e) {
//                 echo 'Message: ' . $e->getMessage();
//             }
//         }
//     }
// }

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

$pdf->Output('I', 'bvoc_student.pdf');
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
}
