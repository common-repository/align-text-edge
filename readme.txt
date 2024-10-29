=== Align Text Edge ===
Contributors: saimeishi
Donate link: 
Tags: align text, float, image, shortcode
Requires at least: 4.7.3
Tested up to: 4.8.0
Stable tag: 4.8.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Align heading text and description by transparent image.

== Description ==

This plugin allows you to align heading text and description use by float attribute of style:

= Arguments =

* uwidth: Background image width. Default value is "20px". You can set (1|2|3|4|5|6|7|8|9|10|11|12|13|14|15|16|17|18|19|20|30|40|50|60|70|80|90|100)px.
* ucnt: Unit count value. Default value is "1". Indent width = (uwidth) x (ucnt).
* htxt: Heading text. Default value is empty.
* roffset: Heading text right offset. Default value is "0px".
* class: Class specification for characters of heading text. If setting more than two, write separate with space.
* style: Style specification for characters of heading text. You can write CSS style format.
         ex.)"color:#1581ed;font-size:1.0em;font-family:Impact,Charcoal;font-weight:bold;"
* desc_class: Class specification for characters of heading description. If setting more than two, write separate with space.
              If set 'same' value or not set value, use CSS class of heading text.
              Set 'none' value, use CSS class of inherit.
* desc_style: Style specification for characters of heading description. You can write CSS style format.
              If set 'same' value or not set value, use CSS style format of heading text.
              Set 'none' value, use CSS style format of inherit.

= Contributors =

* [saimeishi](http://saimeishi.wpblog.jp)

== Installation ==

Download the zip, extract it and upload the extracted folder to your-wp-directory/wp-content/plugins/.
[Dashboard]>[Plugin] and activate this plugin.

=Usage=

------ exsample 1. ------
[ate ucnt='1' htxt='1.' style='font-size:1.5em']Something heading description.[/ate][ate ucnt='1']Something contents.[/ate]
[ate ucnt='2' htxt='2.' style='font-size:1.3em' desc_style='none']Something heading description.[/ate][ate ucnt='2']Something contents.[/ate]
[ate ucnt='3' htxt='3.' font-size='0.8em' desc_style='font-size:1.4em']Something heading description.[/ate][ate ucnt='3']Something contents.[/ate]

------ exsample 2.  note:<span></span> is insert blank line. I think that this is necessary because wpautop is enabled. ------
[ate ucnt='1' htxt='1.' style='font-size:1.3em' desc_style='none']Something heading description.[/ate][ate ucnt='1']Something contents. Something contents. Something contents. Something contents. Something contents. Something contents. Something contents. Something contents. Something contents. Something contents. Something contents. Something contents. Something contents. Something contents. Something contents. Something contents. Something contents.[/ate]
[ate ucnt='2' htxt='2.' style='font-size:1.5em']Something heading description.[/ate][ate ucnt='2']Something descr<span style='color:#b50356;font-size:1.4em'>ipt</span>ion.
Some
thing
description.
[/ate]
[ate ucnt='3' htxt='3.' style='font-size:0.8em' desc_style='font-size:1.4em']Something heading description.[/ate][ate ucnt='3']Something contents.[/ate]
<span></span>
[ate uwidth='10' ucnt='5' htxt='a.' roffset='2px' style='color:#1c6dbf;font-size:1.0em']Something 
heading description.[/ate]
[ate ucnt='3' htxt='i.' style='color:#1581ed;font-size:1.2em;font-family:fantasy' desc_style='same']Something heading description.[/ate]
[ate uwidth='40' ucnt='3' htxt='1.' roffset='15px' style='color:#11af95;font-size:1.1em' desc_style='color:#180672;font-size:1.3em']Something heading description.[/ate]

== Frequently asked questions ==



== Screenshots ==

1. exsample_1_display.png
2. exsample_1_display_all_select.png
3. exsample_2_display.png
4. exsample_2_display_all_select.png

== Changelog ==

= 0.9.4 =
* Bug fix. Delete suffix(px) of width value in img tag.
* Support of img tag replaced by WordPress auto editer.
* Support width unit 1,3,5,7,9,11,13,15,17,19,50,60,70,90,100 pixel.

= 0.9.3 =
* Bug fix, when first input of desc_class and desc_style.
* Support margin-top, margin-bottom of heading description.

= 0.9.2 =
* Support width unit 2,4,6,8,12,14,16,18,80 pixel.

= 0.9.1 =
* Bug fix. Fixed omission of cast to int when headingRightOffset set default value.

= 0.9.0 =
* Support 'class setting' at heading text and description.

= 0.8.0 =
* Add admin screen.

= 0.7.0 =
* Support 'style setting' at heading text and description.

= 0.6.0 =
* Support width unit 10,20,30,40 pixel.

= 0.5.0 =
* First release.

== Credits ==

This plugin can be used by WordPress users for free. However, the author does not guarantee and support its behavior.

