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
        //constructor
        function __construct(){
            add_action('admin_menu', array(&$this,'settings_page'));
            add_action( 'wp_ajax_create_student_users', array(&$this,'create_student_users') );
            add_action( 'wp_ajax_create_donor_users', array(&$this,'create_donor_users') );
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
                });
            </script>
            <div class="wrap">
                <h2>Data Conversion Tools</h2>
                <dl>
                    <dt>Create Student Users:</dt>
                   <dd><button class="create_student_users">Go</button></dd>
                   <dt>Create Donor Users:</dt>
                   <dd><button class="create_donor_users">Go</button></dd>

                </dl>
                <div class="response1"></div>
            </div>
            <?php
        }
    }
}