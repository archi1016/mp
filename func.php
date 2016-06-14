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
	PAGE_ADD('<meta http-equiv="Content-Type" content="text/html; charset=big5" />');
	PAGE_ADD('<title>錯誤 - '.COMPANY_NAME.'</title>');
	PAGE_ADD('<link rel="stylesheet" type="text/css" href="theme.css" />');
	PAGE_ADD('</head><body>');
	PAGE_ADD('<div class="error">'.$ERROR_MESSAGE[$error_code].'<br><br><br><button onclick="history.back();">回上一頁</button></div>');
	PAGE_OUTPUT();

	exit();
}

function PAGE_ADD($l) {
	global $HTML;

	$HTML .= $l."\n";
}

function PAGE_INIT_WITH_TAB() {
	global $THIS_PHP_FILE;
	global $TAB_INFO;
	global $SECTION_INFO;
	global $DOCUMENT_INFO;

	if (LOAD_TEXT_TABLE('tab.inf', $TAB_INFO)) {
		FIND_TAB_INDEX($TabIndex, $c_tab);
		$row_tab = &$TAB_INFO[$TabIndex];
		if (LOAD_TEXT_TABLE($row_tab[FIELD_TAB_ID].'/section.inf', $SECTION_INFO)) {
			FIND_SECTION_INDEX($SectionIndex, $c_section);
			$row_section = &$SECTION_INFO[$SectionIndex];
			$title = $row_section[FIELD_SECTION_TITLE];
			$DocumentIndex = -1;
			$c_document = 0;
			if (LOAD_TEXT_FILE($row_tab[FIELD_TAB_ID].'/'.$row_section[FIELD_SECTION_ID].'.html', $h)) {
				if ('DOCUMENT' == substr($h, 0, 8)) {
					LOAD_TEXT_TABLE($row_tab[FIELD_TAB_ID].'/'.$row_section[FIELD_SECTION_ID].'.html', $DOCUMENT_INFO);
					FIND_DOCUMENT_INDEX($DocumentIndex, $c_document);
					if (-1 == $DocumentIndex) {
						$h = '';
						if ('all' == RETURN_STR_GET('document')) {
							if ($c_document > 1) {
								//$title = '全部 @ '.$title;
								$h = '<h2>索引</h2><ol>';
								$i = 1;
								while ($i < $c_document) {
									$row = &$DOCUMENT_INFO[$i];
									$h .= '<li><a href="#CONTENT_'.$i.'">'.$row[FIELD_DOCUMENT_TITLE].'</a></li>';
									++$i;
								}
								$h .= '</ol>';
								$i = 1;
								while ($i < $c_document) {
									$row = &$DOCUMENT_INFO[$i];
									LOAD_TEXT_FILE($row_tab[FIELD_TAB_ID].'/'.$row[FIELD_DOCUMENT_ID].'.html', $t);
									$h .= '<a name="CONTENT_'.$i.'"><h2>'.$row[FIELD_DOCUMENT_TITLE].'</h2></a>'.$t.'<div class="top">[<a href="#header">回索引</a>]</div>';
									++$i;
								}
								$DocumentIndex = 0;
							}
						} else {
							$h = RETURN_PAGE_DOCUMENTS($c_document, $row_section);
						}
					} else {
						$row_document = &$DOCUMENT_INFO[$DocumentIndex];
						$title = $row_document[FIELD_DOCUMENT_TITLE].' @ '.$title;
						LOAD_TEXT_FILE($row_tab[FIELD_TAB_ID].'/'.$row_document[FIELD_DOCUMENT_ID].'.html', $h);
						$h = '<h2>'.$row_document[FIELD_DOCUMENT_TITLE].'</h2>'.$h;
					}
				}
			} else {
				$h = '<div class="null">(無)</div>';
			}
			$n = RETURN_PAGE_NAV($SectionIndex, $c_section, $DocumentIndex, $c_document);
			
			PAGE_INIT($title);
			if (PAGE_IS_PRINT_MODE()) {
				PAGE_ADD('<div class="content"><div class="print"><a name="header"><h1>'.$title.'</h1></a>'.$h.'</div></div>');
			} else {
				PAGE_ADD(RETURN_PAGE_TABS($TabIndex, $c_tab));
				//PAGE_ADD($n);
				PAGE_ADD('<table cellspacing="0" class="page"><tr valign="top">');
				PAGE_ADD('<td class="content"><div class="content"><a name="header"><h1>'.$row_section[FIELD_SECTION_TITLE].'</h1></a>'.$h.'</div></td>');
				PAGE_ADD('<td class="nav">'.RETURN_PAGE_SECTIONS($SectionIndex, $c_section).'</td>');
				PAGE_ADD('</tr></table>');
				PAGE_ADD($n);
			}
		} else {
			ERROR_STOP(ERROR_LOAD_SECTION);
		}
	} else {
		ERROR_STOP(ERROR_LOAD_TAB);
	}
}

