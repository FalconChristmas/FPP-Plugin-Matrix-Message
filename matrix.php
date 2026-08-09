<?php
//error_reporting(0);
//TODO:
//Oct 31: Installed the ability to send a message directly from a plugin using 'subscribedPlugin' and 'onDemandMessage'


$pluginName = basename(dirname(__FILE__));
$MatrixMessageVersion = "3.1";
$myPid = getmypid();

$DEBUG=false;

$skipJSsettings = 1;
$fppWWWPath = '/opt/fpp/www/';
set_include_path(get_include_path() . PATH_SEPARATOR . $fppWWWPath);

require("common.php");
//include_once("/opt/fpp/www/common.php");
include_once("functions.inc.php");
include_once("MatrixFunctions.inc.php");
include_once("excluded_plugins.inc.php");
include_once("commonFunctions.inc.php");
require ("lock.helper.php");
define('LOCK_DIR', '/tmp/');
define('LOCK_SUFFIX', $pluginName.'.lock');
$messageQueue_Plugin = "FPP-Plugin-MessageQueue"; // NBP 2/2/2020
//if (strpos($pluginName, "FPP-Plugin") !== false) {
//    $messageQueue_Plugin = "FPP-Plugin-MessageQueue";
//}
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;

$fpp_matrixtools_Plugin = "fpp-matrixtools";
$fpp_matrixtools_Plugin_Script = "scripts/matrixtools";

$logFile = $settings['logDirectory']."/".$pluginName.".log";

$messageQueuePluginPath = $pluginDirectory."/".$messageQueue_Plugin."/";

$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE",$messageQueue_Plugin));


$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
if (file_exists($pluginConfigFile)){
	$pluginSettings = parse_ini_file($pluginConfigFile);
}else{
    $pluginSettings= array();
}
	
	//if it is locked then exit. however; we may need to tell it to keep running in a message queue situation
	//do not run it again - if the matrix is active. //this feature blocks this as well
	//check for other active messages below
	if(($pid = lockHelper::lock()) === FALSE) {
		exit(0);
	
	}
	

//$ENABLED = trim(urldecode(ReadSettingFromFile("ENABLED",$pluginName)));
if (isset($pluginSettings['ENABLED'])){
    $ENABLED = $pluginSettings['ENABLED'];
}else{
     $ENABLED ="";
}



if($ENABLED != "ON") {

	logEntry("Plugin Status: DISABLED Please enable in Plugin Setup to use & Restart FPPD Daemon");
	lockHelper::unlock();
	exit(0);

}

//get the FPP version - needed for the various FPPmm commands
$fpp_version = "v" . exec("git --git-dir=/opt/fpp/.git/ describe --tags", $output, $return_val);
if ( $return_val != 0 )
	$fpp_version = "Unknown";
	unset($output);
	logEntry("FPP version: ".$fpp_version);
	
	//example version is : v1.9-50-gfe8e9a5
	//trim before the first -
	$fpp_version = trim(get_string_between ($fpp_version,"v","-"));
	
	logEntry("FPP version: ".$fpp_version);

//Get plugin settings and if they do not exist, set defaults to use so it will still work

if (isset($pluginSettings['PLUGINS'])){
    $MATRIX_PLUGIN_OPTIONS = $pluginSettings['PLUGINS'];
}else{
    $MATRIX_PLUGIN_OPTIONS = "";
    logEntry("No plugins specifically defined, plugin will not work unless plugins are defined to use this program");
}

if (isset($pluginSettings['FONT'])){
    $MATRIX_FONT= $pluginSettings['FONT'];
}else{
    $MATRIX_FONT= reset(getFontsInstalled());
    logEntry("Font not specifically defined, using default font instead");
}

if (isset($pluginSettings['FONT_SIZE'])){
    $MATRIX_FONT_SIZE= $pluginSettings['FONT_SIZE'];
}else{
    $MATRIX_FONT_SIZE="23";
    logEntry("Font size not specifically defined, using default font size instead");
}

