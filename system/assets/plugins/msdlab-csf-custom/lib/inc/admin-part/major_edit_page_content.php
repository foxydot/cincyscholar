<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:40 PM
 */
$major_id = $_GET['major_id'];
$major = null;
$title = 'New Major';
if($_POST) {
    $notifications = array(
        'nononce' => 'Major could not be saved.',
        'success' => 'Major saved!'
    );
    if($_POST['major_Publish_input'] == 0){
        $notifications['success'] = 'Major deleted';
    }
    if ($msg = $this->queries->set_data('csf_major', array('major' => 'MajorId = ' . $_POST['major_MajorId_input']), $notifications)) {
        print '<div class="updated notice notice-success is-dismissible">' . $msg . '</div>';
        $major_id = null;
        unset($_POST);
    }
}
if(!is_null($major_id)){
    $major = $this->queries->get_major($major_id);
    $title = $major->MajorName;
} else {
    $major_id = $this->queries->get_next_id('major','MajorId');
    $major = new stdClass;
    $major->MajorId = $major_id;
    $title = 'New Major';
}
print '<div class="wrap">';
print '<h1 class="wp-heading-inline">'.$title.' Settings</h1>  <a href="admin.php?page=csf-major" class="page-title-action">Return To List</a>          
            <hr class="wp-header-end">';
$form = $this->controls->get_form(array('form_id' => 'csf_major','data' => $major));
print $form;
print '</div>';