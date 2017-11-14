          <h2>Mailing Information</h2>
          <p>If you have a separate address for mailing, such as a PO Box, please enter that here.</p>
          <ul class="input-table">
              
              <li>
                  <label for="careof">Care Of</label>
                  <div class="input">
                    <input name="careof" type="text" id="careof" value="<?php echo stripcslashes(get_option('msdsocial_careof')); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="mailing_street">Street Address</label>
                  <div class="input">
                <input name="mailing_street" type="text" id="mailing_street" value="<?php echo stripcslashes(get_option('msdsocial_mailing_street')); ?>" class="regular-text" /><br />
                <input name="mailing_street2" type="text" id="mailing_street2" value="<?php echo stripcslashes(get_option('msdsocial_mailing_street2')); ?>" class="regular-text" />
                </div>
              </li>
              <li>
                  <label for="mailing_city">City</label>
                  <div class="input">
                <input name="mailing_city" type="text" id="mailing_city" value="<?php echo stripcslashes(get_option('msdsocial_mailing_city')); ?>" class="regular-text" />
                  </div>
              </li>
              <li>
                  <label for="mailing_state">State</label>
                  <div class="input">
                <select name="mailing_state" id="mailing_state" class="regular-text" />
                    <option value=''>Select</option>
                    <?php foreach($states AS $state => $st){ ?>
                        <option value="<?php print $st; ?>"<?php print get_option('msdsocial_mailing_state')==$st?' SELECTED':'';?>><?php print ucwords(strtolower($state)); ?></option>
                    <?php } ?>
                </select>
                </div>
              </li>
              <li>
                  <label for="mailing_zip">ZIP Code</label>
                  <div class="input">
                <input name="mailing_zip" type="text" id="mailing_zip" value="<?php echo stripcslashes(get_option('msdsocial_mailing_zip')); ?>" class="regular-text" />
                  </div>
              </li>
          </ul>