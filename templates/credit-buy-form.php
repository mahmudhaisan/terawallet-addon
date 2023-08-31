
<?php




$url     = wp_get_referer();
$page_id = url_to_postid( $url ); 

echo do_shortcode('[woocommerce_checkout]');
    // add_filter('woocommerce_checkout_fields', 'custom_remove_billing_fields');



