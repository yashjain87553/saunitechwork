define([
    "jquery",
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    "jquery/ui",
    'mage/mage',
    'Magento_Catalog/product/view/validation',
], function($ , modal, alert,$t) {
    "use strict";

    var check = true;
    $.widget('magenest.addToCart', {
        options: {
            successMessage: '',
            errorMessage: '',
            cartUrl: 'checkout/cart/index',
            addUrl :'giftregistrys/add',
            formKey:''
        },

        _create: function () {
            this._bind();
        },

        _bind : function() {
            var  self = this;
            $('.close').click(function () {
                $('#popupAddress').hide();
                $('#popupLogin').hide();
                $('#popupQty').hide();
                $('#popupQtyMin').hide();
            });
            $('#popupLogin').click(function () {
                $(this).hide();
            });
            $('#popupAddress').click(function () {
                $(this).hide();
            });
            $('#popupQty').click(function () {
                $(this).hide();
            });

            $('.add-to-cart').click(function() {
                if($(this).data('hasaddress') == 0) {
                    alert({
                        content: $('#popupAddress').html()
                    });

                } else {
                    // if ($(this).data('hasaddress') == -1) {
                    //     alert({
                    //         content: $('#popupLogin').html()
                    //     });
                    // } else {
                        var id = $(this).data('item');
                        var qty_max = $(this).data('qty-max');
                        var qty = $(this).next('input[name="qty"]').val();
                        if(parseInt(qty) != qty){
                            alert({
                                content: $t("Quantity Must Be An Integer!")
                            });
                            return;
                        }
                        if(parseInt(qty) && parseInt(qty)>0){
                            if (qty > qty_max) {
                                alert({
                                    content: $t("We don't have enough product stock as you requested!")
                                });
                            } else {
                                $('#popupQty').hide();
                                var addUrl = self.options.addUrl;

                                var formKey = self.options.formKey;

                                if (check == true)
                                {
                                    jQuery('body').loader('show');
                                    $.post(addUrl, {item: id, qty:qty , formKey :formKey}).done(function(data) {
                                        // window.location = self.options.cartUrl;
                                        jQuery('body').loader('hide');
                                    });

                                    // check = false;
                                }
                            }
                        } else {
                            alert({
                                content: $t("Please enter a number greater than or equal to 1 in Qty field!")
                            });
                        }
                    // }
                }
            });
        }
    });

    return $.magenest.addToCart;
});