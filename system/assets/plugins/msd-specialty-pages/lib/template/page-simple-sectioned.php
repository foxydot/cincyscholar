<?php
/*
Template Name: Simple Sectioned Page
*/
if(function_exists('genesis')){
    //this is a genesis themed site
    add_action('genesis_before_footer',array('MSDSimpleSectionedPage','sectioned_page_output'),0);
    add_action('wp_print_footer_scripts',array('MSDSimpleSectionedPage','sectioned_page_floating_nav'));
    genesis();
} else {
    //not genesis. Do things kind of the old fashioend way.
    
get_header(); ?>

    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">

        <?php
        // Start the loop.
        while ( have_posts() ) : the_post();

            // Include the page content template.
            get_template_part( 'content', 'page' );
            MSDSimpleSectionedPage::sectioned_page_output();
            // If comments are open or we have at least one comment, load up the comment template.
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;

        // End the loop.
        endwhile;
        ?>

        </main><!-- .site-main -->
    </div><!-- .content-area -->

<?php get_footer();
}
