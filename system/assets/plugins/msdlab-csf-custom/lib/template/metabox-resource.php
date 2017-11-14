<?php global $wpalchemy_media_access; ?>
<table class="form-table">
        <tr>
            <?php $mb->the_field('title'); ?>

            <th scope="row"><label for="<?php $mb->the_name(); ?>">File title</label></th>
            <td>
                <p><input class="large-text" type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" placeholder="" /></p>
            </td>
        </tr>
        <?php $mb->the_field('authorname'); ?>
        <tr valign="top">
            <th scope="row"><label for="<?php $mb->the_name(); ?>">Media Author Name</label></th>
            <td>
                <p><input class="large-text" type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" placeholder="" /></p>
            </td>
        </tr>
        <?php $mb->the_field('file'); ?>
        <tr>
            <th scope="row"><label for="<?php $mb->the_name(); ?>">File</label></th>
            <td>
                <?php $wpalchemy_media_access->setGroupName('pdf-file')->setInsertButtonLabel('Insert This')->setTab('upload'); ?>
                <?php echo $wpalchemy_media_access->getField(array('name' => $mb->get_the_name(), 'value' => $mb->get_the_value())); ?>
                <?php echo $wpalchemy_media_access->getButton(array('label' => 'Add File')); ?>
            </td>
        </tr>
        <?php $mb->the_field('pubdate'); ?>
        <tr valign="top">
            <th scope="row"><label for="<?php $mb->the_name(); ?>">Media Publication Date</label></th>
            <td>
                <p><input class="regular-text date-picker" type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" placeholder="" /></p>
            </td>
        </tr>
</table>