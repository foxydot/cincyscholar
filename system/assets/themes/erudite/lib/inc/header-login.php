<?php
/**
 * Created by PhpStorm.
 * User: CMO
 * Date: 11/29/17
 * Time: 1:07 PM
 */

add_action('genesis_footer','msdlab_print_header_login_modal',8);

function msdlab_print_header_login_modal(){
    $args = array(
        'echo' => false,
    );
    $ret = '<div id="login-modal" class="login-modal modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Login to '.get_bloginfo('name').'</h4>
      </div>
      <div class="modal-body">
        '.wp_login_form($args).'
        <p id="nav">';
if ( get_option( 'users_can_register' ) ) :
    $registration_url = sprintf( '<a href="%s">%s</a>', esc_url( wp_registration_url() ), __( 'Register' ) );

    /** This filter is documented in wp-includes/general-template.php */
    $ret .= apply_filters( 'register', $registration_url );

    $ret .= ' | ';
endif;
        $ret .= '<a href="'.esc_url( wp_lostpassword_url() ).'">Lost your password?</a>
        </p>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->';
    print $ret;
}
