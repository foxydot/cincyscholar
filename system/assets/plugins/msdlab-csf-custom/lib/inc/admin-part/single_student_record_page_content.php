<?php
$tabs = '';
$pane = array();
if($_POST) {
    $user_id = $_POST['user_id'];
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
            $form_id = 'single-student';
            $tabs = '
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#student" aria-controls="student" role="tab" data-toggle="tab">Student</a></li>
    <li role="presentation"><a href="#application" aria-controls="application" role="tab" data-toggle="tab">Application</a></li>
    <li role="presentation"><a href="#financial" aria-controls="financial" role="tab" data-toggle="tab">Financial</a></li>
    <li role="presentation"><a href="#payments" aria-controls="payments" role="tab" data-toggle="tab">Payments</a></li>
    <li role="presentation"><a href="#guardian" aria-controls="guardian" role="tab" data-toggle="tab">Parents/Family</a></li>
    <li role="presentation"><a href="#other" aria-controls="other" role="tab" data-toggle="tab">Other</a></li>
  </ul>';

            $pane['student'] = '<div role="tabpanel" class="tab-pane active" id="student">
                    '.$this->report->student_form($student).'
                </div>';
            $pane['application'] = '<div role="tabpanel" class="tab-pane" id="application">
                    '.$this->report->application_form($student).'
                </div>';
            $pane['financial'] = '<div role="tabpanel" class="tab-pane" id="financial">
                    '.$this->report->need_form($student).'
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
            $pane['payments'] = '<div role="tabpanel" class="tab-pane" id="payments">
                    '.$this->report->payment_form($student).'
                </div>';
            $pane['guardian'] = '<div role="tabpanel" class="tab-pane" id="guardian">
                    '.$this->report->guardian_form($student).'
                </div>';
            $pane['other'] = '<div role="tabpanel" class="tab-pane" id="other">
                    '.$this->report->other_form($student).'
                </div>';

            $ret['title'] = '<h2>Edit '.$student['personal']->FirstName.' '.$student['personal']->LastName.' (User '.$student['personal']->UserId.')</h2>';
            $ret['form_header'] = $this->form->form_header($form_id,array($form_id));
            $ret['Applicant_UserId'] = $this->form->field_hidden("UserId", $user_id);
            $ret['ApplicantId'] = $this->form->field_utility("ApplicantId", $applicant_id);

            $renewal = isset($student['renewal']->RenewalId)?true:false;
            if($renewal) {
                $ret['RenewalInfo'] = $this->form->field_textinfo("RenewalInfo", 'RENEWAL', '', null, null, array('notice', 'renewal'));
            } else{
                $ret['OriginalInfo'] = $this->form->field_textinfo("OriginalInfo", 'FIRST TIME APPLICATION', '', null, null, array('notice', 'original'));
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