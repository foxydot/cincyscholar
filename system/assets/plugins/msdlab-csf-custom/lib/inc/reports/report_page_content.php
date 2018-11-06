<?php
$fields = array(
    'ApplicantId',
    'FirstName',
    'MiddleInitial',
    'LastName',
    'Address1',
    'Address2',
    'City',
    'StateId',
    'ZipCode',
    'CountyId',
    'CellPhone',
    'AlternativePhone',
    'Email',
    'user_email',
    'Last4SSN',
    'DateOfBirth',
    'SexId',
    'FirstGenerationStudent',
    'EducationAttainmentId',
    'HighSchoolId',
    'HighSchoolGraduationDate',
    'HighSchoolGPA',
    'CollegeId',
    'MajorId',
    'OtherSchool',
    'IsIndependent',
    'PlayedHighSchoolSports',
    'Employer',
    'HardshipNote',
    'ApplicationDateTime',
    'InformationSharingAllowed',
    'IsComplete',
    'EthnicityId',
    'Activities',
    'ApplicantHaveRead',
    'ApplicantDueDate',
    'ApplicantDocsReq',
    'ApplicantReporting',
    'CPSPublicSchools',
    'GuardianHaveRead',
    'GuardianDueDate',
    'GuardianDocsReq',
    'GuardianReporting',
    'GuardianFullName1',
    'GuardianEmployer1',
    'GuardianFullName2',
    'GuardianEmployer2',
    'ApplicantEmployer',
    'ApplicantIncome',
    'SpouseEmployer',
    'SpouseIncome',
    'Homeowner',
    'HomeValue',
    'AmountOwedOnHome',
    'InformationSharingAllowedByGuardian',
    'Documents',
);
$tabs = '';
$pane = array();
if($_POST) {
    //ts_data($_POST);
    $result = $this->queries->get_report_set($fields);
    $submitted = $incomplete = array();
    foreach ($result AS $k => $applicant) {

        if(!empty($this->post_vars['college_search_input'])){
            if($applicant->CollegeID != $_POST['college_search_input']){
                continue;
            }
        }

        if(!empty($_POST['employer_search_input'])){
            if(stripos($applicant->Employer,$_POST['employer_search_input'])===false &&
                stripos($applicant->GuardianEmployer1,$_POST['employer_search_input'])===false &&
                stripos($applicant->GuardianEmployer2,$_POST['employer_search_input'])===false){
                continue;
            }
        }

        if(isset($_POST['cps_employee_search_input'])){
            if($applicant->CPSPublicSchools != 1){
                continue;
            }
        }

        if ($applicant->status == 2) {
            $submitted[] = $applicant;
        } else {
            $incomplete[] = $applicant;
        }
    }
    $info = '';
    $class = array('table','table-bordered','sortable');
    if($result){
        $tabs = '
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#submitted" aria-controls="submitted" role="tab" data-toggle="tab">Submitted</a></li>
    <li role="presentation"><a href="#incomplete" aria-controls="incomplete" role="tab" data-toggle="tab">incomplete</a></li>
  </ul>';

        if(count($submitted)>0){
            $pane['submitted'] = '<div role="tabpanel" class="tab-pane active" id="submitted">
                            ' . implode("\n\r",$this->report->print_table('application_submitted',$fields,$submitted,$info,$class,false)) .'
                        </div>';
        } else {
            $pane['submitted'] = '<div role="tabpanel" class="tab-pane active" id="submitted">
                            <div class="notice bg-info text-info">No results</div>
                        </div>';
        }
        if(count($incomplete)>0){
            $pane['incomplete'] = '<div role="tabpanel" class="tab-pane" id="incomplete">
                            ' . implode("\n\r",$this->report->print_table('application_incomplete',$fields,$incomplete,$info,$class,false)) .'
                        </div>';
        } else {
            $pane['incomplete'] = '<div role="tabpanel" class="tab-pane" id="incomplete">
                            <div class="notice bg-info text-info">No results</div>
                        </div>';
        }
    } else {
        $tabs = '<div class="notice bg-info text-info">No results</div>';
    }
}
print '<h2>Scholarship Application Reports</h2>';
if(!$_POST) {
    $this->search->javascript['search-btn'] = '
        $(".search-button input").val("Load All Applications");
        $(".query-filter input, .query-filter select").change(function(){
            $(".search-button input").val("SEARCH");
        });';
}
$this->search->print_form('application');

print $tabs;
print '

  <!-- Tab panes -->
  <div class="tab-content">';
print $pane['submitted'];
print $pane['incomplete'];

print '</div>';