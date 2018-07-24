<?php
class MSDLAB_Queries{

    /**
     * A reference to an instance of this class.
     */
    private static $instance;
    private $post_vars;

    /**
     * Returns an instance of this class.
     */
    public static function get_instance() {

        if( null == self::$instance ) {
            self::$instance = new MSDLAB_Queries();
        }

        return self::$instance;

    }

    /**
     * Save any updated data
     *
     * @return true on success, error message on failure.
     */
     public function set_option_data($form_id){
         if(empty($this->post_vars)){
            return false;
         }
         $nonce = $_POST['_wpnonce'];
         if(!wp_verify_nonce( $nonce, $form_id )) {
             return new WP_Error( 'nononce', __( '<div class="message error">Invalid entry</div>', "erudite" ) );
         }
         foreach ($this->post_vars AS $k => $v){
             if(stripos($k,'_input')){
                 $option = str_replace('_input','',$k);
                 $orig = get_option($option);
                 if($v !== $orig) {
                     if (!update_option($option, $v)) {
                         return new WP_Error( 'update', __( '<div class="message error">Error updating ' . $option .'</div>', "erudite" ) );
                     }
                 }
             }
         }
         return '<div class="message success">Data Updated</div>';
     }


    /*
     * Setting Queries
     */

     public function get_all_colleges($options = array()){
         $data['tables']['college'] = array('*');
         $data['where'] = 'college.Publish = 1';
         $data['order'] = 'name ASC';
         $results = $this->get_result_set($data);
         return $results;
     }


    /*
     * Setting Queries
     */

    /**
     * Create the full result set
     *
     * @return $array The parsed result set.
     */
    public function get_result_set($data){
        global $wpdb;
        $this->__construct();
        foreach($data['tables'] AS $table => $fieldslist){
            $tables[] = strtolower($table);
                foreach($fieldslist AS $field){
                $fields[] = strtolower($table).'.'.$field;
                }
        }
        $sql[] = 'SELECT '.implode(', ',$fields).' FROM '.implode(', ',$tables);
        if(isset($data['where'])){
            $sql[] = 'WHERE '.$data['where'];
        }
        if(isset($data['order'])){
            $sql[] = 'ORDER BY '.$data['order'];
        }
        $sql[] = ';';

        //TODO: refactor all queries to ue proper JOIN
        //error_log('select_sql:'.implode(' ',$sql));
        $result = $wpdb->get_results(implode(' ',$sql));
        return $result;
    }

    public function __construct() {
        global $wpdb;
        if ( ! empty( $_POST ) ) { //add nonce
            $this->post_vars = $_POST;
        }
    }

     public function get_college($college_id){
         $data['tables']['college'] = array('*');
         $data['where'] = 'college.CollegeId = '.$college_id;
         $results = $this->get_result_set($data);
         return $results[0];
     }

     public function get_all_contacts($college_id){
         $data['tables']['collegecontact'] = array('*');
         $data['where'] = 'collegecontact.CollegeId = '.$college_id.' AND collegecontact.Publish = 1';
         $results = $this->get_result_set($data);
         return $results;
     }
     /*
      * Report Queries
      */

     public function get_contact($contact_id){
         $data['tables']['collegecontact'] = array('*');
         $data['where'] = 'collegecontact.CollegecontactId = '.$contact_id;
         $results = $this->get_result_set($data);
         return $results[0];
     }

/*
 *  Form Queries
 */

