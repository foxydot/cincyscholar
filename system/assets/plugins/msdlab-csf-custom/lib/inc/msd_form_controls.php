<?php
class MSDLAB_FormControls{

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
            self::$instance = new MSDLAB_FormControls();
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

    public function form_close(){
        $ret = '</form>';
        return apply_filters('msdlab_csf_manage_form_footer', $ret);
    }

    public function form_footer($id, $content, $class = array()){
        $class = implode(" ",apply_filters('msdlab_'.$id.'_footer_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$content.'</div>';
        return apply_filters('msdlab_'.$id.'_footer', $ret);
    }


    public function build_jquery($id,$jquery){
        $ret = '
        <script>
  jQuery(function($){
    '.implode("\n\r",apply_filters('msdlab_'.$id.'_javascript', $jquery)).'
  });
  </script>';
        return $ret;
    }

    //FIELD LOGIC

    //TODO: Refactor for redundancies

    public function section_header($id, $value = null, $class = array('section-header')){
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<h3 id="'.$id.'_wrapper" class="'.$class.'">'.$value.'</h3>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function field_utility($id, $value = null, $title = "", $validation = null, $class = array('hidden')){
        if(is_null($value)){
            $value = $_POST[$id];
        }
        $form_field = apply_filters('msdlab_csf_'.$id.'_field','<input id="'.$id.'" name="'.$id.'" type="hidden" value="'.$value.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function field_hidden($id, $value = null, $title = "", $validation = null, $class = array('hidden')){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        $form_field = apply_filters('msdlab_csf_'.$id.'_field','<input id="'.$id.'_input" name="'.$id.'_input" type="hidden" value="'.$value.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function field_boolean($id, $value = 0, $title = "", $validation = null, $class = array('bool'), $settings = array()){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        $default_settings = array(
            'true' => 'YES',
            'false' => 'NO'
        );
        $settings = array_merge($default_settings,$settings);
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        $form_field = apply_filters('msdlab_csf_'.$id.'_field','<div class="ui-toggle-btn">
        <input id="'.$id.'_input" name="'.$id.'_input" type="checkbox" value="1"'.checked($value,1,false).' '.$this->build_validation($validation).' />
        <div class="handle" data-on="'.$settings['true'].'" data-off="'.$settings['false'].'"></div></div>');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function field_date($id, $value = null, $title = "Date", $validation = null, $class = array('datepicker')){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        $form_field = apply_filters('msdlab_csf_'.$id.'_field','<input id="'.$id.'_input" name="'.$id.'_input" type="date" value="'.$value.'" placeholder="'.$title.'" '.$this->build_validation($validation).' />');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function field_textfield($id, $value = null, $title = "", $placeholder = null, $validation = null, $class = array('medium')){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        $type = isset($validation['type'])?$validation['type']:'text';
        if($placeholder == null){$placeholder = $title;}
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        $form_field = apply_filters('msdlab_csf_'.$id.'_field','<input id="'.$id.'_input" name="'.$id.'_input" type="'.$type.'" value="'.$value.'" placeholder="'.$placeholder.'" '.$this->build_validation($validation).' />');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function field_textarea($id, $value = null, $title = "", $validation = null, $class = array('textarea')){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        ob_start();
        wp_editor( stripcslashes($value), $id.'_input', array('media_buttons' => false,'teeny' => true,'textarea_rows' => 5) );
        $form_field = ob_get_clean();
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function field_select($id, $value = null, $title = "", $null_option = null, $options = array(), $validation = null, $class = array('select')){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        if($null_option == null){$null_option = 'Select';}
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        //iterate through $options
        $options_str = implode("\n\r",$this->build_options($options,$value,$null_option));
        $select = '<select id="'.$id.'_input" name="'.$id.'_input" '.$this->build_validation($validation).'>'.$options_str.'</select>';
        $form_field = apply_filters('msdlab_csf_'.$id.'_field', $select );
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function build_options($options,$value,$null_option){
        $ret = array();
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

    public function field_radio($id, $value = null, $title = "", $options = array(), $validation = null, $class = array('radio')){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        //iterate through $options
        foreach ($options AS $k => $v){
            $options_array[] = '<div class="'.$id.'_'.$k.'_wrapper option-wrapper"><input id="'.$id.'_'.$k.'" name="'.$id.'" type="radio" value="'.$k.'"'.selected($value,$k,false).' /> <label class="option-label">'.$v.'</label></div>';
        }

        $options_str = '<div class="radio-wrapper">'.implode("\n\r",$options_array).'</div>';
        $form_field = apply_filters('msdlab_csf_'.$id.'_field', $options_str );
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }


    public function field_checkbox_array($id, $value = null, $title = "", $options = array(), $validation = null, $class = array('checkbox')){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        //iterate through $options
        foreach ($options AS $k => $v){
            $options_array[] = '<div class="'.$id.'_'.$k.'_wrapper checkbox-wrapper"><input id="'.$id.'_'.$k.'" name="'.$id.'" type="checkbox" value="'.$k.'"'.selected($value,$k,false).' /> '.$v.'</div>';
        }

        $options_str = implode("\n\r",$options_array);
        $form_field = apply_filters('msdlab_csf_'.$id.'_field', $options_str );
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }


    public function field_button($id,$title = "Save", $class = array('submit'), $type = "submit"){
        $form_field = apply_filters('msdlab_csf_'.$id.'_button','<input id="'.$id.'_button" type="'.$type.'" value="'.$title.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function build_validation($validation_array){
        if(is_null($validation_array)){return;}
        foreach($validation_array AS $k => $v){
            $validation_str[] = $k . ' = "' . $v .'"';
        }
        return implode(' ',$validation_str);
    }

}