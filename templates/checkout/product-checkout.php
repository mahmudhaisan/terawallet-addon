<?php

/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

if (!defined('ABSPATH')) {
	exit;
}


$rechargable_product = wc_get_product( apply_filters( 'woo_wallet_rechargeable_product_id', get_option( '_woo_wallet_recharge_product' ) ) );



$product_id_to_remove = $rechargable_product->id;

// Check if the product is already in the cart
foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
	if ($cart_item['product_id'] == $product_id_to_remove) {
		WC()->cart->remove_cart_item($cart_item_key);
		break; // Remove the first occurrence and exit loop
	}
}

// If checkout registration is disabled and not logged in, the user cannot checkout.
if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
	echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
	return;
}

?>

<form id="dependent-form" class="mt-5 mb-5">


	<div class="row">
		<div class="col-md-5">
			<div class="form-group">
				<label for="category" class="form-label">Select Category:</label>
				<select class="form-select p-3" id="category" name="category">
					<option value="">Select a category</option>
					<?php
					// Query to get all product categories
					$categories = get_terms('product_cat', array('hide_empty' => false, 'exclude' => array(45)));

					print_r($categories);

					foreach ($categories as $category) {
						echo '<option value="' . $category->term_id . '">' . $category->name . '</option>';
					}
					?>
				</select>
			</div>
		</div>
		<div class="col-md-5">
			<div class="form-group">
				<label for="product" class="form-label">Select Product:</label>
				<select class="form-select p-3" id="product" name="product" disabled>
					<option value="">Select a product</option>
				</select>
			</div>
		</div>

		<div class="col-md-2 mt-4 d-flex align-items-center">
			<div class="">
				<button type="button" id="submit-button" class="btn btn-primary btn-custom p-3">Submit</button>
			</div>

		</div>


	</div>

</form>



<?php

?>



<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

	<?php if ( $checkout->get_checkout_fields() ) : ?>

		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div class="col2-set" id="customer_details">
			<div class="col-1">
				<?php do_action( 'woocommerce_checkout_billing' ); ?>
			</div>

			<div class="col-2">
				<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			</div>
		</div>

		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

	<?php endif; ?>
	
	<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
	
	<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>
	
	<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

	<div id="order_review" class="woocommerce-checkout-review-order">
		<?php do_action( 'woocommerce_checkout_order_review' ); ?>
	</div>

	<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
