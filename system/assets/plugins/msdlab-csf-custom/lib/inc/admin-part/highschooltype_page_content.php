<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:39 PM
 */
//page content here
print '<div class="wrap report_table">';
print '<h1 class="wp-heading-inline">HighSchool Type Settings</h1>';
print ' <a href="admin.php?page=highschooltype-edit" class="page-title-action">Add New HighSchool Type</a>
            <hr class="wp-header-end">';
$highschooltypes = $this->queries->get_all_highschooltypes();
if (count($highschooltypes) > 0) {
    $alphas = range('A', 'Z');
    foreach ($alphas AS $a) {
        $links[] = '<a href="#highschooltypes-' . $a . '">' . $a . '</a>';
    }
    $linkstrip = implode(' | ', $links);
    foreach ($highschooltypes AS $highschooltype) {
        $cell['highschooltype_name'] = '<span id="highschooltypes-' . substr($highschooltype->Type, 0, 1) . '"><stong>' . $highschooltype->Type . '</stong></span>';
        $cell['highschooltype_description'] = $highschooltype->Description;
        $cell['highschooltype_edit'] = '<a href="admin.php?page=highschooltype-edit&highschooltype_id=' . $highschooltype->HighSchoolTypeId . '" class="button">Edit HighSchool Type</a>';

        $row[] = implode('</td><td>', $cell);
    }
    $table = implode("</td></tr>\n<tr><td>", $row);
    print $linkstrip;
    print '<table><tr><td>' . $table . '</td></tr></table>';
}
print '</div>';