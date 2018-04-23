<?php
class MSDBootstrapShortcodes{
    /**
         * A reference to an instance of this class.
         */
        private static $instance;


        /**
         * Returns an instance of this class. 
         */
        public static function get_instance() {

                if( null == self::$instance ) {
                        self::$instance = new MSDBootstrapShortcodes();
                } 

                return self::$instance;

        } 
        
    /**
     * Initializes the plugin by setting filters and administration functions.
     */
    private function __construct() {
        add_shortcode('cols-1',array(&$this,'make_columns_shortcode'));
        add_shortcode('cols-2',array(&$this,'make_columns_shortcode'));
        add_shortcode('cols-3',array(&$this,'make_columns_shortcode'));
        add_shortcode('cols-4',array(&$this,'make_columns_shortcode'));
        add_shortcode('cols-5',array(&$this,'make_columns_shortcode'));
        add_shortcode('cols-6',array(&$this,'make_columns_shortcode'));
        add_shortcode('cols-7',array(&$this,'make_columns_shortcode'));
        add_shortcode('cols-8',array(&$this,'make_columns_shortcode'));
        add_shortcode('cols-9',array(&$this,'make_columns_shortcode'));
        add_shortcode('cols-10',array(&$this,'make_columns_shortcode'));
        add_shortcode('cols-11',array(&$this,'make_columns_shortcode'));
        add_shortcode('cols-12',array(&$this,'make_columns_shortcode'));
        add_filter('the_content',array(&$this,'wrap_up_columns'), 1);
    }
    
    function make_columns_shortcode($atts, $content = null, $shortcode_name){
        $atts = shortcode_atts( array(
            'classes' => '',
            'sm' => '12',
        ), $atts );
        preg_match('/\d+/i',$shortcode_name, $matches);
        $count = $matches[0];
        $content = do_shortcode($content);
        $ret = '<div class="col-md-'.$count.' col-sm-'.$atts['sm'].' '.$classes.'">'.$content.'</div>';
        return $ret;
    }

    function wrap_up_columns($content){
        if ( stripos( $content, '[cols-' ) !== false ) {
            $content = '<div class="row">'.$content.'</div>';
        }
        return $content;
    }

}