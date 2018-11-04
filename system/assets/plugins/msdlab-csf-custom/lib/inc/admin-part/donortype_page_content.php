<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:39 PM
 */
//page content here
print '<div class="wrap report_table">';
print '<h1 class="wp-heading-inline">Donor Type Settings</h1>';
print ' <a href="admin.php?page=donortype-edit" class="page-title-action">Add New Donor Type</a>
            <hr class="wp-header-end">';
$donortypes = $this->queries->get_all_donortypes();
if (count($donortypes) > 0) {
    $alphas = range('A', 'Z');
    foreach ($alphas AS $a) {
        $links[] = '<a href="#donortypes-' . $a . '">' . $a . '</a>';
    }
    $linkstrip = implode(' | ', $links);
    foreach ($donortypes AS $donortype) {
        $cell['donortype_name'] = '<span id="donortypes-' . substr($donortype->DonorType, 0, 1) . '"><stong>' . $donortype->DonorType . '</stong></span>';
        $cell['donortype_edit'] = '<a href="admin.php?page=donortype-edit&donortype_id=' . $donortype->DonorTypeId . '" class="button">Edit Donor Type</a>';

        $row[] = implode('</td><td>', $cell);
    }
    $table = implode("</td></tr>\n<tr><td>", $row);
    print $linkstrip;
    print '<table><tr><td>' . $table . '</td></tr></table>';
}
print '</div>';