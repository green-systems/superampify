<?php
/**
 * Superampify
 *
 * @author eskerda
 * @copyright 2012 Interstel Com.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
$baseXML = <<<EOT
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