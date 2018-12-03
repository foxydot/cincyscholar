<?php

if($_POST){
    //get a report
    //custom report queries lazy (to refactor?)
    global $wpdb;
    $sql['select']  = 'SELECT a.UserId, a.ApplicantId, a.FirstName, a.LastName, a.StudentId, a.Last4SSN, b.*, c.* , d.Name, d.InstitutionTermTypeId, e.*';
    $sql['from']    = 'FROM applicant a, applicantscholarship b, payment c, college d, scholarship e';
    $sql['where'][] = 'WHERE a.ApplicantId = b.ApplicantId ';
    $sql['where'][] = 'AND b.DateAwarded > \''.get_option('csf_settings_start_date').'\' ';
    $sql['where'][] = 'AND b.ThankYou = 1 ';
    $sql['where'][] = 'AND b.Signed = 1 ';
    switch($_POST['payment_number_input']){
        case '3':
            $sql['where'][] = 'AND b.GPA2 != \'0.000\' ';
        case '2-Adj':
        case '2':
            $sql['where'][] = 'AND b.GPA1 != \'0.000\' ';
            break;
        case '1-Adj':
        case '1':
        default:
            break;
    }
    $sql['where'][] = 'AND a.ApplicantId = c.ApplicantId ';
    $sql['where'][] = 'AND b.ScholarshipId = e.ScholarshipId ';
    $sql['where'][] = 'AND c.paymentkey = \''.$_POST['payment_number_input'].'\' ';
    $sql['where'][] = 'AND c.PaymentAmt = \'0.00\' ';
    $sql['where'][] = 'AND c.CollegeId = d.CollegeId ';
    $where = implode(' ',$sql['where']);
    $sql['where'] = $where;
    $sql['orderby'] = 'ORDER BY d.Name';

    $result = $wpdb->get_results(implode(' ',$sql));
    //ts_data($result);
    $check_data = array();
    foreach($result as $k => $user){
        $student_id = $user->StudentId != ''?$user->StudentId:'SSN '.$user->Last4SSN;
        $college = $user->CollegeId != 343?$this->queries->get_college_by_id($user->CollegeId):$this->queries->get_other_school($user->ApplicantId);
        $fund = $user->Name;
        $collegefund = $college;
        $check_data[$collegefund]['College'] = $college;
        $check_data[$collegefund]['Students'][] = $user->FirstName.' '.$user->LastName.' ('. $student_id . ')';
        switch($user->InstitutionTermTypeId){
            case 2:
                $check_data[$collegefund]['CheckAmount'][] = $user->AmountAwarded/3;
                break;
            case 3:
            default:
            $check_data[$collegefund]['CheckAmount'][] = $user->AmountAwarded/2;
                break;
        }
    }
}
//print a small selection form
$this->search->print_form_custom_searches('checks_to_print',true,$fields);
//turn result into a report.
$class = implode(" ",apply_filters('msdlab_csf_report_display_table_class', array('table','table-bordered','sortable')));

$fields = array('CollegeId','FirstName','LastName','StudentId','ScholarshipId','CheckAmount');
$ret = array();
$ret['start_table'] = '<table id="'.$id.'" class="'.$class.'">';
$ret['table_header'] = $this->report->table_header($fields,false);
$ret['table_data'] = $this->report->check_table_data($fields,$result,$check_data);
$ret['table_footer'] = $this->report->table_footer($fields,$info,false);
$ret['end_table'] = '</table>';
$ret['export'] = $this->report->print_export_tools($id);

//print any results
print implode("\n\r", $ret);