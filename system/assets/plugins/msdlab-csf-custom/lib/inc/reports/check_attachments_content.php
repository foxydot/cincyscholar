<?php

if($_POST){
    //get a report
    //custom report queries lazy (to refactor?)
    global $wpdb;
    $sql['select']  = 'SELECT a.UserId, a.ApplicantId, a.FirstName, a.LastName, a.StudentId, a.Last4SSN, b.*, c.* , d.Name, d.InstitutionTermTypeId';
    $sql['from']    = 'FROM applicant a, applicantscholarship b, payment c, college d';
    $sql['where'][] = 'WHERE a.ApplicantId = b.ApplicantId ';
    $sql['where'][] = 'AND a.ApplicantId = c.ApplicantId ';
    $sql['where'][] = 'AND c.CollegeId = d.CollegeId ';
    $sql['where'][] = 'AND c.CollegeId = d.CollegeId ';
    $where = implode(' ',$sql['where']);
    $sql['where'] = $where;
    $sql['orderby'] = 'ORDER BY a.LastName';

    $result = $wpdb->get_results(implode(' ',$sql));

    foreach($result as $k => $user){
        switch($user->InstitutionTermTypeId){
            case 2:
                $check_amount = $user->AmountAwarded/3;
                break;
            case 3:
            default:
                $check_amount = $user->AmountAwarded/2;
                break;
        }
        $sql2['update']  = 'UPDATE payment';
        $sql2['set']    = 'SET PaymentDateTime = \''.date('Y-m-d H:i:s',strtotime($_POST['payment_PaymentDateTime_input'])).'\', 
        CheckNumber = \''.$_POST['payment_CheckNumber_input'].'\',
        PaymentAmt = '.$check_amount.'';
        $sql2['where'] = 'WHERE paymentid = '.$user->paymentid.';';
        $wpdb->query(implode(' ',$sql2));
    }
}
//print a small selection form
print $this->controls->get_form(array('form_id' => 'check_attachments',array('data' => $_POST)));
//turn result into a report.
$id = 'check_attachments';
$class = implode(" ",apply_filters('msdlab_csf_report_display_table_class', array('table','table-bordered','sortable')));

$fields = array('LastName','FirstName','StudentId','ScholarshipId','CheckAmount');
$ret = array();
$ret['start_table'] = '<table id="'.$id.'" class="'.$class.'">';
$ret['table_header'] = $this->report->table_header($fields,false);
$ret['table_data'] = $this->report->check_table_data($fields,$result,$check_data);
$ret['table_footer'] = $this->report->table_footer($fields,$info,false);
$ret['end_table'] = '</table>';
$ret['export'] = $this->report->print_export_tools($id);

//print any results
print implode("\n\r", $ret);