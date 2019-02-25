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
    $where = array();
    $where['applicant'] = 'UserId = ' . $_POST['Applicant_UserId_input'];
    if($_POST['Renewal_UserId_input']){
        $where['renewal'] = 'UserId = ' . $_POST['Renewal_UserId_input'];
    }
    if($_POST['Guardian_ApplicantId_input']){
        $where['guardian'] = 'ApplicantId = ' . $_POST['Guardian_ApplicantId_input'];
    }
    if($_POST['Agreements_ApplicantId_input']){
        $where['agreements'] = 'ApplicantId = ' . $_POST['Agreements_ApplicantId_input'];
    }
    if(is_numeric($_POST['ApplicantScholarship_ApplicantId_input'])){
        $where['applicantscholarship'] = 'ApplicantId = ' . $_POST['ApplicantScholarship_ApplicantId_input'];
    }
    if($_POST['Applicant_UserId_input']){
        $where['studentneed'] = 'UserId = ' . $_POST['Applicant_UserId_input'];
    }
    $where['payment'] = 'UserId = ' . $_POST['Applicant_UserId_input'];
    if ($msg = $this->queries->set_data('single_student', $where, $notifications)) {
        print '<div class="updated notice notice-success is-dismissible">' . $msg . '</div>';
        if($_POST['ApplicantScholarship_AwardId_input'] > 0 && $_POST['ApplicantScholarship_ScholarshipId_input'] == ''){
            $deletewhere['applicantscholarship'] = 'AwardId = ' . $_POST['ApplicantScholarship_AwardId_input'];
            $this->queries->delete_data('single_student', $deletewhere, $notifications);
        }
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
        //ts_data($student); //display errors
    } else {
        //ts_data($student); //display errors
        $tabs = $pane = array();
        if($student){
            $form_id = 'single_student';
            foreach($student['scholarship'] AS $disbursement_scholarship) {
                $disbursement_tabs[] = '<li role="presentation"><a href="#disbursement_'.$disbursement_scholarship->ScholarshipId.'" aria-controls="disbursement_'.$disbursement_scholarship->ScholarshipId.'" role="tab" data-toggle="tab">Disbursement ('.$this->queries->get_scholarship_by_id($disbursement_scholarship->ScholarshipId).')</a></li>';
                    }
            $tabs = '
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#student" aria-controls="student" role="tab" data-toggle="tab">Student</a></li>
    '.implode('',$disbursement_tabs).'
    <li role="presentation"><a href="#application" aria-controls="application" role="tab" data-toggle="tab">Application</a></li>
    <li role="presentation"><a href="#signatures" aria-controls="signatures" role="tab" data-toggle="tab">Signatures</a></li>
  </ul>';

            $pane['student'] = '<div role="tabpanel" class="tab-pane active" id="student">
                    '.$this->report->student_form($student).'
                </div>';
            $jquery[] = "
                $('input[type=tel]').change(function(){
                    text = $(this).val().replace(/(\d{3})(\d{3})(\d{4})/, \"($1) $2-$3\");
                    $(this).val(text);
                });
                $('input[type=date]').each(function(){
                    if($(this).val() == '1970-01-01'){
                          $(this).val('');
                    }
                });
                $('.gpa1in input').change(function(){
                    var gpa = $(this).val();
                    $('.gpa1 input').val(gpa);
                });
                $('.gpa2in input').change(function(){
                    var gpa = $(this).val();
                    $('.gpa2 input').val(gpa);
                });
                $('.gpa3in input').change(function(){
                    var gpa = $(this).val();
                    $('.gpa3 input').val(gpa);
                });
                $('.gpacin input').change(function(){
                    var gpa = $(this).val();
                    $('.gpac input').val(gpa);
                });
            ";
            foreach($student['scholarship'] AS $k => $disbursement_scholarship) {
                $pane['disbursement_'.$disbursement_scholarship->ScholarshipId] = '<div role="tabpanel" class="tab-pane" id="disbursement_'.$disbursement_scholarship->ScholarshipId.'">
                    ' . $this->report->payment_form($student,$k) . '
                </div>';
            }
            $jquery[] = "
            $('#calculateneed_button').click(function(e){
                e.preventDefault();
                var direct;
                var indirect;
                var income;
                values = [$('#studentneed_FamilyContribution_input').val(),
                $('#studentneed_Pell_input').val(),
                $('#studentneed_SEOG_input').val(), 
                $('#studentneed_OSCG_input').val(), 
                $('#studentneed_Stafford_input').val(), 
                $('#studentneed_ExternalScholarshipAmt1_input').val(),
                $('#studentneed_ExternalScholarshipAmt2_input').val(), 
                $('#studentneed_ExternalScholarshipAmt3_input').val(), 
                $('#studentneed_ExternalScholarshipAmt4_input').val(), 
                $('#studentneed_ExternalScholarshipAmt5_input').val(), 
                $('#studentneed_ExternalScholarshipAmt6_input').val()];
                vLen = values.length;
                income = 0;
                for(i = 0; i < vLen; i++){
                    if(parseFloat(values[i]) > 0){
                    income = income + parseFloat(values[i]);
                    }
                }
                direct = $('#studentneed_DirectCost_input').val() - income;
                indirect = $('#studentneed_IndirectCost_input').val() - income;
                $('#studentneed_DirectNeed_input').val(direct);
                $('#studentneed_IndirectNeed_input').val(indirect);
            });
            ";
            $jquery[] = "
            $('.awardcalc input').change(function(){
                var plusval = 0;
                var minusval = 0;
                var amountactuallyawarded = 0;
                $('.awardcalc.plus input').each(function(){
                    plusval = plusval + parseFloat($(this).val());
                });
                $('.awardcalc.minus input').each(function(){
                    minusval = minusval + parseFloat($(this).val());
                });
                amountactuallyawarded = plusval - minusval;
                $('#ApplicantScholarship_AmountActuallyAwarded_info').html(amountactuallyawarded);
                $('#ApplicantScholarship_AmountActuallyAwarded_input').val(amountactuallyawarded);
            });
            ";
            $jquery[] = "
            $('#Applicant_Reject_input,#Renewal_Reject_input').change(function(){
                if($(this).is(':checked')){
                    $('#ApplicantScholarship_ScholarshipId_input').val('');
                    $('#ApplicantScholarship_AmountAwarded_input').val(0);
                    $('#ApplicantScholarship_DateAwarded_input').val(0);
                }
            });
            ";
            $scholarship_array = $this->queries->get_select_array_from_db('scholarship', 'ScholarshipId', 'Name','Name',1);

            $newscholarship['ApplicantScholarship_ApplicantId_new'] = $this->form->field_hidden("ApplicantScholarship_ApplicantId_new", $student['personal']->ApplicantId);

            $newscholarship['ApplicantScholarship_ScholarshipId_new'] = $this->form->field_select('ApplicantScholarship_ScholarshipId_new', null, 'New Scholarship', null, $scholarship_array, array(), array('col-md-3', 'col-sm-12'));
            $newscholarship['ApplicantScholarship_AmountAwarded_new'] = $this->form->field_textfield('ApplicantScholarship_AmountAwarded_new', null, 'Amount Awarded', '', array('type' => 'number'), array('col-md-3', 'col-sm-12', 'currency'));
            $newscholarship['ApplicantScholarship_AmountActuallyAwarded_new'] = $this->form->field_textfield('ApplicantScholarship_AmountActuallyAwarded_new', null, 'Amount Actually Awarded', '', array('type' => 'number'), array('col-md-3', 'col-sm-12', 'currency'));

            $newscholarship['ApplicantScholarship_DateAwarded_new'] = $this->form->field_date('ApplicantScholarship_DateAwarded_new','', 'Date Awarded', array(), array('datepicker', 'col-md-3', 'col-sm-12'));
            $newscholarship['ApplicantScholarship_Renew_new'] = $this->form->field_boolean('ApplicantScholarship_Renew_new', 0, 'Renew', array(), array('col-md-2', 'col-sm-12'));
            $newscholarship['ApplicantScholarship_ThankYou_new'] = $this->form->field_boolean('ApplicantScholarship_ThankYou_new', 0, 'Thank You', array(), array('col-md-2', 'col-sm-12'));
            $newscholarship['ApplicantScholarship_Signed_new'] = $this->form->field_boolean('ApplicantScholarship_Signed_new', 0, 'Signed', array(), array('col-md-2', 'col-sm-12'));

            $newscholarshipform = str_replace(array("\n", "\r"), '', implode("",$newscholarship));
            $newscholarshipform = str_replace("'","\'",$newscholarshipform);

            $jquery[] = "
            $('#add_scholarship_btn').click(function(){
                var html = '".$newscholarshipform."';
                $('#scholarship_new').html(html);
            });
            ";
            /*$jquery[] = '$("#' . $form_id . '").validate({
                    
		errorPlacement: function(error, element) {
			// Append error within linked label
			$( element )
				.closest( "form" )
					.find( "label[for=\'" + element.attr( "id" ) + "\']" )
						.append( error );
		},
		errorElement: "span",
		onfocusout: function(element) {
            // "eager" validation
            this.element(element);  
        }
});';*/
            $pane['application'] = '<div role="tabpanel" class="tab-pane" id="application">
                    '.$this->report->application_form($student).'
                    '.$this->report->need_form($student).'

                </div>';

            $pane['signatures'] = '<div role="tabpanel" class="tab-pane" id="signatures">
                    '.$this->report->other_form($student).'
                </div>';

            $student['firstname'] = $student['renewal']->FirstName?$student['renewal']->FirstName:$student['personal']->FirstName;
            $student['lastname'] = $student['renewal']->LastName?$student['renewal']->LastName:$student['personal']->LastName;

            $ret['title'] = '<h2>Edit '.$student['firstname'].' '.$student['lastname'].' (User '.$student['personal']->UserId.')</h2>';
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
            $ret['actions'] = $this->report->action_form($student);
            $ftr['button'] = $this->form->field_button('saveBtn', 'SAVE', array('submit', 'btn'), 'submit', false);
            $ret['form_footer'] = $this->form->form_footer('form_footer',implode("\n",$ftr),array('form-footer', 'col-md-12'));
            $ret['javascript'] = $this->form->build_jquery($form_id,$jquery);
            $ret['nonce'] = wp_nonce_field( $form_id );
            $ret['form_close'] = $this->form->form_close();

            print implode("\n",$ret);

            ts_data($student);
        } else {
            print "Error. No records for this student. This should never happen.";
            die();
        }
    }
} else {
    //error_log("f me");
    print "Error. No records for this student. This should never happen.";
    die();
}