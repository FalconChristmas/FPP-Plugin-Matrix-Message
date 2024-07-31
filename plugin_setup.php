<?php


include_once "common.php";
include_once "functions.inc.php";
include_once "commonFunctions.inc.php";
include_once "MatrixFunctions.inc.php";


include_once 'version.inc';
$pluginName = basename(dirname(__FILE__));


$fpp_matrixtools_Plugin = "fpp-matrixtools";
$fpp_matrixtools_Plugin_Script = "scripts/matrixtools";
$fpp_message_queue_Plugin = "FPP-Plugin-MessageQueue";
$FPP_MATRIX_PLUGIN_ENABLED=false;
$logFile = $settings['logDirectory']."/".$pluginName.".log";
$gitURL = "https://github.com/FalconChristmas/FPP-Plugin-Matrix-Message.git";



if (isset($pluginSettings['DEBUG'])) {
    $DEBUG = urldecode($pluginSettings['DEBUG']);
} else {
    $DEBUG = false;
}

	
if(file_exists($pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script) && file_exists($pluginDirectory."/".$fpp_message_queue_Plugin ))  { // show error message if required plugins not installed
	logEntry($pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script." EXISTS: Enabling");
	$FPP_MATRIX_PLUGIN_ENABLED=true;
} else {
	if (!file_exists($pluginDirectory."/".$fpp_message_queue_Plugin )){
		logEntry("Message Queue to Matrix Overlay plugin is not installed, cannot use this plugin with out it");
		echo "<h1>Message Queue to Matrix Overlay is not installed. Install the plugin and revisit this page to continue.</h1><br/>";	
	}
	if (!file_exists($pluginDirectory."/".$fpp_matrixtools_Plugin."/".$fpp_matrixtools_Plugin_Script)){
        logEntry("FPP Matrix tools plugin is not installed, cannot use this plugin with out it");
        echo "<h1>FPP Matrix Tools plugin is not installed. Install the plugin and revisit this page to continue.</h1>";
	}
	exit(0);
}

$canvasWidth = 1000;
$canvasHeight = 400;
?>
<style>

.matrix-tool-bottom-panel {
	padding-top: 0px !important;
}

.red {
	background: #ff0000;
}

.green {
	background: #00ff00;
}

.blue {
	background: #0000ff;
}

.yellow {
	background: #ffff00;
}

.orange {
	background: #ff8800;
}

.white {
	background: #ffffff;
}

.black {
	background: #000000;
}

.colorButton {
	-moz-transition: border-color 250ms ease-in-out 0s;
	background-clip: padding-box;
	border: 2px solid rgba(0, 0, 0, 0.25);
	border-radius: 50% 50% 50% 50%;
	cursor: pointer;
	display: inline-block;
	height: 20px;
	margin: 1px 2px;
	width: 20px;
}

#currentColor {
    border: 2px solid #000000;
}

</style>
<script type="text/javascript">

    function ShowColorPicker() {
		if ($('#ShowColorPicker').is(':checked')) {
            $('#colpicker').show();
        } else {
            $('#colpicker').hide();
        }
    }

	function setColor(color, updateColpicker = true) {
		if (color.substring(0,1) != '#')
			color = '#' + color;

        pluginSettings['COLOR'] = color;
        SetPluginSetting('FPP-Plugin-Matrix-Message', 'COLOR', color, 0, 0);
        $('#currentColor').css('background-color', color);

		currentColor = color;

        if (updateColpicker)
		    $('#colpicker').colpickSetColor(color);

		
	}

</script>


<div id="<?echo $pluginName;?>" class="settings">
<fieldset>
<legend><?php echo $pluginName." Version: ".$pluginVersion;?> Support Instructions</legend>

<p>Known Issues:
<ul>
<li>NONE</li>
</ul>
<p>Configuration:
<ul>
<li>This plugin allows you to use the fpp-matrixtools plugin to output messages from the MessageQueue system</li>
<li>Select your plugins to output to your matrix below and click SAVE</li>
<li>Configure your Matrix first before selecting here</li>
</ul>


</div>
<input type=hidden name=LAST_READ value= <? $LAST_READ ?>> <!-- is this needed?? -->
<p>ENABLE PLUGIN: <?PrintSettingCheckbox("Matrix Message Plugin", "ENABLED", 0, 0, "ON", "OFF", $pluginName ,$callbackName = "", $changedFunction = ""); ?> </p>
<p>Matrix Name: <? PrintSettingSelect("MATRIX", "MATRIX", 0, 0, $defaultValue="", $values = GetOverlayList(), $pluginName, $callbackName = "", $changedFunction = ""); ?> </p>
<p>Overlay Mode: <? PrintSettingSelect("OVERLAY_MODE", "OVERLAY_MODE", 0, 0, "", Array("Full Overlay" => "1", "Transparent" => "2", "Transparent RGB" => "3"), $pluginName, $callbackName = "", $changedFunction = ""); ?> </p>
<p><h3>The Overlay mode determines how you want your message to display.</h3>
<ul>
	<li>Full Overlay- This will blank out the model and only display your message</li>
	<li>Transparent- This will display your message over the top of whatever is displaying on your matrix <br/>
		but the colors will blend slightly with what is currently being displayed</li>
	<li>Transparent RGB- This will display your message over the top of whatever is displaying on your matrix <br/>
		the colors will override what is currently being displayed</li> 
