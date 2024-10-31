<?php
global $nm_forms_c;
if(empty($_GET['settings-updated'])) $_GET['settings-updated'] = '';
$admin_email = get_option( 'admin_email' );

 //---------------------------------------------------------------------------------------------------------------
// BEG OF Form renaming script --------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------
if (isset($_POST['renaming_sourceform_id']) and ($_POST['renaming_sourceform_id']!=""))
{
    $sourceFormId=$_POST['renaming_sourceform_id'];
    $targetFormId=$_POST['renaming_targetform_id'];
    $targetFormName=$_POST['renaming_targetform_name'];

    $nm_source_forms = get_option( 'nm_f' );
    $i=0;

    foreach($nm_source_forms as $source_form)
    {
      //echo "<br>*****************************************************************************************************************************<br>";
      //print_r($source_form);
      //echo "<br>*****************************************************************************************************************************<br>";

      if (!strcmp ($source_form['nm_form_id'],$sourceFormId))
        {
          // renommage du formulaire choisi !
          $renamed_temp_form=$source_form;
          $renamed_temp_form['nm_form_id']=$targetFormId; 
          $renamed_temp_form['nm_form_title']=$targetFormName; 
          $nm_renamed_forms[$targetFormId]=$renamed_temp_form;
          $i++;
        }
      else
      {
        $indice=$source_form['nm_form_id'];
        $nm_renamed_forms[$indice]=$source_form;
      }
    }
    
      /*
      echo "<br><br>Résultat :";
      echo "<br>*****************************************************************************************************************************<br>";
      print_r($nm_renamed_forms);
      echo "<br>*****************************************************************************************************************************<br>";
      */

    update_option('nm_f',$nm_renamed_forms);
  }

//---------------------------------------------------------------------------------------------------------------
// BEG OF Form duplication script --------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------
if (isset($_POST['duplicated_sourceform_id']) and ($_POST['duplicated_sourceform_id']!=""))
{
  $sourceFormId=$_POST['duplicated_sourceform_id'];
    $targetFormId=$_POST['duplicated_targetform_id'];
    $targetFormName=$_POST['duplicated_targetform_name'];

    $nm_source_forms = get_option( 'nm_f' );
    $i=0;

    foreach($nm_source_forms as $source_form)
    {
      //echo "<br>*****************************************************************************************************************************<br>";
      //print_r($source_form);
      //echo "<br>*****************************************************************************************************************************<br>";

      if (!strcmp ($source_form['nm_form_id'],$sourceFormId))
        {
          // Duplication du formulaire choisi !
          $duplicate_temp_form=$source_form;
          $duplicate_temp_form['nm_form_id']=$targetFormId; //echo "<br>[$i]> Formulaire dupliqué : $sourceFormId --> ".$duplicate_temp_form['nm_form_id'];
          $duplicate_temp_form['nm_form_title']=$targetFormName; //echo " / ".$duplicate_temp_form['nm_form_title'];
          $nm_duplicate_forms[$targetFormId]=$duplicate_temp_form;
          $i++;
        }
      }


    foreach($nm_source_forms as $source_form)
    {
      $indice=$source_form['nm_form_id'];
      $nm_duplicate_forms[$indice]=$source_form;
      $i++;
    }

    /*
      echo "<br><br>Résultat :";
      echo "<br>*****************************************************************************************************************************<br>";
      print_r($nm_duplicate_forms);
      echo "<br>*****************************************************************************************************************************<br>";
    */
      update_option('nm_f',$nm_duplicate_forms);
  }
  //---------------------------------------------------------------------------------------------------------------
  // END OF Form duplication script --------------------------------------------------------------------------------------
  //---------------------------------------------------------------------------------------------------------------

  //---------------------------------------------------------------------------------------------------------------
  // BEG OF Form removing script --------------------------------------------------------------------------------------
  //---------------------------------------------------------------------------------------------------------------
  if (isset($_POST['removing_sourceform_id']) and ($_POST['removing_sourceform_id']!=""))
    {
      $formIdToRemove=$_POST['removing_sourceform_id'];

      $nm_source_forms = get_option( 'nm_f' );
      $i=0;

      foreach($nm_source_forms as $source_form)
      {
        if (strcmp ($source_form['nm_form_id'],$formIdToRemove)) // Tant que l'on est pas sur le formulaire à supprimer, on conserve
          {
            //echo "<BR>$i : ".$source_form['nm_form_id']." --> Conservé";
            $indice=$source_form['nm_form_id'];
            $nm_forms[$indice]=$source_form;
            $i++;
          }
        //else echo "<BR>$i : ".$source_form['nm_form_id']." --> Supprimé";
        }

        /*
        echo "<br>*****************************************************************************************************************************<br>";
        echo "<br><br>Résultat :";
        echo "<br>*****************************************************************************************************************************<br>";
        print_r($nm_forms);
        echo "<br>*****************************************************************************************************************************<br>";
        echo "<br>*****************************************************************************************************************************<br>";
        */
        update_option('nm_f',$nm_forms);
    }
    //---------------------------------------------------------------------------------------------------------------
    // END OF Form removing script --------------------------------------------------------------------------------------
    //---------------------------------------------------------------------------------------------------------------

