var config = {
    map: {
        '*': {
            'mirasvit/rewards/onestepcheckout/securecheckout': 'Mirasvit_Rewards/js/onestepcheckout/securecheckout',
            'Mirasvit_Rewards/js/social': 'Mirasvit_Rewards/js/social',
            'Mirasvit_Rewards/js/product/view': 'Mirasvit_Rewards/js/product/view',
            'pageCache': 'Mirasvit_Rewards/js/pagecache'
        }
    },
    config: {
        mixins: {
            'Magecomp_Paymentfee/js/action/select-payment-method': {
                'Mirasvit_Rewards/js/checkout/override/magecomp_paymentfee/select-payment-method-mixin': true
            },
            'Magento_Checkout/js/action/select-payment-method': {
                'Mirasvit_Rewards/js/checkout/override/select-payment-method-mixin': true
            }
        }
    }
};
