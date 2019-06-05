/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote'
    ],
    function (ko, Component, quote) {
        return Component.extend({
            totals: quote.getTotals(),
            isDisplayed: function () {
                return this.getValue() != 0;
            },
            getValue: function () {
                for (var i in this.totals().total_segments) {
                    var total = this.totals().total_segments[i];
                    /** @todo fix it */
                    if (total.code == 'rewards-spend-amount' || total.code == 'rewards') {
                        if (total.value != 0) {
                            return this.getFormattedPrice(total.value);
                        }
                    }
                }
        
                return 0;
            }
        });
    }
);