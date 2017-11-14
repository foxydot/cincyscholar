<?php
/*
Plugin Name: MSD Site Settings
Description: Provides settings panel for several social/address options and widgets/shortcodes/functions for display.
Version: 1.0
Author: Catherine M OBrien Sandrick (CMOS)
Author URI: http://msdlab.com/biological-assets/catherine-obrien-sandrick/
GitHub Plugin URI: https://github.com/msdlab/msd_site_settings
GitHub Branch:     master
License: GPL v2
*/

if(!class_exists('GitHubPluginUpdater')){
    require_once (plugin_dir_path(__FILE__).'/lib/resource/GitHubPluginUpdater.php');
}

if ( is_admin() ) {
    new GitHubPluginUpdater( __FILE__, 'msdlab', "msd_site_settings" );
}


class MSDSocial{
    private $the_path;
    private $the_url;
    public $icon_size;
    private $ver;
    function MSDSocial(){$this->__construct();}
    function __construct(){
        $this->the_path = plugin_dir_path(__FILE__);
        $this->the_url = plugin_dir_url(__FILE__);
        $this->icon_size = get_option('msdsocial_icon_size')?get_option('msdsocial_icon_size'):'0';
        $this->ver = '1.0';
        /*
         * Pull in some stuff from other files
         */
        //$this->requireDir($this->the_path . 'lib/inc');
        require_once($this->the_path . 'lib/inc/settings.php');
        require_once($this->the_path . 'lib/inc/widgets.php');
        if(!is_admin()){
            wp_enqueue_style('msd-social-style',$this->the_url.'lib/css/style.css');
            wp_enqueue_style('msd-social-style-'.$this->icon_size,$this->the_url.'lib/css/style'.$this->icon_size.'.css');
            wp_enqueue_style('font-awesome-style','//maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css');
        }
        add_action('admin_enqueue_scripts', array(&$this,'add_admin_scripts') );
        add_action('admin_enqueue_scripts', array(&$this,'add_admin_styles') );
        
        add_action('wp_head',array(&$this,'setup_notification_bar'));
        
        add_shortcode('msd-address',array(&$this,'get_address'));
        add_shortcode('msd-additional-locations',array(&$this,'get_additional_locations'));
        add_shortcode('msd-all-locations',array(&$this,'get_all_locations'));
        add_shortcode('msd-bizname',array(&$this,'get_bizname'));
        add_shortcode('msd-copyright',array(&$this,'get_copyright'));
        add_shortcode('msd-digits',array(&$this,'get_digits'));
        add_shortcode('msd-social',array(&$this,'social_media'));
        add_shortcode('msd-hours',array(&$this,'get_hours'));
    }

        function add_admin_scripts() {
            global $current_screen;
            if($current_screen->id == 'settings_page_msdsocial-options'){
                wp_enqueue_script('bootstrap-jquery','//maxcdn.bootstrapcdn.com/bootstrap/latest/js/bootstrap.min.js',array('jquery'),$this->ver,TRUE);
                wp_enqueue_script('timepicker-jquery',$this->the_url.'lib/js/jquery.timepicker.min.js',array('jquery'),$this->ver,FALSE);
                wp_enqueue_script( 'jquery-ui-datepicker' );
                wp_enqueue_script('msdsocial-jquery',$this->the_url.'lib/js/plugin-jquery.js',array('jquery','timepicker-jquery'),$this->ver,TRUE);
                
            }
        }
        
