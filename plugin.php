<?php

/**
 * Plugin Name: Myaccount One Page Checkout
 * Plugin URI: https://github.com/mahmudhaisan/
 * Description: One Page Myaccount Page Checkout
 * Author: Mahmud haisan
 * Author URI: https://github.com/mahmudhaisan
 * Developer: Mahmud Haisan
 * Developer URI: https://github.com/mahmudhaisan
 * Text Domain: myc
 * Domain Path: /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

use Automattic\WooCommerce\Blocks\BlockTypes\ProductNew;

if (!defined('ABSPATH')) {
    die('are you cheating');
}

define("MYC_PLUGINS_PATH", plugin_dir_path(__FILE__));
define("MYC_PLUGINS_DIR_URL", plugin_dir_url(__FILE__));


add_action('wp_enqueue_scripts', 'myc_custom_enqueue_assets');
add_filter('wc_get_template', 'myc_get_template', 10, 5);
add_filter('woocommerce_checkout_fields', 'custom_checkout_fields', 999999999);
add_filter('woocommerce_locate_template', 'checkout_page_templates_override_woo', 10, 3);
add_action('woocommerce_checkout_before_order_review', 'custom_checkout_field');
add_filter('woocommerce_checkout_redirect_empty_cart', '__return_false');
add_action('wp_footer',  'woo_checkout_ajax_trigger');
add_filter('woocommerce_checkout_update_order_review_expired', '__return_false');
add_filter('woocommerce_add_cart_item_data', 'add_woo_wallet_product_price_to_cart_item_data', 10, 4);
add_filter('woocommerce_payment_successful_result', 'remove_woocommerce_payment_successful_redirect', 10, 2);


// Enqueue CSS and JavaScript
function myc_custom_enqueue_assets()
{
    wp_enqueue_style('bootstrap-min', plugin_dir_url(__FILE__) . 'assets/css/bootstrap.min.css');
    wp_enqueue_style('style-css-ss', plugin_dir_url(__FILE__) . 'assets/css/style.css');
    wp_enqueue_style('fontawesome-css-min', plugin_dir_url(__FILE__) . 'assets/css/fontawesome.min.css');
    wp_enqueue_script('bootstrap-min', plugin_dir_url(__FILE__) . 'assets/js/bootstrap.min.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('script', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), '1.0.0', true);
    wp_localize_script(
        'script',
        'myaccount_checkout',
        array(
            'ajaxurl' => admin_url('admin-ajax.php'),
        )
    );
}

function myc_get_template($located, $template_name, $args, $template_path, $default_path)
{

    global $post;
    $post_id =  $post->ID;
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = (array) $user->roles;
        $user_role = $roles[0];
        // $current_balance = retrieve_wallet_debit_transactions($user_id);
        if (('administrator' == $user_role || 'customer' == $user_role) && 'myaccount/my-account.php' == $template_name) {
            // echo $current_balance;
            $located = MYC_PLUGINS_PATH . 'templates/custom-myaccount-template.php';
        }
    }
    return $located;
}

function retrieve_wallet_debit_transactions($user_id)
{
    global $wpdb;

    // Replace 'wp_woo_wallet_transactions' with your actual table name
    $table_name = $wpdb->prefix . 'woo_wallet_transactions';

    // Prepare the SQL query
    $query = $wpdb->prepare(
        "SELECT * FROM $table_name WHERE (type = %s OR type = %s) AND user_id = %d ORDER BY transaction_id DESC",
        'debit',
        'credit',
        $user_id
    );

    // Retrieve the results
    $debit_results = $wpdb->get_results($query);

    $post_orders = wc_get_orders(array(
        'customer' => $user_id,
    ));

    $current_balance = get_woocommerce_currency_symbol() . number_format_i18n($debit_results[0]->balance);

    // Loop through the retrieved orders
    foreach ($post_orders as $order) {
        // Get order items (line items)
        $order_items = $order->get_items();
    }

    return ($current_balance);
}


function custom_checkout_fields($checkout_fields)
{
    global $post;
    $url     = wp_get_referer();
    $page_id = url_to_postid($url);
    $post_id = $post->ID;


    // print_r(wp_get_referer());  

    unset($checkout_fields['billing']['billing_first_name']);
    unset($checkout_fields['billing']['billing_last_name']);
    unset($checkout_fields['billing']['billing_phone']);
    unset($checkout_fields['billing']['billing_email']);
    unset($checkout_fields['order']['order_comments']); // remove order notes
    // and to remove the billing fields below
    unset($checkout_fields['billing']['billing_company']); // remove company field
    unset($checkout_fields['billing']['billing_country']);
    unset($checkout_fields['billing']['billing_address_1']);
    unset($checkout_fields['billing']['billing_address_2']);
    unset($checkout_fields['billing']['billing_city']);
    unset($checkout_fields['billing']['billing_state']); // remove state field
    unset($checkout_fields['billing']['billing_postcode']); // remove zip code field
    // unset($checkout_fields['billing']['billing_wooccm11']); // remove zip code field
    if ($post_id == 27) {
        unset($checkout_fields['billing']['billing_wooccm11']);
    }
    return $checkout_fields;
}


function checkout_page_templates_override_woo($template, $template_name, $template_path)
{
    $basename = basename($template);
    global $post;
    $page_id_num = $post->ID;

    $selected_myaccount_checkout = get_option('myaccount_checkout');
    $selected_product_checkout = get_option('product_checkout');


    if ($page_id_num ==  $selected_myaccount_checkout) {
        if ($basename == 'form-checkout.php') {
            $template = MYC_PLUGINS_PATH . 'templates/checkout/myaccount-checkout.php';
        }
        if ($basename == 'review-order.php') {
            $template = MYC_PLUGINS_PATH . 'templates/checkout/review-order.php';
        }
    }

    if ($page_id_num == $selected_product_checkout) {
        if ($basename == 'form-checkout.php') {
            $template = MYC_PLUGINS_PATH . 'templates/checkout/product-checkout.php';
        }
        if ($basename == 'review-order.php') {
            $template = MYC_PLUGINS_PATH . 'templates/checkout/review-order.php';
        }
    }
    return $template;
}




function custom_checkout_field($checkout)
{
    global $post;

    if (is_account_page()) {
?>
        <div class="quantity mt-4">
            <label for="amount">amount:</label>
            <input type="number" id="credit_amount" name="credit_amount" value="1" min="1">
        </div>
    <?php }
}


function woo_checkout_ajax_trigger()
{ ?>
    <script>
        jQuery(function($) {

            $(document).on('change', '#credit_amount', function() {
                $(document.body).trigger('update_checkout');

            })

        });
    </script>

<?php }


// add_filter( 'woocommerce_add_cart_item_data', 'add_woo_wallet_product_price_to_cart_item_data_ajax', 10, 4 );


function remove_woocommerce_payment_successful_redirect($result, $order_id)
{
    $success_text = 'Order Is Successfull.';
    $result['redirect'] = false;
    $result['messages'] = "<div class='mt-3 bg-success text-white p-3'>$success_text</div><a class='btn btn-dark text-white mt-3 p-2' href='' >Order Aagain</a>";

    return $result;
}


function add_woo_wallet_product_price_to_cart_item_data($cart_item_data, $product_id)
{


global $woocommerce;
    $credit_product_id = get_option('product_credit');
    $product = wc_get_product($credit_product_id);
    $product_price = $product->get_price();

    // $cart_item_data['recharge_amount'] = intval($_SESSION['new']);
    $cart_item_data['recharge_amount'] = $product_price;
    

    $woocommerce->cart->set_session(); 
    return $cart_item_data;
}




if (is_admin() && defined('DOING_AJAX') && DOING_AJAX) {
    require MYC_PLUGINS_PATH . '/ajax.php';
}


// Add a custom dashboard page to the admin menu
function custom_dashboard_menu_page()
{
    add_menu_page(
        'Custom Dashboard',
        'Custom Dashboard',
        'manage_options',
        'custom_dashboard_page',
        'render_custom_dashboard_page'
    );
}
add_action('admin_menu', 'custom_dashboard_menu_page');

// Render the custom dashboard page
function render_custom_dashboard_page()
{
?>
    <div class="wrap">
        <h2>Custom Dashboard</h2>
        <form method="post" action="options.php">
            <?php settings_fields('custom_dashboard_group'); ?>
            <?php do_settings_sections('custom_dashboard_page'); ?>
            <?php submit_button('Save Settings'); ?>
        </form>
    </div>
<?php
}

// Register settings fields and sections
function custom_dashboard_settings_fields()
{
    add_settings_section(
        'custom_dashboard_section',
        'Select Options',
        'render_custom_dashboard_section',
        'custom_dashboard_page'
    );

    $pages = get_pages();
    $page_options = array();
    foreach ($pages as $page) {
        $page_options[$page->ID] = $page->post_title;
    }


    $products = get_posts(array(
        'post_type' => 'product',
        'numberposts' => -1,
        'post_status'    => array( 'publish', 'private' ),
    ));
    
    $product_options = array();
    foreach ($products as $product) {
        $product_options[$product->ID] = $product->post_title;
    }
    add_settings_field(
        'myaccount_checkout',
        'My Account Checkout Page',
        'render_select_field',
        'custom_dashboard_page',
        'custom_dashboard_section',
        ['field' => 'myaccount_checkout', 'options' => $page_options]
    );

    add_settings_field(
        'product_checkout',
        'Product Checkout',
        'render_select_field',
        'custom_dashboard_page',
        'custom_dashboard_section',
        ['field' => 'product_checkout', 'options' => $page_options]
    );

    add_settings_field(
        'product_credit',
        'Product Credit',
        'render_select_field',
        'custom_dashboard_page',
        'custom_dashboard_section',
        ['field' => 'product_credit', 'options' => $product_options]
    );


    register_setting('custom_dashboard_group', 'myaccount_checkout');
    register_setting('custom_dashboard_group', 'product_checkout');
    register_setting('custom_dashboard_group', 'product_credit');
}
add_action('admin_init', 'custom_dashboard_settings_fields');

// Render settings fields
function render_select_field($args)
{
    $field = $args['field'];
    $options = $args['options'];
    $value = get_option($field);
?>
    <select name="<?php echo $field; ?>">
        <?php
        foreach ($options as $option_id => $option_title) {
            echo '<option value="' . esc_attr($option_id) . '" ' . selected($value, $option_id, false) . '>' . esc_html($option_title) . '</option>';
        }
        ?>
    </select>
<?php
}

// Render settings section
function render_custom_dashboard_section()
{
    return true; 

}
?>