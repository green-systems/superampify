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
	require_once('../lib/config.php');
	require_once('../lib/utils.php');
	require_once('../lib/superampify.php');
	require_once('../lib/lastfm.php');
?>
<?php
	$action = $_REQUEST['action'];
	$data = array();
	try{
		$sa = new Superampify($_REQUEST);
		switch($action){
			case 'ping':
				// Pong >_<
				break;
			case 'getMusicFolders':
				$data['response'] = $sa->getMusicFolders();
				$viewFile = 'getMusicFolders.php';
				break;
			case 'getMusicDirectory':
				$id = $_REQUEST['id'];
				$data['response'] = $sa->getMusicDirectory($id);
				$viewFile = 'getMusicDirectory.php';
				break;
			case 'stream':
				$id = $_REQUEST['id'];
				$stream = $sa->getStream($id);
				exit();
			case 'getIndexes':
				$indexes = $sa->getIndexes();
				$viewFile = 'getIndexes.php';
				$data['response'] = $indexes;
				break;
			case 'getLicense':
				$viewFile = 'getLicense.php';
				// Valid :)
				break;
			case 'search2':
				$data['response'] = $sa->search($_REQUEST);
				$viewFile = 'search2.php';
				break;
			case 'getCoverArt':
				$album_id = $_REQUEST['id'];
				// Fetch album information
				$album_info = $sa->getMusicDirectory($album_id);
				$albumName = $album_info['directory']['name'];
				$artistName = $album_info['directory']['artist'];
				if ($_REQUEST['size'] > '1') {
					if ($_REQUEST['size'] < '500')
						$size=$_REQUEST['size'];
					else
						$size = '500';
				}
				else {
					$size='200';
				}
				if (Config::$AART_HANDLER == 'lastfm'){
					$aa_lastfm = new LastFM(Config::$LASTFM_API_KEY);
					try{
						$info = $aa_lastfm::getAlbumInfo($artistName, $albumName);
						$xml = simplexml_load_string($info);
						$image_url = (string)$xml->album->image[3];
					} catch (Exception $e){
						header("HTTP/1.0 404 Not Found");
						$image_url = Config::$ROOT.'/data/defaultcover.png';
					}
					$image_meta = getimagesize($image_url);
					switch($image_meta['mime']){
						case 'image/jpeg':
							$img = ImageCreateFromJPEG($image_url);
							break;
						case 'image/png':
							$img = ImageCreateFromPNG($image_url);
							break;
						default:
							exit();
					}
					$thumb = imagecreatetruecolor($size, $size);
					imagecopyresized($thumb, $img, 0, 0, 0, 0, $size, $size, $image_meta[0], $image_meta[1]);
					header('Content-type: '.$image_meta['mime']);
					switch($image_meta['mime']){
						case 'image/jpeg':
							imagejpeg($thumb);
							break;
						case 'image/png':
							imagepng($thumb);
							break;
					}
					exit();
				} else {
					echo "Album ART Handler is not Configured!"; die;
				}
				break;
			case 'getAlbumList':
				$viewFile = 'getAlbumList.php';
				if (isset($_REQUEST['type']))
					$type = $_REQUEST['type'];
				else
					$type = null;

				if (isset($_REQUEST['size']))
					$type = $_REQUEST['size'];
				else
					$size = null;

				if (isset($_REQUEST['offset']))
					$offset = $_REQUEST['offset'];
				else
					$offset = null;
				$data['response'] = $sa->getAlbumList($type, $size, $offset);
				break;
			case 'getRandomSongs':
				$size = (!isset($_REQUEST['size'])) ? null : $_REQUEST['size'];
				$genre = (!isset($_REQUEST['genre'])) ? null : $_REQUEST['genre'];
				$fromYear = (!isset($_REQUEST['fromYear'])) ? null : $_REQUEST['fromYear'];
				$toYear = (!isset($_REQUEST['toYear'])) ? null : $_REQUEST['toYear'];
				$musicFolderId = (!isset($_REQUEST['musicFolderId'])) ? null : $_REQUEST['musicFolderId'];				
				$data['response'] = $sa->getRandomSongs($size, $genre, $fromYear, $toYear, $musicFolderId);
				$viewFile = 'getRandomSongs.php';
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
	if (!isset($_REQUEST['f']))
		$_REQUEST['f'] = 'xml';

	switch($_REQUEST['f']){
		case 'json':
			include_once('layout/json.php');
			break;
		case 'jsonp':
			$data['callback'] = $_REQUEST['callback'];
			include_once('layout/json.php');
			break;
		default:
			include_once('layout/xml.php');
	}
?>
