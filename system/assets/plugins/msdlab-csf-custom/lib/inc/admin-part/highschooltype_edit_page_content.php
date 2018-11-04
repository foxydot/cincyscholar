<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:40 PM
 */
$highschooltype_id = $_GET['highschooltype_id'];
$highschooltype = null;
$title = 'New High School Type';
if($_POST) {
    $notifications = array(
        'nononce' => 'High School Type could not be saved.',
        'success' => 'High School Type saved!'
    );
    if ($msg = $this->queries->set_data('csf_highschooltype', array('highschooltype' => 'HighSchoolTypeId = ' . $_POST['highschooltype_HighSchoolTypeId_input']), $notifications)) {
        print '<div class="updated notice notice-success is-dismissible">' . $msg . '</div>';
        $highschooltype_id = null;
        unset($_POST);
    }
}
if(!is_null($highschooltype_id)){
    $highschooltype = $this->queries->get_highschooltype($highschooltype_id);
    $title = $highschooltype->Type;
} else {
    $highschooltype_id = $this->queries->get_next_id('highschooltype','HighSchoolTypeId');
    $highschooltype = new stdClass;
    $highschooltype->HighSchoolTypeId = $highschooltype_id;
    $title = 'New HighSchool Type';
}
print '<div class="wrap">';
print '<h1 class="wp-heading-inline">'.$title.' Settings</h1>  <a href="admin.php?page=csf-highschooltype" class="page-title-action">Return To List</a>          
            <hr class="wp-header-end">';
$form = $this->controls->get_form(array('form_id' => 'csf_highschooltype','data' => $highschooltype));
print $form;
print '</div>';