<?php

function msdlab_filter_filepath($filepath){
    if(class_exists('S3_Uploads')){
        $mys3 = S3_Uploads::get_instance();
        $filepath = str_replace(WP_CONTENT_URL, $mys3->get_s3_url(), $filepath);
    }
    return $filepath;
}