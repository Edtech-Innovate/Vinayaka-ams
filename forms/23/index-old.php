<?php
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

if (isset($_GET['student_id'])) {
  require '../../includes/db-config.php';
  session_start();

  if ($_SESSION['university_id'] != 2) {
    header('Location: /');
    exit;
  }

  $id = mysqli_real_escape_string($conn, $_GET['student_id']);
  $id = base64_decode($id);
  $id = intval(str_replace('W1Ebt1IhGN3ZOLplom9I', '', $id));

  $student = $conn->query("SELECT Students.*, Courses.Name as Course, Courses.Short_Name as Course_Short_Name, Sub_Courses.Name as Sub_Course, Admission_Sessions.Name as Session, Admission_Types.Name as Type, Universities.Vertical, TIMESTAMPDIFF(YEAR, DOB, CURDATE()) AS Age FROM Students LEFT JOIN Universities ON Students.University_ID = Universities.ID LEFT JOIN Courses ON Students.Course_ID = Courses.ID LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID LEFT JOIN Admission_Types ON Students.Admission_Type_ID = Admission_Types.ID WHERE Students.ID = $id");
  $student = mysqli_fetch_assoc($student);
  $address = json_decode($student['Address'], true);

  require_once('../../extras/vendor/setasign/fpdf/fpdf.php');
  require_once('../../extras/vendor/setasign/fpdi/src/autoload.php');

  // ─── Helper: save image from DB and return usable local path ───────────────
  function saveStudentImage($conn, $id, $type, $label, $path) {
    $result = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = $id AND Type = '$type'");
    if ($result->num_rows === 0) return '';

    $row    = mysqli_fetch_assoc($result);
    $source = '../..' . $row['Location'];

    if (!file_exists($source)) return '';

    // Detect real extension from the actual file
    $mime = mime_content_type($source);
    $extMap = [
      'image/jpeg' => '.jpg',
      'image/png'  => '.png',
      'image/gif'  => '.gif',
      'image/webp' => '.webp',
    ];
    $ext = $extMap[$mime] ?? '.jpg';

    $dest = $path . $id . '_' . $label . $ext;
    copy($source, $dest);

    return $dest;
  }

  // ─── Helper: place image on PDF with fallback ───────────────────────────────
  function placeImage($pdf, $filePath, $x, $y, $w, $h) {
    if (empty($filePath) || !file_exists($filePath)) return;
    try {
      $pdf->Image($filePath, $x, $y, $w, $h);
    } catch (Exception $e) {
      // Silently skip — image unreadable by FPDF
    }
  }

  $path = "photos/";
  if (!is_dir($path)) mkdir($path, 0755, true);

  $photo     = saveStudentImage($conn, $id, 'Photo',             'Photo',     $path);
  $signature = saveStudentImage($conn, $id, 'Student Signature', 'Signature', $path);
  $parentSignature  = saveStudentImage($conn, $id, 'Parent Signature', 'parentSignature', $path);
//   echo('<pre>');print_r($parentSugnature);die;
  // ─── PDF setup ─────────────────────────────────────────────────────────────
  $pdf = new Fpdi();
  $pdf->SetTitle('Application Form');
  $pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false, 0);

// A4 Page
$pdf->AddPage('P', 'A4');

  $pdf->setSourceFile('mang-form-student.pdf');
  $pdf->AddFont('Hondo', '', 'hondo.php');

  $check = '../../assets/img/forms/checked.png';

  $student_name        = trim($student['First_Name'] . ' ' . $student['Middle_Name'] . ' ' . $student['Last_Name']);
  $form_completion_date = date("d-m-Y", strtotime($student['form_completion_date'] ?: $student['Created_At']));

  // ═══════════════════════════════════════════════════════════════════════════
  // PAGE 1
  // ═══════════════════════════════════════════════════════════════════════════
  $pageId = $pdf->importPage(1, PdfReader\PageBoundaries::MEDIA_BOX);
