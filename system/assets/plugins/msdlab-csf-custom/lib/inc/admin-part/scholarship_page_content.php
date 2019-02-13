<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:39 PM
 */
//page content here
print '<div class="wrap report_table">';
print '<h1 class="wp-heading-inline">Scholarship Settings</h1>';
//button: add scholarship
print ' <a href="admin.php?page=scholarship-edit" class="page-title-action">Add New Scholarship</a>
            <hr class="wp-header-end">';
//list scholarships with edit button, view contacts button
//contacts in a slidedown box?
//$this->controls->print_settings();
$scholarships = $this->queries->get_all_scholarships();
$fund = $this->queries->get_all_funds();
foreach ($fund AS $f){
    $funds[$f->FundId] = $f->Name;
}
if (count($scholarships) > 0) {
    $alphas = range('A', 'Z');
    foreach ($alphas AS $a) {
        $links[] = '<a href="#scholarships-' . $a . '">' . $a . '</a>';
    }
    $linkstrip = implode(' | ', $links);
    foreach ($scholarships AS $scholarship) {
        $renewable = $scholarship->Renewable == 1?'YES':'NO';
        $cell['scholarship_name'] = '<span id="scholarships-' . substr($scholarship->Name, 0, 1) . '"><strong>' . $scholarship->Name . '</strong></span><br />
Fund: '. $funds[$scholarship->FundId] . '<br />
Renewable: '.$renewable.'<br />
Expires: '.$scholarship->Expiration.'<br />';
        $cell['edit'] = '<a href="admin.php?page=scholarship-edit&scholarship_id=' . $scholarship->ScholarshipId . '" class="button">Edit Scholarship</a>';
        $cell['recommends'] = '<a href="admin.php?page=scholarship-recommends&scholarship_id=' . $scholarship->ScholarshipId . '" class="button">View recommended students</a>';

        $row[] = implode('</td><td>', $cell);
    }
    $table = implode("</td></tr>\n<tr><td>", $row);
    print $linkstrip;
    print '<table><tr><td>' . $table . '</td></tr></table>';
}
print '</div>';