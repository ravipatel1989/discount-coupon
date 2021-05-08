(function ($) {
    jQuery(document).ready(function () {

        // Product Remove Ajax Call
        $("#product_details").on('click', '.btnDelete', function () {
            $(this).closest('tr').remove();
        });

        var ajaxRequest = null;
        // Search Product by Keyword
        $("#keyword").keyup(function () {
            ajaxRequest = jQuery.ajax({
                url: backendajax.admin_url,
                type: 'post',
                data: {action: 'data_fetch', keyword: jQuery('#keyword').val()},
                beforeSend : function()    {           
                    if(ajaxRequest != null) {
                        ajaxRequest.abort();
                    }
                },
                success: function (data) {
                    jQuery('#datafetch').html(data);
                }
            });
        });
        $(document).on("click", "ul#searched_products li", function (e) {
            var productId = $(this).attr('id');
            var productTitle = $(this).data('title');
            var productSku = $(this).data('sku');
            var productPrice = $(this).data('price');
            var productSaleprice = $(this).data('sale_price');
            var productHtml = "<tr><td>" + productTitle + "</td><td>" + productSku + "</td><td>" + productPrice + "</td><td>" + productSaleprice + "</td><td><input type='number' size='80' id='discounted_price' name='discounted_price["+productId+"]' value='' /></td><td><button id=" + productId + " class='btnDelete'><i class='fa fa-close'></i></button></td></tr>";
            $("#product_details").append(productHtml);
        });
    });
})(jQuery);