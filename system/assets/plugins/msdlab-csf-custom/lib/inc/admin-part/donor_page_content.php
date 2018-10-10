<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:39 PM
 */
//page content here
print '<div class="wrap report_table">';
print '<h1 class="wp-heading-inline">Donor Management</h1>';
//button: add major
//print ' <a href="admin.php?page=major-edit" class="page-title-action">Add New Major</a>';
print '<hr class="wp-header-end">';
//list majors with edit button, view contacts button
//contacts in a slidedown box?
//$this->controls->print_settings();
$args = array(
    'role'         => 'donor',
    'orderby'      => 'display_name',
    'order'        => 'ASC',
);
$donors = get_users($args);
if (count($donors) > 0) {
    //ts_data($donors);
    $alphas = range('A', 'Z');
    foreach ($alphas AS $a) {
        $links[] = '<a href="#donors-' . $a . '">' . $a . '</a>';
    }
    $linkstrip = implode(' | ', $links);
    foreach ($donors AS $donor) {
        $scholarships = '';
        $cell['display_name'] = '<span id="donors-' . substr($donor->display_name, 0, 1) . '"><stong>' . $donor->display_name . '</stong></span>';
        $cell['email'] = '<a href="'.antispambot($donor->user_email).'">' . antispambot($donor->user_email) . '</a>';
        $cell['scholarships'] = '<ul>'.$scholarships.'</ul>';
        $cell['useredit'] = '<a href="user-edit.php?user_id=' . $donor->ID . '" class="button">Edit User</a>';
        $cell['accessedit'] = '<a href="admin.php?page=donor-edit&user_id=' . $donor->ID . '" class="button">Edit Access</a>';
        $row[] = implode('</td><td>', $cell);
    }
    $table = implode("</td></tr>\n<tr><td>", $row);
    print $linkstrip;
    print '<table><tr><td>' . $table . '</td></tr></table>';
}
print '</div>';