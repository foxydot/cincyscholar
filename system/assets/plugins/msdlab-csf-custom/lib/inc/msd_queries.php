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
             return 'no nonce';
         }
         foreach ($this->post_vars AS $k => $v){
             if(stripos($k,'_input')){
                 $option = str_replace('_input','',$k);
                 $orig = get_option($option);
                 if($v !== $orig) {
                     if (!update_option($option, $v)) {
                         return "Error updating " . $option;
                     }
                 }
             }
         }
         return "Data Updated";
     }


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

             if($r = $wpdb->get_row($select_sql)){

                 $sql = 'UPDATE '.$table.' SET '.implode(', ',$data[$table]).' WHERE '.$where[$table].';';
             } else {
                 $sql = 'INSERT INTO '.$table.' SET '.implode(', ',$data[$table]).';';
             }

             $result = $wpdb->get_results($sql);
             if(is_wp_error($result)){
                 return '<div class="error">Error updating '.$table.'</div>';
             }
        }
         return '<div class="notice">Application saved!</div>';
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
        //ts_data($sql);
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
                $ret[] = basename($fileinfo['name'])." successfully uploaded.\n";
                $filepath = $user_url.'/'.basename($fileinfo['name']);
                $sql = "INSERT INTO `Attachment` SET `ApplicantId` = '".$applicant_id."', `AttachmentTypeId` = '".$attachment_type_id."', `FilePath` = '".$filepath."';";
                $result = $wpdb->get_results($sql);
                if(is_wp_error($result)){
                    print "Error saving upload data to database.";
                    return false;
                }
            } else {
                print "Possible file upload attack!\n";
                return false;
            }
        }
        return implode("<br />\n\r",$ret);
    }

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

}