<?php global $wpalchemy_media_access; ?>

<?php 

$postid = is_admin()?$_GET['post']:$post->ID;
$template_file = get_post_meta($postid,'_wp_page_template',TRUE);
  // check for a template type
if (is_admin()){
    if($template_file == 'page-sectioned.php' ) {
        include_once('metabox-sectioned-complex.php');
    } elseif($template_file == 'page-simple-sectioned.php') {
        include_once('metabox-sectioned-simple.php');
    } else {
    print "Select \"Sectioned Page\" or \"Simple Sectioned Page\" template and save to activate.";
}
} ?>
