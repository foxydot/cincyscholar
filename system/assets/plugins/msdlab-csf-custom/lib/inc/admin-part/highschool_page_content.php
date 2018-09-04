<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:39 PM
 */
//page content here
print '<div class="wrap report_table">';
print '<h1 class="wp-heading-inline">HighSchool Settings</h1>';
//button: add highschool
print ' <a href="admin.php?page=highschool-edit" class="page-title-action">Add New HighSchool</a>
            <hr class="wp-header-end">';
//list highschools with edit button, view contacts button
//contacts in a slidedown box?
//$this->controls->print_settings();
$highschools = $this->queries->get_all_highschools();
$highschooltypes = $this->queries->get_all_highschooltypes();
foreach ($highschooltypes AS $hst){
    $types[$hst->HighSchoolTypeId] = $hst->Type;
}
if (count($highschools) > 0) {
    $alphas = range('A', 'Z');
    foreach ($alphas AS $a) {
        $links[] = '<a href="#highschools-' . $a . '">' . $a . '</a>';
    }
    $linkstrip = implode(' | ', $links);
    foreach ($highschools AS $highschool) {
        $cell['highschool_name'] = '<span id="highschools-' . substr($highschool->SchoolName, 0, 1) . '"><stong>' . $highschool->SchoolName . '</stong></span><br />
'. $types[$highschool->SchoolTypeId] . '<br />
<a href="admin.php?page=highschool-edit&highschool_id=' . $highschool->HighSchoolId . '" class="button">Edit HighSchool</a>';
        $con = array();
        $c = array();
        $c['name'] = $highschool->ContactFirstName . ' ' . $highschool->ContactLastName;
        $c['email'] = '<a href="mailto:' . antispambot($highschool->EmailAddress) . '">' . antispambot($highschool->EmailAddress) . '</a>';
        $c['phone'] = $highschool->PhoneNumber;
        $con[] = implode('<br>', $c);
        $cell['contacts'] = implode('<br><br>', $con);
        $row[] = implode('</td><td>', $cell);
    }
    $table = implode("</td></tr>\n<tr><td>", $row);
    print $linkstrip;
    print '<table><tr><td>' . $table . '</td></tr></table>';
}
print '</div>';