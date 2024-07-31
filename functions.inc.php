<?php

include_once dirname(__FILE__) . "/../fpp-matrixtools/scripts/matrixtools.php.inc";


function outputMessages($queueMessages) {
	global $DEBUG, $pluginDirectory,$MESSAGE_TIMEOUT, $fpp_matrixtools_Plugin, $fpp_matrixtools_Plugin_Script,$Matrix,$MATRIX_FONT,$MATRIX_FONT_SIZE,$MATRIX_PIXELS_PER_SECOND,$COLOR, $INCLUDE_TIME, $TIME_FORMAT, $HOUR_FORMAT,$SEPARATOR, $MATRIX_FONT_ANTIALIAS, $waitForScroll,$DURATION,$overlayMode;

	//print_r($queueMessages);
	
	if($DEBUG)
		logEntry("MESSAGE QUEUE: Inside function ".__METHOD__,0,__FILE__,__LINE__);

	if (count($queueMessages) <=0) {
		logEntry("No messages to output ");
		return;
	}
	

	If (strtoupper(trim($COLOR)) == "RANDOM") {
		//print_r ("Start counter");
		$counter=rand(0,5);
		//print_r ("Start switch");
		switch ($counter) {
			case "0":
				$mycolor = "indigo";
				//echo $mycolor;
				break;
			case "1":
				$mycolor = "red";
				//echo $mycolor;
				break;
			case "2":
				$mycolor = "green";
				//echo $mycolor;
				break;
			case "3":
				$mycolor = "blue";
				//echo $mycolor;
				break;
			case "4":
				$mycolor = "yellow";
				//echo $mycolor;
				break;
			case "5":
				$mycolor = "purple";
				//echo $mycolor;
				break;
		}
		logEntry("MATRIX MESSAGE: Selecting RANDOM COLOR: ".$mycolor);
		//print_r ("End switch");
		$COLOR=($mycolor);
		//print_r ($COLOR);
	} 
	

    $auto = "false";
    if ($waitForScroll == false) {
        if ($overlayMode == 1) {
            $auto = "Enabled";
        } else if ($overlayMode == 2) {
            $auto = "Transparent";
        }
        if ($overlayMode == 3) {
            $auto = "Transparent RGB";
        }
    }
    if ($auto == "false") {
        enableMatrixToolOutput($Matrix);
    }

    for ($i=0;$i<=count($queueMessages)-1;$i++) {
        $messageText = "";
	
        if($INCLUDE_TIME == 1 || $INCLUDE_TIME == "on") {
		
            switch ($HOUR_FORMAT) {
                
                case "12":
                    $messageTime = date($TIME_FORMAT);
                    break;
                    
                case "24":
                    
                    $messageTime = date($TIME_FORMAT);
                    
                    break;
            }
            
            logEntry( "Message time: ".$messageTime);
            
            $messageText = "Time: ".$messageTime." ".$SEPARATOR." ";
        }
    	
        $messageParts = explode("|",$queueMessages[$i]);

        //echo "0: ".$messageParts[0]."\n";
        //echo "1: ".$messageParts[1]."\n";
        //echo "2: " .$messageParts[2]."\n";
        //echo "3: ".$messageParts[3]."\n";

        $messageText .= urldecode($messageParts[1]);
        logEntry("MATRIX PLUGIN: Writing last read for plugin BEFORE sending Message!: ".urldecode($messageParts[2]). ": ".urldecode($messageParts[0]));
    
        WriteSettingToFile("LAST_READ",urldecode($messageParts[0]),urldecode($messageParts[2]));
    
        $MATRIX_PIXELS_PER_SECOND = intval($MATRIX_PIXELS_PER_SECOND);
        $MATRIX_FONT_SIZE = intval($MATRIX_FONT_SIZE);
        $Position = "R2L";
        if ($MATRIX_PIXELS_PER_SECOND == 0) {
            $Position = "Center";
        }
        // echo  "$Matrix, $messageText, $Position, $MATRIX_FONT, $MATRIX_FONT_SIZE, $COLOR, $MATRIX_PIXELS_PER_SECOND, $MATRIX_FONT_ANTIALIAS";
          
        DisplayTextOnModel("localhost", $Matrix, $messageText, $Position, $MATRIX_FONT, $MATRIX_FONT_SIZE, $COLOR, $MATRIX_PIXELS_PER_SECOND, $MATRIX_FONT_ANTIALIAS, $DURATION, $auto);
        if ($waitForScroll) {
            if ($Position != "Center") {
                $isLocked = GetModelData("localhost", $Matrix)["isLocked"];
                while ($isLocked) {
                    sleep(1);
                    $isLocked = GetModelData("localhost", $Matrix)["isLocked"];
                }
            } else {
                sleep($DURATION);
            }
        }
    }
    
    
    if ($waitForScroll == true) {
        disableMatrixToolOutput($Matrix);
    }
}
function getInstalledPlugins($host) {
	include_once 'excluded_plugins.inc.php';
	
    if ($host == "") {
        $host = "localhost";
    }
    $ch = curl_init("http://" . $host . "/api/plugin");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
	$pluginsInstalled = json_decode($data, true);
    $pluginList = [];
	for($i=0;$i<=count($pluginsInstalled)-1;$i++) {
		if(in_array($pluginsInstalled[$i],$EXCLUDE_PLUGIN_ARRAY)) {
			continue;
		}
		$pluginList[$pluginsInstalled[$i]]=$pluginsInstalled[$i];
	}
	//error_log( $test = var_dump($pluginList), 0);
	
    return $pluginList;
}



