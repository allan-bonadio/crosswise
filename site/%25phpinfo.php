<h1><small>server</small> <?= $_SERVER["HTTP_HOST"] ?></h1>
<? 

// censored for security
unset($_SERVER["WGDNCP"]);
unset($_SERVER["WGDUCP"]);
unset($_SERVER["WGDPCP"]);


phpinfo();

echo "<p>\n";
foreach ($_SERVER as $k => $v)
	echo "<br>\$_server[ $k ] = '" . var_export($v,1) ."'\n";
echo "</p><p>\n";
foreach ($_ENV as $k => $v)
	echo "<br>\$_env[ $k ] = '" . var_export($v,1) ."'\n";
echo "</p><p>\n";
foreach ($_REQUEST as $k => $v)
	echo "<br>\$_request[ $k ] = '" . var_export($v,1) ."'\n";
echo "</p>\n";


?>
