<?php
/*
Plugin Name: Align Text Edge
Plugin URI: http://saimeishi.wpblog.jp/plugins_align_text_edge
Description: Align heading text and description text by transparent image.
Version: 0.9.4
Author: saimeishi
Author URI: http://saimeishi.wpblog.jp/
License: GPLv2 or later
Text Domain: align-text-edge
*/

$align_text_edge = new Align_Text_Edge();

// Class Align_Text_Edge
class Align_Text_Edge {
	// Package name
	private $pluginName = 'Align Text Edge';

	// Version number
	private $versionNumber = '0.9.4';

	// Package name
	private $domainName = 'align-text-edge';

	// Default class name.
	private $className = 'align_text_edge';

	// Default shortcode tag name.
	private $defShortcodeTagTextbox = 'ate';
	private $shortcodeTagTextbox;

	// Default checkbox of priority of wpautop.
	private $defPriorityWpautopCheckbox = 'checked';

	// Default priority of wpautop for the_content.(from:/wp-includes/default-filters.php.)
	private $defPriorityWpautopTextbox = 11;

	// Regular expression of background image width+pixel(0 or 10 or 20 or 30 or 40 pixel). 
	// 0 is no image file.
	private $regUnitWidthPx = '/^([0-9]{1,})(|px)$/';

	// Default background image width(pixel).
	private $defUnitWidth = 20;

	// Unit count.
	// 0 is no image file.
	private $defUnitCnt = 1;

	// Default heading text right offset(pixel).
	private $defHeadingRightOffset = 0;

	// Width pixel of Default heading text(Same meaning "width of '1.' by font-size 1em").
	// This need re-measure by javascript.
	private $defHeadingWidth = 14;

	// Regular expression of number+pixel. (except value 01, 001, etc)
	private $regNumPx = '/^(0|[1-9]+[0-9]*)(|px)$/';

	// Regular expression of color. 
	//private $regColor = '/^#([0-9A-Fa-f]{2}){3,6}$/';

	// Regular expression of font-size.(xx-small,x-small,small,medium,large,x-large,xx-large)
	//private $regFontSize = '/^([0-9|\.]{1,})(|em|pt|in)$/';

	// Default heading font-size.
	//private $defHeadingFontSize = '1.0em';


	// Constructor.
	function __construct() {
		add_action('init', array($this, 'init'));
	}

	//Init function.
	public function init() {
		//Note:If you use add_shortcode(),add_action(),add_filter(), etc... in 'PHP Class section', you need second argument replace 'xxx' to 'array($this, 'xxx')'.

		// 1.Get option values.(Tag name, Enable modify wpautop setting, And that's Priority)
		$options = get_option($this->className . '_settings');
		$this->shortcodeTagTextbox = $this->checkOptionDBValue('field_shortcode_tag_textbox', $options['field_shortcode_tag_textbox'], false);
		$priorityWpautopCheckbox = $this->checkOptionDBValue('field_priority_wpautop_checkbox', $options['field_priority_wpautop_checkbox'], false);
		$priorityWpautopTextbox = $this->checkOptionDBValue('field_priority_wpautop_textbox', $options['field_priority_wpautop_textbox'], false);

		// 2.Reset 'wpautop priority'.
		// wpautop is function of automatic text formatting.
		// If done remove_filter, as soon as need do add_filter. On the otherwise, layout of all page and post are might broken.
		// Now priority change to 11. This means that wpautop will do after align-text-edge.js.
		// ex.)Carriage return change to <br>.
		//     Inline tag or sentence adapt to <p>.
		//     If carriage return continuous twice, they are adapt to <p>. 
		// The reason for value 11 of add_filter function last argument is from one line of /wp-includes/default-filters.php.
		// Re-set priority value of wpautop to 11. Therefore wpautop do after do_shortcode.
		//     #---- default-filters.php quote begin ----#
		//     // Shortcodes
		//     add_filter( 'the_content', 'do_shortcode', 11 ); // AFTER wpautop()
		//     #---- default-filters.php quote end   ----#
		if($priorityWpautopCheckbox === 'checked' && has_filter('the_content', 'wpautop')){
			remove_filter('the_content', 'wpautop');
			add_filter('the_content', 'wpautop', intval($priorityWpautopTextbox));
		}
		// wptexturize is function of special character change., like double quote(") and right single quote(') change to entity reference.
		// This function no need in here. I will write it here for a memorandum.
		// ex.)Double quote(")       -> &quot;
		//     Right Single quote(') -> &rsquo;
		//     Ampersand(&)          ->  &amp;
		//remove_filter('the_content', 'wptexturize');

		// 3.shortcode definition. 
		add_shortcode($this->get_shortcode_tag(), array($this, 'shortcode_definition'));
		// Priority not set at add_action. If set some value, not working JavaScript.
		add_action('wp_footer', array(&$this, 'wp_enqueue_shortcode_scripts'));

		// 4.Load 'translation file'. 
		add_action('admin_init', array($this, 'load_textdomain'));

		// 5.Make Admin page.
		if(is_admin()){
			add_action('admin_menu', array($this, 'admin_page_abstract'));
			wp_enqueue_script($this->className . '_admin', plugins_url('js/align-text-edge-admin.min.js', __FILE__), array('jquery'), $this->versionNumber);
			add_action('admin_init', array($this, 'admin_page_parts'));
			add_filter('plugin_action_links_'.plugin_basename(__FILE__), array($this, 'add_link_at_plugins_list'), 10, 2);
		}
	}

