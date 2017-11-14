<?php
if (!class_exists('MSDLab_Subtitle_Support')) {
    class MSDLab_Subtitle_Support {
        //Properties
        private $options;

        //Methods
        /**
         * PHP 4 Compatible Constructor
         */
        public function MSDLab_Subtitle_Support(){$this->__construct();}

        /**
         * PHP 5 Constructor
         */
        function __construct($options){
            global $current_screen;
            //"Constants" setup
            //Actions
            add_action( 'init', array(&$this,'register_metaboxes') );
            add_action('admin_print_styles', array(&$this,'add_admin_styles') );
            add_action('admin_footer',array(&$this,'footer_hook') );
            add_action( 'genesis_entry_header', array(&$this,'msdlab_do_post_subtitle') );
            //Filters
        }

        function register_metaboxes(){
            global $subtitle_metabox;
            $subtitle_metabox = new WPAlchemy_MetaBox(array
            (
                'id' => '_subtitle',
                'title' => 'Subtitle',
                'types' => array('post','page','news','qod','resource','speaker'),
                'context' => 'normal', // same as above, defaults to "normal"
                'priority' => 'high', // same as above, defaults to "high"
                'template' => plugin_dir_path(dirname(__FILE__)).'/template/metabox-subtitle.php',
                'autosave' => TRUE,
                'mode' => WPALCHEMY_MODE_EXTRACT, // defaults to WPALCHEMY_MODE_ARRAY
                'prefix' => '_msdlab_' // defaults to NULL
            ));
        }

        function add_admin_styles() {
            wp_enqueue_style('custom_meta_css',plugin_dir_url(dirname(__FILE__)).'css/meta.css');
        }

        function footer_hook()
        {
            ?><script type="text/javascript">
                jQuery('#titlediv').after(jQuery('#_subtitle_metabox'));
            </script><?php
        }

        function msdlab_do_post_subtitle() {
            global $subtitle_metabox;
            $subtitle_metabox->the_meta();
            $subtitle = $subtitle_metabox->get_the_value('subtitle');

            if ( strlen( $subtitle ) == 0 )
                return;

            $subtitle = sprintf( '<h2 class="entry-subtitle">%s</h2>', apply_filters( 'genesis_post_title_text', $subtitle ) );
            echo apply_filters( 'genesis_post_title_output', $subtitle ) . "\n";

        }

    } //End Class
} //End if class exists statement