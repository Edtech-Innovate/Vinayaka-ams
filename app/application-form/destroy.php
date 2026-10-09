<?php
if ($_SERVER['REQUEST_METHOD'] == 'DELETE' && isset($_GET['id'])) {
    require '../../includes/db-config.php';
    session_start();
    
    // Enable MySQLi exceptions for strict error handling
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $id = base64_decode($id);
    $id = intval(str_replace('W1Ebt1IhGN3ZOLplom9I', '', $id));

    header('Content-Type: application/json; charset=utf-8');

    if ($id <= 0) {
        echo json_encode(['status' => 400, 'message' => 'Invalid Student ID.']);
        exit;
    }

    try {
        // Start Transaction
        $conn->begin_transaction();

        // 1. Check if ID already exists in trash_students
        $check_trash = $conn->prepare("SELECT ID FROM trash_students WHERE ID = ?");
        $check_trash->bind_param("i", $id);
        $check_trash->execute();
        $trash_result = $check_trash->get_result();

        if ($trash_result->num_rows > 0) {
            $check_trash->close();
            throw new Exception("Student record already exists in trash.");
        }
        $check_trash->close();

        // 2. Move student record from Students to trash_students
        $move_to_trash = $conn->query("
            INSERT INTO trash_students (
                ID, Unique_ID, Added_By, Added_For, Lead_Status_ID, Enrollment_No, OA_Number, Roll_No, 
                University_ID, Admission_Type_ID, Admission_Session_ID, Course_ID, Sub_Course_ID, Mode_ID, 
                Admission_Duration, Duration, First_Name, Middle_Name, Last_Name, Father_Name, Mother_Name, 
                Email, Alternate_Email, Contact, Alternate_Contact, DOB, Category, Aadhar_Number, Gender, 
                Nationality, Employement_Status, Marital_Status, Religion, Address, Status, Step, ID_Card, 
                Admit_Card, Exam, Process_By_Center, Payment_Received, Document_Verified, Processed_To_University, 
                Is_Transferred, Created_At, Updated_At, Deleted_At, form_completion_date, abc_id
            )
            SELECT 
                ID, Unique_ID, Added_By, Added_For, Lead_Status_ID, Enrollment_No, OA_Number, Roll_No, 
                University_ID, Admission_Type_ID, Admission_Session_ID, Course_ID, Sub_Course_ID, Mode_ID, 
                Admission_Duration, Duration, First_Name, Middle_Name, Last_Name, Father_Name, Mother_Name, 
                Email, Alternate_Email, Contact, Alternate_Contact, DOB, Category, Aadhar_Number, Gender, 
                Nationality, Employement_Status, Marital_Status, Religion, Address, Status, Step, ID_Card, 
                Admit_Card, Exam, Process_By_Center, Payment_Received, Document_Verified, Processed_To_University, 
                Is_Transferred, Created_At, Updated_At, NOW(), form_completion_date, abc_id
            FROM Students 
            WHERE ID = $id
        ");

        if ($conn->affected_rows === 0) {
            throw new Exception("Student record not found or could not be archived.");
        }

        // 3. Remove physical document files from the server
        $documents = $conn->query("SELECT Location FROM Student_Documents WHERE Student_ID = $id");
        while ($document = $documents->fetch_assoc()) {
            $files = explode('|', $document['Location']);
            foreach ($files as $file) {
                $filePath = '../../' . ltrim($file, '/');
                if (file_exists($filePath) && is_file($filePath)) {
                    @unlink($filePath);
                }
            }
        }

        // 4. Delete foreign key dependencies & main record
        $conn->query("DELETE FROM Student_Documents WHERE Student_ID = $id");
        $conn->query("DELETE FROM Student_Academics WHERE Student_ID = $id");
        $conn->query("DELETE FROM Students WHERE ID = $id");

        // Commit all changes if execution reaches this point cleanly
        $conn->commit();

        echo json_encode(['status' => 200, 'message' => 'Student moved to trash and deleted successfully!']);

    } catch (Exception $e) {
        // Rollback any database changes if an error occurs
        $conn->rollback();
        echo json_encode(['status' => 400, 'message' => $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 405, 'message' => 'Invalid Request Method or Parameters.']);
}