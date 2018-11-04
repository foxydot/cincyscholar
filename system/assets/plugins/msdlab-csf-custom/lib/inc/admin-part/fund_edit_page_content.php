<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:40 PM
 */
$fund_id = $_GET['fund_id'];
$fund = null;
$title = 'New Fund';
if($_POST) {
    $notifications = array(
        'nononce' => 'Fund could not be saved.',
        'success' => 'Fund saved!'
    );
    if ($msg = $this->queries->set_data('csf_fund', array('fund' => 'FundId = ' . $_POST['fund_FundId_input']), $notifications)) {
        print '<div class="updated notice notice-success is-dismissible">' . $msg . '</div>';
        $fund_id = null;
        unset($_POST);
    }
}
if(!is_null($fund_id)){
    $fund = $this->queries->get_fund($fund_id);
    $title = $fund->Name;
} else {
    $fund_id = $this->queries->get_next_id('fund','FundId');
    $fund = new stdClass;
    $fund->FundId = $fund_id;
    $title = 'New Fund';
}
print '<div class="wrap">';
print '<h1 class="wp-heading-inline">'.$title.' Settings</h1>  <a href="admin.php?page=csf-fund" class="page-title-action">Return To List</a>          
            <hr class="wp-header-end">';
$form = $this->controls->get_form(array('form_id' => 'csf_fund','data' => $fund));
print $form;
print '</div>';