function getScrollSpeed(){
	$MAX_PIXELS_PER_SECOND = 200;

        for($i=0;$i<=$MAX_PIXELS_PER_SECOND;$i++) {
			$scrollSpeed[$i]= $i;
        }
	return $scrollSpeed;
}
function getDuration(){
	$MAX_DURATION = 300;

        for($i=1;$i<=$MAX_DURATION;$i++) {
			$maxDuration[$i]= $i;
        }
	return $maxDuration;
}

function getFontSizes(){
	$maxFontSize = 80;
	
	for($i=5; $i<=$maxFontSize; $i++) {
		$fontSize[$i]=$i;
    }
return $fontSize;	
}


function getFontsInstalled() {

	$fontsList = GetFonts("localhost"); // this uses the fpp-matrix tools plugin to get the fonts that it can use!
	for($i=1;$i<=count($fontsList)-1;$i++) {
		$installedFonts[$fontsList[$i]]=$fontsList[$i];
	}
return $installedFonts;        
}


//is fppd running?????
function isFPPDRunning() {
	$FPPDStatus=null;
	logEntry("Checking to see if fpp is running...");
        exec("if ps cax | grep -i fppd; then echo \"True\"; else echo \"False\"; fi",$output);

        if($output[1] == "True" || $output[1] == 1 || $output[1] == "1") {
                $FPPDStatus = "RUNNING";
        }
	//print_r($output);

	return $FPPDStatus;
        //interate over the results and see if avahi is running?

}
//get current running playlist
function getRunningPlaylist() {

	global $sequenceDirectory;
	$playlistName = null;
	$i=0;
	//can we sleep here????

	//sleep(10);
	//FPPD is running and we shoud expect something back from it with the -s status query
	// #,#,#,Playlist name
	// #,1,# = running

	$currentFPP = file_get_contents("/tmp/FPP.playlist");
	logEntry("Reading /tmp/FPP.playlist : ".$currentFPP);
	if($currentFPP == "false") {
		logEntry("We got a FALSE status from fpp -s status file.. we should not really get this, the daemon is locked??");
	}
	$fppParts="";
	$fppParts = explode(",",$currentFPP);
//	logEntry("FPP Parts 1 = ".$fppParts[1]);

	//check to see the second variable is 1 - meaning playing
	if($fppParts[1] == 1 || $fppParts[1] == "1") {
		//we are playing

		$playlistParts = pathinfo($fppParts[3]);
		$playlistName = $playlistParts['basename'];
		logEntry("We are playing a playlist...: ".$playlistName);
		
	} else {

		logEntry("FPPD Daemon is starting up or no active playlist.. please try again");
	}
	
	
	//now we should have had something
	return $playlistName;
}
function logEntry($data,$logLevel=1, $sourceFile = "", $sourceLine = "") {

	global $logFile,$myPid, $LOG_LEVEL;

	
	if($logLevel <= $LOG_LEVEL) 
		//return
		
		if($sourceFile == "") {
			$sourceFile = $_SERVER['PHP_SELF'];
		}
		$data = $sourceFile." : [".$myPid."] ".$data;
		
		if($sourceLine !="") {
			$data .= " (Line: ".$sourceLine.")";
		}
		
		$logWrite= fopen($logFile, "a") or die("Unable to open file!");
		fwrite($logWrite, date('Y-m-d h:i:s A',time()).": ".$data."\n");
		fclose($logWrite);
}



function processCallback($argv) {
	global $DEBUG,$pluginName;
	
	
	if($DEBUG)
		print_r($argv);
	
	
	$registrationType = $argv[2];
	$data =  $argv[4];
	
	logEntry("PROCESSING CALLBACK: ".$registrationType);
	$clearMessage=FALSE;
	
	switch ($registrationType)
	{
		case "media":
			if($argv[3] == "--data")
			{
				$data=trim($data);
				logEntry("DATA: ".$data);
				$obj = json_decode($data);
	
				$type = $obj->{'type'};
				logEntry("Type: ".$type);	
				switch ($type) {
						
					case "sequence":
						logEntry("media sequence name received: ");	
						processSequenceName($obj->{'Sequence'},"STATUS");
							
						break;
					case "media":
							
						logEntry("We do not support type media at this time");
							
						//$songTitle = $obj->{'title'};
						//$songArtist = $obj->{'artist'};
	
	
						//sendMessage($songTitle, $songArtist);
						//exit(0);
	
						break;
						
						case "both":
								
						logEntry("We do not support type media/both at this time");
						//	logEntry("MEDIA ENTRY: EXTRACTING TITLE AND ARTIST");
								
						//	$songTitle = $obj->{'title'};
						//	$songArtist = $obj->{'artist'};
							//	if($songArtist != "") {
						
						
						//	sendMessage($songTitle, $songArtist);
							//exit(0);
						
							break;
	
					default:
						logEntry("We do not understand: type: ".$obj->{'type'}. " at this time");
						exit(0);
						break;
	
				}
	
	
			}
	
			break;
			exit(0);
	
		case "playlist":

			logEntry("playlist type received");
			if($argv[3] == "--data")
                        {
                                $data=trim($data);
                                logEntry("DATA: ".$data);
                                $obj = json_decode($data);
				$sequenceName = $obj->{'sequence0'}->{'Sequence'};	
				$sequenceAction = $obj->{'Action'};	
                                                processSequenceName($sequenceName,$sequenceAction);
                                                //logEntry("We do not understand: type: ".$obj->{'type'}. " at this time");
                                        //      logEntry("We do not understand: type: ".$obj->{'type'}. " at this time");
			}

			break;
			exit(0);			
		default:
			exit(0);
	
	}
	

}
?>
