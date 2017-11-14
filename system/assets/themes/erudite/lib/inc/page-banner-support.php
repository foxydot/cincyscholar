<?php
if(!class_exists('WPAlchemy_MetaBox')){
    include_once (WP_CONTENT_DIR.'/wpalchemy/MetaBox.php');
}

global $wpalchemy_media_access;
if(!class_exists('WPAlchemy_MediaAccess')){
    include_once (WP_CONTENT_DIR.'/wpalchemy/MediaAccess.php');
}
$wpalchemy_media_access = new WPAlchemy_MediaAccess();

if (!class_exists('MSDLab_Page_Banner_Support')) {
    class MSDLab_Page_Banner_Support {
        //Properties
        private $options;

        //Methods
        /**
         * PHP 4 Compatible Constructor
         */
        public function MSDLab_Page_Banner_Support(){$this->__construct();}

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
            add_action('msdlab_title_area',array(&$this,'msdlab_do_page_banner') );

            //Filters
        }

        function register_metaboxes(){
            global $page_banner_metabox;
            $page_banner_metabox = new WPAlchemy_MetaBox(array
            (
                'id' => '_page_banner',
                'title' => 'Page Banner Area',
                'types' => array('post','page'),
                'context' => 'normal', // same as above, defaults to "normal"
                'priority' => 'high', // same as above, defaults to "high"
                'template' => get_stylesheet_directory().'/lib/template/metabox-page_banner.php',
                'autosave' => TRUE,
                'mode' => WPALCHEMY_MODE_EXTRACT, // defaults to WPALCHEMY_MODE_ARRAY
                'prefix' => '_msdlab_' // defaults to NULL
            ));
        }

        function add_admin_styles() {
            //wp_enqueue_style('custom_meta_css',plugin_dir_url(dirname(__FILE__)).'css/meta.css');
        }

        function footer_hook()
        {
            ?><script type="text/javascript">
            jQuery('#titlediv').after(jQuery('#_page_banner_metabox'));
        </script><?php
        }

        function msdlab_do_page_banner(){
            global $post;
            $blog_id = get_option('page_for_posts');
            if(is_page() && !is_home()) {
                global $page_banner_metabox;
                $page_banner_metabox->the_meta();
                $bannerbool = $page_banner_metabox->get_the_value('bannerbool');
                if ($bannerbool != 'true') {
                    return;
                }
                $bannerclass = $page_banner_metabox->get_the_value('bannerclass');
                $banneralign = $page_banner_metabox->get_the_value('banneralign');
                $bannerimage = $page_banner_metabox->get_the_value('bannerimage');
                $bannercontent = apply_filters('the_content', $page_banner_metabox->get_the_value('bannercontent'));
                remove_action('genesis_entry_header', 'genesis_do_post_title');
                $background = strlen($bannerimage) > 0 ? ' style="background-image:url(' . $bannerimage . ')"' : '';
                print '<div class="banner clearfix ' . $banneralign . ' ' . $bannerclass . '">';
                print '<div class="wrap"' . $background . '>';
                print '<div class="gradient">';
                print '<div class="bannertext">';
                print genesis_do_post_title();
                if($bannercontent != '') {
                    print '<div class="bannercontent">' . $bannercontent . '</div>';
                }
                print '</div>';
                print '</div>';
                print '</div>';
                print '</div>';
            } elseif(is_home() || (is_archive() && $post->post_type == "post") || (is_single() && $post->post_type == "post") ) {
                global $page_banner_metabox;
                $page_banner_metabox->the_meta($blog_id);
                $bannerbool = $page_banner_metabox->get_the_value('bannerbool');
                if ($bannerbool != 'true') {
                    return;
                }
                $bannerclass = $page_banner_metabox->get_the_value('bannerclass');
                $banneralign = $page_banner_metabox->get_the_value('banneralign');
                $bannerimage = $page_banner_metabox->get_the_value('bannerimage');
                $bannercontent = apply_filters('the_content', $page_banner_metabox->get_the_value('bannercontent'));
                remove_action('genesis_before_loop', 'genesis_do_cpt_archive_title_description');
                remove_action('genesis_before_loop', 'genesis_do_date_archive_title');
                remove_action('genesis_before_loop', 'genesis_do_blog_template_heading');
                remove_action('genesis_before_loop', 'genesis_do_posts_page_heading');
                remove_action('genesis_before_loop', 'genesis_do_taxonomy_title_description', 15);
                remove_action('genesis_before_loop', 'genesis_do_author_title_description', 15);
                remove_action('genesis_before_loop', 'genesis_do_author_box_archive', 15);
                add_filter('genesis_post_title_text', array(&$this, 'msdlab_blog_page_title'));
                $background = strlen($bannerimage) > 0 ? ' style="background-image:url(' . $bannerimage . ')"' : '';
                print '<div class="banner clearfix ' . $banneralign . ' ' . $bannerclass . '">';
                print '<div class="wrap"' . $background . '>';
                print '<div class="gradient">';
                print '<div class="bannertext">';
                print genesis_do_post_title();
                if($bannercontent != '') {
                    print '<div class="bannercontent">' . $bannercontent . '</div>';
                }
                print '</div>';
                print '</div>';
                print '</div>';
                print '</div>';
                remove_filter('genesis_post_title_text', array(&$this, 'msdlab_blog_page_title'));
            }  else {
                //genesis_do_post_title();
            }
        }

        function msdlab_blog_page_title($title){
            $blog_id = get_option( 'page_for_posts' );
            return get_the_title($blog_id);
        }

        function msdlab_calendar_page_title($title){
            $calendar = get_page_by_path('events-calendar');
            $calendar_id = $calendar->ID;
            return get_the_title($calendar_id);
    }

    } //End Class
} //End if class exists statement