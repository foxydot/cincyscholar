<?php global $wpalchemy_media_access; ?>
<div class="msdlab_meta" id="subtitle_metabox">
    <ul>
        <li>
            <?php $mb->the_field('subtitle'); ?>
            <input type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" />
        </li>
    </ul>
</div>