   <div class="msdlab_meta_control">
 <p id="warning" style="display: none;background:lightYellow;border:1px solid #E6DB55;padding:5px;">Order has changed. Please click Save or Update to preserve order.</p>
    <div class="table">
    <?php $i = 1; ?>
    <?php while($mb->have_fields_and_multi('sections')): ?>
    <?php $mb->the_group_open(); ?>
    <div class="row <?php print $i%2==1?'even':'odd'; ?>">
        <div class="cell"><h3>Section <?php print $i ?> </h3></div>
        <div class="cell">
        <?php $mb->the_field('content-area-title'); ?>
        <label>Section <?php print $i ?> Content Area Title</label>            
        <div class="input_container">
            <input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/></div>
        </div>
        <div class="cell">
            <?php $mb->the_field('content-area-subtitle'); ?>
            <label>Section <?php print $i ?> Subtitle</label>            
            <div class="input_container">
                <input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>"/>
            </div>
        </div>
        <div class="cell">
            <label>Section <?php print $i ?> Content Area Content</label>
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
        <div class="cell file">
            <label>Section <?php print $i ?> Feature Image</label>
            <div class="input_container">
        <?php $mb->the_field('content-area-image'); ?>
        <?php if($mb->get_the_value() != ''){
            print '<img src="'.$mb->get_the_value().'">';
        } ?>
        <?php $group_name = 'feature-img-'. $mb->get_the_index(); ?>
        <?php $wpalchemy_media_access->setGroupName($group_name)->setInsertButtonLabel('Insert This')->setTab('gallery'); ?>
        <?php echo $wpalchemy_media_access->getField(array('name' => $mb->get_the_name(), 'value' => $mb->get_the_value())); ?>
        <?php echo $wpalchemy_media_access->getButton(array('label' => 'Add Image')); ?>
            </div>
        </div>
        <div class="cell">
            <a href="#" class="dodelete button alignright">Remove Section <?php print $i ?></a>
        </div>
    </div>
    <?php $i++; ?>
    <?php $mb->the_group_close(); ?>
    <?php endwhile; ?>
    </div>
    <p style="margin-bottom:15px; padding-top:5px;"><a href="#" class="docopy-sections button">Add Section</a>
</div>