	/* Enqueue scripts of shortcode.
	*/
	public function wp_enqueue_shortcode_scripts() {
		wp_register_script(
			$this->className,
			apply_filters($this->className . '_script', plugins_url('js/align-text-edge.min.js', __FILE__)),
			array('jquery'),
			$this->versionNumber, //filemtime(dirname(__FILE__).'/js/align-text-edge.min.js'), //->  Use file update time unix time by filemtime function for version number.
			true
		);

		wp_enqueue_script($this->className);
	}

	/*
	* @param array $p uwidth,ucnt,htxt,roffset,style,desc_style,
	* @param null  $content Content.
	* @return string
	*/
	public function shortcode_definition($p, $content = null) {
		if(isset($p['uwidth']) && preg_match($this->regUnitWidthPx, $p['uwidth'])){
			$unitWidth = intval($this->get_number_pixel($this->regUnitWidthPx, $p['uwidth'], $this->defUnitWidth));
		} else {
			$unitWidth = intval(apply_filters($this->className . '_unit_width', $this->defUnitWidth));
		}

		if(isset($p['ucnt']) && preg_match($this->regNumPx, $p['ucnt'])){
			$unitCnt = intval($this->get_number_pixel($this->regNumPx, $p['ucnt'], $this->defUnitCnt));
		} else {
			$unitCnt = intval(apply_filters($this->className . '_unit_count', $this->defUnitCnt));
		}
		$indentWidth = $unitWidth * $unitCnt;

		// Default of $bgImgPath is 'images/transparent/transparent_20x1.png'
		if(0 < $indentWidth){
			$bgImgPath = 'images/transparent/transparent_' . strval($unitWidth) . 'x1.png';
			$bgImgPath = plugins_url($bgImgPath, __FILE__);
		} else {
			$bgImgPath = '';
		}
		//var_dump($unitWidth);
		//var_dump($unitCnt);

		if(isset($p['htxt'])){
			$headingText = $p['htxt'];
		} else {
			$headingText = '';
		}
		//var_dump($headingText);

		if(isset($p['roffset']) && preg_match($this->regNumPx, $p['roffset'])){
			$headingRightOffset = intval($this->get_number_pixel($this->regNumPx, $p['roffset'], 0));
		} else {
			$headingRightOffset = intval(apply_filters('default_heading_right_offset', $this->defHeadingRightOffset));
		}
		$headingLeft = $indentWidth - $headingRightOffset - $this->defHeadingWidth;

		if(!isset($p['class']) || strlen($p['class']) <= 0){
			$p['class'] = '';
		} else {
			//More than two spaces replace to one space.
			$p['class'] = mb_ereg_replace(' {2,}',' ',$p['class']);
			$p['class'] = trim($p['class']);
		}
		//var_dump($p['class']);

		if(!isset($p['style']) || strlen($p['style']) <= 0){
			$p['style'] = '';
		} else {
			//Delete string of '; ' or ';  ' or ';   ' ... If had one or more space, not work at JavaScript.
			$p['style'] = mb_ereg_replace('; {1,}',';',$p['style']);
			$p['style'] = trim($p['style']);
		}
		//var_dump($p['style']);

		if(!isset($p['desc_class']) || strlen($p['desc_class']) <= 0 || $p['desc_class'] === 'same'){
			$p['desc_class'] = $p['class'];
		} else {
			if($p['desc_class'] !== 'none'){
				$p['desc_class'] = mb_ereg_replace(' {2,}',' ',$p['desc_class']);
				$p['desc_class'] = trim($p['desc_class']);	
			} else {
				$p['desc_class'] = '';
			}
		}
		//var_dump($p['desc_class']);

		if(!isset($p['desc_style']) || strlen($p['desc_style']) <= 0 || $p['desc_style'] === 'same'){
			$p['desc_style'] = $p['style'];
		} else {
			if($p['desc_style'] !== 'none'){
				$p['desc_style'] = mb_ereg_replace('; {1,}',';',$p['desc_style']);
				$p['desc_style'] = trim($p['desc_style']);	
			} else {
				$p['desc_style'] = '';
			}
		}
		//var_dump($p['desc_style']);

		// Function esc_html and esc_attr is same code in /wp-includes/formatting.php.
		// They function are similiar to wptexturize. Please refer to upper note of wptexturize.
		// If you want to do html tag behind the shortcode, you not adapt esc_html and esc_attr at variable.

		return sprintf('<div class="%1$s" style="margin:0px;padding:0px;position:relative">
<img src="%2$s" alt="" class="alignleft" width="%3$s" style="margin:0px;padding:0px;border-style:none" /><span right_offset="%4$s" class="%5$s" style="margin:0px;padding:0px;position:absolute;top:0px;left:%6$s" add_style="%7$s">%8$s</span></div>
<div class="%1$s_desc" style="margin:0px;padding:0px;overflow:auto"><span class="%9$s" style="%10$s">%11$s</span></div><div style="margin:0px;padding:0px;clear:both"></div>',
			esc_attr(apply_filters($this->className . '_class_name', $this->className)),
			esc_attr($bgImgPath),
			esc_attr($indentWidth),
			esc_attr($headingRightOffset),
			esc_attr($p['class']),
			esc_attr($headingLeft . 'px'),
			esc_attr($p['style']),
			$headingText,
			esc_attr($p['desc_class']),
			esc_attr($p['desc_style']),
			$content
		);
	}

