require([
        "jquery",
        "mage/mage"
    ], function($) {
        window.FORM_KEY = $('input[name="form_key"]').val();

        $().ready(function() {
            if (jQuery('.g-plusone').length) {
                (function () {
                    var po = document.createElement('script');
                    po.type = 'text/javascript';
                    po.async = true;
                    po.src = 'https://apis.google.com/js/platform.js';
                    var s = document.getElementsByTagName('script')[0];
                    s.parentNode.insertBefore(po, s);
                })();
            }

            if (typeof rewardsCurrentTwiiterUrl != 'undefined') {
                var text = rewardsDefaultTwitterText + ' ' + rewardsCurrentTwiiterUrl;
                $('.mst-rewardssocial-tweet').attr('data-text', text);
            }

            if ($('.twitter-share-button.mst-rewardssocial-tweet').length) {
                $("body").on("click", "a.twitter-share-button.mst-rewardssocial-tweet", function(e) {
                    e.preventDefault();
                    tweet();
                    window.location = $(this).attr('href');
                });
            }
            $('div.column.main, div.page-wrapper').on('contentUpdated', function () {
                if (typeof rewardsCurrentTwiiterUrl != 'undefined') {
                    var text = rewardsDefaultTwitterText + ' ' + rewardsCurrentTwiiterUrl;
                    $('.mst-rewardssocial-tweet').attr('data-text', text);
                }
                if (typeof window.twttr != 'undefined' && typeof window.twttr.widgets != 'undefined') {
                    window.twttr.widgets.load();
                }
                if ($('.buttons-facebook-like').length && typeof FB == 'undefined') {
                    // facebook
                    (function(d, s, id) {
                        if (d.getElementById(id)) { // reinit FB on Magento cache load
                            d.getElementById(id).remove();
                            delete FB;
                        }
                        var js, fjs = d.getElementsByTagName(s)[0];
                        if (d.getElementById(id)) {return;}
                        js = d.createElement(s); js.id = id;
                        js.src = "//connect.facebook.net/" + fbLocaleCode + "/all.js#xfbml=1&appId=" + fbAppId +
                            "&version=" + rewardsFacebookApiVersion;
                        fjs.parentNode.insertBefore(js, fjs);
                    }(document, 'script', 'facebook-jssdk'));
                }
                if (typeof gapi != 'undefined') {
                    gapi.plusone.go('.buttons-googleplus-one');
                }
            });
            if ($('.buttons-twitter-like').length || $('.twitter-share-button').length) {
                window.twttr = (function (d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0],
                        t = window.twttr || {};
                    if (d.getElementById(id)) {
                        return t;
                    }
                    js = d.createElement(s);
                    js.id = id;
                    js.async = 1;
                    js.src = "https://platform.twitter.com/widgets.js";
                    fjs.parentNode.insertBefore(js, fjs);

                    t._e = [];
                    t.ready = function (f) {
                        t._e.push(f);
                    };

                    return t;
                }(document, "script", "twitter-wjs"));

                if (typeof window.twttr.ready != "undefined") {
                    twttr.ready(function (twttr) {
                        twttr.events.bind('tweet', function (a) {
                            if (!a) {
                                return;
                            }
                            if ($(a.target).parents('.rewardssocial-buttons').length) {
                                tweet();
                            }
                        });
                    });
                }
            }

            // google+
            //!function (d, s, id) {
            //    var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
            //    if (!d.getElementById(id)) {
            //        js = d.createElement(s);
            //        js.id = id;
            //        js.src = p + '://apis.google.com/js/platform.js';
            //        fjs.parentNode.insertBefore(js, fjs);
            //    }
            //}(document, 'script', 'gplus-script');

            if (typeof rewardsOneUrl != 'undefined') {
                class RewardsGoogleplusOne {
                    constructor(url) {
                        this.url = url;
                    }
                    onPlus() {
                        var self = this;
                        $.ajax({
                            url: this.url,
                            type: 'GET',
                            dataType: 'JSON',
                            complete: function (data) {
                                self.onSuccess(data);
                            }
                        });
                        return this;
                    }
                    onUnPlus() {
                        return this;
                    }
                    onSuccess(response) {
                        $('#status-message').html(response.responseText);
                        $('#googleplus-message').html('');
                        return this;
                    }
                };

                var gp = new RewardsGoogleplusOne(rewardsOneUrl + '?url=' + rewardsCurrentUrl);
                rewardsGoogleplusEvent = function (event) {
                    if (event.state == 'on') {
                        gp.onPlus()
                    } else if (event.state == 'off') {
                        gp.onUnPlus()
                    }
                };
                $("body").on("click", ".buttons-googleplus-one .plus-googleplus-one", function() {
                    gp.onPlus();
                });
            }

            // pinterest
            $("body").on("click", "#buttons-pinterest-pin a", pinIt);
            $("body").on("click", "#rewards_fb_share", function(e) {
                FB.ui({
                    method: 'share',
                    display: 'popup',
                    href: rewardsShareCurrentUrl,
                }, function(response){
                    if (typeof response !== 'undefined') {
                        fbShare();
                    }
                });
            });
        });
    }
);

