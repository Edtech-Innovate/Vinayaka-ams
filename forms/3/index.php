<?php
error_reporting(1);
ini_set('display_errors', 0); // Turn OFF display errors to prevent output before PDF
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

if (isset($_GET['student_id'])) {
  require '../../includes/db-config.php';
  session_start();

  if ($_SESSION['university_id'] == 3 || $_SESSION['university_id'] == 48) {
    $id = mysqli_real_escape_string($conn, $_GET['student_id']);
    $id = base64_decode($id);
    $id = intval(str_replace('W1Ebt1IhGN3ZOLplom9I', '', $id));
    $student = $conn->query("SELECT Students.*, Courses.Name as Course, Sub_Courses.Name as Sub_Course, Admission_Sessions.Name as `Session`, Admission_Types.Name as Type FROM Students LEFT JOIN Courses ON Students.Course_ID = Courses.ID LEFT JOIN Sub_Courses ON Students.Sub_Course_ID = Sub_Courses.ID LEFT JOIN Admission_Sessions ON Students.Admission_Session_ID = Admission_Sessions.ID LEFT JOIN Admission_Types ON Students.Admission_Type_ID = Admission_Types.ID WHERE Students.ID = $id");
    $student = mysqli_fetch_assoc($student);
    $address = json_decode($student['Address'], true);

    require_once('../../extras/vendor/setasign/fpdf/fpdf.php');
    require_once('../../extras/vendor/setasign/fpdi/src/autoload.php');

    // FIX: Use __DIR__ for absolute paths to PDF templates
    $certPdfPath = __DIR__ . '/Glocal-certificate.pdf';
    $appPdfPath  = __DIR__ . '/ApplicationForm.pdf';

    $checke_image = '../../assets/img/forms/checked.png';

    // Extensions  added uppercase for Linux servers
    $file_extensions = array('.png', '.jpg', '.jpeg', '.PNG', '.JPG', '.JPEG');

    if ($student['Course'] == 'CERTIFICATION') {

      // FIX: Check if PDF template exists before proceeding
      if (!file_exists($certPdfPath)) {
        error_log('Missing PDF template: ' . $certPdfPath);
        die('Certificate PDF template not found. Please upload Glocal-certificate.pdf to: ' . __DIR__);
      }

      $pdf = new Fpdi();
      $pdf->SetTitle('Application Form');
      $pageCount = $pdf->setSourceFile($certPdfPath);
      $pdf->SetFont('Arial', 'B', 11);
      $checke_image = '../../assets/img/form/checked.png';

      // Photo
      $student_photo = "";
      $photo = "";
      $photo = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = $id AND Type = 'Photo'");
      if ($photo->num_rows > 0) {
        $photo = mysqli_fetch_assoc($photo);
        $photo = "../.." . $photo['Location'];
        $student_photo = base64_encode(file_get_contents($photo));
        $i = 0;
        $end = 3;
        while ($i < $end) {
          $data1 = base64_decode($student_photo);
          $filename1 = $id . "_Photo" . $file_extensions[$i];
          file_put_contents($filename1, $data1);
          $i++;
        }
      } else {
        $photo = "";
      }

      // Page 1
      $pageId = $pdf->importPage(1, PdfReader\PageBoundaries::MEDIA_BOX);
      $pdf->addPage();
      $pdf->useImportedPage($pageId, 0, 0, 210);

      $pdf->SetFont('Arial', '', 11);

      // Student Name
      $pdf->SetXY(116, 77.5);
      $pdf->Write(1, $student['First_Name'] . '' . $student['Middle_Name'] . $student['Lastt_Name']);

      // Enrollment No.
      $enrollment_no = str_split($student['Enrollment_No']);
      $x = 34.8;
      foreach ($enrollment_no as $enroll) {
        $pdf->SetXY($x, 60.8);
        $pdf->Write(1, $enroll);
        $x += 3.97;
      }

      // Photo — FIX: check all extensions including uppercase
      if (!empty($photo) && file_exists($photo)) {
        $imagePlaced = false;
        for ($ei = 0; $ei < count($file_extensions); $ei++) {
          $filename = $id . "_Photo" . $file_extensions[$ei];
          if (file_exists($filename) && is_readable($filename)) {
            try {
              $pdf->Image($filename, 163, 23, 30.5, 35.9);
              $photo = $filename;
              $imagePlaced = true;
              break;
            } catch (Exception $e) {
              error_log("Photo placement failed: " . $filename . " - " . $e->getMessage());
              continue;
            }
          }
        }
        if (!$imagePlaced) {
          error_log("Could not place photo for student ID: $id");
        }
      }

      // Father Name
      $student_name = str_split($student['Father_Name']);
      $x = 76;
      foreach ($student_name as $name) {
        $pdf->SetXY($x, 91);
        $pdf->Write(1, $name);
        $x += 3.9;
      }

      // DOB
      $dob = str_split($student['DOB']);
      $pdf->SetXY(40, 97.5);
      $pdf->Write(1, $dob[8]);
      $pdf->SetXY(42, 97.5);
      $pdf->Write(1, $dob[9] . '/');
      $pdf->SetXY(45.5, 97.5);
      $pdf->Write(1, $dob[5]);
      $pdf->SetXY(48, 97.5);
      $pdf->Write(1, $dob[6] . '/');
      $pdf->SetXY(51.5, 97.5);
      $pdf->Write(1, $dob[0]);
      $pdf->SetXY(53.5, 97.5);
      $pdf->Write(1, $dob[1]);
      $pdf->SetXY(55.5, 97.5);
      $pdf->Write(1, $dob[2]);
      $pdf->SetXY(57.5, 97.5);
      $pdf->Write(1, $dob[3]);

      // Mobile
      $pdf->SetFont('Arial', '', 10);
      $pdf->SetXY(111, 97.5);
      $pdf->Write(1, $student['Contact']);

      // Gender
      $gender = $student['Gender'] == "Male" ? "Male" : "Female";
      $pdf->SetXY(29, 104);
      $pdf->Write(1, $gender);

      // Email
      $pdf->SetXY(117.5, 104);
      $pdf->Write(1, $student['Email']);

      // Country
      $pdf->SetXY(154, 118);
      $pdf->Write(1, $student['Nationality']);

      // Permanent Address
      $pdf->SetXY(31, 118);
      $pdf->Write(1, $address['present_address']);

      // City
      $pdf->SetXY(45, 118);
      $pdf->Write(1, substr($address['present_city'], 0, 15) . ', ' . $address['present_district']);

      $pdf->SetXY(30, 124);
      $pdf->Write(1, $address['present_district']);

      $pdf->SetXY(115.5, 124);
      $pdf->Write(1, substr($address['present_state'], 0, 18));

      // Pincode
      $permanent_pincode = str_split($address['present_pincode']);
      $x = 165;
      for ($i = 0; $i < count($permanent_pincode); $i++) {
        $pdf->SetXY($x, 124);
        $pdf->Write(1, $permanent_pincode[$i]);
        $x += 3.92;
      }

      // Aadhar
      $permanent_pincode = str_split($student['Aadhar_Number']);
      $x = 45;
      for ($i = 0; $i < count($permanent_pincode); $i++) {
        if ($permanent_pincode[$i] != '-') {
          $pdf->SetXY($x, 136);
          $pdf->Write(1, $permanent_pincode[$i]);
          $x += 5;
        }
      }

      // Cleanup temp files
      $i = 0;
      $end = 3;
      while ($i < $end) {
        if (!empty($student_photo)) {
          $filename = $id . "_Photo" . $file_extensions[$i];
          if (file_exists($filename)) unlink($filename);
        }
        if (!empty($student_signature)) {
          $filename = $id . "_Student_Signature" . $file_extensions[$i];
          if (file_exists($filename)) unlink($filename);
        }
        $i++;
      }

      $pdf->Output('I', 'Application Form.pdf');

    } else {

      // FIX: Check if PDF template exists before proceeding
      if (!file_exists($appPdfPath)) {
        error_log('Missing PDF template: ' . $appPdfPath);
        die('Application PDF template not found. Please upload ApplicationForm.pdf to: ' . __DIR__);
      }

      $pdf = new Fpdi();
      $pdf->SetTitle('Application Form');
      $pageCount = $pdf->setSourceFile($appPdfPath);
      $pdf->SetFont('Arial', 'B', 11);

      $check = '../../assets/img/form/checked.png';

      // Photo
      $student_photo = "";
      $photo = "";
      $photo = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = $id AND Type = 'Photo'");
      if ($photo->num_rows > 0) {
        $photo = mysqli_fetch_assoc($photo);
        $photo = "../.." . $photo['Location'];
        $student_photo = base64_encode(file_get_contents($photo));
        $i = 0;
        $end = 3;
        while ($i < $end) {
          $data1 = base64_decode($student_photo);
          $filename1 = $id . "_Photo" . $file_extensions[$i];
          file_put_contents($filename1, $data1);
          $i++;
        }
      } else {
        $photo = "";
      }

      // Signature
      $student_signature = "";
      $signature = "";
      $sig_result = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = $id AND Type = 'Student Signature'");
      if ($sig_result->num_rows > 0) {
        $sig_row = mysqli_fetch_assoc($sig_result);
        $signature = "../.." . $sig_row['Location'];
        // Use original extension from actual file to avoid case mismatch
        $sig_ext = '.' . pathinfo($signature, PATHINFO_EXTENSION); // e.g. .JPG
        $sig_tempfile = $id . "_Student_Signature" . $sig_ext;
        $student_signature = file_get_contents($signature);
        file_put_contents($sig_tempfile, $student_signature);
      } else {
        $signature = "";
        $sig_tempfile = "";
      }

      // Page 1
      $pageId = $pdf->importPage(1, PdfReader\PageBoundaries::MEDIA_BOX);
      $pdf->addPage();
      $pdf->useImportedPage($pageId, 0, 0, 210);

      $pdf->SetFont('Arial', '', 11);
      $pdf->SetXY(43, 11.5);
      $pdf->Write(1, $student['Unique_ID']);
      // Session
      $pdf->SetXY(160, 11.5);
      $pdf->Write(1, $student['Session']);

      // Enrollment No.
      $enrollment_no = str_split($student['Enrollment_No']);
      $x_positions = [77, 85, 94, 102, 110, 118, 126, 134, 142, 151, 159, 167, 175, 183, 191];
      foreach ($enrollment_no as $index => $enroll) {
        $pdf->SetXY($x_positions[$index], 96.5);
        $pdf->Write(1, $enroll);
      }

      // Programme
      $pdf->SetXY(78, 109);
      $pdf->Write(1, $student['Course'] . ' (' . $student['Sub_Course'] . ')');

      // Photo — FIX: check all extensions including uppercase
      if (!empty($photo) && file_exists($photo)) {
        $imagePlaced = false;
        for ($ei = 0; $ei < count($file_extensions); $ei++) {
          $filename = $id . "_Photo" . $file_extensions[$ei];
          if (file_exists($filename) && is_readable($filename)) {
            try {
              $pdf->Image($filename, 167, 33.9, 31.5, 38.9);
              $photo = $filename;
              $imagePlaced = true;
              break;
            } catch (Exception $e) {
              error_log("Photo placement failed: " . $filename . " - " . $e->getMessage());
              continue;
            }
          }
        }
        if (!$imagePlaced) {
          error_log("Could not place photo for student ID: $id");
        }
      }

      // Signature page 1
      if (!empty($sig_tempfile) && file_exists($sig_tempfile)) {
        try {
          $pdf->Image($sig_tempfile, 136, 78, 60, 10.3);
        } catch (Exception $e) {
          error_log("Signature p1 failed: " . $sig_tempfile . " - " . $e->getMessage());
        }
      }

      // Student Name
      $student_name = $student['First_Name'] . " " . $student['Middle_Name'] . "" . $student['Last_Name'];
      $pdf->SetXY(20, 130);
      $pdf->Write(1, strtoupper($student_name));
      $pdf->SetXY(20, 147);
      $pdf->Write(1, strtoupper($student['Father_Name']));
      $pdf->SetXY(20, 165);
      $pdf->Write(1, strtoupper($student['Mother_Name']));

      // DOB
      $dob_parts = explode('-', $student['DOB']);
      $year  = $dob_parts[0];
      $month = $dob_parts[1];
      $date  = $dob_parts[2];

      $pdf->SetXY(140, 182);
      $pdf->Write(1, $date);
      $pdf->SetXY(160, 182);
      $pdf->Write(1, $month);
      $pdf->SetXY(178, 182);
      $pdf->Write(1, $year);

      // Gender
      if ($student['Gender'] == "Male") {
        $pdf->Image($checke_image, 50, 183, 3, 3);
      } else if ($student['Gender'] == "Female") {
        $pdf->Image($checke_image, 63, 183, 3, 3);
      }

      // Full Address
      $address_full = $address['present_address'] . ', ' . $address['present_city'] . ', ' . $address['present_district'] . ', ' . $address['present_state'] . ' - ' . $address['present_pincode'];
      $pdf->SetXY(19, 196);
      $pdf->MultiCell(180, 10, $address_full);

      $pdf->SetXY(138, 237);
      $pdf->Write(1, $student['Email']);
      $pdf->SetXY(88, 237);
      $pdf->Write(1, $student['Contact']);
      $pdf->SetXY(20, 237);
      $pdf->Write(1, $student['Contact']);

      // Page 2
      $pageId = $pdf->importPage(2, PdfReader\PageBoundaries::MEDIA_BOX);
      $pdf->addPage();
      $pdf->useImportedPage($pageId, 0, 0, 210);
      $pdf->SetFont('Arial', '', 11);

      // Category
      if ($student['Category'] == "SC") {
        $pdf->Image($checke_image, 144, 32, 3, 3);
      } else if ($student['Category'] == "General") {
        $pdf->Image($checke_image, 114, 32, 3, 3);
      } else if ($student['Category'] == "OBC") {
        $pdf->Image($checke_image, 131, 32, 3, 3);
      } else if ($student['Category'] == "ST") {
        $pdf->Image($checke_image, 157, 32, 3, 3);
      } else {
        $pdf->Image($checke_image, 160, 32, 3, 3);
      }

      $pdf->SetXY(20, 18);
      $pdf->Write(1, $student['Nationality']);
      $pdf->SetXY(20, 38);
      $pdf->Write(1, $student['Employement_Status']);

      // Academics
    //   $academis = array('High School', 'Intermediate', 'Under Graduation', 'Post Graduation', 'Other');
    //   $y = '60';
    //   foreach ($academis as $academic) {
    //     $x = '53';
    //     $type = $academic == 'Under Graduation' ? 'UG' : ($academic == 'Post Graduation' ? 'PG' : $academic);
    //     $data = $conn->query("SELECT * FROM Student_Academics WHERE Student_ID = $id AND Type = '$type'");
    //     if ($data->num_rows > 0) {
    //       $data = mysqli_fetch_assoc($data);
    //       $pdf->SetFont('Arial', '', 7);
    //         $pdf->SetXY($x, $y);
    //       $pdf->Write(1, $academis);
    //       $x += 5;
    //       $pdf->SetXY($x, $y);
    //       $pdf->Write(1, !empty($data['Subject']) ? $data['Subject'] : '');
    //       $pdf->SetFont('Arial', '', 10);
    //       $x += 30;
    //       $pdf->SetXY($x, $y);
    //       $pdf->Write(1, !empty($data['Year']) ? $data['Year'] : '');
    //       $x += 35;
    //       $pdf->SetXY($x, $y);
    //       $pdf->Write(1, !empty($data['Board/Institute']) ? substr($data['Board/Institute'], 0, 28) : '');
    //       $x += 50;
    //       $pdf->SetXY($x, $y);
    //       $pdf->Write(1, !empty($data['Total_Marks']) ? $data['Total_Marks'] : '');
    //     }
    //     $y += 8;
    //   }
// Academics
$academics = array('High School', 'Intermediate', 'Under Graduation', 'Post Graduation', 'Other');
$y = '60';
foreach ($academics as $academic) {
    $x = '13';
    $type = $academic == 'Under Graduation' ? 'UG' : ($academic == 'Post Graduation' ? 'PG' : $academic);
    $data = $conn->query("SELECT * FROM Student_Academics WHERE Student_ID = $id AND Type = '$type'");
    if ($data->num_rows > 0) {
        $data = mysqli_fetch_assoc($data);
        $pdf->SetFont('Arial', '', 7);
        
        $pdf->SetXY($x, $y);
        $pdf->Write(1, $academic); // ✅ Fixed: was $academis (array), now $academic (string)
        $x += 45;
        $pdf->SetXY($x, $y);
        $pdf->Write(1, !empty($data['Subject']) ? $data['Subject'] : '');
        $pdf->SetFont('Arial', '', 7);
        $x += 30;
        $pdf->SetXY($x, $y);
        $pdf->Write(1, !empty($data['Year']) ? $data['Year'] : '');
        $x += 35;
        $pdf->SetXY($x, $y);
        $pdf->Write(1, !empty($data['Board/Institute']) ? substr($data['Board/Institute'], 0, 28) : '');
        $x += 50;
        $pdf->SetXY($x, $y);
        $pdf->Write(1, !empty($data['Total_Marks']) ? $data['Total_Marks'] : '');
    }
    $y += 8.5;
}
      // Signature page 2
      if (!empty($sig_tempfile) && file_exists($sig_tempfile)) {
        try {
          $pdf->Image($sig_tempfile, 117, 127, 60, 11.3);
        } catch (Exception $e) {
          error_log("Signature p2 failed: " . $sig_tempfile . " - " . $e->getMessage());
        }
      }

      // Cleanup temp files
      if (!empty($sig_tempfile) && file_exists($sig_tempfile)) unlink($sig_tempfile);
      for ($i = 0; $i < 3; $i++) {
        $f = $id . "_Photo" . $file_extensions[$i];
        if (file_exists($f)) unlink($f);
      }

      $pdf->Output('I', 'Application Form.pdf');
    }
  } else {
    header('Location: /');
  }
}