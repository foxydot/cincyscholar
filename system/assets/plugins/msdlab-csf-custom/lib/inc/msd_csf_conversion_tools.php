<?php
/*
 * Some pre-launch tools to bring the WP up to date with preexisiting data.
 * *remove* auto increment from fdn_users table
 * apply conversions
 * *reapply auto increment to fdn_users table
 * remove auto increment from applicant and donor tables
 *
 * if this doesn't work, we will need to create a user_meta on form submission tying the user to the row ID of the form data.
 * Maybe use a cookie for persistance? not as secure. Think about this.
 */
if(!class_exists('MSDLab_CSF_Conversion_Tools')){
    class MSDLab_CSF_Conversion_Tools{
        //properties
        private $queries;
        private $methods = array();
        //constructor
        function __construct(){
            add_action('admin_menu', array(&$this,'settings_page'));

            add_action( 'wp_ajax_create_student_users', array(&$this,'create_student_users') );
            add_action( 'wp_ajax_create_donor_users', array(&$this,'create_donor_users') );
            add_action( 'wp_ajax_copy_application_dates', array(&$this,'copy_application_dates') );
            add_action( 'wp_ajax_move_applicant_majors', array(&$this,'move_applicant_majors') );
            add_action( 'wp_ajax_reduce_majors', array(&$this,'reduce_majors') );
            add_action( 'wp_ajax_fix_emails', array(&$this,'fix_emails') );
            add_action( 'wp_ajax_update_renewal_table', array(&$this,'update_renewal_table') );
            add_action( 'wp_ajax_update_applicant_table', array(&$this,'update_applicant_table') );
            add_action( 'wp_ajax_parse_emails', array(&$this,'parse_duplicate_emails') );
            add_action( 'wp_ajax_move_collegeid', array(&$this,'move_collegeid') );
            add_action( 'wp_ajax_add_renewal_to_attachment_table', array(&$this,'add_renewal_to_attachment_table') );
            add_action( 'wp_ajax_send_renewal_emails', array(&$this,'send_renewal_emails') );
            add_action( 'wp_ajax_fix_up_renewal_attachments', array(&$this,'fix_up_renewal_attachments') );
            add_action( 'wp_ajax_update_unpublishable_tables', array(&$this,'update_unpublishable_tables') );
            add_action( 'wp_ajax_add_need_table', array(&$this,'add_need_table') );
            add_action( 'wp_ajax_add_payment_table', array(&$this,'add_payment_table') );
            add_action( 'wp_ajax_remove_unneccesary_tables', array(&$this,'remove_unneccesary_tables') );
            add_action( 'wp_ajax_add_employer_table', array(&$this,'add_employer_table') );
            add_action( 'wp_ajax_update_applicant_table_again', array(&$this,'update_applicant_table_again') );
            add_action( 'wp_ajax_update_guardian_table', array(&$this,'update_guardian_table') );
            add_action( 'wp_ajax_update_applicantscholarship_table', array(&$this,'update_applicantscholarship_table') );
            add_action( 'wp_ajax_modify_amount_columns', array(&$this,'modify_amount_columns') );
            add_action( 'wp_ajax_repair_renewals_with_no_user_id', array(&$this,'repair_renewals_with_no_user_id') );
            add_action( 'wp_ajax_clean_text_fields', array(&$this,'clean_text_fields') );
            add_action( 'wp_ajax_add_other_school_to_renewals', array(&$this,'add_other_school_to_renewals') );
            add_action( 'wp_ajax_make_scholarships_deleteable', array(&$this,'make_scholarships_deleteable') );
            add_action( 'wp_ajax_recommend', array(&$this,'recommend') );
            add_action( 'wp_ajax_add_contacts_to_scholarships', array(&$this,'add_contacts_to_scholarships') );
            add_action( 'wp_ajax_create_donoruserscholarship', array(&$this,'create_donoruserscholarship') );
            add_action( 'wp_ajax_add_reject_columns', array(&$this,'add_reject_columns') );
            add_action( 'wp_ajax_add_award_id', array(&$this,'add_award_id') );
            add_action( 'wp_ajax_add_academic_year_columns', array(&$this,'add_academic_year_columns') );
            add_action( 'wp_ajax_add_awardid_to_payments', array(&$this,'add_awardid_to_payments') );
            add_action( 'wp_ajax_add_employerid_columns', array(&$this,'add_employerid_columns') );
            add_action( 'wp_ajax_add_calipari_column', array(&$this,'add_calipari_column') );
            add_action( 'wp_ajax_move_temp_payments_to_payments', array(&$this,'move_temp_payments_to_payments') );
            add_action( 'wp_ajax_update_awarded_users', array(&$this,'update_awarded_users') );
            add_action( 'wp_ajax_update_academic_year_columns', array(&$this,'update_academic_year_columns') );
            add_action( 'wp_ajax_create_renewal_users_from_paper_applicant_list', array(&$this,'create_renewal_users_from_paper_applicant_list') );
            add_action( 'wp_ajax_send_renewal_emails_2019', array(&$this,'send_renewal_emails_2019') );


            add_filter('send_password_change_email',array(&$this,'return_false'));
            add_filter('send_email_change_email',array(&$this,'return_false'));

            $this->queries = new MSDLAB_Queries();
        }
        //methods
        function create_student_users(){
            global $wpdb;
            $sql = "SELECT ApplicantId, UserId, FirstName, LastName, Email FROM Applicant";
            $students = $wpdb->get_results($sql);
            //return ts_data($students,0);
            foreach($students AS $student){
                    if($student->UserId > 0){continue;}
                    $args = array(
                        'first_name' => $student->FirstName,
                        'last_name' => $student->LastName,
                        'user_login' => strtolower($student->FirstName . '_' . $student->LastName),
                        'user_email' => $student->Email, //doublecheck that no one is actually going to get emailed.
                        'role' => 'applicant',
                        'user_pass' => 'This is a lousy pa$$word.',
                    );
                $user_id = wp_insert_user($args);
                if(is_wp_error($user_id)){
                    //ts_data( $user_id );
                    continue;
                }
                    $sql = 'UPDATE Applicant SET UserId = '.$user_id.' WHERE ApplicantId = '.$student->ApplicantId.';';
                    if($wpdb->get_results($sql)){
                        print strtolower($student->FirstName . '_' . $student->LastName) .' assigned UserId '. $user_id .'<br>';
                    }
            }
        }


        function create_donor_users(){
            global $wpdb;
            $sql = "SELECT DonorId, UserId, FirstName, LastName, Email FROM Donor";
            $donors = $wpdb->get_results($sql);
            //return ts_data($donors,0);
            foreach($donors AS $donor){
                if($donor->UserId > 0){continue;}
                $args = array(
                    'first_name'    => $donor->FirstName,
                    'last_name'     => $donor->LastName,
                    'user_login'    => strtolower($donor->FirstName.'_'.$donor->LastName),
                    'user_email'    => strtolower($donor->FirstName.'_'.$donor->LastName).'@msdlab.com',
                    'role'          => 'donor',
                    'user_pass' => 'This is a lousy pa$$word.',
                );
                $user_id = wp_insert_user($args);
                if(is_wp_error($user_id)){
                    //ts_data( $user_id );
                    continue;
                }
                $sql = 'UPDATE Donor SET UserId = '.$user_id.' WHERE DonorId = '.$donor->DonorId.';';
                if($wpdb->get_results($sql)){
                    print strtolower($donor->FirstName . '_' . $donor->LastName) .' assigned UserId '. $user_id .'<br>';
                }
            }
        }

        function copy_application_dates(){
            global $wpdb;
            $sql = "SELECT ApplicantId, ApplicationDateTime FROM Applicant";
            $students = $wpdb->get_results($sql);
            //return ts_data($students,0);
            foreach($students AS $student){
                $sql = 'UPDATE Applicant SET SubmitDateTime = '.$student->ApplicationDateTime.' WHERE ApplicantId = '.$student->ApplicantId.';';
                if($wpdb->get_results($sql)){
                    print $student->ApplcantId .' updated<br>';
                }
            }
        }

        function get_major_array(){
            global $wpdb;
            $sql = "SELECT MajorId, MoveToId FROM temp_majors";
            $majors_results = $wpdb->get_results($sql);
            $majors = array();
            foreach($majors_results AS $mj){
                $majors[$mj->MajorId] = $mj->MoveToId;
            }
            return $majors;
        }

        function move_applicant_majors(){
            global $wpdb;
            $majors = $this->get_major_array();
            $sql = "SELECT ApplicantId, MajorId FROM Applicant";
            $students = $wpdb->get_results($sql);
            //return ts_data($students,0);
            foreach($students AS $student){
                //print $student->MajorId;
                if($student->MajorId != ''){
                    if($majors[$student->MajorId] != ''){
                        $newMajorId = $majors[$student->MajorId];
                        $update_sql = 'UPDATE Applicant SET MajorId = '.$newMajorId.' WHERE ApplicantId = '.$student->ApplicantId.';';
                        if($wpdb->get_results($update_sql)){
                            print $student->ApplcantId .' updated<br>';
                        }
                    }
                }
            }
        }

        function reduce_majors(){
            global $wpdb;
            $majors = $this->get_major_array();
            foreach ($majors AS $k => $v){
                if(!is_null($v)){
                    if($k!=$v){
                        $sql = "DELETE FROM major WHERE MajorId = ".$k.";";
                        if($wpdb->get_results($sql)){
                            print $k .' deleted<br>';
                        }
                    }
                }
            }
        }

        function fix_emails(){
            global $wpdb;
            $sql = "SELECT ApplicantId, Email, UserId FROM Applicant";
            $students = $wpdb->get_results($sql);
            foreach ($students AS $student){
                $user = get_user_by('id',$student->UserId);
                if($student->Email != $user->user_email){
                    $update_sql = 'UPDATE Applicant SET Email = '.$user->user_email.' WHERE ApplicantId = '.$student->ApplicantId.';';
                }
                if($wpdb->get_results($update_sql)){
                    print $student->Email .' updated to '. $user->user_email .'<br>';
                }
            }
        }

        function update_renewal_table(){
            global $wpdb;
            $sql = "ALTER TABLE renewal ADD `UserId` bigint(20) unsigned NOT NULL,
  ADD `FirstName` varchar(50) NOT NULL,
  ADD `MiddleInitial` varchar(1) NOT NULL,
  ADD `LastName` varchar(50) NOT NULL,
  ADD `Address1` varchar(254) NOT NULL,
  ADD `Address2` varchar(254) NOT NULL,
  ADD `City` varchar(50) NOT NULL,
  ADD `StateId` char(2) DEFAULT NULL,
  ADD `ZipCode` varchar(10) NOT NULL,
  ADD `CountyId` int(11) DEFAULT NULL,
  ADD `CellPhone` varchar(25) NOT NULL,
  ADD `AlternativePhone` varchar(25) NOT NULL,
  ADD `Email` varchar(50) NOT NULL,
  ADD `Last4SSN` varchar(4) NOT NULL,
  ADD `StudentId` varchar(50) NOT NULL,
  ADD `DateOfBirth` date NOT NULL,
  ADD `CollegeId` int(11) NOT NULL,
  ADD `MajorId` int(11) DEFAULT NULL,
  ADD `TermsAcknowledged` tinyint(1) unsigned zerofill NOT NULL,
  ADD `RenewalLocked` tinyint(1) unsigned zerofill NOT NULL,
  ADD `Notes` text,
  DROP PermanentAddress;";
            if($wpdb->query($sql)) {
                print "renewal table updated!";
            }
        }

        function update_applicant_table(){
            global $wpdb;
            $sql = "ALTER TABLE applicant
  ADD `StudentId` varchar(50) NOT NULL,
  ADD `CollegeId` int(11) NOT NULL,
  ADD `ResumeOK` tinyint(1) unsigned zerofill NOT NULL,
  ADD `TranscriptOK` tinyint(1) unsigned zerofill NOT NULL,
  ADD `FinancialAidOK` tinyint(1) unsigned zerofill NOT NULL,
  ADD `FAFSAOK` tinyint(1) unsigned zerofill NOT NULL,
  ADD `ApplicationlLocked` tinyint(1) unsigned zerofill NOT NULL;";
  //ADD CONSTRAINT `FK_Applicant_College` FOREIGN KEY (`CollegeId`) REFERENCES `college` (`CollegeId`)*/;";
            if($wpdb->query($sql)) {
                print "applicant table updated!";
            }
        }

        function return_false(){
            return false;
        }

        function parse_emails(){
            global $wpdb;
            $sql = "SELECT * FROM temp_emails";

            $students = $wpdb->get_results($sql);
            add_filter('send_password_change_email',array(&$this,'return_false'));
            add_filter('send_email_change_email',array(&$this,'return_false'));
            //return ts_data($students,0);
            foreach($students AS $student){
                $user = get_user_by('email',$student->email);
                if(!$user) {
                    $sql = 'SELECT UserId FROM applicant WHERE LastName = "'.$student->LastName.'" AND DateOfBirth = "'.$student->DOB.'";';
                    if($res = $wpdb->get_results($sql)){
                        $user = get_user_by('ID',$res[0]->UserId);
                    }
                    if(!$user){
                        $user = get_user_by('login',sanitize_title_with_dashes(strtolower($student->FirstName . '_' . $student->LastName)));
                    }
                }
                if($user){
                    $user_id = $user->ID;
                    $sql = 'UPDATE temp_emails SET user_id = '.$user->ID.', permissions = "'.implode(',',$user->roles).'" WHERE id = "'.$student->id.'";';
                    if($wpdb->get_results($sql)){
                        print $user->display_name .' <br>';
                    }
                    if($student->email != $user->user_email){
                        wp_update_user(array('ID' => $user->ID,'user_email' => $student->email, 'role' => 'awardee'));
                    } else {
                        wp_update_user(array('ID' => $user->ID, 'role' => 'awardee'));

                    }
                } else { //there is still not a user! Create One.
                    $pwd = $this->random_str();
                    $args = array(
                        'first_name' => $student->FirstName,
                        'last_name' => $student->LastName,
                        'user_login' => sanitize_title_with_dashes(strtolower($student->FirstName . '_' . $student->LastName)),
                        'user_email' => $student->email, //doublecheck that no one is actually going to get emailed.
                        'role' => 'awardee',
                        'user_pass' => $pwd,
                    );
                    $user_id = wp_insert_user($args);
                    if(is_wp_error($user_id)){
                        ts_data($user_id);
                        continue;
                    }
                    $sql = 'UPDATE temp_emails SET user_id = '.$user_id.', TempPwd = "'.$pwd.'" WHERE id = "'.$student->id.'";';
                    if($wpdb->query($sql)){
                        print $user->display_name .' <br>';
                    }
                }
                //attach to an application. if there is no application, create one.
                $applicant = $this->queries->get_applicant_id($user_id);
                if(!$applicant){
                    $sql = 'INSERT INTO applicant SET applicant.ApplicationDateTime = "2017-04-16 21:32:33", applicant.UserId = "'.$user_id.'", applicant.Email = "'.$student->email.'", applicant.FirstName = "'.$student->FirstName.'", applicant.MiddleInitial = "", applicant.LastName = "'.$student->LastName.'", applicant.Last4SSN = "0000", applicant.DateOfBirth = "'.$student->DOB.'", applicant.Address1 = "Unknown", applicant.Address2 = "", applicant.City = "Unknown", applicant.StateId = "OH", applicant.CountyId = "24", applicant.ZipCode = "00000", applicant.CellPhone = "unknown", applicant.AlternativePhone = "", applicant.EthnicityId = "24", applicant.StudentId = "'.$student->StudentId.'";';
                    $wpdb->query($sql);
                    $applicant_id = $wpdb->insert_id;
                    $sql = 'SELECT * FROM applicantcollege WHERE applicantcollege.ApplicantId = "'.$applicant_id.'";';
                    $test = $wpdb->get_results($sql);
                    if(count($test) == 0){
                        $sql = 'INSERT INTO applicantcollege SET applicantcollege.ApplicantId = "'.$applicant_id.'", applicantcollege.CollegeId = "343";';
                        $wpdb->query($sql);
                    }
                }
            }
        }


        function parse_duplicate_emails(){
            global $wpdb;
            //$sql = "SELECT * FROM temp_emails WHERE `id` IN (54,55,62,63,64,76,77,128,211,242,243,303,304,352,353,365,366,513,514,544,545,748);";
            $sql = "SELECT * FROM temp_emails WHERE `id` IN (242,243);";

            $students = $wpdb->get_results($sql);
            add_filter('send_password_change_email',array(&$this,'return_false'));
            add_filter('send_email_change_email',array(&$this,'return_false'));
            //return ts_data($students,0);
            foreach($students AS $student){
                $user = get_user_by('ID',$student->user_id);
                if($user){
                    //$user_id = $user->ID;
                    //$sql = 'UPDATE temp_emails SET user_id = '.$user->ID.', permissions = "'.implode(',',$user->roles).'" WHERE id = "'.$student->id.'";';
                    /*if($wpdb->get_results($sql)){
                        print $user->display_name .' <br>';
                    }*/
                    //if($student->email != $user->user_email){
                      //  wp_update_user(array('ID' => $user->ID,'user_email' => $student->email, 'role' => 'awardee'));
                    //} else {
                        wp_update_user(array('ID' => $user->ID, 'role' => 'awardee'));

                    //}
                } else { //there is still not a user! Create One.
                    $pwd = $this->random_str();
                    $args = array(
                        'first_name' => $student->FirstName,
                        'last_name' => $student->LastName,
                        'user_login' => sanitize_title_with_dashes(strtolower($student->FirstName . '_' . $student->LastName)),
                        'user_email' => $student->email, //doublecheck that no one is actually going to get emailed.
                        'role' => 'awardee',
                        'user_pass' => $pwd,
                    );
                    $user_id = wp_insert_user($args);
                    if(is_wp_error($user_id)){
                        ts_data($args);
                        ts_data($user_id);
                        continue;
                    }
                    $sql = 'UPDATE temp_emails SET user_id = '.$user_id.', TempPwd = "'.$pwd.'" WHERE id = "'.$student->id.'";';
                    if($wpdb->query($sql)){
                        print $user->display_name .' <br>';
                    }
                }
                //attach to an application. if there is no application, create one.
                $applicant = $this->queries->get_applicant_id($user_id);
                if(!$applicant){
                    $sql = 'INSERT INTO applicant SET applicant.ApplicationDateTime = "2017-04-16 21:32:33", applicant.UserId = "'.$user_id.'", applicant.Email = "'.$student->email.'", applicant.FirstName = "'.$student->FirstName.'", applicant.MiddleInitial = "", applicant.LastName = "'.$student->LastName.'", applicant.Last4SSN = "0000", applicant.DateOfBirth = "'.$student->DOB.'", applicant.Address1 = "Unknown", applicant.Address2 = "", applicant.City = "Unknown", applicant.StateId = "OH", applicant.CountyId = "24", applicant.ZipCode = "00000", applicant.CellPhone = "unknown", applicant.AlternativePhone = "", applicant.EthnicityId = "24", applicant.StudentId = "'.$student->StudentId.'", applicant.CollegeId = "343";';
                    $wpdb->query($sql);
                    $applicant_id = $wpdb->insert_id;
                    $sql = 'SELECT * FROM applicantcollege WHERE applicantcollege.ApplicantId = "'.$applicant_id.'";';
                    $test = $wpdb->get_results($sql);
                    if(count($test) == 0){
                        $sql = 'INSERT INTO applicantcollege SET applicantcollege.ApplicantId = "'.$applicant_id.'", applicantcollege.CollegeId = "343";';
                        $wpdb->query($sql);
                    }
                }
            }
        }

        function move_collegeid(){
            global $wpdb;
            $sql = "SELECT * FROM applicantcollege";
            $results = $wpdb->get_results($sql);
            $ac = array();
            foreach($results AS $r){
                $ac[$r->ApplicantId] = $r->CollegeId;
            }
            foreach($ac AS $k => $v){
                $sql = 'UPDATE applicant SET CollegeId = '.$v.' WHERE ApplicantId = '.$k.';';
                $wpdb->query($sql);
                print $k .' college id copied.<br>';
            }
        }


        function add_renewal_to_attachment_table(){
            global $wpdb;
            $sql = "ALTER TABLE attachment
            ADD `RenewalId` int(11) NULL AFTER `ApplicantId`;";
            if($wpdb->query($sql)) {
                print "attachment table updated!";
            }
        }

        function send_renewal_emails(){
            global $wpdb;
            $subject = 'An account has been prepared for you on CincinnatiScholarshipFooundation.org';
            $headers[] = 'From: Elizabeth Collins <beth@cincinnatischolarshipfoundation.org>';
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            $headers[] = 'Bcc: beth@cincinnatischolarshipfoundation.org';

            $email_str = '
            <p>An account has been created for you. Please surf to <a href = "http://cincinnatischolarshipfoundation.org">http://cincinnatischolarshipfoundation.org</a>, click the Login/Register button, and login with the following information:</p>
 <p>
email: [[email]]<br/>
password: [[TempPwd]]
 </p><p>
Immediately upon logging in, you may be prompted to change your password. Please choose a secure password you will remember. Once you have changed your password, you will be redirected to the renewal form.
 </p><p>
If your scholarship is need-based, you will be required to submit your '.date("Y").'-'.date("Y",strtotime('+ 1 year')).' student aid report (SAR), financial aid award notification, and grade report to complete your renewal application. 
</p><p>
Please submit your renewal application by June 20, '.date("Y").' to be considered.
  </p><p>
Elizabeth Collins<br/>
Program Administrator
</p><p>
602 Main St., Suite 1000<br/>
Cincinnati OH  45202
 </p><p>
Ph:  (513)345-6701<br/>
Fax:  (513)345-6705
 </p><p>
beth@cincinnatischolarshipfoundation.org<br/>
<a href = "http://cincinnatischolarshipfoundation.org">www.cincinnatischolarshipfoundation.org</a>
</p>
';
            $sql = "SELECT `Email`,`FirstName`,`LastName`,`TempPwd` FROM z_paper_applicant_list;";
            $results = $wpdb->get_results($sql);
            foreach ($results AS $r){
                $to = $r->FirstName.' '.$r->LastName.' <'.$r->Email.'>';

                $temppwd = is_null($r->TempPwd)?'Please use the "forgot password" system to retrieve your password.':$r->TempPwd;
                $pattern = array('/\[\[email\]\]/','/\[\[TempPwd\]\]/');
                $replacement = array($r->Email,$temppwd);
                $message = preg_replace($pattern,$replacement,$email_str);


                //send the email
                if(wp_mail($to, $subject, $message, $headers)){
                    print $r->FirstName.' '.$r->LastName.', '.$r->email.'<br />';
                }
            }
        }

        function send_renewal_emails_2019(){
            global $wpdb;
            $subject = 'It is time to renew your scholarship at CincinnatiScholarshipFooundation.org';            $headers[] = 'From: Elizabeth Collins <beth@cincinnatischolarshipfoundation.org>';
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            $headers[] = 'Bcc: beth@cincinnatischolarshipfoundation.org';

            $email_str = '
            <p>Please surf to <a href = "http://cincinnatischolarshipfoundation.org">http://cincinnatischolarshipfoundation.org</a>, click the Login/Register button, and login with the same account information as last year.</p>
            <p>If you cannot remember your password, please use the "forgot password" feature to recover it.</p>
<p>
Immediately upon logging in, you will be redirected to the renewal form.
 </p><p>
If your scholarship is need-based, you will be required to submit your '.date("Y").'-'.date("Y",strtotime('+ 1 year')).' student aid report (SAR), financial aid award notification, and grade report to complete your renewal application. 
</p><p>
Please submit your renewal application by June 20, '.date("Y").' to be considered.
  </p><p>
Elizabeth Collins<br/>
Program Administrator
</p><p>
602 Main St., Suite 1000<br/>
Cincinnati OH  45202
 </p><p>
Ph:  (513)345-6701<br/>
Fax:  (513)345-6705
 </p><p>
beth@cincinnatischolarshipfoundation.org<br/>
<a href = "http://cincinnatischolarshipfoundation.org">www.cincinnatischolarshipfoundation.org</a>
</p>
';

            $sql = "SELECT `Email`,`FirstName`,`LastName`,`TempPwd` FROM z_paper_applicant_list;";
            $results = $wpdb->get_results($sql);
            foreach ($results AS $r){
                $to = $r->FirstName.' '.$r->LastName.' <'.$r->Email.'>';

                $temppwd = is_null($r->TempPwd)?'Please use the "forgot password" system to retrieve your password.':$r->TempPwd;
                $pattern = array('/\[\[email\]\]/','/\[\[TempPwd\]\]/');
                $replacement = array($r->Email,$temppwd);
                $message = preg_replace($pattern,$replacement,$email_str);


                //send the email
                if(wp_mail($to, $subject, $message, $headers)){
                    print $r->FirstName.' '.$r->LastName.', '.$r->email.'<br />';
                }
            }
        }

        function fix_up_renewal_attachments(){
            global $wpdb;
            $sql = "SELECT b.LastName, b.RenewalId AS RenewalId2, a.* FROM attachment AS a,renewal AS b WHERE a.ApplicantId = b.ApplicantId AND (a.RenewalId = 0 OR a.RenewalId IS NULL) AND b.ApplicantId IN (SELECT c.ApplicantId FROM renewal AS c);";
            $students = $wpdb->get_results($sql);
            foreach ($students AS $student){
                $update_sql = 'UPDATE attachment SET RenewalId = '.$student->RenewalId2.' WHERE ApplicantId = '.$student->ApplicantId.' AND FilePath = "'.$student->FilePath.'";';
                //print $update_sql;
                if($wpdb->query($update_sql)){
                    print $student->LastName .' updated<br>';
                }
            }
        }

        function update_unpublishable_tables(){
            global $wpdb;
            $tables = array('college','collegecontact','highschool','major');
            foreach ($tables AS $t) {
                $sql = "ALTER TABLE $t ADD `Publish` tinyint(1) unsigned zerofill NOT NULL;";
                if ($wpdb->query($sql)) {
                    print $t." table updated!";
                }
                $sql = "UPDATE $t SET `Publish` = 1 WHERE `Publish` != 1";
                if ($wpdb->query($sql)) {
                    print $t." data updated!";
                }
            }
        }

        function add_need_table(){
            global $wpdb;
            $sql = "CREATE TABLE `studentneed` (
  `needid` int(11) NOT NULL AUTO_INCREMENT,
  `ApplicantId` int(11) NOT NULL,
  `RenewalId` int(11) DEFAULT NULL,
  `UserId` bigint(20) unsigned NOT NULL,
  `CalculationDateTime` datetime(3) NOT NULL,
  `DirectCost` bigint(20) unsigned NOT NULL,
  `IndirectCost` bigint(20) unsigned NOT NULL,
  `FamilyContribution` bigint(20) unsigned NOT NULL,
  `Pell` bigint(20) unsigned NOT NULL,
  `SEOG` bigint(20) unsigned NOT NULL,
  `OIG` bigint(20) unsigned NOT NULL,
  `OSCG` bigint(20) unsigned NOT NULL,
  `Stafford` bigint(20) unsigned NOT NULL,
  `ExternalScholarship1` varchar(255) NULL,
  `ExternalScholarship2` varchar(255) NULL,
  `ExternalScholarship3` varchar(255) NULL,
  `ExternalScholarship4` varchar(255) NULL,
  `ExternalScholarship5` varchar(255) NULL,
  `ExternalScholarship6` varchar(255) NULL,
  `ExternalScholarshipAmt1` bigint(20) unsigned NOT NULL,
  `ExternalScholarshipAmt2` bigint(20) unsigned NOT NULL,
  `ExternalScholarshipAmt3` bigint(20) unsigned NOT NULL,
  `ExternalScholarshipAmt4` bigint(20) unsigned NOT NULL,
  `ExternalScholarshipAmt5` bigint(20) unsigned NOT NULL,
  `ExternalScholarshipAmt6` bigint(20) unsigned NOT NULL,
  `DirectNeed` bigint(20) unsigned NOT NULL,
  `IndirectNeed` bigint(20) unsigned NOT NULL,
  `Notes` text,
  `NeedLocked` tinyint(1) unsigned zerofill NOT NULL,
  PRIMARY KEY (`needid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            if ($wpdb->query($sql)) {
                print "table created!";
            }
        }


        function add_payment_table(){
            global $wpdb;
            $sql = "CREATE TABLE `payment` (
  `paymentid` int(11) NOT NULL AUTO_INCREMENT,
  `paymentkey` varchar(120) NULL,
  `UserId` bigint(20) unsigned NOT NULL,
  `ApplicantId` bigint(20) unsigned NOT NULL,
  `PaymentAmt` bigint(20) unsigned NOT NULL,
  `PaymentDateTime` datetime(3) NOT NULL,
  `CheckNumber` varchar(120) NULL,
  `CollegeId` int(11) NOT NULL,
  `RefundRequested` bigint(20) unsigned NOT NULL,
  `RefundReceived` bigint(20) unsigned NOT NULL,
  `RefundAmt` bigint(20) unsigned NOT NULL,
  `RefundNumber` varchar(120) NULL,
  `Notes` text,
  `PaymentLocked` tinyint(1) unsigned zerofill NOT NULL,
  PRIMARY KEY (`paymentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            if ($wpdb->query($sql)) {
                print "table created!";
            }
        }

        function remove_unneccesary_tables(){
            global $wpdb;
            $tables = array('scholarshiprenewal','renewalmajor','externalscholarship','reminder','scholarshiprequirement','scholarshipscholarshiprequirement','temp_majors');
            foreach($tables AS $table){
                $sql = "DROP TABLE $table;";
                if ($wpdb->query($sql)) {
                    print $table . ' dropped.' . "\n";
                }
            }
        }

        function add_employer_table(){
            global $wpdb;
            $sql = "CREATE TABLE `employer` (
  `employerid` int(11) NOT NULL AUTO_INCREMENT,
  `employername` varchar(120) NULL,
  `Notes` text,
  PRIMARY KEY (`employerid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            if ($wpdb->query($sql)) {
                print "employer table created!";
            }
            $employers = array(
                'Procter and Gamble',
                'Kroger',
                'Cincinnati Bell',
                'Macy’s',
                'Cincinnati Financial',
                'Duke',
                'Fifth Third',
                'GE Aircrafts',
                'Ohio National',
                'PNC Bank',
                'Scripps',
                'US Bank',
                'Ameritas',
                'Western & Southern',
                'Milacron',
                'Heidelberg',
                'UDF',
                'American Financial',
                'Other'
            );
            foreach($employers AS $employer){
                $sql = 'INSERT INTO employer SET employer.employername = "'.$employer.'";';
                $wpdb->query($sql);
            }
        }


        function update_applicant_table_again(){
            global $wpdb;
            $sql = "ALTER TABLE applicant
  ADD `AppliedBefore` tinyint(1) unsigned zerofill NOT NULL;";
            if($wpdb->query($sql)) {
                print "applicant table updated again!";
            }
        }
        function update_guardian_table(){
            global $wpdb;
            $sql = "ALTER TABLE guardian
  ADD `Guardian1EmployerId` int(11) NOT NULL,
  ADD `Guardian1Alive` tinyint(1) unsigned zerofill NOT NULL,
  ADD `Guardian2EmployerId` int(11) NOT NULL,
  ADD `Guardian2Alive` tinyint(1) unsigned zerofill NOT NULL;";
            if($wpdb->query($sql)) {
                print "guardian table updated!";
            }
        }
        function update_applicantscholarship_table(){
            global $wpdb;
            $sql = "ALTER TABLE applicantscholarship
  ADD `Renew` tinyint(1) unsigned zerofill NOT NULL,
  ADD `ThankYou` tinyint(1) unsigned zerofill NOT NULL,
  ADD `Signed` tinyint(1) unsigned zerofill NOT NULL,
  ADD `GPA1` decimal(4,3) NOT NULL,
  ADD `GPA2` decimal(4,3) NOT NULL,
  ADD `GPA3` decimal(4,3) NOT NULL,
  ADD `GPAC` decimal(4,3) NOT NULL,
  ADD `Notes` text;";
            if($wpdb->query($sql)) {
                print "applicantscholarship table updated!";
            }
        }

        function modify_amount_columns(){
            global $wpdb;
            $sql = array();
            $sql['applicantscholarship'] = "ALTER TABLE applicantscholarship
 MODIFY COLUMN AmountAwarded decimal(15,2) NOT NULL,
 MODIFY COLUMN AmountActuallyAwarded decimal(15,2) DEFAULT NULL;";
            $sql['payment'] = "ALTER TABLE payment
 MODIFY COLUMN PaymentAmt decimal(15,2) DEFAULT NULL,
 MODIFY COLUMN RefundAmt decimal(15,2) DEFAULT NULL,
 MODIFY COLUMN RefundRequested datetime(3) DEFAULT NULL,
 MODIFY COLUMN RefundReceived datetime(3) DEFAULT NULL;";
            $sql['studentneed'] = "ALTER TABLE studentneed
 MODIFY COLUMN DirectCost decimal(15,2) NOT NULL,
 MODIFY COLUMN IndirectCost decimal(15,2) NOT NULL,
 MODIFY COLUMN FamilyContribution decimal(15,2) DEFAULT NULL,
 MODIFY COLUMN Pell decimal(15,2) DEFAULT NULL,
 MODIFY COLUMN SEOG decimal(15,2) DEFAULT NULL,
 MODIFY COLUMN OIG decimal(15,2) DEFAULT NULL,
 MODIFY COLUMN OSCG decimal(15,2) DEFAULT NULL,
 MODIFY COLUMN Stafford decimal(15,2) DEFAULT NULL,
 MODIFY COLUMN ExternalScholarshipAmt1 decimal(15,2) DEFAULT NULL,
 MODIFY COLUMN ExternalScholarshipAmt2 decimal(15,2) DEFAULT NULL,
 MODIFY COLUMN ExternalScholarshipAmt3 decimal(15,2) DEFAULT NULL,
 MODIFY COLUMN ExternalScholarshipAmt4 decimal(15,2) DEFAULT NULL,
 MODIFY COLUMN ExternalScholarshipAmt5 decimal(15,2) DEFAULT NULL,
 MODIFY COLUMN ExternalScholarshipAmt6 decimal(15,2) DEFAULT NULL,
 MODIFY COLUMN DirectNeed decimal(15,2) NOT NULL,
 MODIFY COLUMN IndirectNeed decimal(15,2) NOT NULL;";
            foreach($sql AS $k=>$s){
                if($wpdb->query($s)) {
                    print $k." table updated!<br>";
                }
            }
        }

        function repair_renewals_with_no_user_id(){
            global $wpdb;
            $sql = 'SELECT a.UserId AS user_id, a.ApplicantId AS applicant_id, a.FirstName, a.LastName, r.UserId, r.ApplicantId, r.FirstName, r.LastName, r.RenewalId FROM applicant AS a, renewal AS r WHERE a.ApplicantId=r.ApplicantId AND r.UserId = 0;';
            $results = $wpdb->get_results($sql);
            foreach ($results AS $r){
                $sql = 'UPDATE renewal SET UserId = '.$r->user_id.' WHERE ApplicantId = '.$r->applicant_id.';';
                if($wpdb->query($sql)){
                    print $r->FirstName .' '. $r->LastName.' updated. <br>';
                }
            }
        }

        function clean_text_fields(){
            global $wpdb;
            $sql = 'SELECT a.ApplicantId, a.FirstName, a.LastName, a.HardshipNote, a.Activities FROM applicant a;';
            $results = $wpdb->get_results($sql);
            foreach ($results AS $r){
                $hardship = $this->strip_ms_word_crud($r->HardshipNote);
                $activities = $this->strip_ms_word_crud($r->Activities);
                $data = array(
                    'HardshipNote' => $hardship,
                    'Activities' => $activities,
                );
                if($wpdb->update('applicant', $data, array('ApplicantId' => $r->ApplicantId), array('%s','%s'), array( '%d' ) )){
                    print $r->ApplicantId.' updated. <br>';
                }
            }
        }

        function strip_ms_word_crud($str){
            $clean = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', strip_tags($str,'<p><br>'));
            return $clean;
        }

        function add_other_school_to_renewals(){
            global $wpdb;
            $sql = "ALTER TABLE renewal
  ADD `OtherSchool` varchar(255) NULL;";
            if($wpdb->query($sql)) {
                print "renewal table updated!";
            }
        }

        function make_scholarships_deleteable(){
            global $wpdb;
            $tables = array('scholarship');
            foreach ($tables AS $t) {
                $sql = "ALTER TABLE $t ADD `Publish` tinyint(1) unsigned zerofill NOT NULL;";
                if ($wpdb->query($sql)) {
                    print $t." table updated!";
                }
                $sql = "UPDATE $t SET `Publish` = 1 WHERE `Publish` != 1";
                if ($wpdb->query($sql)) {
                    print $t." data updated!";
                }
            }
        }

        function recommend(){
            global $wpdb;
            $sql = "CREATE TABLE `recommend` (
  `RecommendationId` int(11) NOT NULL AUTO_INCREMENT,
  `UserId` int(11) NOT NULL,
  `ApplicantId` int(11) NOT NULL,
  `ScholarshipId` int(11) NOT NULL,
  `RecommendationTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Notes` text,
  PRIMARY KEY (`RecommendationId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            if ($wpdb->query($sql)) {
                print "recommend table created!";
            }
        }

        function add_contacts_to_scholarships(){
            global $wpdb;
            $tables = array('scholarship');
            foreach ($tables AS $t) {
                $sql = "ALTER TABLE $t ADD `Contacts` text;";
                if ($wpdb->query($sql)) {
                    print $t." table updated!";
                }
            }
        }


        function create_donoruserscholarship(){
            global $wpdb;
            $sql = "CREATE TABLE `donoruserscholarship` (
  `DUSId` int(11) NOT NULL AUTO_INCREMENT,
  `UserId` int(11) NOT NULL,
  `ScholarshipId` int(11) NOT NULL,
  `Notes` text,
  PRIMARY KEY (`DUSId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            if ($wpdb->query($sql)) {
                print "donoruserscholarship table created!";
            }
        }

        function add_reject_columns(){
            global $wpdb;
            $sql = "ALTER TABLE renewal 
  ADD `Reject` tinyint(1) unsigned zerofill NOT NULL;";
            if($wpdb->query($sql)) {
                print "renewal table updated!";
            }
            $sql = "ALTER TABLE applicant
    ADD `Reject` tinyint(1) unsigned zerofill NOT NULL;";
            if($wpdb->query($sql)) {
                print "applicant table updated!";
            }
        }

        function add_award_id(){
            global $wpdb;
            $sql = "ALTER TABLE applicantscholarship 
  ADD `AwardId` int(11) NOT NULL AUTO_INCREMENT,
  ADD KEY (`AwardId`);";
            if($wpdb->query($sql)) {
                print "AwardId column added!";
            }
        }

        function add_academic_year_columns(){
            global $wpdb;
            //table to add academic year to
            $tables = array('applicant','renewal','payment','applicantscholarship');
            foreach ($tables as $table){
                $sql = "ALTER TABLE $table 
  ADD COLUMN `AcademicYear` smallint(4) unsigned zerofill NOT NULL;";
                if($wpdb->query($sql)) {
                    print "Academic column added to $table!";
                }
            }
            $sql = "UPDATE applicant SET AcademicYear = YEAR(ApplicationDateTime);";
            if($wpdb->query($sql)) {
                print "Applicant Updated";
            }
            $sql = "UPDATE renewal SET AcademicYear = YEAR(RenewalDateTime);";
            if($wpdb->query($sql)) {
                print "Renewal Updated";
            }
            $sql = "UPDATE payment SET AcademicYear = YEAR(PaymentDateTime);";
            if($wpdb->query($sql)) {
                print "Payment Updated";
            }
            $sql = "UPDATE applicantscholarship SET AcademicYear = YEAR(DateAwarded);";
            if($wpdb->query($sql)) {
                print "ApplicantScholarship Updated";
            }
        }

        function add_awardid_to_payments(){
            global $wpdb;
            $table = 'payment';
            $sql = "ALTER TABLE $table ADD COLUMN `AwardId` int(11) NOT NULL;";
            if($wpdb->query($sql)) {
                print "AwardId column added to $table!";
            }
            $sql = "SELECT DISTINCT a.ApplicantId, b.AwardId FROM payment a, applicantscholarship b WHERE a.ApplicantId = b.ApplicantId;";
            //for each ApplicantId, get the awardId
            $results = $wpdb->get_results($sql);
            foreach ($results AS $r){
                $sql = "UPDATE payment SET AwardId = ".$r->AwardId." WHERE ApplicantId = ".$r->ApplicantId.";";
                if($wpdb->query($sql)) {
                    print "AwardId added to Payments for ".$r->ApplicantId."<br />";
                }
            }
        }

        function add_employerid_columns(){
            global $wpdb;
            $sql = "ALTER TABLE applicant ADD `ApplicantEmployerId` int(11) NULL;";
            if($wpdb->query($sql)) {
                print "EmployerId column added to applicant!";
            }
            $sql = "ALTER TABLE applicantfinancial ADD `ApplicantEmployerId` int(11) NULL, ADD `SpouseEmployerId` int(11) NULL;";
            if($wpdb->query($sql)) {
                print "EmployerId column added to applicantfinancial!";
            }
        }

        function add_calipari_column(){
            global $wpdb;
            $sql = "ALTER TABLE applicant ADD `Calipari` tinyint(1) unsigned zerofill NOT NULL;";
            if($wpdb->query($sql)) {
                print "Calipari column added to applicant!";
            }
        }

        function move_temp_payments_to_payments(){
            global $wpdb;
            $sql = "SELECT * FROM temp_payment";
            $results = $wpdb->get_results($sql);
            foreach ($results AS $r){
                $sql = "INSERT INTO `payment` (`paymentkey`, `UserId`, `ApplicantId`, `PaymentAmt`, `PaymentDateTime`, `CheckNumber`, `CollegeId`, `RefundReceived`, `RefundAmt`, `RefundNumber`, `Notes`, `PaymentLocked`, `AcademicYear`, `AwardId`) 
VALUES
	('$r->paymentkey', '$r->UserId', '$r->ApplicantId', '$r->PaymentAmt', '$r->PaymentDateTime', '$r->CheckNumber', '$r->CollegeId', '$r->RefundReceived', '$r->RefundAmt', '$r->RefundNumber', '$r->Notes', '$r->PaymentLocked', '$r->AcademicYear', '$r->AwardId');";
                if($wpdb->query($sql)) {
                    print "Payment ".$wpdb->insert_id." created from temp_payment ".$r->paymentid."<br />";
                }
            }
        }

        function update_awarded_users(){
            global $wpdb;
            $sql = "SELECT a.UserId FROM applicant AS a, applicantscholarship AS b WHERE a.ApplicantId = b.ApplicantId;";
            $results = $wpdb->get_results($sql);
            foreach ($results AS $r){
                $user = get_user_by('ID',$r->UserId);
                if ( in_array( 'applicant', (array) $user->roles ) ) {
                    //update the user role
                    if(wp_update_user(array('ID' => $user->ID,'role' => 'awardee'))){
                        print "$user->display_name upgraded. <br/>";
                    }
                }
            }
        }


        function update_academic_year_columns(){
            global $wpdb;

            $sql = "UPDATE applicant SET AcademicYear = YEAR(ApplicationDateTime) WHERE AcademicYear = 0000;";
            if($wpdb->query($sql)) {
                print "Applicant Updated";
            }
            $sql = "UPDATE renewal SET AcademicYear = YEAR(RenewalDateTime) WHERE AcademicYear = 0000;";
            if($wpdb->query($sql)) {
                print "Renewal Updated";
            }
            $sql = "UPDATE payment SET AcademicYear = YEAR(PaymentDateTime) WHERE AcademicYear = 0000;";
            if($wpdb->query($sql)) {
                print "Payment Updated";
            }
            $sql = "UPDATE applicantscholarship SET AcademicYear = YEAR(DateAwarded) WHERE AcademicYear = 0000;";
            if($wpdb->query($sql)) {
                print "ApplicantScholarship Updated";
            }
        }


        function create_renewal_users_from_paper_applicant_list(){
            /**
             * NOTE: The paper applicant list comes from
             *
             * Create a temp DB for Beth's list
             * CREATE TABLE `z_renewals1819` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `LastName` varchar(255) DEFAULT NULL,
            `FirstName` varchar(255) DEFAULT NULL,
            `Email` varchar(255) DEFAULT NULL,
            `UserId` int(11) DEFAULT NULL,
            `ApplicantId` int(11) DEFAULT NULL,
            `RenewalId` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=764 DEFAULT CHARSET=utf8;
             *
             * Now import her CSV and then run:
             *
            UPDATE z_renewals1819 SET UserId = (SELECT UserId FROM applicant WHERE applicant.Email = z_renewals1819.Email LIMIT 1);
            UPDATE z_renewals1819 SET ApplicantId = (SELECT ApplicantId FROM applicant WHERE applicant.Email = z_renewals1819.Email LIMIT 1);

            UPDATE z_renewals1819 SET UserId = (SELECT UserId FROM renewal WHERE renewal.Email = z_renewals1819.Email LIMIT 1) WHERE UserId IS NULL;
            UPDATE z_renewals1819 SET ApplicantId = (SELECT ApplicantId FROM renewal WHERE renewal.Email = z_renewals1819.Email LIMIT 1) WHERE ApplicantId IS NULL;
            UPDATE z_renewals1819 SET RenewalId = (SELECT RenewalId FROM renewal WHERE renewal.Email = z_renewals1819.Email LIMIT 1) WHERE RenewalId IS NULL;

            UPDATE z_renewals1819 SET UserId = (SELECT ID FROM fdn_users WHERE fdn_users.user_email = z_renewals1819.Email LIMIT 1) WHERE UserId IS NULL;
            UPDATE z_renewals1819 SET ApplicantId = (SELECT ApplicantId FROM applicant WHERE applicant.UserId = z_renewals1819.UserId LIMIT 1) WHERE ApplicantId IS NULL;

            UPDATE z_renewals1819 SET UserId = (SELECT UserId FROM applicant WHERE applicant.FirstName = z_renewals1819.FirstName AND applicant.LastName = z_renewals1819.LastName LIMIT 1) WHERE UserId IS NULL;
            UPDATE z_renewals1819 SET ApplicantId = (SELECT ApplicantId FROM applicant WHERE applicant.FirstName = z_renewals1819.FirstName AND applicant.LastName = z_renewals1819.LastName LIMIT 1) WHERE ApplicantId IS NULL;
             *
             * This will check for exisiting users/applications
             *
             * Now create z_paper_applicant_list by duplicating the above structure and importing hte result of the following query:
             * SELECT * FROM `z_renewals1819` WHERE UserId IS NULL;
             *
             * THEN run this to create users. After this, re-running the test queries should result in a ZERO result.
             */
            global $wpdb;
            $sql = "SELECT * FROM z_paper_applicant_list";

            $students = $wpdb->get_results($sql);
            add_filter('send_password_change_email',array(&$this,'return_false'));
            add_filter('send_email_change_email',array(&$this,'return_false'));
            //return ts_data($students,0);
            foreach($students AS $student){
                $user = get_user_by('email',$student->Email);
                if(!$user) {
                    $sql = 'SELECT UserId FROM applicant WHERE LastName = "'.$student->LastName.'" AND FirstName = "'.$student->FirstName.'";';
                    if($res = $wpdb->get_results($sql)){
                        $user = get_user_by('ID',$res[0]->UserId);
                    }
                    if(!$user){
                        $user = get_user_by('login',sanitize_title_with_dashes(strtolower($student->FirstName . '_' . $student->LastName)));
                    }
                }
                if($user){
                    $user_id = $user->ID;
                    $sql = 'UPDATE z_paper_applicant_list SET UserId = '.$user->ID.', Permissions = "'.implode(',',$user->roles).'" WHERE id = "'.$student->id.'";';
                    if($wpdb->get_results($sql)){
                        print $user->display_name .' <br>';
                    }
                    if($student->Email != $user->user_email){
                        wp_update_user(array('ID' => $user->ID,'user_email' => $student->Email, 'role' => 'awardee'));
                    } else {
                        wp_update_user(array('ID' => $user->ID, 'role' => 'awardee'));

                    }
                } else { //there is still not a user! Create One.
                    $pwd = $this->random_str();
                    $args = array(
                        'first_name' => $student->FirstName,
                        'last_name' => $student->LastName,
                        'user_login' => sanitize_title_with_dashes(strtolower($student->FirstName . '_' . $student->LastName)),
                        'user_email' => $student->Email, //doublecheck that no one is actually going to get emailed.
                        'role' => 'awardee',
                        'user_pass' => $pwd,
                    );
                    $user_id = wp_insert_user($args);
                    if(is_wp_error($user_id)){
                        ts_data($user_id);
                        continue;
                    }
                    $sql = 'UPDATE z_paper_applicant_list SET UserId = '.$user_id.'" WHERE id = "'.$student->id.'";';
                    if($wpdb->query($sql)){
                        print $user->display_name .':';
                    }
                }
                //attach to an application. if there is no application, create one.
                $applicant = $this->queries->get_applicant_id($user_id);
                if(!$applicant){
                    $sql = 'INSERT INTO applicant SET applicant.ApplicationDateTime = "2017-04-16 21:32:33", applicant.UserId = "'.$user_id.'", applicant.Email = "'.$student->Email.'", applicant.FirstName = "'.$student->FirstName.'", applicant.MiddleInitial = "", applicant.LastName = "'.$student->LastName.'", applicant.Last4SSN = "0000", applicant.DateOfBirth = "'.$student->DOB.'", applicant.Address1 = "Unknown", applicant.Address2 = "", applicant.City = "Unknown", applicant.StateId = "OH", applicant.CountyId = "24", applicant.ZipCode = "00000", applicant.CellPhone = "unknown", applicant.AlternativePhone = "", applicant.EthnicityId = "24", applicant.StudentId = "'.$student->StudentId.'";';
                    $wpdb->query($sql);
                    $applicant_id = $wpdb->insert_id;
                    $sql = 'UPDATE z_paper_applicant_list SET ApplicantId = "'.$applicant_id.'" WHERE id = "'.$student->id.'";';
                    if($wpdb->query($sql)){
                        print ' Application '.$applicant_id.' created <br>';
                    }
                    $sql = 'SELECT * FROM applicantcollege WHERE applicantcollege.ApplicantId = "'.$applicant_id.'";';
                    $test = $wpdb->get_results($sql);
                    if(count($test) == 0){
                        $sql = 'INSERT INTO applicantcollege SET applicantcollege.ApplicantId = "'.$applicant_id.'", applicantcollege.CollegeId = "343";';
                        $wpdb->query($sql);
                    }
                }
            }
        }
        
        //utility
        function settings_page()
        {
            if ( count($_POST) > 0 && isset($_POST['csf_settings']) )
            {
                //do post stuff if needed.

            }
            add_submenu_page('tools.php',__('Database Tools'),__('Database Tools'), 'administrator', 'convert-options', array(&$this,'settings_page_content'));
        }
        function settings_page_content()
        {

            ?>
            <style>
                span.note{
                    display: block;
                    font-size: 0.9em;
                    font-style: italic;
                    color: #999999;
                }
                body{
                    background-color: transparent;
                }
                .input-table.even{background-color: rgba(0,0,0,0.1);padding: 2rem 0;}
                .input-table .description{display:none}
                .input-table li:after{content:".";display:block;clear:both;visibility:hidden;line-height:0;height:0}
                .input-table label{display:block;font-weight:bold;margin-right:1%;float:left;width:14%;text-align:right}
                .input-table label span{display:inline;font-weight:normal}
                .input-table span{color:#999;display:block}
                .input-table .input{width:85%;float:left}
                .input-table .input .half{width:48%;float:left}
                .input-table textarea,.input-table input[type='text'],.input-table select{display:inline;margin-bottom:3px;width:90%}
                .input-table .mceIframeContainer{background:#fff}
                .input-table h4{color:#999;font-size:1em;margin:15px 6px;text-transform:uppercase}
            </style>
            <script>
                jQuery(document).ready(function($) {
                    $('.done button').attr("disabled", "disabled").html('Done');
                    $('.create_student_users').click(function(){
                        var data = {
                            action: 'create_student_users',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.create_donor_users').click(function(){
                        var data = {
                            action: 'create_donor_users',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.copy_application_dates').click(function(){
                        var data = {
                            action: 'copy_application_dates',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.move_applicant_majors').click(function(){
                        var data = {
                            action: 'move_applicant_majors',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.reduce_majors').click(function(){
                        var data = {
                            action: 'reduce_majors',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.fix_emails').click(function(){
                        var data = {
                            action: 'fix_emails',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.update_renewal_table').click(function(){
                        var data = {
                            action: 'update_renewal_table',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.update_applicant_table').click(function(){
                        var data = {
                            action: 'update_applicant_table',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.parse_emails').click(function(){
                        var data = {
                            action: 'parse_emails',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.move_collegeid').click(function(){
                        var data = {
                            action: 'move_collegeid',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.add_renewal_to_attachment_table').click(function(){
                        var data = {
                            action: 'add_renewal_to_attachment_table',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.send_renewal_emails').click(function(){
                        var data = {
                            action: 'send_renewal_emails',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.send_renewal_emails_2019').click(function(){
                        var data = {
                            action: 'send_renewal_emails_2019',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.fix_up_renewal_attachments').click(function(){
                        var data = {
                            action: 'fix_up_renewal_attachments',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.update_unpublishable_tables').click(function(){
                        var data = {
                            action: 'update_unpublishable_tables',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.add_need_table').click(function(){
                        var data = {
                            action: 'add_need_table',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.add_payment_table').click(function(){
                        var data = {
                            action: 'add_payment_table',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.remove_unneccesary_tables').click(function(){
                        var data = {
                            action: 'remove_unneccesary_tables',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.add_employer_table').click(function(){
                        var data = {
                            action: 'add_employer_table',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.update_applicant_table_again').click(function(){
                        var data = {
                            action: 'update_applicant_table_again',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.update_guardian_table').click(function(){
                        var data = {
                            action: 'update_guardian_table',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.update_applicantscholarship_table').click(function(){
                        var data = {
                            action: 'update_applicantscholarship_table',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.modify_amount_columns').click(function(){
                        var data = {
                            action: 'modify_amount_columns',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.repair_renewals_with_no_user_id').click(function(){
                        var data = {
                            action: 'repair_renewals_with_no_user_id',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.clean_text_fields').click(function(){
                        var data = {
                            action: 'clean_text_fields',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.add_other_school_to_renewals').click(function(){
                        var data = {
                            action: 'add_other_school_to_renewals',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.make_scholarships_deleteable').click(function(){
                        var data = {
                            action: 'make_scholarships_deleteable',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.recommend').click(function(){
                        var data = {
                            action: 'recommend',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.add_contacts_to_scholarships').click(function(){
                        var data = {
                            action: 'add_contacts_to_scholarships',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.create_donoruserscholarship').click(function(){
                        var data = {
                            action: 'create_donoruserscholarship',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.add_reject_columns').click(function(){
                        var data = {
                            action: 'add_reject_columns',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.add_award_id').click(function(){
                        var data = {
                            action: 'add_award_id',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.add_academic_year_columns').click(function(){
                        var data = {
                            action: 'add_academic_year_columns',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.add_awardid_to_payments').click(function(){
                        var data = {
                            action: 'add_awardid_to_payments',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.add_employerid_columns').click(function(){
                        var data = {
                            action: 'add_employerid_columns',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.add_calipari_column').click(function(){
                        var data = {
                            action: 'add_calipari_column',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.move_temp_payments_to_payments').click(function(){
                        var data = {
                            action: 'move_temp_payments_to_payments',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.update_awarded_users').click(function(){
                        var data = {
                            action: 'update_awarded_users',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.update_academic_year_columns').click(function(){
                        var data = {
                            action: 'update_academic_year_columns',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    $('.create_renewal_users_from_paper_applicant_list').click(function(){
                        var data = {
                            action: 'create_renewal_users_from_paper_applicant_list',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                });

            </script>
            <div class="wrap">
                <h2>Database Update Tools</h2>
                <dl>
                    <div class="done">
                    <dt>Create Student Users:</dt>
                   <dd><button class="create_student_users">Go</button></dd>
                    <dt>Create Donor Users:</dt>
                    <dd><button class="create_donor_users">Go</button></dd>
                    <dt>Copy Application Dates:</dt>
                    <dd><button class="copy_application_dates">Go</button></dd>
                    <dt>Move Applicant Majors:</dt>
                    <dd><button class="move_applicant_majors">Go</button></dd>
                    <dt>Reduce Majors:</dt>
                    <dd><button class="reduce_majors">Go</button></dd>
                    <dt>Fix Emails:</dt>
                    <dd><button class="fix_emails">Go</button></dd>

                    <dt>Update Renewal Table:</dt>
                    <dd><button class="update_renewal_table">Go</button></dd>
                    <dt>Update Applicant Table:</dt>
                    <dd><button class="update_applicant_table">Go</button></dd>
                    <dt>Parse Emails for Renewals:</dt>
                    <dd><button class="parse_emails">Go</button></dd>
                    <dt>Move CollegeID to applicant table:</dt>
                    <dd><button class="move_collegeid">Go</button></dd>
                    <dt>Add renewal to attachment table:</dt>
                    <dd><button class="add_renewal_to_attachment_table">Go</button></dd>
                    <dt>Fix up renewal attachments:</dt>
                    <dd><button class="fix_up_renewal_attachments">Go</button></dd>
                    <dt>Update unpublishable tables:</dt>
                    <dd><button class="update_unpublishable_tables">Go</button></dd>
                    <dt>Add need table:</dt>
                    <dd><button class="add_need_table">Go</button></dd>
                    <dt>Add payment table:</dt>
                    <dd><button class="add_payment_table">Go</button></dd>
                    <dt>Remove unneccessary tables:</dt>
                    <dd><button class="remove_unneccesary_tables">Go</button></dd>
                    <dt>Add employer table:</dt>
                    <dd><button class="add_employer_table">Go</button></dd>
                    <dt>Update Applicant Table Again:</dt>
                    <dd><button class="update_applicant_table_again">Go</button></dd>
                    <dt>Update Guardian Table:</dt>
                    <dd><button class="update_guardian_table">Go</button></dd>
                    <dt>Update ApplicantScholarship Table:</dt>
                    <dd><button class="update_applicantscholarship_table">Go</button></dd>
                    <dt>Modify Amount Columns:</dt>
                    <dd><button class="modify_amount_columns">Go</button></dd>
                    <dt>repair_renewals_with_no_user_id:</dt>
                    <dd><button class="repair_renewals_with_no_user_id">Go</button></dd>
                    <dt>Clean up text fields:</dt>
                    <dd><button class="clean_text_fields">Go</button></dd>
                    <dt>add_other_school_to_renewals:</dt>
                    <dd><button class="add_other_school_to_renewals">Go</button></dd>
                    <dt>make_scholarships_deleteable:</dt>
                    <dd><button class="make_scholarships_deleteable">Go</button></dd>
                    <dt>recommend:</dt>
                    <dd><button class="recommend">Go</button></dd>
                    <dt>add_contacts_to_scholarships:</dt>
                    <dd><button class="add_contacts_to_scholarships">Go</button></dd>
                    <dt>create_donoruserscholarship:</dt>
                    <dd><button class="create_donoruserscholarship">Go</button></dd>
                    <dt>add_reject_columns:</dt>
                    <dd><button class="add_reject_columns">Go</button></dd>
                    <dt>add_award_id:</dt>
                    <dd><button class="add_award_id">Go</button></dd>
                    <dt>add_academic_year_columns:</dt>
                    <dd><button class="add_academic_year_columns">Go</button></dd>
                    </div>
                    <dt>add_awardid_to_payments:</dt>
                    <dd><button class="add_awardid_to_payments">Go</button></dd>
                    <dt>add_employerid_columns:</dt>
                    <dd><button class="add_employerid_columns">Go</button></dd>
                    <dt>add_calipari_column:</dt>
                    <dd><button class="add_calipari_column">Go</button></dd>
                    <dt>move_temp_payments_to_payments:</dt>
                    <dd><button class="move_temp_payments_to_payments">Go</button></dd>
                    <dt>update_awarded_users:</dt>
                    <dd><button class="update_awarded_users">Go</button></dd>

                    <dt>update_academic_year_columns:</dt>
                    <dd><button class="update_academic_year_columns">Go</button></dd>
                    <dt>create_renewal_users_from_paper_applicant_list:</dt>
                    <dd><button class="create_renewal_users_from_paper_applicant_list">Go</button></dd>

                    <dt>Send renewal emails:</dt>
                    <dd><button class="send_renewal_emails_2019">Go</button></dd>
                </dl>
                <div class="response1"></div>
            </div>
            <?php
        }


        /**
         * Generate and return a random characters string
         *
         * Useful for generating passwords or hashes.
         *
         * The default string returned is 8 alphanumeric characters string.
         *
         * The type of string returned can be changed with the "type" parameter.
         * Seven types are - by default - available: basic, alpha, alphanum, num, nozero, unique and md5.
         *
         * @param   string  $type    Type of random string.  basic, alpha, alphanum, num, nozero, unique and md5.
         * @param   integer $length  Length of the string to be generated, Default: 8 characters long.
         * @return  string
         */
        function random_str($type = 'alphanum', $length = 8)
        {
            switch($type)
            {
                case 'basic'    : return mt_rand();
                    break;
                case 'alpha'    :
                case 'alphanum' :
                case 'num'      :
                case 'nozero'   :
                    $seedings             = array();
                    $seedings['alpha']    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $seedings['alphanum'] = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $seedings['num']      = '0123456789';
                    $seedings['nozero']   = '123456789';

                    $pool = $seedings[$type];

                    $str = '';
                    for ($i=0; $i < $length; $i++)
                    {
                        $str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
                    }
                    return $str;
                    break;
                case 'unique'   :
                case 'md5'      :
                    return md5(uniqid(mt_rand()));
                    break;
            }
        }
    }
}