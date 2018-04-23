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
            add_action( 'wp_ajax_parse_emails', array(&$this,'parse_emails') );


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
            if($wpdb->get_results($sql)) {
                print "updated!";
            }
        }

        function update_applicant_table(){
            global $wpdb;
            $sql = "ALTER TABLE applicant
  ADD `StudentId` varchar(50) NOT NULL,
  ADD `ResumeOK` tinyint(1) unsigned zerofill NOT NULL,
  ADD `TranscriptOK` tinyint(1) unsigned zerofill NOT NULL,
  ADD `FinancialAidOK` tinyint(1) unsigned zerofill NOT NULL,
  ADD `FAFSAOK` tinyint(1) unsigned zerofill NOT NULL,
  ADD `ApplicationlLocked` tinyint(1) unsigned zerofill NOT NULL;";
            if($wpdb->get_results($sql)) {
                print "updated!";
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
                    $args = array(
                        'first_name' => $student->FirstName,
                        'last_name' => $student->LastName,
                        'user_login' => sanitize_title_with_dashes(strtolower($student->FirstName . '_' . $student->LastName)),
                        'user_email' => $student->email, //doublecheck that no one is actually going to get emailed.
                        'role' => 'awardee',
                        'user_pass' => 'This is a lousy pa$$word.',
                    );
                    $user_id = wp_insert_user($args);
                    if(is_wp_error($user_id)){
                        ts_data($user_id);
                        continue;
                    }
                    $sql = 'UPDATE temp_emails SET user_id = '.$user_id.' WHERE id = "'.$student->id.'";';
                    if($wpdb->get_results($sql)){
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

        //utility
        function settings_page()
        {
            if ( count($_POST) > 0 && isset($_POST['csf_settings']) )
            {
                //do post stuff if needed.

            }
            add_submenu_page('tools.php',__('Convert Old Data'),__('Convert Old Data'), 'administrator', 'convert-options', array(&$this,'settings_page_content'));
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
                });
            </script>
            <div class="wrap">
                <h2>Data Conversion Tools</h2>
                <dl>
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
                    <dt>Parse Emails:</dt>
                    <dd><button class="parse_emails">Go</button></dd>

                </dl>
                <div class="response1"></div>
            </div>
            <?php
        }
    }
}