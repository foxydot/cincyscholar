<?php
$fields['required'] = array(
    'UserId' => 'UserId',
    'ApplicantId' => 'ApplicantId',
);
$fields['applicant'] = array(
    'LastName' => 'Last Name',
    'FirstName' => 'First Name',
    'MiddleInitial' => 'Middle Initial',
    'StudentId' => 'Student ID',
    'Address1' => 'Address 1',
    'Address2' => 'Address 2',
    'City' => 'City',
    'StateId' => 'State',
    'ZipCode' => 'ZipCode',
    'CountyId' => 'County',
    'CellPhone' => 'Cell Phone',
    'AlternativePhone' => 'Alternative Phone',
    'Email' => 'Email',
    'user_email' => 'user_email',
    'SexId' => 'Gender',
    'EthnicityId' => 'Ethnicity',
    'Last4SSN' => 'Last 4 SSN',
    'DateOfBirth' => 'Date Of Birth',
    'CollegeId' => 'College',
    'MajorId' => 'Major',
    'HighSchoolGraduationDate' => 'High School Graduation Date',
    'ResumeOK' => 'Resume OK',
    'TranscriptOK' => 'Transcript OK',
    'FinancialAidOK' => 'Financial Aid OK',
    'FAFSAOK' => 'FAFSA OK',
    'HighSchoolId' => 'High School',

    'HighSchoolGPA' => 'High School GPA',
    'PlayedHighSchoolSports' => 'Played High School Sports',
    'Calipari' => 'Coach Calipari',

/*
    'EducationAttainmentId' => 'Education Attainment',
    'FirstGenerationStudent' => 'First Generation Student',
    'CPSPublicSchools' => 'CPS Public Schools',
    'OtherSchool' => 'Other School',
    'IsIndependent' => 'Is Independent',
    'Employer' => 'Employer',
    'ApplicationDateTime' => 'Application Date',
    'InformationSharingAllowed' => 'Information Sharing Allowed',
    'IsComplete' => 'Is Complete',
    'ApplicationLocked' => 'Locked',
    'AppliedBefore' => 'Applied Before',
    'Rejected' => 'Rejected',
    'Documents' => 'Documents',*/
);
/*$fields['agreements'] = array(
    'ApplicantHaveRead' => 'Applicant Have Read',
    'ApplicantDueDate' => 'Applicant Due Date',
    'ApplicantDocsReq' => 'Applicant Docs Req',
    'ApplicantReporting' => 'Applicant Reporting',
    'GuardianHaveRead' => 'Guardian Have Read',
    'GuardianDueDate' => 'Guardian Due Date',
    'GuardianDocsReq' => 'Guardian Docs Req',
    'GuardianReporting' => 'Guardian Reporting',
);*/
$fields['financial'] = array(
    //'InformationSharingAllowedByGuardian' => 'InformationSharingAllowedByGuardian',
    'DirectNeed' => 'Direct Need',
    'IndirectNeed' => 'Indirect Need',
    'GuardianFullName1' => 'GuardianFullName1',
    'GuardianEmployer1' => 'GuardianEmployer1',
    'GuardianFullName2' => 'GuardianFullName2',
    'GuardianEmployer2' => 'GuardianEmployer2',
    'Homeowner' => 'Homeowner',
    'AmountOwedOnHome' => 'AmountOwedOnHome',
    'HomeValue' => 'HomeValue',
    /*
    'ApplicantEmployer' => 'ApplicantEmployer',
    'ApplicantIncome' => 'ApplicantIncome',
    'SpouseEmployer' => 'SpouseEmployer',
    'SpouseIncome' => 'SpouseIncome',
    */
    );
$fields['renewal_required'] = array(
    'RenewalId' => 'RenewalId',
);
$fields['renewal'] = array(
    //'RenewalDateTime' => 'Renewal Date Time',
    'AnticipatedGraduationDate' => 'Anticipated Graduation Date',
    'CurrentCumulativeGPA' => 'Current Cumulative GPA',
    'YearsWithCSF' => 'Years With CSF',
    //'TermsAcknowledged' => 'Terms Acknowledged',
    //'RenewalLocked' => 'Renewal Locked',
    //'Reject' => 'Reject',
);
$fields['award'] = array(
    //'AwardId' => 'Award ID',
    'ScholarshipId' => 'Scholarship',
    'DateAwarded' => 'Date Awarded',
    'AmountAwarded' => 'Amount Awarded',
    'AmountActuallyAwarded' => 'Amount Actually Awarded',
    'Renew' => 'Renew',
    'ThankYou' => 'ThankYou',
    'Signed' => 'Signed',
    'GPA1' => 'GPA1',
    'GPA2' => 'GPA2',
    'GPA3' => 'GPA3',
    'GPAC' => 'GPAC',
);


