<?php
$fields = array(
    'RenewalId',
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
    'Last4SSN',
    'DateOfBirth',
    'CurrentCumulativeGPA',
    'CoopStudyAbroadNote',
    'RenewalDateTime',
    'AnticipatedGraduationDate',
    'YearsWithCSF',
    'CollegeId',
    'MajorId',
    'TermsAcknowledged',
    'RenewalLocked',
    'Notes'
);
$tabs = '';
$pane = array();
if($_POST) {
    //ts_data($_POST);
    $result = $this->queries->get_renewal_report_set($fields);
    $submitted = array();
    foreach ($result AS $k => $renewal) {
        $submitted[] = $renewal;
    }
    $info = '';
    $class = array('table','table-bordered');
    if($result){
        $tabs = '
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#submitted" aria-controls="submitted" role="tab" data-toggle="tab">Submitted</a></li>
  </ul>';

        if(count($submitted)>0){
            $pane['submitted'] = '<div role="tabpanel" class="tab-pane active" id="submitted">
                            ' . implode("\n\r",$this->report->print_table('renewal_submitted',$fields,$submitted,$info,$class,false)) .'
                        </div>';
        } else {
            $pane['submitted'] = '<div role="tabpanel" class="tab-pane active" id="submitted">
                            <div class="notice bg-info text-info">No results</div>
                        </div>';
        }
    } else {
        $tabs = '<div class="notice bg-info text-info">No results</div>';
    }
}
print '<h2>Scholarship Renewal Reports</h2>';
if(!$_POST) {
    $this->search->javascript['search-btn'] = '
        $(".search-button input").val("Load All Renewals");
        $(".query-filter input, .query-filter select").change(function(){
            $(".search-button input").val("SEARCH");
        });';
}
$this->search->print_form('renewal');

print $tabs;
print '

  <!-- Tab panes -->
  <div class="tab-content">';
print $pane['submitted'];

print '</div>';