//   $pdf->addPage();
  $pdf->useImportedPage($pageId, 0, 0, 210);
  $pdf->SetFont('Hondo', '', 10);

  // Photo
  placeImage($pdf, $photo, 170, 44.2, 33, 38.7);

  // Gender
//   if ($student['Gender'] == 'Male') {
//     $pdf->Image($check, 36, 158, 3, 3);
//   } elseif ($student['Gender'] == 'Female') {
//     $pdf->Image($check, 55, 158, 3, 3);
//   } else {
//     $pdf->Image($check, 75, 158, 3, 3);
//   }

  // DOB
//   $dob        = date("dmY", strtotime($student['DOB']));
//   $dobChars   = str_split($dob);
//   $xPositions = [128.2, 133.2, 142, 148, 156.5, 161.5, 167.5, 173.5];
//   $y = 159.3;
//   foreach ($dobChars as $index => $char) {
//     $pdf->SetXY($xPositions[$index], $y);
//     $pdf->Write(1, $char);
//   }
//Date
  $pdf->SetXY(15.5, 265.5);
  $pdf->Write(1, date('d-m-Y', strtotime($student['form_completion_date'])));
//   // Student Name
// echo('<pre>');print_r($student);die;
  $pdf->SetXY(15.5, 273.5);
  $pdf->Write(1, strtoupper($student_name));
   $pdf->SetXY(34.5, 282.5);
  $pdf->Write(1, strtoupper($student['Course_Short_Name'].' ('.$student['Sub_Course'].')'));
//   // Father Name
//   $x = 47.3; $y = 180.5;
//   foreach (mb_str_split($student['Father_Name']) as $char) {
//     $pdf->SetXY($x, $y); $pdf->Write(1, $char); $x += 5.4;
//   }

//   // Mother Name
//   $x = 47.3; $y = 188;
//   foreach (mb_str_split($student['Mother_Name']) as $char) {
//     $pdf->SetXY($x, $y); $pdf->Write(1, $char); $x += 5.4;
//   }

//   // Aadhar
//   $x = 47.3; $y = 195;
//   $aadhar = strtoupper(str_replace('-', '', $student['Aadhar_Number']));
//   foreach (mb_str_split($aadhar) as $char) {
//     $pdf->SetXY($x, $y); $pdf->Write(1, $char); $x += 5.4;
//   }

//   // Marital Status
//   $pdf->SetXY(146, 195);
//   $pdf->Write(1, strtoupper($student['Marital_Status']));

//   // Email
//   $x = 47.3; $y = 202.5;
//   foreach (mb_str_split($student['Email']) as $char) {
//     $pdf->SetXY($x, $y); $pdf->Write(1, $char); $x += 5.4;
//   }

//   // Contact
//   $x = 47.3; $y = 210;
//   foreach (mb_str_split($student['Contact']) as $char) {
//     $pdf->SetXY($x, $y); $pdf->Write(1, $char); $x += 5.4;
//   }

  // Category
//   $categoryPos = ['SC' => 39.8, 'ST' => 55.8, 'OBC' => 72, 'General' => 90];
//   if (isset($categoryPos[$student['Category']])) {
//     $pdf->Image($check, $categoryPos[$student['Category']], 215.6, 3, 3);
//   } else {
//     $pdf->Image($check, 113, 215.6, 3, 3);
//   }

  // Nationality


//   // Religion
//   $religionPos = ['Hindu' => 43.8, 'Muslim' => 67, 'Sikh' => 86, 'Christian' => 108, 'Jain' => 127.8];
//   if (isset($religionPos[$student['Religion']])) {
//     $pdf->Image($check, $religionPos[$student['Religion']], 222, 3, 3);
//   }

  // Date / IDs / Session
//   $pdf->SetXY(18, 247);    $pdf->Write(1, $form_completion_date);
$pdf->SetXY(157.5, 5.7); $pdf->Write(1, strtoupper($student['Unique_ID']));
  $pdf->SetXY(153.5, 266.7); $pdf->Write(1, strtoupper($student['Unique_ID']));
