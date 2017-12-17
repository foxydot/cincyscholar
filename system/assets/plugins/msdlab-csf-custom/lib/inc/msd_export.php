<?php
class MSDLAB_Export{

    private $variable;

    /**
     * A reference to an instance of this class.
     */
    private static $instance;


    /**
     * Returns an instance of this class.
     */
    public static function get_instance() {

        if( null == self::$instance ) {
            self::$instance = new MSDLAB_Export();
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
    public function table_header($fields = array(), $class = array(), $echo = true){
        $ret = array();
        foreach($fields AS $field){
            $ret[] = '<th>'.$field->title.'</th>';
        }
    }

}