?>

<div class="wrap">
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
  <h3>Forms List</h3>

  <form method="post" id="nm_all_forms" action="options.php">
    <input type="hidden" id="duplicated_targetform_name" name="duplicated_targetform_name" value="">
    <input type="hidden" id="duplicated_targetform_id" name="duplicated_targetform_id" value="">
    <input type="hidden" id="duplicated_sourceform_id" name="duplicated_sourceform_id" value="">
    <input type="hidden" id="modifying_sourceform_id" name="modifying_sourceform_id" value="">
    <input type="hidden" id="removing_sourceform_id" name="removing_sourceform_id" value="">
    <input type="hidden" id="renaming_targetform_name" name="renaming_targetform_name" value="">
    <input type="hidden" id="renaming_targetform_id" name="renaming_targetform_id" value="">
    <input type="hidden" id="renaming_sourceform_id" name="renaming_sourceform_id" value="">


    <?php settings_fields( 'nm_forms_data' ); ?>
    <div class="nm_forms_container">
  	<div class="nm_forms_inner" id="inner_forms">
  <?
  $nm_forms = get_option( 'nm_f' );

  $i = 0;

  if (empty($nm_forms))
    { ?>
      <div class="add_new_form_inner">
      There's no form to display yet...
      </div>
    <? }

  foreach($nm_forms as $form){ ?>
    <div class="add_new_form_inner">
    <?
    $NbChamps=count($form['fields']);
    //echo $form['nm_form_id']."--> $NbChamps champs<br>";
    /*
    <input placeholder="e.g.: 10000" type="text" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][size]" value="<?=$field['size'];?>" />
    <input placeholder="e.g.: affiliate_id" type="text" name="nm_f[<?=$form['nm_form_id'];?>][fields][<?=$field['slug'];?>][get]" value="<?=$field['get'];?>" />

    <span class="nm_form_delete">
      <span class="dashicons dashicons-no"></span>
    </span>
    */
    ?>
    <h2><?=$form['nm_form_title']." ($NbChamps "; if ($NbChamps>1) echo "fields)"; else echo "field)";?>
      <input type="submit" id="remove_form" class="nm_form_add " value="<?php _e( 'Remove Form', 'nm_forms' ); ?>" onclick="removeForm('<? echo $form['nm_form_id'];?>')"/>
      <input type="submit" id="modify_form" class="nm_form_add" value="<?php _e( 'Modify Form', 'nm_forms' ); ?>" onclick="modifyForm('<? echo $form['nm_form_id'];?>')"/>
      <input type="submit" id="duplicate_form" class="nm_form_add" value="<?php _e( 'Duplicate Form', 'nm_forms' ); ?>" onclick="duplicateForm('<? echo $form['nm_form_id'];?>')"/>
      <input type="submit" id="rename_form" class="nm_form_add" value="<?php _e( 'Rename Form', 'nm_forms' ); ?>"  onclick="renameForm('<? echo $form['nm_form_id'];?>')"/>
      </h2>
    <table class="nm_table">
    <tr><th style="vertical-align:middle;width:200px;">
    Form shortcode : 
    </th><td>
    [nm_forms id="<?=$form['nm_form_id'];?>"]
    </td><td></td></tr>
    <tr><th style="vertical-align:middle;width:200px;">
    Template integration : 
    </th><td>
    <&#63;php echo do_shortcode('[nm_forms id="<?=$form['nm_form_id'];?>"]');&#63;>
    </td><td></td></tr>
    <tr><th style="vertical-align:middle;width:200px;">
        New Form Name : 
    </th><td>
    <input type="text" id="new_form_name_<?=$form['nm_form_id'];?>" name="new_form_name_<?=$form['nm_form_id'];?>" value="" placeholder="Contact form title" class="nm_field_title" style="width:200px;">
    </td><td></td></tr>
    <tr><td></td><td><p class="description" style="vertical-align:top;margin-top:0px;">To rename or duplicate the current form</p></td></tr></table>
    <input type="hidden" id="nm_form_title_<?=$form['nm_form_id'];?>" name="nm_f[<?=$form['nm_form_id'];?>][nm_form_title]" value="<?=$form['nm_form_title'];?>">
    <input type="hidden" id="nm_form_id_<?=$form['nm_form_id'];?>" name="nm_f[<?=$form['nm_form_id'];?>][nm_form_id]" value="<?=$form['nm_form_id'];?>">
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
        //echo"<br><br>";
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
    echo"<br>";
    $i++; ?>
  </div> <?
  }
  //echo "<br>Nb de formulaires : $i<br><br>";
  if (!empty($nm_forms)) {
  ?>
  <input type="submit" class="nm_save_forms button-primary" value="<?php _e( 'Rename All Forms', 'nm_forms' ); ?>" />
<? }?>
</div></div>
</form>
</div>