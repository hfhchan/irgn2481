<?php

function codepointToChar($codepoint) {
	if (preg_match('@^U\+[0-9A-F]{4,5}$@', $codepoint)) {
		return iconv('UTF-32BE', 'UTF-8', pack("H*", str_pad(substr($codepoint, 2), 8, '0', STR_PAD_LEFT)));
	}
	throw new Exception('Invalid Input');
}

function charToCodepoint($char) {
	if (mb_strlen($char, 'UTF-8') === 1) {
		return 'U+'.strtoupper(ltrim(bin2hex(iconv('UTF-8', 'UTF-32BE', $char)),'0'));
	}
	throw new Exception('Invalid Input');
}

function charToUSV($char) {
	if (mb_strlen($char, 'UTF-8') === 1) {
		return hexdec(ltrim(bin2hex(iconv('UTF-8', 'UTF-32BE', $char)), '0'));
	}
	throw new Exception('Invalid Input');
}
