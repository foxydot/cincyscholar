<?php
/*
* A useful troubleshooting function. Displays arrays in an easy to follow format in a textarea.
*/
if(!function_exists('ts_data')){
    function ts_data($data){
        $ret = '<textarea class="troubleshoot" rows="20" cols="100">';
        $ret .= print_r($data,true);
        $ret .= '</textarea>';
        print $ret;
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

//add_action('genesis_footer','my_msdlab_trace_actions');
if(!function_exists('my_msdlab_trace_actions')) {
    function my_msdlab_trace_actions()
    {
        global $wp_filter;
        global $allowedposttags;
        ts_var($wp_filter['genesis_entry_header']);
    }
}
