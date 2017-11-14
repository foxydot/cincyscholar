<?php
class MSDLab_Theme_Tweaks
{
    //Properties
    private $options;

    //Methods
    function __construct($options)
    {
        $defaults = array(
            'apple_touch' => false,
            'shortcut' => true,
            'detect_phone' => false
        );
        $this->options = wp_parse_args($options, $defaults);

        add_action('wp_head',array(&$this,'msdlab_wphead_additions'));
    }

    /**
     * Add apple touch icons
     */
    function msdlab_wphead_additions(){
        $ret = '';
        if($this->options['apple_touch']){
            $ret .= '
            <link href="'.get_stylesheet_directory_uri().'/lib/img/apple-touch-icon.png" rel="apple-touch-icon" />
            <link href="'.get_stylesheet_directory_uri().'/lib/img/apple-touch-icon-76x76.png" rel="apple-touch-icon" sizes="76x76" />
            <link href="'.get_stylesheet_directory_uri().'/lib/img/apple-touch-icon-120x120.png" rel="apple-touch-icon" sizes="120x120" />
            <link href="'.get_stylesheet_directory_uri().'/lib/img/apple-touch-icon-152x152.png" rel="apple-touch-icon" sizes="152x152" />
            ';
        }
        if($this->options['shortcut']){
            $ret = '
            <link rel="shortcut icon" href="'.get_stylesheet_directory_uri().'/lib/img/favicon.ico" type="image/x-icon">
            <link rel="icon" href="'.get_stylesheet_directory_uri().'/lib/img/favicon.ico" type="image/x-icon">
            ';
        }
        if($this->options['detect_phone']){
            $ret = '
            <meta name="format-detection" content="telephone=no">
            ';
        }

        print $ret;
    }
}
