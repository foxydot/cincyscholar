
          <h2>Hours</h2>
          <p>Set open and close time the same to be "closed" for the day.</p>
        <ul class="input-table">
              <li>
                  <label></label>
                  <div class="input">
                    <div class="col-sm-1"><strong>Closed</strong></div>
                    <div class="col-sm-8">
                        <div class="col-sm-6"><strong>Open</strong></div>
                        <div class="col-sm-6"><strong>Close</strong></div>
                    </div>
                    <div class="col-sm-1"><strong>Copy</strong></div>
                  </div>
              </li>
            <?php 
            $days = array(
                'Sunday',
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
            );
            foreach ($days as $day) {
                $open = get_option('msdsocial_hours_'.strtolower($day).'_open');
                $close = get_option('msdsocial_hours_'.strtolower($day).'_close');
                $checked = $open==''||$close==''?' checked':'';
                $hide = $open==''||$close==''?' hidden':'';
                ?>
              <li>
                  <label for="hours_<?php print strtolower($day); ?>"><?php print $day; ?></label>
                  <div class="input">
                      <div class="col-sm-1">
                          <input type="checkbox" class="closed-check" <?php print $checked; ?> />
                      </div>
                      <div class="col-sm-8">
                          <div class="col-sm-5 input-append bootstrap-timepicker">
                            <input name="hours_<?php print strtolower($day); ?>_open" type="text" id="hours_<?php print strtolower($day); ?>_open" value="<?php echo get_option('msdsocial_hours_'.strtolower($day).'_open'); ?>" class="regular-text start time<?php print $hide; ?>" />
                          </div>
                          <div class="col-sm-5  input-append bootstrap-timepicker">
                            <input name="hours_<?php print strtolower($day); ?>_close" type="text" id="hours_<?php print strtolower($day); ?>_close" value="<?php echo get_option('msdsocial_hours_'.strtolower($day).'_close'); ?>" class="regular-text end time<?php print $hide; ?>" />
                          </div>
                      </div>
                      <div class="col-sm-1">
                          <?php if($day != 'Sunday'): ?>
                          <a class="btn btn-default copy">Copy Previous Day</a>
                          <?php endif; ?>
                      </div>
                  </div>
              </li>
              <?php
            }
            ?>
        </ul>