     public function set_data($form_id,$where,$notifications = array()){
         global $wpdb;
         if(empty($this->post_vars)){
             return false;
         }
         $notifications = array_merge(
             array(
                 'nononce' => 'Application could not be saved.',
                 'success' => 'Application saved!'
             ),$notifications
         );
         $nonce = $_POST['_wpnonce'];
         //error_log('form_id:'.$form_id);
         if(wp_verify_nonce( $nonce, $form_id ) === false) {
             return new WP_Error( 'nononce', $notifications['nononce'] );

         }
         foreach ($this->post_vars AS $k => $v){
             if(stripos($k,'_input')){
                $karray = explode('_',$k);
                if(count($karray)<3){continue;}
                $table = strtolower($karray[0]);
                $field = $karray[1];
                $key = $karray[2];
                $tables[] = $table;
                if($key == 'input'){
                    unset($key);
                    $data[$table][] = $table.'.'.$field.' = "'.trim($v).'"';
                } else {
                    $data[$table][$key][] = $table.'.'.$field.' = "'.trim($v).'"';
                }
             }
         }
         $tables = array_flip(array_unique($tables));
         foreach($tables AS $table => $v){
             unset($tables[$table]);
             if($table == 'attachment'){
                 if($this->handle_attachments($data[$table])){
                     continue;
                 } else {
                     return new WP_Error( 'attachments', '<div class="error">Error updating '.$table.'</div>' );
                 }
             }
             if($table == 'payment') { //handling payments with keys
                 foreach ($data[$table] as $key => $datum){
                     $select_sql = 'SELECT paymentid FROM ' . $table . ' WHERE ' . $table . '_key = ' . $key . ' AND ' . $where[$table] . ';';
                     error_log('check_sql: '.$select_sql);
                     if ($r = $wpdb->get_row($select_sql)) {
                         $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $data[$table][$key]) . ' WHERE paymentid = ' . $r->paymentid . ';';
                     } else {
                         $sql = 'INSERT INTO ' . $table . ' SET ' . implode(', ', $data[$table][$key]) . ';';
                     }
                     error_log('update_sql: '.$sql);
                     $result = $wpdb->get_results($sql);
                     if (is_wp_error($result)) {
                         return new WP_Error('update', '<div class="error">Error updating ' . $table . '</div>');
                     }
                 }
             } else {
                 $select_sql = 'SELECT * FROM ' . $table . ' WHERE ' . $where[$table] . ';';
                 //error_log('check_sql: '.$select_sql);
                 if ($r = $wpdb->get_row($select_sql)) {
                     $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $data[$table]) . ' WHERE ' . $where[$table] . ';';
                 } else {
                     $sql = 'INSERT INTO ' . $table . ' SET ' . implode(', ', $data[$table]) . ';';
                 }
                 //error_log('update_sql: '.$sql);
                 $result = $wpdb->get_results($sql);
                 if (is_wp_error($result)) {
                     return new WP_Error('update', '<div class="error">Error updating ' . $table . '</div>');
                 }
             }
        }
         return '<div class="message success">'.$notifications['success'].'</div>';
     }

    function handle_attachments($data){
        global $wpdb;
        foreach($data AS $item){
            $item = explode(' = ',$item);
            $attdat[str_ireplace('Attachment.','',$item[0])] = trim($item[1],'"');
        }
        //peel off the applicant id
        $applicant_id = $attdat['ApplicantId'];

        //check for renewal id
        $renewal_id = false;
        if(array_key_exists('RenewalId',$attdat)) {
            //error_log('array key exists');
            if($attdat['RenewalId'] == ''){
                //this is a new renewal

                //error_log('new renewal');
                $renewal_id = $this->get_renewal_id_by_applicant($applicant_id);
                //error_log('renewal id: '.$renewal_id);
            } else {
                $renewal_id = $attdat['RenewalId'];
            }
            unset($attdat['RenewalId']);
        }


        //create or locate upload dir for applicant
        $upload_dir   = wp_upload_dir();
        if ( isset( $applicant_id ) && ! empty( $upload_dir['basedir'] ) ) {
            if($renewal_id){
                $user_dirname = $upload_dir['basedir'].'/attachments/'.date("Y").'/renewals/'.$applicant_id;
                $user_url = $upload_dir['baseurl'].'/attachments/'.date("Y").'/renewals/'.$applicant_id;
            } else {
                $user_dirname = $upload_dir['basedir'].'/attachments/'.date("Y").'/'.$applicant_id;
                $user_url = $upload_dir['baseurl'].'/attachments/'.date("Y").'/'.$applicant_id;
            }
            if ( ! file_exists( $user_dirname ) ) {
                wp_mkdir_p( $user_dirname );
            }
        }

        //get the attachment type ids
        $atids = $this->get_attachment_type_ids();
        foreach($_FILES AS $k => $fileinfo){
            if($fileinfo['error'] != 0){continue;}
            preg_match('#Attachment_(.*?)_input#i',$k,$matches);
            //get the filetypeid
            $attachment_type = $matches[1];
            $attachment_type_id = $atids[$attachment_type];
            //handle the upload
            $ufile = $user_dirname .'/'. basename($fileinfo['name']);
            //TODO: add test to replace files
            if (move_uploaded_file($fileinfo['tmp_name'], $ufile)) {
                //$ret[] = '<div class="message success">' . basename($fileinfo['name']) . ' successfully uploaded.</div>';
                $filepath = $user_url.'/'.basename($fileinfo['name']);
                $sql = "INSERT INTO `attachment` SET `ApplicantId` = '".$applicant_id."',`RenewalId` = '".$renewal_id."', `AttachmentTypeId` = '".$attachment_type_id."', `FilePath` = '".$filepath."';";
               // error_log('attachemnt_sql: '.$sql);
                $result = $wpdb->get_results($sql);
                if(is_wp_error($result)){
                    print '<div class="message error">Error saving upload data to database.</div>';
                    return false;
                }
            } else {
                //error_log(basename($fileinfo['name']).' not moved.');
                print '<div class="message error">Possible file upload attack!</div>';
                return false;
            }
        }
        return true;
    }

    function get_renewal_id_by_applicant($applicant_id){
        global $wpdb;
        $sql = 'SELECT RenewalId FROM renewal WHERE ApplicantId = "'.$applicant_id.'" AND RenewalDateTime > "'.date('Ymdhis',strtotime('-5 minutes')).'";';
        $results = $wpdb->get_results($sql);
        return $results[0]->RenewalId;
    }

    function get_attachment_type_ids(){
        global $wpdb;
        $sql = 'SELECT * FROM attachmenttype;';
        $result = $wpdb->get_results( $sql, ARRAY_A );
        $attachment_type_ids = array();
        foreach ($result as $r) {
            $attachment_type_ids[$r['AttachmentType']] = $r['AttachmentTypeId'];
        }
        return $attachment_type_ids;
    }

    /*
     * Report Queries
     */

    function get_select_array_from_db($table,$id_field,$field,$orderby = false,$publishflag = 0){
        global $wpdb;
        $where = '';
        if(!$orderby){
            $orderby = $id_field;
        }
        if($publishflag){
            $where = ' WHERE '.strtolower($table).'.Publish = 1 ';
        }
        $sql = 'SELECT `'.$id_field.'`,`'.$field.'` FROM `'.strtolower($table).'` '.$where.'ORDER BY `'.$orderby.'` ASC;';
        $result = $wpdb->get_results( $sql, ARRAY_A );
        foreach ($result AS $k=>$v){
            $array[$v[$id_field]] = $v[$field];
        }
        return $array;
    }

    /**
     * Create the full result set
     *
     * @return $array The parsed result set.
     */
    public function get_report_set($fields = array()){
        global $wpdb;
        //setup initial args
        $user_args = array();
        //get full set
        if(empty($this->post_vars)){
            return $this->get_all_applications();
        }
        //ts_data($this->post_vars);
        $usertable = $wpdb->prefix . 'users';
        $data['tables']['applicant'] = array('*');


        if(empty($this->post_vars['date_search_input_start']) && empty($this->post_vars['date_search_input_end'])) {
            $data['where'] = 'applicant.ApplicationDateTime > '.date('Ymdhis',strtotime(get_option('csf_settings_start_date'))); //replace with dates from settings
        } else {
            if(!empty($this->post_vars['date_search_input_start'])){
                $where[] = 'applicant.ApplicationDateTime > '.date('Ymdhis',strtotime($this->post_vars['date_search_input_start']));
            } else {
                $where[] = 'applicant.ApplicationDateTime > '.date('Ymdhis',strtotime(get_option('csf_settings_start_date'))); //replace with dates from settings
            }
            if(!empty($this->post_vars['date_search_input_start'])){
                $where[] = 'applicant.ApplicationDateTime < '.date('Ymdhis',strtotime($this->post_vars['date_search_input_end']));
            }
            $data['where'] = implode(' AND ',$where);
        }

        if(!empty($this->post_vars['name_search_input'])) {
            //add search for name on application
            $search_terms = explode(' ',addslashes($this->post_vars['name_search_input']));
            if(count($search_terms>1)){
                $fullnamesearch = ' OR (applicant.FirstName LIKE \'%'. $search_terms[0] .'%\' AND applicant.LastName LIKE \'%'. $search_terms[1] .'%\')';
            }
            $data['where'] .= ' AND (applicant.FirstName LIKE \'%'. addslashes($this->post_vars['name_search_input']) .'%\' OR applicant.LastName LIKE \'%'. addslashes($this->post_vars['name_search_input']) .'%\''.$fullnamesearch.') ';
        }
        if(!empty($this->post_vars['city_search_input'])){
            $data['where'] .= ' AND applicant.City LIKE \'%'.$this->post_vars['city_search_input'].'%\'';
        }
        if(!empty($this->post_vars['state_search_input'])){
            $data['where'] .= ' AND applicant.StateId = \''.$this->post_vars['state_search_input'].'\'';
        }
        if(!empty($this->post_vars['county_search_input'])){
            $data['where'] .= ' AND applicant.CountyId = '.$this->post_vars['county_search_input'];
        }
        if(!empty($this->post_vars['zip_search_input'])){
            $data['where'] .= ' AND applicant.ZipCode IN ('.$this->post_vars['zip_search_input'].')';
        }
        if(!empty($this->post_vars['highschool_search_input'])){
            $data['where'] .= ' AND applicant.HighSchoolId = '.$this->post_vars['highschool_search_input'];
        }
        if(!empty($this->post_vars['highschooltype_search_input'])){
            //subquery to get schools with a type?
            $highschools = $this->get_result_set(array('tables' => array('highschool' => array('HighSchoolId')),'where' => ' highschool.SchoolTypeId = '.$this->post_vars['highschooltype_search_input']));
            foreach($highschools AS $school){
                $hs[] = $school->HighSchoolId;
            }
            $highschools = implode(',',$hs);
            $data['where'] .= ' AND applicant.HighSchoolId IN ('.$highschools.')';
        }
        if($this->post_vars['gpa_range_search_input_start']!=0 || $this->post_vars['gpa_range_search_input_end']!=5){
            $data['where'] .= ' AND (applicant.HighSchoolGPA >= '.$this->post_vars['gpa_range_search_input_start'].' AND applicant.HighSchoolGPA <= '.$this->post_vars['gpa_range_search_input_end'].')';
        }
        if($this->post_vars['gradyear_range_search_input_start']!=0 || $this->post_vars['gradyear_range_search_input_end']!=5){
            $data['where'] .= ' AND (YEAR(applicant.HighSchoolGraduationDate) >= '.$this->post_vars['gradyear_range_search_input_start'].' AND YEAR(applicant.HighSchoolGraduationDate) <= '.$this->post_vars['gradyear_range_search_input_end'].')';
        }
        if(!empty($this->post_vars['major_search_input'])){
            $data['where'] .= ' AND applicant.MajorId = '.$this->post_vars['major_search_input'];
        }
        if(!empty($this->post_vars['ethnicity_search_input'])){
            $data['where'] .= ' AND applicant.EthnicityId = '.$this->post_vars['ethnicity_search_input'];
        }
        if(is_numeric($this->post_vars['athlete_search_input'])){
            $data['where'] .= ' AND applicant.PlayedHighSchoolSports = '.$this->post_vars['athlete_search_input'];
        }
        if(is_numeric($this->post_vars['independence_search_input'])){
            $data['where'] .= ' AND applicant.IsIndependent = '.$this->post_vars['independence_search_input'];
        }


        $data['tables'][$usertable] = array('user_email');
        $data['where'] .= ' AND ' . $usertable . '.ID  = applicant.UserId';
        if(!empty($this->post_vars['email_search_input'])) {
            //add search for an email on application
            $data['where'] .= ' AND (applicant.Email LIKE \'%'.$this->post_vars['email_search_input'].'%\' OR ' . $usertable . '.user_email  LIKE \'%'.$this->post_vars['email_search_input'].'%\')';
        }/*
        $data['tables']['applicantcollege'] = array('CollegeId');
        $data['where'] .= ' AND (applicantcollege.ApplicantId = applicant.ApplicantId)';
        */
        if(!empty($this->post_vars['college_search_input'])){
            $data['where'] .= ' AND applicant.CollegeId = '.$this->post_vars['college_search_input'];
        }
        //ts_data($data);
        $results = $this->get_result_set($data);
        //error_log($wpdb->last_query);

        foreach ($results AS $k => $r){
            $applicant_id = $r->ApplicantId;

            $college = $agreements = $financial = $docs = array();

            /*//add college
            $college['tables']['applicantcollege'] = array('CollegeId');
            $college['where'] .= ' AND (applicantcollege.ApplicantId = applicant.ApplicantId)';
            $college_results = $this->get_result_set($college);
            foreach($college_results AS $ar){
                foreach($ar as $y => $z){
                    $results[$k]->$y = $z;
                }
            }*/

            //add agreements
            $agreements['tables']['agreements'] = array('ApplicantHaveRead','ApplicantDueDate','ApplicantDocsReq','ApplicantReporting','GuardianHaveRead','GuardianDueDate','GuardianDocsReq','GuardianReporting');
            $agreements['where'] .= ' agreements.ApplicantId = ' . $applicant_id;
            $agreements_results = $this->get_result_set($agreements);
            foreach($agreements_results AS $ar){
                foreach($ar as $y => $z){
                    $results[$k]->$y = $z;
                }
            }
            //add financial
            if($this->is_indy($applicant_id)){
                $financial['tables']['applicantfinancial'] = array('ApplicantEmployer', 'ApplicantIncome', 'SpouseEmployer', 'SpouseIncome', 'Homeowner', 'HomeValue', 'AmountOwedOnHome');
                $financial['where'] .= ' applicantfinancial.ApplicantId = ' . $applicant_id;
            } else {
                $financial['tables']['guardian'] = array('GuardianFullName1', 'GuardianEmployer1', 'GuardianFullName2', 'GuardianEmployer2', 'Homeowner', 'HomeValue', 'AmountOwedOnHome','InformationSharingAllowedByGuardian','CPSPublicSchools');
                $financial['where'] .= ' guardian.ApplicantId = ' . $applicant_id;
            }
            $financial_results = $this->get_result_set($financial);
            foreach($financial_results AS $fr){
                foreach($fr as $y => $z){
                    $results[$k]->$y = $z;
                }
            }
            //add docs
            $docs['tables']['attachment'] = array('AttachmentTypeId','FilePath');
            $docs['where'] = 'ApplicantId = '.$applicant_id;
            $documents = $this->get_result_set($docs);
            foreach($documents AS $d){
                $results[$k]->Documents .= '<a href="'.$d->FilePath.'">'.$this->get_attachment_type_by_id($d->AttachmentTypeId).'</a><br />';
            }

            //add status
            $status['tables']['applicationprocess'] = array('ProcessStepId','ProcessStepBool');
            $status['where'] = 'ApplicantId = '.$applicant_id;
            $status_results = $this->get_result_set($status);
            foreach($status_results AS $sr){
                if($sr->ProcessStepBool == 1) {
                    if($sr->ProcessStepId > $results[$k]->Status) {
                        $results[$k]->status = $sr->ProcessStepId;
                    }
                }
            }
        }
        return $results;
    }

    /*
    *  Resource Queries
    */

     public function get_all_applications(){
         global $wpdb;
         $usertable = $wpdb->prefix . 'users';
         $data['tables']['applicant'] = array('*');
         $data['where'] = 'applicant.ApplicationDateTime > 20180101000000'; //replace with dates from settings
         $data['tables'][$usertable] = array('user_email');
         $data['where'] .= ' AND ' . $usertable . '.ID  = applicant.UserId';
         //$data['tables']['applicantcollege'] = array('CollegeId');
         //$data['where'] .= ' AND applicantcollege.ApplicantId = applicant.ApplicantId';
         $results = $this->get_result_set($data);

         foreach ($results AS $k => $r){
             $applicant_id = $r->ApplicantId;
             $agreements = $financial = $docs = array();
             //add agreements
             $agreements['tables']['agreements'] = array('ApplicantHaveRead','ApplicantDueDate','ApplicantDocsReq','ApplicantReporting','GuardianHaveRead','GuardianDueDate','GuardianDocsReq','GuardianReporting');
             $agreements['where'] .= ' agreements.ApplicantId = ' . $applicant_id;
             $agreements_results = $this->get_result_set($agreements);
             foreach($agreements_results AS $ar){
                 foreach($ar as $y => $z){
                     $results[$k]->$y = $z;
                 }
             }
             //add financial
             if($this->is_indy($applicant_id)){
                 $financial['tables']['applicantfinancial'] = array('ApplicantEmployer', 'ApplicantIncome', 'SpouseEmployer', 'SpouseIncome', 'Homeowner', 'HomeValue', 'AmountOwedOnHome');
                 $financial['where'] .= ' applicantfinancial.ApplicantId = ' . $applicant_id;
             } else {
                 $financial['tables']['guardian'] = array('GuardianFullName1', 'GuardianEmployer1', 'GuardianFullName2', 'GuardianEmployer2', 'Homeowner', 'HomeValue', 'AmountOwedOnHome','InformationSharingAllowedByGuardian');
                 $financial['where'] .= ' guardian.ApplicantId = ' . $applicant_id;
             }
             $financial_results = $this->get_result_set($financial);
             foreach($financial_results AS $fr){
                 foreach($fr as $y => $z){
                     $results[$k]->$y = $z;
                 }
             }
             //add docs
             $docs['tables']['attachment'] = array('AttachmentTypeId','FilePath');
             $docs['where'] = 'ApplicantId = '.$applicant_id;
             $documents = $this->get_result_set($docs);
             foreach($documents AS $d){
                 $results[$k]->Documents .= '<a href="'.$d->FilePath.'">'.$this->get_attachment_type_by_id($d->AttachmentTypeId).'</a><br />';
             }

             //add status
             $status['tables']['applicationprocess'] = array('ProcessStepId','ProcessStepBool');
             $status['where'] = 'ApplicantId = '.$applicant_id;
             $status_results = $this->get_result_set($status);
             foreach($status_results AS $sr){
                 if($sr->ProcessStepBool == 1) {
                     if($sr->ProcessStepId > $results[$k]->Status) {
                         $results[$k]->status = $sr->ProcessStepId;
                     }
                 }
             }
         }
         return $results;
     }

    function is_indy($applicant_id){
        $indy['where'] = 'applicant.ApplicantId = ' . $applicant_id;;
        $indy['tables']['applicant'] = array('IsIndependent');
        $results = $this->get_result_set($indy);
        $result = $results[0];
        if($result->IsIndependent){
            return true;
        } else {
            return false;
        }
    }

    function get_attachment_type_by_id($id){
        global $wpdb;
        $sql = "SELECT AttachmentType FROM attachmenttype WHERE AttachmentTypeId = ".$id.";";
        $result = $wpdb->get_results( $sql );
        return $result[0]->AttachmentType;
    }

    function get_renewal_report_set($fields){
        global $wpdb;
        //setup initial args
        $user_args = array();
        //get full set
        if(empty($this->post_vars)){
            return $this->get_all_renewals();
        }
        //ts_data($this->post_vars);
        //$usertable = $wpdb->prefix . 'users';
        $data['tables']['renewal'] = array('*');
        //ts_data($this->post_vars);
        if(empty($this->post_vars['date_search_input_start']) && empty($this->post_vars['date_search_input_end'])) {
            $data['where'] = 'renewal.RenewalDateTime > '.date('Ymdhis',strtotime(get_option('csf_settings_start_date'))); //replace with dates from settings
        } else {
            if(!empty($this->post_vars['date_search_input_start'])){
                $where[] = 'renewal.RenewalDateTime > '.date('Ymdhis',strtotime($this->post_vars['date_search_input_start']));
            } else {
                $where[] = 'renewal.RenewalDateTime > '.date('Ymdhis',strtotime(get_option('csf_settings_start_date'))); //replace with dates from settings
            }
            if(!empty($this->post_vars['date_search_input_start'])){
                $where[] = 'renewal.RenewalDateTime < '.date('Ymdhis',strtotime($this->post_vars['date_search_input_end']));
            }
            $data['where'] = implode(' AND ',$where);
        }

        if(!empty($this->post_vars['name_search_input'])) {
            //add search for name on application
            $search_terms = explode(' ',$this->post_vars['name_search_input']);
            if(count($search_terms>1)){
                $fullnamesearch = ' OR (renewal.FirstName LIKE \'%'. $search_terms[0] .'%\' AND renewal.LastName LIKE \'%'. $search_terms[1] .'%\')';
            }
            $data['where'] .= ' AND (renewal.FirstName LIKE \'%'. $this->post_vars['name_search_input'] .'%\' OR renewal.LastName LIKE \'%'. $this->post_vars['name_search_input'] .'%\''.$fullnamesearch.') ';
        }
        if(!empty($this->post_vars['city_search_input'])){
            $data['where'] .= ' AND renewal.City LIKE \'%'.$this->post_vars['city_search_input'].'%\'';
        }
        if(!empty($this->post_vars['state_search_input'])){
            $data['where'] .= ' AND renewal.StateId = \''.$this->post_vars['state_search_input'].'\'';
        }
        if(!empty($this->post_vars['county_search_input'])){
            $data['where'] .= ' AND renewal.CountyId = '.$this->post_vars['county_search_input'];
        }
        if(!empty($this->post_vars['zip_search_input'])){
            $data['where'] .= ' AND renewal.ZipCode IN ('.$this->post_vars['zip_search_input'].')';
        }

        if($this->post_vars['gpa_range_search_input_start']!=0 || $this->post_vars['gpa_range_search_input_end']!=5){
            $data['where'] .= ' AND (renewal.CurrentCumulativeGPA >= '.$this->post_vars['gpa_range_search_input_start'].' AND renewal.CurrentCumulativeGPA <= '.$this->post_vars['gpa_range_search_input_end'].')';
        }
        if(!empty($this->post_vars['major_search_input'])){
            $data['where'] .= ' AND renewal.MajorId = '.$this->post_vars['major_search_input'];
        }
        if(!empty($this->post_vars['college_search_input'])){
            $data['where'] .= ' AND renewal.CollegeId = '.$this->post_vars['college_search_input'];
        }


        //$data['tables'][$usertable] = array('user_email');
        //$data['where'] .= ' AND ' . $usertable . '.ID  = renewal.UserId';
        if(!empty($this->post_vars['email_search_input'])) {
            //add search for an email on application
            $data['where'] .= ' AND renewal.Email  LIKE \'%'.$this->post_vars['email_search_input'].'%\'';
        }
        //ts_data($data);
        $results = $this->get_result_set($data);
        //error_log($wpdb->last_query);
        return $results;
    }



    function get_user_application_status(){
        global $current_user,$applicant_id,$wpdb;
        if(!$applicant_id){$applicant_id = $this->get_applicant_id($current_user->ID);}
        $sql = "SELECT * FROM applicationprocess WHERE applicationprocess.ApplicantId = ".$applicant_id ." ORDER BY applicationprocess.ProcessStepId DESC";
        $result = $wpdb->get_results($sql);
        return $result[0]->ProcessStepId;
    }

    function get_applicant_id($user_id){
        global $wpdb;
        $sql = "SELECT ApplicantId FROM applicant WHERE UserId = ". $user_id;
        //error_log($sql);
        $result = $wpdb->get_results($sql);
        return $result[0]->ApplicantId;
    }

    function get_user_application_status_list(){
        global $current_user,$applicant_id,$wpdb;
        if(!$applicant_id){$applicant_id = $this->get_applicant_id($current_user->ID);}
        //clean up with graphic display of all steps and steps completed
        $steps = $this->get_application_process_steps();
        //ts_data($steps);
        $sql = "SELECT * FROM applicationprocess,processsteps WHERE applicationprocess.ApplicantId = ".$applicant_id." AND applicationprocess.ProcessStepId = processsteps.StepId";
        $result = $wpdb->get_results($sql);
        if(count($result)>0) {
            $hdr = MSDLAB_FormControls::section_header('ProcessHeader', 'Application Process');
            foreach ($result AS $r) {
                $progress[] = $r->StepName;
            }
            return false;
            return $hdr . '<ul><li>' . implode('</li>' . "\n" . '<li>', $progress) . '</li></ul>';
        }
    }


    function get_student_data($applicant_id){
        $personal['tables']['Applicant'] = array('*');
        $personal['where'] = 'applicant.ApplicantId = ' . $applicant_id;

        $independence['tables']['ApplicantIndependenceQuery'] = array('*');
        $independence['where'] .= 'applicantindependencequery.ApplicantId = ' . $applicant_id;

        if($this->is_indy($applicant_id)) {
            $financial['tables']['ApplicantFinancial'] = array('*');
            $financial['where'] .= 'applicantfinancial.ApplicantId = ' . $applicant_id;
        } else {
            $financial['tables']['Guardian'] = array('*');
            $financial['where'] .= 'guardian.ApplicantId = ' . $applicant_id;
        }
        $agreements['tables']['Agreements'] = array('*');
        $agreements['where'] .= 'agreements.ApplicantId = ' . $applicant_id;

        $docs['tables']['Attachment'] = array('*');
        $docs['where'] = 'attachment.ApplicantID = '.$applicant_id;

        $renewal['tables']['renewal'] = array('*');
        $renewal['where'] = 'renewal.ApplicantId = '.$applicant_id;

        $need['tables']['studentneed'] = array('*');
        $need['where'] = 'studentneed.ApplicantId = '.$applicant_id;

        $payment['tables']['payment'] = array('*');
        $payment['where'] = 'payment.ApplicantId = '.$applicant_id;

        $scholarship['tables']['scholarship'] = array('*');
        $scholarship['tables']['applicantscholarship'] = array('*');
        $scholarship['where'] = 'applicantscholarship.ApplicantId = '.$applicant_id.' AND scholarship.ScholarshipId = applicantscholarship.ScholarshipId';

        $queries = array('personal','independence','financial','agreements','docs','renewal','need','payment','scholarship');
        foreach($queries AS $query){
            $result_array = $this->get_result_set(${$query});
            switch($query){
                case 'payment':
                    foreach($result_array AS $ra){
                        $results[$query][$ra->paymentkey] = $ra;
                    }
                    break;
                case 'docs':
                    $results[$query] = $result_array;
                    break;
                default:
                    $results[$query] = $result_array[0];
                    break;
            }
        }
        return $results;
    }

    function get_application_process_steps(){
        global $wpdb;
        $sql = "SELECT * FROM processsteps";
        $result = $wpdb->get_results($sql);
        $ret = array();
        foreach($result AS $r){
            $ret[$r->StepId] = $r->StepName;
        }
        return $ret;
    }

    function get_user_id_by_applicant($applicant_id){
        global $wpdb;
        $sql = "SELECT UserId FROM applicant WHERE ApplicantId = ". $applicant_id;
        //error_log($sql);
        $result = $wpdb->get_results($sql);
        return $result[0]->UserId;
    }

    function get_state_by_id($id){
        global $wpdb;
        $sql = "SELECT State FROM state WHERE StateId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->State;
    }

    function get_county_by_id($id){
        global $wpdb;
        $sql = "SELECT County FROM county WHERE CountyId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->County;
    }

    function get_ethnicity_by_id($id){
        global $wpdb;
        $sql = "SELECT Ethnicity FROM ethnicity WHERE EthnicityId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->Ethnicity;
    }

    function get_sex_by_id($id){
        global $wpdb;
        $sql = "SELECT Sex FROM sex WHERE SexId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->Sex;
    }

    function get_college_by_id($id){
        global $wpdb;
        $sql = "SELECT Name FROM college WHERE CollegeId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->Name;
    }

    function get_major_by_id($id){
        global $wpdb;
        $sql = "SELECT MajorName FROM major WHERE MajorId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->MajorName;
    }

    function get_educationalattainment_by_id($id){
        global $wpdb;
        $sql = "SELECT EducationalAttainment FROM educationalattainment WHERE EducationalAttainmentId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->EducationalAttainment;
    }

    function get_highschool_by_id($id){
        global $wpdb;
        $sql = "SELECT SchoolName FROM highschool WHERE HighSchoolId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->SchoolName;
    }

    function get_highschool_type_by_highschool_id($id){
        global $wpdb;
        $sql = "SELECT SchoolTypeId FROM highschool WHERE HighSchoolId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->SchoolTypeId;
    }

    function get_scholarship_by_id($id){
        global $wpdb;
        $sql = "SELECT Name FROM scholarship WHERE ScholarshipId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->Name;
    }

    function get_fund_by_scholarshipid($id){
        global $wpdb;
        $sql = "SELECT Name FROM fund WHERE FundId = (SELECT FundId FROM scholarship WHERE ScholarshipId = '".$id."');";
        $result = $wpdb->get_results( $sql );
        return $result[0]->Name;
    }

    function get_status_by_id($id){
        global $wpdb;
        $sql = "SELECT StepName FROM processsteps WHERE StepId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->StepName;
    }

    function is_adult($applicant_id){
        $indy['where'] = 'applicant.ApplicantId = ' . $applicant_id;;
        $indy['tables']['applicant'] = array('DateOfBirth');
        $results = $this->get_result_set($indy);
        $result = $results[0];
        $dob = strtotime($result->DateOfBirth);
        $cutoff = strtotime("- 18 years");
        if($dob <= $cutoff){
            return true;
        } else {
            return false;
        }
    }

    function get_college_financials($college_id,$field){
        global $wpdb;
        $sql = 'SELECT '.$field.' FROM college WHERE CollegeId = '.$college_id;
        $results = $wpdb->get_results($sql);
        $dec = explode('.',$results[0]->{$field});
        return $dec[0];
    }

    function get_next_id($table,$id_field){
        global $wpdb;
        $sql = 'SELECT '.$id_field.' FROM '.$table.' ORDER BY '.$id_field.' DESC LIMIT 1';
        $results = $wpdb->get_results($sql);
        return $results[0]->{$id_field}+1;
    }

}