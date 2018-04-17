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

        add_action( 'wp_ajax_remove_pdf', array(&$this,'delete_file') );
        add_action( 'wp_ajax_nopriv_remove_pdf', array(&$this,'delete_file') );

        if(class_exists('MSDLAB_Queries')){
            $this->queries = new MSDLAB_Queries();
        }
    }


    public function form_header($id = "csf_form", $class = array()){
        $class = implode(" ",apply_filters('msdlab_'.$id.'_header_class', $class));
        $ret = '<form id="'.$id.'" class="'.$class.'" method="post" enctype="multipart/form-data">';
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
        $ret = '<div class="row"><h3 id="'.$id.'_wrapper" class="col-sm-12 '.$class.'">'.$value.'</h3></div>';
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
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$form_field.'</div>';
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
        $bkp_field = apply_filters('msdlab_csf_'.$id.'_bkp_field','<input id="'.$id.'_input" name="'.$id.'_input" type="hidden" value="0" />');
        $form_field = apply_filters('msdlab_csf_'.$id.'_field','<div class="ui-toggle-btn">
        <input id="'.$id.'_input" name="'.$id.'_input" type="checkbox" value="1"'.checked($value,1,false).' '.$this->build_validation($validation).' />
        <div class="handle" data-on="'.$settings['true'].'" data-off="'.$settings['false'].'"></div></div>');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$label.$bkp_field.$form_field.'</div>';
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

    public function field_textfield($id, $value, $title = "", $placeholder = null, $validation = null, $class = array('medium')){
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
        if(is_null($value)  || empty($value)){
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

    public function field_radio($id, $value = null, $title = "", $options = array(), $validation = null, $class = array('radio')){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        //iterate through $options
        foreach ($options AS $k => $v){
            $options_array[] = '<div class="'.$id.'_'.$k.'_wrapper option-wrapper"><input id="'.$id.'_'.$k.'_input" name="'.$id.'_input" type="radio" value="'.$k.'"'.checked($value,$k,false).' /> <label class="option-label">'.$v.'</label></div>';
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
            $options_array[] = '<div class="'.$id.'_'.$k.'_wrapper checkbox-wrapper"><input id="'.$id.'_'.$k.'" name="'.$id.'" type="checkbox" value="'.$k.'"'.checked($value,$k,false).' /> '.$v.'</div>';
        }

        $options_str = implode("\n\r",$options_array);
        $form_field = apply_filters('msdlab_csf_'.$id.'_field', $options_str );
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function field_checkbox($id, $value = 0, $title = "", $validation = null, $class = array('checkbox','col-md-12')){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        $form_field = apply_filters('msdlab_csf_'.$id.'_field','<input id="'.$id.'_input" name="'.$id.'_input"  value="1" type="checkbox" '.checked(1,$_POST[$id.'_input'],0).' />');
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$form_field.$title.'</label>');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$label.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function field_upload($id, $value, $title = "", $placeholder = null, $validation = null, $class = array('medium')){
        if(is_object($value)){
            $uploadshow = ' hidden';
            $attachment_id = $value->AttachmentId;
        } else {
            $fileshow = ' hidden';
            $attachment_id = $id.'_delete_btn';
        }
        $type = isset($validation['type'])?$validation['type']:'file';
        $attachment_types = array_flip($this->queries->get_attachment_type_ids());
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_input">'.$title.'</label>');
        $filename = array_pop(explode('/',$value->FilePath));
        $fileext = strtolower(array_pop(explode('.',$filename)));
        $file_display =  '<div class="document'.$fileshow.'">
                    <a href="'.$value->FilePath.'" title="'.$filename.'" class="file-link"><i class="fa fa-file-pdf-o" aria-hidden="true"></i><br /><span class="filename">'.$filename.'</span><br><span class="filecat hidden">'.$attachment_types[$value->AttachmentTypeId].'</span></a>
                    <button class="file-delete" value="'.$value->FilePath.'" id="'.$attachment_id.'">Delete</button>
                </div>';
        $form_field = '<div class="box'.$uploadshow.'">
        <div class="box__input">
			<svg class="box__icon" xmlns="http://www.w3.org/2000/svg" width="50" height="43" viewBox="0 0 50 43"><path d="M48.4 26.5c-.9 0-1.7.7-1.7 1.7v11.6h-43.3v-11.6c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v13.2c0 .9.7 1.7 1.7 1.7h46.7c.9 0 1.7-.7 1.7-1.7v-13.2c0-1-.7-1.7-1.7-1.7zm-24.5 6.1c.3.3.8.5 1.2.5.4 0 .9-.2 1.2-.5l10-11.6c.7-.7.7-1.7 0-2.4s-1.7-.7-2.4 0l-7.1 8.3v-25.3c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v25.3l-7.1-8.3c-.7-.7-1.7-.7-2.4 0s-.7 1.7 0 2.4l10 11.6z"></path></svg>
			<input type="file" name="'.$id.'_input" id="'.$id.'_input" class="box__file">
			<label for="'.$id.'_input" class="button"><strong>Choose File</strong></label>
		</div>
	</div>';
        $form_field = apply_filters('msdlab_csf_'.$id.'_field',$form_field);
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="upload-wrapper '.$class.'">'.$label.$file_display.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function get_files_of_type($type,$data){
        $attachment_types = array_flip($this->queries->get_attachment_type_ids());
        foreach($data AS $d){
            if($attachment_types[$d->AttachmentTypeId] == $type){
                $ret = $d;
            }
        }
        return $ret;
    }

    public function field_button($id,$title = "Save", $class = array('submit'), $type = "submit", $validate = true){
        if($validate == false){$atts = ' formnovalidate=formnovalidate ';}
        $form_field = apply_filters('msdlab_csf_'.$id.'_button','<input id="'.$id.'_button" type="'.$type.'" value="'.$title.'"'.$atts.'/>');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function build_validation($validation_array){
        if(is_null($validation_array)){return;}
        foreach($validation_array AS $k => $v){
            $validation_str[] = $k . ' = "' . $v .'"';
        }
        if($validation_str)
        return implode(' ',$validation_str);
    }


    public function field_result($id, $value, $title = "", $class = array('medium')){
        if(is_null($value)){
            $value = $_POST[$id.'_input'];
        }
        if($placeholder == null){$placeholder = $title;}
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_result">'.$title.'</label>');
        $form_field = apply_filters('msdlab_csf_'.$id.'_field','<span class="result">'.$value.'</span>');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

    public function file_management_front_end($id_prepend,$documents,$class){
        $ret[$id_prepend.'Resume'] = $this->field_upload($id_prepend.'Resume',$this->get_files_of_type('Resume',$documents),'Resume',null,null,$class);
        $ret[$id_prepend.'Transcript'] = $this->field_upload($id_prepend.'Transcript',$this->get_files_of_type('Transcript',$documents),'Transcript',null,null,$class);
        $ret[$id_prepend.'FAFSA'] = $this->field_upload($id_prepend.'FAFSA',$this->get_files_of_type('FAFSA',$documents),'Student Aid Report',null,null,$class);
        $ret[$id_prepend.'FinancialAidAward'] = $this->field_upload($id_prepend.'FinancialAidAward',$this->get_files_of_type('FinancialAidAward',$documents),'Financial Aid Award Letter From College',null,null,$class);
        $ret[$id_prepend.'Additional_1'] = $this->field_upload($id_prepend.'Additional_1',$this->get_files_of_type('Additional_1',$documents),'Additional Document Requested by CSF',null,null,$class);
        $ret[$id_prepend.'Additional_2'] = $this->field_upload($id_prepend.'Additional_2',$this->get_files_of_type('Additional_2',$documents),'Additional Document Requested by CSF',null,null,$class);
        $ret[$id_prepend.'Additional_3'] = $this->field_upload($id_prepend.'Additional_3',$this->get_files_of_type('Additional_3',$documents),'Additional Document Requested by CSF',null,null,$class);
        $ret[$id_prepend.'Additional_4'] = $this->field_upload($id_prepend.'Additional_4',$this->get_files_of_type('Additional_4',$documents),'Additional Document Requested by CSF',null,null,$class);

        return implode("\n",apply_filters('msdlab_csf_file_management_front_end',$ret));
    }

    public function delete_file(){
        global $wpdb;
        $filename = ( $_POST['filename'] );
        $ret = array();
        if(file_exists($filename)){
            $ret['file_exists'] = true;
            if(unlink($filename)){
                $ret['file_deleted'] = true;
            } else {
                $ret['file_deleted'] = false;
            }
        } else {
            $ret['file_exists'] = false;
        }
        $attachment_id = ( $_POST['attachment_id'] );
        $sql = 'DELETE FROM `attachment` WHERE `AttachmentId` = '.$attachment_id.' LIMIT 1;';
        if($result = $wpdb->get_results($sql)){
            $ret['database_deleted'] = true;
        }
        print(json_encode($ret));
        wp_die();
    }

    public function get_file_manager_ajax($id_prepend,$documents){
        $ret = array();
        $ret['del'] = "$('.file-delete').click(function(e){
                e.preventDefault();
                var my_upload_wrapper = $(this).parents('.upload-wrapper');
                var ajaxurl = '". admin_url( 'admin-ajax.php' ) ."';
                var myfn = $(this).attr('value');
                var att_id = $(this).attr('id');
                var data = {
                    action: 'remove_pdf',
                    filename: myfn,
                    attachment_id: att_id
                    }
                $.post(ajaxurl, data, function(response){
                    my_upload_wrapper.find('.document').addClass('hidden');
                    my_upload_wrapper.find('.box').removeClass('hidden').find('.box__file').attr('value','');
                    console.log(response);
                },'json');
             });";

        $ret['uploader'] = "$('.box__file').change(function(e){
            var myfn = $(this).attr('value').replace(/^.*[\\\/]/, '')
            var str_sub = myfn.substr(myfn.lastIndexOf(\".\")+1);
            if(str_sub == 'pdf'){
                var my_upload_wrapper = $(this).parents('.upload-wrapper');
                my_upload_wrapper.find('.document').removeClass('hidden').find('.filename').html(myfn);
                my_upload_wrapper.find('.box').addClass('hidden');
            } else {
                alert('Please upload all documents in PDF format.');
                $(this).attr('value','');
            }
        });";

        return implode("\n",apply_filters('msdlab_csf_file_management_ajax',$ret));
    }

    public function attachment_display($id, $data, $title = "", $class = "", $display = "all", $style = "grid"){
        $label = apply_filters('msdlab_csf_'.$id.'_label','<label for="'.$id.'_result">'.$title.'</label>');
        $attachment_types = array_flip($this->queries->get_attachment_type_ids());
        if($display == "all" && $style == "grid"){
            foreach($data AS $d){
                $filename = array_pop(explode('/',$d->FilePath));
                $fileext = strtolower(array_pop(explode('.',$filename)));
                $grid[] = '<div class="col-xs-6 col-sm-2 document grid-item">
                    <a href="'.$d->FilePath.'" title="'.$filename.'"><i class="fa fa-file-'.$fileext.'-o" aria-hidden="true"></i><br /><span class="filename">'.$filename.'</span><br><span class="filecat">'.$attachment_types[$d->AttachmentTypeId].'</span></a>
                </div>';
            }
            if(count($grid)>0){
                $value = '<div class="row documents grid">'.implode('',$grid).'</div>';
            } else {
                $value = '<div class="row documents grid"><div class="col-xs-12">No documents found.</div></div>';
            }
        }
        $form_field = apply_filters('msdlab_csf_'.$id.'_field','<span class="result">'.$value.'</span>');
        $class = implode(" ",apply_filters('msdlab_csf_'.$id.'_class', $class));
        $ret = '<div id="'.$id.'_wrapper" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_'.$id.'', $ret);
    }

}