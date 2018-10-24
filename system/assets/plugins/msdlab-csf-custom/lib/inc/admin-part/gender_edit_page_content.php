<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:40 PM
 */
$gender_id = $_GET['gender_id'];
$gender = null;
$title = 'New Gender';
if($_POST) {
    $notifications = array(
        'nononce' => 'Gender could not be saved.',
        'success' => 'Gender saved!'
    );
    if ($msg = $this->queries->set_data('csf_gender', array('sex' => 'SexId = ' . $_POST['sex_SexId_input']), $notifications)) {
        print '<div class="updated notice notice-success is-dismissible">' . $msg . '</div>';
        $gender_id = null;
        unset($_POST);
    }
}
if(!is_null($gender_id)){
    $gender = $this->queries->get_gender($gender_id);
    $title = $gender->Sex;
} else {
    $gender_id = $this->queries->get_next_id('sex','SexId');
    $gender = new stdClass;
    $gender->SexId = $gender_id;
    $title = 'New Gender';
}
print '<div class="wrap">';
print '<h1 class="wp-heading-inline">'.$title.' Settings</h1>  <a href="admin.php?page=csf-gender" class="page-title-action">Return To List</a>          
            <hr class="wp-header-end">';
$form = $this->controls->get_form(array('form_id' => 'csf_gender','data' => $gender));
print $form;
print '</div>';