if (isset($pluginSettings['FONT_ANTIALIAS'])){
    $MATRIX_FONT_ANTIALIAS= $pluginSettings['FONT_ANTIALIAS'];
}else{
    $MATRIX_FONT_ANTIALIAS="";
    logEntry("Antialias not specifically defined, using default none instead"); 
}

if (isset($MATRIX_FONT_ANTIALIAS) && $MATRIX_FONT_ANTIALIAS == "1") {
    $MATRIX_FONT_ANTIALIAS = true;
} else {
    $MATRIX_FONT_ANTIALIAS = false;
}

if (isset($pluginSettings['COLOR'])){
    $COLOR= urldecode($pluginSettings['COLOR']);
}else{
    $COLOR="#00ff00";
    logEntry("Color not specifically defined, using default color of Green instead");
}

if (isset($pluginSettings['PIXELS_PER_SECOND'])){
    $MATRIX_PIXELS_PER_SECOND = $pluginSettings['PIXELS_PER_SECOND'];
}else{
    $MATRIX_PIXELS_PER_SECOND = "30";
    logEntry("Pixels per Second not specifically defined, using default value instead");
}

if (isset($pluginSettings['DURATION'])){
    $DURATION = $pluginSettings['DURATION'];
}else{
     $DURATION ="10";
     logEntry("Duration not specifically defined, using default value instead");
}

if (isset($pluginSettings['INCLUDE_TIME'])){
    $INCLUDE_TIME = urldecode($pluginSettings['INCLUDE_TIME']);
}else{
    $INCLUDE_TIME = "";
    logEntry("Include Time not specifically defined, using default instead");
}

if (isset($pluginSettings['TIME_FORMAT'])){
    $TIME_FORMAT = urldecode($pluginSettings['TIME_FORMAT']);
}else{
    $TIME_FORMAT = "h:i:s";
    logEntry("Time Format not specifically defined, using default instead");
}

if (isset($pluginSettings['HOUR_FORMAT'])){
    $HOUR_FORMAT = urldecode($pluginSettings['HOUR_FORMAT']);
}else{
    $HOUR_FORMAT = "24";
    logEntry("Hour Format not specifically defined, using default instead");
}

if (isset($pluginSettings['MATRIX'])){
    $Matrix = urldecode($pluginSettings['MATRIX']);
}else{
    $Matrix = reset(GetOverlayList());
    logEntry("Overlay Model not specifically defined, using default Overlay instead");
}

if (isset($pluginSettings['OVERLAY_MODE'])){
    $overlayMode = urldecode($pluginSettings['OVERLAY_MODE']);
}else{
     $overlayMode = "1";
     logEntry("Overlay Mode not specifically defined, using default instead");
}

if (isset($pluginSettings['MESSAGE_TIMEOUT'])){
    $MATRIX_MESSAGE_TIMEOUT = $pluginSettings['MESSAGE_TIMEOUT'];
}else{
    $MATRIX_MESSAGE_TIMEOUT = "10";
    logEntry("Message Timeout not specifically defined, using default instead");
}

if (isset($pluginSettings['DEBUG'])) {
    $DEBUG = urldecode($pluginSettings['DEBUG']);
} else {
    $DEBUG = false;
}


$SEPARATOR = "|";


if(trim($Matrix == "")) {
	logEntry("No Matrix name is  configured for output: exiting");
	lockHelper::unlock();
	exit(0);
} else {
	logEntry("Configured matrix name: ".$Matrix);
	
}


if($MATRIX_MESSAGE_TIMEOUT == "" || $MATRIX_MESSAGE_TIMEOUT == null) {
	$MESSAGE_TIMEOUT = 10;
	
} else {
	$MESSAGE_TIMEOUT = (int)trim($MATRIX_MESSAGE_TIMEOUT);
}


if(file_exists($messageQueuePluginPath."functions.inc.php")) {
    include $messageQueuePluginPath."functions.inc.php";
    $MESSAGE_QUEUE_PLUGIN_ENABLED=true;
} else {
    logEntry("Message Queue not installed, cannot use this plugin with out it");
    lockHelper::unlock();
    exit(0);
}

