<?php
/*******************************
  THEME OPTIONS PAGE
********************************/

add_action('admin_menu', 'msdsocial_theme_page');
function msdsocial_theme_page ()
{
	if ( count($_POST) > 0 && isset($_POST['msdsocial_settings']) )
	{
		$options = array (
		'biz_name',
		'street',
		'street2',
		'city',
		'state',
		'zip',
		'lat',
		'lng',
		'num_locations',
		'adtl_locations',
        'phone',
        'tracking_phone',
        'tollfree',
        'tracking_tollfree',
		'fax',
		'email',
		'careof',
		'mailing_street',
		'mailing_street2',
		'mailing_city',
		'mailing_state',
		'mailing_zip',
		'facebook_link',
		'twitter_user',
		'pinterest_link',
		'google_link',
		'linkedin_link',
        'instagram_link',
        'tumblr_link',
        'reddit_link',
        'flickr_link',
        'youtube_link',
        'vimeo_link',
        'vine_link',
		'sharethis_link',
		'contact_link',
        'show_blog',
        'show_feed',
		'hours_sunday_open',
        'hours_sunday_close',
        'hours_monday_open',
        'hours_monday_close',
        'hours_tuesday_open',
        'hours_tuesday_close',
        'hours_wednesday_open',
        'hours_wednesday_close',
        'hours_thursday_open',
        'hours_thursday_close',
        'hours_friday_open',
        'hours_friday_close',
        'hours_saturday_open',
        'hours_saturday_close',
        'notification_content',
        'notification_position',
        'notification_start_datetime',
        'notification_end_datetime'
        );
        
        $options = apply_filters('msdlab_social_options',$options);
		
		foreach ( $options as $opt )
		{
			delete_option ( 'msdsocial_'.$opt, $_POST[$opt] );
			add_option ( 'msdsocial_'.$opt, $_POST[$opt] );	
		}			
		 
	}
    //give admin access to the settings
    $role = get_role('administrator');
    $role->add_cap('admin_msd_settings');
	add_submenu_page('options-general.php',__('Settings'), __('MSD Site Settings'), 'admin_msd_settings', 'msdsocial-options', 'msdsocial_settings');
}
function msdsocial_settings()
{
$states = array('ALABAMA'=>"AL",
'ALASKA'=>"AK",
'AMERICAN SAMOA'=>"AS",
'ARIZONA'=>"AZ",
'ARKANSAS'=>"AR",
'CALIFORNIA'=>"CA",
'COLORADO'=>"CO",
'CONNECTICUT'=>"CT",
'DELAWARE'=>"DE",
'DISTRICT OF COLUMBIA'=>"DC",
"FEDERATED STATES OF MICRONESIA"=>"FM",
'FLORIDA'=>"FL",
'GEORGIA'=>"GA",
'GUAM' => "GU",
'HAWAII'=>"HI",
'IDAHO'=>"ID",
'ILLINOIS'=>"IL",
'INDIANA'=>"IN",
'IOWA'=>"IA",
'KANSAS'=>"KS",
'KENTUCKY'=>"KY",
'LOUISIANA'=>"LA",
'MAINE'=>"ME",
'MARSHALL ISLANDS'=>"MH",
'MARYLAND'=>"MD",
'MASSACHUSETTS'=>"MA",
'MICHIGAN'=>"MI",
'MINNESOTA'=>"MN",
'MISSISSIPPI'=>"MS",
'MISSOURI'=>"MO",
'MONTANA'=>"MT",
'NEBRASKA'=>"NE",
'NEVADA'=>"NV",
'NEW HAMPSHIRE'=>"NH",
'NEW JERSEY'=>"NJ",
'NEW MEXICO'=>"NM",
'NEW YORK'=>"NY",
'NORTH CAROLINA'=>"NC",
'NORTH DAKOTA'=>"ND",
"NORTHERN MARIANA ISLANDS"=>"MP",
'OHIO'=>"OH",
'OKLAHOMA'=>"OK",
'OREGON'=>"OR",
"PALAU"=>"PW",
'PENNSYLVANIA'=>"PA",
'RHODE ISLAND'=>"RI",
'SOUTH CAROLINA'=>"SC",
'SOUTH DAKOTA'=>"SD",
'TENNESSEE'=>"TN",
'TEXAS'=>"TX",
'UTAH'=>"UT",
'VERMONT'=>"VT",
'VIRGIN ISLANDS' => "VI",
'VIRGINIA'=>"VA",
'WASHINGTON'=>"WA",
'WEST VIRGINIA'=>"WV",
'WISCONSIN'=>"WI",
'WYOMING'=>"WY");
	?>
<style>
    span.note{
        display: block;
        font-size: 0.9em;
        font-style: italic;
        color: #999999;
    }
    body{
        background-color: transparent;
    }
    .input-table.even{background-color: rgba(0,0,0,0.1);padding: 2rem 0;}
    .input-table .description{display:none}
    .input-table li:after{content:".";display:block;clear:both;visibility:hidden;line-height:0;height:0}
    .input-table label{display:block;font-weight:bold;margin-right:1%;float:left;width:14%;text-align:right}
    .input-table label span{display:inline;font-weight:normal}
    .input-table span{color:#999;display:block}
    .input-table .input{width:85%;float:left}
    .input-table .input .half{width:48%;float:left}
    .input-table textarea,.input-table input[type='text'],.input-table select{display:inline;margin-bottom:3px;width:90%}
    .input-table .mceIframeContainer{background:#fff}
    .input-table h4{color:#999;font-size:1em;margin:15px 6px;text-transform:uppercase}
</style>
<div class="wrap">
	<h2>MSDLAB Site Settings</h2>
	<p>This panel provides an interface for site settings used by custom MSDLAB themes and plugins.</p>
	
<form method="post" action="">
    <!-- Nav tabs -->
    <ul class="nav nav-tabs">
      <li class="active"><a href="#social-media" data-toggle="tab"><?php print apply_filters('msdlab_social_media_panel_title','Social Media'); ?></a></li>
      <li><a href="#primary" data-toggle="tab"><?php print apply_filters('msdlab_social_address_panel_title','Primary Location'); ?></a></li>
      <li><a href="#locations" data-toggle="tab"><?php print apply_filters('msdlab_social_additional_panel_title','Additional Locations'); ?></a></li>
      <li><a href="#mailing" data-toggle="tab"><?php print apply_filters('msdlab_social_mailing_panel_title','Mailing'); ?></a></li>
      <li><a href="#hours" data-toggle="tab"><?php print apply_filters('msdlab_social_hours_panel_title','Business Hours'); ?></a></li>
      <li><a href="#notification" data-toggle="tab"><?php print apply_filters('msdlab_social_notification_panel_title','Notification Bar'); ?></a></li>
    </ul>
    
    <!-- Tab panes -->
    <div class="tab-content">
      <div class="tab-pane active" id="social-media">
           <?php include_once('settings-social-media.php'); ?>
      </div>
      <div class="tab-pane" id="primary">
           <?php include_once('settings-primary-location.php'); ?>
      </div>
      <div class="tab-pane" id="locations">
           <?php include_once('settings-additional-locations.php'); ?>
      </div>
      <div class="tab-pane" id="mailing">
           <?php include_once('settings-mailing.php'); ?>
      </div>
      <div class="tab-pane" id="hours">
           <?php include_once('settings-hours.php'); ?>
      </div>
      <div class="tab-pane" id="notification">
           <?php include_once('settings-notification.php'); ?>
      </div>
    </div>
		<p class="submit">
		<input type="submit" name="Submit" class="button-primary" value="Save Changes" />
		<input type="hidden" name="msdsocial_settings" value="save" style="display:none;" />
		</p>
</form>
</div>
 <script type="text/javascript">
 function clearfields(target){
     target.find('input,textarea,select').val('');
 }
jQuery('.time').timepicker();
jQuery(document).ready(function($) {  
    $('.closed-check').click(function(){
        var sibs = $(this).parent().siblings().find('.time');
        if($(this).is(':checked')){
            sibs.val('');
            sibs.addClass('hidden');
        } else {
            sibs.removeClass('hidden');
        }
    });
    $('.copy').click(function(){
        var cur = $(this).parent().parent();
        var prev = $(this).parent().parent().parent().prev('li').find('.input');
        cur.find('.time.start').val(prev.find('.time.start').val());
        cur.find('.time.end').val(prev.find('.time.end').val());
    });
    for(i=0;i<=10;i++){
        if(i<=Number($('#num_locations').val())){
            $('#adtl_location_' + i).show();
        } else {
            $('#adtl_location_' + i).hide();
            clearfields($('#adtl_location_' + i));
        }
    }
    $('#num_locations').change(function(){
        for(i=0;i<=10;i++){
            if(i<=Number($(this).val())){
                $('#adtl_location_' + i).show();
            } else {
                $('#adtl_location_' + i).hide();
                clearfields($('#adtl_location_' + i));
            }
        }
    });
});
</script>
<?php }