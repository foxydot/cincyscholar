<?php global $wpalchemy_media_access; ?>
<table class="form-table">
    <tbody>
    <?php $mb->the_field('phone'); ?>
    <tr valign="top">
        <th scope="row"><label for="<?php $mb->the_name(); ?>">Phone</label></th>
        <td>
            <p><input class="large-text" type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" placeholder="" /></p>
        </td>
    </tr>
    <?php $mb->the_field('mobile'); ?>
    <tr valign="top">
        <th scope="row"><label for="<?php $mb->the_name(); ?>">Mobile</label></th>
        <td>
            <p><input class="large-text" type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" placeholder="" /></p>
        </td>
    </tr>
    <?php $mb->the_field('fax'); ?>
    <tr valign="top">
        <th scope="row"><label for="<?php $mb->the_name(); ?>">Fax</label></th>
        <td>
            <p><input class="large-text" type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" placeholder="" /></p>
        </td>
    </tr>
    <?php $mb->the_field('email'); ?>
    <tr valign="top">
        <th scope="row"><label for="<?php $mb->the_name(); ?>">Email Address</label></th>
        <td>
            <p><input class="large-text" type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" placeholder="" /></p>
        </td>
    </tr>
    <?php $mb->the_field('url'); ?>
    <tr valign="top">
        <th scope="row"><label for="<?php $mb->the_name(); ?>">Website</label></th>
        <td>
            <p><input class="large-text" type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" placeholder="http://" /></p>
        </td>
    </tr>
    <tr>
        <th colspan="2">
            <hr>
        </th>
    </tr>
    <tr>
        <th colspan="2">
            Social Media
        </th>
    </tr>
    <?php $mb->the_field('twitter'); ?>
    <tr valign="top">
        <th scope="row"><label for="<?php $mb->the_name(); ?>"><i class="fa fa-twitter"></i> Twitter URL</label></th>
        <td>
            <p><input class="large-text" type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" placeholder="http://" /></p>
        </td>
    </tr>
    <?php $mb->the_field('linked_in'); ?>
    <tr valign="top">
        <th scope="row"><label for="<?php $mb->the_name(); ?>"><i class="fa fa-linkedin"></i> Linked In URL</label></th>
        <td>
            <p><input class="large-text" type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" placeholder="http://" /></p>
        </td>
    </tr>
    <?php $mb->the_field('facebook'); ?>
    <tr valign="top">
        <th scope="row"><label for="<?php $mb->the_name(); ?>"><i class="fa fa-facebook"></i> Facebook URL</label></th>
        <td>
            <p><input class="large-text" type="text" name="<?php $mb->the_name(); ?>" value="<?php $mb->the_value(); ?>" placeholder="http://" /></p>
        </td>
    </tr>
    </tbody>
</table>