if (isset($_GET['subscribedPlugin'])) {
    $subscribedPlugin = $_GET['subscribedPlugin'];
    logEntry("Only getting plugin messages for plugin: ".$subscribedPlugin);
    $MATRIX_PLUGIN_OPTIONS = $subscribedPlugin;
}

if (isset($_GET['onDemandMessage'])) {
	$onDemandMessage = $_GET['onDemandMessage'];
	logEntry("Receiving an onDemandMessage from subscribed plugin: ".$subscribedPlugin);
	$MATRIX_PLUGIN_OPTIONS = $subscribedPlugin;
}

$waitForScroll = true;
if (isset($_GET['nowait'])) {
    $waitForScroll = false;
}



$MATRIX_ACTIVE = false;
        
//TODO: Change this to get pluguin messages from their resprective datbases. once this is done, then can just get new plugin messages that way!

if ($MESSAGE_QUEUE_PLUGIN_ENABLED) {
	if(isset($onDemandMessage) && $onDemandMessage != "") {
		//got an ondemand message, and we may get more and more of these so we should output them all
		$queueMessages = array(time() . "|" . $onDemandMessage . "|" . $subscribedPlugin);
	} else {
        $queueMessages = getNewPluginMessages($MATRIX_PLUGIN_OPTIONS);
	}
	
	$messageCount = count($queueMessages);
    if ($messageCount > 0) {
        //if($queueMessages != null || $queueMessages != "") {
        $MATRIX_ACTIVE = true;
        $queueCount =0;
        
        $LOOP_COUNT =0;
        do {
            logEntry("MATRIX MESSAGE: LOOP COUNT: ".$LOOP_COUNT++);
            logEntry("MATRIX MESSAGE: QUEUE COUNT: ".$queueCount,0,__FILE__,__LINE__);// $sourceFile, $sourceLine)
            if($queueCount >0) {
                foreach ($queueMessages as $tmpMSG) {
                    logEntry("MATRIX MESSAGE: LOOP ID: ".$LOOP_COUNT." MSG: ".$tmpMSG,0,__FILE__,__LINE__);
                }
            }
            //extract the high water mark from the first message and write that back to the plugin! or
            //gets the same message twice in a flood of incomming on demand messages
            
            // Jan 3:$messageQueueParts = explode("|",$queueMessages[0]);
            //	logEntry("MATRIX plugin: Writing high water for plugin:".$MATRIX_PLUGIN_OPTIONS." ".urldecode($messageQueueParts[0]));
        	////	WriteSettingToFile("LAST_READ",urldecode($messageQueueParts[0]),$MATRIX_PLUGIN_OPTIONS);
        		
        	//echo "0: ".$messageParts[0]."\n";
        	logEntry("-----------------------------------");
        		
            outputMessages($queueMessages);
			
            if($DEBUG)
                logEntry("MATRIX PLUGIN OPTIONS[0] = ".$MATRIX_PLUGIN_OPTIONS);
            if((strtoupper($MATRIX_PLUGIN_OPTIONS) != "CFOLNANOMATRIXSYSTEM") && (!isset($onDemandMessage) || $onDemandMessage == "")) {
				if($DEBUG) {
					logEntry("MATRIX MESSAGE: On demand mode, querying for new plugin messages");
				}
                //get new messages
                $queueMessages = null;
                
                $queueMessages = getNewPluginMessages($MATRIX_PLUGIN_OPTIONS);
                $queueCount = count($queueMessages);
                logEntry("Matrix Message NEW QUEUE COUNT: ".$queueCount);
                
                sleep(1);
			}
       	} while ($queueCount > 0) ;
        
    } else {
       	logEntry("MATRIX MESSAGE: No messages  exists??");
    }
        
} else {
        logEntry("MessageQueue plugin is not enabled/installed");
        lockHelper::unlock();
        exit(0);
}

lockHelper::unlock();

?>
