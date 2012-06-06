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

/** 
	Subsonic API Documentation
		+ http://www.subsonic.org/pages/api.jsp
	Ampache API Documentation
		+ http://ampache.org/wiki/dev:xmlapi
**/


class Index{
	public $artist = array();

	public $name;

	public function __construct($name){
		$this->name = $name;
		return $this;
	}
	public function addArtist( Artist $artist ){
		$this->artist[] = $artist;
	}

	public function toXML(){
		$response =	<<<EOT
<index name="{$this->name}">
EOT;
		foreach ($this->artist as $artist){
			$response = $response.$artist->toXML();
		}
		return $response."</index>";
	}

	public function toJSON(){

	}
}

class Artist{

	public $id;
	public $name;

	public function __construct( $id, $name){
		$this->id = $id;
		$this->name = $name;
	}

	public function toXML(){
		$xml = <<<EOT
<artist name="{$this->name}" id="{$this->id}"/>
EOT;
		return $xml;
	}

	public function toJSON(){

	}
}

class Superampify{
	
	//my.subampapi.com/rest/getIndexes.view?u=admin&p=enc:61646d696e&v=1.6.0&c=MiniSub&f=jsonp
	
	protected static $user = "";
	protected static $time = "";
	protected static $auth = "";
	protected static $version = "";
	protected static $client = "";
	protected static $format = "";
	protected static $lastmod = "";

	protected static $AMPACHE_HANDSHAKE_URL = 'server/xml.server.php?action=handshake&auth=%s&timestamp=%s&version=350001&user=%s';
	protected static $AMPACHE_ACTION_URL = 'server/xml.server.php?action=%s&auth=%s';

	public function __construct($query){
		self::$time = time();
		self::generateAuth($query);
		return $this;
	}

	static function getMusicFolders(){
		/* Not Implemented! */
		return array(
			'musicFolders' => array(
					'musicFolder' => array(
						'id' => 0,
						'name' => 'Music'
					)
			)
		);
	}

	static function getMusicDirectory($id){
		$sid = split('_', $id);

		$musicDirectory = array();
		$directory = array(
			'child' => array(),
			'name' => '',
			'id' => $id
		);

		if ($sid[0] == 'album'){
			$directory['id'] = $sid[1];
			$songs = $albums = self::getAmpacheAlbumSongs($sid[1]);
			foreach ($songs as $song){
				if ($directory['name'] == ''){
					$directory['name'] = $song['album'];
				}
				$directory['child'][] = array(
					'id' => $song['song_id'],
					'parent' => 'album_'.$song['album_id'],
					'title' => $song['title'],
					'isDir' => false,
					'album' => $song['album'],
					'artist' => $song['artist'],
					'duration' => $song['time'],
					'bitRate' => round($song['size'] / $song['time'] * "0.008"),
					'track' => $song['track'],
					'year' => '0',
					'genre' => '',
					'size' => $song['size'],
					'suffix' => '',
					'contentType'=>'audio/mpeg',
					'isVideo' => false,
					'coverArt' => $song['album_id'],
					'path' => sprintf('%s/%s/%d - %s',$song['album'],$song['artist'],$song['track'],$song['title'])
				);
			}
			$musicDirectory['directory'] = $directory;
		} else {
			$directory['id'] = $id;
			$albums = self::getAmpacheArtistsAlbums($id);
			foreach ($albums as $album){
				if ($directory['name'] == ''){
					$directory['name'] = $album['artist'];
				}
				$directory['child'][] = array(
					'artist' => $album['artist'],
					'averageRating' => $album['rating'],
					'coverArt' => $album['cover_id'],
					'id' => 'album_'.$album['album_id'],
					'isDir' => true,
					'parent' => $album['artist_id'],
					'title' => $album['title'],
					'userRating' => $album['rating']
				);
			}
			$musicDirectory['directory'] = $directory;
		}
		return $musicDirectory;
	}

	static function getIndexes(){
		//Convert Time to Timestamp
		$delim = array('-', 'T', ':', '+');
		$lastmoddel = str_replace($delim, ',', self::$lastmod); //Extra step to create a uniform value
		$lastmodarr = explode(',', $lastmoddel);
		$lastmodstamp = gmmktime($lastmodarr[3]-$lastmodarr[6],$lastmodarr[4]-$lastmodarr[7],$lastmodarr[5],$lastmodarr[1],$lastmodarr[2],$lastmodarr[0]);
		//Get Artists if changed or ifModifiedSince is not set
		if (isset($_REQUEST['ifModifiedSince']) && $_REQUEST['ifModifiedSince'] != '' && $lastmodstamp == $_REQUEST['ifModifiedSince'])
			return array();
		
		$response = array(
			'indexes' => array()
		);

		$artists = self::getAmpacheArtists();
		$letter_artists = array();
		foreach ($artists['artist'] as $artist){
			$name = $artist['name'];
			if (preg_match('/^[a-zA-Z]*$/',$name[0]) > 0){
				// starts with allowed char
				$letter = strtoupper($name[0]);
			} else {
				// should be grouped under #
				$letter = '#';
			}
			if (!isset($letter_artists[$letter]))
				$letter_artists[$letter] = array();

			$letter_artists[$letter][] = new Artist($artist['id'], $artist['name']);
		}
		foreach ($letter_artists as $letter=>$artists){
			$idx = new Index($letter);
			foreach ($artists as $artist){
				$idx->addArtist($artist);
			}
			$response['indexes']['index'][] = $idx;
		}
		$response['indexes']['lastModified'] = $lastmodstamp;
		return $response;
	}

