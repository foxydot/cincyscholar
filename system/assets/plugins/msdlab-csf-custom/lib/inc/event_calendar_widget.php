<?php
/**
 * Gronked from: Widget API: WP_Widget_Calendar class
 *
 * @package WordPress
 * @subpackage Widgets
 * @since 4.4.0
 */

/**
 * Core class used to implement the Calendar widget.
 *
 * @since 2.8.0
 *
 * @see WP_Widget
 */
class MSDLab_Events_Calendar_Widget extends WP_Widget {
    /**
     * Ensure that the ID attribute only appears in the markup once
     *
     * @since 4.4.0
     *
     * @static
     * @access private
     * @var int
     */
    private static $instance = 0;

    /**
     * Sets up a new Calendar widget instance.
     *
     * @since 2.8.0
     * @access public
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'events_calendar_widget',
            'description' => __( 'A calendar of Events.' ),
            'customize_selective_refresh' => true,
        );
        parent::__construct( 'events_calendar', __( 'Events Calendar' ), $widget_ops );
    }

    /**
     * Outputs the content for the current Calendar widget instance.
     *
     * @since 2.8.0
     * @access public
     *
     * @param array $args     Display arguments including 'before_title', 'after_title',
     *                        'before_widget', and 'after_widget'.
     * @param array $instance The settings for the particular instance of the widget.
     */
    public function widget( $args, $instance ) {
        /** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

        echo $args['before_widget'];
        if ( $title ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        if ( 0 === self::$instance ) {
            echo '<div id="events_calendar_wrap" class="events_calendar_wrap">';
        } else {
            echo '<div class="events_calendar_wrap">';
        }
        msdlab_get_events_calendar();
        echo '</div>';
        echo $args['after_widget'];

        self::$instance++;
    }

    /**
     * Handles updating settings for the current Calendar widget instance.
     *
     * @since 2.8.0
     * @access public
     *
     * @param array $new_instance New settings for this instance as input by the user via
     *                            WP_Widget::form().
     * @param array $old_instance Old settings for this instance.
     * @return array Updated settings to save.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field( $new_instance['title'] );

        return $instance;
    }

    /**
     * Outputs the settings form for the Calendar widget.
     *
     * @since 2.8.0
     * @access public
     *
     * @param array $instance Current settings.
     */
    public function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
        $title = sanitize_text_field( $instance['title'] );
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
        <?php
    }
}


/**
 * Display calendar with days that have posts as links.
 *
 * The calendar is cached, which will be retrieved, if it exists. If there are
 * no posts for the month, then it will not be displayed.
 *
 * @since 1.0.0
 *
 * @global wpdb      $wpdb
 * @global int       $m
 * @global int       $monthnum
 * @global int       $year
 * @global WP_Locale $wp_locale
 * @global array     $posts
 *
 * @param bool $initial Optional, default is true. Use initial calendar names.
 * @param bool $echo    Optional, default is true. Set to false for return.
 * @return string|void String when retrieving.
 */
