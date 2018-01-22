<?php
class MSDLAB_Queries{

    private $post_vars;
    

    /**
     * A reference to an instance of this class.
     */
    private static $instance;


    /**
     * Returns an instance of this class.
     */
    public static function get_instance() {

        if( null == self::$instance ) {
            self::$instance = new MSDLAB_Queries();
        }

        return self::$instance;

    }

    public function __construct() {
        global $wpdb;
        if ( ! empty( $_POST ) ) { //add nonce
            $this->post_vars = $_POST;
        }
        /*if(class_exists('MSDLAB_FormControls')){
            $this->form = new MSDLAB_FormControls();
        }*/
    }


    /*
     * Setting Queries
     */

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
             return '<div class="message error">Invalid entry</div>';
         }
         foreach ($this->post_vars AS $k => $v){
             if(stripos($k,'_input')){
                 $option = str_replace('_input','',$k);
                 $orig = get_option($option);
                 if($v !== $orig) {
                     if (!update_option($option, $v)) {
                         return '<div class="message error">Error updating ' . $option .'</div>';
                     }
                 }
             }
         }
         return '<div class="message success">Data Updated</div>';
     }

     /*
      * Report Queries
      */

     public function get_all_applications(){
         global $wpdb;
         $usertable = $wpdb->prefix . 'users';
         $data['tables']['Applicant'] = array('*');
         $data['where'] = 'Applicant.ApplicationDateTime > 20180101000000'; //replace with dates from settings
         $data['tables'][$usertable] = array('user_email');
         $data['where'] .= ' AND ' . $usertable . '.ID  = Applicant.UserId';
         $data['tables']['ApplicantCollege'] = array('CollegeId');
         $data['where'] .= ' AND ApplicantCollege.ApplicantId = Applicant.ApplicantId';
         $results = $this->get_result_set($data);

         foreach ($results AS $k => $r){
             $applicant_id = $r->ApplicantId;
             $agreements = $financial = $docs = array();
             //add agreements
             $agreements['tables']['Agreements'] = array('ApplicantHaveRead','ApplicantDueDate','ApplicantDocsReq','ApplicantReporting','GuardianHaveRead','GuardianDueDate','GuardianDocsReq','GuardianReporting');
             $agreements['where'] .= ' Agreements.ApplicantId = ' . $applicant_id;
             $agreements_results = $this->get_result_set($agreements);
             foreach($agreements_results AS $ar){
                 foreach($ar as $y => $z){
                     $results[$k]->$y = $z;
                 }
             }
             //add financial
             if($this->is_indy($applicant_id)){
                 $financial['tables']['ApplicantFinancial'] = array('ApplicantEmployer', 'ApplicantIncome', 'SpouseEmployer', 'SpouseIncome', 'Homeowner', 'HomeValue', 'AmountOwedOnHome');
                 $financial['where'] .= ' ApplicantFinancial.ApplicantId = ' . $applicant_id;
             } else {
                 $financial['tables']['Guardian'] = array('GuardianFullName1', 'GuardianEmployer1', 'GuardianFullName2', 'GuardianEmployer2', 'Homeowner', 'HomeValue', 'AmountOwedOnHome','InformationSharingAllowedByGuardian');
                 $financial['where'] .= ' Guardian.ApplicantId = ' . $applicant_id;
             }
             $financial_results = $this->get_result_set($financial);
             foreach($financial_results AS $fr){
                 foreach($fr as $y => $z){
                     $results[$k]->$y = $z;
                 }
             }
             //add docs
             $docs['tables']['Attachment'] = array('AttachmentTypeId','FilePath');
             $docs['where'] = 'ApplicantID = '.$applicant_id;
             $documents = $this->get_result_set($docs);
             foreach($documents AS $d){
                 $results[$k]->Documents .= '<a href="'.$d->FilePath.'">'.$this->get_attachment_type_by_id($d->AttachmentTypeId).'</a><br />';
             }
         }
         return $results;
     }

