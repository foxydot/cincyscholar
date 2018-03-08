<?php
class MSDLAB_Display{

    private $variable;

    private $export_header;

    private $export_csv;

    /**
     * A reference to an instance of this class.
     */
    private static $instance;


    /**
     * Returns an instance of this class.
     */
    public static function get_instance() {

        if( null == self::$instance ) {
            self::$instance = new MSDLAB_Display();
        }

        return self::$instance;

    }

    public function __construct() {
        if(class_exists('MSDLAB_Queries')){
            $this->queries = new MSDLAB_Queries();
        }

    }

    /**
     * Create a Table Header for the result set display
     *
     * @param array $fields An array of field objects.
     * @param array $class An array of class names to add to the wrapper.
     * @param bool $echo Whether to print the return value with appropriate wrappers, or return it.
     *
     * @return string The header to be printed, or void if the param $echo is true.
     */
    public function table_header($fields, $echo = true){
        $ret = array();
        $exh = array();
        foreach($fields AS $key => $value){
            $ret[] = '<th>'.$value.'</th>';
            $exh[] = $this->csv_safe($value);
        }

        $this->export_header = implode(",",$exh);

        if($echo){
            print $ret = apply_filters('msdlab_csf_report_display_table_header','<tr>'.implode("\n\r", $ret).'<tr>');
        } else {
            return '<tr>'.implode("\n\r", $ret).'<tr>';
        }
    }


    /**
     * Create a Table Footer for the result set display
     *
     * @param array $fields An array of field objects.
     * @param array $info The result information.
     * @param bool $echo Whether to print the return value with appropriate wrappers, or return it.
     *
     * @return string The footer to be printed, or void if the param $echo is true.
     */
    public function table_footer($fields, $info, $echo = true){
        $ret = array();
        $numfields = count($fields);
        if(count($info)>0) {
            foreach ($info as $key => $value) {
                $ret[] = '<div class=""><label>' . $key . ': </label><span class="">' . $value . '</span></div>';
            }
        }

        $ret = apply_filters('msdlab_csf_report_display_table_footer', '<th colspan="'.$numfields.'">'.implode("\r\n",$ret).'</th>');

        if($echo){
            print '<tr>'.$ret.'</tr>';
        } else {
            return '<tr>'.$ret.'</tr>';
        }
    }

    /**
     * Prepare result set in a nice table
     *
     * @param array $fields An array of field objects.
     * @param array $info The result information.
     * @param bool $echo Whether to print the return value with appropriate wrappers, or return it.
     *
     * @return string The footer to be printed, or void if the param $echo is true.
     */
    public function table_data($fields, $result, $echo = true){
        $ret = array();
        $ecsv = array();
        $i = 0;
        foreach($result as $k => $user){
            $row = array();
            $erow = array();
            foreach ($fields as $key => $value) {
                switch ($value){
                    case 'ApplicantId':
                        $portal_page = get_option('csf_settings_student_welcome_page');
                        $printval = '<a href="'.get_permalink($portal_page).'?applicant_id='.$user->{$value}.'" target="_blank">'.$user->{$value}.'</a>';
                        break;
                    case 'CountyId':
                        $printval = $this->queries->get_county_by_id($user->{$value});
                        break;
                    case 'StateId':
                        $printval = $this->queries->get_state_by_id($user->{$value});
                        break;
                    case 'CollegeId':
                        $printval = $this->queries->get_college_by_id($user->{$value});
                        break;
                    case 'MajorId':
                        $printval = $this->queries->get_major_by_id($user->{$value});
                        break;
                    case 'SexId':
                        $printval = $this->queries->get_sex_by_id($user->{$value});
                        break;
                    case 'EducationAttainmentId':
                        $printval = $this->queries->get_educationalattainment_by_id($user->{$value});
                        break;
                    case 'EthnicityId':
                        $printval = $this->queries->get_ethnicity_by_id($user->{$value});
                        break;
                    case 'HighSchoolId':
                        $printval = $this->queries->get_highschool_by_id($user->{$value});
                        break;
                    case 'FirstGenerationStudent':
                    case 'IsIndependent':
                    case 'PlayedHighSchoolSports':
                    case 'CPSPublicSchools':
                    case 'InformationSharingAllowed':
                    case 'IsComplete':
                    case 'ApplicantHaveRead':
                    case 'ApplicantDueDate':
                    case 'ApplicantDocsReq':
                    case 'ApplicantReporting':
                    case 'GuardianHaveRead':
                    case 'GuardianDueDate':
                    case 'GuardianDocsReq':
                    case 'GuardianReporting':
                    case 'Homeowner':
                    case 'InformationSharingAllowedByGuardian':
                        $printval = $user->{$value}>0?'Yes':'No';
                        break;
                    case 'Activities':
                    case 'HardshipNote':
                        $printval = strip_tags($user->{$value});
                        break;
                    default:
                        $printval = $user->{$value};
                        break;
                }
                $row[] = '<td class="'.$value.'"><div>'.$printval.'</div></td>';
                $erow[] = $this->csv_safe($printval);
            }
            $class = $i%2==0?'even':'odd';
            $ret[] = '<tr class="'.$class.'">'.implode("\n\r", $row).'</tr>';
            $ecsv[] = implode(",",$erow);
            $i++;
        }

        $this->export_csv = implode("\n", $ecsv);

        if($echo){
            print implode("\n\r", $ret);
        } else {
            return implode("\n\r", $ret);
        }
    }

