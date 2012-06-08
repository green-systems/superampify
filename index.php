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
	require_once('lib/config.php');
	require_once('lib/utils.php');
	require_once('lib/superampify.php');
?>
<?php
	$action = $_GET['action'];
	$data = array();
	try{
		$sa = new Superampify($_GET);
		switch($action){
			case 'ping':
				// Pong >_<
				break;
			case 'getMusicFolders':
				$data['response'] = $sa::getMusicFolders();
				break;
			case 'getMusicDirectory':
				$id = $_REQUEST['id'];
				$data['response'] = $sa::getMusicDirectory($id);
				$viewFile = 'getMusicDirectory.php';
				break;
			case 'stream':
				$id = $_REQUEST['id'];
				$stream = $sa::getStream($id);
				exit();
			case 'getIndexes':
				$indexes = $sa::getIndexes();
				$viewFile = 'getIndexes.php';
				$data['response'] = $indexes;
			case 'getLicense':
				$viewFile = 'getLicense.php';
				// Valid :)
				break;
			default:
				// Do nothing..
		}
		/*****
			At this point, if there has not been
			any exception, we can assume the status
			is ok
		*****/
		$data['status'] = 'ok';
	} catch(Exception $e) {
		$data['status'] = 'failed';
		$data['error'] = array(
			'code' => 0,
			'message' => $e->getMessage()
		);
	}
	if (!isset($_GET['f']))
		$_GET['f'] = 'xml';

	switch($_GET['f']){
		case 'json':
			include_once('layout/json.php');
			break;
		case 'jsonp':
			$data['callback'] = $_GET['callback'];
			include_once('layout/json.php');
			break;
		default:
			include_once('layout/xml.php');
	}
?>