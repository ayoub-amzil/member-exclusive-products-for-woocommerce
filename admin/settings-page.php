<?php
if (!defined('ABSPATH')) exit;

add_action('restrict_manage_posts', 'mepfw_add_products_filter');
function mepfw_add_products_filter($post_type) {
    if ('product' !== $post_type) return;

    if (isset($_GET['mepfw_filter_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['mepfw_filter_nonce'])), 'mepfw_filter_nonce')) {
        if (isset($_GET['mepfw_login_restriction'])) {
            $selected = sanitize_text_field(wp_unslash($_GET['mepfw_login_restriction']));
        } else {
            $selected = '';
        }
    } else {
        $selected = '';
    }

    wp_nonce_field('mepfw_filter_nonce', 'mepfw_filter_nonce');
    ?>
    <select name="mepfw_login_restriction" id="mepfw_login_restriction">
        <option value=""><?php echo esc_html__('All access types', 'member-exclusive-products-for-woocommerce'); ?></option>
        <option value="restricted" <?php selected($selected, 'restricted'); ?>>
            <?php echo esc_html__('Restricted to Logged-in', 'member-exclusive-products-for-woocommerce'); ?>
        </option>
        <option value="public" <?php selected($selected, 'public'); ?>>
            <?php echo esc_html__('Public Products', 'member-exclusive-products-for-woocommerce'); ?>
        </option>
    </select>
    <?php
}

add_filter('parse_query', 'mepfw_filter_products_query');
function mepfw_filter_products_query($query) {
    global $pagenow, $post_type;

    if (
        'edit.php' !== $pagenow ||
        'product' !== $post_type ||
        !isset($_GET['mepfw_login_restriction']) ||
        empty($_GET['mepfw_login_restriction'])
    ) return;

    if (!isset($_GET['mepfw_filter_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['mepfw_filter_nonce'])), 'mepfw_filter_nonce')) {
        wp_die(esc_html__('Security check failed', 'member-exclusive-products-for-woocommerce'));
    }

    $meta_query = array();
    $value = sanitize_text_field(wp_unslash($_GET['mepfw_login_restriction']));

    switch ($value) {
        case 'restricted':
            $meta_query[] = array(
                'key' => '_mepfw_is_logged_in_only',
                'value' => 'yes',
                'compare' => '='
            );
            break;

        case 'public':
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key' => '_mepfw_is_logged_in_only',
                    'value' => 'no',
                    'compare' => '='
                ),
                array(
                    'key' => '_mepfw_is_logged_in_only',
                    'compare' => 'NOT EXISTS'
                )
            );
            break;
    }

    if (!empty($meta_query)) {
        $query->set('meta_query', $meta_query);
    }
}

add_filter('bulk_actions-edit-product', 'mepfw_add_bulk_actions');
function mepfw_add_bulk_actions($bulk_actions) {
    $bulk_actions['mepfw_set_restricted'] = __('Set as login required', 'member-exclusive-products-for-woocommerce');
    $bulk_actions['mepfw_set_public'] = __('Set as public', 'member-exclusive-products-for-woocommerce');
    return $bulk_actions;
}

add_filter('handle_bulk_actions-edit-product', 'mepfw_handle_bulk_actions', 10, 3);
function mepfw_handle_bulk_actions($redirect_to, $doaction, $post_ids) {
    if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'bulk-posts')) {
        wp_die(esc_html__('Security check failed', 'member-exclusive-products-for-woocommerce'));
    }

    if (!in_array($doaction, ['mepfw_set_restricted', 'mepfw_set_public'])) {
        return $redirect_to;
    }

    $updated_count = 0;
    $value = ($doaction === 'mepfw_set_restricted') ? 'yes' : 'no';

    foreach ($post_ids as $post_id) {
        if (update_post_meta($post_id, '_mepfw_is_logged_in_only', $value)) {
            $updated_count++;
            mepfw_clear_restriction_transient($post_id);
        }
    }

    $redirect_to = add_query_arg(
        [
            'mepfw_bulk_updated' => $updated_count,
            'mepfw_bulk_action' => $doaction,
            'mepfw_bulk_nonce' => wp_create_nonce('mepfw_bulk_action')
        ],
        $redirect_to
    );

    return $redirect_to;
}

add_action('admin_notices', 'mepfw_bulk_action_admin_notice');
function mepfw_bulk_action_admin_notice() {
    if (!empty($_REQUEST['mepfw_bulk_updated']) && isset($_REQUEST['mepfw_bulk_action'])) {
        if (!isset($_REQUEST['mepfw_bulk_nonce']) ||
            !wp_verify_nonce(
                sanitize_text_field(wp_unslash($_REQUEST['mepfw_bulk_nonce'])),
                'mepfw_bulk_action'
            )) {
            return;
        }

        $count = intval($_REQUEST['mepfw_bulk_updated']);
        $action = sanitize_text_field(wp_unslash($_REQUEST['mepfw_bulk_action']));

        if (!in_array($action, ['mepfw_set_restricted', 'mepfw_set_public'])) {
            return;
        }

        $message = sprintf(
        /* translators: %d number of products updated */
            _n(
                'Updated %d product visibility.',
                'Updated %d products visibility.',
                $count,
                'member-exclusive-products-for-woocommerce'
            ),
            $count
        );

        $type = ($action === 'mepfw_set_restricted') ?
            __('made restricted to logged-in users', 'member-exclusive-products-for-woocommerce') :
            __('made publicly visible', 'member-exclusive-products-for-woocommerce');

        echo '<div class="notice notice-success is-dismissible">';
        echo '<p>' . esc_html($message) . ' ' . esc_html($type) . '.</p>';
        echo '</div>';
    }
}