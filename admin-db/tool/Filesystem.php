<?php

class Filesystem
{
    public static function scandirtree($path = '', $cut = null, &$name = array())
	{
		$path = $path == '' ? dirname(__FILE__) : $path;
		if (substr($path, -1) != DIRECTORY_SEPARATOR) {
			$path = $path.DIRECTORY_SEPARATOR;
		}
		$cut = $cut ?? strlen($path);
		
		$lists = @scandir($path);

		if(!empty($lists))
		{
			foreach($lists as $f)
			{
				if(is_dir($path.$f))
				{
					if($f != ".." && $f != ".")
					{
						Filesystem::scandirtree($path.$f, $cut, $name);
					}
				}
				else
				{
					$name[] = substr($path.$f, $cut);
				}
			}
		}
		return $name;
	}
}

?>