/*
 *  Form Queries
 */
     public function set_data($form_id,$where){
         global $wpdb;
         if(empty($this->post_vars)){
             return false;
         }
         $nonce = $_POST['_wpnonce'];
         if(wp_verify_nonce( $nonce, $form_id ) === false) {
             return 'no nonce';
         }
         foreach ($this->post_vars AS $k => $v){
             if(stripos($k,'_input')){
                $karray = explode('_',$k);
                if(count($karray)<3){continue;}
                $table = $karray[0];
                $field = $karray[1];
                $tables[] = $table;
                $data[$table][] = $table.'.'.$field.' = "'.trim($v).'"';
             }
         }
         $tables = array_flip(array_unique($tables));
         foreach($tables AS $table => $v){
             unset($tables[$table]);
             if($table == 'Attachment'){
                 if($this->handle_attachments($data[$table])){
                     continue;
                 } else {
                     return '<div class="error">Error updating '.$table.'</div>';
                 }
             }
             $select_sql = 'SELECT * FROM '.$table.' WHERE '.$where[$table].';';
//ts_data($select_sql);
             if($r = $wpdb->get_row($select_sql)){
                 $sql = 'UPDATE '.$table.' SET '.implode(', ',$data[$table]).' WHERE '.$where[$table].';';
             } else {
                 $sql = 'INSERT INTO '.$table.' SET '.implode(', ',$data[$table]).';';
             }
//ts_data($sql);
             $result = $wpdb->get_results($sql);
             if(is_wp_error($result)){
                 return '<div class="message error">Error updating '.$table.'</div>';
             }
        }
         return '<div class="message success">Application saved!</div>';
     }
    /**
     * Create the full result set
     *
     * @return $array The parsed result set.
     */
    public function get_result_set($data){
        global $wpdb;
        $this->__construct();
        foreach($data['tables'] AS $table => $fieldslist){
            $tables[] = $table;
                foreach($fieldslist AS $field){
                $fields[] = $table.'.'.$field;
                }
        }
        $sql = 'SELECT '.implode(', ',$fields).' FROM '.implode(', ',$tables).' WHERE '.$data['where'].';';
        $result = $wpdb->get_results($sql);
        return $result;
    }

    function get_select_array_from_db($table,$id_field,$field,$orderby = false){
        global $wpdb;
        if(!$orderby){
            $orderby = $id_field;
        }
        $sql = 'SELECT `'.$id_field.'`,`'.$field.'` FROM `'.$table.'` ORDER BY `'.$orderby.'` ASC;';
        $result = $wpdb->get_results( $sql, ARRAY_A );
        foreach ($result AS $k=>$v){
            $array[$v[$id_field]] = $v[$field];
        }
        return $array;
    }

    function handle_attachments($data){
        global $wpdb;
        foreach($data AS $item){
            $item = explode(' = ',$item);
            $attdat[str_ireplace('Attachment.','',$item[0])] = trim($item[1],'"');
        }
        //peel off the applicant id
        $applicant_id = $attdat['ApplicantId'];
        unset($attdat['ApplicantId']);

        //create or locate upload dir for applicant
        $upload_dir   = wp_upload_dir();
        if ( isset( $applicant_id ) && ! empty( $upload_dir['basedir'] ) ) {
            $user_dirname = $upload_dir['basedir'].'/attachments/'.date("Y").'/'.$applicant_id;
            $user_url = $upload_dir['baseurl'].'/attachments/'.date("Y").'/'.$applicant_id;
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
            if (move_uploaded_file($fileinfo['tmp_name'], $ufile)) {
                //$ret[] = '<div class="message success">' . basename($fileinfo['name']) . ' successfully uploaded.</div>';
                $filepath = $user_url.'/'.basename($fileinfo['name']);
                $sql = "INSERT INTO `Attachment` SET `ApplicantId` = '".$applicant_id."', `AttachmentTypeId` = '".$attachment_type_id."', `FilePath` = '".$filepath."';";
                $result = $wpdb->get_results($sql);
                if(is_wp_error($result)){
                    print '<div class="message error">Error saving upload data to database.</div>';
                    return false;
                }
            } else {
                print '<div class="message error">Possible file upload attack!</div>';
                return false;
            }
        }
        return true;
    }


    /*
    *  Resource Queries
    */

    function get_attachment_type_ids(){
        global $wpdb;
        $sql = 'SELECT * FROM AttachmentType;';
        $result = $wpdb->get_results( $sql, ARRAY_A );
        $attachment_type_ids = array();
        foreach ($result as $r) {
            $attachment_type_ids[$r['AttachmentType']] = $r['AttachmentTypeId'];
        }
        return $attachment_type_ids;
    }

    function get_attachment_type_by_id($id){
        global $wpdb;
        $sql = "SELECT AttachmentType FROM AttachmentType WHERE AttachmentTypeId = ".$id.";";
        $result = $wpdb->get_results( $sql );
        return $result[0]->AttachmentType;
    }
    function get_state_by_id($id){
        global $wpdb;
        $sql = "SELECT State FROM State WHERE StateId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->State;
    }
    function get_county_by_id($id){
        global $wpdb;
        $sql = "SELECT County FROM County WHERE CountyId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->County;
    }
    function get_ethnicity_by_id($id){
        global $wpdb;
        $sql = "SELECT Ethnicity FROM Ethnicity WHERE EthnicityId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->Ethnicity;
    }
    function get_sex_by_id($id){
        global $wpdb;
        $sql = "SELECT Sex FROM Sex WHERE SexId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->Sex;
    }
    function get_college_by_id($id){
        global $wpdb;
        $sql = "SELECT Name FROM College WHERE CollegeId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->Name;
    }
    function get_major_by_id($id){
        global $wpdb;
        $sql = "SELECT MajorName FROM Major WHERE MajorId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->MajorName;
    }
    function get_educationalattainment_by_id($id){
        global $wpdb;
        $sql = "SELECT EducationalAttainment FROM EducationalAttainment WHERE EducationalAttainmentId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->EducationalAttainment;
    }
    function get_highschool_by_id($id){
        global $wpdb;
        $sql = "SELECT SchoolName FROM HighSchool WHERE HighSchoolId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->SchoolName;
    }

    function is_indy($applicant_id){
        $indy['where'] = 'Applicant.ApplicantId = ' . $applicant_id;;
        $indy['tables']['Applicant'] = array('IsIndependent');
        $results = $this->get_result_set($indy);
        $result = $results[0];
        if($result->IsIndependent){
            return true;
        } else {
            return false;
        }
    }
    function is_adult($applicant_id){
        $indy['where'] = 'Applicant.ApplicantId = ' . $applicant_id;;
        $indy['tables']['Applicant'] = array('DateOfBirth');
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


}