<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:40 PM
 */
$donortype_id = $_GET['donortype_id'];
$donortype = null;
$title = 'New Donor Type';
if($_POST) {
    $notifications = array(
        'nononce' => 'Donor Type could not be saved.',
        'success' => 'Donor Type saved!'
    );
    if ($msg = $this->queries->set_data('csf_donortype', array('donortype' => 'DonorTypeId = ' . $_POST['donortype_DonorTypeId_input']), $notifications)) {
        print '<div class="updated notice notice-success is-dismissible">' . $msg . '</div>';
        $donortype_id = null;
        unset($_POST);
    }
}
if(!is_null($donortype_id)){
    $donortype = $this->queries->get_donortype($donortype_id);
    $title = $donortype->DonorType;
} else {
    $donortype_id = $this->queries->get_next_id('donortype','DonorTypeId');
    $donortype = new stdClass;
    $donortype->DonorTypeId = $donortype_id;
    $title = 'New Donor Type';
}
print '<div class="wrap">';
print '<h1 class="wp-heading-inline">'.$title.' Settings</h1>  <a href="admin.php?page=csf-donortype" class="page-title-action">Return To List</a>          
            <hr class="wp-header-end">';
$form = $this->controls->get_form(array('form_id' => 'csf_donortype','data' => $donortype));
print $form;
print '</div>';