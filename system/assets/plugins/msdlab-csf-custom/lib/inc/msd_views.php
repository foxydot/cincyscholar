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
            $exh[] = $value;
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
        foreach ($info as $key => $value) {
            $ret[] = '<div class=""><label>'.$key.': </label><span class="">'.$value.'</span></div>';

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
                    default:
                        $printval = $user->{$value};
                        break;
                }
                $row[] = '<td class="'.$value.'">'.$printval.'</td>';
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
    public function print_export_tools(){
        $ret =  '<form name="export" action="'.plugin_dir_url(__FILE__).'exporttocsv.php" method="post">
        <input type="submit" value="Export table to CSV">
        <input type="hidden" value="Cincinnati Scholarship Foundation Application Report" name="csv_hdr">
        <input type="hidden" value=\''.$this->export_header."\n".$this->export_csv.'\' name="csv_output">
        </form>';
        return $ret;
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
        $ret['export'] = $this->print_export_tools();

        if($echo){
            print implode("\n\r", $ret);
        } else {
            return $ret;
        }
    }

    public function csv_safe($value){
        $value = preg_replace('%\'%i','‘',$value);
        return $value;
    }

}