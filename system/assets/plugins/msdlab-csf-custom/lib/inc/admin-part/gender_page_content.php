<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:39 PM
 */
//page content here
print '<div class="wrap report_table">';
print '<h1 class="wp-heading-inline">Gender Settings</h1>';
//button: add ethnicity
print ' <a href="admin.php?page=gender-edit" class="page-title-action">Add New Gender</a>
            <hr class="wp-header-end">';
//list ethnicities with edit button,
//$this->controls->print_settings();
$genders = $this->queries->get_all_genders();
if (count($genders) > 0) {
    $alphas = range('A', 'Z');
    foreach ($alphas AS $a) {
        $links[] = '<a href="#genders-' . $a . '">' . $a . '</a>';
    }
    $linkstrip = implode(' | ', $links);
    foreach ($genders AS $gender) {
        $cell['ethnicity_name'] = '<span id="gender-' . substr($gender->Sex, 0, 1) . '"><stong>' . $gender->Sex . '</stong></span>';
        $cell['edit'] = '<a href="admin.php?page=gender-edit&gender_id=' . $gender->SexId . '" class="button">Edit Gender</a>';
        $row[] = implode('</td><td>', $cell);
    }
    $table = implode("</td></tr>\n<tr><td>", $row);
    print $linkstrip;
    print '<table><tr><td>' . $table . '</td></tr></table>';
}
print '</div>';