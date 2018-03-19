<?php
class MSDLab_ReportControls{

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
        $this->queries = new MSDLAB_Queries();
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

    public function search_box($title = "Search Students",$button = "SEARCH", $id = "student_search", $class = array('query-filter','search-box')){
       $label = apply_filters('msdlab_csf_manage_search_label','<label for="'.$id.'_input">'.$title.'</label>');
       $form_field = apply_filters('msdlab_csf_manage_search_form_field','<input id="'.$id.'_input" name="'.$id.'_input" type="search" value="'.$_POST[$id.'_input'].'" placeholder="'.$title.'" />');
       $class = implode(" ",apply_filters('msdlab_csf_manage_search_class', $class));
       $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field.'</div>';
       return apply_filters('msdlab_csf_manage_search', $ret);
   }

   public function college_search($title = "College",$id = "college_search", $class = array('query-filter','college-search')){
        $colleges = $this->queries->get_select_array_from_db('College', 'CollegeId', 'Name','Name');
       $options = array('<option value=""' . selected("", $default, false) . '>---Select---</option>');

       foreach($colleges AS $k => $v){
           if(empty( $_POST )) {
               $options[] = '<option value="' . $k . '"' . selected($k, $default, false) . '>' . $v . '</option>';
           } else {
               $options[] = '<option value="' . $k . '"' . selected($k, $_POST[$id . '_input'], false) . '>' . $v . '</option>';
           }
       }

       $label = apply_filters('msdlab_csf_manage_college_search_label','<label for="'.$id.'_input">'.$title.'</label>');
       $form_field = '<select id="'.$id.'_input" name="'.$id.'_input">'.implode("\r\n",$options).'</select>';
       $form_field = apply_filters('msdlab_csf_manage_college_search_form_field',$form_field);
       $button = apply_filters('msdlab_csf_manage_college_search_button','<input id="'.$id.'_button" type="submit" value="'.$button.'" />');
       $class = implode(" ",apply_filters('msdlab_csf_manage_college_search_class', $class));
       $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field.'</div>';
       return apply_filters('msdlab_csf_manage_college_search', $ret);
   }