//   $pdf->SetXY(160.5, 8);   $pdf->Write(1, strtoupper($student['Unique_ID']));
//   $pdf->SetXY(55.5, 8);    $pdf->Write(1, "Mode: ODL");
//   $pdf->SetXY(85.5, 30);   $pdf->Write(1, $student['Session']);

//   // Bottom strip
//   $pdf->SetXY(16.5, 254.7);  $pdf->Write(1, strtoupper($student_name));
//   $pdf->SetXY(113.5, 254.7); $pdf->Write(1, strtoupper($student['Father_Name']));
     $pdf->SetXY(113, 273.5);
     $pdf->Write(1, strtoupper($student['Father_Name']));
//   // Course (smaller font for long names)
//   $pdf->SetFont('Hondo', '', 8);
//   $pdf->SetXY(34.5, 261.7);
//   $pdf->MultiCell(68, 5, strtoupper($student['Course_Short_Name'] . '(' . $student['Sub_Course'] . ')'), 0, 'L');
//   $pdf->SetFont('Hondo', '', 10);

  // Signature on page 1
  placeImage($pdf, $signature, 117, 279.7, 32, 8);

  // ═══════════════════════════════════════════════════════════════════════════
  // PAGE 2
  // ═══════════════════════════════════════════════════════════════════════════
  $pageId = $pdf->importPage(2, PdfReader\PageBoundaries::MEDIA_BOX);
 $pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false, 0);
// A4 Page
$pdf->AddPage('P', 'A4');

  $pdf->useImportedPage($pageId, 0, 0, 210);
  $pdf->SetFont('Arial', '', 10);
if ($student['Gender'] == 'Male') {
    $pdf->Image($check, 37.5, 16, 3, 3);
   
  } elseif ($student['Gender'] == 'Female') {
    $pdf->Image($check, 56, 16, 3, 3);
  } else {
      $pdf->Image($check, 76.5, 16, 3, 3);
  }
   $dob        = date("dmY", strtotime($student['DOB']));
  $dobChars   = str_split($dob);
  $xPositions = [129.5, 134.5, 143.5, 149, 157, 162.5, 168, 174];
  $y = 16.5;
  foreach ($dobChars as $index => $char) {
    $pdf->SetXY($xPositions[$index], $y);
    $pdf->Write(1, $char);
  }
  
   $x = 49; $y = 23;
  foreach (mb_str_split($student_name) as $char) {
    $pdf->SetXY($x, $y); $pdf->Write(1, $char); $x += 5.3;
  }
  $x = 49; $y = 36;
  foreach (mb_str_split($student['Father_Name']) as $char) {
    $pdf->SetXY($x, $y); $pdf->Write(1, $char); $x += 5.3;
  }
   $x = 49; $y = 43;
  foreach (mb_str_split($student['Mother_Name']) as $char) {
    $pdf->SetXY($x, $y); $pdf->Write(1, $char); $x += 5.3;
  }
   $x = 49; $y = 50;
  $aadhar = strtoupper(str_replace('-', '', $student['Aadhar_Number']));
  foreach (mb_str_split($aadhar) as $char) {
    $pdf->SetXY($x, $y); $pdf->Write(1, $char); $x += 5.3;
  }
 
   $categoryPos = ['SC' => 42.8, 'ST' => 58, 'OBC' => 73.4, 'General' => 92.7];
  if (isset($categoryPos[$student['Category']])) {
    $pdf->Image($check, $categoryPos[$student['Category']], 55.6, 3, 3);
  } else {
     $pdf->Image($check, 115.5, 55.6, 3, 3);
  }
  $pdf->SetXY(154, 56.6);
  $pdf->Write(1, strtoupper($student['Nationality']));
   $religionPos = ['Hindu' => 46.8, 'Muslim' => 70.5, 'Sikh' => 92, 'Christian' => 114.6, 'Jain' => 134.5];
  if (isset($religionPos[$student['Religion']])) {
    $pdf->Image($check, $religionPos[$student['Religion']], 62.8, 3, 3);
  }
  // Present Address (split across two lines, 50 chars each)
  $presentAddress = strtoupper($address['present_address']);
  $chars = mb_str_split($presentAddress);
  $line1 = implode('', array_slice($chars, 0, 50));
  $line2 = implode('', array_slice($chars, 50));

  $pdf->SetXY(8, 76.8);  $pdf->Write(5, $line1);
  if (!empty($line2)) {
    $pdf->SetXY(8, 83.8); $pdf->Write(5, $line2);
  }

  $pdf->SetXY(14, 92);  $pdf->Write(1, $address['present_city']);
  $pdf->SetXY(62, 92);  $pdf->Write(1, $address['present_state']);
  $pdf->SetXY(22, 98.5); $pdf->Write(1, $address['present_pincode']);
