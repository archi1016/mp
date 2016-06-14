<?php

require('config.php');
require('define.php');

date_default_timezone_set('Asia/Taipei');

$HTML = '';
$THIS_PHP_FILE = basename($_SERVER['SCRIPT_NAME']);
$THIS_WEB_URL = $THIS_PHP_FILE;

function GOTO($new_url) {
	PAGE_ADD('<html><head><meta http-equiv="refresh" content="0; url='.$new_url.'" /></head></html>');
	PAGE_OUTPUT();
	exit();
}

function ERROR_STOP($error_code) {
	require('error.php');

	PAGE_ADD('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
	PAGE_ADD('<html xmlns="http://www.w3.org/1999/xhtml"><head>');
	PAGE_ADD('<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />');
	PAGE_ADD('<title>Error - '.COMPANY_NAME.'</title>');
	PAGE_ADD('<link rel="stylesheet" type="text/css" href="theme.css" />');
	PAGE_ADD('</head><body>');
	PAGE_ADD('<div class="error">'.$ERROR_MESSAGE[$error_code].'<br><br><br><button onclick="history.back();">Back</button></div>');
	PAGE_OUTPUT();

	exit();
}

function PAGE_ADD($l) {
	global $HTML;

	$HTML .= $l."\n";
}

function PAGE_INIT($title) {
	PAGE_ADD('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
	PAGE_ADD('<html xmlns="http://www.w3.org/1999/xhtml"><head>');
	PAGE_ADD('<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />');
	PAGE_ADD('<meta name="viewport" content="width=1024" />');
	PAGE_ADD('<title>'.$title.' - '.PRODUCT_NAME.'</title>');
	PAGE_ADD('<link rel="stylesheet" type="text/css" href="theme.css" />');
	PAGE_ADD('<link rel="shortcut icon" type="image/x-icon" href="logo/logo.ico" />');
	PAGE_ADD('<link rel="icon" type="image/png" href="logo/logo_16.png" />');
	PAGE_ADD('<link rel="apple-touch-icon-precomposed" href="logo/logo_128.png" />');
	PAGE_ADD('</head><body>');
	PAGE_ADD('<h1>'.PRODUCT_NAME.'</h1>');
}

function PAGE_FOOTER() {
	global $TAB_INFO;
	
	PAGE_ADD('<div class="footer">');
	PAGE_ADD(COMPANY_NAME);
	PAGE_ADD('</div>');
}

function PAGE_OUTPUT() {
	global $HTML;

	PAGE_ADD('</body></html>');	
	header('Content-Length: '.strlen($HTML));
	echo $HTML;
}

function RETURN_STR_GET($key) {
	if (isset($_GET[$key])) {
		return $_GET[$key];
	} else {
		return '';
	}
}

function RETURN_INT_GET($key) {
	if (isset($_GET[$key])) {
		return (int) $_GET[$key];
	} else {
		return 0;
	}
}

function LOAD_TEXT_FILE($fp, &$t) {
	if (file_exists($fp)) {
		$t = file_get_contents($fp);
	} else {
		$t = '';
	}
	return ($t != '');
}

function LOAD_TEXT_TABLE($fp, &$t) {
	$t = NULL;
	$c = '';
	if (LOAD_TEXT_FILE($fp, $c)) {
		$c = str_replace('&', '&amp;', $c);
		return LOAD_TEXT_TABLE_CORE($c, $t);
	} else {
		return FALSE;
	}
}

function LOAD_TEXT_TABLE_CORE(&$c, &$t) {
	$b = 0;
	$c = str_replace("\r", '', $c);
	$l = split("\n", $c);
	$ls = count($l);
	$i = 0;
	while ($i < $ls) {
		if ($l[$i] != '') {
			$r = split("\t", $l[$i]);
			$rs = count($r);
			$j = 0;
			while ($j < $rs) {
				$t[$b][$j] = $r[$j];
				++$j;
			}
			++$b;
		}
		++$i;
	}
	return is_array($t);
}

?>