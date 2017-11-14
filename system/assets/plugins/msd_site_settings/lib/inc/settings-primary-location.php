          <h2>Primary Location Information</h2>
          <p>Please input information for the primary physical location. If you have a separate address for mailing, please use the Mailing tab to supply a mailing address.</p>
          <ul class="input-table">
              <li>
                  <label for="biz_name">Business Name</label>
                  <div class="input">
                    <input name="biz_name" type="text" id="biz_name" value="<?php echo stripcslashes(get_option('msdsocial_biz_name')); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="street">Street Address</label>
                  <div class="input">
                <input name="street" type="text" id="street" value="<?php echo stripcslashes(get_option('msdsocial_street')); ?>" class="regular-text" /><br />
                <input name="street2" type="text" id="street2" value="<?php echo stripcslashes(get_option('msdsocial_street2')); ?>" class="regular-text" />
                </div>
              </li>
              <li>
                  <label for="city">City</label>
                  <div class="input">
                <input name="city" type="text" id="city" value="<?php echo stripcslashes(get_option('msdsocial_city')); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="state">State</label>
                  <div class="input">
                <select name="state" id="state" class="regular-text" />
                    <option value=''>Select</option>
                    <?php foreach($states AS $state => $st){ ?>
                        <option value="<?php print $st; ?>"<?php print get_option('msdsocial_state')==$st?' SELECTED':'';?>><?php print ucwords(strtolower($state)); ?></option>
                    <?php } ?>
                </select>
                </div>
              </li>
              <li>
                  <label for="zip">ZIP Code</label>
                  <div class="input">
                <input name="zip" type="text" id="zip" value="<?php echo stripcslashes(get_option('msdsocial_zip')); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="lat">Latitude</label>
                  <div class="input">
                <input name="lat" type="text" id="lat" value="<?php echo stripcslashes(get_option('msdsocial_lat')); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="lng">Longitude</label>
                  <div class="input">
                <input name="lng" type="text" id="lng" value="<?php echo stripcslashes(get_option('msdsocial_lng')); ?>" class="regular-text" />
                  </div>
              </li>
          </ul>
           <h2>Contact Information</h2>
          <p></p>
          <ul class="input-table">
              <li>
                  <label for="phone">Phone</label>
                  <div class="input">
                    <input name="phone" type="text" id="phone" value="<?php echo stripcslashes(get_option('msdsocial_phone')); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="tracking_phone">Tracking Phone</label>
                  <div class="input">
                    <input name="tracking_phone" type="text" id="tracking_phone" value="<?php echo stripcslashes(get_option('msdsocial_tracking_phone')); ?>" class="regular-text" />
                  <span class="note">If you fill this in, this is what will display in the browser, and the "real" phone number will be available only in the code.</span>
                  </div>
              </li>
              <li>
                  <label for="tollfree">Toll Free</label>
                  <div class="input">
                    <input name="tollfree" type="text" id="tollfree" value="<?php echo stripcslashes(get_option('msdsocial_tollfree')); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="tracking_tollfree">Tracking Toll Free</label>
                  <div class="input">
                    <input name="tracking_tollfree" type="text" id="tracking_tollfree" value="<?php echo stripcslashes(get_option('msdsocial_tracking_tollfree')); ?>" class="regular-text" /> 
                  <span class="note">If you fill this in, this is what will display in the browser, and the "real" phone number will be available only in the code.</span>
                  </div>
              </li>
              <li>
                  <label for="fax">Fax</label>
                  <div class="input">
                    <input name="fax" type="text" id="fax" value="<?php echo stripcslashes(get_option('msdsocial_fax')); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="email">Email</label>
                  <div class="input">
                    <input name="email" type="text" id="email" value="<?php echo stripcslashes(get_option('msdsocial_email')); ?>" class="regular-text" />
                  <span class="note">Primary contact email. This may be displayed on the website.</span>
                  </div>
              </li>
          </ul>