<?php
class MSDLAB_SettingControls{

    public $javascript;

    /**
     * A reference to an instance of this class.
     */
    private static $instance;


    /**
     * Returns an instance of this class.
     */
    public static function get_instance() {

        if( null == self::$instance ) {
            self::$instance = new MSDLAB_SettingControls();
        }

        return self::$instance;

    }

    public function __construct() {


    }


    public function form_header($id = "csf_form", $class = array()){
        $class = implode(" ",apply_filters('msdlab_'.$id.'_header_class', $class));
        $ret = '<form id="'.$id.'" class="'.$class.'" method="post">';
        return apply_filters('msdlab_'.$id.'_header', $ret);
    }

    public function form_footer(){
        $ret = '</form>';
        return apply_filters('msdlab_csf_manage_form_footer', $ret);
    }


    public function build_javascript($id = "csf_form"){
        $ret = '
        <script>
  jQuery(function($){
    '.implode(" ",apply_filters('msdlab_'.$id.'_javascript', $this->javascript)).'
  });
  </script>';
        return $ret;
    }

    //SETTINGS PANEL

    public function settings_date($title = "Date",$id = "date", $class = array('datepicker'), $value = null){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        $form_field = apply_filters('msdlab_csf_'.$id.'_field','<input id="'.$id.'_input" name="'.$id.'_input" type="date" value="'.$value.'" placeholder="'.$title.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function settings_textfield($title = "",$id = "text", $class = array('medium'), $value = null){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        $form_field = apply_filters('msdlab_csf_'.$id.'_field','<input id="'.$id.'_input" name="'.$id.'_input" type="text" value="'.$value.'" placeholder="'.$title.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function settings_textarea($title = "Text to display out of season",$id = "text", $class = array('textarea'), $value = null){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        ob_start();
        wp_editor( stripcslashes($value), $id.'_input', array('media_buttons' => false,'teeny' => true,'textarea_rows' => 5) );
        $form_field = ob_get_clean();
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function settings_pageselect($title = "Select Page",$id = "pageselect", $class = array('select'), $value = null){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        $options = array();
        //iterate through available pages
        $form_field = apply_filters('msdlab_csf_'.$id.'_field',wp_dropdown_pages( array( 'name' => $id.'_input', 'echo' => 0, 'show_option_none' => __( '&mdash; Select &mdash;' ), 'option_none_value' => '0', 'selected' => get_option( $id ) ) ) );
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function settings_button($title = "Save",$id = "submit", $class = array('submit'), $type = "submit"){
        $form_field = apply_filters('msdlab_csf_'.$id.'_button','<input id="'.$id.'_button" type="'.$type.'" value="'.$title.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function print_settings($echo = true){
        $form_id = 'csf_settings_form';
        $ret = array();
        $ret['admin_address'] = $this->settings_textfield("Admin Address","csf_settings_admin_address",array(''),get_option('csf_settings_admin_address'));
        $ret['start_date'] = $this->settings_date("Start Date","csf_settings_start_date",array('datepicker'),get_option('csf_settings_start_date'));
        $ret['end_date'] = $this->settings_date("End Date","csf_settings_end_date",array('datepicker'),get_option('csf_settings_end_date'));
        $ret['alt_text'] = $this->settings_textarea("Text to Display When Not Taking Applications","csf_settings_alt_text",array(''),get_option('csf_settings_alt_text'));
        $ret['welcome_page'] = $this->settings_pageselect("Select Student Portal Welcome Page","csf_settings_student_welcome_page");
        $ret['button'] = $this->settings_button();
        $ret['nonce'] = wp_nonce_field( 'csf_settings' );
        $ret['javascript'] = $this->build_javascript($form_id);

        if($echo){
            print $this->form_header($form_id);
            print implode("\n\r", $ret);
            print $this->form_footer();
        } else {
            return $ret;
        }
    }
}