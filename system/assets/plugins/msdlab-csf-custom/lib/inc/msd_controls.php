<?php
class MSDLAB_QueryControls{

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
            self::$instance = new MSDLAB_QueryControls();
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

    //

    /*public function search_box($title = "Search Students",$button = "SEARCH", $id = "student_search", $class = array('query-filter','search-box')){
        $label = apply_filters('msdlab_csf_manage_search_label','<label for="'.$id.'_input">'.$title.'</label>');
        $form_field = apply_filters('msdlab_csf_manage_search_form_field','<input id="'.$id.'_input" name="'.$id.'_input" type="search" value="'.$_POST[$id.'_input'].'" placeholder="'.$title.'" />');
        $button = apply_filters('msdlab_csf_manage_search_button','<input id="'.$id.'_button" type="submit" value="'.$button.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_manage_search_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_manage_search', $ret);
    }*/



    /*public function role_search($title = "Limit To",$button = "SEARCH", $id = "role_search", $class = array('query-filter','role-search'), $roles = array()){
        $default = '5-8';
        if(count($roles)<1){
            $roles = array(
                'Entire Database' => '',
                'Applicants' => '10',
                'Awardees' => '20',
                'Renewals' => '30',
                'Rejections' => '1',
            );
        }
        $options = array();
        foreach($roles AS $k => $v){
            if(empty( $_POST )) {
                $options[] = '<option value="' . $v . '"' . selected($v, $default, false) . '>' . $k . '</option>';
            } else {
                $options[] = '<option value="' . $v . '"' . selected($v, $_POST[$id . '_input'], false) . '>' . $k . '</option>';
            }
        }

        $label = apply_filters('msdlab_csf_manage_role_search_label','<label for="'.$id.'_input">'.$title.'</label>');
        $form_field = '<select id="'.$id.'_input" name="'.$id.'_input">'.implode("\r\n",$options).'</select>';
        $form_field = apply_filters('msdlab_csf_manage_role_search_form_field',$form_field);
        $button = apply_filters('msdlab_csf_manage_role_search_button','<input id="'.$id.'_button" type="submit" value="'.$button.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_manage_role_search_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_manage_role_search', $ret);
    }*/

    /*public function date_search_type($title = "Limit by Date",$button = "SEARCH", $id = "date_search_type", $class = array('query-filter','date-search-type'), $types = array()){
        if(count($types)<1){
            $types = array(
                'No' => '',
                'Application Date' => 'application',
                'Last Renewal Date' => 'renewal',
            );
        }
        $options = array();
        foreach($types AS $k => $v){
            $options[] = '<option value="'.$v.'"'.selected($v, $_POST[$id.'_input'], false).'>'.$k.'</option>';
        }

        $label = apply_filters('msdlab_csf_manage_date_search_type_label','<label for="'.$id.'_input">'.$title.'</label>');
        $form_field = '<select id="'.$id.'_input" name="'.$id.'_input">'.implode("\r\n",$options).'</select>';
        $form_field = apply_filters('msdlab_csf_manage_date_search_type_form_field',$form_field);
        $button = apply_filters('msdlab_csf_manage_date_search_type_button','<input id="'.$id.'_button" type="submit" value="'.$button.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_manage_date_search_type_class', $class));
        $this->javascript[] = '$( "#'.$id.'_input" ).change(function(){
            if($(this).val() != ""){
                $(".date-search").show();
            } else {
                $(".date-search").hide();
            }
        });';

        $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_manage_date_search_type', $ret);
    }*/

    /*public function date_search($title = "Between Dates",$button = "SEARCH", $id = "date_search", $class = array('query-filter','date-search','hidden'), $start_date = FALSE, $end_date = FALSE ){
        $start_date = !$start_date?date("M d, Y",strtotime('-1 month')):$start_date;
        $end_date = !$end_date?date("M d, Y"):$end_date;

        $label = apply_filters('msdlab_csf_manage_date_search_label','<label for="'.$id.'_input">'.$title.'</label>');
        $form_field_start = apply_filters('msdlab_csf_manage_date_search_start_form_field','<input id="'.$id.'_input_start" name="'.$id.'_input_start" type="date" value="'.$start_date.'" class="datepicker" />');
        $form_field_end = apply_filters('msdlab_csf_manage_date_search_end_form_field','<input id="'.$id.'_input_end" name="'.$id.'_input_end" type="date" value="'.$end_date.'" class="datepicker" />');
        $button = apply_filters('msdlab_csf_manage_date_search_button','<input id="'.$id.'_button" type="submit" value="'.$button.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_manage_date_search_class', $class));
        $this->javascript[] = '$( ".datepicker" ).datepicker();';

        $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field_start.$form_field_end.'</div>';
        return apply_filters('msdlab_csf_manage_date_search', $ret);
    }*/


    /*public function search_button($button = "SEARCH", $id = "search_button", $class = array('search-button')){
        $button = apply_filters('msdlab_csf_manage_search_button','<input id="'.$id.'_button" name="'.$id.'_button" type="submit" class="button button-primary" value="'.$button.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_manage_search_button_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$button.'</div>';
        return apply_filters('msdlab_csf_manage_search_button', $ret);
    }*/

    /*public function build_javascript(){
        $ret = '
        <script>
  jQuery(function($){
    '.implode(" ",apply_filters('msdlab_csf_manage_search_javascript', $this->javascript)).'
  });
  </script>';
        return $ret;
    }*/


    /*public function print_form($echo = true){
        $ret = array();
        $ret['search'] = $this->search_box();
        $ret['role_search'] = $this->role_search();
        $ret['date_search_type'] = $this->date_search_type();
        $ret['date_search'] = $this->date_search();
        $ret['search_button'] = $this->search_button();
        $ret['nonce'] = wp_nonce_field( 'records_search' );
        $ret['javascript'] = $this->build_javascript();

        if($echo){
            print $this->search_form_header();
            print implode("\n\r", $ret);
            print $this->search_form_footer();
        } else {
            return $ret;
        }
    }*/

    /*public function print_output_object($id = 'report_output', $class = array('report-output'), $echo = true){
        $class = implode(" ",apply_filters('msdlab_csf_manage_output_object_class', $class));
        $ret = '<div id="'.$id.'" name="'.$id.'" class="'.$class.'">Report</div>';

        if($echo){
            print apply_filters('msdlab_csf_manage_output_object', $ret);
        } else {
            return apply_filters('msdlab_csf_manage_output_object', $ret);
        }
    }*/
}