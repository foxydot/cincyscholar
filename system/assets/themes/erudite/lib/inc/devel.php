<?php
/*
* A useful troubleshooting function. Displays arrays in an easy to follow format in a textarea.
*/
if(!function_exists('ts_data')){
    function ts_data($data){
        $current_user = wp_get_current_user();
        $ret = '<textarea class="troubleshoot" rows="20" cols="100">';
        $ret .= print_r($data,true);
        $ret .= '</textarea>';
        if(is_admin() && $current_user->user_login == 'msd_lab'){
            print $ret;
        }
    }
}
/*
* A useful troubleshooting function. Dumps variable info in an easy to follow format in a textarea.
*/
if(!function_exists('ts_var')){
    function ts_var($var){
        ts_data(var_export( $var , true ));
    }
}

//add_action('genesis_footer','my_msdlab_trace');
if(!function_exists('my_msdlab_trace')) {
    function my_msdlab_trace()
    {
        global $wp_filter;
        global $allowedposttags;
        global $current_user;
        ts_data($current_user);
        //ts_var($wp_filter['genesis_entry_header']);
    }
}
