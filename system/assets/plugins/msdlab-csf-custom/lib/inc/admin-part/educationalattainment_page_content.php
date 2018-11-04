<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:39 PM
 */
//page content here
print '<div class="wrap report_table">';
print '<h1 class="wp-heading-inline">Educational Attainment Settings</h1>';
print ' <a href="admin.php?page=educationalattainment-edit" class="page-title-action">Add New Educational Attainment</a>
            <hr class="wp-header-end">';
$educationalattainments = $this->queries->get_all_educationalattainments();
if (count($educationalattainments) > 0) {
    $alphas = range('A', 'Z');
    foreach ($alphas AS $a) {
        $links[] = '<a href="#educationalattainments-' . $a . '">' . $a . '</a>';
    }
    $linkstrip = implode(' | ', $links);
    foreach ($educationalattainments AS $educationalattainment) {
        $cell['educationalattainment_name'] = '<span id="educationalattainments-' . substr($educationalattainment->EducationalAttainment, 0, 1) . '"><stong>' . $educationalattainment->EducationalAttainment . '</stong></span>';
        $cell['educationalattainment_edit'] = '<a href="admin.php?page=educationalattainment-edit&educationalattainment_id=' . $educationalattainment->EducationalAttainmentId . '" class="button">Edit Educational Attainment</a>';

        $row[] = implode('</td><td>', $cell);
    }
    $table = implode("</td></tr>\n<tr><td>", $row);
    print $linkstrip;
    print '<table><tr><td>' . $table . '</td></tr></table>';
}
print '</div>';