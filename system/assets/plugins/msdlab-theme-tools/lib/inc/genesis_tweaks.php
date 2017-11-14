<?php
class MSDLab_Genesis_Tweaks
{

    //Properties
    private $options;

    //Methods
    function __construct($options)
    {
        $defaults = array(
            'responsive' => true,
            'preheader' => 'genesis_before_header', //what to hook it to?
        );

        $this->options = wp_parse_args($options, $defaults);

        if($this->options[responsive]) {
            add_theme_support('genesis-responsive-viewport');//* Add viewport meta tag for mobile browsers
        }
        if($this->options[preheader]){
            add_action($this->options[preheader], array(&$this,'msdlab_pre_header'));
            add_action('msdlab_pre_header',array(&$this,'msdlab_pre_header_sidebar'), 15);
            add_action('after_setup_theme',array(&$this,'msdlab_add_preheader_sidebar'), 4);
        }


    }

    /**
     * Add pre-header with social and search
     */
    function msdlab_pre_header(){
        print '<div class="pre-header">
        <div class="wrap">';
        do_action('msdlab_pre_header');
        print '
        </div>
    </div>';
    }

    function msdlab_pre_header_sidebar(){
        print '<div class="widget-area">';
        dynamic_sidebar( 'pre-header' );
        print '</div>';
    }

    function msdlab_add_preheader_sidebar(){
        genesis_register_sidebar(array(
            'name' => 'Pre-header Sidebar',
            'description' => 'Widget above the logo/nav header',
            'id' => 'pre-header'
        ));
    }

}