	// Return number except px.
	private function get_number_pixel($regExp, $target, $defRet) {
		$ret = $defRet;
		if(preg_match($regExp, $target, $matches)) {
			if(1 < count($matches)){
				$ret = $matches[1];
			}
		}
		//var_dump($ret);
		return $ret;
	}

	// Return font-size except em.
	/*private function get_font_size($target) {
		$ret = $this->defHeadingFontSize;
		if(preg_match($this->regFontSize, $target, $matches)) {
			if(1 < count($matches) && 0 < floatval($matches[1])){
				$ret = $matches[1] . "em";
			}
		}
		//var_dump($ret);
		return $ret;
	}*/

	// Get shortcode tag.
	private function get_shortcode_tag() {
		return apply_filters($this->className . '_shortcode_tag', $this->shortcodeTagTextbox);
	}

	// Make abstract of 'Admin page'.
	public function admin_page_abstract() {
		// Add item at 'Admin root' screen.
		//add_menu_page(
		//	$page_title,　// Title tag of displayed page at [Admin root > Settings > This plugin page].
		//	$menu_title, // Text of page from [Admin root > Settings].
		//	$capability, // Access authority('manage_options' or 'administrator').
		//	$menu_slug,  // Slug of menu.
		//	$function,   // The function to be called to output the content for this page.
		//	$icon_url,   // Icon of menu
		//	$position    // Position of menu(1～99. Detail is reffer to manual).
		//);

		// Add item at 'Admin root > Settings' screen.
		return $hook = add_options_page(
			$this->pluginName,                // Title tag of displayed page at [Admin root > Settings > This plugin page].
			$this->pluginName,                // Text of displayed at [Admin root > Settings].
			'manage_options',                 // Access authority('manage_options' or 'administrator').
			$this->className,                 // Slug of menu.
			array($this, 'admin_page_html') // The function to be called to output the content for this page.
		);
	}

