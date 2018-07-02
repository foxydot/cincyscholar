<?php
$tabs = '';
$pane = array();
if($_POST) {
    $application_id = $_POST['applicant_id'];
} elseif($_GET){
    $application_id = $_GET['applicant_id'];
} else {
    print "Error. Please select student.";
    die();
}

if($student = $this->queries->get_student_data($applicant_id)) {
    if(is_wp_error($student)){
        ts_data($student); //display errors
    } else {
        ts_data($student);
    }
} else {
    print "Error. No records for this student. This should never happen.";
    die();
}