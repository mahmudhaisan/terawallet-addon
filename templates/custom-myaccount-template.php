<?php
defined('ABSPATH') || exit;

global $post;
$user = wp_get_current_user();
$user_id = $user->ID;
$current_balance = retrieve_wallet_debit_transactions($user_id);
$page_id = $post->ID;


// Get the current user's ID
$current_user_id = get_current_user_id();

// Get all available order statuses
$order_statuses = wc_get_order_statuses();

// Initialize arrays to store counts for each status
$completed_order_count = 0;
$failed_order_count = 0;
$other_status_counts = array();
$processing_hold_pending_count = 0;
$total_pending_processing_hold_amount = 0;


// Query arguments to retrieve orders count for the current user with each status
foreach ($order_statuses as $status_key => $status_name) {
    $args = array(
        'post_type'      => 'shop_order',
        'post_status'    => $status_key,
        'posts_per_page' => -1, // Retrieve all orders for each status
        'meta_query'     => array(
            array(
                'key'     => '_customer_user',
                'value'   => $current_user_id,
                'compare' => '=',
            ),
        ),
    );

    // Get the count of orders based on the query arguments
    $status_count = count(get_posts($args));

    // Categorize counts based on order status
    if (in_array($status_key, array('wc-processing', 'wc-on-hold', 'wc-pending'))) {
        $processing_hold_pending_count += $status_count;


        // Calculate the total order amount for processing, on-hold, and pending orders
        $orders = get_posts($args);
        foreach ($orders as $order) {
            $order_id = $order->ID;
            $order_amount = get_post_meta($order_id, '_order_total', true);
            $total_pending_processing_hold_amount += floatval($order_amount);
        }

    } elseif ($status_key === 'wc-completed') {
        $completed_order_count = $status_count;
    } elseif ($status_key === 'wc-failed') {
        $failed_order_count = $status_count;
    } else {
        $other_status_counts[$status_key] = $status_count;
    }
}




?>
<div class="container my-5">

    <div class="row gx-5 cus-content bg-white  text-dark">
        <div class="col-md-6">
            <div class="shadow-lg p-3 mb-3 bg-white rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="h5">Your Current Balance</p>
                        <p class="h5 font-weight-300"><?php echo $current_balance; ?></p>
                    </div>

                    <div>
                        <button class="btn btn-dark text-white" id="buy_credit_btn">
                            <i class="fas fa-cart-shopping"></i> Buy Credit
                        </button>
                        <input type="hidden" value="<?php echo $page_id; ?>">
                    </div>




                </div>



                <div class="my-woo-checkout" style="display: none;">

                
                    <?php echo do_shortcode('[woocommerce_checkout]'); ?>

                </div>
            </div>
        </div>
        <div class="col-md-3 ">
            <div class=" shadow-lg p-3 mb-3 bg-white rounded">
                <p class="">Processing Balance</p>
                <h3 class="cus-unpaid-balance"><?php echo get_woocommerce_currency_symbol(). $total_pending_processing_hold_amount   ?> </h3>
            </div>
        </div>
        
        
        <div class="col-md-3 ">
            <div class=" shadow-lg p-3 mb-3 bg-white rounded">
                <p class="">Buy Products</p>
                <a href="<?php echo wc_get_checkout_url() ; ?>" class="h6 text-dark" > Click Here </a>
            </div>
        </div>
        
       
    </div>






    <div class="row cus-order-area">
        <div class="col-md-4 ">
            <div class="cus-card shadow-lg p-3 mb-3 bg-white rounded">

                <h4><?php echo  $completed_order_count ?></h4>
                <div class="success-bar"></div>
                <p>Completed Orders</p>
            </div>
        </div>
        <div class="col-md-4 ">
            <div class="cus-card shadow-lg p-3 mb-3 bg-white rounded">
                <h4> <?php echo  $processing_hold_pending_count; ?></h4>
                <div class="process-bar"></div>
                <p>Orders in process</p>
            </div>
        </div>
        <div class="col-md-4 ">
            <div class="cus-card shadow-lg p-3 mb-3 bg-white rounded">
                <h4><?php echo  $failed_order_count ?></h4>
                <div class="falls-bar"></div>
                <p>Orders process falls</p>
            </div>
        </div>
    </div>


</div>


<?php

