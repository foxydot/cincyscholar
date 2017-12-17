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
     public function set_data(){
         if(empty($this->post_vars)){
            return false;
         }
         $nonce = $_POST['_wpnonce'];
         if(!wp_verify_nonce( $nonce, 'csf_settings' )) {
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
    
    /**
     * Create the full result set
     *
     * @return $array The parsed result set.
     */
    public function get_result_set($fields = array()){
        global $wpdb;
        $this->__construct();
        //ts_data($this->post_vars);
        //setup initial args
        $active_users = array(
            'meta_query' => array(
                array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'ja_disable_user',
                        'value'   => '1',
                        'compare' => '!='
                    ),
                    array(
                        'key'     => 'ja_disable_user',
                        'compare' => 'NOT EXISTS'
                    ),
                ),
            )
        );
        $user_args = array();

        //get full set
        if(empty($this->post_vars)){
            $user_args['role__in'] = array('s2member_level5','s2member_level6','s2member_level7','s2member_level8',);
            $user_args2['role__in'] = array('s2member_level5','s2member_level6','s2member_level7','s2member_level8',);
        }
        if(!empty($this->post_vars['member_search_input'])) {
            $user_args['search'] = '*'.$this->post_vars['member_search_input'].'*';
            $user_args2['meta_query'][] = array(
                'relation' => 'OR',
                array(
                    'key'     => 'first_name',
                    'value'   => $this->post_vars['member_search_input'],
                    'compare' => 'LIKE'
                ),
                array(
                    'key'     => 'last_name',
                    'value'   => $this->post_vars['member_search_input'],
                    'compare' => 'LIKE'
                ),
                array(
                    'key'     => 'nickname',
                    'value'   => $this->post_vars['member_search_input'],
                    'compare' => 'LIKE'
                ),
            );
        }
        if(!empty($this->post_vars['role_search_input'])) {
            switch($this->post_vars['role_search_input']){
                case 'A':
                    $user_args['role'] = 'Administrator';
                    $user_args2['role'] = 'Administrator';
                    break;
                case '5-8':
                    $user_args['role__in'] = array('s2member_level5','s2member_level6','s2member_level7','s2member_level8',);
                    $user_args2['role__in'] = array('s2member_level5','s2member_level6','s2member_level7','s2member_level8',);
                    break;
                case '2-4':
                    $user_args['role__in'] = array('s2member_level2','s2member_level3','s2member_level4',);
                    $user_args2['role__in'] = array('s2member_level2','s2member_level3','s2member_level4',);
                    break;
                case '1':
                    $user_args['role'] = 'Subscriber';
                    $user_args2['role'] = 'Subscriber';
                    break;
            }
        }
        if(!empty($this->post_vars['date_search_type_input'])) {
            switch($this->post_vars['date_search_type_input']){
                //don't forget to add the hangers on to the forms to add the metas
                case 'application':
                    $date_meta = array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'date_application',
                            'value'   => date('Y-m-d',strtotime($this->post_vars['date_search_input_start'])),
                            'compare' => '>=',
                            'type'    => 'DATE'
                        ),
                        array(
                            'key'     => 'date_application',
                            'value'   => date('Y-m-d',strtotime($this->post_vars['date_search_input_end'])),
                            'compare' => '<=',
                            'type'    => 'DATE'
                        ),
                    );
                    break;
                case 'renewal':
                    $date_meta = array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'date_last_renewed',
                            'value'   => date('Y-m-d',strtotime($this->post_vars['date_search_input_start'])),
                            'compare' => '>=',
                            'type'    => 'DATE'
                        ),
                        array(
                            'key'     => 'date_last_renewed',
                            'value'   => date('Y-m-d',strtotime($this->post_vars['date_search_input_end'])),
                            'compare' => '<=',
                            'type'    => 'DATE'
                        ),
                    );
                    break;
                case 'survey':
                    $date_meta = array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'date_last_survey',
                            'value'   => date('Y-m-d',strtotime($this->post_vars['date_search_input_start'])),
                            'compare' => '>=',
                            'type'    => 'DATE'
                        ),
                        array(
                            'key'     => 'date_last_survey',
                            'value'   => date('Y-m-d',strtotime($this->post_vars['date_search_input_end'])),
                            'compare' => '<=',
                            'type'    => 'DATE'
                        ),
                    );
                    break;
                case 'login':
                    $date_meta = array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'ninc_s2member_last_login_time',
                            'value'   => strtotime($this->post_vars['date_search_input_start']),
                            'compare' => '>=',
                            'type'    => 'NUMERIC'
                        ),
                        array(
                            'key'     => 'ninc_s2member_last_login_time',
                            'value'   => strtotime($this->post_vars['date_search_input_end']),
                            'compare' => '<=',
                            'type'    => 'NUMERIC'
                        ),
                    );
                    break;
            }
            $user_args['meta_query'][] = $date_meta;
            $user_args2['meta_query'][] = $date_meta;
        }
        $args = array_merge_recursive($user_args,$active_users);
        $user_query = new WP_User_Query($args);
        $users = $user_query->get_results();
        if(count($user_args2)>0){
            $args2 = array_merge_recursive($user_args2,$active_users);
            $user_query2 = new WP_User_Query($args2);
            $users2 = $user_query2->get_results();
            $users = array_replace_recursive($users,$users2);
        }
        $users = $this->add_user_extras($users);
        $result = $users;
        return $result;
    }
}