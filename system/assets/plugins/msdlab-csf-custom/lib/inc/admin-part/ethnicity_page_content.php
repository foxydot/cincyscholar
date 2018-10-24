<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:39 PM
 */
//page content here
print '<div class="wrap report_table">';
print '<h1 class="wp-heading-inline">Ethnicity Settings</h1>';
//button: add ethnicity
print ' <a href="admin.php?page=ethnicity-edit" class="page-title-action">Add New Ethnicity</a>
            <hr class="wp-header-end">';
//list ethnicities with edit button,
//$this->controls->print_settings();
$ethnicities = $this->queries->get_all_ethnicities();
if (count($ethnicities) > 0) {
    $alphas = range('A', 'Z');
    foreach ($alphas AS $a) {
        $links[] = '<a href="#ethnicities-' . $a . '">' . $a . '</a>';
    }
    $linkstrip = implode(' | ', $links);
    foreach ($ethnicities AS $ethnicity) {
        $cell['ethnicity_name'] = '<span id="ethnicities-' . substr($ethnicity->Ethnicity, 0, 1) . '"><stong>' . $ethnicity->Ethnicity . '</stong></span>';
        $cell['edit'] = '<a href="admin.php?page=ethnicity-edit&ethnicity_id=' . $ethnicity->EthnicityId . '" class="button">Edit Ethnicity</a>';
        $row[] = implode('</td><td>', $cell);
    }
    $table = implode("</td></tr>\n<tr><td>", $row);
    print $linkstrip;
    print '<table><tr><td>' . $table . '</td></tr></table>';
}
print '</div>';