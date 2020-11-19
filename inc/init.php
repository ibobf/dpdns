<?php
$auth = array(
		'Id'		=> '',		// DNSPod Token ID
		'Token'		=> ''		// DNSPod Token
);

$domainId = '';				// DNSPod域名ID
$recordId = '';				// DNSPod记录ID
$subDomain = '';			// 域名主机名称(如www、@等)

/**
 * 递归创建文件夹
 * @param string $path
 * @return boolean
 */
function mkdirs($path) {
	$upDir = pathinfo($path, PATHINFO_DIRNAME);
	if (!file_exists($upDir)) mkdirs($upDir);
	return mkdir($path);
}

/**
 * 使用cURL发送请求
 * @param unknown $uri
 * @param array $posts
 * @return mixed
 */
function request($uri, $posts=array()) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_AUTOREFERER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_USERAGENT, 'TaoBoy/1.0');
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_URL, $uri);
	if (!empty($posts)) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($posts));
	}
	
	$data = null;
	$step = 0;
	do {
		$data = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$step++;
	} while($code != 200 && $step < 3);
	
	curl_close($ch);
	return $data;
}

/**
 * 获取客户真实IP
 * @return string|unknown
 */
function realIP(){
	$realip = NULL;
	
	if (isset($_SERVER)) {
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			
			foreach ($arr AS $ip) {
				$ip = trim($ip);
				if ($ip != 'unknown') {
					$realip = $ip;
					break;
				}
			}
		} elseif (isset($_SERVER['HTTP_CLIENT_IP'])) $realip = $_SERVER['HTTP_CLIENT_IP'];
		else {
			if (isset($_SERVER['REMOTE_ADDR'])) $realip = $_SERVER['REMOTE_ADDR'];
			else $realip = '0.0.0.0';
		}
	} else {
		if (getenv('HTTP_X_FORWARDED_FOR')) $realip = getenv('HTTP_X_FORWARDED_FOR');
		elseif (getenv('HTTP_CLIENT_IP')) $realip = getenv('HTTP_CLIENT_IP');
		else $realip = getenv('REMOTE_ADDR');
	}
	
	preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
	$realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
	
	return $realip;
}

/**
 * 写入信息至文件
 * @param unknown $info
 * @param unknown $fileName
 * @param unknown $path
 * @return boolean
 */
function logInfo($info, $fileName, $path) {
	if (!file_exists($path)) mkdirs($path);
	
	$file = fopen($path.$fileName, 'a+');
	fwrite($file, "\r\n".date('Y-m-d H:i:s')."\t".$info);
	fclose($file);
	
	return true;
}

define('ROOTs', dirname(dirname(__FILE__)));