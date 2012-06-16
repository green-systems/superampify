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
		$response =	"<index name=\"".htmlentities($this->name)."\">";
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
		$xml = "<artist name=\"".htmlentities($this->name)."\" id=\"".$this->id."\"/>";
		return $xml;
	}

	public function toJSON(){

	}
}

class Superampify{
	
	//my.subampapi.com/rest/getIndexes.view?u=admin&p=enc:61646d696e&v=1.6.0&c=MiniSub&f=jsonp
	
	var $user = "";
	var $time = "";
	var $auth = "";
	var $version = "";
	var $client = "";
	var $format = "";
	var $lastmod = "";

	public static $AMPACHE_HANDSHAKE_URL = 'server/xml.server.php?action=handshake&auth=%s&timestamp=%s&version=350001&user=%s';
	protected static $AMPACHE_ACTION_URL = 'server/xml.server.php?action=%s&auth=%s';

	public function __construct($query){
		$this->time = time();
		$this->generateAuth($query);
		return $this;
	}

	public function getMusicFolders(){
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

	public function getMusicDirectory($id){
		$sid = split('_', $id);

		$musicDirectory = array();
		$directory = array(
			'child' => array(),
			'name' => '',
			'id' => $id
		);

		if ($sid[0] == 'album'){
			$directory['id'] = $sid[1];
			$songs = $albums = $this->getAmpacheAlbumSongs($sid[1]);
			foreach ($songs as $song){
				if ($directory['name'] == ''){
					$directory['name'] = $song['album'];
				}
				if ($directory['artist'] == ''){
					$directory['artist'] = $song['artist'];
				}
				$directory['child'][] = array(
					'id' => $song['song_id'],
					'parent' => 'album_'.$song['album_id'],
					'title' => $song['title'],
					'isDir' => false,
					'type' => 'music',
					'album' => $song['album'],
					'artist' => $song['artist'],
					'duration' => $song['time'],
					'bitRate' => round($song['size'] / $song['time'] * "0.008"),
					'track' => $song['track'],
					'year' => '0',
					'genre' => '',
					'size' => $song['size'],
					'suffix' => 'mp3',
					'contentType'=>'audio/mpeg',
					'isVideo' => false,
					'coverArt' => 'album_'.$song['album_id'],
					'path' => sprintf('%s/%s/%d - %s.mp3',$song['artist'],$song['album'],$song['track'],$song['title'])
				);
			}
			$musicDirectory['directory'] = $directory;
		} else {
			$directory['id'] = $id;
			$albums = $this->getAmpacheArtistsAlbums($id);
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

	public function getIndexes(){
		//Convert Time to Timestamp
		$delim = array('-', 'T', ':', '+');
		$lastmoddel = str_replace($delim, ',', $this->lastmod); //Extra step to create a uniform value
		$lastmodarr = explode(',', $lastmoddel);
		$lastmodstamp = gmmktime($lastmodarr[3]-$lastmodarr[6],$lastmodarr[4]-$lastmodarr[7],$lastmodarr[5],$lastmodarr[1],$lastmodarr[2],$lastmodarr[0]);
		//Get Artists if changed or ifModifiedSince is not set
		if (isset($_REQUEST['ifModifiedSince']) && $_REQUEST['ifModifiedSince'] != '' && $lastmodstamp == $_REQUEST['ifModifiedSince'])
			return array();
		
		$response = array(
			'indexes' => array()
		);

		$artists = $this->getAmpacheArtists();
		$letter_artists = array();
		foreach ($artists as $artist){
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

	function getStream ($id){
		$stream = $this->getAmpacheStream($id);
	}

	function search ($query){
		
		if (!isset($query['query']))
			$q = '';
		else{
			/* Only allow valid characters */
			$q = preg_replace('/[^a-z0-9\ ]/', '', $query['query']);
			/* Remove one letter words */
			$q = preg_replace('/\s+\S{1,2}(?!\S)|(?<!\S)\S{1,2}\s+/', '', $q);
			if (strlen($q) < 4)
				return array('searchResult2'=>'');
		}
		$q = trim(htmlentities($q));

		if (!isset($query['artistCount']))
			$artistCount = 10;
		else
			$artistCount = $query['artistCount'];

		if (!isset($query['albumCount']))
			$albumCount = 20;
		else
			$albumCount = $query['albumCount'];

		if (!isset($query['songCount']))
			$songCount = 25;
		else
			$songCount = $query['songCount'];

		$artists = $this->getAmpacheArtists($q);
		$albums = $this->getAmpacheAlbums($q);
		$songs = $this->getAmpacheSongs($q);

		$r = array('artist' => array(), 'album'=>array(), 'song' => array());
		
		foreach ($artists as $artist){

			$r['artist'][] = $artist;
			$albums = array_merge($albums, $this->getAmpacheArtistsAlbums($artist['id']));
		}

		foreach ($albums as $album){

			$r['album'][] = array(
				'artist' => $album['artist'],
				'averageRating' => $album['rating'],
				'coverArt' => $album['cover_id'],
				'id' => 'album_'.$album['album_id'],
				'isDir' => true,
				'parent' => $album['artist_id'],
				'title' => $album['title'],
				'userRating' => $album['rating']
			);
			$songs = array_merge($songs, $this->getAmpacheAlbumSongs($album['album_id']));
		}

		foreach ($songs as $song){

			$r['song'][] = array(
				'id' => $song['song_id'],
				'parent' => 'album_'.$song['album_id'],
				'title' => $song['title'],
				'isDir' => false,
				'type' => 'music',
				'album' => $song['album'],
				'artist' => $song['artist'],
				'duration' => $song['time'],
				'bitRate' => round($song['size'] / $song['time'] * "0.008"),
				'track' => $song['track'],
				'year' => '0',
				'genre' => '',
				'size' => $song['size'],
				'suffix' => 'mp3',
				'contentType'=>'audio/mpeg',
				'isVideo' => false,
				'coverArt' => 'album_'.$song['album_id'],
				'path' => sprintf('%s/%s/%d - %s.mp3',$song['artist'],$song['album'],$song['track'],$song['title'])
			);
		}
		
		/*foreach ($r as $key=>$part){
			if (sizeof($r[$key]) == 1)
				$r[$key] = $part[0];
		}*/
		if (sizeof($r) > 0)
			return array('searchResult2'=>$r);
		else
			return array('searchResult2'=>'');
	}

	private function generateAuth($query){
		if (isset ($query['handshake'])){
			$this->auth = (string) $query['handshake'];
		} else {
			$this->user = $query['u'];
			$password = $query['p'];
			if (substr($password,0,4)=="enc:"){
				$password = PREG_REPLACE(
					"'([\S,\d]{2})'e","chr(hexdec('\\1'))",substr($password,4)
				);
	        }
			$passphrase = hash('sha256',$this->time.hash('sha256',$password));
			$this->setAuthHandshake($passphrase);
		}
	}

	private function setAuthHandshake( $passphrase ){
		$url = sprintf(
			Config::$AMPACHE_SERVER.Superampify::$AMPACHE_HANDSHAKE_URL
			,$passphrase
			,$this->time
			,$this->user);
		$handshake = file_get_contents($url);
		$handsmpl = Superampify::handleXMLCall($handshake);
		$auth = $handsmpl->auth[0]; //Could be stored somewhere so not every request results in new handshake
		$lastmod = $handsmpl->add[0]; //last modified for getIndexes.view
		$this->lastmod = $lastmod;
		$this->auth = (string) $auth;
	}

	private function getAmpacheActionUrl($action){
		return
			sprintf(Config::$AMPACHE_SERVER.Superampify::$AMPACHE_ACTION_URL,$action,$this->auth);
	}

	private static function handleXMLCall( $plain_xml, $param0 = null, $param1 = null){
		$xml = simplexml_load_string($plain_xml, $param0, $param1);
		if (isset($xml->error)){
			throw new Exception($xml->error[0]);
		} else {
			return $xml;
		}
	}

	private function getAmpacheArtists($filter = null){
		$url = $this->getAmpacheActionUrl('artists');
		if (isset($filter))
			$url.='&filter='.$filter;
		$response = file_get_contents($url);
		$artists_xml = Superampify::handleXMLCall($response);
		$artists = array();
		foreach ($artists_xml->children() as $node){
			$attributes = $node->attributes();
			$artists[] = array(
				'id' => (int) $attributes[0],
				'name' => (string) $node->name
			);
		}
		
		return $artists;
	}

	private function getAmpacheAlbums($filter = null){
		$url = $this->getAmpacheActionUrl('albums');
		if (isset($filter))
			$url.='&filter='.$filter;
		$response = file_get_contents($url);
		$albums = array();
		$xml_response = Superampify::handleXMLCall($response);
		foreach ($xml_response->children() as $node){
			$album_attributes = $node->attributes();
			$artist_attributes = $node->artist->attributes();
			
			$artist_id = (int) $artist_attributes['id'];
			$album_id = (int) $album_attributes['id'];
			$artist = (string) $node->artist;
			$rating = (int) $node->rating;
			$title = (string) $node->name;
			$albums[] = array(
				'artist_id' => $artist_id,
				'album_id' => $album_id,
				'artist' => $artist,
				'rating' => $rating,
				'title' => $title,
				'cover_id' => 'album_'.$album_id
			);
		}
		return $albums;
	}

	private function getAmpacheSongs($query){
		$url = $this->getAmpacheActionUrl('songs').'&filter='.$query;
		$response = file_get_contents($url);
		$songsxml = Superampify::handleXMLCall($response, null, LIBXML_NOCDATA);
		$songs = array();
		foreach ($songsxml->children() as $node) {
			$song_attrs = $node->attributes();
			$album_attrs = $node->album->attributes();
			$artist_attrs = $node->artist->attributes();
			$songs[] = array(
				'song_id' => (String) $song_attrs[0],
				'title' => (String) $node->title,
				'artist_id' =>  (String) $artist_attrs[0],
				'artist' => (String) $node->artist,
				'album_id' => (String) $album_attrs[0],
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
		return $songs;
	}

	private function getAmpacheStream ($id){
		$url = $this->getAmpacheActionUrl('song').'&filter='.$id;
		$song_xml = Superampify::handleXMLCall(file_get_contents($url), null, LIBXML_NOCDATA);
		$song_url = (string) $song_xml->song->url;
		header('Content-type: audio/mpeg');
		$stream = fopen($song_url, 'r');
		echo stream_get_contents($stream, -1, -1);
		fclose($stream);
	}

	private function getAmpacheArtistsAlbums($id){
		$url = $this->getAmpacheActionUrl('artist_albums').'&filter='.$id;
		$response = file_get_contents($url);
		$artists_albums = array();
		$xml_response = Superampify::handleXMLCall($response);
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
				'cover_id' => 'album_'.$album_id
			);
		}
		return $artists_albums;
	}

	private function getAmpacheAlbumSongs($id){
		$url = $this->getAmpacheActionUrl('album_songs').'&filter='.$id;
		$response = file_get_contents($url);
		$songsxml = Superampify::handleXMLCall($response, null, LIBXML_NOCDATA);
		$album_songs = array();
		foreach ($songsxml->children() as $node) {
			$song_attrs = $node->attributes();
			$album_attrs = $node->album->attributes();
			$artist_attrs = $node->artist->attributes();
			$album_songs[] = array(
				'song_id' => (String) $song_attrs[0],
				'title' => (String) $node->title,
				'artist_id' =>  (String) $artist_attrs[0],
				'artist' => (String) $node->artist,
				'album_id' => (String) $album_attrs[0],
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
	}
}