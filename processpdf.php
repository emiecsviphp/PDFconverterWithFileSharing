<?php

require('./../../../wp-load.php');

// for tcpdf API
require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');

require_once 'scribd.php';

global $wpdb, $table_prefix,$current_user;
get_currentuserinfo();
$user_id = $current_user->ID;

date_default_timezone_set('UTC');
$mysqldate = date( 'Y-m-d H:i:s');	

$task = trim(addslashes($_POST['task']));
$wpurl = get_bloginfo('wpurl');


if($_POST['task'] == 'processPDF')	{
	$ids = $_POST['values'];
	$ids = explode(',',$ids);
	echo '<br/>';
	foreach($ids as $id)	{
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

			if($_POST['postype'] == 'posts')	{
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
			$pdf->Output('pdf files/'.$title.'.pdf', 'F');
			
			$fileLocal = WP_PLUGIN_DIR.'/exponentmediapdfconverter/pdf files/'.$title.'.pdf';
			
			$scribd = sendToScribd($title,$wpurl);
			$slideShare = sendSlideShare($title, $wpurl, true);
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

			$return_url_slideshare = sendSlideShare($title, $wpurl, true);
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
			
			echo '<span style="font-weight:bold;">'.$title.'</span>';
			echo '<br/>';
			echo '<a href="'.$return_url_box.'" target="_blank">'.$return_url_box.'</a>';
			echo '<br/>';
			echo '<a href="'.$return_url_scribd.'" target="_blank">'.$return_url_scribd.'</a>';
			echo '<br/>';
			echo '<a href="'.$return_url_slideshare.'" target="_blank">'.$return_url_slideshare.'</a>';
			echo '<br/>';
			echo '<a href="'.$return_url_sendspace.'" target="_blank">'.$return_url_sendspace.'</a>';
			echo '<br/>';
			echo '<a href="'.$return_url_sendCrocko.'" target="_blank">'.$return_url_sendCrocko.'</a>';
			echo '<br/>';
			echo '<a href="'.$return_url_ifileit.'" target="_blank">'.$return_url_ifileit.'</a>';
			echo '<br/>';

			//$fourshared = sendToFourShared($title,$fileLocal);						
			//sendToFourShared($title,$fileLocal);
			//sendEdocr($title,$fileLocal);
		}
		echo '<br/>';
	}
}

if($_POST['task'] == 'processAuthorizeBox')	{
	global $wpdb, $table_prefix,$current_user,$post_id;
    $pluginname = 'exponent_media_pdf_converter';
	$settings = get_option($pluginname.'_settings');
	
	$send_box_apikey = $settings['box_apikey'];
	$send_box_get_ticket_url = 'https://www.box.net/api/1.0/rest?action=get_ticket&api_key='.$send_box_apikey.'';
	$send_box_ticket_data = @file_get_contents($send_box_get_ticket_url);
	$send_box_ticket_data = simplexml_load_string($send_box_ticket_data);
	$send_box_ticket = $send_box_ticket_data->ticket;
	$send_box_auth_ticket_url = 'https://www.box.net/api/1.0/auth/'.$send_box_ticket.'';
	echo $send_box_auth_ticket_url;

}

function sendToFourShared($title,$fileLocal)	{

	$user_login = "emiecs_viphp";
	$user_password = "csemie";
	 
	$fileName = $title.'.pdf';
	$fileSize = strlen(file_get_contents($fileLocal));

	$client = new SoapClient("https://api.4shared.com/jax2/DesktopApp?wsdl");
	$session = $client->createUploadSessionKey($user_login, $user_password, -1);
	$datacenter = $client->getNewFileDataCenter($user_login, $user_password, -1);
	if ($datacenter <= 0) die("<b>Error: Something went wrong</b>");
	$uploadUrl = $client->getUploadFormUrl($datacenter, $session);

	$fileId = $client->uploadStartFile($user_login, $user_password, -1, $fileName, $fileSize);

	$post_params = array(
		'resumableFileId' => $fileId,
		'resumableFirstByte' => 0,
		'FilePart' => '@'.$fileLocal
	);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $uploadUrl);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
	$req = curl_exec($ch);
	
	$finish = $client->uploadFinishFile($user_login, $user_password, $fileId, md5_file($fileLocal));
	
	/*
	echo '<pre>';
	print_r($session);
	echo '<br/>';
	print_r($datacenter);
	echo '<br/>';
	print_r($fileId);
	echo '<br/>';
	print_r($finish);
	echo '<br/>';
	print_r($req);
	echo '</pre>';
	*/
	
	return $finish;
	
}

function sendEdocr($title,$fileLocal)	{
	
	require_once("phpEdocr/phpEdocr.php");

	define(CONSUMER_KEY , 		"68092168a220054bcbfb3e1d2b5dbe55");
	define(CONSUMER_SECRET , 	"6f8a09dcc202e3506c126f216b29d49c");

	$obj_phpEdocr = new phpEdocr(CONSUMER_KEY,CONSUMER_SECRET);

	$api_response = $obj_phpEdocr->upload_document(
		$fileLocal,
		array(
			"title" 				=> $title,
			"tags"					=> $title,
			"description" 			=> 'This file is uploaded using the software called exponent web media pdf converter'
			//"groups" 				=> $_POST['groups']
		)
	);
	
}