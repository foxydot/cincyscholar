<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 6/30/18
 * Time: 10:39 PM
 */
//page content here
print '<div class="wrap report_table">';
print '<h1 class="wp-heading-inline">College Settings</h1>';
//button: add college
print ' <a href="admin.php?page=college-edit" class="page-title-action">Add New College</a>
           <a href="admin.php?page=contact-edit" class="page-title-action">Add New Contact</a>
            <hr class="wp-header-end">';
//list colleges with edit button, view contacts button
//contacts in a slidedown box?
//$this->controls->print_settings();
$colleges = $this->queries->get_all_colleges();
if (count($colleges) > 0) {
    $alphas = range('A', 'Z');
    foreach ($alphas AS $a) {
        $links[] = '<a href="#colleges-' . $a . '">' . $a . '</a>';
    }
    $linkstrip = implode(' | ', $links);
    foreach ($colleges AS $college) {
        $contacts = $this->queries->get_all_contacts($college->CollegeId);
        $cell['college_name'] = '<span id="colleges-' . substr($college->Name, 0, 1) . '">' . $college->Name . '</span><br /><a href="admin.php?page=college-edit&college_id=' . $college->CollegeId . '" class="button">Edit College</a>';
        $con = array();
        foreach ($contacts AS $contact) {
            $c = array();
            $c['name'] = $contact->FirstName . ' ' . $contact->LastName;
            $c['dept'] = $contact->Department;
            $c['email'] = '<a href="mailto:' . antispambot($contact->Email) . '">' . antispambot($contact->Email) . '</a>';
            $c['phone'] = $contact->PhoneNumber;
            $c['edit'] = '<a href="admin.php?page=contact-edit&contact_id=' . $contact->CollegeContactId . '" class="button">Edit ' . $contact->FirstName . ' ' . $contact->LastName . '</a>';
            $con[] = implode('<br>', $c);
        }
        $cell['contacts'] = implode('<br><br>', $con);
        $row[] = implode('</td><td>', $cell);
    }
    $table = implode("</td></tr>\n<tr><td>", $row);
    print $linkstrip;
    print '<table><tr><td>' . $table . '</td></tr></table>';
}
print '</div>';