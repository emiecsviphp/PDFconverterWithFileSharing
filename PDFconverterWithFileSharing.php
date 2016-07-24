<?php
/*
Plugin Name: PDF Converter With File Sharing
Plugin URI: 
Description: A plugin that allow the administrator to convert a post or pages to PDF and then being sent to some file sharing sites like http://www.scribd.com/developers and http://www.slideshare.net/developers
Author: James Bolongan
Version: 1.0
*/

include('generalsettings.php');
include('apis_account_page.php');
include('posts_pdf_converter.php');
include('pages_pdf_converter.php');
include('posts_status.php');
include('pages_status.php');
include('sendtofilesharingsite.php');


global $wpdb, $table_prefix,$current_user;


$sql = "SHOW TABLES FROM ".DB_NAME."";
$result = mysql_query($sql);

if (!$result) {
	echo "DB Error, could not list tables\n";
	echo 'MySQL Error: ' . mysql_error();
	exit;
}

while ($row = mysql_fetch_row($result)) {
	$tables[] = $row[0];
	$parsetable = explode('_',$row[0]);
	$tablenames[] = $parsetable[1];
}

$table_returned_urls = $table_prefix.'pdf_returned_urls';
if (!in_array($table_returned_urls, $tables)) {		
	$result = mysql_query("
					CREATE TABLE IF NOT EXISTS `$table_returned_urls` (
					  `id` bigint(20) NOT NULL auto_increment,
					  `post_id` int(20) default NULL,
					  `sn` varchar(255) NOT NULL,
					  `sn_username` varchar(255) NOT NULL,
					  `returnurl` varchar(255) NOT NULL,
					  PRIMARY KEY  (`id`),
					  KEY `postid` (`post_id`)
					)ENGINE=InnoDB  DEFAULT CHARSET=utf8;"
				);
	if (!$result) {
		die('Invalid query: ' . mysql_error());
	}
}

add_action('admin_menu', 'exponentmediapdfconverter');

function exponentmediapdfconverter() {
	add_menu_page('PDF Converter', 'PDF Converter', 'administrator', 'exponent-media-pdf-converter', 'general_settings_pdf_converter_function');
	add_submenu_page( 'exponent-media-pdf-converter', 'General Settings', 'General Settings', 'administrator', 'exponent-media-pdf-converter', 'general_settings_pdf_converter_function');
	add_submenu_page( 'exponent-media-pdf-converter', 'API\'s Account Page', 'API\'s Account Page', 'administrator', 'apis-account-page-pdf-converter', 'apis_account_page_pdf_converter_function');
	add_submenu_page( 'exponent-media-pdf-converter', 'Posts PDF Converter', 'Posts PDF Converter', 'administrator', 'posts-pdf-converter', 'posts_pdf_converter_function');
	add_submenu_page( 'exponent-media-pdf-converter', 'Pages PDF Converter', 'Pages PDF Converter', 'administrator', 'pages-pdf-converter', 'pages_pdf_converter_function');
	add_submenu_page( 'exponent-media-pdf-converter', 'Posts Status', 'Posts Status', 'administrator', 'status-posts-pdf-converter', 'status_posts_pdf_converter_function');
	add_submenu_page( 'exponent-media-pdf-converter', 'Pages Status', 'Pages Status', 'administrator', 'status-pages-pdf-converter', 'status_pages_pdf_converter_function');
}


add_action('admin_init', 'addApiOptionsPDFConverter');
function addApiOptionsPDFConverter() {

    $pluginname = 'exponent_media_pdf_converter';
	add_option($pluginname.'_settings',array());

}
