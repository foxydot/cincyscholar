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