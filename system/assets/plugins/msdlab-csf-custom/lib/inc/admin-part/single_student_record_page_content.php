<?php
$tabs = '';
$pane = array();
if($_POST) {
    //ts_data($_POST);
    $user_id = $_POST['Applicant_UserId_input'];
    $notifications = array(
        'nononce' => 'Student info could not be saved.',
        'success' => 'Student info saved!'
    );
    $where = array(
        'applicant' => 'UserId = ' . $_POST['Applicant_UserId_input'],
        'renewal' => 'UserId = ' . $_POST['Renewal_UserId_input'],
        'guardian' => 'ApplicantId = ' . $_POST['Guardian_ApplicantId_input'],
        'agreements' => 'ApplicantId = ' . $_POST['Agreements_ApplicantId_input'],
        'applicantscholarship' => 'ApplicantId = ' . $_POST['ApplicantScholarship_ApplicantId_input'],
        'studentneed' => 'UserId = ' . $_POST['Applicant_UserId_input'],
        'payment' => 'UserId = ' . $_POST['Applicant_UserId_input'],
    );
    if ($msg = $this->queries->set_data('single_student', $where, $notifications)) {
        print '<div class="updated notice notice-success is-dismissible">' . $msg . '</div>';
        unset($_POST);
    }
} elseif($_GET){
    $user_id = $_GET['user_id'];
} else {
    print "Error. Please select student.";
    die();
}

$applicant_id = $this->queries->get_applicant_id($user_id);

if($student = $this->queries->get_student_data($applicant_id)) {
    if(is_wp_error($student)){
        ts_data($student); //display errors
    } else {

        //ts_data($student); //display errors
        $tabs = $pane = array();
        if($student){
            $form_id = 'single_student';
            $tabs = '
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#student" aria-controls="student" role="tab" data-toggle="tab">Student</a></li>
    <li role="presentation"><a href="#disbursement" aria-controls="disbursement" role="tab" data-toggle="tab">Disbursement</a></li>
    <li role="presentation"><a href="#application" aria-controls="application" role="tab" data-toggle="tab">Application</a></li>
    <li role="presentation"><a href="#signatures" aria-controls="signatures" role="tab" data-toggle="tab">Signatures</a></li>
  </ul>';

            $pane['student'] = '<div role="tabpanel" class="tab-pane active" id="student">
                    '.$this->report->student_form($student).'
                </div>';
            $pane['disbursement'] = '<div role="tabpanel" class="tab-pane" id="disbursement">
                    '.$this->report->payment_form($student).'
                </div>';
            $jquery[] = "
            $('#calculateneed_button').click(function(e){
                e.preventDefault();
                var direct;
                var indirect;
                var income;
                income = $('#studentneed_FamilyContribution_input').val() + 
                $('#studentneed_Pell_input').val() + 
                $('#studentneed_SEOG_input').val() + 
                $('#studentneed_OIG_input').val() + 
                $('#studentneed_OSCG_input').val() + 
                $('#studentneed_OSCG_input').val() + 
                $('#studentneed_ExternalScholarshipAmt1_input').val() + 
                $('#studentneed_ExternalScholarshipAmt2_input').val() + 
                $('#studentneed_ExternalScholarshipAmt3_input').val() + 
                $('#studentneed_ExternalScholarshipAmt4_input').val() + 
                $('#studentneed_ExternalScholarshipAmt5_input').val() + 
                $('#studentneed_ExternalScholarshipAmt6_input').val();
                direct = $('#studentneed_DirectCost_input').val() - income;
                indirect = $('#studentneed_IndirectCost_input').val() - income;
                $('#studentneed_DirectNeed_input').val(direct);
                $('#studentneed_IndirectNeed_input').val(indirect);
            });
            ";
            $pane['application'] = '<div role="tabpanel" class="tab-pane" id="application">
                    '.$this->report->application_form($student).'
                    '.$this->report->need_form($student).'

                </div>';

            $pane['signatures'] = '<div role="tabpanel" class="tab-pane" id="signatures">
                    '.$this->report->other_form($student).'
                </div>';

            $ret['title'] = '<h2>Edit '.$student['personal']->FirstName.' '.$student['personal']->LastName.' (User '.$student['personal']->UserId.')</h2>';
            $ret['form_header'] = $this->form->form_header($form_id,array($form_id));
            $ret['Applicant_UserId'] = $this->form->field_hidden("UserId", $user_id);
            $ret['ApplicantId'] = $this->form->field_utility("ApplicantId", $applicant_id);

            $renewal = isset($student['renewal']->RenewalId)?true:false;
            if($renewal) {
                $ret['RenewalInfo'] = $this->form->field_textinfo("RenewalInfo", '', 'RENEWAL', null, null, array('notice', 'renewal'));
            } else{
                $ret['OriginalInfo'] = $this->form->field_textinfo("OriginalInfo", '', 'FIRST TIME APPLICATION', null, null, array('notice', 'original'));
            }
            $ret['tabs'] =  $tabs;
            $ret[] = '

<!-- Tab panes -->
<div class="tab-content">';
            $ret['panes'] = implode("\n\n",$pane);
            $ret[] = '</div>';
            $ftr['button'] = $this->form->field_button('saveBtn', 'SAVE', array('submit', 'btn'));
            $ret['form_footer'] = $this->form->form_footer('form_footer',implode("\n",$ftr),array('form-footer', 'col-md-12'));
            $ret['javascript'] = $this->form->build_jquery($form_id,$jquery);
            $ret['nonce'] = wp_nonce_field( $form_id );
            $ret['form_close'] = $this->form->form_close();

            print implode("\n",$ret);
        } else {
            print "Error. No records for this student. This should never happen.";
            die();
        }
    }
} else {
    error_log("f me");
    print "Error. No records for this student. This should never happen.";
    die();
}