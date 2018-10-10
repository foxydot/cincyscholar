<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:41 PM
 */
$donor_id = $_GET['user_id'];
if($_POST) {
    $notifications = array(
        'nononce' => 'Donor info could not be saved.',
        'success' => 'Donor info saved!'
    );

    if ($msg = $this->queries->set_data('csf_donor', array('donoruserscholarship' => 'UserId = ' . $donor_id), $notifications)) {
        if(!is_wp_error($msg)){
            print '<div class="updated notice notice-success is-dismissible">' . $msg . '</div>';
        } else {
            ts_data($msg);
        }
        unset($_POST);
    }
}
if(!is_null($donor_id)){
    $donor = $this->queries->get_donor($donor_id);
    ts_data($donor);
    $title = $donor['user']->display_name;
}
$scholarship_array = $this->queries->get_select_array_from_db('scholarship', 'ScholarshipId', 'Name','Name',1);

print '<div class="wrap">';
print '<h1 class="wp-heading-inline">'.$title.' Settings</h1> <a href="admin.php?page=csf-donors" class="page-title-action">Return To List</a>         
            <hr class="wp-header-end">';
$form_id = 'csf_donor';
$ret['form_header'] = $this->form->form_header($form_id);
$ret['UserId'] = $this->form->field_hidden('donoruserscholarship_UserId',$donor_id);
$ret['ScholarshipId'] = $this->form->field_checkbox_array('donoruserscholarship_ScholarshipId',$donor['donor'],'Allow Viewing Access To:',$scholarship_array,array(), array('col-sm-12'));

$ftr['button'] = $this->form->field_button('saveBtn', 'SAVE', array('submit', 'btn'), 'submit', false);
$ret['form_footer'] = $this->form->form_footer('form_footer',implode("\n",$ftr),array('form-footer', 'col-md-12'));

$ret['nonce'] = wp_nonce_field( $form_id );
$ret['form_close'] = $this->form->form_close();
print implode("\n",$ret);
print '</div>';