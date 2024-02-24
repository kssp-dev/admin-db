<?php

$url = getenv('ADMIN_DB_UPDATE_URL');

if (empty($url)) {
	exit('ADMIN_DB_UPDATE_URL environment variable MUST be declared');
}

if (!file_exists('index.php')) {
	exit('Site directory not found. You should initiate it manually first.');
}

if (file_exists('composer.phar')) {
	exit('Development site can not be updated');
}

print('Initialization... ');

$site_dir = getcwd();

require_once __DIR__ . '/vendor/autoload.php';

//print($url . '<br>');
//print($zip . '<br>');

$done = false;

$zip = tempnam(sys_get_temp_dir(), 'zip');
unlink($zip);
$zip = $zip . '.zip';

$tmp = tempnam(sys_get_temp_dir(), 'zip');

$fs = new Symfony\Component\Filesystem\Filesystem();

print('OK<br>');
print('Connecting to update server... ');
$hurl = fopen($url, "r");

if ($hurl) {
	print('OK<br>');
	print('Creating local file... ');
	$hzip = fopen($zip, "w+");
	
	if ($hzip) {
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
	}
	
	fclose($hurl);
}
	
try {
	print('Extracting files... ');

	$za = new splitbrain\PHPArchive\Zip();
	$za->open($zip);
	
	unlink($tmp);
	mkdir($tmp);
		
	$za->extract($tmp);	
		
	print('OK<br>');
	print('Removing old files... ');
	
	$dir_list = scandir($site_dir);
	
	if ($dir_list) {
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
		
		foreach(scandir($tmp) as $file){
			$path = realpath($tmp . DIRECTORY_SEPARATOR . $file);
			if (is_dir($path) && $file != '.' && $file != '..') {	
				$fs->mirror($path, $site_dir);
				
				print('OK<br>');
				
				$done = true;
		
				break;
			}
		}
	}	
} catch (Exception $e) {}
	
if (!$done) {
	print('FAILED<br>');
}

unlink($zip);
$fs->remove($tmp);

?>
