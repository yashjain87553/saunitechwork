/**
 * @author Nir Goldman
 * @package Gold_NewsletterPopup
 */

window.onload = function(e){
    checkCookie();
  };
  function checkCookie()
  {
    var myCookie = getCookie("visit");
    if (myCookie == null) {
        WriteCookie();
		pop('popup_newsltr');
    }
    else {
        document.getElementById("popup_newsltr").style.display="none";
    }
  }



  function WriteCookie()
  {
     cookievalue="set";
     document.cookie="visit=" + cookievalue;

  }
  function getCookie(name) {
    var dc = document.cookie;
    var prefix = name + "=";
    var begin = dc.indexOf("; " + prefix);
    if (begin == -1) {
        begin = dc.indexOf(prefix);
        if (begin != 0) return null;
    }
    else
    {
        begin += 2;
        var end = document.cookie.indexOf(";", begin);
        if (end == -1) {
        end = dc.length;
        }
    }
    return unescape(dc.substring(begin + prefix.length, end));
}



function pop(popup_newsltr) {
        document.getElementById(popup_newsltr).style.display = 'block';
}
function hide(popup_newsltr) {
        document.getElementById(popup_newsltr).style.display = 'none';
}
//To detect escape button
document.onkeydown = function(evt) {
        evt = evt || window.event;
        if (evt.keyCode == 27) {
                hide('popup_newsltr');
        }
};