function PAGE_INIT($title) {
	PAGE_ADD('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
	PAGE_ADD('<html xmlns="http://www.w3.org/1999/xhtml"><head>');
	PAGE_ADD('<meta http-equiv="Content-Type" content="text/html; charset=big5" />');
	PAGE_ADD('<meta name="viewport" content="width=1024" />');
	PAGE_ADD('<title>'.$title.' - '.PRODUCT_NAME.'</title>');
	PAGE_ADD('<link rel="stylesheet" type="text/css" href="theme.css" />');
	PAGE_ADD('<link rel="shortcut icon" type="image/x-icon" href="logo/logo.ico" />');
	PAGE_ADD('<link rel="icon" type="image/png" href="logo/logo_16.png" />');
	PAGE_ADD('<link rel="apple-touch-icon-precomposed" href="logo/logo_128.png" />');
	PAGE_ADD('</head><body>');
	//PAGE_ADD('<div class="header"><img src="logo/logo_32.png" title="'.PRODUCT_NAME.'" /><h1>'.$title.'</h1></div>');
}

function PAGE_FOOTER() {
	global $TAB_INFO;
	
	if (PAGE_IS_PRINT_MODE()) {
		PAGE_ADD('<div class="footerp">'.PRODUCT_NAME.'<br />'.COMPANY_NAME.'</div>');
	} else {
		PAGE_ADD('<div class="footer">');
		PAGE_ADD(COMPANY_NAME);
		$h = '<div class="pages">'.PRODUCT_NAME;
		foreach ($TAB_INFO as $row) {
			$h .= '．<a href="'.$row[FIELD_TAB_ID].'.php" class="page">'.$row[FIELD_TAB_TITLE].'</a>';
		}
		$h .= '</div>';
		PAGE_ADD($h);
		PAGE_ADD('</div>');
	}
}

function PAGE_OUTPUT() {
	global $HTML;

	PAGE_ADD('</body></html>');	
	header('Content-Length: '.strlen($HTML));
	echo $HTML;
}

function PAGE_IS_PRINT_MODE() {
	return isset($_GET['print']);
}

function RETURN_PAGE_TABS($TabIndex, $c_tab) {
	global $TAB_INFO;
	
	$h = '<div class="header"><div class="tab">';
	$i = 0;
	while ($i < $c_tab) {
		$row = &$TAB_INFO[$i];
		if ($TabIndex != $i) {
			$h .= '<a href="'.$row[FIELD_TAB_ID].'.php"';
			if ('0' != $row[FIELD_TAB_NEW]) {
				$h .= ' target="_blank"';
			}
			$h .= ' class="page">'.$row[FIELD_TAB_TITLE].'</a>';
		} else {
			$h .= '<a href="'.$row[FIELD_TAB_ID].'.php" class="current">'.$row[FIELD_TAB_TITLE].'</a>';
		}	
		++$i;
	}
	$h .= '</div></div>';
	return $h;
}

function RETURN_PAGE_SECTIONS($SectionIndex, $c_section) {
	global $THIS_PHP_FILE;
	global $SECTION_INFO;
	
	$h = '<div class="section">';
	$i = 0;
	while ($i < $c_section) {
		$row = &$SECTION_INFO[$i];
		if ($i != $SectionIndex) {
			$c = 'item';
		} else {
			$c = 'current';
		}
		$h .= '<a href="'.$THIS_PHP_FILE.'?section='.$row[FIELD_SECTION_ID].'" class="'.$c.'">'.$row[FIELD_SECTION_TITLE].'</a>';
		++$i;
	}
	$h .= '</div>';
	return $h;
}


function RETURN_PAGE_NAV($SectionIndex, $c_section, $DocumentIndex, $c_document) {
	global $THIS_PHP_FILE;
	global $SECTION_INFO;
	global $DOCUMENT_INFO;

	$h = '<table cellspacing="0" class="nav"><tr>';
	if (-1 == $DocumentIndex) {
		$PrevSectionIndex = $SectionIndex - 1;
		if ($PrevSectionIndex < 0) $PrevSectionIndex = $c_section - 1;
		$prev_row = &$SECTION_INFO[$PrevSectionIndex];
	
		$NextSectionIndex = $SectionIndex + 1;
		if ($NextSectionIndex >= $c_section) $NextSectionIndex = 0;
		$next_row = &$SECTION_INFO[$NextSectionIndex];

		$link_url = $THIS_PHP_FILE.'?section=';
		$h .= '<td class="prev"><a href="'.$link_url.$prev_row[FIELD_SECTION_ID].'">&lt; '.$prev_row[FIELD_SECTION_TITLE].'</a></td>';
		$h .= '<td class="next"><a href="'.$link_url.$next_row[FIELD_SECTION_ID].'">'.$next_row[FIELD_SECTION_TITLE].' &gt;</a></td>';
	} else {
		$PrevDocumentIndex = $DocumentIndex - 1;
		if ($PrevDocumentIndex < 1) $PrevDocumentIndex = $c_document - 1;
		$prev_row = &$DOCUMENT_INFO[$PrevDocumentIndex];
	
		$NextDocumentIndex = $DocumentIndex + 1;
		if ($NextDocumentIndex >= $c_document) $NextDocumentIndex = 1;
		$next_row = &$DOCUMENT_INFO[$NextDocumentIndex];
		
		$link_url = $THIS_PHP_FILE.'?section='.$SECTION_INFO[$SectionIndex][FIELD_SECTION_ID].'&amp;document=';
		$h .= '<td class="prev"><a href="'.$link_url.$prev_row[FIELD_DOCUMENT_ID].'">&lt; '.$prev_row[FIELD_DOCUMENT_TITLE].'</a></td>';
		$h .= '<td class="next"><a href="'.$link_url.$next_row[FIELD_DOCUMENT_ID].'">'.$next_row[FIELD_DOCUMENT_TITLE].' &gt;</a></td>';
	}
	$h .= '</tr></table>';
	return $h;
}

function RETURN_PAGE_DOCUMENTS($c_document, &$row_section) {
	global $THIS_PHP_FILE;
	global $DOCUMENT_INFO;
	
	$h = '';
	if ($c_document > 1) {
		$h = '<ol start="0">';
		$link_url = $THIS_PHP_FILE.'?section='.$row_section[FIELD_SECTION_ID].'&amp;document=all';
		$h .= '<li><a href="'.$link_url.'&amp;print" target="_blank">印</a>．<a href="'.$link_url.'">(全部文件)</a></li>';
		$i = 1;
		while ($i < $c_document) {
			$row = &$DOCUMENT_INFO[$i];
			$link_url = $THIS_PHP_FILE.'?section='.$row_section[FIELD_SECTION_ID].'&amp;document='.$row[FIELD_DOCUMENT_ID];
			$h .= '<li><a href="'.$link_url.'&amp;print" target="_blank">印</a>．<a href="'.$link_url.'">'.$row[FIELD_DOCUMENT_TITLE].'</a></li>';
			++$i;
		}
		$h .= '</ol>';
	}
	return $h;
}

function FIND_TAB_INDEX(&$TabIndex, &$c_tab) {
	global $THIS_PHP_FILE;
	global $TAB_INFO;
	
	$TabIndex = 0;
	$c_tab = count($TAB_INFO);
	$i = 0;
	while ($i < $c_tab) {
		if ($THIS_PHP_FILE == $TAB_INFO[$i][FIELD_TAB_ID].'.php') {
			$TabIndex = $i;
			break;
		}
		++$i;
	}
}

function FIND_SECTION_INDEX(&$SectionIndex, &$c_section) {
	global $SECTION_INFO;
	
	$SectionId = RETURN_STR_GET('section');
	$c_section = count($SECTION_INFO);
	$SectionIndex = $c_section - 1;
	while ($SectionIndex >= 0) {
		if ($SECTION_INFO[$SectionIndex][FIELD_SECTION_ID] == $SectionId) {
			break;
		}
		--$SectionIndex;
	}
	if ($SectionIndex < 0) $SectionIndex = 0;
}

function FIND_DOCUMENT_INDEX(&$DocumentIndex, &$c_document) {
	global $DOCUMENT_INFO;
	
	$DocumentId = RETURN_STR_GET('document');
	$c_document = count($DOCUMENT_INFO);
	$DocumentIndex = -1;
	if ($c_document > 1) {
		$i = 1;
		while ($i < $c_document) {
			if ($DOCUMENT_INFO[$i][FIELD_DOCUMENT_ID] == $DocumentId) {
				$DocumentIndex = $i;
				break;
			}
			++$i;
		}
	}
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