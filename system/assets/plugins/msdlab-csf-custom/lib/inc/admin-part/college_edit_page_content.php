<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:40 PM
 */
$college_id = $_GET['college_id'];
$college = null;
$title = 'New College';
if($_POST) {
    $notifications = array(
        'nononce' => 'College could not be saved.',
        'success' => 'College saved!'
    );
    if($_POST['college_Publish_input'] == 0){
        $notifications['success'] = 'College deleted';
    }
    if ($msg = $this->queries->set_data('csf_college', array('college' => 'CollegeId = ' . $college_id), $notifications)) {
        print '<div class="updated notice notice-success is-dismissible">' . $msg . '</div>';
        $college_id = null;
        unset($_POST);
    }
}
if(!is_null($college_id)){
    $college = $this->queries->get_college($college_id);
    $title = $college->Name;
} else {
    $title = 'New College';
}
print '<div class="wrap">';
print '<h1 class="wp-heading-inline">'.$title.' Settings</h1>  <a href="admin.php?page=csf-college" class="page-title-action">Return To List</a>          
            <hr class="wp-header-end">';
$form = $this->controls->get_form(array('form_id' => 'csf_college','data' => $college));
print $form;
print '</div>';