	/* Make parts of 'Admin page'.
	*/
	public function admin_page_parts() {
		// Entry of setting( and check input value).
		register_setting(
			$this->className,               // Group name of setting.
			$this->className . '_settings', // Name of DB.
			array($this, 'data_sanitize')   // Function to be called when adjust input value.
		);

		// Add section of input item.
		add_settings_section(
			$this->className . '_settings_section',                         // Section ID.
			esc_html__($this->pluginName . ' Settings', $this->domainName), // Section title.
			array($this, $this->className . '_settings_section_callback'),  // The function to be called to output introduce of section.
			$this->className                                                // Slug of setting page(To be same name which $menu_slug of add_menu_page()).                                               
		);

		// Add textbox, button for shortcode_tag.
		add_settings_field(
			'field_shortcode_tag_textbox',                      // Field ID.
			esc_html__('Set shortcode tag', $this->domainName), // Field title.
                   array($this, 'field_render_textbox'),               // The function of output html of input item.
			$this->className,                                   // Slug of setting page(To be same name which $menu_slug of add_menu_page() or add_options_page()).
			$this->className . '_settings_section',             // Section ID(To be same name which $id of add_settings_section()).
                   array('checkboxFieldID' => '',                      // Add argument of $callback(If not necessary, omit it).
				'checkboxSize' => '',
				'target'  => '',
				'action'  => '',
				'fieldID' => 'field_shortcode_tag_textbox',
				'baseID'  => 'shortcode_tag',
				'default' => $this->defShortcodeTagTextbox,
				'size'    => 20,
				'note'    => esc_html__('Note:More than 2 letters. Cannot use / and multibyte. Even ', $this->domainName) . ' äoß, o"o, ❤m' . esc_html__(' are OK.', $this->domainName))
		);

		// Add textbox, button for priority_wpautop.
		add_settings_field(
			'field_priority_wpautop_textbox',
			esc_html__('Set priority of wpautop', $this->domainName),
			array($this, 'field_render_textbox'),
			$this->className,
			$this->className . '_settings_section',
			array('checkboxFieldID' => 'field_priority_wpautop_checkbox',
				'checkboxSize' => '10',
				'target'  => 'textbox,button',
				'action'  => 'disabled_sync',
				'fieldID' => 'field_priority_wpautop_textbox',
				'baseID'  => 'priority_wpautop',
				'default' => $this->defPriorityWpautopTextbox,
				'size'    => 10,
				'note'    => esc_html__('Note:If no use wpautop, set unchecked in checkbox.', $this->domainName))
		);
	}

	/* Sanitize field value.(Check by regular expression)
	*/
	public function data_sanitize($input) {
		$newInput = array();
		
		foreach($input as $key => $value){
			$newInput[$key] = $this->checkOptionDBValue($key, $value, true);
		}

		// For save test.
		//$newInput['field_shortcode_tag_textbox'] = $input['field_shortcode_tag_textbox'];

		return $newInput;
	}

	/* Add description of Post Notifier.(Display always.)
	*/
	public function align_text_edge_settings_section_callback() {
		echo esc_html__('Set your own values.', $this->domainName);
	}

	/* Field render textbox.
	*/
	public function field_render_textbox($args) {
		$options = get_option($this->className . '_settings');
		if($args['checkboxFieldID'] !== ''){
			$fieldValue = $this->checkOptionDBValue($args['checkboxFieldID'], $options[$args['checkboxFieldID']], false);
			//type="hidden" is always unchecked. And type="checkbox" is checked or unchecked.
			if($fieldValue == 'checked'){
				$checkStatus = "checked='checked'";
				$childDisabledStatus = "";
			} else {
				$checkStatus = "";
				$childDisabledStatus = 'disabled="disabled"';
			}
			
			$checkboxOnOff = sprintf('<input id="%2$s_hiddencheckbox" type="hidden" name="%1$s_settings[%3$s]"><input id="%2$s_checkbox" type="checkbox" name="%1$s_settings[%3$s]" %4$s size="%5$s" onclick="invoke_event(this)" target="%6$s" action="%7$s">',
						$this->className,
						$args['baseID'],
						$args['checkboxFieldID'],
						$checkStatus,
						$args['checkboxSize'],
						$args['target'],
						$args['action']);
		} else {
			$checkboxOnOff = '';
			$childDisabledStatus = "";
		}


		$fieldValue = $this->checkOptionDBValue($args['fieldID'], $options[$args['fieldID']], false);
		if($args['note'] !== ''){
			$marginLeft = 8;
			if($checkboxOnOff !== ''){
				$marginLeft += (intval($args['checkboxSize'])<<1);
			}
			$note = '</br><span style="margin-left:' . strval($marginLeft) . 'px">' . $args['note'] . '<span>';
		} else {
			$note = '';
		}

		$html = sprintf('%2$s<input id="%3$s_textbox" type="text" name="%1$s_settings[%4$s]" value="%5$s" %6$s size="%7$s" ><input id="%3$s_button" type="button" value="default" %6$s size="30" onclick="invoke_event(this)" target="textbox" action="set" set_value="%8$s">
%9$s',
					$this->className,
					$checkboxOnOff,
					$args['baseID'],
					$args['fieldID'],
					$fieldValue,
					$childDisabledStatus,
					$args['size'],
					$args['default'],
					$note);
		echo $html;
	}

