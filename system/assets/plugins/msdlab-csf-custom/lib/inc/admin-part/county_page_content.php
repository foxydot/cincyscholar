<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:39 PM
 */
//page content here
print '<div class="wrap report_table">';
print '<h1 class="wp-heading-inline">Counties Settings</h1>';
//button: add county
print ' <a href="admin.php?page=county-edit" class="page-title-action">Add New County</a>
            <hr class="wp-header-end">';
//list counties with edit button,
//$this->controls->print_settings();
$counties = $this->queries->get_all_counties();
if (count($counties) > 0) {
    $alphas = range('A', 'Z');
    foreach ($alphas AS $a) {
        $links[] = '<a href="#counties-' . $a . '">' . $a . '</a>';
    }
    $linkstrip = implode(' | ', $links);
    foreach ($counties AS $county) {
        $cell['county_name'] = '<span id="counties-' . substr($county->County, 0, 1) . '"><stong>' . $county->County . ' ('.$county->StateID.')</stong></span>';
        $cell['edit'] = '<a href="admin.php?page=county-edit&county_id=' . $county->CountyId . '" class="button">Edit County</a>';
        $row[] = implode('</td><td>', $cell);
    }
    $table = implode("</td></tr>\n<tr><td>", $row);
    print $linkstrip;
    print '<table><tr><td>' . $table . '</td></tr></table>';
}
print '</div>';