/*
Align Text Edge 0.9.4
Copyright 2017, saimeishi (http://saimeishi.wpblog.jp/)
Released under the GPLv2 or later
align-text-edge-admin.min.js powerd by YUI Compressor.
 $ java -jar yuicompressor-2.4.7.jar align-text-edge-admin.js -o align-text-edge-admin.min.js --charset utf-8
*/
function invoke_event(e){var d=e.getAttribute("action");var b=e.getAttribute("target").split(",");var a=e.getAttribute("id");for(var c=0;c<b.length;c++){$targetId=a.replace(new RegExp("(.*)_.*$"),"$1_"+b[c]);switch(b[c]){case"textbox":switch(d){case"set":$targetElement=document.getElementById($targetId);$targetElement.value=e.getAttribute("set_value");break;case"disabled_sync":$targetElement=document.getElementById($targetId);(function(f){if(f(e).prop("checked")){f($targetElement).prop("disabled",false)}else{f($targetElement).prop("disabled",true)}})(jQuery);break}break;case"button":switch(d){case"disabled_sync":$targetElement=document.getElementById($targetId);(function(f){if(f(e).prop("checked")){f($targetElement).prop("disabled",false)}else{f($targetElement).prop("disabled",true)}})(jQuery);break}break;case"checkbox":switch(d){case"set":$targetElement=document.getElementById($targetId);(function(f){if(e.getAttribute("set_value")!=="checked"){f($targetElement).prop("checked",false)}else{f($targetElement).prop("checked",true)}})(jQuery);break}break}}};