	/* Load textdomain.(Loading setting of path of translation files.)
	*/
	public function load_textdomain() {
		load_plugin_textdomain(
			$this->domainName,
			false,
			plugin_basename(dirname(__FILE__)) . '/languages'
		);
	}

	/* Add link item at 'plugins list' of 'Admin root > Plugins' screen(Now add 'Settings' link only).
	*/
	public function add_link_at_plugins_list($links, $file) {
		// If add_filter first argument is change to 'plugin_action_links', you need comment out below section.
		//static $thisFile;
		//if(!$thisFile){
		//	$thisFile = plugin_basename(__FILE__);
		//}
		//if($file !== $thisFile){
		//	return $links;
		//}
		
		// Add Settings link.
		$addText = esc_html__('Settings', $this->domainName);
		$addLink = '<a href="' . menu_page_url($this->className, false) . '">' . $addText . '</a>';
		//$addLink = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=' . $this->className . '">' . $settingText . '</a>';
		array_unshift( $links, $addLink );

		// Add something link(now google).
		//$addText = esc_html__('Support', $this->domainName);
		//$addLink = '<a href="https://www.google.co.jp/">' . $addText . '</a>';
		//array_unshift($links, $addLink);
	
		return $links;
	}

	/* Output 'Admin page' html form.
	*/
	public function admin_page_html() {

		?>
		<form action='options.php' method='post'>

		<?php
			// Build on options page with admin_page_parts function. Part1.
			settings_fields($this->className);
			// Build on options page with admin_page_parts function. Part2.
			do_settings_sections($this->className);

			// Add save button.
			submit_button();

			// Get translation text.
			$textText = esc_html__('Text.', $this->domainName);
			$displayBrowserText = esc_html__('Screen display at browser.', $this->domainName);
			$displayBrowserAllSelectText = esc_html__('Screen display in all select at browser.', $this->domainName);
			$noteExsampleText = esc_html__('Note:<span></span> is insert blank line. I think that this is necessary because wpautop is enabled.', $this->domainName);

			// Usage exsample text.
			$exsample_1_text = "<br><span>[ate ucnt='1' htxt='1.' style='font-size:1.5em']Something heading text.[/ate][ate ucnt='1']Something description.[/ate]</br>[ate ucnt='2' htxt='2.' style='font-size:1.3em' desc_style='none']Something heading text.[/ate][ate ucnt='2']Something description.[/ate]</br>[ate ucnt='3' htxt='3.' font-size='0.8em' desc_style='font-size:1.4em']Something heading text.[/ate][ate ucnt='3']Something description.[/ate]</span>";
			$exsample_2_text = " *" . $noteExsampleText . "</br><span>[ate ucnt='1' htxt='1.' style='font-size:1.3em' desc_style='none']Something heading text.[/ate][ate ucnt='1']Something description. Something description. Something description. Something description. Something description. Something description. Something description. Something description. Something description. Something description. Something description. Something description. Something description. Something description. Something description. Something description. Something description.[/ate]</br>[ate ucnt='2' htxt='2.' style='font-size:1.5em']Something heading text.[/ate][ate ucnt='2']Something descr&lt;span style='color:#b50356;font-size:1.4em'&gt;ipt&lt;/span&gt;ion.</br>Some</br>thing</br>description.</br>[/ate]</br>[ate ucnt='3' htxt='3.' style='font-size:0.8em' desc_style='font-size:1.4em']Something heading text.[/ate][ate ucnt='3']Something description.[/ate]</br>&lt;span&gt;&lt;/span&gt;</br>[ate uwidth='10' ucnt='5' htxt='a.' roffset='2px' style='color:#1c6dbf;font-size:1.0em']Something </br>heading text.[/ate]</br>[ate ucnt='3' htxt='i.' style='color:#1581ed;font-size:1.2em;font-family:fantasy' desc_style='same']Something heading text.[/ate]</br>[ate uwidth='40' ucnt='3' htxt='1.' roffset='15px' style='color:#11af95;font-size:1.1em' desc_style='color:#180672;font-size:1.3em']Something heading text.[/ate]</span>";
			
			// Create html.
			$html  = '</br></br>';
			$html .= '<h2>' . esc_html__('*Usage exsample 1.*', $this->domainName) . '</h2>';
			$html .= '<ol type="1">';
			$html .= '<li>' . $textText . $exsample_1_text . '</li></br>';
			$html .= '<li>' . $displayBrowserText . '<p><img style="width:58%;" src="' . plugin_dir_url(__FILE__) . 'images/screen_shot/exsample_1_display.png"></p></li></br>';
			$html .= '<li>' . $displayBrowserAllSelectText . '<p><img style="width:58%;" src="' . plugin_dir_url(__FILE__) . 'images/screen_shot/exsample_1_display_all_select.png"></p></li></br>';
			$html .= '</ol>';
			$html .= '<h2>' . esc_html__('*Usage exsample 2.*', $this->domainName) . '</h2>';
			$html .= '<ol type="1">';
			$html .= '<li>' . $textText . $exsample_2_text . '</li></br>';
			$html .= '<li>' . $displayBrowserText . '<p><img style="width:76%;" src="' . plugin_dir_url(__FILE__) . 'images/screen_shot/exsample_2_display.png"></p></li></br>';
			$html .= '<li>' . $displayBrowserAllSelectText . '<p><img style="width:76%;" src="' . plugin_dir_url(__FILE__) . 'images/screen_shot/exsample_2_display_all_select.png"></p></li>';
			$html .= '</ol>';

			echo $html;
		?>

		</form>
		<?php

	}

