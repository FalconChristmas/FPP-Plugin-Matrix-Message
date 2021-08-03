<?php

//get the string between two characters
function get_string_between ($str,$from,$to) {
	
	$string                                         = substr($str,strpos($str,$from)+strlen($from));
	
	if (strstr ($string,$to,TRUE) != FALSE) {
		
		$string                                     =   strstr ($string,$to,TRUE);
		
	}
	
	return $string;
	
}
//update plugin

function updatePluginFromGitHub($gitURL, $branch="master", $pluginName) {


	global $settings;
	logEntry ("updating plugin: ".$pluginName);

	logEntry("settings: ".$settings['pluginDirectory']);

	//create update script
	//$gitUpdateCMD = "sudo cd ".$settings['pluginDirectory']."/".$pluginName."/; sudo /usr/bin/git git pull ".$gitURL." ".$branch;

	$pluginUpdateCMD = "/opt/fpp/scripts/update_plugin ".$pluginName;

	logEntry("update command: ".$pluginUpdateCMD);


	exec($pluginUpdateCMD, $updateResult);

	//logEntry("update result: ".print_r($updateResult));

	//loop through result
	return;// ($updateResult);



}

function directoryToArray($directory, $recursive) {
	$array_items = array();
	if ($handle = opendir($directory)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				if (is_dir($directory. "/" . $file)) {
					if($recursive) {
						$array_items = array_merge($array_items, directoryToArray($directory. "/" . $file, $recursive));
					}
					$file = $directory . "/" . $file;
					$array_items[] = preg_replace("/\/\//si", "/", $file);
				} else {
					$file = $directory . "/" . $file;
					$array_items[] = preg_replace("/\/\//si", "/", $file);
				}
			}
		}
		closedir($handle);
	}
	return $array_items;
}


?>
