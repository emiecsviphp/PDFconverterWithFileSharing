<?php
/* 
	This file is part of the plugin of exponentmediapdfconverter
*/

function apis_account_page_pdf_converter_function()	{
	global $wpdb, $table_prefix,$current_user;
	
    $pluginname = 'exponent_media_pdf_converter';
	$options = get_option($pluginname.'_settings');
	
	if(isset($_POST['submitPDFAPIoption']))	{
		$options['scribd_apikey'] = $_POST['scribd_apikey'];
		$options['scribd_secret'] = $_POST['scribd_secret'];

		$options['slideshare_apikey'] = $_POST['slideshare_apikey'];
		$options['slideshare_secret'] = $_POST['slideshare_secret'];
		$options['slideshare_username'] = $_POST['slideshare_username'];
		$options['slideshare_password'] = $_POST['slideshare_password'];

		$options['sendspace_apikey'] = $_POST['sendspace_apikey'];
		$options['sendspace_user_name'] = $_POST['sendspace_user_name'];
		$options['sendspace_password'] = $_POST['sendspace_password'];
		
		$sendspacetokens = processSendSpaceToken($_POST['sendspace_apikey'],$_POST['sendspace_user_name'],$_POST['sendspace_password']);

		if(count($sendspacetokens) > 0)	{
			foreach($sendspacetokens as $index => $value)	{
				$options['sendspace_'.$index] = $value;
			}
		}
		
		$options['box_apikey'] = $_POST['box_apikey'];
		$options['box_ticket'] = $_POST['box_ticket'];
		$options['box_auth_token'] = $_POST['box_auth_token'];	
		$options['crocko_apikey'] = $_POST['crocko_apikey'];
		
		$options['ifileit_username'] = $_POST['ifileit_username'];
		$options['ifileit_password'] = $_POST['ifileit_password'];
			
		update_option($pluginname.'_settings', $options);

	}
	
	if(($_REQUEST['ticket'] != '') && ($_REQUEST['auth_token'] != ''))	{
		$options['box_ticket'] = $_REQUEST['ticket'];
		$options['box_auth_token'] = $_REQUEST['auth_token'];
		update_option($pluginname.'_settings', $options);
	}
	
	$settings = get_option($pluginname.'_settings');
	
	//echo '<pre>';
	//print_r($settings);
	//echo '</pre>';
	
	?>
	<form method="post" action="#" id="posts_pdf_converter_form" name="general_settings_pdf_converter_form">
	<div class="wrap">
		<div class="icon32" id="icon-plugins"><br></div>
		<h2>Application Settings</h2>
		<br/>
		<?php
		if(isset($_POST['submitPDFAPIoption']))	{
			echo '<div class="updated">Application Settings successfully saved.</div>';
			echo '<br/>';
		}
		?>
		<div>
			<span style="font-weight:bold;font-size:15px">Scribd</span>
			<table class="form-table"><tbody>
			<tr valign="top">
				<th scope="row">API Key</th>
				<td>
					<input type="text" class="regular-text" value="<?php echo $settings['scribd_apikey']; ?>" id="scribd_apikey" name="scribd_apikey">
					<a target="_blank" href="http://www.scribd.com/developers/signup_api">Sign Up for an API account</a> or <a target="_blank" href="http://www.scribd.com/account/edit#api">Get your API key in your account</a>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">API Secret</th>
				<td>
					<input type="text" class="regular-text" value="<?php echo $settings['scribd_secret']; ?>" id="scribd_secret" name="scribd_secret">
				</td>
			</tr>
			</tbody></table>
		</div>
		<br/>
		<div>
			<span style="font-weight:bold;font-size:15px">Slide Share</span>
			<table class="form-table"><tbody>
			<tr valign="top">
				<th scope="row">API Key</th>
				<td>
					<input type="text" class="regular-text" value="<?php echo $settings['slideshare_apikey']; ?>" id="slideshare_apikey" name="slideshare_apikey">
					<a target="_blank" href="http://www.slideshare.net/developers/applyforapi">Apply for API Key</a>.
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Shared Secret</th>
				<td>
					<input type="text" class="regular-text" value="<?php echo $settings['slideshare_secret']; ?>" id="slideshare_secret" name="slideshare_secret">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Username</th>
				<td>
					<input type="text" class="regular-text" value="<?php echo $settings['slideshare_username']; ?>" id="slideshare_username" name="slideshare_username">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Password</th>
				<td>
					<input type="password" class="regular-text" value="<?php echo $settings['slideshare_password']; ?>" id="slideshare_password" name="slideshare_password">
				</td>
			</tr>
			</tbody></table>
		</div>
		<br/>
		<div>
			<span style="font-weight:bold;font-size:15px">Sendspace</span>
			<table class="form-table"><tbody>
			<tr valign="top">
				<th scope="row">API Key</th>
				<td>
					<input type="text" class="regular-text" value="<?php echo $settings['sendspace_apikey']; ?>" id="sendspace_apikey" name="sendspace_apikey">
					<a target="_blank" href="http://www.sendspace.com/dev_apikeys.html">Create a sendspace API Key</a>.
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Username</th>
				<td>
					<input type="text" class="regular-text" value="<?php echo $settings['sendspace_user_name']; ?>" id="sendspace_user_name" name="sendspace_user_name">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Password</th>
				<td>
					<input type="password" class="regular-text" value="<?php echo $settings['sendspace_password']; ?>" id="sendspace_password" name="sendspace_password">
				</td>
			</tr>
			</tbody></table>
		</div>
		<br/>
		<div>
			<span style="font-weight:bold;font-size:15px">Box</span>
			<table class="form-table"><tbody>
			<tr valign="top">
				<th scope="row">API Key</th>
				<td>
					<input type="text" class="regular-text" value="<?php echo $settings['box_apikey']; ?>" id="box_apikey" name="box_apikey">
					<input type="hidden" class="regular-text" value="<?php echo $settings['box_ticket']; ?>" id="box_ticket" name="box_ticket">
					<input type="hidden" class="regular-text" value="<?php echo $settings['box_auth_token']; ?>" id="box_auth_token" name="box_auth_token">
					<a target="_blank" href="http://www.box.com/developers/services/edit/">Create a box application and get your api key</a>.
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<?php
					$send_box_apikey = $settings['box_apikey'];
					$send_box_get_ticket_url = 'https://www.box.net/api/1.0/rest?action=get_ticket&api_key='.$send_box_apikey.'';
					$send_box_ticket_data = @file_get_contents($send_box_get_ticket_url);
					$send_box_ticket_data = simplexml_load_string($send_box_ticket_data);
					$send_box_ticket = $send_box_ticket_data->ticket;
					$send_box_auth_ticket_url = 'https://www.box.net/api/1.0/auth/'.$send_box_ticket.'';
					?>
					<script src="http://code.jquery.com/jquery-latest.js"></script>
					<script language="JavaScript">
						var wpurl = '<?php bloginfo('wpurl'); ?>';
						function authorizeBox()	{
							jQuery.noConflict();
							jQuery.ajax({
								type: "POST",
								url: wpurl + "/wp-content/plugins/exponentmediapdfconverter/processpdf.php",
								data: '&task=processAuthorizeBox',
								beforeSend: function() {
									jQuery("#loaddata").html("<img src='"+wpurl+"/wp-content/plugins/exponentmediapdfconverter/images/loading.gif' alt='loading...' title='loading...' style='color:#33cc33'/>");
								},
								success: function(response){
									//jQuery("#loaddata").html(response);
									if(response == 'https://www.box.net/api/1.0/auth/')	{
										alert('Fill-up the box api key first and click the "Save Changes" button');
										jQuery("#loaddata").html('<a href="#" onclick="authorizeBox()">Authorize box.com Application</a>');
									}
									else	{
										//window.location.href='https://www.box.net/api/1.0/auth/'+response;
										//jQuery("#loaddata").html('<span id="loaddata"><a href="'+response+'">Authorize box.com Application</a></span>');
										window.location.href = response;
									}
								}
							});
						}
					</script>
					<span id="loaddata"><a href="#" onclick="authorizeBox()">Authorize box.com Application</a></span>
					
					<!--<a href="#" onclick="authorizeBox()">Authorize box.com Application</a>-->
				</th>
				<td></td>
			</tr>
			</tbody></table>
		</div>
		<br/>
		<div>
			<span style="font-weight:bold;font-size:15px">Crocko</span>
			<table class="form-table"><tbody>
			<tr valign="top">
				<th scope="row">API Key</th>
				<td>
					<input type="text" class="regular-text" value="<?php echo $settings['crocko_apikey']; ?>" id="crocko_apikey" name="crocko_apikey">
					<a href="http://www.crocko.com/accounts/profile/misc">Sign up and get you api key in your account</a>
				</td>
			</tr>
			</tbody></table>
		</div>
		<br/>
		<div>
			<span style="font-weight:bold;font-size:15px">Ifile.it</span>
			<table class="form-table"><tbody>
			<tr valign="top">
				<th scope="row">Username</th>
				<td>
					<input type="text" class="regular-text" value="<?php echo $settings['ifileit_username']; ?>" id="ifileit_username" name="ifileit_username">
					<a href="https://secure.ifile.it/account-signup.html">Sign up free account</a>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Password</th>
				<td>
					<input type="password" class="regular-text" value="<?php echo $settings['ifileit_password']; ?>" id="ifileit_password" name="ifileit_password">
				</td>
			</tr>
			</tbody></table>
		</div>
		
		
		<input type="submit" class="button-primary" value="Save Changes" style="cursor:pointer;" name="submitPDFAPIoption">
	</div>
	</form>
	<?php	
}


