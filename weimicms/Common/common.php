<?php

function isAndroid()
{
	if (strstr($_SERVER["HTTP_USER_AGENT"], "Android")) {
		return 1;
	}

	return 0;
}

function arr_htmlspecialchars($value)
{
	return is_array($value) ? array_map("arr_htmlspecialchars", $value) : htmlspecialchars($value);
}

function urlGetTpl($action)
{
	$url_content = curl_get_tpl($action);
	return $url_content;
}

function getStaticPath()
{
	$staticPath = C("STATICS_PATH");

	if ("1" == $staticPath) {
		$staticPath = C("SITE_URL");
	}
	else {
		$staticPath = (C("STATICS_PATH") ? C("STATICS_PATH") : "http://s.404.cn");
	}

	return $staticPath;
}

function getPayType($type)
{
	$payType = array("alipay" => "支付宝", "weixin" => "微信", "tenpay" => "财付通", "tenpaycomputer" => "财付通", "yeepay" => "易宝支付", "allinpay" => "通联支付", "daofu" => "货到付款", "dianfu" => "到店付款", "chinabank" => "网银在线", "cardpay" => "会员卡");
	$type = strtolower($type);
	return $payType[$type];
}

function getSelfUrl()
{
	$secure = (isset($_SERVER["HTTPS"]) && ((strcasecmp($_SERVER["HTTPS"], "on") === 0) || ($_SERVER["HTTPS"] == 1))) || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && (strcasecmp($_SERVER["HTTP_X_FORWARDED_PROTO"], "https") === 0));
	$hostInfo = "";

	if ($secure) {
		$http = "https";
	}
	else {
		$http = "http";
	}

	if (isset($_SERVER["HTTP_HOST"])) {
		$hostInfo = $http . "://" . $_SERVER["HTTP_HOST"];
	}
	else {
		$hostInfo = $http . "://" . $_SERVER["SERVER_NAME"];

		if ($secure) {
			$port = (isset($_SERVER["SERVER_PORT"]) ? (int) $_SERVER["SERVER_PORT"] : 443);
		}
		else {
			$port = (isset($_SERVER["SERVER_PORT"]) ? (int) $_SERVER["SERVER_PORT"] : 80);
		}

		if ((($port !== 80) && !$secure) || (($port !== 443) && $secure)) {
			$hostInfo .= ":" . $port;
		}
	}

	$requestUri = "";

	if (isset($_SERVER["HTTP_X_REWRITE_URL"])) {
		$requestUri = $_SERVER["HTTP_X_REWRITE_URL"];
	}
	else if (isset($_SERVER["REQUEST_URI"])) {
		$requestUri = $_SERVER["REQUEST_URI"];

		if (!empty($_SERVER["HTTP_HOST"])) {
			if (strpos($requestUri, $_SERVER["HTTP_HOST"]) !== false) {
				$requestUri = preg_replace("/^\w+:\/\/[^\/]+/", "", $requestUri);
			}
		}
		else {
			$requestUri = preg_replace("/^(http|https):\/\/[^\/]+/i", "", $requestUri);
		}
	}
	else if (isset($_SERVER["ORIG_PATH_INFO"])) {
		$requestUri = $_SERVER["ORIG_PATH_INFO"];

		if (!empty($_SERVER["QUERY_STRING"])) {
			$requestUri .= "?" . $_SERVER["QUERY_STRING"];
		}
	}
	else {
		exit("没有获取到当前的url");
	}

	return $hostInfo . $requestUri;
}

function removeUTF8Bom($string)
{
	if (substr($string, 0, 3) == pack("CCC", 239, 187, 191)) {
		return substr($string, 3);
	}

	return $string;
}

function shareFilter($subject)
{
	$subject = str_replace("'", "", $subject);
	$subject = str_replace("\"", "", $subject);
	$subject = str_replace("\r", "", $subject);
	$subject = str_replace("\n", "", $subject);
	$subject = str_replace("\t", " ", $subject);
	return trim($subject);
}

function filterWeiXinContent($subject)
{
	$subject = str_replace("'", "&apos;", $subject);
	$subject = str_replace("\r\n", "", $subject);
	$subject = str_replace("\n\r", "", $subject);
	$subject = str_replace("\r", "", $subject);
	$subject = str_replace("\n", "", $subject);
	$subject = str_replace("\t", "", $subject);
	return $subject;
}

function nulltoblank($v)
{
	if (empty($v)) {
		return $v = "";
	}
	else {
		return $v;
	}
}


?>
