define(
    [
        'jquery',
        'Magento_PageCache/js/page-cache'
    ],
    function($){
        $.widget('rewards.pageCacheFix', $.mage.pageCache, {
            _create: function () {
                try {
                    this._super();
                } catch (e) {
                    console.log(e);
                }
            },
        });

        return $.rewards.pageCacheFix;
    }
);