function processSendSpaceToken($sendspace_apikey,$sendspace_user_name,$sendspace_password)	{

    $pluginname = 'exponent_media_pdf_converter';
	$options = get_option($pluginname.'_settings');

	$get_token_url = 'http://api.sendspace.com/rest/?method=auth.createtoken&api_key='.$sendspace_apikey.'&api_version=1.0&response_format=xml&app_version=0.1';
	$data_token = @file_get_contents($get_token_url);
	$data_token = simplexml_load_string($data_token);		
	$sendspace_token = $data_token->token;
	
	$sendspace_md5_password = strtolower(md5($sendspace_password));
	$sendspace_tokened_password = strtolower(md5($sendspace_token.$sendspace_md5_password));
	$get_sessionkey_url = 'http://api.sendspace.com/rest/?method=auth.login&token='.$sendspace_token.'&user_name='.$sendspace_user_name.'&tokened_password='.$sendspace_tokened_password.'';
	$data_sessionkey = @file_get_contents($get_sessionkey_url);		
	$data_sessionkey = simplexml_load_string($data_sessionkey);
	$sendspace_session_key = $data_sessionkey->session_key;
		
	$get_upload_info_url = 'http://api.sendspace.com/rest/?method=upload.getinfo&session_key='.$sendspace_session_key.'&speed_limit=0';
	$data_upload_info = @file_get_contents($get_upload_info_url);
	$data_upload_info = simplexml_load_string($data_upload_info);
	$first_xml_array = get_object_vars ($data_upload_info); 		
	$second_xml_array = get_object_vars($first_xml_array['upload']);
	
	$sendspaceimportantvalues = array();
	if(count($second_xml_array) > 1)	{
		foreach($second_xml_array as $value)	{
			$sendspaceimportantvalues['url'] = $value['url'];
			$sendspaceimportantvalues['max_file_size'] = $value['max_file_size'];
			$sendspaceimportantvalues['upload_identifier'] = $value['upload_identifier'];
			$sendspaceimportantvalues['extra_info'] = $value['extra_info'];		
		}
	}
	
	return $sendspaceimportantvalues;
}