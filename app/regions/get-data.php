<?php
// Ensure no hidden errors corrupt the string output format
error_reporting(E_ERROR | E_PARSE);

require '../../includes/db-config.php';
require '../../includes/helpers.php';

// Route 1: Fetching Sub-Courses
if (isset($_GET['id']) && !empty($_GET['id']) && isset($_GET['type']) && $_GET['type'] == 'Course') {
    
    $course_id = intval($_GET['id']); // Force treat value strictly as an Integer
    
    $option = "<option value=''>Choose Sub-Course</option>";
    if (function_exists('getSubCourses')) {
        $option .= getSubCourses($conn, $course_id);
    } else {
        $option .= "<option value=''>Helper Function Error</option>";
    }
    ob_clean();
    header('Content-Type: text/html; charset=UTF-8');
    echo $option;
    exit;

// Route 2: Fetching Semester Duration
} else if (!empty($_GET['course_id']) && !empty($_GET['sub_course_id']) && isset($_GET['type']) && $_GET['type'] == 'Semester') {
    
    // Cast variables strictly to clean integers to eliminate query compilation bugs
    $course_id = intval($_GET['course_id']);
    $sub_course_id = intval($_GET['sub_course_id']);
    
    $sem = 0;
    
    // Secure query execution tracking block
    $duration_sql = $conn->query("SELECT Min_Duration FROM Sub_Courses WHERE ID = $sub_course_id AND Course_ID = $course_id AND Status = 1");
    
    if ($duration_sql && $duration_sql->num_rows > 0) {
        $duration = $duration_sql->fetch_assoc();
        $sem = intval($duration['Min_Duration']);
    }
    
    $options = '<option value="">Choose Duration</option>';
    if ($sem > 0) {
        for ($i = 1; $i <= $sem; $i++) {
            $options .= '<option value="' . $i . '">' . $i . '</option>';
        }
    } else {
        $options = "<option value=''>No Record Found</option>";
    }
    
    echo $options;
    exit;

// Route 3: Global Catch-all Error Fallback
} else {
    echo "<option value=''>No Record Found</option>";
    exit;
}