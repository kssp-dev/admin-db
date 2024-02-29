<?php

$url = getenv('ADMIN_DB_UPDATE_URL');

if (empty($url)) {
	print('ADMIN_DB_UPDATE_URL environment variable MUST be declared');
	exit(1);
}

print('Initialization... ');

$site_dir = __DIR__;

while (!(
	$site_dir == '.'
	|| $site_dir == '/'
	|| $site_dir == '\\'
	|| (
		file_exists($site_dir . '/index.php')
		|| file_exists($site_dir . '/index.html')
		|| file_exists($site_dir . '/index.htm')
	) && file_exists($site_dir . '/vendor')
)) {
	$site_dir = dirname($site_dir);
}

if (!file_exists($site_dir . '/index.php')) {
	print('FAILED<br>');
	print('Site directory not found. You should initiate it manually first.');
	exit(2);
}

require_once $site_dir . '/vendor/autoload.php';

$fs = new Symfony\Component\Filesystem\Filesystem();
$za = new splitbrain\PHPArchive\Zip();

$code = 0;

$tmp = tempnam(sys_get_temp_dir(), 'zip');

$zip = tempnam(sys_get_temp_dir(), 'zip');
unlink($zip);
$zip = $zip . '.zip';

try {
	if (file_exists($site_dir . '/composer.phar')) {
		throw new Exception('Development site can not be updated');
	}
	
	print('OK<br>');
	
	print('Connecting to update server... ');
	$hurl = fopen($url, "r");

	if (! $hurl) {
		throw new Exception('Can not open URL ' . $url);
	}
	print('OK<br>');
	
	print('Creating local file... ');
	$hzip = fopen($zip, "w+");
	
	if (!$hzip) {
		fclose($hurl);
		throw new Exception('Can not create file ' . $zip);
	}
	print('OK<br>');
	
	print('Downloading update... ');	
	do {
		$data = fread($hurl, 1024);
		if ($data) {
			$n = fwrite($hzip, $data);
		}
	} while ($data && $n);	
	print('OK<br>');
				
	fclose($hzip);	
	fclose($hurl);
	
	print('Extracting files... ');
	$za->open($zip);
	
	if (file_exists($tmp)) {
		unlink($tmp);
	}
	mkdir($tmp);
		
	$za->extract($tmp);
	print('OK<br>');
	
	print('Search for source directory... ');
	$source_dir = null;
	
	foreach(scandir($tmp) as $file){
		$path = realpath($tmp . DIRECTORY_SEPARATOR . $file);
		if (is_dir($path) && $file != '.' && $file != '..'
			&& (
				file_exists($path . '/index.php')
				|| file_exists($path . '/index.html')
				|| file_exists($path . '/index.htm')
			)
			&& file_exists($path . '/vendor')
		) {	
			$source_dir = $path;
			break;
		}
	}
	
	if (!$source_dir) {
		throw new Exception('Site root not found in the update archive');
	}
	print('OK<br>');
	
	print('Removing old files... ');
	$dir_list = scandir($site_dir);
	
	if (!$dir_list) {
		throw new Exception('Scan dir error ' . $site_dir);
	}
	
	if (file_exists($site_dir . '/composer.phar')) {
		throw new Exception('Development site can not be updated');
	}
	
	foreach($dir_list as $file){
		$path = realpath($site_dir . DIRECTORY_SEPARATOR . $file);
		if(!is_dir($path)){
			if (preg_match('/\.sh$/i', $file) != 1) {
				unlink($path);
			}
		} else if ($file != '.' && $file != '..') {
			$fs->remove($path);
		}
	}	
	print('OK<br>');
	
	print('Copying new files... ');	
	$fs->mirror($source_dir, $site_dir);			
	print('OK<br>');
	
} catch (Exception $e) {
	print('FAILED<br>' . $e->getMessage() . '<br>');
	$code = 3;
}

print('Cleanning... ');
if (file_exists($zip)) {		
	unlink($zip);
}
if (file_exists($tmp)) {
	$fs->remove($tmp);
}
print('OK<br>');

exit($code);

?>
