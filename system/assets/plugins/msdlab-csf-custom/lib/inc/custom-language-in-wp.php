<?php
function msdlab_email_info_language() {

    $your_content = ob_get_contents();
    $your_content = preg_replace( '/(\<p class="message register"\>)(.*?)(\<\/p\>)/', "$1 Please register to apply for scholarships. Use the address that you will be using after high school graduation, not your high school email address. Each applicant or renewal applicant must use their own unique email address and system login.<br \/><br \/>Please be sure to check spam folders if your registration information does not appear to arrive. $3", $your_content );
    ob_get_clean();
    echo $your_content;
}
add_action( 'register_form', 'msdlab_email_info_language' );

//    $your_content = preg_replace('/(\<p id="reg_passmail"\>Registration confirmation will be emailed to you.)(\<\/p\>)/',"$1 Please be sure to check SPAM folders if the email does not seem to have arrived.$2",$your_content);