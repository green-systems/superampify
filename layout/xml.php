<?php $baseXML = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<subsonic-response xmlns="http://subsonic.org/restapi" version="1.7.0">
</subsonic-response>
EOT;
$xmloutput = simplexml_load_string($baseXML);
$xmloutput->addAttribute('status', $data['status']);
if (isset($data['error'])){
	$child = $xmloutput->addChild('error');
	$child->addAttribute('code', $data['error']['code']);
	$child->addAttribute('message', $data['error']['message']);
}
if (isset($data['response'])){
	$response = $data['response'];
	$xmloutput = Utils::assocArrayToXML($xmloutput, $response);
}
header ("Content-Type: text/xml");
echo $xmloutput->asXML(); ?>