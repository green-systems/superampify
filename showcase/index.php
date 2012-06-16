<?php
	include_once('../lib/superampify.php');
	include_once('../lib/config.php');

	/** We do not really need to include these files if we do not want,
		we just need too constants:
			- AMPACHE_HANDSHAKE_URL , which is:
server/xml.server.php?action=handshake&auth=%s&timestamp=%s&version=350001&user=%s'
			- AMPACHE_SERVER. which points to "your owncloud server/apps/media"				
	**/

	session_start();

	/**
		This is a function that generates a handshake that works with
		Ampache, and OwnCloud.

		This handshake serves as a way to keep the session going
		(instead of passing the user and the password around on each
		call)

		Notice how it takes the user and the password, in plaintext.

		So, it can only be generated during log-in time (the only time
		the user sends the password in clear text to the server)

		This handshake gets read later in subtunes.

		So, the idea here is:
			- Generate this handshake on log-in time in runners-id.
			- Save it on the session, whatever the way runners-id handles sessions.
			- Read it on subtunes, when it's opened.

		Basically, this code is just a showcase that this works.
	**/

	function getAuthHandshake($user, $password) {
		$time = time();
		$passphrase = hash('sha256',$time.hash('sha256',$password));
		$url = sprintf(
			Config::$AMPACHE_SERVER.Superampify::$AMPACHE_HANDSHAKE_URL
			,$passphrase
			,$time
			,$user);
		$handshake = file_get_contents($url);
		$handsmpl = simplexml_load_string($handshake);
		$auth = $handsmpl->auth[0]; //Could be stored somewhere so not every request results in new handshake
		$lastmod = $handsmpl->add[0]; //last modified for getIndexes.view
		return (string) $auth;
	}
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete'){
			session_destroy();
			header( 'Location: index.php' );
	}
	if (isset($_SESSION['handshake'])){
		$handshake = $_SESSION['handshake'];
	}else if (isset($_REQUEST['u']) && isset($_REQUEST['p'])){
			$user = $_REQUEST['u'];
			$password = $_REQUEST['p'];
			$handshake = getAuthHandshake($user, $password);
			$_SESSION['handshake'] = $handshake;
	}
?>

<html>
<head>
</head>
<body>
<h1>This simulates being in runners-id</h1>
<p>This is not a login page, this just generates a handshake, that later is reused inside subtunes. If this works, it's assumed we can generate a handshake for a logged in runners-id user, and then, on the link to REZ Music, pass along the handshake to subtunes</p>
<p>Login here, then, when you are inside, access the link to enter subtunes without having to enter any credentials</p>
<?php if (isset($handshake)): ?>
<h1>This is your handshake: <?php echo $handshake; ?></h1>
<p>Now, you can just go to <a href="../subtunes/">subtunes</a></p>
<p><a href="index.php?action=delete">Delete my session</a></p>
<?php else: ?>
<form action="index.php" method="POST">
<label>Username: </label><input type="text" name="u"/></br>
<label>Password: </label><input type="password" name="p"/></br>
<button>Get Handshake</button>
</form>
<?php endif; ?>
</body>
</html>