<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:40 PM
 */
$ethnicity_id = $_GET['ethnicity_id'];
$ethnicity = null;
$title = 'New Ethnicity';
if($_POST) {
    $notifications = array(
        'nononce' => 'Ethnicity could not be saved.',
        'success' => 'Ethnicity saved!'
    );
    if ($msg = $this->queries->set_data('csf_ethnicity', array('ethnicity' => 'EthnicityId = ' . $_POST['ethnicity_EthnicityId_input']), $notifications)) {
        print '<div class="updated notice notice-success is-dismissible">' . $msg . '</div>';
        $ethnicity_id = null;
        unset($_POST);
    }
}
if(!is_null($ethnicity_id)){
    $ethnicity = $this->queries->get_ethnicity($ethnicity_id);
    $title = $ethnicity->EthnicityName;
} else {
    $ethnicity_id = $this->queries->get_next_id('ethnicity','EthnicityId');
    $ethnicity = new stdClass;
    $ethnicity->EthnicityId = $ethnicity_id;
    $title = 'New Ethnicity';
}
print '<div class="wrap">';
print '<h1 class="wp-heading-inline">'.$title.' Settings</h1>  <a href="admin.php?page=csf-ethnicity" class="page-title-action">Return To List</a>          
            <hr class="wp-header-end">';
$form = $this->controls->get_form(array('form_id' => 'csf_ethnicity','data' => $ethnicity));
print $form;
print '</div>';