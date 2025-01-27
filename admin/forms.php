<?php
global $nm_forms_c;
$modifying_global=True;

	//print_r($_POST);

if(empty($_GET['settings-updated'])) $_GET['settings-updated'] = '';
if (isset($_GET['modifying_sourceform_id']))
	{
		$FormIdToModify=$_GET['modifying_sourceform_id'];
		$modifying_global=False;
		//echo "FormIdToModify :  $FormIdToModify<br><br>";
	}
?>

<div class="wrap">

	<?php if($_GET['settings-updated'] == 'true'){?>
	<div class="updated">
		<p>All forms have been saved.</p>
	</div>
	<?php } ?>

	<?php if (version_compare(PHP_VERSION, '5.3.0') < 0) { ?>
	<div class="nm_warning updated">
		<p>
    		<?php echo 'NM Contact forms plugin requires PHP version to be 5.3.0 or newer, your version: ' . PHP_VERSION . "\n"; ?>
		</p>

	</div>
	<?php } ?>

	<h2>NM contact forms <a id="add_new_form" href="" class="add-new-h2">Add new</a></h2>

	<div id="add_new_form_block" style="display:none;">

		<div class="add_new_form_inner">
			<h2>Create new contact form</h2>
			<input id="new_form_title" type="text" value="" placeholder="Contact form title"/>
			<input type="submit" id="add_form" class="button-primary" value="<?php _e( 'Add new form', 'nm_forms' ); ?>" />
		</div>

	</div>

	<?php $nm_forms_c->nm_donate();?>
	<h3>Detailed Forms</h3>

	<form method="post" id="nm_all_forms" action="options.php">
		<input type="hidden" id="modifying_sourceform_id" name="modifying_sourceform_id" value="<? echo $FormIdToModify;?>">

	<?php settings_fields( 'nm_forms_data' ); ?>

	<?php
	$nm_forms = get_option( 'nm_f' );
	$admin_email = get_option( 'admin_email' );
	if (empty($nm_forms))
    { ?>
      <div class="add_new_form_inner">
      There's no form to display yet...
      </div>
    <? }


	?>
	<div class="nm_forms_container">
	<div class="nm_forms_inner" id="inner_forms">
		<?php if(!empty($nm_forms)){?>

			<?php
			$i = 0;
			foreach($nm_forms as $form)
			{
// ($modifying_global) or (!$modifying_global and !strcmp($form['nm_form_id'],$FormIdToModify)
			if (($modifying_global) or (!strcmp($form['nm_form_id'],$FormIdToModify)))
			{
			if(!array_key_exists('js_redirect', $form)) $form['js_redirect'] = '';
			if(!array_key_exists('autoreply', $form)) $form['autoreply'] = '';
			if(!array_key_exists('required', $form)) $form['required'] = '';

			$i++;
			if(empty($form['show_labels'])) $form['show_labels'] = 0;
			if(empty($form['required'])) $form['required'] = 0;
			?>

				<div class="nm_form" data-nm_form_id="<?=$form['nm_form_id'];?>">

					<input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][nm_form_title]" value="<?=$form['nm_form_title'];?>">
					<input type="hidden" class="nm_form_id" name="nm_f[<?=$form['nm_form_id'];?>][nm_form_id]" value="<?=$form['nm_form_id'];?>">
					<div class="nm_form_heading">
						<h3><?=$form['nm_form_title'];?><span class="nm_form_delete"><span class="dashicons dashicons-no"></span></span><span class="nm_form_add">Add field</span></h3>
					</div>

					<ul class="nm_errors">
					</ul>


					<div class="nm_left_side">

					<div class="nm_left_empty" style="<?php if(!count($form['fields'])){ echo 'display:block;'; } ?>">

					<span class="dashicons dashicons-index-card"></span>
					<span>Form is empty, please add your first field.</span>

					</div>

					<ul class="menu nm_menu nm_form_fields nm_sortable  ui-sortable">

						<?php if(!empty($form['fields'])){?>
						<?php

						$submit = $nm_forms_c->arrSearch('submit',$form['fields']);
						$recaptcha = $nm_forms_c->arrSearch('recaptcha',$form['fields']);
						$honeypot = $nm_forms_c->arrSearch('honeypot',$form['fields']);
						$anydiv=0;

						foreach($form['fields'] as $field){
						if(!array_key_exists('read_only', $field)) $field['read_only'] = '';
						if(!array_key_exists('field', $field)) $field['field'] = '';

						?>

						<li class="nm_item menu-item menu-item-page">
							<dl class="menu-item-bar">
							<dt class="menu-item-handle handle_nm">
							<span class="item-title">
								<?
								if ($field['type'] == 'startingzone')
									{
										$fieldtitletodisplay=mb_strtoupper('ZONE : '.$field['title']);
										$anydiv=1;
									}
								else
									{
										if ($anydiv>0) $fieldtitletodisplay='&emsp;&emsp;&emsp;'.$field['title'];
										else $fieldtitletodisplay=$field['title'];
									}
								if ($field['title']=="")
									$fieldtitletodisplay.=$field['slug'];
								?>
								<span class="nm_field_heading menu-item-title"><?=$fieldtitletodisplay;?></span>
							</span>
							<span class="nm_toggle"></span>
							</dt>
							</dl>

							<div class="nm_field_settings">

								<table class="nm_table">
								<tr><th>
								Field ID:
								</th><td>
								<input type="text" class="" value="<?=$field['slug'];?>" readonly />
								</td></tr><tr><th>
								Field title:
								</th><td>
								<input type="text" class="nm_field_title" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][title]" value="<?=$field['title'];?>"/>
								</td></tr><tr class="nm_placeholder <?=$nm_forms_c->hideRow($field['type'],'placeholder');?>"><th>
								Field placeholder:
								</th><td>
								<input type="text" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][placeholder]" value="<?=$field['placeholder'];?>"/>
								</td></tr><tr><th>
								Field type:
								</th><td>
									<select type="text" class="nm_field_type" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][type]">
										<option value="checkbox" <?php if($field['type'] == 'checkbox'){?>selected<?php }?>>Checkboxes</option>
										<option value="email" <?php if($field['type'] == 'email'){?>selected<?php }?>>Email</option>
										<option value="freetextlabel" <?php if($field['type'] == 'freetextlabel'){?>selected<?php }?>>Free text with label</option>
										<option value="get_hidden" <?php if($field['type'] == 'get_hidden'){?>selected<?php }?>>GET variable (hidden)</option>
										<option value="honeypot" <?php if($field['type'] == 'honeypot'){?>selected<?php }?> <?php if($field['type'] != 'honeypot' && $honeypot){?>disabled<?php }?>>Honey Pot</option>
										<option value="horizontal" <?php if($field['type'] == 'horizontal'){?>selected<?php }?>>Horizontal rule</option>
										<option value="html" <?php if($field['type'] == 'html'){?>selected<?php }?>>HTML</option>
										<option value="radio" <?php if($field['type'] == 'radio'){?>selected<?php }?>>Radio Buttons</option>
										<option value="recaptcha" <?php if($field['type'] == 'recaptcha'){?>selected<?php }?> <?php if($field['type'] != 'recaptcha' && $recaptcha){?>disabled<?php }?>>reCaptcha</option>
										<option value="select" <?php if($field['type'] == 'select'){?>selected<?php }?>>Select</option>
										<option value="file_upload" <?php if($field['type'] == 'file_upload'){?>selected<?php }?>>Single file upload</option>
										<option value="startingzone" <?php if($field['type'] == 'startingzone'){?>selected<?php }?>>Starting Zone</option>
										<option value="submit" <?php if($field['type'] == 'submit'){?>selected<?php }?> <?php if($field['type'] != 'submit' &&
										$submit){?>disabled<?php }?>>Submit</option>
										<option value="text" <?php if($field['type'] == 'text'){?>selected<?php }?>>Text</option>
										<option value="textarea" <?php if($field['type'] == 'textarea'){?>selected<?php }?>>Textarea</option>
									</select>
								</td></tr>
								<tr class="nm_select_options <?=$nm_forms_c->hideRow($field['type'],'options');?> <?php if($field['type'] == 'select' || $field['type'] == 'radio' || $field['type'] == 'checkbox'){?>nm_show_row<?php }?>">
									<th>
									Options:
									</th>
									<td>
									<textarea style="min-height:200px;" placeholder="One option per line" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][select_options]"><?=$field['select_options'];?></textarea>
									</td>
								</tr>
								<tr class="nm_html <?=$nm_forms_c->hideRow($field['type'],'html');?>"><th>
								HTML:
								</th><td>
								<textarea style="min-height:200px;" placeholder="HTML / Free text" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][html]"><?=$field['html'];?></textarea>
								</td></tr>
								<tr class="nm_get <?=$nm_forms_c->hideRow($field['type'],'get');?>"><th>
								GET variable:
								</th><td>
								<input placeholder="e.g.: affiliate_id" type="text" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][get]" value="<?=$field['get'];?>" />
								</td></tr>
								<tr class="nm_read_only <?=$nm_forms_c->hideRow($field['type'],'read_only');?>"><th>
								Read-only:
								</th><td>
								<input type="checkbox" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][read_only]" <?php if($field['read_only'] == 'on'){?>checked<?php }?>/>
								</td></tr>
								<tr class="nm_exts <?=$nm_forms_c->hideRow($field['type'],'extensions');?>"><th>
								Allowed extensions:
								</th><td>
								<input placeholder="e.g.:jpg,png,gif" type="text" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][extensions]" value="<?=$field['extensions'];?>" />
								<p class="description">Separated by coma</p>
								</td></tr>
								<tr class="nm_size <?=$nm_forms_c->hideRow($field['type'],'size');?>"><th>
								Max file size:
								</th><td>
								<input placeholder="e.g.: 10000" type="text" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][size]" value="<?=$field['size'];?>" />
								<p class="description">Bytes, 1 000 000 ~ 1MB</p>
								</td></tr>
								<tr class="nm_required <?=$nm_forms_c->hideRow($field['type'],'required');?>"><th>
								Required:
								</th><td>
								<input type="checkbox" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][required]" <?php if($field['required'] == 'on'){?>checked<?php }?>/>
								</td></tr><tr><th>
								Extra classes:
								</th><td>
								<input type="text" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][classes]" value="<?=$field['classes'];?>"/>
								</td></tr>
								<tr><th>
								</th><td>
								<a href="" class="nm_delete_field">Delete field</a>
								</td></tr>
								</table>

								<input type="hidden" class="nm_field_slug" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][slug]" value="<?=$field['slug'];?>"/>

							<div style="clear:both;"></div>
							</div>
						</li>

						<?php } ?>
						<?php } ?>

					</ul>
					</div>
					<div class="nm_right_side">

					<ul class="menu nm_menu nm_sortable  ui-sortable">
						<li class="nm_item menu-item menu-item-page">
							<dl class="menu-item-bar">
							<dt class="menu-item-handle handle_nm">
							<span class="item-title">
								<span class="menu-item-title">Form Settings</span>
							</span>
							</dt>
							</dl>

							<div class="nm_field_settings nm_form_settings" style="display:block;">

								<table class="nm_table">
									<tr>
										<th>
											Email subject:
										</th>
										<td>
											<input type="text" name="nm_f[<?=$form['nm_form_id'];?>][subject]" value="<?=$form['subject'];?>">
										</td>
									</tr>
									<tr>
										<th>
											Receivers:
										</th>
										<td>
											<input type="text" name="nm_f[<?=$form['nm_form_id'];?>][receivers]" value="<?=(!empty($form['receivers'])) ? $form['receivers'] : $admin_email;?>">
											<p class="description">Separated by coma.</p>
										</td>
									</tr>
									<tr>
										<th>
											From email:
										</th>
										<td>
											<input type="text" name="nm_f[<?=$form['nm_form_id'];?>][sender]" value="<?=$form['sender'];?>">
											<p class="description">Email or field ID, fallback to admin email.</p>
										</td>
									</tr>
									<tr>
										<th>
											Reply-to email:
										</th>
										<td>
											<input type="text" name="nm_f[<?=$form['nm_form_id'];?>][reply_to]" value="<?=$form['reply_to'];?>">
											<p class="description">Email or field ID, fallback to admin email. Default is sender email.</p>
										</td>
									</tr>
									<tr>
										<th>
											From title:
										</th>
										<td>
											<input type="text" name="nm_f[<?=$form['nm_form_id'];?>][sender_title]" value="<?=$form['sender_title'];?>">
											<p class="description">You can construct form title using field IDs. e.g.: From {{nm_field_1}}</p>
										</td>
									</tr>
									<tr>
										<th>
											Show labels:
										</th>
										<td>
											<input type="checkbox" name="nm_f[<?=$form['nm_form_id'];?>][show_labels]" <?php if($form['show_labels'] == 'on'){?>checked<?php }?>/>
										</td>
									</tr>
									<tr>
										<th>
											Before FORM:
										</th>
										<td>
											<input type="text" name="nm_f[<?=$form['nm_form_id'];?>][before_form]" value="<?=htmlentities($form['before_form']);?>">
										</td>
									</tr>
									<tr>
										<th>
											After FORM:
										</th>
										<td>
											<input type="text" name="nm_f[<?=$form['nm_form_id'];?>][after_form]" value="<?=htmlentities($form['after_form']);?>">
										</td>
									</tr>
									<tr>
										<th>
											Redirect url:
										</th>
										<td>
											<input type="text" name="nm_f[<?=$form['nm_form_id'];?>][redirect]" value="<?=htmlentities($form['redirect']);?>">
											<p class="description">Redirect after successful submission.</p>
										</td>
									</tr>
									<tr>
										<th>
											Javascript redirect:
										</th>
										<td>
											<label>
											<input type="checkbox" name="nm_f[<?=$form['nm_form_id'];?>][js_redirect]" <?php if($form['js_redirect'] == 'on'){?>checked<?php }?>/>
											Yes
											</label>
											<p class="description">Incase PHP redirect breaks the page.</p>
										</td>
									</tr>
									<tr>
										<th style="vertical-align: top">Enable Autoreply</th>
										<td><input style="margin-bottom: 10px" id="autoreply" type="checkbox" <?php if($form['autoreply'] == 'on'){?>checked<?php }?> name="nm_f[<?=$form['nm_form_id'];?>][autoreply]">
										<input id="autoreply-subject" placeholder="Autoreply subject" name="nm_f[<?=$form['nm_form_id'];?>][autoreply_subject]" type="text" style="margin-bottom: 10px" <?php if(isset($form['autoreply_subject'])){?>
												class="show" value="<?php echo $form['autoreply_subject']; ?>"
											<?php }else {?>
												placeholder="Autoreply subject"
											<?php } ?>/>
										<textarea id="autoreply-text" name="nm_f[<?=$form['nm_form_id'];?>][autoreply_msg]"
											<?php if(isset($form['autoreply_msg'])){?>
												class="show"
											<?php }else {?>
												placeholder="Autoreply message"
											<?php } ?>><?php if(isset($form['autoreply_msg'])){ echo $form['autoreply_msg']; }?></textarea>
										</td>
									</tr>
								</table>
							</div>
						</li>
					</ul>
					<div class="nm_shortcode">
							Form shortcode:</br>
							<span class="nm_bold">[nm_forms id="<?=$form['nm_form_id'];?>"]</span>
							<div class="nm_sep"></div>
							Template integration:</br>
							<span class="nm_bold"><&#63;php echo do_shortcode('[nm_forms id="<?=$form['nm_form_id'];?>"]');&#63;></span>
							<div class="nm_sep"></div>
							Default Horizontal rules classes :</br>
							<span class="nm_bold">dashed, dotted, gradient, thindotted, shadow </span>
					</div>

					<input type="submit" class="nm_save_forms button-primary" value="<?php _e( 'Save Forms', 'nm_forms' ); ?>" />
					</div>


				</div>
			<?php
					}	// if $modifying_global
					else {
							//echo "otherform<br>";
							?>
							<input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][nm_form_title]" value="<?=$form['nm_form_title'];?>">
					    <input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][nm_form_id]" value="<?=$form['nm_form_id'];?>">
							<input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][subject]" value="<?=$form['subject'];?>">
					    <input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][receivers]" value="<?=(!empty($form['receivers'])) ? $form['receivers'] : $admin_email;?>">
					    <input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][sender]" value="<?=$form['sender'];?>">
					    <input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][reply_to]" value="<?=$form['reply_to'];?>">
					    <input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][sender_title]" value="<?=$form['sender_title'];?>">
					    <input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][show_labels]" value="<?=$form['show_labels'];?>">
					    <input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][before_form]" value="<?=$form['before_form'];?>">
					    <input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][after_form]" value="<?=$form['after_form'];?>">
					    <input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][redirect]" value="<?=$form['redirect'];?>">
					    <input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][js_redirect]" value="<?=$form['js_redirect'];?>">
					    <input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][autoreply]" value="<?=$form['autoreply'];?>">
					    <input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][autoreply_subject]" value="<?=$form['autoreply_subject'];?>">
					    <input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][autoreply_msg]" value="<?php if(isset($form['autoreply_msg'])){ echo $form['autoreply_msg']; }?>">
							<?
							foreach($form['fields'] as $field){ // Lire tous les champs du formulaire courant
								?>
								<input type="hidden" value="<?=$field['slug'];?>"/>
								<input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][title]" value="<?=$field['title'];?>"/>
								<input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][type]" value="<?=$field['type'];?>">
								<input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][placeholder]" value="<?=$field['placeholder'];?>"/>
								<textarea style="min-height:200px;display:none;" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][select_options]"><? echo $field['select_options'];?></textarea>
								<textarea style="min-height:200px;display:none;" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][html]"><? echo $field['html'];?></textarea>
								<input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][read_only]" value="<?=$field['read_only'];?>"/>
								<input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][required]" value="<?=$field['required'];?>"/>
								<input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][classes]" value="<?=$field['classes'];?>"/>
								<input type="hidden" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][slug]" value="<?=$field['slug'];?>"/>
								<?
							}
					}

				}
			} // foreach $form
		 ?>


	</div>
	</div>
	<? if (($modifying_global) and (!empty($nm_forms))) { ?>
		<input type="submit" class="nm_save_forms button-primary" value="<?php _e( 'Save All Forms', 'nm_forms' ); ?>" />
	<? }?>

	</form>

</div>
