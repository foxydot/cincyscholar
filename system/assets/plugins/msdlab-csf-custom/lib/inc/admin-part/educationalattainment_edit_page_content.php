<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:40 PM
 */
$educationalattainment_id = $_GET['educationalattainment_id'];
$educationalattainment = null;
$title = 'New Educational Attainment';
if($_POST) {
    $notifications = array(
        'nononce' => 'Educational Attainment could not be saved.',
        'success' => 'Educational Attainment saved!'
    );
    if ($msg = $this->queries->set_data('csf_educationalattainment', array('educationalattainment' => 'EducationalAttainmentId = ' . $_POST['educationalattainment_EducationalAttainmentId_input']), $notifications)) {
        print '<div class="updated notice notice-success is-dismissible">' . $msg . '</div>';
        $educationalattainment_id = null;
        unset($_POST);
    }
}
if(!is_null($educationalattainment_id)){
    $educationalattainment = $this->queries->get_educationalattainment($educationalattainment_id);
    $title = $educationalattainment->EducationalAttainment;
} else {
    $educationalattainment_id = $this->queries->get_next_id('educationalattainment','EducationalAttainmentId');
    $educationalattainment = new stdClass;
    $educationalattainment->EducationalAttainmentId = $educationalattainment_id;
    $title = 'New Educational Attainment';
}
print '<div class="wrap">';
print '<h1 class="wp-heading-inline">'.$title.' Settings</h1>  <a href="admin.php?page=csf-educationalattainment" class="page-title-action">Return To List</a>          
            <hr class="wp-header-end">';
$form = $this->controls->get_form(array('form_id' => 'csf_educationalattainment','data' => $educationalattainment));
print $form;
print '</div>';