<?php


add_action('wp_ajax_add_to_cart_product_amount_update', 'add_to_cart_product_amount_update');

function add_to_cart_product_amount_update()
{
    global $woocommerce;
    $product_quantity = $_POST['product_amount_value'];
    $credit_product_id = get_option('product_credit');
    $product = wc_get_product(  $credit_product_id);
    $product_price = $product->get_price();
    $product_updated_price = $product_price * $product_quantity;


    foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
        $woocommerce->cart->cart_contents[$cart_item_key]['recharge_amount'] = $product_updated_price;

    } 
    $woocommerce->cart->set_session(); 


    print_r(wc()->cart->get_cart());
    
    wp_die();
}












function get_products_by_category() {
    // Get the category ID from the AJAX request
    $category_id = $_POST['category_id'];

    // Query to get products based on the category
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'id',
                'terms' => $category_id,
            ),
        ),
    );

    $products = new WP_Query($args);

    // Generate the options for the product dropdown
    $options = '<option value="">Select a product</option>';
    if ($products->have_posts()) {
        while ($products->have_posts()) {
            $products->the_post();
            $options .= '<option value="' . get_the_ID() . '">' . get_the_title() . '</option>';
        }
    }

    echo $options;
    wp_die();
}

add_action('wp_ajax_get_products_by_category', 'get_products_by_category');
add_action('wp_ajax_nopriv_get_products_by_category', 'get_products_by_category');






function add_product_to_cart() {
    if (isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        $quantity = 1; // You can customize the quantity as needed.

        // Add the product to the cart
        WC()->cart->add_to_cart($product_id, $quantity);

        global $woocommerce;
// $woocommerce->cart->get_cart();

print_r($woocommerce->cart->cart_contents );
    } else {
        echo json_encode(array('error' => 'Invalid product ID'));
    }

    die();
}

add_action('wp_ajax_add_product_to_cart', 'add_product_to_cart');
add_action('wp_ajax_nopriv_add_product_to_cart', 'add_product_to_cart');
