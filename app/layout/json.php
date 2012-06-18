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
$base = array(
	'subsonic-response' => array(
		'status' => $data['status'],
		'version' => '1.7.0',
		'xmlns' => 'http://subsonic.org/restapi'
	)
);
if (isset($data['error'])){
	$base['subsonic-response']['error'] = array(
		'code' => $data['error']['code'],
		'message' => $data['error']['message']
	);
}
if (isset($data['response'])){
	foreach ($data['response'] as $key=>$r){
		$base['subsonic-response'][$key] = $r;
	}
}
if (isset($data['callback']))
	header ("Content-Type:text/javascript;charset=UTF-8");
else
	header ("Content-Type: application/json;charset=UTF-8");
	/**
	while we wait for PHP 5.4, just use this
	ugly replace to unescape slashes on url.

	Later, use json_encode($result, JSON_UNESCAPED_SLASHES);
	**/
?>
<?php if (isset($data['callback'])): ?><?php echo $data['callback']; ?>(<?php endif; ?>
<?php echo str_replace('\\/', '/', json_encode($base)); ?>
<?php if (isset($data['callback'])): ?>);<?php endif; ?>