	/* Check value of Database.
	*/
	public function checkOptionDBValue($key, $value, $displayError) {
		//$A = (expr1) ? (expr2) : (expr3); //When expr1 is true, set A to expr2. When expr1 is false, set A to expr3. 
		$newValue = '';
		switch($key) {
			case 'field_shortcode_tag_textbox':
				//More than 2 letters and exclude / and multibyte characters. But .-o, ]b, äoß, o"o, o\'o, ❤m are OK.
				//Useable letters of shortcode tag name is except '/' and multi byte letters. More than 2 letters.
				// '===' is same variable type and value.
				if(!compact('value') || $value === null){
					//Undefined variable or null
					$newValue = $this->defShortcodeTagTextbox;
				} else {
					if(0 < strlen(trim($value)) && 2 <= strlen($value) && strpos($value, '/') === false && strlen($value) === mb_strlen($value)){
						$newValue = esc_attr($value);
					}
				}
				break;
			case 'field_priority_wpautop_checkbox':
				if(!compact('value') || $value === null){
					//Undefined variable or null
					$newValue = $this->defPriorityWpautopCheckbox;
				} else {
					//If checkbox is checked, $value to be 'checked' or 'on'.
					if(0 < strlen(trim($value)) && $value !== 'unchecked'){
						$newValue = 'checked';
					} else {
						$newValue = 'unchecked';
					}
				}
				break;
			case 'field_priority_wpautop_textbox':
				//Integer 0 to ...
				if(!compact('value') || $value === null){
					//Undefined variable or null
					$newValue = $this->defPriorityWpautopTextbox;
				} else {
					if(0 < strlen(trim($value)) && preg_match('/^(0|[1-9]+[0-9]*)$/', $value)){
						$newValue = $value;
					}
				}
				break;
		}

		if(!$newValue){
			if($displayError){
				add_settings_error(
					$this->className . '_settings',
					$key,
					esc_html__('Check your setting value.', $this->domainName),
					'error'
				);
			}
			switch($key) {
				case 'field_shortcode_tag_textbox':
					$newValue = $this->defShortcodeTagTextbox;
					break;
				case 'field_priority_wpautop_checkbox':
					$newValue = $this->defPriorityWpautopCheckbox;
					break;
				case 'field_priority_wpautop_textbox':
					$newValue = $this->defPriorityWpautopTextbox;
					break;
			}
		}
		return $newValue;
	}
}//End of class