    public function select_search($title = "Select", $id = "select_search", $data = array(), $class = array('query-filter','college-search')){
        $options = array('<option value=""' . selected("", $default, false) . '>---Select---</option>');

        foreach($data AS $k => $v){
            if(empty( $_POST )) {
                $options[] = '<option value="' . $k . '"' . selected($k, $default, false) . '>' . $v . '</option>';
            } else {
                $options[] = '<option value="' . $k . '"' . selected($k, $_POST[$id . '_input'], false) . '>' . $v . '</option>';
            }
        }

        $label = apply_filters('msdlab_csf_manage_college_search_label','<label for="'.$id.'_input">'.$title.'</label>');
        $form_field = '<select id="'.$id.'_input" name="'.$id.'_input">'.implode("\r\n",$options).'</select>';
        $form_field = apply_filters('msdlab_csf_manage_college_search_form_field',$form_field);
        $button = apply_filters('msdlab_csf_manage_college_search_button','<input id="'.$id.'_button" type="submit" value="'.$button.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_manage_college_search_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field.'</div>';
        return apply_filters('msdlab_csf_manage_college_search', $ret);
    }

   public function role_search($title = "Limit To",$button = "SEARCH", $id = "role_search", $class = array('query-filter','role-search'), $roles = array()){
        if(count($roles)<1){
            $roles = array(
                'Entire Database' => '',
                'Incomplete' => 'I',
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
    }

    public function date_search_type($title = "Limit by Date",$button = "SEARCH", $id = "date_search_type", $class = array('query-filter','date-search-type'), $types = array()){
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
    }

    public function date_search($title = "Between Dates",$button = "SEARCH", $id = "date_search", $class = array('query-filter','date-search','hidden'), $start_date = FALSE, $end_date = FALSE ){
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
    }

    public function number_range_search($title = "Between",$button = "SEARCH", $id = "num_range_search", $class = array('query-filter','num-range-search'), $start_num = FALSE, $end_num = FALSE, $step = 1 ){
        $start_num = $_POST[$id.'_input_start']?$_POST[$id.'_input_start']:$start_num;
        $end_num = $_POST[$id.'_input_send']?$_POST[$id.'_input_end']:$end_num;
        $label = apply_filters('msdlab_csf_manage_num_range_search_label','<label for="'.$id.'_input">'.$title.'</label>');
        $form_field_start = apply_filters('msdlab_csf_manage_num_range_search_start_form_field','<input id="'.$id.'_input_start" name="'.$id.'_input_start" type="number" value="'.$start_num.'" step="'.$step.'" class="num-range" />');
        $form_field_end = apply_filters('msdlab_csf_manage_num_range_search_end_form_field','<input id="'.$id.'_input_end" name="'.$id.'_input_end" type="number" value="'.$end_num.'" step="'.$step.'" class="num-range" />');
        $button = apply_filters('msdlab_csf_manage_num_range_search_button','<input id="'.$id.'_button" type="submit" value="'.$button.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_manage_num_range_search_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$label.$form_field_start.$form_field_end.'</div>';
        return apply_filters('msdlab_csf_manage_num_range_search', $ret);
    }

    public function search_button($button = "SEARCH", $id = "search_button", $class = array('search-button')){
        $button = apply_filters('msdlab_csf_manage_search_button','<input id="'.$id.'_button" name="'.$id.'_button" type="submit" class="button button-primary" value="'.$button.'" />');
        $class = implode(" ",apply_filters('msdlab_csf_manage_search_button_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$button.'</div>';
        return apply_filters('msdlab_csf_manage_search_button', $ret);
    }
    public function reset_button($button = "RESET", $id = "reset_button", $class = array('reset-button')){
        $button = apply_filters('msdlab_csf_manage_reset_button','<a href="" id="'.$id.'_button" name="'.$id.'_button" type="reset" class="button button-primary">'.$button.'</a>');
        $class = implode(" ",apply_filters('msdlab_csf_manage_reset_button_class', $class));
        $ret = '<div id="'.$id.'" class="'.$class.'">'.$button.'</div>';
        $this->javascript[] = '';
        return apply_filters('msdlab_csf_manage_reset_button', $ret);
    }

    public function build_javascript(){
        $ret = '
        <script>
  jQuery(function($){
    '.implode(" ",apply_filters('msdlab_csf_manage_search_javascript', $this->javascript)).'
  });
  </script>';
        return $ret;
    }

    public function print_form($echo = true){
        $ret = array();
        $ret['search_all_button'] = $this->search_button('Load All Applications');
        $ret['search_by_name'] = $this->search_box('Search By Name','','name_search');
        $ret['search_by_email'] = $this->search_box('Search By Email','','email_search');
        $ret['search_by_city'] = $this->search_box('Search By City','','city_search');
        $states = $this->queries->get_select_array_from_db('state', 'StateId', 'State','State');
        $ret['state_search'] = $this->select_search('State: ','state_search', $states);
        $counties = $this->queries->get_select_array_from_db('county', 'CountyId', 'County','County');
        $ret['county_search'] = $this->select_search('County: ','county_search', $counties);
        $ret['search_by_zip'] = $this->search_box('Search By ZipCode (comma separated list)','','zip_search');
        $colleges = $this->queries->get_select_array_from_db('college', 'CollegeId', 'Name','Name');
        $ret['college_search'] = $this->select_search('College: ','college_search', $colleges);
        $highschools = $this->queries->get_select_array_from_db('highschool', 'HighSchoolId', 'SchoolName','SchoolName');
        $ret['highschool_search'] = $this->select_search('High School: ','highschool_search', $highschools);
        $highschooltypes = $this->queries->get_select_array_from_db('highschooltype', 'HighSchoolTypeId', 'Description','HighSchoolTypeId');
        $ret['highschool_type_search'] = $this->select_search('High School Type: ','highschooltype_search', $highschooltypes);
        $ret['gpa_search'] = $this->number_range_search('GPA Between','','gpa_range_search',array('query-filter'),0.00,5.00,0.1);
        $majors = $this->queries->get_select_array_from_db('major', 'MajorId', 'MajorName','MajorName');
        $ret['major_search'] = $this->select_search('Major: ','major_search', $majors);
        $ethnicity = $this->queries->get_select_array_from_db('ethnicity', 'EthnicityID', 'Ethnicity','EthnicityID');
        $ret['ethnicity_search'] = $this->select_search('Ethnicity: ','ethnicity_search', $ethnicity);
        $athletics = array('0'=>'Non-althlete','1'=>'Athlete');
        $ret['athlete_search'] = $this->select_search('Althletics:','athlete_search',$athletics);
        $independence = array('0'=>'Dependent','1'=>'Independant');
        $ret['independence_search'] = $this->select_search('Independence:','independence_search',$independence);
        $ret['search_by_employer'] = $this->search_box('Employer','','employer_search'); //this is handled AFTER the query
        /*
Need (there should be a place in the database where cost of attendance, EFC, grants, loans, federal and state aid are entered and calculated)
*/
        $ret['search_button'] = $this->search_button();
        $ret['reset_button'] = $this->reset_button();
        $ret['nonce'] = wp_nonce_field( 'records_search' );
        $ret['javascript'] = $this->build_javascript();

        if($echo){
            print $this->form_header();
            print implode("\n\r", $ret);
            print $this->form_footer();
        } else {
            return $ret;
        }
    }
}