<?php

function srvIsPHPFileUpload($a) {
	$isPHPFile = false;
	foreach($a as $key => $value) {
		if (is_array($value)) {
			$isPHPFile |= srvIsPHPFileUpload($value);
		}

		if (is_string($value) && strpos(strtolower($value), '.php'))
			$isPHPFile |= true;
	}

	return $isPHPFile;
}


/**
 * All code is in a function to avoid exposing variables to PHP application.
 */
function srvPHPProtect() 
{
	global $HTTP_RAW_POST_DATA;

  extract(require(__DIR__ . '/../configs/db.php'));

  $host = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
  $uri = (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
 
	$url = "http://{$host}{$uri}";

	$flag = 0;

	// Block execution of any script under upload folder.
	if (strpos('wp-content/uploads', $_SERVER['SCRIPT_FILENAME']) !== false)
	  $flag = 1;

	else if (strpos('wp-content/plugins/tinymce-advanced/uninstall.php', $url) !== false)
	  $flag = 2;

	else if (strpos('wp-content/plugins/backwpup/job/show_working.php', $url) !== false)
	  $flag = 3;

	else if (strpos('wp-content/plugins/worker/src/MWP/Action/Exception.php', $url) !== false)
	  $flag = 4;

	else if (strpos('wp-content/plugins/revslider/inc_php/revslider_widget.class.php', $url) !== false)
	  $flag = 5;

	else if (strpos('wp-content/plugins/contact-form-7/modules/special-mail-tags.php', $url) !== false)
	  $flag = 6;

	else if (strpos('wp-content/plugins/sitepress-multilingual-cms/menu/taxonomy-menu.php', $url) !== false)
	  $flag = 7;

	else if (strpos('wp-content/themes/twentyfifteen/config.php', $url) !== false)
	  $flag = 8;

	else if (strpos('wp-content/themes/twentythirteen/footer.php', $url) !== false)
	  $flag = 9;

	else if (preg_match('/web.php.xl/', $url))
	  $flag = 10;


	// Block $_POST[_mysite_download_skin] => ../../../../../wp-config.php
	else if (preg_match('/dl-skin.php/', $url) && isset($_POST['_mysite_download_skin']) && preg_match('/wp-config/', $_POST['_mysite_download_skin']))
		$flag = 13;

	// Block backdoor call
	else if (isset($_POST['a']) && isset($_POST['c']) && isset($_POST['p1']))
		$flag = 14;

	// Block upload of PHP file.
	else if (srvIsPHPFileUpload($_FILES))
		$flag = 15;

	// Block post with PHP code
	else if (strpos(print_r($_POST, true), '<?php') !== false)
	  $flag = 16;

	// Block malware by signature
	else if (strpos(print_r($_POST, true), 'JGVjb250PSJQRDl3YUhBZ0lBMEtKSG95TmowaWFtMXBUMEJ6ZUdoR2JrUStTbHh5TDNVclVtTkllak45WjF4dVpIdGVPQ0EvWlZaM2JGOVVYRnhjZEh4T05YRXBU') !== false)
		$flag = 19;

	// Block a malware upload thru revslider_ajax_action
	else if (isset($_POST['action']) && $_POST['action'] === 'revslider_ajax_action' && strpos(print_r($_FILES, true), 'revslider.zip') !== false)
		$flag = 20;

	// Block malware by signature
	else if (strpos(print_r($_POST, true), 'PD9waHAgIA0KJHoyNj0iam1pT0BzeGhGbkQ+SlxyL3UrUmNIejN9Z1xuZHteOCA') !== false)
		$flag = 21;

	// Block malware by signature
	else if (strpos(print_r($_POST, true), 'SADQdAlGcOS8gBADdRp4BhI7AApwAAAAAAAEAC') !== false)
		$flag = 22;

	// Block malware by signature
	else if (strpos(print_r($_POST, true), 'JGY1Mj0iVzRqPVBaNTwseWkqXFxhPyk3ISsgQkclZHpOb2Y') !== false)
		$flag = 26;

	// Block malware by signature
	else if (strpos(print_r($_POST, true), 'JG0yPSJFelhfNTt7OW88VSFaMEFZUVxuP1RqeUcvMVxyPTp') !== false)
		$flag = 28;

	// Block malware by signature
	else if (strpos(print_r($_POST, true), 'JGM2NT0iNVNCd2tLXHJGWmxzUTo2RHIpLSgwXjhdX1JKLkV') !== false)
		$flag = 29;

	// Block post with PHP code
	else if (strpos(print_r($_POST, true), 'get_magic_quotes_gpc') !== false)
	  $flag = 27;

	// Block post with PHP code
	else if (strpos(print_r($_POST, true), 'eval(') !== false)
	  $flag = 30;

  // Try to catch majority of posted maldet
  else if ($keys = array_keys($_POST) && strlen($keys[0]) == 7 && preg_match("/[a-zA-Z0-9+\/]{200,10000}/", $_POST[$keys[0]], $match)) {
	  $flag = 31;
  }

	// Block backdoor call
	else if (isset($_POST['act']) && isset($_POST['f']) && isset($_POST['ft']))
		$flag = 32;

	else if (strpos($url, '?gf_page=upload') !== false)
		$flag = 33;

	else if (strpos($url, 'xmlrpc.php') !== false && $_SERVER['REQUEST_METHOD'] == 'POST' && $_POST == array()) {
		global $HTTP_RAW_POST_DATA;
		$HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
		$flag = 34;
	}

	else if (strpos('wp-content/wp-includes/nav-menu.php', $url) !== false)
		$flag = 35;

	else if (
    preg_match('/.php/', $url) && 
    preg_match("/([a-zA-Z0-9\\+\/]{200,}[=]{1,2})/", print_r($_POST, true), $match) &&
    isset($_POST['SAMLResponse']) == false &&
    isset($_POST['public_key']) == false 
  )
		$flag = 36;

	else if (strpos('/options.php?cookie=1', $url) !== false)
		$flag = 37;
  
	else if (strpos('wp-content/themes/twentyfourteen/css/cache.php', $url) !== false)
		$flag = 38;

	else if (
    preg_match("/([a-zA-Z0-9=\\+\/]{80,})/", print_r($_POST, true), $match) && 
    preg_match("/function|scan|gzinflate|base64_decode|foreach|set_time_limit|strtolower|file_get_contents|error_reporting|ignore_user_abort|eval|preg_match_all|mysql_connect/i", base64_decode($match[1])) &&
    isset($_POST['SAMLResponse']) == false 
  )
		$flag = 39;

  else if (	
    preg_match("/str_replace|preg_match|set_time_limit|base64_decode|strtolower|file_get_contents|error_reporting|ignore_user_abort|preg_match_all|mysql_connect/i", print_r($_POST, true)) 
  )
	$flag = 44;
 
  else if (strpos('/wp-content/themes/sketch/', $url) !== false)
	$flag = 45;

  else if (strpos('/post.php', $url) !== false && isset($_POST['code']) && isset($_POST['pass']))
	$flag = 46;

  else if (strpos('/wp-includes/nav-menu.php', $url) !== false)
		$flag = 40;

  // > 20K base64 encoded post
	else if (
    preg_match("/([a-zA-Z0-9\\+\/]{20000,})/", print_r($_POST, true), $match)
  )
		$flag = 41;

	else if (strpos('/options.php?cookie=1', $url) !== false)
		$flag = 42;

	else if (strpos('/wp-content/plugins/woocommerce/cache.php', $url) !== false)
		$flag = 43;

	else if (
		isset($_POST['action']) && $_POST['action'] === 'wpmp_pp_ajax_call' &&
		isset($_POST['execute']) && $_POST['execute'] === 'wp_insert_user' &&
		isset($_POST['user_login']) && array_search($_POST['user_login'], array('wproot', 'rootuser'))
	) 
		$flag = 23;

	else if (strpos(print_r($_POST, true), 'eval(base64_decode') !== false)
		$flag = 24;
	
	else if (strpos($url, 'page=wysija_campaigns') !== false && isset($_POST['action']) && $_POST['action'] === 'themeupload')
		$flag = 25;


	else if (strpos(print_r($_POST, true), 'phpinfo()') !== false)
		$flag = 35;

	// Limit wordpress login per IP
	if (preg_match('/wp-login.php/', $url)) 
	{
		$db = mysql_connect($myHost, $myUser, $myPass);
		mysql_select_db($myDb, $db);

		$ip = $_SERVER['REMOTE_ADDR'];

		$escUrl = mysql_real_escape_string($url, $db);

		mysql_query(
			"DELETE FROM hack_login WHERE lastUpdate < DATE_SUB(NOW(), INTERVAL 10 MINUTE)"
		);

		mysql_query("
			INSERT INTO hack_login (`ip`, `lastUpdate`, `count`, `lastUrl`) VALUES (INET_ATON('{$ip}'), NOW(), 1, '{$escUrl}')
			ON DUPLICATE KEY UPDATE `count` = `count` + 1
		", $db);

		$result = mysql_query(
			"SELECT * FROM hack_login WHERE `ip` = INET_ATON('{$ip}') AND count >= 30 "
		);

		if (mysql_num_rows($result))
			$flag = 17;

		mysql_close($db);
	}


	if (strpos('/cgi-bin/', $url) !== false && strpos('set_time_limit(0)', print_r($_POST, true)) )
	  $flag = 18;

	// Log request to understand how you get hacked
	if ($logRequest && ($_POST || $_FILES || strlen(print_r($_GET, true)) > 150 || $flag !== 0))
	{
		$db = mysql_connect($myHost, $myUser, $myPass);
		mysql_select_db($myDb, $db);

		$url = mysql_real_escape_string($url, $db);
		$method = mysql_real_escape_string($_SERVER['REQUEST_METHOD'], $db);
		$post = mysql_real_escape_string(print_r($_POST, true), $db);
		$files = mysql_real_escape_string(print_r($_FILES, true), $db);
		$server = mysql_real_escape_string(print_r($_SERVER, true), $db);
		$rawpostdata = (isset($HTTP_RAW_POST_DATA) && $HTTP_RAW_POST_DATA ? mysql_real_escape_string(print_r($HTTP_RAW_POST_DATA, true), $db) : '');
		
		$flag = (int) $flag;
		$internal = (int) ($_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR']);
		
		mysql_query(
			"INSERT INTO hack_log (`created`, `url`, `method`, `post`, `files`, `server`, `rawpostdata`, `flag`, `internal`) ".
			"VALUES ".
			"(NOW(), '{$url}', '{$method}', '{$post}', '{$files}', '{$server}', '{$rawpostdata}', {$flag}, {$internal})"
		, $db);

		if (isset($_GET['yl']))
		{
			echo mysql_error($db);
		}

		mysql_close($db);
	}

	if ($flag !== 0 && $_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR']) 
	{
		print "Mebweb code {$flag} if you see this error page please contact info@mebagenceweb.com";
		die();
	}
}

srvPHPProtect();
