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
            $ret[] = '<th>'.$key.'</th>';

            if($key != 'Profile' && $key != 'Edit Member' && $key != 'Application' && $key != 'Documentation') {
                $exh[] = '"' . $key . '"';
            }
        }

        $this->export_header = implode(",",$exh);

        if($echo){
            print $ret = apply_filters('msdlab_ninc_report_display_table_header','<tr>'.implode("\n\r", $ret).'<tr>');
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

        $ret = apply_filters('msdlab_ninc_report_display_table_footer', '<th colspan="'.$numfields.'">'.implode("\r\n",$ret).'</th>');

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
        foreach($result as $user){
            $fields = array(
                'Legal Name' => $user->meta['legal_name'][0],
                'Pen Name(s)' => $user->data->application[5], //not a perfect solution
                'WP Email' => $user->user_email, //hotlink
                'NincLink Email' => $user->meta['secondary_email'][0],///?????? //hotlink
                'Application Date'  => MSDLAB_Queries::get_application_date($user), //application date OR application date from meta OR join date from meta,
                'Renewal Date' => $user->meta['date_last_renewed'][0], //latest form renewal date OR new member dues date OR renewal date from meta,
                'Paid Thru Date' => $user->meta['date_paid_thru'][0], //latest form renewal date OR new member dues date OR renewal date from meta,
                'Last Login Date' => date('Y-m-d',strtotime($user->meta['last_activity'][0])),
                'Country' => $user->meta['address_country'][0],
                'ACA Survey Date' => $user->meta['date_last_survey'][0],//date of latest ACA survey form, //hotlink to form entry
                'Profile' => MSDLAB_Queries::get_user_profile_link($user),
                'Edit Member' => MSDLAB_Queries::get_user_edit_link($user),
                'Application' => MSDLAB_Queries::get_user_application_link($user),
                'Documentation' => MSDLAB_Queries::get_user_verify_link($user),
                'Conference Registration' => $user->data->conference,
            );
            $row = array();
            $erow = array();
            foreach ($fields as $key => $value) {
                $row[] = '<td class="'.$key.'">'.$value.'</td>';
                if($key != 'Profile' && $key != 'Edit Member' && $key != 'Application' && $key != 'Documentation') {
                    $erow[] = '"' . convert_quotes_for_export($value) . '"';
                }
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
        $ret =  '<form name="export" action="'.plugin_dir_url(__FILE__).'../lib/exporttocsv.php" method="post">
        <input type="submit" value="Export table to CSV">
        <input type="hidden" value="ninc_member_report" name="csv_hdr">
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
        $class = implode(" ",apply_filters('msdlab_ninc_report_display_table_class', $class));
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

}