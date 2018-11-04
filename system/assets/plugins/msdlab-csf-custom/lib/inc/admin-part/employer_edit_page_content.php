<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:40 PM
 */
$employer_id = $_GET['employer_id'];
$employer = null;
$title = 'New Employer';
if($_POST) {
    $notifications = array(
        'nononce' => 'Employer could not be saved.',
        'success' => 'Employer saved!'
    );
    if ($msg = $this->queries->set_data('csf_employer', array('employer' => 'employerid = ' . $_POST['employer_employerid_input']), $notifications)) {
        print '<div class="updated notice notice-success is-dismissible">' . $msg . '</div>';
        $employer_id = null;
        unset($_POST);
    }
}
if(!is_null($employer_id)){
    $employer = $this->queries->get_employer($employer_id);
    $title = $employer->employername;
} else {
    $employer_id = $this->queries->get_next_id('employer','employerid');
    $employer = new stdClass;
    $employer->employerid = $employer_id;
    $title = 'New Employer';
}
print '<div class="wrap">';
print '<h1 class="wp-heading-inline">'.$title.' Settings</h1>  <a href="admin.php?page=csf-employer" class="page-title-action">Return To List</a>          
            <hr class="wp-header-end">';
$form = $this->controls->get_form(array('form_id' => 'csf_employer','data' => $employer));
print $form;
print '</div>';