/*
Align Text Edge 0.9.4
Copyright 2017, saimeishi (http://saimeishi.wpblog.jp/)
Released under the GPLv2 or later
align-text-edge-admin.min.js powerd by YUI Compressor.
 $ java -jar yuicompressor-2.4.7.jar align-text-edge-admin.js -o align-text-edge-admin.min.js --charset utf-8
*/
function invoke_event($obj){
	var $action = $obj.getAttribute('action');
	var $targetArray = $obj.getAttribute('target').split(',');
	var $objId = $obj.getAttribute('id');

	for(var $i=0; $i<$targetArray.length; $i++) {
		$targetId = $objId.replace(new RegExp('(.*)_.*$'), '$1' + '_' + $targetArray[$i]);

		switch($targetArray[$i]){
			case 'textbox':
				switch($action){
					case 'set':
						$targetElement = document.getElementById($targetId);
						$targetElement.value = $obj.getAttribute('set_value');
						break;
					case 'disabled_sync':
						$targetElement = document.getElementById($targetId);
						(function($){
							if($($obj).prop('checked')){
								$($targetElement).prop('disabled', false);
							} else {
								$($targetElement).prop('disabled', true);
							}
						})(jQuery);
						break;
				}
				break;
			case 'button':
				switch($action){
					case 'disabled_sync':
						$targetElement = document.getElementById($targetId);
						(function($){
							if($($obj).prop('checked')){
								$($targetElement).prop('disabled', false);
							} else {
								$($targetElement).prop('disabled', true);
							}
						})(jQuery);
						break;
				}
				break;
			case 'checkbox':
				switch($action){
					case 'set':
						$targetElement = document.getElementById($targetId);
						(function($){
							if($obj.getAttribute('set_value') !== 'checked'){
								$($targetElement).prop('checked', false);
							} else {
								$($targetElement).prop('checked', true);
							}
						})(jQuery);
						break;
				}
				break;
		}
		//alert($targetId);
		//console.log($targetArray[$i]);
	}

};





