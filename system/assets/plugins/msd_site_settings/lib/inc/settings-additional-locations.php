<?php
    for($n=0;$n<=10;$n++){
        $selected = get_option('msdsocial_num_locations')==$n?' selected=selected':'';
        $numbers_select .= '
        <option value="'.$n.'"'.$selected.'>'.$n.'</option>';
    }
?>
          <h2><?php print apply_filters('msdlab_social_additional_panel_title','Additional Locations'); ?></h2>
          <p>Show <select name="num_locations" id="num_locations"><?php print $numbers_select; ?></select> Additional Location(s)</p>
          <?php 
            $additional_locations = get_option(msdsocial_adtl_locations);
          ?>
          <?php
            for($n=1;$n<10;$n++){
                $eo = $n%2==0?' even':' odd';
          ?>
          <ul class="input-table<?php print $eo; ?>" id="adtl_location_<?php print $n ?>">
              <li>
                  <label for="location_name">Location Name</label>
                  <div class="input">
                    <input name="adtl_locations[<?php print $n ?>][location_name]" type="text" id="location_name_<?php print $n ?>" value="<?php echo stripslashes($additional_locations[$n][location_name]); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="street">Street Address</label>
                  <div class="input">
                <input id="street_<?php print $n ?>" type="text" name="adtl_locations[<?php print $n ?>][street]" value="<?php echo stripslashes($additional_locations[$n][street]); ?>" class="regular-text" /><br />
                <input id="street2_<?php print $n ?>" type="text" name="adtl_locations[<?php print $n ?>][street2]" value="<?php echo stripslashes($additional_locations[$n][street2]); ?>" class="regular-text" />
                </div>
              </li>
              <li>
                  <label for="city">City</label>
                  <div class="input">
                <input name="adtl_locations[<?php print $n ?>][city]" type="text" id="city_<?php print $n ?>" value="<?php echo stripslashes($additional_locations[$n][city]); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="state">State</label>
                  <div class="input">
                <select name="adtl_locations[<?php print $n ?>][state]" id="state_<?php print $n ?>" class="regular-text" />
                    <option value=''>Select</option>
                    <?php foreach($states AS $state => $st){ ?>
                        <option value="<?php print $st; ?>"<?php print $additional_locations[$n][state]==$st?' SELECTED':'';?>><?php print ucwords(strtolower($state)); ?></option>
                    <?php } ?>
                </select>
                </div>
              </li>
              <li>
                  <label for="zip">ZIP Code</label>
                  <div class="input">
                <input name="adtl_locations[<?php print $n ?>][zip]" type="text" id="zip_<?php print $n ?>" value="<?php echo stripslashes($additional_locations[$n][zip]); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="phone">Phone</label>
                  <div class="input">
                    <input name="adtl_locations[<?php print $n ?>][phone]" type="text" id="phone_<?php print $n ?>" value="<?php echo stripslashes($additional_locations[$n][phone]); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="tracking_phone">Tracking Phone</label>
                  <div class="input">
                    <input name="adtl_locations[<?php print $n ?>][tracking_phone]" type="text" id="tracking_phone_<?php print $n ?>" value="<?php echo stripslashes($additional_locations[$n][tracking_phone]); ?>" class="regular-text" />
                  <span class="note">If you fill this in, this is what will display in the browser, and the "real" phone number will be available only in the code.</span>
                  </div>
              </li>
              <li>
                  <label for="fax">Fax</label>
                  <div class="input">
                    <input name="adtl_locations[<?php print $n ?>][fax]" type="text" id="fax_<?php print $n ?>" value="<?php echo stripslashes($additional_locations[$n][fax]); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="email">Email</label>
                  <div class="input">
                    <input name="adtl_locations[<?php print $n ?>][email]" type="text" id="email_<?php print $n ?>" value="<?php echo stripslashes($additional_locations[$n][email]); ?>" class="regular-text" />
                  <span class="note">Primary contact email. This may be displayed on the website.</span>
                  </div>
              </li>
              <li><label></label><div class="input"><strong>Mapping</strong></div></li>
              <li>
                  <label for="lat">Latitude</label>
                  <div class="input">
                <input name="adtl_locations[<?php print $n ?>][lat]" type="text" id="lat_<?php print $n ?>" value="<?php echo stripslashes($additional_locations[$n][lat]); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="lng">Longitude</label>
                  <div class="input">
                <input name="adtl_locations[<?php print $n ?>][lng]" type="text" id="lng_<?php print $n ?>" value="<?php echo stripslashes($additional_locations[$n][lng]); ?>" class="regular-text" />
                  </div>
              </li>
          </ul>
          <?php
          }
          ?>