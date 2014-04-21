#!/usr/bin/php 
<?

print "####\n";

while (True) {
	$line = fgets(STDIN);
	$line = trim($line);  # trim out newline at end or spaces at start
	if ($line) {
		if ($line[-1] != ';')
			$stmt = 'return '. $line .';';
		else
			$stmt = $line;
		////echo "----before eval\n";
		$res = eval($stmt);
		////echo "----after eval\n";
		////var_dump($res);
		print ' ' . $line ."\n";
		////echo "----sec print = \n";
		print ' = ' . $res ."\n";
		////echo "----after print around agian\n";
	}
	else {
		print "####\n";
	}
}

