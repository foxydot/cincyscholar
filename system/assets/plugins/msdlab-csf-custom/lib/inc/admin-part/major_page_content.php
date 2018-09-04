<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:39 PM
 */
//page content here
print '<div class="wrap report_table">';
print '<h1 class="wp-heading-inline">Major Settings</h1>';
//button: add major
print ' <a href="admin.php?page=major-edit" class="page-title-action">Add New Major</a>
            <hr class="wp-header-end">';
//list majors with edit button, view contacts button
//contacts in a slidedown box?
//$this->controls->print_settings();
$majors = $this->queries->get_all_majors();
if (count($majors) > 0) {
    $alphas = range('A', 'Z');
    foreach ($alphas AS $a) {
        $links[] = '<a href="#majors-' . $a . '">' . $a . '</a>';
    }
    $linkstrip = implode(' | ', $links);
    foreach ($majors AS $major) {
        $cell['major_name'] = '<span id="majors-' . substr($major->MajorName, 0, 1) . '"><stong>' . $major->MajorName . '</stong></span><br />
'. $types[$major->SchoolTypeId];
        $cell['edit'] = '<a href="admin.php?page=major-edit&major_id=' . $major->MajorId . '" class="button">Edit Major</a>';
        $row[] = implode('</td><td>', $cell);
    }
    $table = implode("</td></tr>\n<tr><td>", $row);
    print $linkstrip;
    print '<table><tr><td>' . $table . '</td></tr></table>';
}
print '</div>';