function msdlab_get_events_calendar( $initial = true, $echo = true ) {
    global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;

    $key = md5( $m . $monthnum . $year );
    $cache = wp_cache_get( 'get_events_calendar', 'events_calendar' );

    if ( $cache && is_array( $cache ) && isset( $cache[ $key ] ) ) {
        /** This filter is documented in wp-includes/general-template.php */
        $output = apply_filters( 'get_events_calendar', $cache[ $key ] );

        if ( $echo ) {
            echo $output;
            return;
        }

        return $output;
    }

    if ( ! is_array( $cache ) ) {
        $cache = array();
    }
/*
    // Quick check. If we have no posts at all, abort!
    if ( ! $posts ) {
        $gotsome = $wpdb->get_var("SELECT 1 as test FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish' LIMIT 1");
        if ( ! $gotsome ) {
            $cache[ $key ] = '';
            wp_cache_set( 'get_events_calendar', $cache, 'events_calendar' );
            return;
        }
    }
*/
    if ( isset( $_GET['w'] ) ) {
        $w = (int) $_GET['w'];
    }
    // week_begins = 0 stands for Sunday
    $week_begins = (int) get_option( 'start_of_week' );
    $ts = current_time( 'timestamp' );

    // Let's figure out when we are
    if ( ! empty( $monthnum ) && ! empty( $year ) ) {
        $thismonth = zeroise( intval( $monthnum ), 2 );
        $thisyear = (int) $year;
    } elseif ( ! empty( $w ) ) {
        // We need to get the month from MySQL
        $thisyear = (int) substr( $m, 0, 4 );
        //it seems MySQL's weeks disagree with PHP's
        $d = ( ( $w - 1 ) * 7 ) + 6;
        $thismonth = $wpdb->get_var("SELECT DATE_FORMAT((DATE_ADD('{$thisyear}0101', INTERVAL $d DAY) ), '%m')");
    } elseif ( ! empty( $m ) ) {
        $thisyear = (int) substr( $m, 0, 4 );
        if ( strlen( $m ) < 6 ) {
            $thismonth = '01';
        } else {
            $thismonth = zeroise( (int) substr( $m, 4, 2 ), 2 );
        }
    } else {
        $thisyear = gmdate( 'Y', $ts );
        $thismonth = gmdate( 'm', $ts );
    }

    $unixmonth = mktime( 0, 0 , 0, $thismonth, 1, $thisyear );
    $last_day = date( 't', $unixmonth );

    // Get the next and previous month and year with at least one post
    $previous_month = mktime(0, 0, 0, gmdate("m",$ts)-1, gmdate("d",$ts),   gmdate("Y",$td));
    $next_month = mktime(0, 0, 0, gmdate("m",$ts)+1, gmdate("d",$ts),   gmdate("Y",$td));
    $previous = array('year'=>gmdate('Y', $previous_month),'month'=>gmdate('m',$previous_month));
    $next = array('year'=>gmdate('Y', $next_month),'month'=>gmdate('m',$next_month));

    /* translators: Calendar caption: 1: month name, 2: 4-digit year */
    $calendar_caption = _x('%1$s %2$s', 'calendar caption');
    $calendar_output = '<table id="wp-events-calendar">
	<caption>' . sprintf(
            $calendar_caption,
            $wp_locale->get_month( $thismonth ),
            date( 'Y', $unixmonth )
        ) . '</caption>
	<thead>
	<tr>';

    $myweek = array();

    for ( $wdcount = 0; $wdcount <= 6; $wdcount++ ) {
        $myweek[] = $wp_locale->get_weekday( ( $wdcount + $week_begins ) % 7 );
    }

    foreach ( $myweek as $wd ) {
        $day_name = $initial ? $wp_locale->get_weekday_initial( $wd ) : $wp_locale->get_weekday_abbrev( $wd );
        $wd = esc_attr( $wd );
        $calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
    }

    $calendar_output .= '
	</tr>
	</thead>

	<tfoot>
	<tr>';

        $calendar_output .= "\n\t\t".'<td colspan="3" id="prev"><a href="' . tribe_get_previous_month_link() . '">&laquo; ' .
            //$wp_locale->get_month_abbrev( $wp_locale->get_month( $previous['month'] ) ) .
            'prev' .
            '</a></td>';


    $calendar_output .= "\n\t\t".'<td class="pad">&nbsp;</td>';


        $calendar_output .= "\n\t\t".'<td colspan="3" id="next"><a href="' . tribe_get_next_month_link() . '">' .
            //$wp_locale->get_month_abbrev( $wp_locale->get_month( $next['month'] ) ) .
            'next' .
            ' &raquo;</a></td>';


    $calendar_output .= '
	</tr>
	</tfoot>

	<tbody>
	<tr>';

    $daywithpost = array();
    $dayswithposts = array();

    $monthevents = tribe_get_events(array('start_date'=>$thisyear.'-'.$thismonth.'-01 00:00:00','end_date'=>$thisyear.'-'.$thismonth.'-'.$last_day.' 23:59:59','hide_upcoming'=>false,'posts_per_page'=>-1));

    $monthpublicevents = tribe_get_events(array('start_date'=>$thisyear.'-'.$thismonth.'-01 00:00:00','end_date'=>$thisyear.'-'.$thismonth.'-'.$last_day.' 23:59:59','hide_upcoming'=>false,'posts_per_page'=>-1,'tribe_events_cat'=>'public-events'));

    $public_ids = array();
    foreach ($monthpublicevents AS $publicevent){
        $public_ids[] = $publicevent->ID;
    }
    //create dayswithposts from monthevents array.
    foreach($monthevents AS $event){
        if(date('d',strtotime($event->EventStartDate)) == date('d',strtotime($event->EventEndDate))){
            $dayswithposts[date('j',strtotime($event->EventStartDate))][] = $event->ID;
        } else {
            for ($i=date('d',strtotime($event->EventStartDate)); $i<=date('j',strtotime($event->EventEndDate)); $i++){
                $dayswithposts[$i][] = $event->ID;
            }
        }
    }

    // See how much we should pad in the beginning
    $pad = calendar_week_mod( date( 'w', $unixmonth ) - $week_begins );
    if ( 0 != $pad ) {
        $calendar_output .= "\n\t\t".'<td colspan="'. esc_attr( $pad ) .'" class="pad">&nbsp;</td>';
    }

    $newrow = false;
    $daysinmonth = (int) date( 't', $unixmonth );

    for ( $day = 1; $day <= $daysinmonth; ++$day ) {
        if ( isset($newrow) && $newrow ) {
            $calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
        }
        $newrow = false;

        if ( $day == gmdate( 'j', $ts ) &&
            $thismonth == gmdate( 'm', $ts ) &&
            $thisyear == gmdate( 'Y', $ts ) ) {
            $calendar_output .= '<td id="today">';
        } else {
            $calendar_output .= '<td>';
        }

        if ( isset($dayswithposts[$day]) ) {
            // any posts today?
            $class = 'resident-events';
            foreach($dayswithposts[$day] AS $event_id){
                if(in_array($event_id,$public_ids)){
                    $class = 'public-events';
                    continue;
                }
            }
            $date_format = date( _x( 'F j, Y', 'daily archives date format' ), strtotime( "{$thisyear}-{$thismonth}-{$day}" ) );
            /* translators: Post calendar label. 1: Date */
            $label = sprintf( __( 'Events on %s' ), $date_format );
            $calendar_output .= sprintf(
                '<a href="%s" aria-label="%s" class="%s"">%s</a>',
                tribe_get_day_link( $thisyear.'-'.$thismonth.'-'.$day),
                esc_attr( $label ),
                $class,
                $day
            );
        } else {
            $calendar_output .= $day;
        }
        $calendar_output .= '</td>';

        if ( 6 == calendar_week_mod( date( 'w', mktime(0, 0 , 0, $thismonth, $day, $thisyear ) ) - $week_begins ) ) {
            $newrow = true;
        }
    }

    $pad = 7 - calendar_week_mod( date( 'w', mktime( 0, 0 , 0, $thismonth, $day, $thisyear ) ) - $week_begins );
    if ( $pad != 0 && $pad != 7 ) {
        $calendar_output .= "\n\t\t".'<td class="pad" colspan="'. esc_attr( $pad ) .'">&nbsp;</td>';
    }
    $calendar_output .= "\n\t</tr>\n\t</tbody>\n\t</table>";

    $cache[ $key ] = $calendar_output;
    wp_cache_set( 'get_events_calendar', $cache, 'events_calendar' );

    if ( $echo ) {
        /**
         * Filters the HTML calendar output.
         *
         * @since 3.0.0
         *
         * @param string $calendar_output HTML output of the calendar.
         */
        echo apply_filters( 'get_events_calendar', $calendar_output );
        return;
    }
    /** This filter is documented in wp-includes/general-template.php */
    return apply_filters( 'get_events_calendar', $calendar_output );
}

/**
 * Purge the cached results of get_calendar.
 *
 * @see get_calendar
 * @since 2.1.0
 */
function msdlab_delete_get_events_calendar_cache() {
    wp_cache_delete( 'get_events_calendar', 'events_calendar' );
}
