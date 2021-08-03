<?php
    
include_once dirname(__FILE__) . "/../fpp-matrixtools/scripts/matrixtools.php.inc";

//display the various overlay modes for matrix tools


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
function GetOverlayList() { 
	$modelsList = GetModels("");
	for($i=0;$i<=count($modelsList)-1;$i++) {
        $OverlayModels[trim($modelsList[$i]["Name"])]=trim($modelsList[$i]["Name"]);
	}
	return $OverlayModels;
}


?>