window.fbAsyncInit = function() {
    FB.Event.subscribe('xfbml.render', function(b) {
        FB.Event.subscribe('edge.create', fbLike);
        FB.Event.subscribe('edge.remove', fbUnlike);
    });
};

var addThisTimerCounter = 0;
var addThisTimer = setInterval( function() {
    addThisTimerCounter++;
    if ( typeof addthis !== 'undefined' ) {
        clearInterval( addThisTimer );
        addthis.addEventListener('addthis.menu.share', addthisShare);
    } else if (addThisTimerCounter > 40) { // wait 4sec
        clearInterval( addThisTimer );
    }
}, 100 );

function fbShare() {
    jQuery.ajax({
        url: window.fbShareUrl + '?url=' + rewardsCurrentUrl,
        type: 'POST',
        dataType: 'JSON',
        complete: function (data) {
            jQuery('#status-message').html(data.responseText);
            jQuery('#facebook-share-message').html('');
        }
    });
}

function fbLike() {
    jQuery.ajax({
        url: window.fbLikeUrl + '?url=' + rewardsCurrentUrl,
        type: 'POST',
        dataType: 'JSON',
        complete: function (data) {
            jQuery('#status-message').html(data.responseText);
            jQuery('#facebook-message').html('');
        }
    });
}

function fbUnlike() {
    jQuery.ajax({
        url: window.fbUnlikeUrl + '?url=' + rewardsCurrentUrl,
        type: 'POST',
        dataType: 'JSON',
        complete: function (data) {
            jQuery('#status-message').html(data.responseText);
            jQuery('#facebook-message').html('');
        }
    });
}

function tweet() {
    jQuery.ajax({
        url: window.rewardsTwitterUrl + '?url=' + rewardsCurrentUrl,
        type: 'POST',
        dataType: 'JSON',
        complete: function (data) {
            jQuery('#status-message').html(data.responseText);
            jQuery('#twitter-message').html('');
        }
    });
}

function googlePlus() {
    jQuery.ajax({
        url: window.rewardsOneUrl + '?url=' + rewardsCurrentUrl,
        type: 'POST',
        dataType: 'JSON',
        complete: function (data) {
            jQuery('#status-message').html(data.responseText);
            jQuery('#googleplus-message').html('');
        }
    });
}

function pinIt() {
    jQuery.ajax({
        url: window.rewardsPinUrl + '?url=' + rewardsCurrentUrl,
        type: 'POST',
        dataType: 'JSON',
        complete: function (data) {
            jQuery('#status-message').html(data.responseText);
            jQuery('#pinterest-message').html('');
        }
    });
}

function addthisShare(e) {
    if (e.type == 'addthis.menu.share') {
        switch (e.data.service) {
            case "facebook":
                fbLike();
                break;
            case "twitter":
                tweet();
                break;
            case "google_plusone_share":
                googlePlus();
                break;
            case "pinterest_share":
                pinIt();
                break;
        }
    }
}

