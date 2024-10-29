/*
Align Text Edge 0.9.4
Copyright 2017, saimeishi (http://saimeishi.wpblog.jp/)
Released under the GPLv2 or later
align-text-edge.min.js powerd by YUI Compressor.
 $ java -jar yuicompressor-2.4.7.jar align-text-edge.js -o align-text-edge.min.js --charset utf-8
*/
(function($){
	var $ateCls = document.getElementsByClassName('align_text_edge');
	var $ateDescCls = document.getElementsByClassName('align_text_edge_desc');

	if($ateCls.length != $ateDescCls.length){
		return;
	}
	
	for(var i=0; i<$ateCls.length; i++){
		var $tagImg = $ateCls[i].getElementsByTagName('img');
		var $tagSpan;
		if(0 < $tagImg.length){
			//Get and set image width.
			var $imgWidth = 0;
			var $sliceStart = $tagImg[0].src.lastIndexOf("?w=");
			if(0 <= $sliceStart){
				$imgWidth = parseInt($tagImg[0].src.slice($sliceStart+3), 10);
			}
			if(0 < $imgWidth){
				$tagImg[0].width = $imgWidth;//No nees suffix 'px'.
			}
			if($imgWidth <= 0){
				$imgWidth = parseInt($tagImg[0].width, 10);
			}
			//alert("imgWidth=" + $imgWidth + " type=" + typeof($imgWidth));

			$tagSpan = $ateCls[i].getElementsByTagName('span');
			if(0 < $imgWidth && 0 < $tagSpan.length){
				var $rightOffset = $tagSpan[0].getAttribute('right_offset');
				var $addStyle = $tagSpan[0].getAttribute('add_style');

				var $measureEle = document.getElementById('measure_heading_text_width');
				var $measureStyle = 'visibility:hidden;position:absolute;white-space:nowrap;' + $addStyle;
				if($measureEle != null){
					$measureEle.setAttribute('style', $measureStyle);
				} else {
					//Use jQuery 1
					$(document.body).append($('<span id="measure_heading_text_width" style="' + $measureStyle + '"></span>'));
				}
				//Use jQuery 2
				var $e = $("#measure_heading_text_width");
				var $headingWidth = $e.text($tagSpan[0].innerHTML).get(0).offsetWidth;
				$e.empty();

				$tagSpan[0].style.left = String($imgWidth - $headingWidth - $rightOffset) + 'px';
				$tagSpan[0].setAttribute('style', $tagSpan[0].getAttribute('style') + $addStyle);
				//alert('Heading text=' + $tagSpan[0].innerHTML);
			}
		}

		$tagSpan = $ateDescCls[i].getElementsByTagName('span');
		if(0 < $tagSpan.length){
			//get margin-top is $tagSpan[0].style.marginTop;
			//set margin-top is $tagSpan[0].style.marginTop = '-25px';

			var $mrgTop = $tagSpan[0].style.marginTop;
			var $mrgBottom = $tagSpan[0].style.marginBottpm;

			if($mrgTop && 0 < $mrgTop.length){
				$ateDescCls[i].style.marginTop = $mrgTop;
			}
			if($mrgBottom && 0 < $mrgBottom.length){
				$ateDescCls[i].style.marginBottom = $mrgBottom;
			}
		}
	}
})(jQuery);