	static function getStream ($id){
		$stream = self::getAmpacheStream($id);
	}

	private function generateAuth($query){
		self::$user = $query['u'];
		$password = $query['p'];
		if (substr($password,0,4)=="enc:"){
			$password = PREG_REPLACE(
				"'([\S,\d]{2})'e","chr(hexdec('\\1'))",substr($password,4)
			);
        }
		$passphrase = hash('sha256',self::$time.hash('sha256',$password));
		self::setAuthHandshake($passphrase);
	}

	private function setAuthHandshake( $passphrase ){
		$url = sprintf(
			Config::$AMPACHE_SERVER.self::$AMPACHE_HANDSHAKE_URL
			,$passphrase
			,self::$time
			,self::$user);
		$handshake = file_get_contents($url);
		$handsmpl = simplexml_load_string($handshake);
		if ($handsmpl->error[0] != ''){
			throw new Exception($handsmpl->error[0]);
		}
		$auth = $handsmpl->auth[0]; //Could be stored somewhere so not every request results in new handshake
		$lastmod = $handsmpl->add[0]; //last modified for getIndexes.view
		self::$lastmod = $lastmod;
		self::$auth = (string) $auth;
	}

	private function getAmpacheActionUrl($action){
		return
			sprintf(Config::$AMPACHE_SERVER.self::$AMPACHE_ACTION_URL,$action,self::$auth);
	}

	private function getAmpacheArtists(){
		$response = file_get_contents(self::getAmpacheActionUrl('artists'));
		$assoc_response = Utils::simpleXMLToArray(simplexml_load_string($response));
		if (!isset($assoc_response['artist'][0])){
			return array(
				'artist' => array($assoc_response['artist'])
			);
		} else {
			return $assoc_response;	
		}
	}

	private function getAmpacheStream ($id){
		$url = self::getAmpacheActionUrl('song').'&filter='.$id;
		$song_xml = simplexml_load_string(file_get_contents($url), null, LIBXML_NOCDATA);
		$song_url = (string) $song_xml->song->url;
		header('Content-type: '.$songxml->song->mime);
		$stream = fopen($song_url, 'r');
		echo stream_get_contents($stream, -1, -1);
		fclose($stream);
	}

	private function getAmpacheArtistsAlbums($id){
		$url = self::getAmpacheActionUrl('artist_albums').'&filter='.$id;
		$response = file_get_contents($url);
		$artists_albums = array();
		$xml_response = simplexml_load_string($response);
		foreach ($xml_response->children() as $node){
			$album_attributes = $node->attributes();
			$artist_attributes = $node->artist->attributes();
			
			$artist_id = (int) $artist_attributes['id'];
			$album_id = (int) $album_attributes['id'];
			$artist = (string) $node->artist;
			$rating = (int) $node->rating;
			$title = (string) $node->name;
			$artists_albums[] = array(
				'artist_id' => $artist_id,
				'album_id' => $album_id,
				'artist' => $artist,
				'rating' => $rating,
				'title' => $title,
				'cover_id' => $album_id
			);
		}
		return $artists_albums;
	}

	private function getAmpacheAlbumSongs($id){
		$url = self::getAmpacheActionUrl('album_songs').'&filter='.$id;
		$response = file_get_contents($url);
		$songsxml = simplexml_load_string($response, null, LIBXML_NOCDATA);
		$album_songs = array();
		foreach ($songsxml->children() as $node) {
			$song_attrs = $node->attributes();
			$album_songs[] = array(
				'song_id' => (String) $song_attrs[0],
				'title' => (String) $node->title,
				'artist_id' =>  (String) $node->artist->attributes()[0],
				'artist' => (String) $node->artist,
				'album_id' => (String) $node->album->attributes()[0],
				'album' => (String) $node->album,
				'url' => (String) $node->url,
				'time' => (int) $node->time,
				'track' => (int) $node->track,
				'size' => (int) $node->size,
				'cover' => (String) $node->art,
				'rating' => (int) $node->rating,
				'preciserating' => (int) $node->preciserating
			);
		}
		return $album_songs;

		print_r($response); die;
	}
}