        function add_admin_styles() {
            global $current_screen;
            if($current_screen->id == 'settings_page_msdsocial-options'){
                wp_register_style('bootstrap-style','//maxcdn.bootstrapcdn.com/bootstrap/latest/css/bootstrap.min.css');
                wp_register_style('font-awesome-style','//maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css',array('bootstrap-style'));
                wp_register_style('timepicker-style',$this->the_url.'lib/css/jquery.timepicker.css');
                wp_enqueue_style('font-awesome-style');
                wp_enqueue_style('timepicker-style');
                wp_enqueue_style('jqueryui-smoothness','//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css');
            }
        }  


//contact information
function get_bizname(){
    $ret .= (get_option('msdsocial_biz_name')!='')?stripslashes(get_option('msdsocial_biz_name')):stripslashes(get_bloginfo('name'));
    return $ret;
}
function get_address(){
    if((get_option('msdsocial_street')!='') || (get_option('msdsocial_city')!='') || (get_option('msdsocial_state')!='') || (get_option('msdsocial_zip')!='')) {
        $ret = '<address itemscope itemtype="http://schema.org/LocalBusiness">';
            $ret .= (get_option('msdsocial_street')!='')?'<span itemprop="streetAddress" class="msdsocial_street">'.get_option('msdsocial_street').'</span> ':'';
            $ret .= (get_option('msdsocial_street2')!='')?'<span itemprop="streetAddress" class="msdsocial_street_2">'.get_option('msdsocial_street2').'</span> ':'';
            $ret .= (get_option('msdsocial_city')!='')?'<span itemprop="addressLocality" class="msdsocial_city">'.get_option('msdsocial_city').'</span>, ':'';
            $ret .= (get_option('msdsocial_state')!='')?'<span itemprop="addressRegion" class="msdsocial_state">'.get_option('msdsocial_state').'</span> ':'';
            $ret .= (get_option('msdsocial_zip')!='')?'<span itemprop="postalCode" class="msdsocial_zip">'.get_option('msdsocial_zip').'</span> ':'';
        $ret .= '</address>';
          return $ret;
        } else {
            return false;
        } 
}
function get_additional_locations(){
    $additional_locations = get_option(msdsocial_adtl_locations);
    $ret = '';
    foreach($additional_locations AS $loc){
        if(($loc[street]!='') || ($loc[city]!='') || ($loc[state]!='') || ($loc[zip]!='')) {
            $ret .= '<address itemscope itemtype="http://schema.org/LocalBusiness">';
                $ret .= ($loc[location_name]!='')?'<span itemprop="name" class="msdsocial_location_name">'.$loc[location_name].'</span> ':'';
                $ret .= ($loc[street]!='')?'<span itemprop="streetAddress" class="msdsocial_street">'.$loc[street].'</span> ':'';
                $ret .= ($loc[street2]!='')?'<span itemprop="streetAddress" class="msdsocial_street_2">'.$loc[street2].'</span> ':'';
                $ret .= ($loc[city]!='')?'<span itemprop="addressLocality" class="msdsocial_city">'.$loc[city].'</span>, ':'';
                $ret .= ($loc[state]!='')?'<span itemprop="addressRegion" class="msdsocial_state">'.$loc[state].'</span> ':'';
                $ret .= ($loc[zip]!='')?'<span itemprop="postalCode" class="msdsocial_zip">'.$loc[zip].'</span> ':'';
                $ret .= $this->get_location_digits($loc,FALSE,'');
            $ret .= '</address>';
        }
    }
    return $ret;
}
function get_all_locations(){
    $primary_location = array(
        array(
            'location_name' => $this->get_bizname(),
            'street' => get_option('msdsocial_street'),
            'street2' => get_option('msdsocial_street2'),
            'city' => get_option('msdsocial_city'),
            'state' => get_option('msdsocial_state'),
            'zip' => get_option('msdsocial_zip'),
            'phone' => get_option('msdsocial_phone'),
            'tracking_phone' => get_option('msdsocial_tracking_phone'),
            'fax' => get_option('msdsocial_fax'),
            'email' => get_option('msdsocial_email'),
            'lat' => get_option('msdsocial_lat'),
            'lng' => get_option('msdsocial_lng'),
        )
    );
    $additional_locations = get_option(msdsocial_adtl_locations);
    $locations = array_merge($primary_location,$additional_locations);
    $ret = '';
    foreach($locations AS $loc){
        if(($loc[street]!='') || ($loc[city]!='') || ($loc[state]!='') || ($loc[zip]!='') || ($loc[phone]!='')) {
            $ret .= '<li>
            <address itemscope itemtype="http://schema.org/LocalBusiness">';
                $ret .= ($loc[location_name]!='')?'<span itemprop="name" class="msdsocial_location_name">'.$loc[location_name].'</span> ':'';
                $ret .= ($loc[street]!='')?'<span itemprop="streetAddress" class="msdsocial_street">'.$loc[street].'</span> ':'';
                $ret .= ($loc[street2]!='')?'<span itemprop="streetAddress" class="msdsocial_street_2">'.$loc[street2].'</span> ':'';
                $ret .= ($loc[city]!='')?'<span itemprop="addressLocality" class="msdsocial_city">'.$loc[city].'</span>, ':'';
                $ret .= ($loc[state]!='')?'<span itemprop="addressRegion" class="msdsocial_state">'.$loc[state].'</span> ':'';
                $ret .= ($loc[zip]!='')?'<span itemprop="postalCode" class="msdsocial_zip">'.$loc[zip].'</span> ':'';
                $ret .= $this->get_location_digits($loc,FALSE,'');
            $ret .= '</address>
            </li>';
        }
    }
    return '<ul class="all-locations">'.$ret.'</ul>';
}

function get_digits($dowrap = TRUE,$sep = " | "){
        $sepsize = count($sep);
        if((get_option('msdsocial_phone')!='') || (get_option('msdsocial_tollfree')!='') || (get_option('msdsocial_fax')!='')) {
            if((get_option('msdsocial_tracking_phone')!='')){
                if(wp_is_mobile()){
                  $phone .= '<span itemprop="telephone" class="msdsocial_phone"><a href="tel:+1'.get_option('msdsocial_tracking_phone').'">'.get_option('msdsocial_tracking_phone').'</a></span> ';
                } else {
                  $phone .= '<span itemprop="telephone" class="msdsocial_phone">'.get_option('msdsocial_tracking_phone').'</span> ';
                }
              $phone .= '<span  itemprop="telephone" class="msdsocial_phone" style="display: none;">'.get_option('msdsocial_phone').'</span> ';
            } else {
                if(wp_is_mobile()){
                  $phone .= (get_option('msdsocial_phone')!='')?'<span itemprop="telephone" class="msdsocial_phone"><a href="tel:+1'.get_option('msdsocial_phone').'" itemprop="telephone">'.get_option('msdsocial_phone').'</a></span> ':'';
                } else {
                  $phone .= (get_option('msdsocial_phone')!='')?'<span itemprop="telephone" class="msdsocial_phone">'.get_option('msdsocial_phone').'</span> ':'';
                }
            }
            if((get_option('msdsocial_tracking_tollfree')!='')){
                if(wp_is_mobile()){
                  $tollfree .= '<span itemprop="telephone" class="msdsocial_tollfree"><a href="tel:+1'.get_option('msdsocial_tracking_tollfree').'">'.get_option('msdsocial_tracking_tollfree').'</a></span> ';
                } else {
                  $tollfree .= '<span itemprop="telephone" class="msdsocial_tollfree">'.get_option('msdsocial_tracking_tollfree').'</span> ';
                }
              $tollfree .= '<span itemprop="telephone" class="msdsocial_tollfree" style="display: none;">'.get_option('msdsocial_tollfree').'</span> ';
            } else {
                if(wp_is_mobile()){
                  $tollfree .= (get_option('msdsocial_tollfree')!='')?'<span itemprop="telephone" class="msdsocial_tollfree"><a href="tel:+1'.get_option('msdsocial_tollfree').'" itemprop="telephone">'.get_option('msdsocial_tollfree').'</a></span> ':'';
                } else {
                  $tollfree .= (get_option('msdsocial_tollfree')!='')?'<span itemprop="telephone" class="msdsocial_tollfree">'.get_option('msdsocial_tollfree').'</span> ':'';
                }
            }
            $fax = (get_option('msdsocial_fax')!='')?'<span itemprop="faxNumber" class="msdsocial_fax">'.get_option('msdsocial_fax').'</span> ':'';
            $ret = $phone;
            $ret .= ($phone!='' && $tollfree!='')?$sep:'';
            $ret .= $tollfree;
            $ret .= (!stripos($ret,$sep,$sepsize))?$sep:'';
            $ret .= $fax;
          if($dowrap){$ret = '<address itemscope itemtype="http://schema.org/LocalBusiness">'.$ret.'</address>';}
        return $ret;
        } else {
            return false;
        } 
}

function get_location_digits($loc,$dowrap = TRUE,$sep = " | "){
        $sepsize = count($sep);
        if(($loc[phone]!='') || ($loc[tollfree]!='') || ($loc[fax]!='')) {
            if(($loc[tracking_phone]!='')){
                if(wp_is_mobile()){
                  $phone .= '<span itemprop="telephone" class="msdsocial_phone"><a href="tel:+1'.$loc[tracking_phone].'">'.$loc[tracking_phone].'</a></span> ';
                } else {
                  $phone .= '<span itemprop="telephone" class="msdsocial_phone">'.$loc[tracking_phone].'</span> ';
                }
              $phone .= '<span itemprop="telephone" class="msdsocial_phone" style="display: none;">'.$loc[phone].'</span> ';
            } else {
                if(wp_is_mobile()){
                  $phone .= ($loc[phone]!='')?'<span itemprop="telephone" class="msdsocial_phone"><a href="tel:+1'.$loc[phone].'" itemprop="telephone">'.$loc[phone].'</a></span> ':'';
                } else {
                  $phone .= ($loc[phone]!='')?'<span itemprop="telephone" class="msdsocial_phone">'.$loc[phone].'</span> ':'';
                }
            }
            if(($loc[tracking_tollfree]!='')){
                if(wp_is_mobile()){
                  $tollfree .= '<span itemprop="telephone" class="msdsocial_tollfree"><a href="tel:+1'.$loc[tracking_tollfree].'">'.$loc[tracking_tollfree].'</a></span> ';
                } else {
                  $tollfree .= '<span itemprop="telephone" class="msdsocial_tollfree">'.$loc[tracking_tollfree].'</span> ';
                }
              $tollfree .= '<span itemprop="telephone" class="msdsocial_tollfree" style="display: none;">'.$loc[tollfree].'</span> ';
            } else {
                if(wp_is_mobile()){
                  $tollfree .= ($loc[tollfree]!='')?'<span itemprop="telephone" class="msdsocial_tollfree"><a href="tel:+1'.$loc[tollfree].'" itemprop="telephone">'.$loc[tollfree].'</a></span> ':'';
                } else {
                  $tollfree .= ($loc[tollfree]!='')?'<span itemprop="telephone" class="msdsocial_tollfree">'.$loc[tollfree].'</span> ':'';
                }
            }
            $fax = ($loc[fax]!='')?'<span itemprop="faxNumber" class="msdsocial_fax">'.$loc[fax].'</span> ':'';
            $ret = $phone;
            $ret .= ($phone!='' && $tollfree!='')?$sep:'';
            $ret .= $tollfree;
            $ret .= (!stripos($ret,$sep,$sepsize))?$sep:''; //TODO:Why error here?
            $ret .= $fax;
          if($dowrap){$ret = '<address itemscope itemtype="http://schema.org/LocalBusiness">'.$ret.'</address>';}
        return $ret;
        } else {
            return false;
        } 
}

function get_phone($dowrap = TRUE){
        if((get_option('msdsocial_phone')!='')) {
            if((get_option('msdsocial_tracking_phone')!='')){
                if(wp_is_mobile()){
                  $ret .= '<a href="tel:+1'.get_option('msdsocial_tracking_phone').'">'.get_option('msdsocial_tracking_phone').'</a> ';
                } else {
                  $ret .= '<span>'.get_option('msdsocial_tracking_phone').'</span> ';
                }
              $ret .= '<span itemprop="telephone" style="display: none;">'.get_option('msdsocial_phone').'</span> ';
            } else {
                if(wp_is_mobile()){
                  $ret .= (get_option('msdsocial_phone')!='')?'<a href="tel:+1'.get_option('msdsocial_phone').'" itemprop="telephone">'.get_option('msdsocial_phone').'</a> ':'';
                } else {
                  $ret .= (get_option('msdsocial_phone')!='')?'<span itemprop="telephone">'.get_option('msdsocial_phone').'</span> ':'';
                }
            }
          if($dowrap){$ret = '<address itemscope itemtype="http://schema.org/LocalBusiness">'.$ret.'</address>';}
        return $ret;
        } else {
            return false;
        } 
}

function get_hours($atts = array()){
    extract( shortcode_atts( array(
                'sep' => ' | ',
                'additup' => TRUE
            ), $atts ) );
    $days = array(
                'Sunday',
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
            );
            foreach ($days as $day) {
                $open = get_option('msdsocial_hours_'.strtolower($day).'_open');
                $close = get_option('msdsocial_hours_'.strtolower($day).'_close');
                $hours[$day]['open'] = $open;
                $hours[$day]['close'] = $close;
            }
    if($additup){
        foreach($hours as $k => $v){
            if($v['open']==''||$v['close']==''){
                $closed[] = $k;
                if($v != $prev){
                    $ds = $ns;
                    $de = $prek;
                }
            } else {
                if($v == $prev){
                    $ds = $ns;
                } else {
                    $ns = $k;
                    $de = $prek;
                    if($prev){
                        if($ds == $de){
                            $str[] = '<span class="day">'.$ds.':</span> <span class="hours">'.$prev['open'].'-'.$prev['close'].'</span>';
                        } elseif($de == 'Sunday'){
                            $str[] = '<span class="day">'.$de.':</span> <span class="hours">'.$prev['open'].'-'.$prev['close'].'</span>';
                        } else {
                            $str[] = '<span class="day">'.$ds.'-'.$de.':</span> <span class="hours">'.$prev['open'].'-'.$prev['close'].'</span>';
                        }
                        $ds = $ns;
                    }
                }
            $prek = $k;
            $prev = $v;
            }
        }
        $de = $prek;
        if($ds == $de){
            $str[] = '<span class="day">'.$ds.':</span> <span class="hours">'.$prev['open'].'-'.$prev['close'].'</span>';
        } else {
            $str[] ='<span class="day">'. $ds.'-'.$de.':</span> <span class="hours">'.$prev['open'].'-'.$prev['close'].'</span>';
        }
    } else {
        foreach($hours as $k => $v){
            if($v['open']==''||$v['close']==''){
                $str[] = '<span class="day">'.$k.':</span> <span class="hours">Closed</span>';
            } else {
                $str[] = '<span class="day">'.$k.':</span> <span class="hours">'.$v['open'].'-'.$v['close'].'</span>';
            }
        }
    }
    return implode($sep,$str);
}
//create copyright message
function copyright($address = TRUE){
    if($address){
        $ret .= $this->msdsocial_get_address();
        $ret .= $this->msdsocial_get_digits();
    }
    $ret .= 'Copyright &copy;'.date('Y').' ';
    $ret .= $this->msdsocial_get_bizname();
    print $ret;
}


function social_media($atts = array()){
    extract( shortcode_atts( array(
            ), $atts ) );
    
    if(get_option('msdsocial_facebook_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_facebook_link').'" class="fa fa-facebook" title="Join Us on Facebook!" target="_blank"></a>';
    }    
    if(get_option('msdsocial_twitter_user')!=""){
        $ret .= '<a href="http://www.twitter.com/'.get_option('msdsocial_twitter_user').'" class="fa fa-twitter" title="Follow Us on Twitter!" target="_blank"></a>';
    }    
    if(get_option('msdsocial_pinterest_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_pinterest_link').'" class="fa fa-pinterest" title="Pinterest" target="_blank"></a>';
    }    
    if(get_option('msdsocial_google_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_google_link').'" class="fa fa-google-plus" title="Google+" target="_blank"></a>';
    }    
    if(get_option('msdsocial_linkedin_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_linkedin_link').'" class="fa fa-linkedin" title="LinkedIn" target="_blank"></a>';
    }    
    if(get_option('msdsocial_instagram_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_instagram_link').'" class="fa fa-instagram" title="Instagram" target="_blank"></a>';
    }    
    if(get_option('msdsocial_tumblr_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_tumblr_link').'" class="fa fa-tumblr" title="Tumblr" target="_blank"></a>';
    }    
    if(get_option('msdsocial_reddit_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_reddit_link').'" class="fa fa-reddit" title="Reddit" target="_blank"></a>';
    }    
    if(get_option('msdsocial_flickr_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_flickr_link').'" class="fa fa-flickr" title="Flickr" target="_blank"></a>';
    }    
    if(get_option('msdsocial_youtube_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_youtube_link').'" class="fa fa-youtube" title="YouTube" target="_blank"></a>';
    }    
    if(get_option('msdsocial_vimeo_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_vimeo_link').'" class="fa fa-vimeo-square" title="Vimeo" target="_blank"></a>';
    }    
    if(get_option('msdsocial_vine_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_vine_link').'" class="fa fa-vine" title="Vine" target="_blank"></a>';
    }    
    if(get_option('msdsocial_sharethis_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_sharethis_link').'" class="fa fa-share-alt" title="ShareThis" target="_blank"></a>';
    }    
    if(get_option('msdsocial_contact_link')!=""){
        $ret .= '<a href="'.get_option('msdsocial_contact_link').'" class="fa fa-envelope" title="Contact Us" target="_blank"></a>';
    }    
    if(get_option('msdsocial_show_blog')!=""){
        if (get_option('show_on_front')=='page') {
          $blog_page_id = get_option('page_for_posts');
          $blog_url = get_permalink($blog_page_id);
        } else {
          $blog_url = get_option('home');
        }
        $ret .= '<a href="'.$blog_url.'" class="fa fa-newspaper-o" title="Blog" target="_blank"></a>';
    }
    if(get_option('msdsocial_show_feed')!=""){
        $ret .= '<a href="'.get_bloginfo('rss2_url').'" class="fa fa-rss" title="RSS Feed" target="_blank"></a>';
    }
    $ret = apply_filters('msdlab_social_icons_output',$ret);
    $ret = '<div id="social-media" class="social-media">'.$ret.'</div>';
    return $ret;
}

function get_hours_deux(){ ///why are there two of these?
    $days = array(
            'Sunday',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
        );
        foreach ($days as $day) {
            $open = get_option('msdsocial_hours_'.strtolower($day).'_open');
            $close = get_option('msdsocial_hours_'.strtolower($day).'_close');
            $closed = $open==''||$close==''?FALSE:TRUE;
            $hours[$day] = $closed?$open . ' to ' . $close:'CLOSED';
        }
        $prev = array();
        foreach ($days as $day) {
            if($hours[$day] != $prev['hours']){
                if(isset($prev['hours'])){
                    if(isset($prev['day'])){
                        $ret .= ' - '.$prev['day'];
                    }
                    $ret .= '</span><span class="hours">'.$prev['hours'].'</span></div>
';
                }
                $ret .= '<div class="hours '.$day.'"><span class="day">'.$day;
                unset($prev['day']);
            } else {
                $prev['day'] = $day;
            }
            if($day == 'Saturday'){
                if($hours[$day] == $prev['hours'] && isset($prev['day'])){
                    $ret .= ' - '.$prev['day'];
                }
                $ret .= '</span><span class="hours">'.$hours[$day].'</span></div>
';
            }
            $prev['hours'] = $hours[$day];
        }
        return '<div class="business-hours">'.$ret.'</div>';
}

function notification_bar_in_date_window($date = '',$convert = TRUE){
    if(empty($date)){
        $convert = FALSE;
        $date = time();
    }
    if($convert){
        $date = strtotime($date);
    }
    $start = strtotime(get_option('msdsocial_notification_start_datetime'));
    $end = strtotime(get_option('msdsocial_notification_end_datetime'));
    if(($date >= $start && $date <= $end) || ($date >= $start && $end == '') || ($start == '' && $end == '')){
        return TRUE;
    } else {
        return FALSE;
    }
}
function setup_notification_bar(){
    if(get_option('msdsocial_notification_content') == ''){
        return false;
    }
    
    $position = get_option('msdsocial_notification_position');
    if($position<10){
        return false;
    }
    if($this->notification_bar_in_date_window()){
        $genesis = function_exists('genesis')?1:0;
        switch($position){
            case 10:
                if($genesis){
                    add_action('genesis_before_header',array(&$this,'print_notification_bar'),1);
                } else {
                    $this->print_notification_bar();
                }
                break;
            case 20:
                if($genesis){
                    add_action('genesis_after_header',array(&$this,'print_notification_bar'),99);
                } else {
                    add_action('loop_start',array(&$this,'print_notification_bar'),1);
                }
                break;
            case 30:
                if($genesis){
                    add_action('genesis_before_footer',array(&$this,'print_notification_bar'),1);
                } else {
                    add_action('wp_footer',array(&$this,'print_notification_bar'),1);
                }
                break;
            case 40:
                if($genesis){
                    add_action('genesis_after_footer',array(&$this,'print_notification_bar'),99);
                } else {
                    add_action('wp_footer',array(&$this,'print_notification_bar'),99);
                }
                break;
            case 50:
                global $notification_bar_html;
                $notification_bar_html = $this->get_notification_bar();
                return true;
                break;
            default:
                return false;
                break;
        }
    }
}

function print_notification_bar(){
    print $this->get_notification_bar();
}
function get_notification_bar(){
    $content = apply_filters('the_content',stripcslashes(get_option('msdsocial_notification_content')));
    return '<div class="notification-bar"><div class="wrap">' . $content . '</div></div>';
}

function requireDir($dir){
    $dh = @opendir($dir);

    if (!$dh) {
        throw new Exception("Cannot open directory $dir");
    } else {
        while (($file = readdir($dh)) !== false) {
            if ($file != '.' && $file != '..') {
                $requiredFile = $dir . DIRECTORY_SEPARATOR . $file;
                if ('.php' === substr($file, strlen($file) - 4)) {
                    require_once $requiredFile;
                } elseif (is_dir($requiredFile)) {
                    requireDir($requiredFile);
                }
            }
        }
    closedir($dh);
    }
    unset($dh, $dir, $file, $requiredFile);
}
    //end of class
}
$msd_social = new MSDSocial();