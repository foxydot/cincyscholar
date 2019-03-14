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
    public function get_all_highschools($options = array()){
        $data['tables']['highschool'] = array('*');
        $data['where'] = 'highschool.Publish = 1';
        $data['order'] = 'SchoolName ASC';
        $results = $this->get_result_set($data);
        return $results;
    }
    public function get_all_majors($options = array()){
        $data['tables']['major'] = array('*');
        $data['where'] = 'major.Publish = 1';
        $data['order'] = 'MajorName ASC';
        $results = $this->get_result_set($data);
        return $results;
    }
    public function get_all_scholarships($options = array()){
        $data['tables']['scholarship'] = array('*');
        $data['where'] = 'scholarship.Publish = 1';
        $data['order'] = 'Name ASC';
        $results = $this->get_result_set($data);
        return $results;
    }
    public function get_all_funds($options = array()){
        $data['tables']['fund'] = array('*');
        $data['order'] = 'Name ASC';
        $results = $this->get_result_set($data);
        return $results;
    }
    public function get_all_counties($options = array()){
        $data['tables']['county'] = array('*');
        $data['order'] = 'StateId, County ASC';
        $results = $this->get_result_set($data);
        return $results;
    }
    public function get_all_ethnicities($options = array()){
        $data['tables']['ethnicity'] = array('*');
        $data['order'] = 'Ethnicity ASC';
        $results = $this->get_result_set($data);
        return $results;
    }
    public function get_all_genders($options = array()){
        $data['tables']['sex'] = array('*');
        $data['order'] = 'Sex ASC';
        $results = $this->get_result_set($data);
        return $results;
    }
    public function get_all_highschooltypes($options = array()){
        $data['tables']['highschooltype'] = array('*');
        $data['order'] = 'Type ASC';
        $results = $this->get_result_set($data);
        return $results;
    }

    public function get_all_employers($options = array()){
        $data['tables']['employer'] = array('*');
        $data['order'] = 'employername ASC';
        $results = $this->get_result_set($data);
        return $results;
    }
    public function get_all_educationalattainments($options = array()){
        $data['tables']['educationalattainment'] = array('*');
        $data['order'] = 'EducationalAttainment ASC';
        $results = $this->get_result_set($data);
        return $results;
    }
    public function get_all_donortypes($options = array()){
        $data['tables']['donortype'] = array('*');
        $data['order'] = 'DonorType ASC';
        $results = $this->get_result_set($data);
        return $results;
    }
    public function get_all_institutiontermtypes($options = array()){
        $data['tables']['institutiontermtype'] = array('*');
        $data['order'] = 'InstitutionTermType ASC';
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
        if(!is_array($data['tables'])){
            return false;
        }
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
        error_log('select_sql:'.implode(' ',$sql));
        $result = $wpdb->get_results(implode(' ',$sql));
        return $result;
    }

    public function __construct() {
        global $wpdb;
        if ( ! empty( $_POST ) ) { //add nonce
            $this->post_vars = $_POST;
        }
    }

    public function get_donor($donor_id){
        $ret['user'] = get_user_by('ID',$donor_id);
        $data['tables']['donoruserscholarship'] = array('*');
        $data['tables']['scholarship'] = array('*');
        $data['where'] = 'donoruserscholarship.UserId = '.$donor_id.' AND donoruserscholarship.ScholarshipId = scholarship.ScholarshipId';
        $ret['donor'] = $this->get_result_set($data);
        return $ret;
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

    public function get_highschool($highschool_id){
        $data['tables']['highschool'] = array('*');
        $data['where'] = 'highschool.HighSchoolId = '.$highschool_id;
        $results = $this->get_result_set($data);
        return $results[0];
    }
    public function get_major($major_id){
        $data['tables']['major'] = array('*');
        $data['where'] = 'major.MajorId = '.$major_id;
        $results = $this->get_result_set($data);
        return $results[0];
    }
    public function get_county($county_id){
        $data['tables']['county'] = array('*');
        $data['where'] = 'county.CountyId = '.$county_id;
        $results = $this->get_result_set($data);
        return $results[0];
    }
    public function get_scholarship($scholarship_id){
        $data['tables']['scholarship'] = array('*');
        $data['where'] = 'scholarship.ScholarshipId = '.$scholarship_id;
        $results = $this->get_result_set($data);
        return $results[0];
    }
    public function get_educationalattainment($educationalattainment_id){
        $data['tables']['educationalattainment'] = array('*');
        $data['where'] = 'educationalattainment.EducationalAttainmentId = '.$educationalattainment_id;
        $results = $this->get_result_set($data);
        return $results[0];
    }
    public function get_employer($employer_id){
        $data['tables']['employer'] = array('*');
        $data['where'] = 'employer.employerid = '.$employer_id;
        $results = $this->get_result_set($data);
        return $results[0];
    }
    public function get_ethnicity($ethnicity_id){
        $data['tables']['ethnicity'] = array('*');
        $data['where'] = 'ethnicity.EthnicityId = '.$ethnicity_id;
        $results = $this->get_result_set($data);
        return $results[0];
    }
    public function get_gender($gender_id){
        $data['tables']['sex'] = array('*');
        $data['where'] = 'sex.SexId = '.$gender_id;
        $results = $this->get_result_set($data);
        return $results[0];
    }
    public function get_fund($fund_id){
        $data['tables']['fund'] = array('*');
        $data['where'] = 'fund.FundId = '.$fund_id;
        $results = $this->get_result_set($data);
        return $results[0];
    }
    public function get_highschooltype($type_id){
        $data['tables']['highschooltype'] = array('*');
        $data['where'] = 'highschooltype.HighSchoolTypeId = '.$type_id;
        $results = $this->get_result_set($data);
        return $results[0];
    }
    public function get_donortype($type_id){
        $data['tables']['donortype'] = array('*');
        $data['where'] = 'donortype.DonorTypeId = '.$type_id;
        $results = $this->get_result_set($data);
        return $results[0];
    }
    public function get_institutiontermtype($type_id){
        $data['tables']['institutiontermtype'] = array('*');
        $data['where'] = 'institutiontermtype.InstitutionTermTypeId = '.$type_id;
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
                 'nononce' => 'Changes could not be saved.',
                 'success' => 'Changes saved!'
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
                 //if(is_array($v)){
                     //$v = serialize($v);
                 //}
                 if(is_string($v)){
                     $v = esc_sql(trim($v));
                 }
                if($key == 'input'){
                    unset($key);
                    if(is_array($v)){
                        $data[$table][$field] = $v;
                    } else {
                        $data[$table][$field] = $table . '.' . $field . ' = "' . $v . '"';
                    }
                } else {
                    $data[$table][$key][$field] = $table.'.'.$field.' = "'.$v.'"';
                }
             }
         }
         //error_log(json_encode($this->post_vars));
         //error_log(json_encode($data));
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
             if($table == 'applicantscholarship'){
                 foreach ($data[$table] as $key => $datum) {
                     $select_sql = 'SELECT AwardId, ApplicantId, ScholarshipId FROM ' . $table . ' WHERE ' . $table . '.AwardId = "' . $key . '";';
                     //error_log('check_sql: '.$select_sql);
                     if ($r = $wpdb->get_row($select_sql) && $key != 'new') {
                         $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $data[$table][$key]) . ' WHERE AwardId = ' . $key . ';';
                     } else {
                         $sql = 'INSERT INTO ' . $table . ' SET ' . implode(', ', $data[$table][$key]) . ';';
                         $this->update_user_role_award();
                     }
                     //error_log('update_sql: '.$sql);
                     $result = $wpdb->get_results($sql);
                     if (is_wp_error($result)) {
                         return new WP_Error('update', '<div class="error">Error updating ' . $table . '</div>');
                     }
                 }
             }
             elseif($table == 'payment') { //handling payments with keys
                 //ts_data($data[$table]);

                 foreach ($data[$table] as $key => $datum) {
                     $keys = explode('-',$key,2);
                     $data[$table][$key]['AwardId'] = 'payment.AwardId = "'.$keys[0].'"';
                     $select_sql = 'SELECT paymentid FROM ' . $table . ' WHERE ' . $table . '.paymentkey = "' . $keys[1] . '" AND AwardId = "'. $keys[0] .'" AND ' . $where[$table] . ';';
                     //error_log('check_sql: '.$select_sql);
                     if ($r = $wpdb->get_row($select_sql)) {
                         $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $data[$table][$key]) . ' WHERE paymentid = ' . $r->paymentid . ';';
                     } else {
                         $sql = 'INSERT INTO ' . $table . ' SET ' . implode(', ', $data[$table][$key]) . ';';
                     }
                     //error_log('update_sql: '.$sql);
                     $result = $wpdb->get_results($sql);
                     if (is_wp_error($result)) {
                         return new WP_Error('update', '<div class="error">Error updating ' . $table . '</div>');
                     }
                 }
             } elseif($table == 'recommend'){
                 $scholarships = $data[$table]['ScholarshipId'];
                 unset($data[$table]['ScholarshipId']);
                 $select_sql = 'SELECT RecommendationId,ScholarshipId FROM ' . $table . ' WHERE ' .$data[$table]['UserId'].';';
                 $test_ids = $wpdb->get_results($select_sql);
                 foreach($test_ids AS $ids){
                     if(!in_array($ids->ScholarshipId,$scholarships)){
                         $delete_sql = 'DELETE FROM ' . $table . ' WHERE RecommendationId = ' . $ids->RecommendationId . ';';
                         $delete_response = $wpdb->get_results($delete_sql);
                     }
                 }

                 foreach($scholarships AS $scholarship){
                     $select_sql = 'SELECT RecommendationId FROM ' . $table . ' WHERE ScholarshipId = '.$scholarship.' AND '.$data[$table]['UserId'].';';
                     // error_log('check_sql: '.$select_sql);
                     if ($r = $wpdb->get_row($select_sql)) {
                         $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $data[$table]) . ',ScholarshipId = '.$scholarship.' WHERE RecommendationId = ' . $r->RecommendationId . ';';
                         // error_log($sql);
                     } else {
                         $sql = 'INSERT INTO ' . $table . ' SET ' . implode(', ', $data[$table]) . ',ScholarshipId = '.$scholarship.';';
                     }
                     //error_log('update_sql: '.$sql);
                     $result = $wpdb->get_results($sql);
                     if (is_wp_error($result)) {
                         return new WP_Error('update', '<div class="error">Error updating ' . $table . '</div>');
                     }
                 }
             } elseif($table == 'donoruserscholarship'){
                 $scholarships = $data[$table]['ScholarshipId'];
                 //ts_data($scholarships);
                 unset($data[$table]['ScholarshipId']);
                 $select_sql = 'SELECT DUSId,ScholarshipId FROM ' . $table . ' WHERE ' .$data[$table]['UserId'].';';
                 $test_ids = $wpdb->get_results($select_sql);
                 foreach($test_ids AS $ids){
                     if(!in_array($ids->ScholarshipId,$scholarships)){
                         $delete_sql = 'DELETE FROM ' . $table . ' WHERE DUSId = ' . $ids->DUSId . ';';
                         $delete_response = $wpdb->get_results($delete_sql);
                     }
                 }

                 foreach($scholarships AS $scholarship){
                     $select_sql = 'SELECT DUSId FROM ' . $table . ' WHERE ScholarshipId = '.$scholarship.' AND '.$data[$table]['UserId'].';';
                     // error_log('check_sql: '.$select_sql);
                     if ($r = $wpdb->get_row($select_sql)) {
                         $sql = 'UPDATE ' . $table . ' SET ' . implode(', ', $data[$table]) . ',ScholarshipId = '.$scholarship.' WHERE DUSId = ' . $r->DUSId . ';';
                         // error_log($sql);
                     } else {
                         $sql = 'INSERT INTO ' . $table . ' SET ' . implode(', ', $data[$table]) . ',ScholarshipId = '.$scholarship.';';
                     }
                     //error_log('update_sql: '.$sql);
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

    function delete_data($form_id,$where,$notifications = array()){
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
        foreach ($where AS $table => $clause){
            $sql = "DELETE FROM $table WHERE $clause;";
            $wpdb->query($sql);
        }
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


    /*
     * Create a result set just for recommends to a scholarship.
     *
     */
    public function get_recommended_students($fields,$scholarship_id){
        global $wpdb;
        $sql = 'SELECT * FROM applicant,recommend WHERE applicant.UserId = recommend.UserId AND recommend.ScholarshipId = ' . $scholarship_id .';';
        $results = $wpdb->get_results($sql);
        foreach ($results AS $k => $r){
            $applicant_id = $r->ApplicantId;

            $scholarship = $agreements = $financial = $docs = $status = $payment = $need = array();

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

            //add scholarship info
            $scholarship['tables']['applicantscholarship'] = array('*');
            $scholarship['where'] = 'ApplicantId = '.$applicant_id;
            $scholarship_results = $this->get_result_set($scholarship);
            foreach($scholarship_results AS $sr){
                foreach($sr as $y => $z){
                    $results[$k]->$y = $z;
                }
            }
            //add payments
            $payment['tables']['payment'] = array('*');
            $payment['where'] = 'ApplicantId = '.$applicant_id;
            $payment_results = $this->get_result_set($payment);
            foreach($payment_results AS $pr){
                $results[$k]->payment[] = $pr;
            }

            //add need
            $need['tables']['studentneed'] = array('*');
            $need['where'] = 'ApplicantId = '.$applicant_id;
            $need_results = $this->get_result_set($need);
            foreach($need_results AS $nr){
                $results[$k]->need[] = $nr;
            }
        }
        return $results;
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
        ts_data($this->post_vars);
        $usertable = $wpdb->prefix . 'users';
        $data['tables']['applicant'] = array('*');

        if(empty($this->post_vars['application_date_search_input_start']) && empty($this->post_vars['application_date_search_input_end'])) {
            $data['where'] = 'applicant.AcademicYear = '.$this->post_vars['academic_year_input'];
        } else {
            if(!empty($this->post_vars['application_date_search_input_start'])){
                $where[] = 'UNIX_TIMESTAMP(applicant.ApplicationDateTime) > '.strtotime($this->post_vars['application_date_search_input_start']);
            } else {
                $where[] = 'UNIX_TIMESTAMP(applicant.ApplicationDateTime) > '.strtotime(get_option('csf_settings_start_date')); //replace with dates from settings
            }
            if(!empty($this->post_vars['application_date_search_input_end'])){
                $where[] = 'UNIX_TIMESTAMP(applicant.ApplicationDateTime) < '.strtotime($this->post_vars['application_date_search_input_end']);
            }
            $data['where'] = implode(' AND ',$where);
        }

        if(!empty($this->post_vars['name_search_input'])) {
            //add search for name on application
            $search_terms = explode(' ',addslashes($this->post_vars['name_search_input']));
            if(count($search_terms)>1){
                $fullnamesearch = ' OR (applicant.FirstName LIKE \'%'. $search_terms[0] .'%\' AND applicant.LastName LIKE \'%'. $search_terms[1] .'%\')';
            }
            $data['where'] .= ' AND (applicant.FirstName LIKE \'%'. addslashes($this->post_vars['name_search_input']) .'%\' OR applicant.LastName LIKE \'%'. addslashes($this->post_vars['name_search_input']) .'%\''.$fullnamesearch.') ';
        }
        $data['tables'][$usertable] = array('user_email');
        $data['where'] .= ' AND ' . $usertable . '.ID  = applicant.UserId';
        if(!empty($this->post_vars['email_search_input'])) {
            //add search for an email on application
            $data['where'] .= ' AND (applicant.Email LIKE \'%'.$this->post_vars['email_search_input'].'%\' OR ' . $usertable . '.user_email  LIKE \'%'.$this->post_vars['email_search_input'].'%\')';
        }
        if(!empty($this->post_vars['studentid_search_input'])){
            $data['where'] .= ' AND applicant.StudentId LIKE \'%'.$this->post_vars['studentid_search_input'].'%\'';
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
        if(!empty($this->post_vars['ethnicity_search_input'])){
            $data['where'] .= ' AND applicant.EthnicityId = '.$this->post_vars['ethnicity_search_input'];
        }
        if(!empty($this->post_vars['gender_search_input'])){
            $data['where'] .= ' AND applicant.SexId LIKE \'%'.$this->post_vars['gender_search_input'].'%\'';
        }
        if(is_numeric($this->post_vars['athlete_search_input'])){
            $data['where'] .= ' AND applicant.PlayedHighSchoolSports = '.$this->post_vars['athlete_search_input'];
        }
        if(is_numeric($this->post_vars['independence_search_input'])){
            $data['where'] .= ' AND applicant.IsIndependent = '.$this->post_vars['independence_search_input'];
        }
        if(!empty($this->post_vars['college_search_input'])){
            $data['where'] .= ' AND applicant.CollegeId = '.$this->post_vars['college_search_input'];
        }
        if(!empty($this->post_vars['major_search_input'])){
            $data['where'] .= ' AND applicant.MajorId = '.$this->post_vars['major_search_input'];
        }
        if(!empty($this->post_vars['educational_attainment_input'])){
            $data['where'] .= ' AND applicant.EducationAttainmentId = '.$this->post_vars['educational_attainment_input'];
        }
        if(is_numeric($this->post_vars['complete_search_input'])){
            $data['where'] .= ' AND applicant.IsComplete = '.$this->post_vars['complete_search_input'];
        }
        if(is_numeric($this->post_vars['transcript_search_input'])){
            $data['where'] .= ' AND applicant.TranscriptOK = '.$this->post_vars['transcript_search_input'];
        }
        if(is_numeric($this->post_vars['resume_search_input'])){
            $data['where'] .= ' AND applicant.ResumeOK = '.$this->post_vars['resume_search_input'];
        }
        if(is_numeric($this->post_vars['sar_search_input'])){
            $data['where'] .= ' AND applicant.FinancialAidOK = '.$this->post_vars['sar_search_input'];
        }
        if(is_numeric($this->post_vars['firstgen_search_input'])){
            $data['where'] .= ' AND applicant.FirstGenerationStudent = '.$this->post_vars['firstgen_search_input'];
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
        if(isset($this->post_vars['gradyear_range_search_input_start']) || isset($this->post_vars['gradyear_range_search_input_end'])) {
            if ($this->post_vars['gradyear_range_search_input_start'] != date('Y')-20 || $this->post_vars['gradyear_range_search_input_end'] != date('Y')) {
                $data['where'] .= ' AND (YEAR(applicant.HighSchoolGraduationDate) >= ' . $this->post_vars['gradyear_range_search_input_start'] . ' AND YEAR(applicant.HighSchoolGraduationDate) <= ' . $this->post_vars['gradyear_range_search_input_end'] . ')';
            }
        }
        if(isset($this->post_vars['gpa_range_search_input_start']) || isset($this->post_vars['gpa_range_search_input_end'])){
            if($this->post_vars['gpa_range_search_input_start']!=0 || $this->post_vars['gpa_range_search_input_end']!=100){
                $data['where'] .= ' AND (applicant.HighSchoolGPA >= '.$this->post_vars['gpa_range_search_input_start'].' AND applicant.HighSchoolGPA <= '.$this->post_vars['gpa_range_search_input_end'].')';
            }
        }
        if(isset($this->post_vars['hs_gpa_range_search_input_start']) || isset($this->post_vars['hs_gpa_range_search_input_end'])) {
            if ($this->post_vars['hs_gpa_range_search_input_start'] != 0 || $this->post_vars['hs_gpa_range_search_input_end'] != 100) {
                $data['where'] .= ' AND (applicant.HighSchoolGPA >= ' . $this->post_vars['hs_gpa_range_search_input_start'] . ' AND applicant.HighSchoolGPA <= ' . $this->post_vars['hs_gpa_range_search_input_end'] . ')';
            }
        }
        //scholarship stuff search
        if(
            is_numeric($this->post_vars['scholarship_search_input']) ||
            !empty($this->post_vars['award_date_search_input_start']) ||
            !empty($this->post_vars['award_date_search_input_end']) ||
            $this->post_vars['multischolarship_search_input'] != 0 ||
            is_numeric($this->post_vars['thankyounote_search_input']) ||
            is_numeric($this->post_vars['signed_search_input']) ||
            is_numeric($this->post_vars['award_search_input']) ||
            $this->post_vars['gpa1_range_search_input_start'] != 0 ||
            $this->post_vars['gpa1_range_search_input_end'] != 100 ||
            $this->post_vars['gpa2_range_search_input_start'] != 0 ||
            $this->post_vars['gpa2_range_search_input_end'] != 100 ||
            $this->post_vars['gpa3_range_search_input_start'] != 0 ||
            $this->post_vars['gpa3_range_search_input_end'] != 100 ||
            $this->post_vars['gpac_range_search_input_start'] != 0 ||
            $this->post_vars['gpac_range_search_input_end'] != 100
        ){
            $data['tables']['applicantscholarship'] = array('AmountAwarded','DateAwarded');
            $data['where'] .= ' AND applicantscholarship.ApplicantId = applicant.ApplicantId';
            if(is_numeric($this->post_vars['scholarship_search_input'])){
                $data['where'] .= ' AND applicantscholarship.ScholarshipId = '.$this->post_vars['scholarship_search_input'];
            }

            if(!empty($this->post_vars['award_date_search_input_start']) || !empty($this->post_vars['award_date_search_input_end'])) {
                if(!empty($this->post_vars['award_date_search_input_start'])){
                    $where[] = 'UNIX_TIMESTAMP(applicantscholarship.DateAwarded) > '.strtotime($this->post_vars['award_date_search_input_start']);
                } else {
                    $where[] = 'UNIX_TIMESTAMP(applicantscholarship.DateAwarded) > '.strtotime(get_option('csf_settings_start_date')); //replace with dates from settings
                }
                if(!empty($this->post_vars['award_date_search_input_start'])){
                    $where[] = 'UNIX_TIMESTAMP(applicantscholarship.DateAwarded) < '.strtotime($this->post_vars['award_date_search_input_end']);
                }
                $data['where'] .= ' AND '.implode(' AND ',$where);
            } else {
                $data['where'] .= ' AND UNIX_TIMESTAMP(applicantscholarship.DateAwarded) > '.strtotime(get_option('csf_settings_start_date')); //replace with dates from settings
            }
            if($this->post_vars['multischolarship_search_input'] == 2){
                $data['where'] .= ' AND applicant.ApplicantId IN (SELECT ApplicantId FROM applicantscholarship GROUP BY ApplicantId HAVING COUNT(*) > 1)';
            }
            if($this->post_vars['multischolarship_search_input'] == 1){
                $data['where'] .= ' AND applicant.ApplicantId IN (SELECT ApplicantId FROM applicantscholarship GROUP BY ApplicantId HAVING COUNT(*) = 1)';
            }

            if(is_numeric($this->post_vars['thankyounote_search_input'])){
                $data['where'] .= ' AND applicantscholarship.ThankYou = ' . $this->post_vars['thankyounote_search_input'];
            }
            if(is_numeric($this->post_vars['signed_search_input'])){
                $data['where'] .= ' AND applicantscholarship.Signed = ' . $this->post_vars['signed_search_input'];
            }
            if ($this->post_vars['gpa1_range_search_input_start'] != 0 || $this->post_vars['gpa1_range_search_input_end'] != 100) {
                $data['where'] .= ' AND (applicantscholarship.GPA1 >= ' . $this->post_vars['gpa1_range_search_input_start'] . ' AND applicantscholarship.GPA1 <= ' . $this->post_vars['gpa1_range_search_input_end'] . ')';
            }
            if ($this->post_vars['gpa2_range_search_input_start'] != 0 || $this->post_vars['gpa2_range_search_input_end'] != 100) {
                $data['where'] .= ' AND (applicantscholarship.GPA2 >= ' . $this->post_vars['gpa2_range_search_input_start'] . ' AND applicantscholarship.GPA2 <= ' . $this->post_vars['gpa2_range_search_input_end'] . ')';
            }
            if ($this->post_vars['gpa3_range_search_input_start'] != 0 || $this->post_vars['gpa3_range_search_input_end'] != 100) {
                $data['where'] .= ' AND (applicantscholarship.GPA3 >= ' . $this->post_vars['gpa3_range_search_input_start'] . ' AND applicantscholarship.GPA3 <= ' . $this->post_vars['gpa3_range_search_input_end'] . ')';
            }
            if ($this->post_vars['gpac_range_search_input_start'] != 0 || $this->post_vars['gpac_range_search_input_end'] != 100) {
                $data['where'] .= ' AND (applicantscholarship.GPAC >= ' . $this->post_vars['gpac_range_search_input_start'] . ' AND applicantscholarship.GPAC <= ' . $this->post_vars['gpac_range_search_input_end'] . ')';
            }
        }
        //need stuff search
        if(
            $this->post_vars['direct_need_search_input_start'] != 0 ||
            $this->post_vars['direct_need_search_input_end'] != 1000000 ||
            $this->post_vars['indirect_need_search_input_start'] != 0 ||
            $this->post_vars['indirect_need_search_input_end'] != 1000000
        ){
            $data['tables']['studentneed'] = array('DirectNeed','IndirectNeed');
            $data['where'] .= ' AND studentneed.ApplicantId = applicant.ApplicantId';

            if ($this->post_vars['direct_need_search_input_start'] != 0 || $this->post_vars['direct_need_search_input_end'] != 1000000) {
                $data['where'] .= ' AND (studentneed.DirectNeed >= ' . $this->post_vars['direct_need_search_input_start'] . ' AND studentneed.DirectNeed <= ' . $this->post_vars['direct_need_search_input_end'] . ')';
            }
            if ($this->post_vars['indirect_need_search_input_start'] != 0 || $this->post_vars['indirect_need_search_input_end'] != 1000000) {
                $data['where'] .= ' AND (studentneed.IndirectNeed >= ' . $this->post_vars['indirect_need_search_input_start'] . ' AND studentneed.IndirectNeed <= ' . $this->post_vars['indirect_need_search_input_end'] . ')';
            }
        }

        //payment stuff search
        if(
            !empty($this->post_vars['payment_date_search_input_start']) ||
            !empty($this->post_vars['payment_date_search_input_end']) ||
            !empty($this->post_vars['check_number_search_input_start']) ||
            !empty($this->post_vars['check_number_search_input_end'])
        ){
            $data['tables']['payment'] = array('paymentid');
            $data['where'] .= ' AND payment.ApplicantId = applicant.ApplicantId';

            if(!empty($this->post_vars['payment_date_search_input_start']) || !empty($this->post_vars['payment_date_search_input_end'])) {
                if(!empty($this->post_vars['payment_date_search_input_start'])){
                    $where[] = 'UNIX_TIMESTAMP(payment.PaymentDateTime) > '.strtotime($this->post_vars['payment_date_search_input_start']);
                } else {
                    $where[] = 'UNIX_TIMESTAMP(payment.PaymentDateTime) > '.strtotime(get_option('csf_settings_start_date')); //replace with dates from settings
                }
                if(!empty($this->post_vars['award_date_search_input_start'])){
                    $where[] = 'UNIX_TIMESTAMP(payment.PaymentDateTime) < '.strtotime($this->post_vars['payment_date_search_input_end']);
                }
                $data['where'] .= ' AND '.implode(' AND ',$where);
            }
            if (isset($this->post_vars['check_number_search_input_start']) || isset($this->post_vars['check_number_search_input_start'])) {
                $data['where'] .= ' AND (payment.CheckNumber >= ' . $this->post_vars['check_number_search_input_start'] . ' AND payment.CheckNumber <= ' . $this->post_vars['check_number_search_input_start'] . ')';
            }
        }
        $results = $this->get_result_set($data);
        //error_log('REPORT QUERY: ' . $wpdb->last_query);

        foreach ($results AS $k => $r){
            $applicant_id = $r->ApplicantId;

            $college = $agreements = $financial = $docs = array();

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

            //add scholarship info
            $scholarship['tables']['applicantscholarship'] = array('*');
            $scholarship['where'] = 'ApplicantId = '.$applicant_id.'AND AcademicYear = '.$this->post_vars['academic_year_input'];

            $scholarship_results = $this->get_result_set($scholarship);
            foreach($scholarship_results AS $sr){
                foreach($sr as $y => $z){
                    $results[$k]->$y = $z;
                }
            }

            //add payments
            $payment['tables']['payment'] = array('*');
            $payment['where'] = 'ApplicantId = '.$applicant_id;
            $payment_results = $this->get_result_set($payment);
            foreach($payment_results AS $pr){
                $results[$k]->payment[] = $pr;
            }

            //add need
            $need['tables']['studentneed'] = array('*');
            $need['where'] = 'ApplicantId = '.$applicant_id;
            $need_results = $this->get_result_set($need);
            foreach($need_results AS $nr){
                $results[$k]->need[] = $nr;
            }
        }
        //error_log(json_encode($results));
        return $results;
    }

    /*
    *  Resource Queries
    */

     public function get_all_applications(){
         global $wpdb;
         $usertable = $wpdb->prefix . 'users';
         $data['tables']['applicant'] = array('*');
         $data['where'] = 'applicant.AcademicYear = '.date("Y"); //replace with dates from settings
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

        if(empty($this->post_vars['renewal_date_search_input_start']) && empty($this->post_vars['renewal_date_search_input_end'])) {
            $data['where'] = 'renewal.AcademicYear = '.$this->post_vars['academic_year_input'];
        } else {
            if(!empty($this->post_vars['renewal_date_search_input_start'])){
                $where[] = 'UNIX_TIMESTAMP(renewal.RenewalDateTime) > '.strtotime($this->post_vars['renewal_date_search_input_start']);
            } else {
                $where[] = 'UNIX_TIMESTAMP(renewal.RenewalDateTime) > '.strtotime(get_option('csf_settings_start_date')); //replace with dates from settings
            }
            if(!empty($this->post_vars['renewal_date_search_input_end'])){
                $where[] = 'UNIX_TIMESTAMP(renewal.RenewalDateTime) < '.strtotime($this->post_vars['renewal_date_search_input_end']);
            }
            $data['where'] = implode(' AND ',$where);
        }
        if(!empty($this->post_vars['name_search_input'])) {
            //add search for name on application
            $search_terms = explode(' ',$this->post_vars['name_search_input']);
            if(count($search_terms)>1){
                $fullnamesearch = ' OR (renewal.FirstName LIKE \'%'. $search_terms[0] .'%\' AND renewal.LastName LIKE \'%'. $search_terms[1] .'%\')';
            }
            $data['where'] .= ' AND (renewal.FirstName LIKE \'%'. $this->post_vars['name_search_input'] .'%\' OR renewal.LastName LIKE \'%'. $this->post_vars['name_search_input'] .'%\''.$fullnamesearch.') ';
        }
        if(!empty($this->post_vars['email_search_input'])) {
            //add search for an email on application
            $data['where'] .= ' AND renewal.Email  LIKE \'%'.$this->post_vars['email_search_input'].'%\'';
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


        if($this->post_vars['gpac_range_search_input_start']!=0 || $this->post_vars['gpac_range_search_input_end']!=100){
            $data['where'] .= ' AND (renewal.CurrentCumulativeGPA >= '.$this->post_vars['gpac_range_search_input_start'].' AND renewal.CurrentCumulativeGPA <= '.$this->post_vars['gpac_range_search_input_end'].')';
        }
        if(!empty($this->post_vars['major_search_input'])){
            $data['where'] .= ' AND renewal.MajorId = '.$this->post_vars['major_search_input'];
        }
        if(!empty($this->post_vars['college_search_input'])){
            $data['where'] .= ' AND renewal.CollegeId = '.$this->post_vars['college_search_input'];
        }


        if(!empty($this->post_vars['studentid_search_input'])){
            $data['where'] .= ' AND renewal.StudentId LIKE \'%'.$this->post_vars['studentid_search_input'].'%\'';
        }

        if(!empty($this->post_vars['ethnicity_search_input']) ||
            !empty($this->post_vars['gender_search_input']) ||
            is_numeric($this->post_vars['athlete_search_input']) ||
            is_numeric($this->post_vars['independence_search_input'])
        ) {
            $data['tables']['applicant'] = array('*');
            $data['where'] .= ' AND renewal.ApplicantId = applicant.ApplicantId';
            if (!empty($this->post_vars['ethnicity_search_input'])) {
                $data['where'] .= ' AND applicant.EthnicityId = ' . $this->post_vars['ethnicity_search_input'];
            }
            if (!empty($this->post_vars['gender_search_input'])) {
                $data['where'] .= ' AND applicant.SexId LIKE \'%' . $this->post_vars['gender_search_input'] . '%\'';
            }
            if (is_numeric($this->post_vars['athlete_search_input'])) {
                $data['where'] .= ' AND applicant.PlayedHighSchoolSports = ' . $this->post_vars['athlete_search_input'];
            }
            if (is_numeric($this->post_vars['independence_search_input'])) {
                $data['where'] .= ' AND applicant.IsIndependent = ' . $this->post_vars['independence_search_input'];
            }
        }

        //scholarship stuff search
        if(
            is_numeric($this->post_vars['scholarship_search_input']) ||
            !empty($this->post_vars['award_date_search_input_start']) ||
            !empty($this->post_vars['award_date_search_input_end']) ||
            $this->post_vars['multischolarship_search_input'] != 0 ||
            is_numeric($this->post_vars['thankyounote_search_input']) ||
            is_numeric($this->post_vars['signed_search_input']) ||
            is_numeric($this->post_vars['award_search_input']) ||
            $this->post_vars['gpa1_range_search_input_start'] != 0 ||
            $this->post_vars['gpa1_range_search_input_end'] != 100 ||
            $this->post_vars['gpa2_range_search_input_start'] != 0 ||
            $this->post_vars['gpa2_range_search_input_end'] != 100 ||
            $this->post_vars['gpa3_range_search_input_start'] != 0 ||
            $this->post_vars['gpa3_range_search_input_end'] != 100 ||
            $this->post_vars['gpac_range_search_input_start'] != 0 ||
            $this->post_vars['gpac_range_search_input_end'] != 100
        ){
            $data['tables']['applicantscholarship'] = array('AmountAwarded','DateAwarded');
            $data['where'] .= ' AND applicantscholarship.ApplicantId = renewal.ApplicantId AND AcademicYear = '.$this->post_vars['academic_year_input'];

            if(!empty($this->post_vars['award_date_search_input_start']) || !empty($this->post_vars['award_date_search_input_end'])) {
                if(!empty($this->post_vars['award_date_search_input_start'])){
                    $where[] = 'UNIX_TIMESTAMP(applicantscholarship.DateAwarded) > '.strtotime($this->post_vars['award_date_search_input_start']);
                } else {
                    $where[] = 'UNIX_TIMESTAMP(applicantscholarship.DateAwarded) > '.strtotime(get_option('csf_settings_start_date')); //replace with dates from settings
                }
                if(!empty($this->post_vars['award_date_search_input_start'])){
                    $where[] = 'UNIX_TIMESTAMP(applicantscholarship.DateAwarded) < '.strtotime($this->post_vars['award_date_search_input_end']);
                }
                $data['where'] .= ' AND '.implode(' AND ',$where);
            } else {
                $data['where'] .= ' AND UNIX_TIMESTAMP(applicantscholarship.DateAwarded) > '.strtotime(get_option('csf_settings_start_date')); //replace with dates from settings
            }

            if($this->post_vars['multischolarship_search_input'] == 2){
                $data['where'] .= ' AND renewal.ApplicantId IN (SELECT ApplicantId FROM applicantscholarship GROUP BY ApplicantId HAVING COUNT(*) > 1)';
            }
            if($this->post_vars['multischolarship_search_input'] == 1){
                $data['where'] .= ' AND renewal.ApplicantId IN (SELECT ApplicantId FROM applicantscholarship GROUP BY ApplicantId HAVING COUNT(*) = 1)';
            }
            if(is_numeric($this->post_vars['thankyounote_search_input'])){
                $data['where'] .= ' AND applicantscholarship.ThankYou = ' . $this->post_vars['thankyounote_search_input'];
            }
            if(is_numeric($this->post_vars['signed_search_input'])){
                $data['where'] .= ' AND applicantscholarship.Signed = ' . $this->post_vars['signed_search_input'];
            }
            if ($this->post_vars['gpa1_range_search_input_start'] != 0 || $this->post_vars['gpa1_range_search_input_end'] != 100) {
                $data['where'] .= ' AND (applicantscholarship.GPA1 >= ' . $this->post_vars['gpa1_range_search_input_start'] . ' AND applicantscholarship.GPA1 <= ' . $this->post_vars['gpa1_range_search_input_end'] . ')';
            }
            if ($this->post_vars['gpa2_range_search_input_start'] != 0 || $this->post_vars['gpa2_range_search_input_end'] != 100) {
                $data['where'] .= ' AND (applicantscholarship.GPA2 >= ' . $this->post_vars['gpa2_range_search_input_start'] . ' AND applicantscholarship.GPA2 <= ' . $this->post_vars['gpa2_range_search_input_end'] . ')';
            }
            if ($this->post_vars['gpa3_range_search_input_start'] != 0 || $this->post_vars['gpa3_range_search_input_end'] != 100) {
                $data['where'] .= ' AND (applicantscholarship.GPA3 >= ' . $this->post_vars['gpa3_range_search_input_start'] . ' AND applicantscholarship.GPA3 <= ' . $this->post_vars['gpa3_range_search_input_end'] . ')';
            }
            if ($this->post_vars['gpac_range_search_input_start'] != 0 || $this->post_vars['gpac_range_search_input_end'] != 100) {
                $data['where'] .= ' AND (applicantscholarship.GPAC >= ' . $this->post_vars['gpac_range_search_input_start'] . ' AND applicantscholarship.GPAC <= ' . $this->post_vars['gpac_range_search_input_end'] . ')';
            }
        }
        //need stuff search
        if(
            $this->post_vars['direct_need_search_input_start'] != 0 ||
            $this->post_vars['direct_need_search_input_end'] != 1000000 ||
            $this->post_vars['indirect_need_search_input_start'] != 0 ||
            $this->post_vars['indirect_need_search_input_end'] != 1000000
        ){
            $data['tables']['studentneed'] = array('DirectNeed','IndirectNeed');
            $data['where'] .= ' AND studentneed.ApplicantId = renewal.ApplicantId';

            if ($this->post_vars['direct_need_search_input_start'] != 0 || $this->post_vars['direct_need_search_input_end'] != 1000000) {
                $data['where'] .= ' AND (studentneed.DirectNeed >= ' . $this->post_vars['direct_need_search_input_start'] . ' AND studentneed.DirectNeed <= ' . $this->post_vars['direct_need_search_input_end'] . ')';
            }
            if ($this->post_vars['indirect_need_search_input_start'] != 0 || $this->post_vars['indirect_need_search_input_end'] != 1000000) {
                $data['where'] .= ' AND (studentneed.IndirectNeed >= ' . $this->post_vars['indirect_need_search_input_start'] . ' AND studentneed.IndirectNeed <= ' . $this->post_vars['indirect_need_search_input_end'] . ')';
            }
        }

        //payment stuff search
        if(
            !empty($this->post_vars['payment_date_search_input_start']) ||
            !empty($this->post_vars['payment_date_search_input_end']) ||
            !empty($this->post_vars['check_number_search_input_start']) ||
            !empty($this->post_vars['check_number_search_input_end'])
        ){
            $data['tables']['payment'] = array('paymentid');
            $data['where'] .= ' AND payment.ApplicantId = renewal.ApplicantId';

            if(!empty($this->post_vars['payment_date_search_input_start']) || !empty($this->post_vars['payment_date_search_input_end'])) {
                if(!empty($this->post_vars['payment_date_search_input_start'])){
                    $where[] = 'UNIX_TIMESTAMP(payment.PaymentDateTime) > '.strtotime($this->post_vars['payment_date_search_input_start']);
                } else {
                    $where[] = 'UNIX_TIMESTAMP(payment.PaymentDateTime) > '.strtotime(get_option('csf_settings_start_date')); //replace with dates from settings
                }
                if(!empty($this->post_vars['award_date_search_input_start'])){
                    $where[] = 'UNIX_TIMESTAMP(payment.PaymentDateTime) < '.strtotime($this->post_vars['payment_date_search_input_end']);
                }
                $data['where'] .= ' AND '.implode(' AND ',$where);
            }
            if (isset($this->post_vars['check_number_search_input_start']) || isset($this->post_vars['check_number_search_input_start'])) {
                $data['where'] .= ' AND (payment.CheckNumber >= ' . $this->post_vars['check_number_search_input_start'] . ' AND payment.CheckNumber <= ' . $this->post_vars['check_number_search_input_start'] . ')';
            }
        }

        $results = $this->get_result_set($data);
        //error_log('RENEWAL REPORT QUERY: ' . $wpdb->last_query);
        return $results;
    }



    function get_user_application_status(){
        global $current_user,$applicant_id,$wpdb;
        if(!$applicant_id){$applicant_id = $this->get_applicant_id($current_user->ID);}
        $sql = "SELECT * FROM applicationprocess WHERE applicationprocess.ApplicantId = ".$applicant_id ." ORDER BY applicationprocess.ProcessStepId DESC";
        //error_log($sql);
        $result = $wpdb->get_results($sql);
        return $result[0]->ProcessStepId;
    }

    function get_applicant_id($user_id,$academic_year = FALSE){
        if(!$academic_year){$academic_year = date("Y");}
        global $wpdb;
        $sql = "SELECT ApplicantId FROM renewal WHERE UserId = ". $user_id ." AND AcademicYear = " . $academic_year . " LIMIT 1;";
        $result = $wpdb->get_results($sql);
        if(!$result[0]->ApplicantId) {
            $sql = "SELECT ApplicantId FROM applicant WHERE UserId = " . $user_id . " AND AcademicYear = " . $academic_year . " LIMIT 1;";
            //error_log($sql);
            $result = $wpdb->get_results($sql);
        }
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


    function get_student_data($applicant_id,$academic_year = FALSE){
         if(!$academic_year){$academic_year = date("Y");}
        $personal['tables']['Applicant'] = array('*');
        $personal['where'] = 'applicant.ApplicantId = ' . $applicant_id;

        $recommend['tables']['recommend'] = array('*');
        $recommend['where'] = 'recommend.ApplicantId = ' . $applicant_id;

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
        $renewal['where'] = 'renewal.ApplicantId = '.$applicant_id.' AND renewal.AcademicYear = '.$academic_year;

        $need['tables']['studentneed'] = array('*');
        $need['where'] = 'studentneed.ApplicantId = '.$applicant_id;

        $payment['tables']['payment'] = array('*');
        $payment['where'] = 'payment.ApplicantId = '.$applicant_id;

        $scholarship['tables']['scholarship'] = array('*');
        $scholarship['tables']['applicantscholarship'] = array('*');
        $scholarship['where'] = 'applicantscholarship.ApplicantId = '.$applicant_id.' AND applicantscholarship.AcademicYear = '.$academic_year.' AND scholarship.ScholarshipId = applicantscholarship.ScholarshipId ORDER BY applicantscholarship.DateAwarded';
        $queries = array('personal','recommend','independence','financial','agreements','docs','renewal','need','payment','scholarship');
        foreach($queries AS $query){
            $result_array = $this->get_result_set(${$query});
            switch($query){
                case 'payment':
                    foreach($result_array AS $ra){
                        $results[$query][$ra->AwardId][$ra->paymentkey] = $ra;
                    }
                    break;
                case 'scholarship':
                case 'recommend':
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

    public function get_other_school($applicant_id)
    {
        $data['tables']['renewal'] = array('CollegeId');
        $data['where'] = 'renewal.ApplicantId = ' . $applicant_id;
        $results = $this->get_result_set($data);
        if (count($results) >= 1) {
            $college_id = $results[0]->CollegeId;
            if($college_id != 343 && $college_id != 0){
                return $this->get_college_by_id($college_id);
            } else {
                $data = array();
                $data['tables']['renewal'] = array('OtherSchool');
                $data['where'] = 'renewal.ApplicantId = ' . $applicant_id ;
                $results = $this->get_result_set($data);
                if (count($results) < 1) {
                    $data = array();
                    $data['tables']['applicant'] = array('OtherSchool');
                    $data['where'] = 'applicant.ApplicantId = ' . $applicant_id ;
                    $results = $this->get_result_set($data);
                }
                return $results[0]->OtherSchool;
            }
        } else {
            $data = array();
            $data['tables']['renewal'] = array('OtherSchool');
            $data['where'] = 'renewal.ApplicantId = ' . $applicant_id;
            $results = $this->get_result_set($data);
            if (count($results) < 1) {
                $data = array();
                $data['tables']['applicant'] = array('OtherSchool');
                $data['where'] = 'applicant.ApplicantId = ' . $applicant_id;
                $results = $this->get_result_set($data);
            }
            return $results[0]->OtherSchool;
        }
    }

    function get_major_by_id($id){
        global $wpdb;
        $sql = "SELECT MajorName FROM major WHERE MajorId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->MajorName;
    }

    function get_employer_by_id($id){
        global $wpdb;
        $sql = "SELECT employername FROM employer WHERE employerid = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->employername;
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
        $sql = "SELECT a.Type FROM highschooltype a, highschool b WHERE a.HighSchoolTypeId = b.SchoolTypeId AND b.HighSchoolId = '".$id."';";
        $result = $wpdb->get_results( $sql );
        return $result[0]->Type;
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

    function get_next_check_number(){
        global $wpdb;
        $sql = 'SELECT DISTINCT CheckNumber FROM payment ORDER BY CheckNumber DESC LIMIT 1';
        $results = $wpdb->get_results($sql);
        return $results[0]->CheckNumber+1;
    }

    function academic_year($date){
         //error_log(date('j',$date));
         if(date('n',$date)<=7){
             return (date('Y',$date) - 1);
         } else {
             return date('Y',$date);
         }
    }

    function update_user_role_award(){
         $user_id = $this->post_vars['UserId_input'];
         $user = new WP_User($user_id);
         $scholarship_id = $this->post_vars['ApplicantScholarship_ScholarshipId_new_input'];
         $scholarship = $this->get_scholarship($scholarship_id);
         if(!user_can($user,'view_award')) {
             if ($scholarship->Renewable == 1) {
                 $user->set_role('awardee');
             } else {
                 $user->set_role('nonrenewable');
             }
         }
    }

    function copy_application($applicant_id){
         global $wpdb;
         //why is this getting tripped on renewals?
        error_log('~~~~~BEGIN MYSTERY MACHINE~~~~~');
        error_log('Q: Why is copy_application getting tripped on renewals?');
        error_log('Data Passed: '. json_encode($applicant_id));
        error_log('User Data: '. json_encode(wp_get_current_user()));
        error_log('Server Data: '. json_encode($_SERVER));
        error_log('Request Data: '.json_encode($_REQUEST));
        error_log('~~~~~END MYSTERY MACHINE~~~~~');
         //get original application
        $sql = "SELECT * FROM applicant WHERE applicant.ApplicantID = ".$applicant_id.";";
        $applicant = $wpdb->get_results($sql);
        foreach($applicant[0] AS $k => $v){
            switch($k){
                case 'ApplicantId':
                case 'ApplicationDateTime':
                    break;
                case 'AcademicYear':
                    $values[] = $k.' = '.date('Y');
                    break;
                case 'AppliedBefore':
                    $values[] = $k.' = 1';
                    break;
                case 'ApplicationlLocked':
                case 'InformationSharingAllowed':
                case 'IsComplete':
                case 'ResumeOK':
                case 'TranscriptOK':
                case 'FinancialAidOK':
                case 'FAFSAOK':
                    $values[] = $k.' = 0';
                    break;
                case 'HardshipNote':
                case 'Notes':
                case 'Signature':
                    $values[] = $wpdb->prepare("$k = %s",array(''));
                    break;
                default:
                    $values[] = $wpdb->prepare("$k = %s",array($v));
                    break;
            }
        }
        $sql = "INSERT INTO applicant SET ".implode(',',$values).";";
        $wpdb->query($sql);
        $new_applicant_id = $wpdb->insert_id;
        //copy applicant financial
        $sql = "SELECT * FROM applicantfinancial WHERE applicantfinancial.ApplicantID = ".$applicant_id.";";
        $applicantfinancial = $wpdb->get_results($sql);
        foreach($applicantfinancial[0] AS $k => $v){
            switch($k){
                case 'FinancialId':
                    break;
                case 'ApplicantId':
                    $values[] = $k.' = '. $new_applicant_id ;
                    break;
                default:
                    $values[] = $wpdb->prepare("$k = %s",array($v));
                    break;
            }
        }

        $sql = "INSERT INTO applicantfinancial SET ".implode(',',$values).";";
        $wpdb->query($sql);
        //copy applicantindependencequery
        $sql = "SELECT * FROM applicantindependencequery WHERE applicantfinancial.ApplicantID = ".$applicant_id.";";
        $applicantindependencequery = $wpdb->get_results($sql);
        foreach($applicantindependencequery[0] AS $k => $v){
            switch($k){
                case 'ApplicantId':
                    $values[] = $k.' = '. $new_applicant_id;
                    break;
                default:
                    $values[] = $wpdb->prepare("$k = %s",array($v));
                    break;
            }
        }
        $sql = "INSERT INTO applicantindependencequery SET ".implode(',',$values).";";
        $wpdb->query($sql);

        //copy guardian
        $sql = "SELECT * FROM guardian WHERE applicantfinancial.ApplicantID = ".$applicant_id.";";
        $guardian = $wpdb->get_results($sql);
        foreach($guardian[0] AS $k => $v){
            switch($k){
                case 'GuardianId':
                    break;
                case 'ApplicantId':
                    $values[] = $k.' = '. $new_applicant_id;
                    break;
                default:
                    $values[] = $wpdb->prepare("$k = %s",array($v));
                    break;
            }
        }
        $sql = "INSERT INTO guardian SET ".implode(',',$values).";";
        $wpdb->query($sql);

        return $new_applicant_id;
        }

}