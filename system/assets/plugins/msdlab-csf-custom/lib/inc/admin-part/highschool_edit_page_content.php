<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:40 PM
 */
$highschool_id = $_GET['highschool_id'];
$highschool = null;
$title = 'New High School';
if($_POST) {
    $notifications = array(
        'nononce' => 'High School could not be saved.',
        'success' => 'High School saved!'
    );
    if($_POST['highschool_Publish_input'] == 0){
        $notifications['success'] = 'High School deleted';
    }
    if ($msg = $this->queries->set_data('csf_highschool', array('highschool' => 'HighSchoolId = ' . $_POST['highschool_HighSchoolId_input']), $notifications)) {
        print '<div class="updated notice notice-success is-dismissible">' . $msg . '</div>';
        $highschool_id = null;
        unset($_POST);
    }
}
if(!is_null($highschool_id)){
    $highschool = $this->queries->get_highschool($highschool_id);
    $title = $highschool->SchoolName;
} else {
    $highschool_id = $this->queries->get_next_id('highschool','HighSchoolId');
    $highschool = new stdClass;
    $highschool->HighSchoolId = $highschool_id;
    $title = 'New HighSchool';
}
print '<div class="wrap">';
print '<h1 class="wp-heading-inline">'.$title.' Settings</h1>  <a href="admin.php?page=csf-highschool" class="page-title-action">Return To List</a>          
            <hr class="wp-header-end">';
$form = $this->controls->get_form(array('form_id' => 'csf_highschool','data' => $highschool));
print $form;
print '</div>';