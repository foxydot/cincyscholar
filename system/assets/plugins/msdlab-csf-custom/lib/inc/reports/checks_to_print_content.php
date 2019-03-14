<?php

if($_POST){
    //get a report
    //custom report queries lazy (to refactor?)
    global $wpdb;
    //first get applicant ids for payments
    $subsql['select']  = 'SELECT AwardId';
    $subsql['from']    = 'FROM applicantscholarship b';
    $subsql['where'][] = 'WHERE';
    $subsql['where'][] = 'b.AcademicYear = '.$_POST['academic_year_input'];
    $subsql['where'][] = 'AND b.ThankYou = 1 ';
    $subsql['where'][] = 'AND b.Signed = 1 ';
    switch($_POST['payment_number_input']){
        case '3':
            $subsql['where'][] = 'AND b.GPA2 != \'0.000\' ';
        case '2-Adj':
        case '2':
            $subsql['where'][] = 'AND b.GPA1 != \'0.000\' ';
            break;
        case '1-Adj':
        case '1':
        default:
            break;
    }
    $where = implode(' ',$subsql['where']);
    $subsql['where'] = $where;
    $sub = implode(' ',$subsql);

    $sql['select']  = 'SELECT a.UserId, a.ApplicantId, a.FirstName, a.LastName, a.StudentId, a.Last4SSN, a.CollegeId AS theCollegeId, b.*, c.* , d.Name, d.InstitutionTermTypeId, e.*, e.Name AS ScholarshipName';
    $sql['from']    = 'FROM applicant a, applicantscholarship b, payment c, college d, scholarship e';
    $sql['where'][] = 'WHERE a.ApplicantId = b.ApplicantId ';
    $sql['where'][] = 'AND a.ApplicantId = c.ApplicantId ';
    $sql['where'][] = 'AND b.ScholarshipId = e.ScholarshipId ';
    $sql['where'][] = 'AND b.AwardId = c.AwardId ';
    $sql['where'][] = 'AND c.paymentkey = \''.$_POST['payment_number_input'].'\' ';
    $sql['where'][] = 'AND c.PaymentAmt = \'0.00\' ';
    $sql['where'][] = 'AND a.CollegeId = d.CollegeId ';
    $sql['where'][] = 'AND c.AwardId IN ('.$sub.')';
    $where = implode(' ',$sql['where']);
    $sql['where'] = $where;
    $sql['orderby'] = 'ORDER BY a.LastName';
    $result = $wpdb->get_results(implode(' ',$sql));
    //ts_data(implode(' ',$sql));
    //ts_data($result);
    $check_data = array();
    foreach($result as $k => $user){
        $student_id = $user->StudentId != ''?$user->StudentId:'SSN '.$user->Last4SSN;
        if($user->theCollegeId == 343 || $user->theCollegeId == 0){
            $college = $this->queries->get_other_school($user->ApplicantId);
        } else {
            $college = $this->queries->get_college_by_id($user->theCollegeId);
        }

        $fund = $user->Name;
        $collegefund = $college;
        $check_data[$collegefund]['College'] = $college;
        $check_data[$collegefund]['Students'][$user->ApplicantId] = array(
            //'ID' => $user->ApplicantId,
            'College'   => $college,
            'FirstName' => $user->FirstName,
            'LastName'  => $user->LastName,
            'StudentId' => $student_id,
            'Sholarship'  => $user->ScholarshipName,
        );
        switch($user->InstitutionTermTypeId){
            case 2:
                $check_data[$collegefund]['CheckAmount'][] = $user->AmountAwarded/3;
                $check_data[$collegefund]['Students'][$user->ApplicantId]['CheckAmount'] = $user->AmountAwarded/3;
                break;
            case 3:
            default:
            $check_data[$collegefund]['CheckAmount'][] = $user->AmountAwarded/2;
            $check_data[$collegefund]['Students'][$user->ApplicantId]['CheckAmount'] = $user->AmountAwarded/2;
            break;
        }
    }
    ksort($check_data);
    //ts_data($check_data);
}
//print a small selection form
$this->search->print_form_custom_searches('checks_to_print',true,$fields);
//turn result into a report.
$class = implode(" ",apply_filters('msdlab_csf_report_display_table_class', array('table','table-bordered','sortable')));

$fields = array('College','FirstName','LastName','StudentId','Scholarship','CheckAmount');
$ret = array();
$ret['start_table'] = '<table id="'.$id.'" class="'.$class.'">';
$ret['table_header'] = $this->report->table_header($fields,false);
$ret['table_data'] = $this->report->checks_to_print_table_data($fields,$result,$check_data);
$ret['table_footer'] = $this->report->table_footer($fields,$info,false);
$ret['end_table'] = '</table>';
$ret['export'] = $this->report->print_export_tools($id);

//print any results
print implode("\n\r", $ret);