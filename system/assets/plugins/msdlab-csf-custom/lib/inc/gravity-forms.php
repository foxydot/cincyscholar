<?php
/*
 * functions to override gravity forms defaults
 */

//pre render form 2
add_filter( 'gform_get_form_filter_2', 'msdlab_add_donate_button' );

function msdlab_add_donate_button($formstring){
    $jquery[] = "<script>
jQuery(document).ready(function($) {
    src = $('#gform_submit_button_2')
    donate = src.clone();
    donate.val('Donate').attr('id','gform_donate_button_2');
    src.after(donate);
});
</script>";
    $formstring .= implode("\n",$jquery);
    return $formstring;
}