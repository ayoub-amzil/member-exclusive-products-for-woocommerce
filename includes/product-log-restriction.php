<?php
add_action( 'template_redirect', 'mepfw_restrict_single_product' );
function mepfw_restrict_single_product() {
    if ( is_user_logged_in() || ! is_singular( 'product' ) ) return;

    $product = wc_get_product( get_queried_object_id() );
    if ( 'yes' === $product->get_meta( '_mepfw_is_logged_in_only' ) ) {
        wp_redirect( wc_get_page_permalink( 'myaccount' ) );
        exit;
    }
}


add_action( 'pre_get_posts', 'mepfw_modify_product_queries' );
function mepfw_modify_product_queries( $query ) {
    if ( is_admin() || is_user_logged_in() ) return;

    if ( $query->is_main_query() && ( is_shop() || is_product_taxonomy() || is_search() ) ) {
        $meta_query = (array) $query->get( 'meta_query' );

        $meta_query[] = array(
            'relation' => 'OR',
            array(
                'key' => '_mepfw_is_logged_in_only',
                'compare' => 'NOT EXISTS'
            ),
            array(
                'key' => '_mepfw_is_logged_in_only',
                'value' => 'yes',
                'compare' => '!='
            )
        );

        $query->set( 'meta_query', $meta_query );
    }
}

add_action('woocommerce_before_shop_loop', 'mepfw_show_restricted_products_notice');
function mepfw_show_restricted_products_notice() {
    if (is_user_logged_in()) return;

    $has_restricted = get_transient('mepfw_has_restricted_products');

    if (false === $has_restricted) {
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_mepfw_is_logged_in_only',
                    'value' => 'yes',
                    'compare' => '='
                )
            ),
            'posts_per_page' => 1
        );

        $query = new WP_Query($args);
        $has_restricted = $query->have_posts() ? 1 : 0;
        set_transient('mepfw_has_restricted_products', $has_restricted, HOUR_IN_SECONDS);
    }

    if ($has_restricted) {
        echo '<div class="woocommerce-info">';
        echo esc_html__('Some products are only available to registered users. Please login to view all products.', 'member-exclusive-products-for-woocommerce');
        echo ' <a href="' . esc_url(wc_get_page_permalink('myaccount')) . '">' . esc_html__('Login here', 'member-exclusive-products-for-woocommerce') . '</a>';
        echo '</div>';
    }
}

add_action('save_post_product', 'mepfw_clear_restriction_transient');
function mepfw_clear_restriction_transient($post_id) {
    delete_transient('mepfw_has_restricted_products');
}