<?php
class MSDSimpleSectionedPage{
    /**
         * A reference to an instance of this class.
         */
        private static $instance;


        /**
         * Returns an instance of this class. 
         */
        public static function get_instance() {

                if( null == self::$instance ) {
                        self::$instance = new MSDSimpleSectionedPage();
                } 

                return self::$instance;

        } 
        
        /**
         * Initializes the plugin by setting filters and administration functions.
         */
        private function __construct() {
            add_action('admin_footer',array(&$this,'info_footer_hook') );            
        }
    
    function sectioned_page_output(){
        wp_enqueue_script('sticky',WP_PLUGIN_URL.'/'.plugin_dir_path('msd-specialty-pages/msd-specialty-pages.php'). '/lib/js/jquery.sticky.js',array('jquery'),FALSE,TRUE);
        
        global $post,$subtitle_metabox,$sectioned_page_metabox,$nav_ids;
        $i = 1;
        if(is_object($sectioned_page_metabox)){
        while($sectioned_page_metabox->have_fields('sections')){
            $eo = $i%2==1?'even':'odd';
            $pull = $i%2==1?'left':'right';
            $title = apply_filters('the_title',$sectioned_page_metabox->get_the_value('content-area-title'));
            $wrapped_title = trim($title) != ''?'<div class="section-title">
        <h3 class="wrap">
            '.$title.'
        </h3>
    </div>':'';
            $slug = sanitize_title_with_dashes(str_replace('/', '-', $sectioned_page_metabox->get_the_value('content-area-title')));
            $subtitle = $sectioned_page_metabox->get_the_value('subtitle') !=''?'<h4 class="section-subtitle">'.apply_filters('the_content',$sectioned_page_metabox->get_the_value('content-area-subtitle')).'</h4>':'';
            if($slug=='location'){remove_filter( 'the_content', 'wpautop' );}
            $content = apply_filters('the_content',$sectioned_page_metabox->get_the_value('content-area-content'));
            if($slug=='location'){add_filter( 'the_content', 'wpautop' );}
            $image = $sectioned_page_metabox->get_the_value('content-area-image') !=''?'<img src="'.$sectioned_page_metabox->get_the_value('content-area-image').'" class="pull-'.$pull.'">':'';
            $nav_ids[] = $slug;
            $nav[] = '';
            $floating_nav[] = '<a id="'.$slug.'_fl_nav" href="#'.$slug.'"><i class="fa-3x adex-'.$slug.'"></i>'.str_replace(' ', '<br>', $title).'</a>';
            $sections[] = '
<div id="'.$slug.'" class="section section-'.$eo.' section-'.$slug.' clearfix">
    '.$wrapped_title.'
    <div class="section-body">
        <div class="wrap">
            '.$image.'
            '.$subtitle.'
            '.$content.'
        </div>
    </div>
</div>
';
            $i++;
        }//close while
        print '<div id="floating_nav" class="floating_nav">'.implode("\n",$floating_nav).'</div>';
        print implode("\n",$sections);
        
        }//clsoe if
    }

    function sectioned_page_floating_nav(){
        global $nav_ids; //http://julian.com/research/velocity/ llook at this to speed up animations
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $("#floating_nav").sticky({ topSpacing: 0 });
        });
        </script>
        <?php
    }
        function info_footer_hook()
        {
            $postid = is_admin()?$_GET['post']:$post->ID;
            $template_file = get_post_meta($postid,'_wp_page_template',TRUE);
            if($template_file == 'page-simple-sectioned.php'){
                ?><script type="text/javascript">
                        jQuery('#postdivrich').after(jQuery('#_sectioned_page_metabox'));
                    </script><?php
            }
        }
    function enqueue_admin(){}
}