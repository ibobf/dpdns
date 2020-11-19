<?php
require_once 'inc/init.php';

if (!empty($_REQUEST['domain_id']) && empty($domainId)) $domainId = trim($_REQUEST['domain_id']);
if (!empty($_REQUEST['record_id']) && empty($recordId)) $recordId = trim($_REQUEST['record_id']);
if (!empty($_REQUEST['sub_domain']) && empty($subDomain)) $subDomain = trim($_REQUEST['sub_domain']);

if (empty($domainId) || empty($recordId) || empty($subDomain)) {
	die("您必须填写或传入Domain ID和Record ID以及Sub Domain.\r\n");
}
$info = " Request Update.\r\n";
$info .= "Domain Id:".$domainId."\tRecord Id:".$recordId."\tSub Domain:".$subDomain."\r\n";
$ip = realIP();
$lastFile = ROOTs.DIRECTORY_SEPARATOR.'lasted'.DIRECTORY_SEPARATOR.$subDomain.$domainId.$recordId.'.ip';

$lasted = file_exists($lastFile) ? file_get_contents($lastFile) : '';
$info .= "Client IP:".$ip."\tLasted IP:".$lasted."\r\n";
$result = '';
if ($ip != $lasted) {
	$posts = array(
			'domain_id'=>$domainId,
			'record_id'=>$recordId,
			'sub_domain'=>$subDomain,
			'record_type'=>'A',
			'record_line_id'=>'0',
			'value'=>$ip,
			'ttl'=>180,
			'login_token' => $auth['Id'].','.$auth['Token'],
			'format' => 'json'
	);
	$res = json_decode(request('https://dnsapi.cn/Record.Modify', $posts), true);
	if (!empty($res) && !empty($res['status'])) {
		file_put_contents($lastFile, $ip);
		$result = "Update successed.";
	} else $result = "Update failed. | ".json_encode($res);
} else $result = "IP Match.";
$info .= $result."\r\n";

logInfo($info, date('ymd', time()).'.log', ROOTs.DIRECTORY_SEPARATOR.'logs/');

echo $result;