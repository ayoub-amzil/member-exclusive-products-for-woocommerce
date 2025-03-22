<?php
/**
 * Plugin Name: Member-Exclusive Products for WooCommerce
 * Plugin URI: https://wordpress.org/plugins/member-exclusive-products-for-woocommerce
 * Description: A WordPress plugin that empowers store owners to control product visibility based on user login status.
 * Version: 1.0.0
 * Author: AMZIL AYOUB
 * Author URI: https://www.linkedin.com/in/amzil-ayoub/
 * Text Domain: member-exclusive-products-for-woocommerce
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

defined( 'ABSPATH' ) || exit;


add_action( 'woocommerce_product_options_general_product_data', 'mepfw_add_restriction_field' );
function mepfw_add_restriction_field() {
    global $post;

    echo '<div class="options_group">';

    wp_nonce_field( 'mepfw_save_restriction_field', 'mepfw_restriction_nonce' );

    woocommerce_wp_checkbox( array(
        'id'            => '_mepfw_is_logged_in_only',
        'label'         => __( 'Enable to hide this product from guests', 'member-exclusive-products-for-woocommerce' ),
        //'description'   => __( 'Enable to hide this product from guests', 'member-exclusive-products-for-woocommerce' ),
        'value'         => get_post_meta( $post->ID, '_mepfw_is_logged_in_only', true ),
    ) );
    echo '</div>';
}

add_action( 'woocommerce_process_product_meta', 'mepfw_save_restriction_field' );
function mepfw_save_restriction_field( $product_id ) {
    if ( ! isset( $_POST['mepfw_restriction_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mepfw_restriction_nonce'] ), 'mepfw_save_restriction_field' ) ) {
        wp_die( esc_html__( 'Security check failed.', 'member-exclusive-products-for-woocommerce' ) );
    }

    if ( ! current_user_can( 'edit_product', $product_id ) ) {
        wp_die( esc_html__( 'You do not have permission to edit this product.', 'member-exclusive-products-for-woocommerce' ) );
    }


    $value = isset( $_POST['_mepfw_is_logged_in_only'] ) ? 'yes' : 'no';
    $value = sanitize_text_field( wp_unslash( $value ) );

    update_post_meta( $product_id, '_mepfw_is_logged_in_only', $value );
}

require_once plugin_dir_path( __FILE__ ) . 'includes/product-log-restriction.php';

if ( is_admin() ) {
    require_once plugin_dir_path( __FILE__ ) . 'admin/settings-page.php';
}