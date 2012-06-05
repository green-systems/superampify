<?php
/**
 * Superampify
 *
 * @author eskerda
 * @copyright 2012 eskerda eskerda@gmail.com
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

	public function setAuthHandshake( $passphrase ){
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
		self::$auth = $auth;
	}

	public function getAmpacheActionUrl($action){
		return
			sprintf(Config::$AMPACHE_SERVER.self::$AMPACHE_ACTION_URL,$action,self::$auth);
	}
}