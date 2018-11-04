<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:39 PM
 */
//page content here
print '<div class="wrap report_table">';
print '<h1 class="wp-heading-inline">Employer Settings</h1>';
print ' <a href="admin.php?page=employer-edit" class="page-title-action">Add New Employer</a>
            <hr class="wp-header-end">';
$employers = $this->queries->get_all_employers();
if (count($employers) > 0) {
    $alphas = range('A', 'Z');
    foreach ($alphas AS $a) {
        $links[] = '<a href="#employers-' . $a . '">' . $a . '</a>';
    }
    $linkstrip = implode(' | ', $links);
    foreach ($employers AS $employer) {
        $cell['employer_name'] = '<span id="employers-' . substr($employer->employername, 0, 1) . '"><stong>' . $employer->employername . '</stong></span>';
        $cell['employer_notes'] = $employer->Notes;
        $cell['employer_edit'] = '<a href="admin.php?page=employer-edit&employer_id=' . $employer->employerid . '" class="button">Edit Employer</a>';

        $row[] = implode('</td><td>', $cell);
    }
    $table = implode("</td></tr>\n<tr><td>", $row);
    print $linkstrip;
    print '<table><tr><td>' . $table . '</td></tr></table>';
}
print '</div>';