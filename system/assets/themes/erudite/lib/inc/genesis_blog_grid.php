<?php
/**
 * Grid Loop Pagination
 * Returns false if not grid loop.
 * Returns an array describing pagination if is grid loop
 *
 * @author Bill Erickson
 * @link http://www.billerickson.net/a-better-and-easier-grid-loop/
 *
 * @param object $query
 * @return bool is grid loop (true) or not (false)
 */
function be_grid_loop_pagination( $query = false ) {

    // If no query is specified, grab the main query
    global $wp_query;
    if( !isset( $query ) || empty( $query ) || !is_object( $query ) )
        $query = $wp_query;

    // Sections of site that should use grid loop
    if( ! ( $query->is_home() || $query->is_archive() ) )
        return false;
    // Specify pagination
    return array(
        'features_on_front' => 1,
        'teasers_on_front' => 6,
        'features_inside' => 0,
        'teasers_inside' => 8,
    );
}

/**
 * Grid Loop Query Arguments
 *
 * @author Bill Erickson
 * @link http://www.billerickson.net/a-better-and-easier-grid-loop/
 *
 * @param object $query
 * @return null
 */
function be_grid_loop_query_args( $query ) {
    if(is_admin()) return $query;
    $grid_args = be_grid_loop_pagination( $query );


    if( $query->is_main_query() && ( $query->is_home() || $query->is_archive() ) && is_array($grid_args) ) {
        // First Page
        $paged = $query->query_vars['paged'];
        if( ! $paged ) {
            $query->set( 'posts_per_page', ( $grid_args['features_on_front'] + $grid_args['teasers_on_front'] ) );
            // Other Pages
        } else {
            $query->set( 'posts_per_page', ( $grid_args['features_inside'] + $grid_args['teasers_inside'] ) );
            $offset = ( $grid_args['features_on_front'] + $grid_args['teasers_on_front'] ) + ( $grid_args['features_inside'] + $grid_args['teasers_inside'] ) * ( $paged - 2 );
            $query->set( 'offset', $offset );
            // Offset is posts on first page + posts on internal pages * ( current page - 2 )
        }

    }
}
add_action( 'pre_get_posts', 'be_grid_loop_query_args');

/**
 * Grid Loop Post Classes
 *
 * @author Bill Erickson
 * @link http://www.billerickson.net/a-better-and-easier-grid-loop/
 *
 * @param array $classes
 * @return array $classes
 */
function be_grid_loop_post_classes( $classes ) {
    global $wp_query;

    // Only run on main query
    if( ! $wp_query->is_main_query() )
        return $classes;

    // Only run on grid loop
    $grid_args = be_grid_loop_pagination();
    if( !$grid_args || ! $wp_query->is_main_query() )
        return $classes;

    if(!is_cpt('post'))
        return $classes;
    // First Page Classes
    if( !$wp_query->query_vars['paged'] ) {
        remove_action( 'genesis_entry_footer', 'genesis_post_meta');
        // Features
        if( $wp_query->current_post < $grid_args['features_on_front'] ) {
            $classes[] = 'genesis-feature';
            $classes[] = 'col-md-12';

            // Teasers
        } else {
            $classes[] = 'genesis-teaser';
            $classes[] = 'col-sm-6';
            $classes[] = 'col-xs-12';
        }

        // Inner Pages
    } else {
        remove_action( 'genesis_entry_footer', 'genesis_post_meta');
        // Features
        if( $wp_query->current_post < $grid_args['features_inside'] ) {
            $classes[] = 'genesis-feature';
            $classes[] = 'col-md-12';

            // Teasers
        } else {
            $classes[] = 'genesis-teaser';
            $classes[] = 'col-sm-6';
            $classes[] = 'col-xs-12';
        }

    }

    return $classes;
}
add_filter( 'post_class', 'be_grid_loop_post_classes' );



/**
 * Grid Loop Featured Image
 *
 * @param string image size
 * @return string
 */
function be_grid_loop_image( $defaults ) {
    global $wp_query;
    $grid_args = be_grid_loop_pagination();
    if( ! $grid_args )

        return $defaults;

    // Feature
    if( ( ! $wp_query->query_vars['paged'] && $wp_query->current_post < $grid_args['features_on_front'] ) || ( $wp_query->query_vars['paged'] && $wp_query->current_post < $grid_args['features_inside'] ) )
        $defaults['size'] = 'child_full';

    if( ( ! $wp_query->query_vars['paged'] && $wp_query->current_post > ( $grid_args['features_on_front'] - 1 ) ) || ( $wp_query->query_vars['paged'] && $wp_query->current_post > ( $grid_args['features_inside'] - 1 ) ) )
        $defaults['size'] = 'child_thumbnail';

    return $defaults;
}
add_filter( 'genesis_get_image_default_args', 'be_grid_loop_image' );