//   $pdf->SetXY(42.5, 59); $pdf->Write(1, $student['Father_Name']);
  $x = 50.3; $y = 115;
 $pdf->SetXY($x, $y);  $pdf->Write(1, $student['Contact']);
 

  // Contact
  $x = 135.3; $y = 115;
 $pdf->SetXY($x, $y);  $pdf->Write(1, $student['Email']);
//   // Academics 139.7
  $academics   = ['High School', 'Intermediate', 'Under Graduation', 'Post Graduation', 'Other'];
  $yPositions  = [
    'High School'       => 139.7,
    'Intermediate'      => 146.7,
    'Under Graduation'  => 156.7,
    'Post Graduation'   => 166.7,
    'Diploma'           => 174.7,
    'Other'             => 182.7,
  ];

  $pdf->SetFont('Arial', '', 6);
  foreach ($academics as $academic) {
    $y    = $yPositions[$academic] ?? null;
    if (!$y) continue;

    $type = ($academic === 'Under Graduation') ? 'UG'
          : (($academic === 'Post Graduation')  ? 'PG' : $academic);

    $result = $conn->query("SELECT `Board/Institute` AS board, Year, Subject, Total_Marks,Marks_Obtained,Max_Marks FROM Student_Academics WHERE Student_ID = $id AND Type = '$type'");

    if ($result && $result->num_rows > 0) {
      $row = $result->fetch_assoc();
// echo('<pre>');print_r($row);
      // Board
      $x = 46.5;
      $pdf->SetXY($x, $y);
      $pdf->Write(1, strlen($row['board']) > 20 ? substr($row['board'], 0, 18) . '...' : $row['board']);
      $x = 125.5;
      $pdf->SetXY($x, $y);
      $pdf->Write(1, substr($row['Max_Marks'], 0, 4));
      $x = 140.5;
      $pdf->SetXY($x, $y);
      $pdf->Write(1, substr($row['Marks_Obtained'], 0, 4));
      // Marks
      $x = 153.6;
      $pdf->SetXY($x, $y);
      $pdf->Write(1, substr($row['Total_Marks'], 0, 4));

    //   // Year
      $x = 167;
      $pdf->SetXY($x, $y);
      $pdf->Write(1, $row['Year']);

    //   // Subject
      $x = 183.5;
      $pdf->SetFont('Arial', '', 5);
      $pdf->SetXY($x, $y-2);
    //   $pdf->Write(1, strlen($row['Subject']) > 44 ? substr($row['Subject'], 0, 43) . '...' : $row['Subject']);
    //   $pdf->SetXY($x, $y);

$pdf->MultiCell(
    25, // width
    2,  // line height
    $row['Subject'],
    0,  // border
    'L' // alignment
);
      $pdf->SetFont('Arial', '', 6);
    }
  }

//   // Signature on page 2
  placeImage($pdf, $signature, 55, 215.7, 32, 6);

  $pdf->SetFont('Arial', '', 10);
  $pdf->SetXY(139, 217.7); $pdf->Write(1, $student['Father_Name']);
   placeImage($pdf, $parentSignature, 55, 223, 32, 6);
//   echo('<pre>');print_r($signature);die;
//   $pdf->SetXY(165, 205.5); $pdf->Write(1, $form_completion_date);

  // ─── Output ────────────────────────────────────────────────────────────────
  $pdf->Output('I', 'Application MU.pdf');
}