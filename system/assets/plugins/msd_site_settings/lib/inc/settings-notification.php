<?php
    $notification_inputs = array();
    $notification_inputs['content'] = '<li>
                  <label for="notification_content">Content</label>
                  <div class="input">
                    <textarea name="notification_content" id="notification_content" class="regular-text" rows="5">' . stripcslashes(get_option('msdsocial_notification_content')) . '</textarea>
                  </div>
              </li>';
    $position_check = array(
        0 => '',
        10 => '',
        20 => '',
        30 => '',
        40 => '',
        50 => '',
    );
    $position_check[get_option('msdsocial_notification_position')] = 'CHECKED';
    $notification_inputs['position'] = '<li>
                  <label for="notification_position">Position (note: these really only work with Genesis themes right now)</label>
                  <div class="input">
                    <input name="notification_position" type="radio" id="notification_position" value="0" '. $position_check[0] . ' > Do Not Display &nbsp;|&nbsp;
                    <input name="notification_position" type="radio" id="notification_position" value="10" '. $position_check[10] . ' > Above Header &nbsp;|&nbsp;
                    <input name="notification_position" type="radio" id="notification_position" value="20" '. $position_check[20] . ' > Below Header &nbsp;|&nbsp;
                    <input name="notification_position" type="radio" id="notification_position" value="30" '. $position_check[30] . ' > Above Footer &nbsp;|&nbsp;
                    <input name="notification_position" type="radio" id="notification_position" value="40" '. $position_check[40] . ' > Below Footer &nbsp;|&nbsp;
                    <input name="notification_position" type="radio" id="notification_position" value="50" '. $position_check[50] . ' > Custom
                  </div>
              </li>';
    $notification_inputs['start_datetime'] = '<li>
                  <label for="notification_start_datetime">Dates</label>
                  <div class="input">
                    <div class="col-md-6">
                    Display Start: 
                    <input name="notification_start_datetime" type="text" id="notification_start_datetime" value="' . get_option('msdsocial_notification_start_datetime') . '" class="regular-text date-picker" />
                    </div>
                    <div class="col-md-6">
                    Display End: 
                    <input name="notification_end_datetime" type="text" id="notification_end_datetime" value="' . get_option('msdsocial_notification_end_datetime') . '" class="regular-text date-picker" />
                    </div>
                  </div>
              </li>';
    
    $notification_inputs = apply_filters('msdlab_notification_inputs', $notification_inputs);
    $form_inputs = implode("\n", $notification_inputs);
?>
          <h2><?php print apply_filters('msdlab_social_notification_panel_title','Notification Bar'); ?></h2>
          <p></p>
          <ul class="input-table">
              <?php print $form_inputs; ?>
          </ul>