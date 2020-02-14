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

            $methods = get_class_methods($this);
            $hidden_methods = array('__construct','settings_page','settings_page_content','random_str');
            foreach($hidden_methods AS $hm) {
                if (($key = array_search($hm, $methods)) !== false) {
                    unset($methods[$key]);
                }
            }
            foreach ($methods AS $method){
                add_action('wp_ajax_'.$method, array(&$this,$method));
            }

            add_filter('send_password_change_email',array(&$this,'return_false'));
            add_filter('send_email_change_email',array(&$this,'return_false'));

            $this->queries = new MSDLAB_Queries();
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
            $methods = get_class_methods($this);
            $hidden_methods = array('__construct','settings_page','settings_page_content','random_str');
            foreach($hidden_methods AS $hm) {
                if (($key = array_search($hm, $methods)) !== false) {
                    unset($methods[$key]);
                }
            }
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
                    <?php
                    foreach ($methods AS $method){
                        print "$('.".$method."').click(function(){
                        var data = {
                            action: '".$method."',
                        }
                        jQuery.post(ajaxurl, data, function(response) {
                            $('.response1').html(response);
                            console.log(response);
                        });
                    });
                    ";
                    }
                    ?>
                });

            </script>
            <div class="wrap">
                <h2>Database Update Tools</h2>
                <div class="row">
                    <div style="float: left;width: 25%;">
                <ul>
                    <?php
                    foreach ($methods AS $method){
                        print '<li><button class="'.$method.'">'.$method.'</button></li>
                   ';
                    }
                    ?>
                </ul>
                    </div>
                    <div style="float: left;width: 75%;">
                <div class="response1"></div>
                    </div>
                </div>
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
            $subject = 'It is time to renew your scholarship at CincinnatiScholarshipFooundation.org';
            $headers['from'] = 'From: Elizabeth Collins <beth@cincinnatischolarshipfoundation.org>';
            $headers['content-type'] = 'Content-Type: text/html; charset=UTF-8';
            $headers['bcc'] = 'Bcc: beth@cincinnatischolarshipfoundation.org';

            $email_str = '
            <p>Please surf to <a href = "http://cincinnatischolarshipfoundation.org">http://cincinnatischolarshipfoundation.org</a>, click the Login/Register button, and login with the same account information as last year.</p>
            <p>The email associated with your account is: [[user_email]]</p>
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

            $sql = "SELECT * FROM z_renewals1819;";
            $results = $wpdb->get_results($sql);
            foreach ($results AS $r){
                $to = $r->FirstName.' '.$r->LastName.' <'.$r->Email.'>';
                if($r->Email != $r->UserEmail){
                    $headers['cc'] = 'Cc: '.$r->UserEmail;
                } else {
                    unset($headers['cc']);
                }

                $pattern = array('/\[\[email\]\]/','/\[\[TempPwd\]\]/','/\[\[user_email\]\]/');
                $replacement = array($r->Email,$temppwd,$r->UserEmail);
                $message = preg_replace($pattern,$replacement,$email_str);


                //send the email
                if(wp_mail($to, $subject, $message, $headers)){
                    print $r->FirstName.' '.$r->LastName.', '.$r->Email.'<br />';
                }
            }
        }


        function send_renewal_emails_2020(){
            global $wpdb;
            $subject = 'It is time to renew your scholarship at CincinnatiScholarshipFoundation.org';
            $headers['from'] = 'From: Elizabeth Collins <beth@cincinnatischolarshipfoundation.org>';
            $headers['content-type'] = 'Content-Type: text/html; charset=UTF-8';
            $headers['bcc'] = 'Bcc: beth@cincinnatischolarshipfoundation.org';

            $email_str = '
            <p>Please surf to <a href = "http://cincinnatischolarshipfoundation.org">http://cincinnatischolarshipfoundation.org</a>, click the Login/Register button, and login with the same account information as last year.</p>
            <p>The email associated with your account is: [[user_email]]</p>
            <p>If you cannot remember your password, please use the "forgot password" feature to recover it.</p>
<p>
Immediately upon logging in, you will be redirected to the renewal form.
 </p><p>
 The renewal process is necessary to determine that you still meet the criteria to receive your scholarship for the 2020-2021 academic year.  We will need the following documents to complete your renewal application: 
<ul>
<li>your 2020-2021 Student Aid Report (SAR)</li>
<li>your 2020-2021 Financial Aid Notification</li>
<li>your spring semester grades, when they become available </li>
</ul>
The deadline to submit the renewal application, with or without the required documentation, is May 15th, '.date("Y").'. 
</p><p>
Elizabeth Collins<br/>
Program Administrator
</p><p>
324 East 4th St., 2nd Floor<br/>
Cincinnati OH  45202
 </p><p>
Ph:  (513)345-6701<br/>
Fax:  (513)345-6705
 </p><p>
beth@cincinnatischolarshipfoundation.org<br/>
<a href = "http://cincinnatischolarshipfoundation.org">www.cincinnatischolarshipfoundation.org</a>
</p>
';

            $sql = "SELECT * FROM z_renewals2021;";
            $results = $wpdb->get_results($sql);
            foreach ($results AS $r){
                $to = $r->FirstName.' '.$r->LastName.' <'.$r->Email.'>';
                if($r->Email != $r->UserEmail){
                    $headers['cc'] = 'Cc: '.$r->UserEmail;
                } else {
                    unset($headers['cc']);
                }

                $pattern = array('/\[\[email\]\]/','/\[\[TempPwd\]\]/','/\[\[user_email\]\]/');
                $replacement = array($r->Email,$temppwd,$r->UserEmail);
                $message = preg_replace($pattern,$replacement,$email_str);


                //send the email
                if(wp_mail($to, $subject, $message, $headers)){
                    print $r->FirstName.' '.$r->LastName.', '.$r->Email.'<br />';
                    //print $to .'<br>'. $subject .'<br>'. $message .'<br><br>';
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

            UPDATE z_renewals1819 SET ROLE = (SELECT meta_value FROM fdn_usermeta WHERE fdn_usermeta.user_id = z_renewals1819.UserId AND fdn_usermeta.meta_key = 'fdn_capabilities' LIMIT 1);
             *
             * This will check for exisiting users/applications
             *
             * Dump all "Student Awardee Renewing" down to "Student Awardee", then run Upgrade Renewals.
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

        function fix_stupid_extra_duplicates(){
            global $wpdb;

            $sql = 'SELECT a.ApplicantId, b.ApplicantId AS RenewalApplicantId, a.UserId, b.RenewalId, a.FirstName, a.LastName, a.Email FROM applicant a, renewal b WHERE a.AcademicYear = 2018 AND b.AcademicYear = 2019 AND a.UserId = b.UserId AND a.ApplicantId != b.ApplicantId;';
            $students = $wpdb->get_results($sql);
            foreach($students AS $student){
                $sql = 'UPDATE renewal SET ApplicantId = '.$student->ApplicantId.' WHERE ApplicantId = '.$student->RenewalApplicantId.';';
                if($wpdb->query($sql)){
                    print '<li>'.$student->FirstName.' '.$student->LastName.' ('.$student->UserId.') renewal moved from Application #'.$student->RenewalApplicantId.' to '.$student->ApplicantId.'.</li>';
                }
                $sql = 'DELETE FROM applicant WHERE ApplicantId = '.$student->RenewalApplicantId.';';
                if($wpdb->query($sql)){
                    print '<li>Duplicate application #'.$student->RenewalApplicantId.' deleted.</li>';
                }              }
        }


        function update_renewal_table_with_completion_fields(){
            global $wpdb;
            $sql = "ALTER TABLE renewal 
ADD `IsComplete` tinyint(4) NOT NULL,
ADD `ResumeOK` tinyint(1) unsigned zerofill NOT NULL,
ADD `TranscriptOK` tinyint(1) unsigned zerofill NOT NULL,
ADD `FinancialAidOK` tinyint(1) unsigned zerofill NOT NULL,
ADD `FAFSAOK` tinyint(1) unsigned zerofill NOT NULL;";
            if($wpdb->query($sql)) {
                print "renewal table updated!";
            }
        }

        function add_previous_award_info_to_renewals(){
            global $wpdb;
            $sql = 'SELECT ApplicantId FROM renewal';
            $result = $wpdb->get_results($sql);
            foreach ($result AS $r){
                $award = $award_notes = array();
                $applicant_id = $r->ApplicantId;
                $award['tables']['applicantscholarship'] = array('*');
                $award['tables']['scholarship'] = array('*');
                $award['where'] = 'applicantscholarship.ApplicantId = ' . $applicant_id .' AND applicantscholarship.ScholarshipId = scholarship.ScholarshipId';
                $awards = $this->queries->get_result_set($award);

                foreach ($awards AS $award){
                    $notes = array();

                    $notes[] = $award->AwardId.'(';
                    $notes[] = 'Academic Year: '.$award->AcademicYear;
                    $notes[] = 'Scholarship: '.$award->Name;
                    $notes[] = 'Amount Awarded: $'.$award->AmountAwarded;
                    $notes[] = 'Award Date: '.$award->DateAwarded;
                    $notes[] = ');';
                    $award_notes[] = implode("\n",$notes);
                }
                $award_note = implode("\n",$award_notes);
                $sql = 'UPDATE renewal SET Notes = "'.$award_note.'" WHERE ApplicantId = '.$applicant_id.';';
                if($wpdb->query($sql)) {
                    print $applicant_id." updated!";
                }
            }
        }

        function fix_bad_locks(){
            global $wpdb;
            $sql = 'SELECT UserId FROM applicant WHERE AcademicYear = 2019 GROUP BY UserId HAVING COUNT(UserId) > 1;';
            $result = $wpdb->get_results($sql,'ARRAY_N');
            foreach($result AS $r){
                $user_ids[] = $r[0];
            }
            $sql = 'SELECT * FROM applicant WHERE applicant.UserId IN ('.implode(',',$user_ids).') AND AcademicYear = 2019 ORDER BY applicant.UserId, applicant.ApplicantId;';
            $result = $wpdb->get_results($sql);
            foreach($result AS $r){
                $grouped[$r->UserId][] = $r;
            }
            $ignored_fields = array('ApplicantId','ApplicationDateTime','Notes');
            $ignored_cols = array('ApplicantId','associates');

            foreach($grouped AS $group){
                $last = array_key_last($group);
                foreach ($group AS $index => $object) {
                    if ($index == 0) {
                        $canonical_applicant_id = $object->ApplicantId;
                        $canon[$canonical_applicant_id] = $object;
                        $origin = clone $object;
                        $notes = $associated_applicant_ids = array();
                        continue;
                    }
                    foreach ($object AS $key => $value) {
                        if (!in_array($key, $ignored_fields)) {
                            if ($value != $canon[$canonical_applicant_id]->{$key} && $value != '' && $value != 0) {
                                $canon[$canonical_applicant_id]->{$key} = $value;
                            }
                        } elseif ($key == 'Notes'){
                            $notes[] = $value;
                        } elseif($key == 'ApplicantId'){
                            $associated_applicant_ids[] = $value;
                        }
                    }
                    if($index == $last){
                        $notes = implode("\n",array_unique($notes));
                        $notes .= "/n Associated applicant ids: ". implode(", ",$associated_applicant_ids);
                        $canon[$canonical_applicant_id]->Notes = $notes;
                        $canon[$canonical_applicant_id]->associates = $associated_applicant_ids;
                    }
                }
                $datastr = array();
                foreach($canon[$canonical_applicant_id] AS $col => $data){
                    if(!in_array($col,$ignored_cols)) {
                        if($data == $origin->{$col}) {
                            //print '<tr><td>'.$data.'</td><td>'.$origin->{$col}.'</td></tr>';
                        } else {
                            $datastr[] = $col . ' = "' . $data . '"';
                        }
                    }
                }
                ts_data($canonical_applicant_id);
                $sql = 'UPDATE applicant SET '. implode(', ',$datastr) .' WHERE ApplicantId = '.$canonical_applicant_id;
                ts_data($sql);
                $sql = 'SELECT * FROM applicantfinancial WHERE ApplicantId IN ('.implode(", ",$associated_applicant_ids).');';
                ts_data($sql);
                $sql = 'UPDATE attachment SET ApplicantId = '.$canonical_applicant_id.' WHERE ApplicantId IN ('.implode(", ",$associated_applicant_ids).');';
                ts_data($sql);
                $sql = 'UPDATE applicant SET AcademicYear = 1850 WHERE ApplicantId IN ('.implode(", ",$associated_applicant_ids).');';
                ts_data($sql);

            }
        }

        function upgrade_renewals(){
            global $wpdb;
            $sql = "SELECT * FROM z_renewals2021";

            $students = $wpdb->get_results($sql);

            foreach($students AS $student){
                $user = get_user_by('ID',$student->UserId);
                if($user){
                    $user_id = $user->ID;
                    if($wpdb->get_results($sql)){
                        print $user->display_name .' <br>';
                    }
                    if($student->Email != $user->user_email){
                        wp_update_user(array('ID' => $user->ID,'user_email' => $student->Email, 'role' => 'renewal'));
                    } else {
                        wp_update_user(array('ID' => $user->ID, 'role' => 'renewal'));

                    }
                }
            }
        }

        function remove_user_data(){
           /* DELETE FROM agreements WHERE ApplicantId = 7207;
DELETE FROM agreements WHERE ApplicantId = 10475;
DELETE FROM applicantcollege WHERE ApplicantId = 7207;
DELETE FROM applicantcollege WHERE ApplicantId = 10475;
DELETE FROM applicantfinancial WHERE ApplicantId = 7207;
DELETE FROM applicantfinancial WHERE ApplicantId = 10475;
DELETE FROM applicantindependencequery WHERE ApplicantId = 7207;
DELETE FROM applicantindependencequery WHERE ApplicantId = 10475;
DELETE FROM applicantscholarship WHERE ApplicantId = 7207;
DELETE FROM applicantscholarship WHERE ApplicantId = 10475;
DELETE FROM applicationprocess WHERE ApplicantId = 7207;
DELETE FROM applicationprocess WHERE ApplicantId = 10475;
DELETE FROM attachment WHERE ApplicantId = 7207;
DELETE FROM attachment WHERE ApplicantId = 10475;
DELETE FROM guardian WHERE ApplicantId = 7207;
DELETE FROM guardian WHERE ApplicantId = 10475;
DELETE FROM recommend WHERE ApplicantId = 7207;
DELETE FROM recommend WHERE ApplicantId = 10475;
DELETE FROM renewal WHERE ApplicantId = 7207;
DELETE FROM renewal WHERE ApplicantId = 10475;
DELETE FROM studentneed WHERE ApplicantId = 7207;
DELETE FROM studentneed WHERE ApplicantId = 10475;

DELETE FROM applicant WHERE ApplicantId = 7207;
DELETE FROM applicant WHERE ApplicantId = 10475; */
        }
    }
}

if (! function_exists("array_key_last")) {
    function array_key_last($array) {
        if (!is_array($array) || empty($array)) {
            return NULL;
        }

        return array_keys($array)[count($array)-1];
    }
}