</ul>
<p>Include Time: <?PrintSettingCheckbox("Include Time", "INCLUDE_TIME", 0, 0, "on", "off", $pluginName , ""); ?> 
 Time Format: <? PrintSettingSelect("Time Format", "TIME_FORMAT", 0, 0, $defaultValue="HH:MM", Array("HH:MM" => "h:i", "HH:MM:SS" => "h:i:s"), $pluginName, $callbackName = "", $changedFunction = ""); ?> 
 Hour Format: <? PrintSettingSelect("Hour Format", "HOUR_FORMAT", 0, 0, $defaultValue="24 Hour", Array("24 Hour" => "24", "12 Hour" => "12"), $pluginName, $callbackName = "", $changedFunction = ""); ?> </p>
<p>Plugins to use: <? PrintSettingMultiSelect("PLUGINS", "PLUGINS", 0, 0, $defaultValue="", $values = getInstalledPlugins($host=""), $pluginName, $callbackName = "", $changedFunction = ""); ?></p>
<p>Font: <? PrintSettingSelect("FONT", "FONT", 0, 0, $defaultValue="", getFontsInstalled(), $pluginName, $callbackName = "", $changedFunction = ""); ?>
 Font Size: <? PrintSettingSelect("FONT_SIZE", "FONT_SIZE", 0, 0, $defaultValue="20", getFontSizes(), $pluginName, $callbackName = "", $changedFunction = ""); ?>
 Anti-Aliased: <?PrintSettingCheckbox("FONT_ANTIALIAS", "FONT_ANTIALIAS", 0, 0, "1", "", $pluginName , ""); ?> 
 Scroll Speed: <? PrintSettingSelect("PIXELS_PER_SECOND", "PIXELS_PER_SECOND", 0, 0, $defaultValue="20", getScrollSpeed(), $pluginName, $callbackName = "", $changedFunction = ""); ?> </p>
Duration: <? PrintSettingSelect("DURATION", "DURATION", 0, 0, $defaultValue="10", getDuration(), $pluginName, $callbackName = "", $changedFunction = ""); ?> </p>
<p><b>If you set the scroll speed to 0, then the message will display on the center of the matrix <br/>
for the number of seconds set in the Duration</b></p> 
<div id= "divCanvas" class='ui-tabs-panel matrix-tool-bottom-panel'>
			<table border=0>
            <tr><td valign='top'>
			<div>
				<table border=0>
					<tr><td valign='top'>Pallette:</td>
						<td><div class='colorButton red' onClick='setColor("#ff0000");'></div>
							<div class='colorButton green' onClick='setColor("#00ff00");'></div>
							<div class='colorButton blue' onClick='setColor("#0000ff");'></div>
						    <div class='colorButton white' onClick='setColor("#ffffff");'></div>
							<div class='colorButton black' onClick='setColor("#000000");'></div>
						</td>
					</tr>
                    <tr><td>Current Color:</td><td><span id='currentColor'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></td></tr>
            <tr><td colspan='2'>Show Color Picker: <? PrintSettingCheckbox("Show Color Picker", "ShowColorPicker", 0, 0, "1", "0", "FPP-Plugin-Matrix-Message", "ShowColorPicker"); ?></td></tr>
            <tr><td valign='top' colspan='2'>
                <div id="colpicker"></div>
			</td></tr>
				</table>
			</div>
			</td></tr>
            </table>
			
			
				
		</div>


<p>To report a bug, please file it against <?php echo $gitURL;?>



</fieldset>
</div>
<br />
</html>
<script>

	$("#matrixTabs").tabs({active: 0, cache: true, spinner: "", fx: { opacity: 'toggle', height: 'toggle' } });

    var colpickTimer = null;
	$('#colpicker').colpick({
		flat: true,
		layout: 'rgbhex',
		color: '#ff0000',
		submit: false,
		onChange: function(hsb,hex,rgb,el,bySetColor) {
            if (colpickTimer != null)
                clearTimeout(colpickTimer);

            colpickTimer = setTimeout(function() { setColor('#'+hex, false); }, 500);
		}
	});

    if (pluginSettings.hasOwnProperty('COLOR') && pluginSettings['COLOR'] != '') {
        currentColor = pluginSettings['COLOR'];
        $('#currentColor').css('background-color', currentColor);
    }

    ShowColorPicker();
	GetBlockList();
    GetFontList();

</script>
