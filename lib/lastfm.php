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
	Last.fm API Documentation
		+ http://www.last.fm/api/show/album.getInfo
**/

class LastFM{

	protected static $api_key = "";

	protected static $ROOT = "http://ws.audioscrobbler.com/2.0/";
	protected static $ALBUM_INFO_URL = "/?method=album.getinfo&api_key=%s&artist=%s&album=%s";

	public function __construct($public_api_key){
		self::$api_key = $public_api_key;
		return $this;
	}
	static function getAlbumInfo($artistName, $albumName){
		$url = sprintf(self::$ROOT.self::$ALBUM_INFO_URL,self::$api_key,urlencode($artistName),urlencode($albumName));
		$info = file_get_contents($url);
		if ($info == ""){
			throw new Exception("not found");
		}
		$xml = simplexml_load_string($info);
		return $info;
	}
}