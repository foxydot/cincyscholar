<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:39 PM
 */
//page content here
print '<div class="wrap report_table">';
print '<h1 class="wp-heading-inline">Fund Settings</h1>';
print ' <a href="admin.php?page=fund-edit" class="page-title-action">Add New Fund</a>
            <hr class="wp-header-end">';
$funds = $this->queries->get_all_funds();
if (count($funds) > 0) {
    $alphas = range('A', 'Z');
    foreach ($alphas AS $a) {
        $links[] = '<a href="#fund-' . $a . '">' . $a . '</a>';
    }
    $linkstrip = implode(' | ', $links);
    foreach ($funds AS $fund) {
        $cell['fund_name'] = '<span id="fund-' . substr($fund->Name, 0, 1) . '"><stong>' . $fund->Name . '</stong></span>';
        $cell['edit'] = '<a href="admin.php?page=fund-edit&fund_id=' . $fund->FundId . '" class="button">Edit Fund</a>';
        $row[] = implode('</td><td>', $cell);
    }
    $table = implode("</td></tr>\n<tr><td>", $row);
    print $linkstrip;
    print '<table><tr><td>' . $table . '</td></tr></table>';
}
print '</div>';