<div class="msdlab_meta_control" id="msdlab_meta_control">
    <p id="warning" style="display: none;background:lightYellow;border:1px solid #E6DB55;padding:5px;">Order has changed. Please click Save or Update to preserve order.</p>
    <div class="table">
    <?php $s = 1; ?>
    <?php while($mb->have_fields_and_multi('sections')): ?>
    <?php $mb->the_group_open(); ?>
    <?php $section_name = strlen($mb->get_the_value('section-name'))==0?'Section '.$s:$mb->get_the_value('section-name')?>
    <div id="<?php print $section_name; ?>" class="msdlab_section row <?php print $s%2==1?'even':'odd'; ?>">
        <h2 class="section_handle <?php print $s%2==1?'even':'odd'; ?>"><span><?php print $section_name; ?></span></h2>
        <div class="section_data">
        <div class="section_params">
        <div class="cell">
            <label>Section Name*</label>            
            <div class="input_container">
                <input type="text" name="<?php $mb->the_name('section-name'); ?>" value="<?php $mb->the_value('section-name'); ?>"/><br />
                <i>Please use a section name. This is used to produce identifying wrappers for the content!</i>
            </div>
        </div>
        <div class="cell">  
            <label>Hide Section</label>
            <div class="input_container">
                <?php $mb->the_field('section-hidden-bool'); ?>
                <div class="ui-toggle-btn">
                    <input type="checkbox" name="<?php $mb->the_name(); ?>" value="1"<?php $mb->the_checkbox_state('1'); ?>/> 
                    <div class="handle" data-on="ON" data-off="OFF"></div>
                </div>
            </div>
        </div>
        <div class="cell">
            <?php $mb->the_field('layout'); ?>
            <label><?php print $section_name; ?> Layout</label>            
            <div class="input_container">
                <select name="<?php $mb->the_name(); ?>" class="layout">
                    <option value=""<?php $mb->the_select_state('default'); ?>>Default</option>
                    <option value="two-col"<?php $mb->the_select_state('two-col'); ?>>Two Columns</option>
                    <option value="three-col"<?php $mb->the_select_state('three-col'); ?>>Three Columns</option>
                    <option value="four-col"<?php $mb->the_select_state('four-col'); ?>>Four Columns</option>
                </select>
            </div>
        </div>
        <div class="cell file">
            <label>Background Image</label>
            <div class="input_container">
                <?php $mb->the_field('background-image'); ?>
                <?php if($mb->get_the_value() != ''){
                    $thumb_array = wp_get_attachment_image_src( get_attachment_id_from_src($mb->get_the_value()), 'thumbnail' );
                    $thumb = $thumb_array[0];
                } else {
                    $thumb = WP_PLUGIN_URL.'/msd-specialty-pages/lib/img/spacer.gif';
                } ?>
                <img class="background-preview-img" src="<?php print $thumb; ?>">
                <?php $group_name = 'background-img-'. $mb->get_the_index(); ?>
                <?php $wpalchemy_media_access->setGroupName($group_name)->setInsertButtonLabel('Insert This')->setTab('gallery'); ?>
                <?php echo $wpalchemy_media_access->getField(array('name' => $mb->get_the_name(), 'value' => $mb->get_the_value())); ?>
                <?php echo $wpalchemy_media_access->getButton(array('label' => '+')); ?>
        </div>
        </div>
        <div class="cell">  
            <label>Parallax Background</label>
            <div class="input_container">
                <?php $mb->the_field('background-image-parallax'); ?>
                <div class="ui-toggle-btn">
                    <input type="checkbox" name="<?php $mb->the_name(); ?>" value="1"<?php $mb->the_checkbox_state('1'); ?>/> 
                    <div class="handle" data-on="ON" data-off="OFF"></div>
                </div>
            </div>
        </div>
        <div class="cell">
            <label>Background Color</label>
            <div class="input_container">
                <?php $mb->the_field('background-color'); ?>
                <input class="colorpicker" type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/>
            </div>
        </div>       
        <div class="cell">
            <?php $mb->the_field('css-classes'); ?>
            <label>CSS Classes</label>            
            <div class="input_container">
                <input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/><br />
            </div>
        </div>
        </div>
        <div class="content-area box">
            <div class="cell">
                <?php $mb->the_field('content-area-title'); ?>
                <label>Title</label>            
                <div class="input_container">
                    <input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/>
                </div>
            </div>
            <div class="cell">
                <?php $mb->the_field('content-area-subtitle'); ?>
                <label>Subtitle</label>            
                <div class="input_container">
                    <input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/>
                </div>
            </div>
            <div class="cell">
                <label>Use Header Text</label>
                <div class="input_container">
                    <?php $mb->the_field('header-area-bool'); ?>
                    <div class="ui-toggle-btn">
                      <input type="checkbox" name="<?php $mb->the_name(); ?>" value="1"<?php $mb->the_checkbox_state('1');?> />
                      <div class="handle" data-on="ON" data-off="OFF"></div>
                    </div>
                    <div class="switchable">
                        <?php 
                        $mb->the_field('header-area-content');
                        $mb_content = html_entity_decode($mb->get_the_value(), ENT_QUOTES, 'UTF-8');
                        $mb_editor_id = sanitize_key($mb->get_the_name());
                        $mb_settings = array('textarea_name'=>$mb->get_the_name(),'textarea_rows' => '5',);
                        wp_editor( $mb_content, $mb_editor_id, $mb_settings );
                        ?>
                   </div>
               </div>
            </div>
            <div class="cell column-1">
                <label class="cols-2 cols-3 cols-4">Column 1 Width</label>
                <div class="input_container cols-2 cols-3 cols-4">
                    <?php 
                    $mb->the_field('content-area-width');
                    ?>
                    <input type="text" class="small" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" /> How many columns out of 12?
                </div>
                <label><span class="cols-2 cols-3 cols-4">Column 1 </span>Content</label>
                <div class="input_container">
                    <?php 
                    $mb->the_field('content-area-content');
                    $mb_content = html_entity_decode($mb->get_the_value(), ENT_QUOTES, 'UTF-8');
                    $mb_editor_id = sanitize_key($mb->get_the_name());
                    $mb_settings = array('textarea_name'=>$mb->get_the_name(),'textarea_rows' => '5',);
                    wp_editor( $mb_content, $mb_editor_id, $mb_settings );
                    ?>
               </div>
            </div>
            <?php for($i=2;$i<=4;$i++){ ?>
            <div class="cell column-<?php print $i; ?> <?php print get_hidden($i) ?>">
                <label>Column <?php print $i; ?> Width</label>
                <div class="input_container">
                    <?php 
                    $mb->the_field('column-'.$i.'-area-width');
                    ?>
                    <input type="text" class="small" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" /> How many columns out of 12?
                </div>
                <label>Column <?php print $i; ?> Content</label>
                <div class="input_container">
                    <?php 
                    $mb->the_field('column-'.$i.'-area-content');
                    $mb_content = html_entity_decode($mb->get_the_value(), ENT_QUOTES, 'UTF-8');
                    $mb_editor_id = sanitize_key($mb->get_the_name());
                    $mb_settings = array('textarea_name'=>$mb->get_the_name(),'textarea_rows' => '5',);
                    wp_editor( $mb_content, $mb_editor_id, $mb_settings );
                    ?>
               </div>
            </div>
            <?php } ?>
            <div class="cell">
                <label>Use Footer Text</label>
                <div class="input_container">
                    <?php $mb->the_field('footer-area-bool'); ?>
                    <div class="ui-toggle-btn">
                      <input type="checkbox" name="<?php $mb->the_name(); ?>" value="1"<?php $mb->the_checkbox_state('1');?> />
                      <div class="handle" data-on="ON" data-off="OFF"></div>
                    </div>
                    <div class="switchable">
                        <?php 
                        $mb->the_field('footer-area-content');
                        $mb_content = html_entity_decode($mb->get_the_value(), ENT_QUOTES, 'UTF-8');
                        $mb_editor_id = sanitize_key($mb->get_the_name());
                        $mb_settings = array('textarea_name'=>$mb->get_the_name(),'textarea_rows' => '5',);
                        wp_editor( $mb_content, $mb_editor_id, $mb_settings );
                        ?>
                   </div>
               </div>
            </div>
            <div class="cell file" style="display: none;">
                <label>Feature Image</label>
                <div class="input_container">
                <?php $mb->the_field('content-area-image'); ?>
                <?php if($mb->get_the_value() != ''){
                    $thumb_array = wp_get_attachment_image_src( get_attachment_id_from_src($mb->get_the_value()), 'thumbnail' );
                    $thumb = $thumb_array[0];
                } else {
                    $thumb = WP_PLUGIN_URL.'/msd-specialty-pages/lib/img/spacer.gif';
                } ?>
                <img class="content-area-preview-img" src="<?php print $thumb; ?>">
                <?php $group_name = 'content-area-feature-img-'. $mb->get_the_index(); ?>
                <?php $wpalchemy_media_access->setGroupName($group_name)->setInsertButtonLabel('Insert This')->setTab('gallery'); ?>
                <?php echo $wpalchemy_media_access->getField(array('name' => $mb->get_the_name(), 'value' => $mb->get_the_value())); ?>
                <?php echo $wpalchemy_media_access->getButton(array('label' => '+')); ?>
                
                <br />
                <?php $mb->the_field('feature-image-float'); ?>
                <strong>Feature image align:</strong> 
                <input type="radio" name="<?php $mb->the_name(); ?>" value="none"<?php $mb->the_radio_state('none'); ?>/> None 
                <input type="radio" name="<?php $mb->the_name(); ?>" value="left"<?php $mb->the_radio_state('left'); ?>/> Left 
                <input type="radio" name="<?php $mb->the_name(); ?>" value="center"<?php $mb->the_radio_state('center'); ?>/> Center 
                <input type="radio" name="<?php $mb->the_name(); ?>" value="right"<?php $mb->the_radio_state('right'); ?>/> Right 
                </div>
            </div>
        </div>
        <div class="cell footer">
            <a href="#" class="dodelete button alignright">Remove <?php print strlen($mb->get_the_value('section-name'))==0?'Section '.$s:$mb->the_value('section-name')?></a>
        </div>
        </div>
    </div>
    <?php $s++; ?>
    <?php $mb->the_group_close(); ?>
    <?php endwhile; ?>
    </div>
    <p style="margin-bottom:15px; padding-top:5px;"><a href="#" class="docopy-sections button">Add Section</a>
</div>

<?php
function get_hidden($i){
    $ret = '';
    switch($i){
        case 2:
            $ret .= 'cols-2 ';
        case 3:
            $ret .= 'cols-3 ';
        case 4:
            $ret .= 'cols-4 ';
            break;
    }
    return $ret;
}
