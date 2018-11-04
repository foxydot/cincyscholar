<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:40 PM
 */
$institutiontermtype_id = $_GET['institutiontermtype_id'];
$institutiontermtype = null;
$title = 'New Institution Term Type';
if($_POST) {
    $notifications = array(
        'nononce' => 'Institution Term Type could not be saved.',
        'success' => 'Institution Term Type saved!'
    );
    if ($msg = $this->queries->set_data('csf_institutiontermtype', array('institutiontermtype' => 'InstitutionTermTypeId = ' . $_POST['institutiontermtype_InstitutionTermTypeId_input']), $notifications)) {
        print '<div class="updated notice notice-success is-dismissible">' . $msg . '</div>';
        $institutiontermtype_id = null;
        unset($_POST);
    }
}
if(!is_null($institutiontermtype_id)){
    $institutiontermtype = $this->queries->get_institutiontermtype($institutiontermtype_id);
    $title = $institutiontermtype->InstitutionTermType;
} else {
    $institutiontermtype_id = $this->queries->get_next_id('institutiontermtype','InstitutionTermTypeId');
    $institutiontermtype = new stdClass;
    $institutiontermtype->InstitutionTermTypeId = $institutiontermtype_id;
    $title = 'New Institution Term Type';
}
print '<div class="wrap">';
print '<h1 class="wp-heading-inline">'.$title.' Settings</h1>  <a href="admin.php?page=csf-institutiontermtype" class="page-title-action">Return To List</a>          
            <hr class="wp-header-end">';
$form = $this->controls->get_form(array('form_id' => 'csf_institutiontermtype','data' => $institutiontermtype));
print $form;
print '</div>';