/**
 * Fix Posts Nav
 *
 * The posts navigation uses the current posts-per-page to
 * calculate how many pages there are. If your homepage
 * displays a different number than inner pages, there
 * will be more pages listed on the homepage. This fixes it.
 *
 */
function be_fix_posts_nav() {

    if( get_query_var( 'paged' ) )
        return;

    global $wp_query;
    $grid_args = be_grid_loop_pagination();
    if( ! $grid_args )
        return;

    $max = ceil ( ( $wp_query->found_posts - $grid_args['features_on_front'] - $grid_args['teasers_on_front'] ) / ( $grid_args['features_inside'] + $grid_args['teasers_inside'] ) ) + 1;
    $wp_query->max_num_pages = $max;

}
add_filter( 'genesis_after_endwhile', 'be_fix_posts_nav', 5 );

//add_action ( 'genesis_before_entry', 'msdlab_add_pagination');
function msdlab_add_pagination() {
    if ( is_single() && is_cpt('post') ) {
        add_action( 'genesis_entry_footer', 'msdlab_post_navigation_links' );
    }
}

function msdlab_post_navigation_links() {
    print '<div class="pagination">';
    previous_post_link('<div class="prev-link alignleft page-nav"><i class="fa fa-arrow-left"></i> %link</div>', 'Previous Post');
    next_post_link('<div class="next-link alignright page-nav">%link <i class="fa fa-arrow-right"></i></div>', 'Next Post');
    print '</div>';
}

// Customize Grid Loop Content
add_action('genesis_before_entry', 'msdlab_switch_content');
function msdlab_switch_content() {
    if(is_cpt('post') && (is_archive() || is_home())){
        remove_action('genesis_entry_content', 'genesis_do_post_content');
        remove_action('genesis_entry_content', 'genesis_do_post_image',8);
        remove_action( 'genesis_entry_header', 'msdlab_do_post_subtitle', 13);
        remove_action( 'genesis_entry_header', 'genesis_do_post_title');
        add_action( 'genesis_entry_header', 'msdlab_grid_loop_header');
        add_action( 'genesis_entry_header', 'genesis_do_post_title');
        add_action('genesis_entry_content', 'msdlab_grid_loop_content');
    }
}

function msdlab_grid_loop_content() {
    global $_genesis_loop_args;
    if ( in_array( 'genesis-feature', get_post_class() ) ) {
        the_excerpt();
        printf( '<a href="%s" title="%s" class="readmore-button alignright">%s</a>', get_permalink(), the_title_attribute('echo=0'), 'Continue Reading >' );

    }
    else {
        return false;
    }
}

function msdlab_grid_loop_header() {
    global $_genesis_loop_args;
    if ( in_array( 'genesis-feature', get_post_class() ) ) {
        printf( '<a href="%s" title="%s" class="featured_image_wrapper">%s</a>', get_permalink(), the_title_attribute('echo=0'), genesis_get_image() );

    }
    else {
        printf( '<a href="%s" title="%s" class="grid_image_wrapper">%s</a>', get_permalink(), the_title_attribute('echo=0'), genesis_get_image() );
    }
}
function msdlab_get_comments_number(){ //not used
    $num_comments = get_comments_number();
    if ($num_comments == '1') $comments = $num_comments.' ' . __( 'comment', 'adaptation' );
    else $comments = $num_comments.' ' . __( 'comments', 'adaptation' );
    return '<a class="comments" href="'.get_permalink().'/#comments">'.$comments.'</a>';
}

add_filter('body_class','msdlab_add_blog_grid_body_class');
function msdlab_add_blog_grid_body_class($classes){
    if(is_cpt('post') && (is_archive() || is_home())){
        $classes[] = 'genesis-blog-grid';
    }
    return $classes;
}

add_filter( 'genesis_pre_get_option_site_layout', 'msdlab_add_sidebar_to_single_posts' );

function msdlab_add_sidebar_to_single_posts($layout){
    global $post;
    if(is_single() && $post->post_type == "post"){
        $layout = 'content_sidebar';
    }
    return $layout;
}