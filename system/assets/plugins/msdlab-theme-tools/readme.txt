goes in functions.php

/***Tools Plugin**/
//instantiate sub packages
if(class_exists('MSDLab_Theme_Tweaks')){
    $options = array();
    $ttweaks = new MSDLab_Theme_Tweaks($options);
}
if(class_exists('MSDLab_Genesis_Bootstrap')){
    $options = array();
    $bootstrappin = new MSDLab_Genesis_Bootstrap($options);
}
if(class_exists('MSDLab_Genesis_Tweaks')){
    $options = array(
        'preheader' => 'genesis_header_right'
    );
    $gtweaks = new MSDLab_Genesis_Tweaks($options);
}
if(class_exists('MSDLab_Subtitle_Support')){
    $options = array();
    $subtitle_support = new MSDLab_Subtitle_Support($options);
}