    /**
     *
     */
    public function print_export_tools($id){
        $temp_filename = 'Cincinnati Scholarship Foundation Application Report '.$id.'_'.date("Y-m-d_H-i",time()).'.csv';
        //create or locate upload dir for tempfiles
        $upload_dir   = wp_upload_dir();
        if ( ! empty( $upload_dir['basedir'] ) ) {
            $temp_dirname = $upload_dir['basedir'].'/exports/temp';
            $temp_url = $upload_dir['baseurl'].'/exports/temp/'.$temp_filename;
            //TODO: add a cron to clean out this directory once a day.
            if ( ! file_exists( $temp_dirname ) ) {
                wp_mkdir_p( $temp_dirname );
            }
        }
        //create an empty file and open for writing
        $temp_file = fopen($temp_dirname.'/'.$temp_filename,'w+b');
        //write to file
        fwrite($temp_file,$this->export_header."\n".$this->export_csv);
        fclose($temp_file);
        $ret['form'] = '<a href="'.$temp_url.'" id="csv_export_'.$id.'" class="button csv-export export-'.$id.'">Export to CSV</a>';
        return implode("\n\r", $ret);
    }


    /**
     * Print a table
     *
     * @param array $fields An array of field objects.
     * @param array $info The result information.
     * @param bool $echo Whether to print the return value with appropriate wrappers, or return it.
     *
     * @return string The footer to be printed, or void if the param $echo is true.
     */
    public function print_table($id, $fields, $result, $info, $class = array(), $echo = true){
        $class = implode(" ",apply_filters('msdlab_csf_report_display_table_class', $class));
        $ret = array();
        $ret['start_table'] = '<table id="'.$id.'" class="'.$class.'">';
        $ret['table_header'] = $this->table_header($fields,false);
        $ret['table_data'] = $this->table_data($fields,$result,false);
        $ret['table_footer'] = $this->table_footer($fields,$info,false);
        $ret['end_table'] = '</table>';
        $ret['export'] = $this->print_export_tools($id);

        if($echo){
            print implode("\n\r", $ret);
        } else {
            return $ret;
        }
    }

    public function csv_safe($value){
        $value = preg_replace('%\'%i','‘',$value);
        $value = strip_tags($value,'<p><a>');
        $value = preg_replace("/<a.+href=['|\"]([^\"\']*)['|\"].*>(.+)<\/a>/i",'\2 (\1)',$value);
        $value = preg_replace('^[\r\n]+^',"\n",$value);
        $value = '"'.$value.'"';
        return $value;
    }
}