$select_fields['default'] = array('title' => 'Default', 'fields' => array(
    'applicant_fields_LastName',
    'applicant_fields_FirstName',
    'applicant_fields_StudentId',
    'applicant_fields_EducationAttainmentId',
    'applicant_fields_CollegeId',
    'applicant_fields_MajorId',
));
$select_fields['scholarshipwhs'] = array('title' => 'Scholarship & High School', 'fields' => array(
    'applicant_fields_StudentId',
    'applicant_fields_FirstName',
    'applicant_fields_LastName',
    'applicant_fields_Email',
    'applicant_fields_HighSchoolId',
    'applicant_fields_HighSchoolGraduationDate',
    'applicant_fields_CollegeId',
    'award_fields_ScholarshipId',
    'award_fields_AmountAwarded',
));
$tabs = $pane = array();
if($_POST) {
    //ts_data($_POST);
    $this->search->javascript['collapse-btn-init'] = '
        $(".collapsable").css("display","none");
        $(".collapse-button i").removeClass("fa-compress").addClass("fa-expand");
        ';
    $results_exist = false;
    $applicant_fields_input = $_POST['applicant_fields_input']?$_POST['applicant_fields_input']:array();
    $financial_fields_input = $_POST['financial_fields_input']?$_POST['financial_fields_input']:array();
    $award_fields_input = $_POST['award_fields_input']?$_POST['award_fields_input']:array();
    $renewal_fields_input = $_POST['renewal_fields_input']?$_POST['renewal_fields_input']:array();

    $select_applicant_fields = array_merge($fields['required'],$applicant_fields_input,$financial_fields_input,$award_fields_input);
    //ts_data($select_applicant_fields);
    $result = $this->queries->get_report_set($select_applicant_fields);
    //ts_data($result);
    $submitted = $incomplete = $awarded = array();
    foreach ($result AS $k => $applicant) {
        if(!empty($this->post_vars['college_search_input'])){
            if($applicant->CollegeId != $_POST['college_search_input']){
                continue;
            }
        }
        //error_log('College Search Gate passed');
        if(!empty($_POST['employer_search_input'])){
            if(stripos($applicant->Employer,$_POST['employer_search_input'])===false &&
                stripos($applicant->GuardianEmployer1,$_POST['employer_search_input'])===false &&
                stripos($applicant->GuardianEmployer2,$_POST['employer_search_input'])===false){
                continue;
            }
        }
        //error_log('Employer Search Gate passed');
        if(isset($_POST['cps_employee_search_input'])){
            if($applicant->CPSPublicSchools != 1){
                continue;
            }
        }
        if(isset($_POST['calipari_search_input'])){
            if($applicant->Calipari != 1){
                continue;
            }
        }

        //error_log('CPS Search Gate passed');
        if (($applicant->ScholarshipId > 0) && (strtotime($applicant->DateAwarded) > strtotime($application_start_date))) {
            //error_log('is awardee');
            $awarded[] = $applicant;
        } elseif ($applicant->status == 2) {
            //error_log('is submitted');
            $submitted[] = $applicant;
        } else {
            //error_log('is incomplete');
            $incomplete[] = $applicant;
        }
    }
    if(count($awarded) > 0 || count($submitted) > 0 || count($incomplete) > 0){$results_exist = true;}

    $select_renewal_fields = array_merge($fields['required'],$fields['renewal_required'],$applicant_fields_input,$renewal_fields_input,$financial_fields_input,$award_fields_input);

    $result = $this->queries->get_renewal_report_set($select_renewal_fields);
    ts_data($result);
    $renewals = array();
    foreach ($result AS $k => $renewal) {
        if(!empty($this->post_vars['college_search_input'])){
            if($renewal->CollegeId != $_POST['college_search_input']){
                continue;
            }
        }

        if(!empty($_POST['employer_search_input'])){
            if(stripos($renewal->Employer,$_POST['employer_search_input'])===false &&
                stripos($renewal->GuardianEmployer1,$_POST['employer_search_input'])===false &&
                stripos($renewal->GuardianEmployer2,$_POST['employer_search_input'])===false){
                continue;
            }
        }

        if(isset($_POST['cps_employee_search_input'])){
            if($renewal->CPSPublicSchools != 1){
                continue;
            }
        }
        if(isset($_POST['calipari_search_input'])){
            if($applicant->Calipari != 1){
                continue;
            }
        }
        if (($renewal->ScholarshipId > 0 ) && (strtotime($renewal->DateAwarded) > strtotime($application_start_date))) {
            //error_log('is awardee');
            $awarded[] = $renewal;
        } else {
            $renewals[] = $renewal;
        }
    }

    if(count($awarded) > 0 || count($renewals) > 0){$results_exist = true;}
    $info = '';
    $class = array('table','table-bordered','sortable');
    if($results_exist){
        $tabs = '
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#awarded" aria-controls="awarded" role="tab" data-toggle="tab">Scholarship Awardees</a></li>
    <li role="presentation"><a href="#submitted" aria-controls="submitted" role="tab" data-toggle="tab">Submitted Applications</a></li>
    <li role="presentation"><a href="#incomplete" aria-controls="incomplete" role="tab" data-toggle="tab">Incomplete Applications</a></li>
    <li role="presentation"><a href="#renewal" aria-controls="renewal" role="tab" data-toggle="tab">Submitted Renewals</a></li>
  </ul>';
        if(count($awarded)>0){
            $pane['awarded'] = '<div role="tabpanel" class="tab-pane active" id="awarded">
                            <div class="result-count">'.count($awarded).' Results Found</div>
                            ' . implode("\n\r",$this->report->print_table('application_awarded',$select_applicant_fields,$awarded,$info,$class,false)) .'
                        </div>';
        } else {
            $pane['awarded'] = '<div role="tabpanel" class="tab-pane active" id="awarded">
                            <div class="notice bg-info text-info">No results</div>
                        </div>';
        }
        if(count($submitted)>0){
            $pane['submitted'] = '<div role="tabpanel" class="tab-pane" id="submitted">
                            <div class="result-count">'.count($submitted).' Results Found</div>
                            ' . implode("\n\r",$this->report->print_table('application_submitted',$select_applicant_fields,$submitted,$info,$class,false)) .'
                        </div>';
        } else {
            $pane['submitted'] = '<div role="tabpanel" class="tab-pane" id="submitted">
                            <div class="notice bg-info text-info">No results</div>
                        </div>';
        }
        if(count($incomplete)>0){
            $pane['incomplete'] = '<div role="tabpanel" class="tab-pane" id="incomplete">
                            <div class="result-count">'.count($incomplete).' Results Found</div>
                            ' . implode("\n\r",$this->report->print_table('application_incomplete',$select_applicant_fields,$incomplete,$info,$class,false)) .'
                        </div>';
        } else {
            $pane['incomplete'] = '<div role="tabpanel" class="tab-pane" id="incomplete">
                            <div class="notice bg-info text-info">No results</div>
                        </div>';
        }
        if(count($renewal)>0){
            $pane['renewal'] = '<div role="tabpanel" class="tab-pane" id="renewal">
                            <div class="result-count">'.count($renewals).' Results Found</div>
                            ' . implode("\n\r",$this->report->print_table('renewal',$select_renewal_fields,$renewals,$info,$class,false)) .'
                        </div>';
        } else {
            $pane['renewal'] = '<div role="tabpanel" class="tab-pane" id="renewal">
                            <div class="notice bg-info text-info">No results</div>
                        </div>';
        }
    } else {
        $tabs = '<div class="notice bg-info text-info">No results</div>';
    }
}
print '<h2>Scholarship Application Reports</h2>';
$this->search->javascript['collapse-fields'] = '
        $(".collapse-fields").click(function(){
            $(this).find("i").toggleClass("fa-compress").toggleClass("fa-expand");
            $(".collapsable-fields").slideToggle("slow");
        });';
