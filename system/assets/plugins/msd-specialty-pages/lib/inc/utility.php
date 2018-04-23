<?php
if (!class_exists('MSDSpecialtyPagesUtility')) {
    class MSDSpecialtyPagesUtility {
        function __construct(){
            
        }
        
        function meta_revision(){
            function add_meta_keys_to_revision( $keys ) {
                $keys[] = 'meta-key-to-revision';
                return $keys;
            }
            
            if(class_exists('WP_Post_Meta_Revisioning')){
                    add_filter( 'wp_post_revision_meta_keys', array(&$this,'add_meta_keys_to_revision') );
            }
        }
    } //End Class
} //End if class exists statement



//add action to save function, early
//add action to display function, late\
//action follows:
//get all meta keys
//add them to revisioning
