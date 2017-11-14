<?php global $wpalchemy_media_access; ?>
<table class="form-table sidebar-content-controls">
    <tbody>
    <?php $mb->the_field('sidebarbool'); ?>
    <tr valign="top">
        <th scope="row"><label for="sidebarbool"></label></th>
        <td>
            <p><input type="checkbox" id="sidebarbool" name="<?php $mb->the_name(); ?>" value="true"<?php $mb->the_checkbox_state('true'); ?>/> Use sidebar content?</p>
        </td>
    </tr>
    <?php $mb->the_field('sidebarcontent'); ?>
    <tr valign="top" class="switchable">
        <th scope="row"><label for="sidebarcontent">Sidebar Content</label></th>
        <td>
            <?php
            $mb_content = html_entity_decode($mb->get_the_value(), ENT_QUOTES, 'UTF-8');
            $mb_editor_id = sanitize_key($mb->get_the_name());
            $mb_settings = array('textarea_name'=>$mb->get_the_name(),'textarea_rows' => '5',);
            wp_editor( $mb_content, $mb_editor_id, $mb_settings );
            ?>
        </td>
    </tr>
    <?php $mb->the_field('sidebarclass'); ?>
    <tr valign="top" class="switchable">
        <th scope="row"><label for="sidebarclass">Any custom class names for sidebar styling</label></th>
        <td>
            <input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" />
        </td>
    </tr>
    </tbody>
</table>
<script type="text/javascript">
    var sidebartoggle = jQuery('.sidebar-content-controls .switchable');
    if(jQuery('#sidebarbool').is(':checked')){

    } else {
        sidebartoggle.hide();
    }
    jQuery('#sidebarbool').click(function(){
        if(jQuery(this).is(':checked')){
            sidebartoggle.slideDown(500);
        } else {
            sidebartoggle.slideUp(500);
        }
    });
</script>