<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:40 PM
 */
$county_id = $_GET['county_id'];
$county = null;
$title = 'New County';
if($_POST) {
    $notifications = array(
        'nononce' => 'County could not be saved.',
        'success' => 'County saved!'
    );
    if ($msg = $this->queries->set_data('csf_county', array('county' => 'CountyId = ' . $_POST['county_CountyId_input']), $notifications)) {
        print '<div class="updated notice notice-success is-dismissible">' . $msg . '</div>';
        $county_id = null;
        unset($_POST);
    }
}
if(!is_null($county_id)){
    $county = $this->queries->get_county($county_id);
    $title = $county->CountyName;
} else {
    $county_id = $this->queries->get_next_id('county','CountyId');
    $county = new stdClass;
    $county->CountyId = $county_id;
    $title = 'New County';
}
print '<div class="wrap">';
print '<h1 class="wp-heading-inline">'.$title.' Settings</h1>  <a href="admin.php?page=csf-county" class="page-title-action">Return To List</a>          
            <hr class="wp-header-end">';
$form = $this->controls->get_form(array('form_id' => 'csf_county','data' => $county));
print $form;
print '</div>';