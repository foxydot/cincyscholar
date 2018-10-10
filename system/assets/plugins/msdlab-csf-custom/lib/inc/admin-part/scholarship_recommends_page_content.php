<?php
$fields = array(
    'UserId',
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
    'Documents',
    'Notes',
);
    if(!$scholarship_id){$scholarship_id = $_GET['scholarship_id'];}
    $tabs = $pane = array();
    $application_start_date = get_option('csf_settings_start_date');
    $application_end_date = get_option('csf_settings_end_date');
    $scholarship = $this->queries->get_scholarship($scholarship_id);
    $results_exisit = false;
    $result = $this->queries->get_recommended_students($fields,$scholarship_id);
    ts_data($result);
    $submitted = $incomplete = $awarded = array();
    foreach ($result AS $k => $applicant) {
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
    if(count($awarded) > 0 || count($submitted) > 0 || count($incomplete) > 0){$results_exisit = true;}

    /*$result = $this->queries->get_renewal_report_set($fields2);
    ts_data($result);
    $renewals = array();
    foreach ($result AS $k => $renewal) {

        if (($renewal->ScholarshipId > 0 ) && (strtotime($renewal->DateAwarded) > strtotime($application_start_date))) {
            error_log('is awardee');
            $awarded[] = $renewal;
        } else {
            $renewals[] = $renewal;
        }
    }
*/
    if(count($awarded) > 0 || count($renewals) > 0){$results_exisit = true;}

    $info = '';
    $class = array('table','table-bordered','sortable');
    if($results_exisit){
        $tabs = '
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation"><a href="#awarded" aria-controls="awarded" role="tab" data-toggle="tab">Scholarship Awardees</a></li>
    <li role="presentation" class="active"><a href="#submitted" aria-controls="submitted" role="tab" data-toggle="tab">Submitted Applications</a></li>
    <li role="presentation"><a href="#incomplete" aria-controls="incomplete" role="tab" data-toggle="tab">Incomplete Applications</a></li>
    <li role="presentation"><a href="#renewal" aria-controls="renewal" role="tab" data-toggle="tab">Submitted Renewals</a></li>
  </ul>';
        if(count($awarded)>0){
            $pane['awarded'] = '<div role="tabpanel" class="tab-pane" id="awarded">
                            <div class="result-count">'.count($awarded).' Results Found</div>
                            ' . implode("\n\r",$this->report->print_table('application_awarded',$fields,$awarded,$info,$class,false)) .'
                            '. $this->report->print_export_email(sanitize_with_underscores($scholarship->Name).'-Recommendations',$scholarship,$scholarship->Contacts) .'
                        </div>';
        } else {
            $pane['awarded'] = '<div role="tabpanel" class="tab-pane" id="awarded">
                            <div class="notice bg-info text-info">No results</div>
                        </div>';
        }
        if(count($submitted)>0){
            $pane['submitted'] = '<div role="tabpanel" class="tab-pane active" id="submitted">
                            <div class="result-count">'.count($submitted).' Results Found</div>
                            ' . implode("\n\r",$this->report->print_table('application_submitted',$fields,$submitted,$info,$class,false)) .'
                            '. $this->report->print_export_email(sanitize_with_underscores($scholarship->Name).'-Recommendations',$scholarship,$scholarship->Contacts) .'
                        </div>';
        } else {
            $pane['submitted'] = '<div role="tabpanel" class="tab-pane" id="submitted">
                            <div class="notice bg-info text-info">No results</div>
                        </div>';
        }
        if(count($incomplete)>0){
            $pane['incomplete'] = '<div role="tabpanel" class="tab-pane" id="incomplete">
                            <div class="result-count">'.count($incomplete).' Results Found</div>
                            ' . implode("\n\r",$this->report->print_table('application_incomplete',$fields,$incomplete,$info,$class,false)) .'
                            '. $this->report->print_export_email(sanitize_with_underscores($scholarship->Name).'-Recommendations',$scholarship,$scholarship->Contacts) .'
                        </div>';
        } else {
            $pane['incomplete'] = '<div role="tabpanel" class="tab-pane" id="incomplete">
                            <div class="notice bg-info text-info">No results</div>
                        </div>';
        }
        if(count($renewal)>0){
            $pane['renewal'] = '<div role="tabpanel" class="tab-pane" id="renewal">
                            <div class="result-count">'.count($renewal).' Results Found</div>
                            ' . implode("\n\r",$this->report->print_table('renewal',$fields2,$renewals,$info,$class,false)) .'
                            '. $this->report->print_export_email(sanitize_with_underscores($scholarship->Name).'-Recommendations',$scholarship,$scholarship->Contacts) .'
                        </div>';
        } else {
            $pane['renewal'] = '<div role="tabpanel" class="tab-pane" id="renewal">
                            <div class="notice bg-info text-info">No results</div>
                        </div>';
        }
    } else {
        $tabs = '<div class="notice bg-info text-info">No results</div>';
    }

print '<h2>'. $scholarship->Name .' Recommendations</h2>';

    print $tabs;
    print '

  <!-- Tab panes -->
  <div class="tab-content">';
    print implode("\n",$pane);
    print '</div>';
