<?php
/**
 * Plugin Name: NM Contact Forms
 * Plugin URI: https://github.com/Bigloltrash/nm-contact-forms/wiki
 * Description: This plugin has built in honeyPot and reChaptcha anti spam solutions. Supports GET variables (allows to pass GET or POST variable info to the form). Option to turn off default CSS, add extra classes. User friendly UI, drag and drop sorting.
 * Version: 2.0
 * Author: Aidas Keburys @ Nutmedia & Bigloltrash
 * Author URI: http://nutmedia.co.uk
 * Network: Optional. Whether the plugin can only be activated network wide. Example: true
 * License: GPL2
 */

/*  Copyright 2014 AIDAS KEBURYS (email : info@nutmedia.co.uk)
	Copyright 2018 Bigloltrash (email : bigloltrash@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class nm_forms {

	function __construct() {
		if( is_admin() ){

			$plugin = plugin_basename(__FILE__);

			add_action('admin_menu', array($this,'admin_menu'));
			add_action('init', array($this,'admin_head'));
			add_filter("plugin_action_links_$plugin", array($this,'your_plugin_settings_link') );
		}

		add_action( 'admin_init', array($this,'nm_forms_init') );
		add_shortcode( 'nm_forms', array($this,'nm_forms_shortcode') );

	}

	function your_plugin_settings_link($links) {
		  $settings_link = '<a href="/wp-admin/admin.php?page=nm_settings">Settings</a>';
		  array_unshift($links, $settings_link);
		  return $links;
		}


	function nm_render($template, $data){

		ob_start();

		if(isset($data)){
			extract($data, EXTR_SKIP);
		}

		$dir = plugin_dir_path(__FILE__);
		include($dir.'templates/'.$template.'.php');

		$output = ob_get_contents();
		ob_end_clean();

		return $output;

	}

	function hideRow($type,$row){

		$hide = '';

		switch($type){
			case 'startingzone':
				if($row == 'extensions' || $row == 'size' || $row == 'html' || $row == 'placeholder' || $row == 'required' || $row == 'options' || $row == 'get' || $row == 'read_only') $hide = 'nm_hide';
				break;
			case 'text':
				if($row == 'extensions' || $row == 'size' || $row == 'html') $hide = 'nm_hide';
				break;
			case 'email':
				if($row == 'extensions' || $row == 'size' || $row == 'html') $hide = 'nm_hide';
				break;
			case 'textarea':
				if($row == 'extensions' || $row == 'size' || $row == 'html') $hide = 'nm_hide';
				break;
			case 'select':
				if($row == 'extensions' || $row == 'size' || $row == 'html' || $row == 'read_only') $hide = 'nm_hide';
				break;
			case 'checkbox':
				if($row == 'extensions' || $row == 'size' || $row == 'html' || $row == 'read_only') $hide = 'nm_hide';
				break;
			case 'html':
				if($row == 'extensions' || $row == 'size' || $row == 'placeholder' || $row == 'required' || $row == 'options' || $row == 'read_only') $hide = 'nm_hide';
				break;
			case 'freetextlabel':
				if($row == 'extensions' || $row == 'size' || $row == 'placeholder' || $row == 'required' || $row == 'options' || $row == 'read_only') $hide = 'nm_hide';
				break;
			case 'radio':
				if($row == 'extensions' || $row == 'size' || $row == 'html' || $row == 'read_only') $hide = 'nm_hide';
				break;
			case 'get_hidden':
				if($row == 'extensions' || $row == 'size' || $row == 'placeholder' || $row == 'required' || $row == 'html' || $row == 'read_only') $hide = 'nm_hide';
				break;
			case 'file_upload':
				if($row == 'placeholder' || $row == 'html' || $row == 'read_only') $hide = 'nm_hide';
				break;
			case 'submit':
				if($row == 'extensions' || $row == 'size' || $row == 'placeholder' || $row == 'options' || $row == 'get' || $row == 'required' || $row == 'html' || $row == 'read_only') $hide = 'nm_hide';
				break;
			case 'horizontal':
				if($row == 'extensions' || $row == 'size' || $row == 'placeholder' || $row == 'options' || $row == 'get' || $row == 'required' || $row == 'html' || $row == 'read_only') $hide = 'nm_hide';
				break;
			case 'recaptcha':
				if($row == 'extensions' || $row == 'size' || $row == 'placeholder' || $row == 'options' || $row == 'get' || $row == 'required' || $row == 'html' || $row == 'read_only') $hide = 'nm_hide';
				break;
			case 'honeypot':
				if($row == 'extensions' || $row == 'size' || $row == 'placeholder' || $row == 'options' || $row == 'get' || $row == 'required' || $row == 'html' || $row == 'read_only') $hide = 'nm_hide';
				break;
		}

		return $hide;

	}


	function formatBytes($size, $precision = 2)
	{
		$base = log($size, 1024);
		$suffixes = array('', 'Kb', 'Mb', 'Gb', 'Tb');

		return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
	}

	function replace_tags($string, $tags){
		return preg_replace_callback('/\\{\\{([^{}]+)\}\\}/',
				function($matches) use ($tags)
				{
					$key = $matches[1];
					return array_key_exists($key, $tags)
						? $tags[$key]
						: '';
				}
				, $string);
	}

	function nm_forms_shortcode( $atts ) {

		$nm_form_s = get_option( 'nm_f_s' );

		if(empty($nm_form_s['recaptcha_lang'])) $nm_form_s['recaptcha_lang'] = 'en';
		wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js?hl='.$nm_form_s['recaptcha_lang'], array(), null, true );

		// Bigloltrash 17/12/2018 : loading specific css or default one
		$upload = wp_upload_dir();
		$upload_dir = $upload['baseurl'];
		if($nm_form_s['own_css'] == ''){
			if($nm_form_s['default_css'] == 'on')
				{
					wp_enqueue_style( 'nm_forms-css', plugins_url('nm-contact-forms/css/front.css') );
				}
			}
			else {
				$own_css_url=$upload_dir.'/'.$nm_form_s['own_css'];
				wp_enqueue_style( 'nm_forms-css', $own_css_url );
			}


		$params = shortcode_atts( array(
			'id' => '',
		), $atts );

		$form_id = $params['id'];
		$nm_forms = get_option( 'nm_f' );

		if(!empty($nm_forms[$form_id])){
			$nm_form = $nm_forms[$form_id];
			$form = $this->nm_build_form($nm_form);
		}else $form = 'Form not found';

		return $form;

	}
		
		function nm_donate(){
			$nm_form_s = get_option( 'nm_f_s' );
			if(!isset($nm_form_s['hide_donation'])){
			?>
		
			<div class="updated nm_donation">
			<h3 class="nm_donate_heading">Hey there!</h3>
		
			If you are happy with this plugin please Donate.<br>You can disable this message in plugin settings page. Thank you!
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
				<input type="hidden" name="cmd" value="_donations">
				<input type="hidden" name="business" value="bigloltrash@gmail.com">
				<input type="hidden" name="lc" value="FR">
				<input type="hidden" name="item_name" value="NM Contact Forms">
				<input type="hidden" name="no_note" value="0">
				<input type="hidden" name="currency_code" value="EUR">
				<input type="hidden" name="bn" value="PP-DonationsBF:btn_donate_LG.gif:NonHostedGuest">
				<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
			</form>
			<div style="clear:both;"></div>
			</div>
		
			<?php
			}}
			
	function nm_send($nm_form){

		$data['fields'] = array();
		$response['errors'] = array();

		$nm_form_s = get_option( 'nm_f_s' );

		$admin_email = get_option( 'admin_email' );
		$sender_email = $admin_email;

		if(!empty($nm_form_s['default_sender']) && filter_var($nm_form_s['default_sender'], FILTER_VALIDATE_EMAIL)){
		$sender_email = $nm_form_s['default_sender'];
		}

		if(!empty($nm_form['sender']) && $nm_form['fields'][str_replace(array('{{','}}'), '', $nm_form['sender'])]['type'] == 'email'){

			$se = $_POST[str_replace(array('{{','}}'), '', $nm_form['sender'])];

			if(!empty($se) && filter_var($se, FILTER_VALIDATE_EMAIL)){

				$sender_email = $se;

			}

		}elseif(!empty($nm_form['sender']) && filter_var($nm_form['sender'], FILTER_VALIDATE_EMAIL)) $sender_email = $nm_form['sender'];


		$reply_to_email = $sender_email;

		if(!empty($nm_form['reply_to']) && $nm_form['fields'][str_replace(array('{{','}}'), '', $nm_form['reply_to'])]['type'] == 'email'){

			$re = $_POST[str_replace(array('{{','}}'), '', $nm_form['reply_to'])];

			if(!empty($re) && filter_var($re, FILTER_VALIDATE_EMAIL)){

				$reply_to_email = $re;

			}

		}elseif(!empty($nm_form['reply_to']) && filter_var($nm_form['reply_to'], FILTER_VALIDATE_EMAIL)) $reply_to_email = $nm_form['reply_to'];



		$from_title = $nm_form['nm_form_title'].' submission';
		if(!empty($nm_form_s['default_sender_title'])) $from_title = $nm_form_s['default_sender_title'];
		if(!empty($nm_form['sender_title'])) $from_title = $this->replace_tags($nm_form['sender_title'],$_POST);

		$subject = $nm_form['nm_form_title'].' submission';
		if(!empty($nm_form['subject'])) $subject = $this->replace_tags($nm_form['subject'],$_POST);

		$nonce = wp_verify_nonce( $_POST['nm_nonce'], 'send_form-'.$nm_form['nm_form_id'] );
		$recaptcha_field = $this->arrSearch('recaptcha',$nm_form);

		if($recaptcha_field){

			if(!empty($nm_form_s['secret'])){

			if(ini_get('allow_url_fopen')){

				$recaptcha = json_decode(file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$nm_form_s['secret'].'&response='.$_POST['g-recaptcha-response']));

			}else{

				$data = array(
		            'secret' => $nm_form_s['secret'],
		            'response' => $_POST['g-recaptcha-response']
		        );

				$verify = curl_init();
				curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
				curl_setopt($verify, CURLOPT_POST, true);
				curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
				curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
				$curl_response = curl_exec($verify);

				$recaptcha = json_decode($curl_response);


			}

			}else $response['errors'][] = 'reCaptcha secret is missing';

			if(!$recaptcha->success) $response['errors'][] = 'You did not pass security test.';

		}

		if(!$nonce) $response['errors'][] = 'Invalid nonce';

		$attachments = array();

		if(count($_FILES)){


			$dir = WP_CONTENT_DIR . "/uploads/wp-attachments/";
			if (!is_dir($dir)) {
				mkdir($dir);
			}

			foreach($_FILES as $field_id=>$file){

				$field_settings = $nm_form['fields'][$field_id];

				if(empty($file['name']) && $field_settings['required'] == 'on') $response['errors'][] = $field_settings['title']. ' is required.';


				if(!empty($file['name']) && !count($response['errors'])){


					$max_size = 10000000; //10MB
					if(!empty($field_settings['size']) && is_numeric($field_settings['size'])) $max_size = $field_settings['size']; //10MB

					$allowed_exts = array('jpg','png','gif','pdf','txt','doc','docx');

					if(!empty($field_settings['extensions'])){

						$allowed_exts = explode(',',$field_settings['extensions']);

					}

					$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
					if(!in_array($ext,$allowed_exts) ) {
						$response['errors'][] = $file['name'].' format is not allowed';
					}

					if($file['size'] > $max_size) $response['errors'][] = $file['name'].' is too large. Max allowed size is '.$this->formatBytes($max_size);

					if(!count($response['errors'])){

						move_uploaded_file($file["tmp_name"], WP_CONTENT_DIR . "/uploads/wp-attachments/" . $file["name"]);
						$attachments[] = WP_CONTENT_DIR . "/uploads/wp-attachments/" . $file["name"];



					}
				}
			}

		}

		foreach($nm_form['fields'] as $field_id=>$the_field){

			if(empty($the_field['type'])) $the_field['type'] = '';
			if(empty($the_field['required'])) $the_field['type'] = 0;

			if($the_field['type'] != 'file_upload' && $the_field['required'] && (empty($_POST[$field_id]) || !isset($_POST[$field_id]))) $response['errors'][] = $the_field['title']. ' is required.';

		}

		$all_fields = $_POST;

		unset($all_fields['nm_nonce']);
		unset($all_fields['g-recaptcha-response']);

		foreach($all_fields as $field_id=>$field_value){

			$field_settings = $nm_form['fields'][$field_id];

			$attachment_id = '';
			$honeypot_field_id = '';

			if($field_settings['type'] == 'file_upload') $attachment_id = $field_id;


			if($field_settings['type'] == 'honeypot'){

				$honeypot_field_id = $field_id;
				$honeypot_field_value = $field_value;
				if(!empty($honeypot_field_value)) $response['errors'][] = 'You just got into sweet honey trap';

			}

			if($field_settings['type'] == 'email'){

				if(!filter_var($field_value, FILTER_VALIDATE_EMAIL)) $response['errors'][] = 'Email is invalid';

			}

			if($field_id != 'nm_nonce' && $field_id != 'g-recaptcha-response' && $field_id != $honeypot_field_id && $field_id != $attachment_id){

				if(!count($field_value)) $response['errors'][] = $field_settings['title']. ' is required.';

				$data['fields'][$field_id]['title'] = $field_settings['title'];

				if(is_array($field_value)) $field_value = implode(', ',$field_value);

				$data['fields'][$field_id]['value'] = stripslashes($this->xss_clean($field_value));

				if(empty($field_value)) $data['fields'][$field_id]['value'] = ''; // former value : n/a
			}

		}

		$response['data'] = $_POST;

		if(!count($response['errors'])){

			$data['subject'] = $subject;
			$html = $this->nm_render('email_template', $data);

			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			$headers .= 'From: '.$from_title.' <'.$sender_email.'>' . "\r\n";
			$headers .= 'Reply-To: '.$reply_to_email. "\r\n";

			$receivers = explode(',',$nm_form['receivers']);
			if(!count($receivers)) $receivers = array($admin_email);

			wp_mail( $receivers, $subject, $html, $headers, $attachments );

			if($nm_form['autoreply'] == 'on' && isset($reply_to_email) && isset($nm_form['autoreply_msg']) && isset($nm_form['autoreply_subject']) && strlen($nm_form['autoreply_msg']) > 0) {
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				$headers .= 'From: '.$nm_form['sender'].' <'.$nm_form['sender'].'>' . "\r\n";
				$headers .= 'Reply-To: '.$receivers[0]. "\r\n";
				$data2['subject'] = $nm_form['autoreply_subject'];
				$data2['fields'][0]['title'] = null;
				$data2['fields'][0]['value'] = $nm_form['autoreply_msg'];
				$html = $this->nm_render('email_template', $data2);
				wp_mail( $reply_to_email, $nm_form['autoreply_subject'], $html, $headers);
			}
			$response['success'] = 'Your message was sent successfully. Thanks.';

			unset($response['data']);

		}

		return $response;

	}

	function nm_build_form($nm_form){

		if(isset($_POST['nm_nonce'])){
			$response = $this->nm_send($nm_form);

			if(!empty($nm_form['redirect']) && empty($response['errors'])){


				if($nm_form['js_redirect'] == 'on'){

				echo '<script>window.location = "'.$nm_form['redirect'].'";</script>';

				}else{

				header("Location: ".$nm_form['redirect']);
				die();

				}

			}

		}

		if(empty($response['data'])) $response['data'] = array();
		if(empty($response['errors'])) $response['errors'] = array();
		if(empty($nm_form['show_labels'])) $nm_form['show_labels'] = array();


		$nm_form_s = get_option( 'nm_f_s' );

		$nonce = wp_create_nonce( 'send_form-' . $nm_form['nm_form_id'] );

		$form =  $nm_form['before_form'];
		$form.= '<div class="nm_form_holder" id="nm_'.$nm_form['nm_form_id'].'"><form action="#nm_response" enctype="multipart/form-data" method="POST">';
		$form.='<input type="hidden" name="nm_nonce" value="'.$nonce.'">';
		$form.='<div class="nm_form" id="'.$nm_form['nm_form_id'].'">';
		$divcounter=0;
		$zonename='';
		foreach($nm_form['fields'] as $field){

			if(!array_key_exists('read_only', $field)) $field['read_only'] = '';

			if($field['read_only'] == 'on' ) $read_only = 'readonly';
			else $read_only = '';

			if(!empty($_GET[$field['get']])) $get_value = $this->xss_clean($_GET[$field['get']]);
			else{
				if (!empty($_POST[$field['get']]))
					$get_value=$this->xss_clean($_POST[$field['get']]);
				else
				   $get_value = '';
			}
			switch($field['type']){
				case 'text':
					$form.= '<div class="'.$field['classes'].'">';
					if($nm_form['show_labels'] == 'on') $form.= '<div class="nm_label">'.$field['title'].'</div>';
					if(count($response['data'])){
					$form.= '<input '.$read_only.' type="text" name="'.$field['slug'].'" placeholder="'.$field['placeholder'].'" value="'.stripslashes($response['data'][$field['slug']]).'"></div>';
				}else $form.= '<input '.$read_only.' type="text" name="'.$field['slug'].'" placeholder="'.$field['placeholder'].'" value="'.stripslashes($get_value).'"></div>';
					break;
				case 'file_upload':
					$form.= '<div class="'.$field['classes'].'">';
					$form.= '<div class="nm_label">'.$field['title'].'</div>';
					$form.= '<input type="file" name="'.$field['slug'].'"></div>';
					break;
				case 'startingzone':
					if ($divcounter>0)
						{
						$form.=  '</div> <!-- closing zone '.$zonename.' -->';
						$divcounter--;
						}
					$zonename=$field['slug'];
					$form.= '<div id="'.$zonename.'" class="'.$field['classes'].'"> <!-- starting zone '.$zonename.' -->';
					$divcounter++;
				case 'html':
					$form.= '<div class="'.$field['classes'].'">';
					if(strlen($field['html'])) $form.= $field['html'];
					else $form.=str_replace(array("\r\n", "\r", "\n"), "<br />",$get_value);
					$form.='</div>';
					break;
				case 'horizontal':
					$form.= '<hr class="'.$field['classes'].'">';
					break;
				case 'freetextlabel':
					$form.= '<div class="'.$field['classes'].'">';
					if($nm_form['show_labels'] == 'on') $form.= '<div class="nm_label">'.$field['title'].'</div>';
					if(strlen($field['html'])) $form.= $field['html'];
					else $form.=str_replace(array("\r\n", "\r", "\n"), "<br />",$get_value);
					$form.='</div>';
					break;
				case 'email':
					$form.= '<div class="'.$field['classes'].'">';
					if($nm_form['show_labels'] == 'on') $form.= '<div class="nm_label">'.$field['title'].'</div>';
					if(count($response['data'])){
					$form.= '<input '.$read_only.' type="email" name="'.$field['slug'].'" placeholder="'.$field['placeholder'].'" value="'.$response['data'][$field['slug']].'"></div>';
					}else $form.= '<input '.$read_only.' type="email" name="'.$field['slug'].'" placeholder="'.$field['placeholder'].'" value="'.$get_value.'"></div>';
					break;
				case 'radio':
					$options = preg_split("/\\r\\n|\\r|\\n/",$field['select_options']);
					$form.= '<div class="'.$field['classes'].'">';
					if($nm_form['show_labels'] == 'on') $form.= '<div class="nm_label">'.$field['title'].'</div>';
					foreach($options as $option){
						if($option == $get_value) $checked = 'checked';
						else $checked = '';
						$form.= '<label class="r_label"><input type="radio" name="'.$field['slug'].'" value="'.$option.'" '.$checked.'>'.$option.'</label>';
						}
					$form.= '</div>';
					break;
				case 'checkbox':
					$options = preg_split("/\\r\\n|\\r|\\n/",$field['select_options']);
					$form.= '<div class="'.$field['classes'].'">';
					if($nm_form['show_labels'] == 'on') $form.= '<div class="nm_label">'.$field['title'].'</div>';
					foreach($options as $option){

						if($option == $get_value) $checked = 'checked';
						else $checked = '';

						$form.= '<label class="cb_label"><input type="checkbox" name="'.$field['slug'].'[]" value="'.$option.'" '.$checked.'>'.$option.'</label>';
					}
					$form.= '</div>';
					break;
				case 'get_hidden':
					$form.= '<div class="'.$field['classes'].'" style="display:none;">';
					if($nm_form['show_labels'] == 'on') $form.= '<div class="nm_label">'.$field['title'].'</div>';
					if(count($response['data'])){
					$form.= '<input type="hidden" name="'.$field['slug'].'" value="'.$response['data'][$field['slug']].'"></div>';
					}else $form.= '<input type="hidden" name="'.$field['slug'].'" value="'.$get_value.'"></div>';
					break;
				case 'honeypot':
					$form.= '<div class="'.$field['classes'].'" style="display:none;">';
					if($nm_form['show_labels'] == 'on') $form.= '<div class="nm_label">'.$field['title'].'</div>';
					$form.= '<input type="text" name="'.$field['slug'].'" value=""></div>';
					break;
				case 'textarea':
					$form.= '<div class="'.$field['classes'].'">';
					if($nm_form['show_labels'] == 'on') $form.= '<div class="nm_label">'.$field['title'].'</div>';
					if(count($response['data'])){
					$form.= '<textarea '.$read_only.' name="'.$field['slug'].'" placeholder="'.$field['placeholder'].'">'.stripslashes($response['data'][$field['slug']]).'</textarea></div>';
				}else $form.= '<textarea  '.$read_only.' name="'.$field['slug'].'" placeholder="'.$field['placeholder'].'">'.stripslashes($get_value).'</textarea></div>';
					break;
				case 'submit':
					$form.= '<input type="submit" class="'.$field['classes'].'" value="'.$field['title'].'">';
					break;
				case 'recaptcha':
					if(!empty($nm_form_s['recaptcha'])){
						$form.= '<div class="'.$field['classes'].'"><div class="g-recaptcha" data-sitekey="'.$nm_form_s['recaptcha'].'"></div></div>';
					}else{
						$form.= '<div class="'.$field['classes'].'">reCaptcha sitekey is missing</div>';
					}
					break;
				case 'select':
					$options = preg_split("/\\r\\n|\\r|\\n/",$field['select_options']);
					$form.= '<div class="'.$field['classes'].'">';
					if($nm_form['show_labels'] == 'on') $form.= '<div class="nm_label">'.$field['title'].'</div>';
					$form.= '<select name="'.$field['slug'].'">';

					if(!empty($field['placeholder'])) $form.= '<option value="">'.$field['placeholder'].'</option>';

					foreach($options as $option){

						if($option == $get_value) $selected = 'selected';
						else $selected = '';

						$form.= '<option value="'.$option.'" '.$selected.'>'.$option.'</option>';

					}
					$form.= '</select></div>';
					break;
			}

		}
		if ($divcounter>0)
			{
				do {
					$form.=  '</div> <!-- closing divcounter N°'.$divcounter.' -->';
					$divcounter--;
				} while ($divcounter >0);

			}

		$form.=  '</div>';

		if(!empty($response['success'])) $form.= '<div id="nm_response" class="nm_success">Thanks! Message sent successfully.</div>';

		if(count($response['errors'])){

			$form.=  '<div id="nm_response" class="nm_errors">';
			$form.='<ul>';

			foreach($response['errors'] as $error){
			$form.=  '<li>'. $error . '</li>';
			}
			$form.=  '</ul>';
			$form.=  '</div>';

		}

		$form.=  '</form></div>';
		$form.=  $nm_form['after_form'];

		return $form;

	}

	function nm_forms_init(){
		register_setting( 'nm_forms_data', 'nm_f', array($this,'nm_callback'));
		register_setting( 'nm_forms_settings', 'nm_f_s', array($this,'nm_callback'));
	}

	function nm_callback($array){

		foreach($array as $key=>$arr){

			if(!empty($arr['nm_form_id'])){
				if(!isset($arr['show_labels'])) $array[$key]['show_labels'] = 'false';
			}

		}

		return $array;

	}


	function arrSearch($search, array $data)
	{
		$iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($data), RecursiveIteratorIterator::CHILD_FIRST);

		foreach ($iterator as $key => $value) {
			if (is_string($key) && ($search == $key)) {
				return true;
			}
			if (is_string($value) && ($search == $value)) {
				return true;
			}
		}
		return false;
	}



	function admin_menu()
	{
		/*
		add_menu_page("Contact forms", "Contact forms", 'manage_options', 'nm_forms', 'nm_forms', false, '81.9874554721');
		add_submenu_page( 'nm_forms', 'Forms', 'Forms', 'manage_options', 'nm_forms', 'nm_forms' );
		add_submenu_page( 'nm_forms', 'Settings', 'Settings', 'manage_options', 'nm_settings', 'nm_settings' );
		*/

		add_menu_page("Contact forms", "Contact forms", 'manage_options', 'nm_forms_list', 'nm_forms_list', plugins_url( '/nm-contact-forms/admin/images/adminlogo2-v2.png' ), '81.9874554721');
		add_submenu_page( 'nm_forms_list', 'Forms list', 'Forms list', 'manage_options', 'nm_forms_list', 'nm_forms_list' );
		add_submenu_page( 'nm_forms_list', 'Detailed Forms', 'Detailed Forms', 'manage_options', 'nm_forms', 'nm_forms' );
		add_submenu_page( 'nm_forms_list', 'Settings', 'Settings', 'manage_options', 'nm_settings', 'nm_settings' );

	}

	function admin_head()
	{

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-button' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_style( 'nm_forms-css', plugins_url('nm-contact-forms/admin/admin.css') );
		wp_enqueue_script( 'nm_forms', plugins_url('nm-contact-forms/admin/js/nm_forms.js'), array(), null, true );
	}

	function xss_clean($data){

		// Fix &entity\n;
		$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
		$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
		$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
		$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

		// Remove any attribute starting with "on" or xmlns
		$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

		// Remove javascript: and vbscript: protocols
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

		// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

		// Remove namespaced elements (we do not need them)
		$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

		do
		{
			// Remove really unwanted tags
			$old_data = $data;
			$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
		}
		while ($old_data !== $data);

		// we are done...
		return $data;

	}

	function settings()
	{
		include('admin/settings.php');
	}

	function forms()
	{
		include('admin/forms.php');
	}

	function forms_list()
	{
		include('admin/formslist.php');
	}



}

$nm_forms_c = new nm_forms();



function nm_forms_list(){

	global $nm_forms_c;
	$nm_forms_c->forms_list();

}

function nm_forms(){

	global $nm_forms_c;
	$nm_forms_c->forms();

}

function nm_settings(){

	global $nm_forms_c;
	$nm_forms_c->settings();

}
