<?php
function make_url_tag($String)
{
	// Make Lowercase First
	$String = strtolower($String);
	
	// Now Replace non-Punctuation
	$String = preg_replace("/([^a-z0-9-_]+)/", "-", $String);
	
	// Trim any trailing Hyphens
	$String = trim($String, "-");
	
	// Return the Result
	return $String;
}

function recursive_trim($input)
{
	if (is_array($input)) {
		return array_map('recursive_trim', $input);
	} else {
		return trim($input);
	}
}

function create_hash($Num_Chars = 10, $Salt = '')
{
	return substr(md5(microtime(1).$Salt), 0, $Num_Chars);
}

function email_headers($to, $from, $reply_to = FALSE)
{
	$headers = "MIME-Version: 1.0" . "\n";
	$headers .= "Content-type:text/html;charset=iso-8859-1" . "\n";
	$headers .= "To: ".$to."\n";
	$headers .= "From: ".$from."\n";
	if ($reply_to) {
		$headers .= "Reply-To: ".$reply_to."\n";
	}
	else {
		$headers .= "Reply-To: ".$from."\n";	
	}
	
	return $headers;
}
?>