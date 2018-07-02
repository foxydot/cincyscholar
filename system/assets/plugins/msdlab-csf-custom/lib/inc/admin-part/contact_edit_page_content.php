<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:41 PM
 */
$contact_id = $_GET['contact_id'];
$contact = null;
$title = 'New Contact';
if($_POST) {
    $notifications = array(
        'nononce' => 'Contact could not be saved.',
        'success' => 'Contact saved!'
    );
    if($_POST['collegecontact_Publish_input'] == 0){
        $notifications['success'] = 'Contact deleted';
    }
    if ($msg = $this->queries->set_data('csf_contact', array('collegecontact' => 'CollegeContactId = ' . $contact_id), $notifications)) {
        print '<div class="updated notice notice-success is-dismissible">' . $msg . '</div>';
        $contact_id = null;
        unset($_POST);
    }
}
if(!is_null($contact_id)){
    $contact = $this->queries->get_contact($contact_id);
    $title = $contact->FirstName .' '. $contact->LastName;
} else {
    $title = 'New Contact';
}

print '<div class="wrap">';
print '<h1 class="wp-heading-inline">'.$title.' Settings</h1> <a href="admin.php?page=csf-college" class="page-title-action">Return To List</a>         
            <hr class="wp-header-end">';
$form = $this->controls->get_form(array('form_id' => 'csf_contact','data' => $contact));
print $form;
print '</div>';