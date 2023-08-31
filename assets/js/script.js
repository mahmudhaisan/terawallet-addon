jQuery(document).ready(function ($) {




    $('#buy_credit_btn').click(function (e) {
        e.preventDefault();

        // Toggle the visibility of the checkout div
        $(".my-woo-checkout").toggle();

    });



    $('#credit_amount').on('change keyup',  function () {
        var product_amount = $(this).val();

        if(product_amount == ''){
            product_amount =1;
        }
        console.log(product_amount);
        $.ajax({
            url: myaccount_checkout.ajaxurl,
            type: 'post',
            data: {
                'action': 'add_to_cart_product_amount_update',
                'product_amount_value': product_amount,
            },
            success: function (response) {
                console.log(response);
                $(document.body).trigger('update_checkout');
            }
        })

    })



    // When the category dropdown changes
    $('#category').change(function () {
        var categoryId = $(this).val();

        // Disable the product dropdown
        $('#product').prop('disabled', true);

        // Clear options in the product dropdown
        $('#product').html('<option value="">Select a product</option>');

        if (categoryId !== '') {
            // Fetch products based on the selected category using AJAX
            $.ajax({
                type: 'POST',
                url: myaccount_checkout.ajaxurl, // WordPress AJAX URL
                data: {
                    action: 'get_products_by_category',
                    category_id: categoryId,
                },
                success: function (response) {
                    // Populate the product dropdown with the retrieved products
                    $('#product').html(response);
                    $('#product').prop('disabled', false);
                },
            });
        }
    });



    $('#submit-button').click(function () {
        var selectedProductId = $('#product').val();
        var resultContainer = $('#result-container');

        console.log(selectedProductId);
        if (selectedProductId) {
            // Product selected, send an AJAX request to add the product to the cart
            $.ajax({
                type: 'POST',
                url: myaccount_checkout.ajaxurl,
                data: {
                    action: 'add_product_to_cart',
                    product_id: selectedProductId,
                },
                // dataType: 'json',
                success: function (response) {

                    console.log(response);
                    // Trigger an update of the WooCommerce cart and checkout
                    $(document.body).trigger('update_checkout');
                },
                error: function () {
                    // Handle errors here
                    resultContainer.html('<div class="alert alert-danger">Error adding the product to the cart.</div>');
                },
            });
        } else {
            // No product selected, display an error message
            resultContainer.html('<div class="alert alert-danger">Please select a product.</div>');
        }
    });


});