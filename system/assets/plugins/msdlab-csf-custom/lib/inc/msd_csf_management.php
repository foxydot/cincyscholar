<?php
if (!class_exists('MSDLab_CSF_Management')) {
    class MSDLab_CSF_Management {
        //Properties
        var $cpt = 'application';

        var $edlvls_array;
        var $highschool_array;
        var $gradyr_array;

        var $states_array = array(
            'AL' => 'Alabama',
            'AK' => 'Alaska',
            'AZ' => 'Arizona',
            'AR' => 'Arkansas',
            'CA' => 'California',
            'CO' => 'Colorado',
            'CT' => 'Connecticut',
            'DE' => 'Delaware',
            'DC' => 'District Of Columbia',
            'FL' => 'Florida',
            'GA' => 'Georgia',
            'HI' => 'Hawaii',
            'ID' => 'Idaho',
            'IL' => 'Illinois',
            'IN' => 'Indiana',
            'IA' => 'Iowa',
            'KS' => 'Kansas',
            'KY' => 'Kentucky',
            'LA' => 'Louisiana',
            'ME' => 'Maine',
            'MD' => 'Maryland',
            'MA' => 'Massachusetts',
            'MI' => 'Michigan',
            'MN' => 'Minnesota',
            'MS' => 'Mississippi',
            'MO' => 'Missouri',
            'MT' => 'Montana',
            'NE' => 'Nebraska',
            'NV' => 'Nevada',
            'NH' => 'New Hampshire',
            'NJ' => 'New Jersey',
            'NM' => 'New Mexico',
            'NY' => 'New York',
            'NC' => 'North Carolina',
            'ND' => 'North Dakota',
            'OH' => 'Ohio',
            'OK' => 'Oklahoma',
            'OR' => 'Oregon',
            'PA' => 'Pennsylvania',
            'RI' => 'Rhode Island',
            'SC' => 'South Carolina',
            'SD' => 'South Dakota',
            'TN' => 'Tennessee',
            'TX' => 'Texas',
            'UT' => 'Utah',
            'VT' => 'Vermont',
            'VA' => 'Virginia',
            'WA' => 'Washington',
            'WV' => 'West Virginia',
            'WI' => 'Wisconsin',
            'WY' => 'Wyoming',
        );

        var $counties_array;
        //Methods
        /**
         * PHP 5 Constructor
         */
        function __construct(){
            global $current_screen;
            //TODO: Add a user management panel
            //TODO: Add a scholarship management panel
            $required_files = array('msd_form_controls','msd_controls','msd_export','msd_queries','msd_views');
            foreach($required_files AS $rq){
                if(file_exists(plugin_dir_path(__FILE__).'/'.$rq . '.php')){
                    require_once(plugin_dir_path(__FILE__).'/'.$rq . '.php');
                } else {
                    ts_data(plugin_dir_path(__FILE__).'/'.$rq . '.php does not exisit');
                }
            }
            if(class_exists('MSDLAB_FormControls')){
                $this->form = new MSDLAB_FormControls();
            }
            if(class_exists('MSDLAB_QueryControls')){
                $this->controls = new MSDLAB_QueryControls();
            }
            if(class_exists('MSDLAB_Queries')){
                $this->queries = new MSDLAB_Queries();
            }
            if(class_exists('MSDLAB_Display')){
                $this->display = new MSDLAB_Display();
            }

            //register stylesheet
            //Actions
            add_action('admin_menu', array(&$this,'settings_page'));
            add_action('wp_enqueue_scripts', array(&$this,'add_styles_and_scripts'));
            //Filters

            //Shortcodes
            add_shortcode('application', array(&$this,'application_shortcode_handler'));

        }

        function add_admin_styles_and_scripts(){
            wp_enqueue_style('bootstrap-style','//maxcdn.bootstrapcdn.com/bootstrap/latest/css/bootstrap.min.css',false,'4.5.0');
            wp_enqueue_style('font-awesome-style','//maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css',false,'4.5.0');
            wp_enqueue_script('bootstrap-jquery','//maxcdn.bootstrapcdn.com/bootstrap/latest/js/bootstrap.min.js',array('jquery'));
        }

        function add_styles_and_scripts(){
            wp_enqueue_style( 'msdform-css', plugin_dir_url(__DIR__).'/../css/msdform.css' );
        }

        function settings_page(){
            add_menu_page(__('CSF Management and Reports'),__('CSF'), 'administrator', 'csf-settings', array(&$this,'setting_page_content'),'dashicons-chart-area');
            add_submenu_page('csf-settings',__('Settings'),__('Settings'),'administrator','csf-settings', array(&$this,'setting_page_content'));
        }

        function setting_page_content(){
            //page content here
            if($msg = $this->queries->set_data()){
                print '<div class="updated notice notice-success is-dismissible">'.$msg.'</div>';
            }
            print '<h2>Scholarship Application Period</h2>';
            $this->controls->print_settings();
        }

        function application_shortcode_handler($atts,$content){
            extract(shortcode_atts( array(
                'application' => 'default', //default to primary application
            ), $atts ));

            if($content == ''){
                $content = get_option('csf_settings_alt_text');
            }
            $start_date = strtotime(get_option('csf_settings_start_date'));
            $end_date = strtotime(get_option('csf_settings_end_date'));
            $today = time();
            if($today >= $start_date && $today <= $end_date){
                if(is_user_logged_in()){
                    $ret = array();
                    if(current_user_can('edit_application')){
                        $ret = $this->get_form('application');
                    }
                    if(current_user_can('view_application_process')){
                        $ret[] = 'VIEW APPLICATION PROCESS';
                    }
                    if(current_user_can('view_award')){
                        $ret[] = 'VIEW AWARD';
                    }
                    if(current_user_can('view_renewal_process')){
                        $ret[] = 'VIEW RENEWAL PROCESS';
                    }
                    return implode("\n\r",$ret);
                } else {
                    return '<div class="login-trigger"><span class="button">Login/Register</span></div>';
                }
            } else {
                return $content;
            }
        }

        //ultilities

        function numToOrdinalWord($num)
        {
            $first_word = array('eth','First','Second','Third','Fouth','Fifth','Sixth','Seventh','Eighth','Ninth','Tenth','Elevents','Twelfth','Thirteenth','Fourteenth','Fifteenth','Sixteenth','Seventeenth','Eighteenth','Nineteenth','Twentieth');
            $second_word =array('','','Twenty','Thirty','Forty','Fifty');

            if($num <= 20)
                return $first_word[$num];

            $first_num = substr($num,-1,1);
            $second_num = substr($num,-2,1);

            return $string = str_replace('y-eth','ieth',$second_word[$second_num].'-'.$first_word[$first_num]);
        }

        //db funzies

        function get_select_array_from_db($table,$id_field,$field){
            global $wpdb;
            $sql = 'SELECT `'.$id_field.'`,`'.$field.'` FROM `'.$table.'`;';
            $result = $wpdb->get_results( $sql, ARRAY_A );
            foreach ($result AS $k=>$v){
                $array[$v[$id_field]] = $v[$field];
            }
            return $array;
        }

        function get_form($form_id,$options = array()){
            global $current_user;
            $defaults = array();
            //just in case
            $options = array_merge($defaults,$options);
            //get the form selects
            $this->edlvls_array = $this->get_select_array_from_db('EducationalAttainment','EducationalAttainmentId','EducationalAttainment');
            $this->highschool_array = $this->get_select_array_from_db('HighSchool','HighSchoolId','SchoolName');
            for($yr = 2000;$yr <= date("Y");$yr++){
                $this->gradyr_array[$yr] = $yr;
            }
            $jquery = $ret = array();
            $ret['form_header'] = $this->form->form_header($form_id);
            switch($form_id){
                case 'application':
                    //TODO: sort out paging
                    //TODO: sort out js validation

                    $jquery[] = "$('.ui-toggle-btn').each(function(){
        var toggled = $(this).parent().next('.switchable');
        if($(this).find('input[type=checkbox]').is(':checked')){
            toggled.slideDown(0);
        } else {
            toggled.slideUp(0);
        }
    });";
                    $jquery[] = "$('.ui-toggle-btn').click(function(){
                            var toggled = $(this).parent().next('.switchable');
                            if($(this).find('input[type=checkbox]').is(':checked')){
                                toggled.slideDown(500);
                            } else {
                                toggled.slideUp(500);
                            }
                        });";
                    $ret['hdrPersInfo'] = $this->form->section_header('hdrPersInfo','Personal Information');
                    $ret['Applicant_ApplicationDateTime'] = $this->form->field_hidden("Applicant_ApplicationDateTime",time());
                    $ret['userID'] = $this->form->field_hidden("userID",$current_user->ID); //matching userID to applicantID. HRM. This is autoincremented in the DB. We will need to create userids for all the old data and start UIDs at a higher number than exisiting applicant IDs
                    $ret['Applicant_FirstName'] = $this->form->field_textfield('Applicant_FirstName',null,'First Name',  array('text' => true), array('req'));
                    $ret['Applicant_MiddleInitial'] = $this->form->field_textfield('Applicant_MiddleInitial',null,'Middle Initial', array('text' => true));
                    $ret['Applicant_LastName'] = $this->form->field_textfield('Applicant_LastName',null,'Last Name', array('text' => true), array('req'));
                    $ret['Applicant_Last4SSN'] = $this->form->field_textfield('Applicant_Last4SSN',null,'Last 4 numbers of your SS#', array('number' => true,'maxchar' => 4, 'minchar' => 4), array('req'));
                    $ret['Applicant_Address1'] = $this->form->field_textfield('Applicant_Address1',null,'Address', array('text' => true, 'required' => true), array('req'));
                    $ret['Applicant_Address2'] = $this->form->field_textfield('Applicant_Address2',null,'', array('text' => true));
                    $ret['Applicant_City'] = $this->form->field_textfield('Applicant_City',null,'City', array('text' => true), array('req'));
                    $ret['Applicant_StateId'] = $this->form->field_select('Applicant_StateId','OH','State',$this->states_array, array('required' => true), array('req'));
                    $ret['Applicant_CountyId'] = $this->form->field_select('Applicant_CountyId',null,'County',$this->counties_array);
                    $ret['Applicant_ZipCode'] = $this->form->field_textfield('Applicant_ZipCode',null,'ZIP Code', array('number' => true, 'minchar'=>5, 'maxchar'=>10), array('req'));
                    $ret['Applicant_CellPhone'] = $this->form->field_textfield('Applicant_CellPhone',null, 'Mobile Phone Number',array('required'=>true,'phone'=>true),array('req'));
                    $ret['Applicant_AlternativePhone'] = $this->form->field_textfield('Applicant_AlternativePhone',null, 'Alternative Phone Number',array('phone'=>true),array('req'));
                    $ret['Applicant_DateOfBirth'] = $this->form->field_date('Applicant_DateOfBirth', null, 'Date of Birth', array('date' => true), array('req'));
                    $ret[] = '<hr>';
                    $ret['disclaim'] = 'The Cincinnati Scholarship Foundation administers some scholarships that are restricted to members of a certain ethnic background or gender. While you are not required to supply this information, it may be to your advantage to do so.';
                    $ret['Applicant_EthnicityId'] = $this->form->field_select('Applicant_EthnicityId',null,'Race',null,$this->race_array);
                    $ret['Applicant_SexID'] = $this->form->field_radio('Applicant_SexID',null,'Gender',null,array('M'=>'Male','F'=>'Female'));

                    //not in applicant table?
                    $ret['hdrCollegeInfo'] = $this->form->section_header('hdrCollegeInfo','Academic Information');
                    for($i=1;$i<=4;$i++) {
                        $ret['cboCollege'.$i] = $this->form->field_select('cboCollege'.$i,null, $this->numToOrdinalWord($i).' Choice', null, $this->college_array);
                        $ret['txtCollege'.$i] = $this->form->field_textfield('txtCollege'.$i, null,'', array('text'=>true));
                        }
                    $ret['Applicant_HighSchoolId'] = $this->form->field_select("Applicant_HighSchoolId",null,"High School Attended", $this->highschool_array, array('required' => true), array('req'));
                    $ret['Applicant_HighSchoolGraduationDate'] = $this->form->field_select('Applicant_HighSchoolGraduationDate',null,"Year of Graduation",$this->gradyr_array);
                    //fields not in DB?
                    //$ret[''] = $this->form->field_boolean('',null, 'Were you a participant in the High School Scholarship Program?');
                    //$ret[''] = $this->form->field_textfield('',null,'Elementary School Attended');
                    //$ret[''] = $this->form->field_textfield('',null,'Middle or Junior High School');
                    $ret['Applicant_MajorId'] = $this->form->field_select('Applicant_MajorId',null,'Intended Major (If Uncertain, select Undecided)',$this->major_array,null,array('req'));
                    $ret['FirstGenerationStudent'] = $this->form->field_boolean('FirstGenerationStudent',null,'Has anyone in your immediate family ever attended college?');

                    $ret['hdrExperience'] = $this->form->section_header('HIGH SCHOOL AND/OR COLLEGE EXPERIENCE');
                    $ret['instructionExperience'] = 'The fields below allow a maximum of 255 characters each. You may choose to send a separate "resume" should your submission(s) exceed that amount. If you plan to submit a resume, please enter "see resume" in the fields below.';
                    //are these still applicable?
                    $ret['Applicant_Employer'] = $this->form->field_textarea('Applicant_Employer',null,'Employment (Give name of employer, hours/week, and years you worked)');
                    //$ret[''] = $this->form->field_textarea('',null,'Employment (Give name of employer, hours/week, and years you worked)');
                    //$ret[''] = $this->form->field_textarea('',null,'Employment (Give name of employer, hours/week, and years you worked)');




                    $ret['Applicant_EducationAttainmentId'] = $this->form->field_select("Applicant_EducationAttainmentId",'2',"Please select which year you will enter this fall", $this->edlvls_array, array('required' => true), array('req'));
                    $ret['rdoGraduate'] = $this->form->field_boolean("rdoGraduate",false,"Do you have a degree?", array('required' => true), array('req'));
                    $ret['txtGradLevel'] = $this->form->field_textfield('txtGradLevel',null,'If so, what level?', array('text' => true), array('switchable'));
                    $ret['rdoCSF'] = $this->form->field_boolean('rdoCSF', true, 'Have you ever applied to the Cincinnati Scholarship Foundation before?', array('required' => true), array('req'));
                    $ret['txtCSFwhen'] = $this->form->field_date('txtCSFwhen', null, 'if so, when (approx.)?', array('date' => true), array('switchable'));






                    $ret['button'] = $this->form->field_button('saveBtn','Next');
                    $ret['javascript'] = $this->form->build_jquery($form_id,$jquery);
                    break;
                default:
                    break;
            }
            $ret['nonce'] = wp_nonce_field( $form_id . '_nonce' );
            $ret['form_footer'] = $this->form->form_footer();
            return $ret;
        }
    } //End Class
} //End if class exists statement