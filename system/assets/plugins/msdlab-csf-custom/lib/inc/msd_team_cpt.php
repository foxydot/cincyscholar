<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 9/18/17
 * Time: 8:14 PM
 */
if (!class_exists('MSDTeamCPT')) {
    class MSDTeamCPT {
        //Properties
        var $cpt = 'team';
        //Methods
        /**
         * PHP 4 Compatible Constructor
         */
        public function MSDTeamCPT(){$this->__construct();}

        /**
         * PHP 5 Constructor
         */
        function __construct(){
            global $current_screen;
            //Actions
            add_action( 'init', array(&$this,'register_taxonomies') );
            add_action( 'init', array(&$this,'register_cpt') );
            add_action( 'init', array(&$this,'register_metaboxes') );
            //add_action('admin_head', array(&$this,'plugin_header'));
            add_action('admin_print_scripts', array(&$this,'add_admin_scripts') );
            add_action('admin_print_styles', array(&$this,'add_admin_styles') );
            add_action('admin_footer',array(&$this,'info_footer_hook') );
            // important: note the priority of 99, the js needs to be placed after tinymce loads
            add_action('admin_print_footer_scripts',array(&$this,'print_footer_scripts'),99);
            //add_action('template_redirect', array(&$this,'my_theme_redirect'));
            add_action('wp_head',array(&$this,'cpt_display'));

            //add_action('admin_head', array(&$this,'codex_custom_help_tab'));

            //Filters
            //add_filter( 'pre_get_posts', array(&$this,'custom_query') );
            add_filter( 'enter_title_here', array(&$this,'change_default_title') );

            add_shortcode('team',array(&$this,'shortcode_handler'));

        }


        function register_taxonomies(){

            $labels = array(
                'name' => _x( 'Team categories', 'team-category' ),
                'singular_name' => _x( 'Team category', 'team-category' ),
                'search_items' => _x( 'Search team categories', 'team-category' ),
                'popular_items' => _x( 'Popular team categories', 'team-category' ),
                'all_items' => _x( 'All team categories', 'team-category' ),
                'parent_item' => _x( 'Parent team category', 'team-category' ),
                'parent_item_colon' => _x( 'Parent team category:', 'team-category' ),
                'edit_item' => _x( 'Edit team category', 'team-category' ),
                'update_item' => _x( 'Update team category', 'team-category' ),
                'add_new_item' => _x( 'Add new team category', 'team-category' ),
                'new_item_name' => _x( 'New team category name', 'team-category' ),
                'separate_items_with_commas' => _x( 'Separate team categories with commas', 'team-category' ),
                'add_or_remove_items' => _x( 'Add or remove team categories', 'team-category' ),
                'choose_from_most_used' => _x( 'Choose from the most used team categories', 'team-category' ),
                'menu_name' => _x( 'Team categories', 'team-category' ),
            );

            $args = array(
                'labels' => $labels,
                'public' => true,
                'show_in_nav_menus' => true,
                'show_ui' => true,
                'show_tagcloud' => false,
                'hierarchical' => true, //we want a "category" style taxonomy, but may have to restrict selection via a dropdown or something.

                'rewrite' => array('slug'=>'team-category','with_front'=>false),
                'query_var' => true
            );

            register_taxonomy( 'team_category', array($this->cpt), $args );
        }

        function register_cpt() {

            $labels = array(
                'name' => _x( 'Team Members', 'team' ),
                'singular_name' => _x( 'Team Member', 'team' ),
                'add_new' => _x( 'Add New', 'team' ),
                'add_new_item' => _x( 'Add New Team Member', 'team' ),
                'edit_item' => _x( 'Edit Team Member', 'team' ),
                'new_item' => _x( 'New Team Member', 'team' ),
                'view_item' => _x( 'View Team Member', 'team' ),
                'search_items' => _x( 'Search Team Members', 'team' ),
                'not_found' => _x( 'No team members found', 'team' ),
                'not_found_in_trash' => _x( 'No team members found in Trash', 'team' ),
                'parent_item_colon' => _x( 'Parent Team:', 'team' ),
                'menu_name' => _x( 'Team Members', 'team' ),
            );

            $args = array(
                'labels' => $labels,
                'hierarchical' => false,
                'description' => 'Team',
                'supports' => array( 'title', 'editor', 'thumbnail' ),
                'taxonomies' => array( 'team_category' ),
                'public' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'menu_position' => 20,

                'show_in_nav_menus' => true,
                'publicly_queryable' => true,
                'exclude_from_search' => true,
                'has_archive' => true,
                'query_var' => true,
                'can_export' => true,
                'rewrite' => array('slug'=>'team','with_front'=>false),
                'capability_type' => 'post',
                'menu_icon' => 'dashicons-groups',
            );

            register_post_type( $this->cpt, $args );
        }


        function register_metaboxes(){
            global $team_info,$contact_info;
            $team_info = new WPAlchemy_MetaBox(array
            (
                'id' => '_team_information',
                'title' => 'Team Member Info',
                'types' => array($this->cpt),
                'context' => 'normal',
                'priority' => 'high',
                'template' => plugin_dir_path(dirname(__FILE__)).'template/metabox-team.php',
                'autosave' => TRUE,
                'mode' => WPALCHEMY_MODE_EXTRACT, // defaults to WPALCHEMY_MODE_ARRAY
                'prefix' => '_team_' // defaults to NULL
            ));
            $contact_info = new WPAlchemy_MetaBox(array
            (
                'id' => '_contact_information',
                'title' => 'Contact Info',
                'types' => array($this->cpt),
                'context' => 'normal',
                'priority' => 'high',
                'template' => plugin_dir_path(dirname(__FILE__)).'template/metabox-contact.php',
                'autosave' => TRUE,
                'mode' => WPALCHEMY_MODE_EXTRACT, // defaults to WPALCHEMY_MODE_ARRAY
                'prefix' => '_contact_' // defaults to NULL
            ));
        }


        function add_admin_scripts() {
            global $current_screen;
            if($current_screen->post_type == $this->cpt){
            }
        }

        function add_admin_styles() {
            global $current_screen;
            if($current_screen->post_type == $this->cpt){
                wp_enqueue_style('custom_meta_css',plugin_dir_url(dirname(__DIR__)).'css/meta.css');
            }
        }

        function print_footer_scripts()
        {
            global $current_screen;
            if($current_screen->post_type == $this->cpt){
                print '<script type="text/javascript">/* <![CDATA[ */
					jQuery(function($)
					{
						var i=1;
						$(\'.customEditor textarea\').each(function(e)
						{
							var id = $(this).attr(\'id\');
			 
							if (!id)
							{
								id = \'customEditor-\' + i++;
								$(this).attr(\'id\',id);
							}
			 
							tinyMCE.execCommand(\'mceAddControl\', false, id);
			 
						});
							$(\'#postdivrich\').prepend(\'<h2>Team Member Bio</h2>\');
					});
				/* ]]> */</script>';
            }
        }

        function info_footer_hook()
        {
            global $current_screen;
            if($current_screen->post_type == $this->cpt){
                ?><script type="text/javascript">
                    jQuery('#postdivrich').before(jQuery('#_contact_information_metabox'));
                    jQuery('#_contact_information_metabox').before(jQuery('#_team_information_metabox'));
                </script><?php
            }
        }


        function my_theme_redirect() {
            global $wp;

            //A Specific Custom Post Type
            if ($wp->query_vars["post_type"] == $this->cpt) {
                if(is_single()){
                    $templatefilename = 'single-'.$this->cpt.'.php';
                    if (file_exists(STYLESHEETPATH . '/' . $templatefilename)) {
                        $return_template = STYLESHEETPATH . '/' . $templatefilename;
                    } else {
                        $return_template = plugin_dir_path(dirname(__DIR__)). 'template/' . $templatefilename;
                    }
                    do_theme_redirect($return_template);

                    //A Custom Taxonomy Page
                } elseif ($wp->query_vars["taxonomy"] == 'team_category') {
                    $templatefilename = 'taxonomy-team_category.php';
                    if (file_exists(STYLESHEETPATH . '/' . $templatefilename)) {
                        $return_template = STYLESHEETPATH . '/' . $templatefilename;
                    } else {
                        $return_template = plugin_dir_path(dirname(__DIR__)) . 'template/' . $templatefilename;
                    }
                    do_theme_redirect($return_template);
                }
            }
        }

        function codex_custom_help_tab() {
            global $current_screen;
            if($current_screen->post_type != $this->cpt)
                return;

            // Setup help tab args.
            $args = array(
                'id'      => 'title', //unique id for the tab
                'title'   => 'Title', //unique visible title for the tab
                'content' => '<h3>The Event Title</h3>
                          <p>The title of the event.</p>
                          <h3>The Permalink</h3>
                          <p>The permalink is created by the title, but it doesn\'t change automatically if you change the title. To change the permalink when editing an event, click the [Edit] button next to the permalink. 
                          Remove the text that becomes editable and click [OK]. The permalink will repopulate with the new Location and date!</p>
                          ',  //actual help text
            );

            // Add the help tab.
            $current_screen->add_help_tab( $args );

            // Setup help tab args.
            $args = array(
                'id'      => 'event_info', //unique id for the tab
                'title'   => 'Event Info', //unique visible title for the tab
                'content' => '<h3>Event URL</h3>
                          <p>The link to the page describing the event</p>
                          <h3>The Event Date</h3>
                          <p>The Event Date is the date of the event. This value is restrained to dates (chooseable via a datepicker module). This value is also used to sort events for the calendars, upcoming events, etc.</p>
                          <p>For single day events, set start and end date to the same date.',  //actual help text
            );

            // Add the help tab.
            $current_screen->add_help_tab( $args );

        }


        function custom_query( $query ) {
            if(!is_admin()){
                if($query->is_main_query()) {
                    $post_types = $query->get('post_type');             // Get the current post types in the query

                    if(!is_array($post_types) && !empty($post_types))   // Check that the current posts types are stored as an array
                        $post_types = explode(',', $post_types);

                    if(empty($post_types))
                        $post_types = array('post'); // If there are no post types defined, be sure to include posts so that they are not ignored

                    if ($query->is_search()) {
                        $searchterm = $query->query_vars['s'];
                        if ($searchterm != '') {
                            $query->set('meta_value', $searchterm);
                            $query->set('meta_compare', 'LIKE');
                        };

                        $post_types[] = $this->cpt;                         // Add your custom post type

                    } elseif ($query->is_archive()) {
                        $post_types[] = $this->cpt;                         // Add your custom post type
                    }

                    $post_types = array_map('trim', $post_types);       // Trim every element, just in case
                    $post_types = array_filter($post_types);            // Remove any empty elements, just in case
                    $query->set('post_type', $post_types);              // Add the updated list of post types to your query
                }
            }
            return $query;
        }

        function change_default_title( $title ){
            global $current_screen;
            if  ( $current_screen->post_type == $this->cpt ) {
                return __('Team Member Name','team');
            } else {
                return $title;
            }
        }

        function cpt_display(){
            global $post;
            if(is_cpt($this->cpt)) {
                if (is_single()){
                    //display content here
                    add_action('genesis_before_entry_content',array(&$this,'single_pre_content'));

                } else {
                    //display for aggregate here
                }
            }
        }

        function single_pre_content(){
            global $post,$team_info,$contact_info;
            $headshot = get_the_post_thumbnail($post->ID,'headshot-lg',array( 'class' => 'alignleft' ));
            $team_info->the_meta($post->ID);
            $jobtitle = $team_info->get_the_value('jobtitle');
            if($jobtitle != ''){
                $jobtitle = '<h4 class="jobtitle">'.$jobtitle.'</h4>';
            }

            print $jobtitle;
            print $headshot;
        }

        function single_post_content(){

        }


        function get_all_team_members(){
            $args = array(
                'posts_per_page'   => -1,
                'orderby'          => 'title',
                'order'            => 'ASC',
                'post_type'        => $this->cpt,
            );
            $posts = get_posts($args);
            $i = 0;
            foreach($posts AS $post){
                $posts[$i]->lastname = get_post_meta($post->ID,'_team_alpha',TRUE);
                $i++;
            }
            usort($posts,array(&$this,'sort_by_lastname'));
            return $posts;
        }

        function shortcode_handler($atts){
            extract(shortcode_atts(array(
                'type' => 'grid',
            ), $atts));
            $ret = '';
            $fullteam = $this->get_all_team_members();
            foreach($fullteam AS $team) {
                $ret .= $this->team_display($team);
            }
            return '<div class="team-display-'.$type.'">'.$ret.'</div>';
        }

        function team_display($team,$attr = array()){
            global $post,$team_info,$contact_info;
            extract($attr);
            $headshot = get_the_post_thumbnail($team->ID,'headshot-lg',array( 'class' => 'addborder' ));
            $team_info->the_meta($team->ID);
            $jobtitle = $team_info->get_the_value('jobtitle');
            if($jobtitle != ''){
                $jobtitle = '<h4 class="jobtitle">'.$jobtitle.'</h4>';
            }
            $team_contact_info = '';
            $contact_info->the_meta($team->ID);
            $contact_info->the_field('_team_member_phone');
            if($contact_info->get_the_value() != ''){
                $team_contact_info .= '<li class="phone"><i class="icon-phone icon-large"></i> '.msd_str_fmt($contact_info->get_the_value(),'phone').'</li>';
            }

            $contact_info->the_field('_team_member_mobile');
            if($contact_info->get_the_value() != ''){
                $team_contact_info .= '<li class="mobile"><i class="icon-mobile-phone icon-large"></i> '.msd_str_fmt($contact_info->get_the_value(),'phone').'</li>';
            }

            $contact_info->the_field('_team_member_linked_in');
            if($contact_info->get_the_value() != ''){
                $team_contact_info .= '<li class="linkedin"><a href="'.$contact_info->get_the_value().'"><i class="icon-linkedin-sign icon-large"></i> Connect</a></li>';
            }

            $contact_info->the_field('_team_member_bio_sheet');
            if($contact_info->get_the_value() != ''){
                $team_contact_info .= '<li class="vcard"><a href="'.$contact_info->get_the_value().'"><i class="icon-download-alt icon-large"></i> Download Bio</a></li>';
            }

            $contact_info->the_field('_team_member_email');
            if($contact_info->get_the_value() != ''){
                $team_contact_info .= '<li class="email"><i class="icon-envelope-alt icon-large"></i> '.msd_str_fmt($contact_info->get_the_value(),'email').'</li>';
            }
            $teamstr = '
            <div class="team '.$team->post_name.' col-sm-6 col-xs-12">
                <div class="headshot">
                    '.$headshot.'
                </div>
                <div class="info">
                    <h3 class="name">'.$team->post_title.'</h3>
                    '.$jobtitle;
                $teamstr .= '
                        <div class="bio">'.$team->post_content.'</div>';
            $teamstr .= '
                    <ul class="team_member-contact-info">
                    '.$team_contact_info.'
                    </ul>
                </div>
            </div>';
            return $teamstr;
        }

        function sort_by_lastname( $a, $b ) {
            return $a->lastname == $b->lastname ? 0 : ( $a->lastname < $b->lastname ) ? -1 : 1;
        }

        function print_shortcode_handler(){
            print $this->shortcode_handler(array());
        }
    } //End Class
} //End if class exists statement