<?php
    
include_once dirname(__FILE__) . "/../fpp-matrixtools/scripts/matrixtools.php.inc";

//display the various overlay modes for matrix tools

function PrintOverlayMode($overlayMode) {
	
	global $DEBUG;
	echo " 1 = FULL OVERLAY, 2 = TRANSPARENT, 3 = Transparent RGB \n";

	echo "<select name=\"OVERLAY_MODE\"> \n";
	
	for ($i=1;$i<=3;$i++) {
		
		if ($overlayMode == $i) {
			echo "<option selected value=\"".$i."\">".$i."</option> \n";
		} else {
			echo "<option value=\"".$i."\">".$i."</option> \n";
		}	
	}
	echo "</select> \n";
}

function clearMatrix($matrix="", $host="") {
	global $pluginDirectory, $fpp_matrixtools_Plugin, $fpp_matrixtools_Plugin_Script,$Matrix,$settings;;
	
	if ($matrix == "") {
		$matrix = $Matrix;
	}
    
    ClearModel($host, $Matrix);
}

function enableMatrixToolOutput($matrix="", $host="") {
	global $DEBUG, $fpp_version, $settings, $pluginDirectory,$fpp_matrixtools_Plugin, $fpp_matrixtools_Plugin_Script,$Matrix, $overlayMode;
	
	if ($overlayMode == "") {
		$overlayMode = "1";
	}

	if ($matrix =="" ) {
		$matrix = $Matrix;
	}
    
    SetModelState($host, $matrix, $overlayMode);
}

function disableMatrixToolOutput($matrix="", $host="") {
	global $DEBUG, $fpp_version, $settings,$pluginDirectory,$fpp_matrixtools_Plugin, $fpp_matrixtools_Plugin_Script,$Matrix;

	if($matrix =="" ) {
		$matrix = $Matrix;
	}
    SetModelState($host, $matrix, 0);
}
function PrintMatrixList($SELECT_NAME="MATRIX", $MATRIX_READ, $host="") {
	global $pluginDirectory,$fpp_matrixtools_Plugin,$fpp_matrixtools_Plugin_Script;//,$blockOutput;
	$blockOutput = GetModels($host);
	//print_r($blockOutput);

	echo "<select name=\"".$SELECT_NAME."\" id=\"".$SELECT_NAME."\">";
	for($i=0;$i<=count($blockOutput)-1;$i++) {
        if(trim($blockOutput[$i]["Name"])==$MATRIX_READ) {
            echo "<option selected value=\"".trim($MATRIX_READ)."\">".trim($MATRIX_READ)."</option>\n";
        } else {
            echo "<option value=\"".trim($blockOutput[$i]["Name"])."\">".trim($blockOutput[$i]["Name"])."</option>\n";
        }
	}

	echo "</select>";
}




?>
