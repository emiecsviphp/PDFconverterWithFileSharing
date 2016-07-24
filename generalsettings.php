<?php
/* 
	This file is part of the plugin of exponentmediapdfconverter
*/

function general_settings_pdf_converter_function()	{
	global $wpdb, $table_prefix,$current_user;

    $pluginname = 'exponent_media_pdf_converter';
	$options = get_option($pluginname.'_settings');
	
	if(isset($_POST['submitPDFoption']))	{
		if($_POST['update_posts'])	{ $options['update_posts'] = '1'; } else	{ $options['update_posts'] = ''; }
		if($_POST['update_pages'])	{ $options['update_pages'] = '1'; } else	{ $options['update_pages'] = '0'; }
		update_option($pluginname.'_settings', $options);
	}

	?>
	<form method="post" action="#" id="posts_pdf_converter_form" name="general_settings_pdf_converter_form">
	<div class="wrap">
		<div class="icon32" id="icon-plugins"><br></div>
		<h2>General Settings</h2>
		<br/>
		<table class="form-table"><tbody>
		<tr valign="top">
			<th scope="row">Publish / Update Option</th>
			<td>
				<div>
					<?php

					$settings = get_option($pluginname.'_settings');
					$update_posts = $settings['update_posts'];
					$update_pages = $settings['update_pages'];
					
					if($update_posts == '1')	{ $update_posts_checked = 'checked="checked"'; } else	{ $update_posts_checked = ''; }
					if($update_pages == '1')	{ $update_pages_checked = 'checked="checked"'; } else	{ $update_pages_checked = ''; }
					?>
					<input <?php echo $update_posts_checked; ?> type="checkbox" value="1" id="update_posts" name="update_posts">Posts
				</div>
				<div>
					<input <?php echo $update_pages_checked; ?> type="checkbox" value="1" id="update_pages" name="update_pages">Pages
				</div>
				<div>
					<span class="description">Allow post or pages to be convert to pdf and send out when publish / update button is click.</span>
				</div>
			</td>
		</tr>	
		
		<!--
		<tr valign="top">
			<th scope="row"><label for="blogname">Site Title</label></th>
			<td>
				<input type="text" class="regular-text" value="Wordpress 331" id="blogname" name="blogname">
			</td>
		</tr>
		-->
		</tbody></table>
		<input type="submit" class="button-primary" value="Save Changes" style="cursor:pointer;" name="submitPDFoption">
	</div>
	</form>
	<?php	
}