$this->search->javascript['collapse-search'] = '
        $(".collapse-search").click(function(){
            $(this).find("i").toggleClass("fa-compress").toggleClass("fa-expand");
            $(".collapsable-search").slideToggle("slow");
        });';
$this->search->javascript['preset-fields'] = '
        var def = $(".default_button").attr("fieldset").split(",");
        $(".checkbox-wrapper input").each(function(){
            if($.inArray("#"+$(this).attr("id"),def) > -1){
                $(this).prop("checked", true);
            }
        });
        $(".fieldset_button").click(function(){
            $(".checkbox-wrapper input").prop("checked",false);
            fs = $(this).attr("fieldset");
            $(fs).prop("checked", true);
        });';
$this->search->javascript['collapse-fields-init'] = '
        $(".collapsable-fields").css("display","none");
        ';
if(!$_POST) {
    $this->search->javascript['search-btn'] = '
        $(".search-button input").val("Load All Students");
        $(".query-filter input, .query-filter select").focus(function(){
            $(".search-button input").val("SEARCH");
        });';
}
$this->search->print_form('consolidated',true,$fields,$select_fields);
if($_POST) {
    print $tabs;
    print '

  <!-- Tab panes -->
  <div class="tab-content">';
    print implode("\n",$pane);
    print '</div>';
}