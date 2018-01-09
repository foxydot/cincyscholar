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
         /*print '<br>$this->>post_vars<br>';
         ts_data($this->post_vars);*/
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
             $select_sql = 'SELECT * FROM '.$table.' WHERE '.$where[$table].';';

             /*print '<br>$select_sql '.$table.'<br>';
             ts_data($select_sql);*/

             if($r = $wpdb->get_row($select_sql)){

                 /*print '<br>$r '.$table.'<br>';
                 ts_data($r);*/

                 $sql = 'UPDATE '.$table.' SET '.implode(', ',$data[$table]).' WHERE '.$where[$table].';';
             } else {
                 $sql = 'INSERT INTO '.$table.' SET '.implode(', ',$data[$table]).';';
             }

             /*print '<br>$sql '.$table.'<br>';
             ts_data($sql);*/

             $result = $wpdb->get_results($sql);
             if(is_wp_error($result)){
                 return 'Error updating '.$table.';';
             }

             /*print '<br>$result '.$table.'<br>';
             ts_data($result);*/
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
        $result = $wpdb->get_results($sql);
        return $result;
    }

    function get_select_array_from_db($table,$id_field,$field){
        global $wpdb;
        $sql = 'SELECT `'.$id_field.'`,`'.$field.'` FROM `'.$table.'`;';
        $result = $wpdb->get_results( $sql, ARRAY_A );
        foreach ($result AS $k=>$v){
            $array[$v[$id_field]] = $v[$field];
        }

        return $array;
    }

}