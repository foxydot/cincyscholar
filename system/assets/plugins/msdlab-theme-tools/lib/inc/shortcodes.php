<?php
class MSDLab_Shortcodes
{

    //Properties
    private $options;

    //Methods
    function __construct($options)
    {
        $defaults = array();

        $this->options = wp_parse_args($options, $defaults);

        add_shortcode('mailto', array(&$this, 'msdlab_mailto_function'));
        add_shortcode('button',array(&$this, 'msdlab_button_function'));

    }

    function msdlab_mailto_function($atts, $content)
    {
        extract(shortcode_atts(array(
            'email' => '',
        ), $atts));
        $content = trim($content);
        if ($email == '' && preg_match('|[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}|i', $content, $matches)) {
            $email = $matches[0];
        }
        $email = antispambot($email);
        if (strlen($content) < 1) {
            $content = $email;
        }
        return '<a href="mailto:' . $email . '">' . $content . '</a>';
    }

    function msdlab_button_function($atts, $content = null){
        extract( shortcode_atts( array(
            'url' => null,
            'target' => '_self',
            'class' => null
        ), $atts ) );
        if(strstr($url,'mailto:',0)){
            $parts = explode(':',$url);
            if(is_email($parts[1])){
                $url = $parts[0].':'.antispambot($parts[1]);
            }
        }
        $ret = '<a class="button btn' . $class . '" href="' . $url . '" target="' . $target . '">'.remove_wpautop($content).'</a>';
        return $ret;
    }

}