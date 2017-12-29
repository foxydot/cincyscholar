<?php
if (!class_exists('MSDLab_CSF_Management')) {
    class MSDLab_CSF_Management {
        //Properties

        //Methods
        /**
         * PHP 5 Constructor
         */
        function __construct(){
            global $current_screen;
            //TODO: Add a user management panel
            //TODO: Add a scholarship management panel
            $required_files = array('msd_csf_application','msd_controls','msd_export','msd_queries','msd_views');
            foreach($required_files AS $rq){
                if(file_exists(plugin_dir_path(__FILE__).'/'.$rq . '.php')){
                    require_once(plugin_dir_path(__FILE__).'/'.$rq . '.php');
                } else {
                    ts_data(plugin_dir_path(__FILE__).'/'.$rq . '.php does not exisit');
                }
            }

            if(class_exists('MSDLab_CSF_Application')){
                $this->application = new MSDLab_CSF_Application();
            }
            if(class_exists('MSDLAB_QueryControls')){
                $this->controls = new MSDLAB_QueryControls();
            }
            if(class_exists('MSDLAB_Queries')){
                $this->queries = new MSDLAB_Queries();
            }
            if(class_exists('MSDLAB_Display')){
                $this->display = new MSDLAB_Display();
            }

            //register stylesheet
            //Actions
            add_action('admin_menu', array(&$this,'settings_page'));
            add_action('wp_enqueue_scripts', array(&$this,'add_styles_and_scripts'));
            //Filters

            //Shortcodes

        }

        function add_admin_styles_and_scripts(){
            wp_enqueue_style('bootstrap-style','//maxcdn.bootstrapcdn.com/bootstrap/latest/css/bootstrap.min.css',false,'4.5.0');
            wp_enqueue_style('font-awesome-style','//maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css',false,'4.5.0');
            wp_enqueue_script('bootstrap-jquery','//maxcdn.bootstrapcdn.com/bootstrap/latest/js/bootstrap.min.js',array('jquery'));
        }

        function add_styles_and_scripts(){
        }

        function settings_page(){
            add_menu_page(__('CSF Management and Reports'),__('CSF'), 'administrator', 'csf-settings', array(&$this,'setting_page_content'),'dashicons-chart-area');
            add_submenu_page('csf-settings',__('Settings'),__('Settings'),'administrator','csf-settings', array(&$this,'setting_page_content'));
        }

        function setting_page_content(){
            //page content here
            if($msg = $this->queries->set_option_data('csf_settings')){
                print '<div class="updated notice notice-success is-dismissible">'.$msg.'</div>';
            }
            print '<h2>Scholarship Application Period</h2>';
            $this->controls->print_settings();
        }

        //ultilities


        //db funzies

    } //End Class
} //End if class exists statement