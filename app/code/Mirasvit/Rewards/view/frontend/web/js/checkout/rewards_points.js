define(
    [
        'Mirasvit_Rewards/js/checkout/cart/rewards_points'
    ],
    function(
        cartRewardsPoints
    ) {
        'use strict';

        if (typeof cartRewardsPoints == 'undefined') {
            return null;
        }

        var template = 'Mirasvit_Rewards/checkout/rewards/checkout/usepoints';
        if (!cartRewardsPoints().isShowRewards() && cartRewardsPoints().rewardsCheckoutNotification) {
            template = 'Mirasvit_Rewards/checkout/rewards/checkout/notification';
        }

        return cartRewardsPoints.extend({
            defaults: {
                template: template
            }
        });
    }
);