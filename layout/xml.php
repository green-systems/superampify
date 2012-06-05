<?php $baseXML = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<subsonic-response xmlns="http://subsonic.org/restapi" version="1.7.0" status="{$data['status']}">
</subsonic-response>
EOT;
$xmloutput = simplexml_load_string($baseXML);
$xmloutput->addAttribute('status', $data['status']);
header ("Content-Type: text/xml");
echo $xmloutput->asXML(); ?>