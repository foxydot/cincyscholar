<?php
/*
 * Add styles and scripts
*/
add_action('wp_enqueue_scripts', 'msdlab_add_styles');
add_action('wp_enqueue_scripts', 'msdlab_add_scripts');
//add_action('admin_enqueue_scripts','msdlab_add_admin_styles');


function msdlab_add_styles() {
    global $is_IE;
    if(!is_admin()){
        //use cdn
        wp_enqueue_style('bootstrap-style','//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',false,'4.5.0');
        wp_enqueue_style('font-awesome-style','//maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css',false,'4.5.0');
        //use local
        // wp_enqueue_style('font-awesome-style',get_stylesheet_directory_uri().'/lib/font-awesome/css/font-awesome.css',array('bootstrap-style'));
        $queue[] = 'font-awesome-style';
        wp_enqueue_style('msd-style',get_stylesheet_directory_uri().'/lib/css/style.css',$queue);
        $queue[] = 'msd-style';
        if(is_front_page()){
            wp_enqueue_style('msd-homepage-style',get_stylesheet_directory_uri().'/lib/css/homepage.css',$queue);
            $queue[] = 'msd-homepage-style';
        }
        if($is_IE){
            wp_enqueue_style('ie-style',get_stylesheet_directory_uri().'/lib/css/ie.css',$queue);
            $queue[] = 'ie-style';

            wp_enqueue_style('ie8-style',get_template_directory_uri() . '/lib/css/ie8.css');
            global $wp_styles;
            $wp_styles->add_data( 'ie8-style', 'conditional', 'lte IE 8' );
        }
    }
}

function msdlab_add_scripts() {
    global $is_IE;
    if(!is_admin()){
        //use cdn
        wp_enqueue_script('bootstrap-jquery','//maxcdn.bootstrapcdn.com/bootstrap/latest/js/bootstrap.min.js',array('jquery'));
        //use local
        //wp_enqueue_script('bootstrap-jquery',get_stylesheet_directory_uri().'/lib/bootstrap/js/bootstrap.min.js',array('jquery'));
        //responsive menu
        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
        wp_enqueue_script( 'genesis-msdlab-child-responsive-menu', get_stylesheet_directory_uri() . "/lib/js/responsive-menus{$suffix}.js", array( 'jquery' ), CHILD_THEME_VERSION, true );
        wp_localize_script(
            'genesis-msdlab-child-responsive-menu',
            'genesis_responsive_menu',
            genesis_msdlab_child_responsive_menu_settings()
        );
        wp_enqueue_script('matchHeight',get_stylesheet_directory_uri().'/lib/js/jquery.matchHeight.min.js',array('jquery','bootstrap-jquery'));
        wp_enqueue_script('msd-jquery',get_stylesheet_directory_uri().'/lib/js/theme-jquery.js',array('jquery','bootstrap-jquery'));

        if($is_IE){
        }
        if(is_front_page()){
            wp_enqueue_script('msd-homepage-jquery',get_stylesheet_directory_uri().'/lib/js/homepage-jquery.js',array('jquery','bootstrap-jquery'));
        }
    }
}
function msdlab_add_admin_styles(){
    //use cdn
    //Do we actually need this? If so, look here: https://rushfrisby.com/using-bootstrap-in-wordpress-admin-panel/
    wp_enqueue_style('bootstrap-style','//maxcdn.bootstrapcdn.com/bootstrap/latest/css/bootstrap.min.css');
    wp_enqueue_style('font-awesome-style','//maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css',array('bootstrap-style'),'4.5.0');

}



// Define our responsive menu settings.
function genesis_msdlab_child_responsive_menu_settings() {

    $settings = array(
        'mainMenu'          => __( 'Menu', 'genesis-msdlab-child' ),
        'menuIconClass'     => 'dashicons-before dashicons-menu',
        'subMenu'           => __( 'Submenu', 'genesis-msdlab-child' ),
        'subMenuIconsClass' => 'dashicons-before dashicons-arrow-down-alt2',
        'menuClasses'       => array(
            'combine' => array(
                '.nav-primary',
                '.nav-secondary',
            ),
            'others'  => array(),
        ),
    );

    return $settings;

}