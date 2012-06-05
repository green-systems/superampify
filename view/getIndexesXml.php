<?php $out = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<subsonic-response xmlns="http://subsonic.org/restapi" version="1.7.0" status="ok">
<indexes lastModified="{$indexes['indexes']['lastModified']}">
{$indexes['indexes']['index']}
</indexes>
</subsonic-response>
EOT;
header ("Content-Type: text/xml");
echo $out;