<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:39 PM
 */
//page content here
print '<div class="wrap report_table">';
print '<h1 class="wp-heading-inline">Institution Term Type Settings</h1>';
print ' <a href="admin.php?page=institutiontermtype-edit" class="page-title-action">Add New Institution Term Type</a>
            <hr class="wp-header-end">';
$institutiontermtypes = $this->queries->get_all_institutiontermtypes();
if (count($institutiontermtypes) > 0) {
    $alphas = range('A', 'Z');
    foreach ($alphas AS $a) {
        $links[] = '<a href="#institutiontermtypes-' . $a . '">' . $a . '</a>';
    }
    $linkstrip = implode(' | ', $links);
    foreach ($institutiontermtypes AS $institutiontermtype) {
        $cell['institutiontermtype_name'] = '<span id="institutiontermtypes-' . substr($institutiontermtype->InstitutionTermType, 0, 1) . '"><stong>' . $institutiontermtype->InstitutionTermType . '</stong></span>';
        $cell['institutiontermtype_edit'] = '<a href="admin.php?page=institutiontermtype-edit&institutiontermtype_id=' . $institutiontermtype->InstitutionTermTypeId . '" class="button">Edit Institution Term Type</a>';

        $row[] = implode('</td><td>', $cell);
    }
    $table = implode("</td></tr>\n<tr><td>", $row);
    print $linkstrip;
    print '<table><tr><td>' . $table . '</td></tr></table>';
}
print '</div>';