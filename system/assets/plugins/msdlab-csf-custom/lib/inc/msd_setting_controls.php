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
        if(class_exists('MSDLAB_Queries')){
            $this->queries = new MSDLAB_Queries();
        }
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

    public function settings_hidden($id, $value = null, $title = "", $validation = null, $class = array('hidden')){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        $form_field = apply_filters('msdlab_csf_'.$id.'_field','<input id="'.$id.'_input" name="'.$id.'_input" type="hidden" value="'.$value.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$form_field.'</div>';
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

    public function settings_select($id, $value = null, $title = "", $null_option = null, $options = array(), $validation = null, $class = array('select')){
        if(is_null($value)  || empty($value)){
            $value = $_POST[$id.'_input'];
        }
        if($null_option == null){$null_option = 'Select';}
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        //iterate through $options
        $options_str = implode("\n\r",$this->build_options($options,$value,$null_option));
        $select = '<select id="'.$id.'_input" name="'.$id.'_input">'.$options_str.'</select>';
        $form_field = apply_filters('msdlab_csf_'.$id.'_field', $select );
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function build_options($options,$value,$null_option){
        $ret = array();
        $cur = $options[$value];
        $options = array_unique($options);
        if(!empty($cur)) {
            $options[$value] = $cur;
        }
        if(is_array($null_option)){
            $ret[] = '<option value="'.$null_option['value'].'">'.$null_option['option'].'</option>';
        } else {
            $ret[] = '<option>'.$null_option.'</option>';
        }
        foreach ($options AS $k => $v){
            $ret[] = '<option value="'.$k.'"'.selected($value,$k,false).'>'.$v.'</option>';
        }
        return $ret;
    }

    public function settings_button($title = "Save",$id = "submit", $class = array('submit'), $type = "submit"){
        $form_field = apply_filters('msdlab_csf_'.$id.'_button','<input id="'.$id.'_button" type="'.$type.'" value="'.$title.'" class="button button-primary button-large" />');
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

    public function get_form($options){
        extract($options);
        if(!$form_id){ return false; }
        switch ($form_id){
            case 'csf_college':
                $institution_term_type_options = $this->queries->get_select_array_from_db('institutiontermtype','InstitutionTermTypeId','InstitutionTermType');
                $college_id = isset($data->CollegeId)?$data->CollegeId:$this->queries->get_next_id('college','CollegeId');
                $ret['hdr'] = $this->form_header($form_id);
                $ret['college_id'] = $this->settings_hidden('college_CollegeId',$college_id,'College ID',null,null);
                $ret['name'] = $this->settings_textfield('College Name','college_Name',array('large','setting-field'),$data->Name);
                //TODO: Add state code OR array for figuring out in/out state
                $ret['indirect_cost'] = $this->settings_textfield('Indirect Cost','college_IndirectCost',array('large','setting-field'),$data->IndirectCost);
                $ret['inst_type'] = $this->settings_hidden('college_InstitutionTypeId',0,'Institution Type',null,null);
                $ret['inst_term'] = $this->settings_select('college_InstitutionTermTypeId',$data->InstitutionTermTypeId?$data->InstitutionTermTypeId:1,'Institution Term',array('','Select'),$institution_term_type_options,null, array('large','setting-field'));
                $ret['books'] = $this->settings_textfield('Book Fee','college_BookFee',array('large','setting-field'),$data->BookFee);
                $ret['rboff'] = $this->settings_textfield('Room &amp; Board Off Campus','college_RoomBoardOffCampus',array('large','setting-field'),$data->RoomBoardOffCampus);
                $ret['rbon'] = $this->settings_textfield('Room &amp; Board On Campus','college_RoomBoardOnCampus',array('large','setting-field'),$data->RoomBoardOnCampus);
                $ret['instate'] = $this->settings_textfield('In State Tuition','college_InStateTuition',array('large','setting-field'),$data->InStateTuition);
                $ret['outstate'] = $this->settings_textfield('Out State Tuition','college_OutStateTuition',array('large','setting-field'),$data->OutStateTuition);
                $ret['submit'] = $this->settings_button();
                $ret['nonce'] = wp_nonce_field( $form_id );
                $ret['javascript'] = $this->build_javascript($form_id);
                $ret['ftr'] = $this->form_footer();
                break;
            case 'csf_contact':
                $states = $this->queries->get_select_array_from_db('state','StateId','State');
                $colleges = $this->queries->get_select_array_from_db('college','CollegeId','Name');
                $ret['hdr'] = $this->form_header($form_id);
                $ret['contact_id'] = $this->settings_hidden('collegecontact_CollegeContactId',$contact_id,'Contact ID',null,null);
                $ret['firstname'] = $this->settings_textfield('First Name','collegecontact_FirstName',array('large','setting-field'),$data->FirstName);
                $ret['lastname'] = $this->settings_textfield('Last Name','collegecontact_LastName',array('large','setting-field'),$data->LastName);
                $ret['Address1'] = $this->settings_textfield('Address','collegecontact_Address1',array('large','setting-field'),$data->Address1);
                $ret['Address2'] = $this->settings_textfield('Address','collegecontact_Address2',array('large','setting-field'),$data->Address2);
                $ret['City'] = $this->settings_textfield('City','collegecontact_City',array('large','setting-field'),$data->City);
                $ret['StateId'] = $this->settings_select('collegecontact_StateId',$data->StateId?$data->StateId:'OH','State',array('','Select'),$states,null, array('large','setting-field'));
                $ret['ZipCode'] = $this->settings_textfield('ZipCode','collegecontact_ZipCode',array('large','setting-field'),$data->ZipCode);
                $ret['PhoneNumber'] = $this->settings_textfield('Phone Number','collegecontact_PhoneNumber',array('large','setting-field'),$data->PhoneNumber);
                $ret['FaxNumber'] = $this->settings_textfield('Fax Number','collegecontact_FaxNumber',array('large','setting-field'),$data->FaxNumber);
                $ret['Department'] = $this->settings_textfield('Department','collegecontact_Department',array('large','setting-field'),$data->Department);
                $ret['Email'] = $this->settings_textfield('Email','collegecontact_Email',array('large','setting-field'),$data->Email);
                $ret['CollegeId'] = $this->settings_select('collegecontact_CollegeId',$data->CollegeId?$data->CollegeId:343,'College',array('','Select'),$colleges,null, array('large','setting-field'));
                $ret['Notes'] = $this->settings_textarea('Notes','collegecontact_Notes',array('large','setting-field'),$data->Notes);
                $ret['submit'] = $this->settings_button();
                //delete?
                $ret['nonce'] = wp_nonce_field( $form_id );
                $ret['javascript'] = $this->build_javascript($form_id);
                $ret['ftr'] = $this->form_footer();
                break;
        }
        return implode("\n",$ret);
    }
}