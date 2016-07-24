<?php
/* 
	This file is part of the plugin of exponentmediapdfconverter
*/

require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');
require_once 'scribd.php';


$pluginname = 'exponent_media_pdf_converter';
$settings = get_option($pluginname.'_settings');
$update_posts = $settings['update_posts'];
$update_pages = $settings['update_pages'];

if($update_posts == '1')	{
	add_action('edit_post', 'proceessPDF');
}
if($update_pages == '1')	{ 
	add_action('edit_post', 'proceessPDF');
}

function proceessPDF()	{
	
	global $wpdb, $table_prefix,$current_user,$post_id;
	$wpurl = get_bloginfo('wpurl');

	$id = $post_id;
	if(is_numeric($id))	{

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		$pdf->setFontSubsetting(true);
		$pdf->AddPage();
		
		$postType = get_post_type($id);
		if($postType == 'post')	{
			$args = array( 'p' => $id );
		}
		else	{
			$args = array( 
						'p' => $id,
						'post_type' => 'page'
					);
		}
		
		$the_query = new WP_Query( $args );
		while ( $the_query->have_posts() ) {
			$the_query->the_post();
			$content = apply_filters('the_content', get_the_content()); 
			$title = get_the_title();
		}

		$html = '<h1>'.$title.'</h1>';
		$html .= $content;
		
		$pdf->writeHTML($html, true, 0, true, 0);
		$pdf->Output(WP_PLUGIN_DIR.'/exponentmediapdfconverter/pdf files/'.$title.'.pdf', 'F');
		
		$fileLocal = WP_PLUGIN_DIR.'/exponentmediapdfconverter/pdf files/'.$title.'.pdf';
		
		$scribd = sendToScribd($title,$wpurl);
		$sendTosendspace = sendTosendspace($title,$fileLocal);

		/* parsing the data for sendbox */

		$sendBox = sendBox($title,$fileLocal);
		$files = $sendBox[1];
		
		foreach($sendBox as $newsendBoxA)	{
			$files = $newsendBoxA;
		}
		
		foreach($files as $newsendBoxB)	{
			$attributes = $newsendBoxB;
		}
		
		foreach($attributes as $attribute)	{
			$attribute = $attribute;
		}

		$public_name = $attribute['public_name'];
		$return_url_box = 'http://www.box.com/s/'.$public_name.'';		
		$return_url_scribd = 'http://www.scribd.com/doc/'.$scribd['doc_id'].'/';
		$return_url_sendspace = 'http://www.sendspace.com/file/'.$sendTosendspace.'';

		$return_url_slideshare = sendSlideShare($title,$wpurl,false);
		$return_url_sendCrocko = sendCrocko($fileLocal);
		$return_url_ifileit = ifileit($fileLocal);
		
		$return_urls = array();
		$return_urls['box'] = $return_url_box;
		$return_urls['scribd'] = $return_url_scribd;
		$return_urls['slideshare'] = $return_url_slideshare;
		$return_urls['sendspace'] = $return_url_sendspace;
		$return_urls['crocko'] = $return_url_sendCrocko;
		$return_urls['ifileit'] = $return_url_ifileit;

		insertReturnUrlTable($id,$return_urls);
	}
}

