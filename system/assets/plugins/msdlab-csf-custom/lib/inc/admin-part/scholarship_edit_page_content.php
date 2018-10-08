<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:40 PM
 */
$scholarship_id = $_GET['scholarship_id'];
$scholarship = null;
$title = 'New Scholarship';
if($_POST) {
    $notifications = array(
        'nononce' => 'Scholarship could not be saved.',
        'success' => 'Scholarship saved!'
    );
    if($_POST['scholarship_Publish_input'] == 0){
        $notifications['success'] = 'Scholarship deleted';
    }
    if ($msg = $this->queries->set_data('csf_scholarship', array('scholarship' => 'ScholarshipId = ' . $_POST['scholarship_ScholarshipId_input']), $notifications)) {
        print '<div class="updated notice notice-success is-dismissible">' . $msg . '</div>';
        //$scholarship_id = null;
        unset($_POST);
    }
}
if(!is_null($scholarship_id)){
    $scholarship = $this->queries->get_scholarship($scholarship_id);
    $title = $scholarship->Name;
} else {
    $scholarship_id = $this->queries->get_next_id('scholarship','ScholarshipId');
    $scholarship = new stdClass;
    $scholarship->ScholarshipId = $scholarship_id;
    $title = 'New Scholarship';
}
print '<div class="wrap">';
print '<h1 class="wp-heading-inline">'.$title.' Settings</h1>  <a href="admin.php?page=csf-scholarship" class="page-title-action">Return To List</a>          
            <hr class="wp-header-end">';
$form = $this->controls->get_form(array('form_id' => 'csf_scholarship','data' => $scholarship));
print $form;
print '</div>';