function insertReturnUrlTable($id,$return_urls) {
	global $wpdb, $table_prefix,$current_user;

	foreach ($return_urls as $index => $key)	{
		$wpdb->query("
			INSERT INTO 
				`".$table_prefix."pdf_returned_urls`
			(`post_id`, `sn`, `sn_username`, `returnurl`)
				VALUES
			('".$id."','".$index."','','".$key."')
		");	
	}
}

function sendToScribd($title,$wpurl)	{

    $pluginname = 'exponent_media_pdf_converter';
	$settings = get_option($pluginname.'_settings');

	$scribd_api_key = $settings['scribd_apikey'];
	$scribd_secret = $settings['scribd_secret']; 
	
	$scribd = new Scribd($scribd_api_key, $scribd_secret);
	
	$url = $wpurl.'/wp-content/plugins/exponentmediapdfconverter/pdf files/'.$title.'.pdf';	
	$url = str_replace(' ', '%20', $url);
	
	$doc_type = null;
	$access = "public";
	$rev_id = null; 

	$data = $scribd->uploadFromUrl($url, $doc_type, $access, $rev_id); 
	
	return $data;
}

function sendSlideShare($title,$wpurl,$fromAJAX)	{

    $pluginname = 'exponent_media_pdf_converter';
	$settings = get_option($pluginname.'_settings');;

	$url = $wpurl.'/wp-content/plugins/exponentmediapdfconverter/pdf files/'.$title.'.pdf';	
	$url = str_replace(' ', '%20', $url);

	$ts=time();
	$hash=sha1($settings['slideshare_secret'].$ts);
	
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
    curl_setopt($ch, CURLOPT_URL, "http://www.slideshare.net/api/2/upload_slideshow?api_key=".$settings['slideshare_apikey']."&ts=$ts&hash=$hash");
    curl_setopt($ch, CURLOPT_POST, true);
	
    $post = array(
        "username" => $settings['slideshare_username'],
		"password" => $settings['slideshare_password'],
		"slideshow_title" => $title,
		"slideshow_srcfile" => 'test.ppt',
		"upload_url" => $url,
		"make_src_public" => 'Y',
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
    $response = curl_exec($ch);
	
	$xml = simplexml_load_string($response);
	$json = json_encode($xml);
	$array = json_decode($json,TRUE);

	foreach ($array as $SlideShowID)	{
		$SlideShowID = $SlideShowID;
	}
	
	$url_title = strtolower($title);
	$url_title = str_replace(" ", "-", $url_title);	

	if($fromAJAX == true)	{
		$url = $url_title.'-'.$SlideShowID;
	}
	else	{
		$url = $url_title;
	}
	
	$url = preg_replace('/[^a-zA-Z0-9_%\[().\]\\/-]/s', '', $url);
	
	$return_url_slideshare = 'http://www.slideshare.net/'.$settings['slideshare_username'].'/'.$url.'';
	
	return $return_url_slideshare;
	
}

function sendTosendspace($title,$fileLocal)	{

    $pluginname = 'exponent_media_pdf_converter';
	$settings = get_option($pluginname.'_settings');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");	
	curl_setopt($ch, CURLOPT_URL, $settings['sendspace_url']);
    curl_setopt($ch, CURLOPT_POST, true);
    $post = array(
        "MAX_FILE_SIZE" => $settings['sendspace_max_file_size'],
		"UPLOAD_IDENTIFIER" => $settings['sendspace_upload_identifier'],
		"extra_info" => $settings['sendspace_extra_info'],
		"userfile" => "@".$fileLocal,
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
    $response = curl_exec($ch);
	
	$response = explode('=',$response);
	
	return $response[2];
	
}

function sendBox($title,$fileLocal)	{

    $pluginname = 'exponent_media_pdf_converter';
	$settings = get_option($pluginname.'_settings');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
    curl_setopt($ch, CURLOPT_URL, 'https://upload.box.net/api/1.0/upload/'.$settings['box_auth_token'].'/0');
    curl_setopt($ch, CURLOPT_POST, true);
	
    $post = array(
		"file" => '@'.$fileLocal,
		"share" => '1',
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
    $response = curl_exec($ch);
	
	$xml = simplexml_load_string($response);
	$json = json_encode($xml);
	$array = json_decode($json,TRUE);

	return $array;
}

function sendCrocko($fileLocal)	{

    $pluginname = 'exponent_media_pdf_converter';
	$settings = get_option($pluginname.'_settings');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/atom+xml', "Authorization: ".$settings['crocko_apikey'].""));
    curl_setopt($ch, CURLOPT_URL, 'http://api.crocko.com/files');
    curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    $post = array(
		"upload" => '@'.$fileLocal,
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 
    $response = curl_exec($ch);
	
	$headers = substr($response,0,curl_getinfo($ch,CURLINFO_HEADER_SIZE));
	$body = substr($response,curl_getinfo($ch,CURLINFO_HEADER_SIZE));
	
	$data = simplexml_load_string($body);
	
	$return_url = $data->entry->link[0]['href'];
	
	return $return_url;
}

function ifileit($fileLocal)	{
    $pluginname = 'exponent_media_pdf_converter';
	$settings = get_option($pluginname.'_settings');
	
	require_once( 'IfileApi.php' );

	$IfileApi = new IfileApi();
	$filepath =	$fileLocal;

	$username = $settings['ifileit_username'];
	$password = $settings['ifileit_password'];

	try {		
		$apikey = $IfileApi->fetchApiKey($username, $password);
	}
	catch ( IfileApiException $e){
		$e->getMessage();
	}
	
	try {	
		$upload = $IfileApi->upload(
			$filepath,
			$apikey
		);
		$retun_url = 'http://ifile.it/'.$upload['ukey'].'/'.rawurlencode( $upload['name'] )."\n\n";
		//print print_r( $upload,true );
	}
	catch ( IfileApiException $e){
		$e->getMessage();
	}
	
	return $retun_url;
}