<?PHP

// INTEGRIA IMS v1.2
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica, info@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License (LGPL)
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

	include "config.php";
	include "functions.php";

// ===============================================================================
// Draw a simple pie graph with incidents, by assigned user
// ===============================================================================

function incident_peruser ($width, $height){
    require ("../include/config.php");
    require ("../include/functions_db.php");
    $res = mysql_query("SELECT * FROM tusuario");
    while ($row=mysql_fetch_array($res)){
        $id_user = $row["id_usuario"];
        $datos = give_db_sqlfree_field ("SELECT COUNT(id_usuario) FROM tincidencia WHERE id_usuario = '$id_user'");
        if ($datos > 0){
            $data[] = $datos;
            $legend[] = $id_user;
        } 
    } 
    if (isset($data))
        generic_pie_graph ($width, $height, $data, $legend);
    else 
        graphic_error();
}

// ===============================================================================
// Draw a simple pie graph with reported workunits for a specific TASK
// ===============================================================================

function graph_workunit_task ($width, $height, $id_task){
    require ("../include/config.php");
    require ("../include/functions_db.php");
    $res = mysql_query("SELECT SUM(duration), id_user FROM tworkunit, tworkunit_task
                    WHERE tworkunit_task.id_task = $id_task AND 
                    tworkunit_task.id_workunit = tworkunit.id 
                    GROUP BY id_user");
    while ($row=mysql_fetch_array($res)){
        $data[] = $row[0];
        $legend[] = $row[1];
    } 
     
    if (isset($data))
        generic_pie_graph ($width, $height, $data, $legend);
    else 
        graphic_error();
}


// ===============================================================================
// Draw a simple pie graph with reported workunits for a specific USER, per TASK/PROJECT
// ===============================================================================

function graph_workunit_user ($width, $height, $id_user, $date_from ){
    require ("../include/config.php");
    require ("../include/functions_db.php");
    $date_to = date("Y-m-d", strtotime("$date_from + 30 days"));
    $res = mysql_query("SELECT SUM(duration), id_task, timestamp, ttask.name, tproject.name 
                    FROM tworkunit, tworkunit_task, ttask, tproject  
                    WHERE tworkunit.id_user = '$id_user' AND 
                    tworkunit.id = tworkunit_task.id_workunit AND 
                    tworkunit.timestamp > '$date_from' AND 
		    tworkunit.timestamp < '$date_to' AND
                    tworkunit_task.id_task = ttask.id AND
                    tproject.id = ttask.id_project 
                    GROUP BY id_task ORDER BY SUM(duration) DESC");

    while ($row=mysql_fetch_array($res)){
        $data[] = $row[0];
        $legend[] = $row[4]." / ".$row[3] . " (".$row[0].")";
    } 
     
    if (isset($data))
        generic_pie_graph ($width, $height, $data, $legend);
    else 
        graphic_error();
}


// ===============================================================================
// Draw a simple pie graph with reported workunits for a specific USER, per TASK/PROJECT
// ===============================================================================

function graph_workunit_project_user ($width, $height, $id_user, $date_from){
    require ("../include/config.php");
    require ("../include/functions_db.php");
    $date_to = date("Y-m-d", strtotime("$date_from + 30 days"));
    $res = mysql_query("SELECT SUM(duration), tproject.name 
                    FROM tworkunit, tworkunit_task, ttask, tproject  
                    WHERE tworkunit.id_user = '$id_user' AND 
                    tworkunit.id = tworkunit_task.id_workunit AND 
                    tworkunit.timestamp > '$date_from' AND 
		    tworkunit.timestamp < '$date_to' AND
                    tworkunit_task.id_task = ttask.id AND
                    tproject.id = ttask.id_project 
                    GROUP BY tproject.name ORDER BY SUM(duration) DESC");
    while ($row=mysql_fetch_array($res)){
        $data[] = $row[0];
        $legend[] = $row[1] ." (".$row[0].")";
    } 
     
    if (isset($data))
        generic_pie_graph ($width, $height, $data, $legend);
    else 
        graphic_error();
}

// ===============================================================================
// ===============================================================================
// ===============================================================================


function graphic_error () {
    global $config;

    Header('Content-type: image/png');
    $imgPng = imageCreateFromPng($config["homedir"].'/images/error.png');
    imageAlphaBlending($imgPng, true);
    imageSaveAlpha($imgPng, true);
    imagePng($imgPng);
}

// ***************************************************************************
// Draw a dynamic progress bar using GDlib directly
// ***************************************************************************

function progress_bar ($progress, $width, $height) {
	// Copied from the PHP manual:
	// http://us3.php.net/manual/en/function.imagefilledrectangle.php
	// With some adds from sdonie at lgc dot com
	// Get from official documentation PHP.net website. Thanks guys :-)
	function drawRating($rating, $width, $height) {
		global $config;
		global $lang_label;
		global $REMOTE_ADDR;

		require ("languages/language_".$config["language_code"].".php");
		if ($width == 0) {
			$width = 150;
		}
		if ($height == 0) {
			$height = 20;
		}

		//$rating = $_GET['rating'];
		$ratingbar = (($rating/100)*$width)-2;

		$image = imagecreate($width,$height);
		//colors
		$back = ImageColorAllocate($image,255,255,255);
		$border = ImageColorAllocate($image,0,0,0);
		$red = ImageColorAllocate($image,255,60,75);
		$green = ImageColorAllocate($image,50,205,50);
		$fill = ImageColorAllocate($image,44,81,150);

		ImageFilledRectangle($image,0,0,$width-1,$height-1,$back);
		if ($rating > 100)
			ImageFilledRectangle($image,1,1,$ratingbar,$height-1,$red);
		elseif ($rating == 100)
			ImageFilledRectangle($image,1,1,$ratingbar,$height-1,$green);
		else
			ImageFilledRectangle($image,1,1,$ratingbar,$height-1,$fill);
			
		ImageRectangle($image,0,0,$width-1,$height-1,$border);
		if ($rating > 50)
			if ($rating > 100)
				ImageTTFText($image, 8, 0, ($width/4), ($height/2)+($height/5), $back, $config["fontpath"],$lang_label["out_of_limits"]);
			else
				ImageTTFText($image, 8, 0, ($width/2)-($width/10), ($height/2)+($height/5), $back, $config["fontpath"], $rating."%");
		else
			ImageTTFText($image, 8, 0, ($width/2)-($width/10), ($height/2)+($height/5), $border, $config["fontpath"], $rating."%");
		imagePNG($image);
		imagedestroy($image);
   	}

   	Header("Content-type: image/png");
	if ($progress > 100 || $progress < 0){
		// HACK: This report a static image... will increase render in about 200% :-) useful for
		// high number of realtime statusbar images creation (in main all agents view, for example
		$imgPng = imageCreateFromPng("../images/outlimits.png");
		imageAlphaBlending($imgPng, true);
		imageSaveAlpha($imgPng, true);
		imagePng($imgPng); 
   	} else 
   		drawRating($progress,$width,$height);
}

function generic_histogram ($width, $height, $mode, $valuea, $valueb, $maxvalue, $labela, $labelb){
	include ("../include/config.php");
	require ("../include/languages/language_".$config["language_code"].".php");
	// $ratingA, $ratingB, $ratingA_leg, $ratingB_leg;
	$ratingA=$valuea;
	$ratingB=$valueb;
	
   	Header("Content-type: image/png");
	$image = imagecreate($width,$height);
	//colors
	$white = ImageColorAllocate($image,255,255,255);
	$black = ImageColorAllocate($image,0,0,0);
	$red = ImageColorAllocate($image,255,60,75);
	$blue = ImageColorAllocate($image,75,60,255);
	$grey = ImageColorAllocate($image,120,120,120);
	$margin_up = 2;
	$max_value = $maxvalue;
	if ($mode != 2){
		$size_per = ($max_value / ($width-40));
	} else {
		$size_per = ($max_value / ($width));
	}
	if ($mode == 0) // with strips 
		$rectangle_height = ($height - 10 - 2 - $margin_up ) / 2;
	else
		$rectangle_height = ($height - 2 - $margin_up ) / 2;
	// First rectangle
	if ($size_per == 0)
		$size_per = 1;
	if ($mode != 2){
		ImageFilledRectangle($image, 40, $margin_up, ($ratingA/$size_per)+40, $margin_up+$rectangle_height -1 , $blue);
		$legend = $ratingA;
		ImageTTFText($image, 7, 0, 0, $margin_up+8, $black, $config["fontpath"], $labela);
		// Second rectangle
		ImageFilledRectangle($image, 40, $margin_up+$rectangle_height + 1 , ($ratingB/$size_per)+40, ($rectangle_height*2)+$margin_up , $red);
		$legend = $ratingA;
		// ImageTTFText($image, 8, 0, ($width-10), ($height/2)+10, $black, $config_fontpath, $ratingB);
		ImageTTFText($image, 7, 0, 0,  $margin_up+$rectangle_height+8, $black, $config["fontpath"], $labelb);
	} else { // mode 2, without labels
		ImageFilledRectangle($image, 1, $margin_up, ($ratingA/$size_per)+1, $margin_up+$rectangle_height -1 , $blue);
		$legend = $ratingA;
		// Second rectangle
		ImageFilledRectangle($image, 1, $margin_up+$rectangle_height + 1 , ($ratingB/$size_per)+1, ($rectangle_height*2)+$margin_up , $red);
		$legend = $ratingA;
	}
	if ($mode == 0){ // With strips
		// Draw limits
		$risk_low =  ($config_risk_low / $size_per) + 40;
		$risk_med =  ($config_risk_med / $size_per) + 40;
		$risk_high =  ($config_risk_high / $size_per) + 40;
		imageline($image, $risk_low, 0, $risk_low , $height, $grey);
		imageline($image, $risk_med , 0, $risk_med  , $height, $grey);
		imageline($image, $risk_high, 0, $risk_high , $height, $grey);
		ImageTTFText($image, 7, 0, $risk_low-20, $height, $grey, $config["fontpath"], "Low");
		ImageTTFText($image, 7, 0, $risk_med-20, $height, $grey, $config["fontpath"], "Med.");
		ImageTTFText($image, 7, 0, $risk_high-25, $height, $grey, $config["fontpath"], "High");
	}
	imagePNG($image);
	imagedestroy($image);
}

// ===============================================================================
// Generic PIE graph
// ===============================================================================

function generic_pie_graph ($width=300, $height=200, $data, $legend) {
	require ("../include/config.php");
	require_once '../include/Image/Graph.php';
	require ("../include/languages/language_".$config["language_code"].".php");
	if (sizeof($data) > 0){
		// create the graph
		$driver=& Image_Canvas::factory('png',array('width'=>$width,'height'=>$height,'antialias' => 'native'));
		$Graph = & Image_Graph::factory('graph', $driver);
		// add a TrueType font
		$Font =& $Graph->addNew('font', $config["fontpath"]);
		// set the font size to 7 pixels
		$Font->setSize(7);
		$Graph->setFont($Font);
		// create the plotarea
		$Graph->add(
			Image_Graph::horizontal(
				$Plotarea = Image_Graph::factory('plotarea'),
				$Legend = Image_Graph::factory('legend'),
			50
			)
		);
		$Legend->setPlotarea($Plotarea);
		// Create the dataset
		// Merge data into a dataset object (sancho)
		$Dataset1 =& Image_Graph::factory('dataset');
		for ($a=0;$a < sizeof($data); $a++){
			$Dataset1->addPoint(str_pad($legend[$a],15), $data[$a]);
		}
		$Plot =& $Plotarea->addNew('pie', $Dataset1);
		$Plotarea->hideAxis();
		// create a Y data value marker
		$Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_PCT_Y_TOTAL);
		// create a pin-point marker type
		$PointingMarker =& $Plot->addNew('Image_Graph_Marker_Pointing_Angular', array(1, &$Marker));
		// and use the marker on the 1st plot
		$Plot->setMarker($PointingMarker);
		// format value marker labels as percentage values
		$Marker->setDataPreprocessor(Image_Graph::factory('Image_Graph_DataPreprocessor_Formatted', '%0.1f%%'));
		$Plot->Radius = 15;
		$FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
		$Plot->setFillStyle($FillArray);
		
		$FillArray->addColor('green@0.7');
		$FillArray->addColor('yellow@0.7');
		$FillArray->addColor('red@0.7');
		$FillArray->addColor('orange@0.7');
		$FillArray->addColor('blue@0.7');
		$FillArray->addColor('purple@0.7');
		$FillArray->addColor('lightgreen@0.7');
		$FillArray->addColor('lightblue@0.7');
		$FillArray->addColor('lightred@0.7');
		$FillArray->addColor('grey@0.6', 'rest');
		$Plot->explode(6);
		$Plot->setStartingAngle(0);
		// output the Graph
		$Graph->done();
	} else 
		graphic_error ();
}

// ===========================================================================
// odo_generic - Odometer graph
// 
// Pure = 0 for no legend, fullscreen graph
// ===========================================================================

function odo_generic ($value1, $value2, $value3, $width= 350, $height= 260, $max=100, $pure = 1){
	require_once 'Image/Graph.php';
	include ("../include/config.php");
	
	if ($max <= $config_risk_high){
		$max = $config_risk_high+1;
	}
	
	// create the graph
	$driver=& Image_Canvas::factory('png',array('width'=>$width,'height'=>$height,'antialias' => 'native'));
	$Graph = & Image_Graph::factory('graph', $driver);
	// add a TrueType font
	$Font =& $Graph->addNew('font', $config["fontpath"]);
	// set the font size to 11 pixels
	$Font->setSize(7);
	$Graph->setFont($Font);

	// create the plotarea
	if ($pure == 0){
		$Graph->add(
				Image_Graph::vertical(
					$Plotarea = Image_Graph::factory('plotarea'),
					$Legend = Image_Graph::factory('legend'),
			80
				)
		);
	} else {
		$Graph->add(
				Image_Graph::vertical(
					$Plotarea = Image_Graph::factory('plotarea'),
					$Legend = Image_Graph::factory('legend'),
			100
				)
		);
	}

	$Legend->setPlotarea($Plotarea);
	$Legend->setAlignment(IMAGE_GRAPH_ALIGN_HORIZONTAL);
	if ($value1 <0)
		$value1=0;
	if ($value2 <0)
                $value2=0;
	if ($value3 <0)
                $value3=0;
	/***************************Arrows************************/
	$Arrows = & Image_Graph::factory('dataset');
	$Arrows->addPoint('Current', $value1, 'GLOBAL');
	$Arrows->addPoint('Past', $value2, 'DATA');
	$Arrows->addPoint('Objective', $value3, 'MONITOR');

	/**************************PARAMATERS for PLOT*******************/

	// create the plot as odo chart using the dataset
	$Plot =& $Plotarea->addNew('Image_Graph_Plot_Odo',$Arrows);
	$Plot->setRange(0, $max);
	$Plot->setAngles(180, 180);
	$Plot->setRadiusWidth(80);
	$Plot->setLineColor('black');//for range and outline

	$Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_VALUE_Y);
	$Plot->setArrowMarker($Marker);

	$Plotarea->hideAxis();
	/***************************Axis************************/
	// create a Y data value marker

	$Marker->setFillColor('transparent');
	$Marker->setBorderColor('transparent');
	$Marker->setFontSize(7);
	$Marker->setFontColor('black');

	// create a pin-point marker type
	$Plot->setTickLength(10);
	$Plot->setAxisTicks(5);
	/********************************color of arrows*************/
	$FillArray = & Image_Graph::factory('Image_Graph_Fill_Array');
	$FillArray->addColor('red@0.9', 'A');
	$FillArray->addColor('blue@0.9', 'B');
	$FillArray->addColor('green@0.9', 'C');

	// create a line array
	$LineArray =& Image_Graph::factory('Image_Graph_Line_Array');
	$LineArray->addColor('red', 'A');
	$LineArray->addColor('blue', 'B');
	$LineArray->addColor('green', 'C');
	$Plot->setArrowLineStyle($LineArray);
	$Plot->setArrowFillStyle($FillArray);

	/***************************MARKER OR ARROW************************/
	// create a Y data value marker

	$Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_VALUE_Y);
	$Marker->setFillColor('transparent');
	$Marker->setBorderColor('transparent');
	$Marker->setFontSize(7);
	$Marker->setFontColor('black');
	// create a pin-point marker type
	if ($pure == 0){
		$PointingMarker =& $Plot->addNew('Image_Graph_Marker_Pointing_Angular', array(20, &$Marker));
		// and use the marker on the plot
		$Plot->setMarker($PointingMarker);
	}
	/**************************RANGE*******************/
	$Plot->addRangeMarker(0, $config_risk_med);
	$Plot->addRangeMarker($config_risk_med, $config_risk_high);
	$Plot->addRangeMarker($config_risk_high, $max);
	// create a fillstyle for the ranges
	$FillRangeArray = & Image_Graph::factory('Image_Graph_Fill_Array');
	$FillRangeArray->addColor('#1F4373@0.9');
	$FillRangeArray->addColor('#708090@0.6');
	$FillRangeArray->addColor('#FFB300@0.6');
	$Plot->setRangeMarkerFillStyle($FillRangeArray);
	// output the Graph
	$Graph->done();
}



function generic_bar_graph ( $width =380, $height = 200, $data, $legend) {
	include ("../include/config.php");
	require_once 'Image/Graph.php';
	require ("../include/languages/language_".$language_code.".php");
	
    	if (sizeof($data) > 10){
    		$height = sizeof($legend) * 20;
    	}

	// create the graph
	$Graph =& Image_Graph::factory('graph', array($width, $height));
	// add a TrueType font
	$Font =& $Graph->addNew('font', $config["fontpath"]);
	$Font->setSize(9);
	$Graph->setFont($Font);
	$Graph->add(
		Image_Graph::vertical (
			$Plotarea = Image_Graph::factory('plotarea',array('category', 'axis', 'horizontal')),
			$Legend = Image_Graph::factory('legend'),
			100
		)
	);
	
	$Legend->setPlotarea($Plotarea);
	// Create the dataset
	// Merge data into a dataset object (sancho)
	$Dataset1 =& Image_Graph::factory('dataset');
	for ($a=0;$a < sizeof($data); $a++){
		$Dataset1->addPoint(substr($legend[$a],0,22), $data[$a]);
	}
	$Plot =& $Plotarea->addNew('bar', $Dataset1);
	$GridY2 =& $Plotarea->addNew('bar_grid', IMAGE_GRAPH_AXIS_Y_SECONDARY);
	$GridY2->setLineColor('gray');
	$GridY2->setFillColor('lightgray@0.05');
	$Plot->setLineColor('gray');
	$Plot->setFillColor('blue@0.85');
	$Graph->done(); 
}


function generic_area_graph ($data, $data_label, $width, $height){
	require_once 'Image/Graph.php';
	include ("../include/config.php");
	require ("../include/languages/language_".$language_code.".php");
	$color ="#437722"; 
	
	$mymax = 0;
	for ($ax=0; $ax < sizeof($data); $ax++){
		if ($data > $mymax)
			$mymax = $data[$ax];
			//echo $data_label[$ax]. " " .$data[$ax]."<br>";
	}	

	// Create graph 
	if (sizeof($data) > 1){
		// Create graph
		// create the graph
		$Graph =& Image_Graph::factory('graph', array($width, $height));
		// add a TrueType font
		$Font =& $Graph->addNew('font', $config["fontpath"]);
		$Font->setSize(6);
		$Graph->setFont($Font);
		$Graph->add(
		Image_Graph::vertical(
			Image_Graph::factory('title', array("", 2)),
			$Plotarea = Image_Graph::factory('plotarea'),
			0)
		);
		// Create the dataset
		// Merge data into a dataset object (sancho)
		$Dataset =& Image_Graph::factory('dataset');
		for ($a=0;$a < sizeof($data); $a++){
			$Dataset->addPoint(substr($data_label[$a],5,5), round($data[$a],1));
		}
		$Plot =& $Plotarea->addNew('area', array(&$Dataset));
		// set a line color
		$Plot->setLineColor('gray');
		// set a standard fill style
		$Plot->setFillColor('#708090@0.4');
		// $Plotarea->hideAxis();
		$AxisX =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_X);
		// $AxisX->Hide();
		$AxisY =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_Y);
		$AxisY->setLabelOption("showtext",true);
		$interval = round (($mymax/ 5),1);
		$AxisY->setLabelInterval($interval);
		$AxisX->setLabelInterval(sizeof($data) / 5);
		$GridY2 =& $Plotarea->addNew('bar_grid', IMAGE_GRAPH_AXIS_Y_SECONDARY);
		$GridY2->setLineColor('blue');
		$GridY2->setFillColor('blue@0.1');
		$AxisY->forceMaximum($mymax + ($mymax/10)) ;
		$AxisY2 =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_Y_SECONDARY);
		$Graph->done();
	} else {
		Header("Content-type: image/png");
		drawWarning($width,$height);
	}
}

// ***************************************************************************
// Draw a radar/spider generic map graph. Uses three arrays (dataXX)
// If data1 is empty, draw only a graph data (data1)
// ***************************************************************************

function generic_radar ($data1, $data2, $datalabel, $label1="", $label2 ="", $width, $height) {
	include ("../include/config.php");
	require ("../include/languages/language_".$language_code.".php");
	require_once 'Image/Graph.php';
	require_once 'Image/Canvas.php';

	if (sizeof($data2) > 2)
		$second_data = 1;
	else
		$second_data = -1;
	
	$maxvalue =0;
	for ($ax=0;$ax < sizeof($data1); $ax++){
		if ($data1[$ax] > $maxvalue)
			$maxvalue = $data1[$ax];
		if ((isset($data2[$ax])) AND ($data2[$ax] > $maxvalue))
			$maxvalue = $data2[$ax];		
	}
	// Create graph with Image_graph functions
	// =======================================
	if (sizeof($data1) > 2) {
		// create the graph
		$Graph =& Image_Graph::factory('graph', array($width, $height));
		// add a TrueType font
		$Font =& $Graph->addNew('font',$config["fontpath"]);
		$Font->setSize(7);
		$Graph->setFont($Font);
		$Graph->add( Image_Graph::vertical(
            				$Plotarea = Image_Graph::factory('Image_Graph_Plotarea_Radar'),
            				$Legend = Image_Graph::factory('legend'),
            				90
        			)
		);
		$Legend->setPlotarea($Plotarea);
		$Plotarea->addNew('Image_Graph_Grid_Polar', IMAGE_GRAPH_AXIS_Y);
		$DS1 =& Image_Graph::factory('dataset');
		$DS2 =& Image_Graph::factory('dataset');
		for ($a=0;$a < sizeof($data1); $a++){
			$DS1->addPoint($datalabel[$a],$data1[$a]);
			if ($second_data != -1)
				$DS2->addPoint($datalabel[$a],$data2[$a]);
		}
		$Plot =& $Plotarea->addNew('Image_Graph_Plot_Radar', $DS1);
		$Plot->setTitle($label1);
		if ($second_data!= -1){		
			$Plot2 =& $Plotarea->addNew('Image_Graph_Plot_Radar', $DS2);
			$Plot2->setTitle($label2);
			$Plot2->setLineColor('red@0.4');
			$Plot2->setFillColor('red@0.2');
		}
		// set a standard fill style
		$Plot->setLineColor('blue@0.4');
		$Plot->setFillColor('blue@0.2');
		
		$AxisY =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_Y);
		$AxisY->setLabelOption("showtext",true);
		$AxisY->setLabelInterval(ceil($maxvalue/3));
		// output the Graph
		$Graph->done();
	} else {
   		Header("Content-type: image/png");
		drawWarning($width, $height);
	}
}


function project_tree ($id_project, $id_user){
    include ("../include/config.php");
    require ("../include/functions_db.php");

    if (user_belong_project ($id_user, $id_project)==0){
        audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager of unauthorized project");
        include ($config["homedir"]."/general/noaccess.php");
        exit;
    }

    if ($id_project != -1)
        $project_name = give_db_value ("name", "tproject", "id", $id_project);
    else
        $project_name = "";

    $dotfilename = $config["homedir"]. "attachment/tmp/$id_user.dot";
    $pngfilename = $config["homedir"]. "attachment/tmp/$id_user.project.png";
    $dotfile = fopen ($dotfilename, "w");

    $total_task = 0;
    $sql2="SELECT * FROM ttask WHERE id_project = $id_project"; 
    if ($result2=mysql_query($sql2))    
    while ($row2=mysql_fetch_array($result2)){
        if ((user_belong_task ($id_user, $row2["id"]) == 1)){
            $task[$total_task] = $row2["id"];
            $task_name[$total_task] = $row2["name"];
            $task_parent[$total_task] = $row2["id_parent_task"];
            $task_workunit[$total_task] = give_wu_task ($row2["id"]);
            $total_task++;
        }
    }
    
    
    fwrite ($dotfile, "digraph Integria {\n");
    fwrite ($dotfile, "      ranksep=2.0;\n");
    fwrite ($dotfile, "      ratio=auto;\n");
    fwrite ($dotfile, "      size=\"9,12\";\n");
    fwrite ($dotfile, "      node[fontsize=8];\n");
    fwrite ($dotfile, '      project [label="'. wordwrap($project_name,12,'\\n').'",shape="ellipse", style="filled", color="grey"];'."\n");
    for ($ax=0; $ax < $total_task; $ax++){
        fwrite ($dotfile, 'TASK'.$task[$ax].' [label="'.wordwrap($task_name[$ax],12,'\\n').'"];');
        fwrite ($dotfile, "\n");
    }
    
    // Make project first parent task relation visible
    for ($ax=0; $ax < $total_task; $ax++){
        if ($task_parent[$ax] == 0){
            fwrite ($dotfile, 'project -> TASK'.$task[$ax].';');
            fwrite ($dotfile, "\n");
        }
    }
    // Make task-subtask parent task relation visible
    for ($ax=0; $ax < $total_task; $ax++){
        if ($task_parent[$ax] != 0){
            fwrite ($dotfile, 'TASK'.$task_parent[$ax].' -> TASK'.$task[$ax].';');
            fwrite ($dotfile, "\n");
        }
    }
    
    fwrite ($dotfile,"}");
    fwrite ($dotfile, "\n");
    
    // exec ("twopi -Tpng $dotfilename -o $pngfilename");
    exec ("twopi -Tpng $dotfilename -o $pngfilename");
    Header('Content-type: image/png');
    $imgPng = imageCreateFromPng($pngfilename);
    imageAlphaBlending($imgPng, true);
    imageSaveAlpha($imgPng, true);
    imagePng($imgPng);
    //unlink ($pngfilename);
    unlink ($dotfilename);
}

function all_project_tree ($id_user, $completion, $project_kind){
    include ("../include/config.php");
    require ("../include/functions_db.php");

    $dotfilename = $config["homedir"]. "attachment/tmp/$id_user.all.dot";
    $pngfilename = $config["homedir"]. "attachment/tmp/$id_user.projectall.png";
    $mapfilename = $config["homedir"]. "attachment/tmp/$id_user.projectall.map";
    $dotfile = fopen ($dotfilename, "w");


    fwrite ($dotfile, "digraph Integria {\n");
    fwrite ($dotfile, "      ranksep=1.8;\n");
    fwrite ($dotfile, "      ratio=auto;\n");
    fwrite ($dotfile, "      size=\"9,9\";\n");
    fwrite ($dotfile, 'URL="'.$config["base_url"].'/index.php?sec=projects&sec2=operation/projects/project_tree";'."\n");

    fwrite ($dotfile, "      node[fontsize=8];\n");
    fwrite ($dotfile, "      me [label=\"$id_user\", style=\"filled\", color=\"yellow\";\n");

    $total_project = 0;
    $total_task = 0;
    if ($project_kind == "all")
        $sql1="SELECT * FROM tproject WHERE disabled = 0"; 
    else
        $sql1="SELECT * FROM tproject WHERE disabled = 0 AND end != '0000-00-00 00:00:00'"; 
    if ($result1=mysql_query($sql1))    
    while ($row1=mysql_fetch_array($result1)){
        if ((user_belong_project ($id_user, $row1["id"],1 ) == 1)){
            $project[$total_project] = $row1["id"];
            $project_name[$total_project] = $row1["name"];
            if ($completion < 0)
                $sql2="SELECT * FROM ttask WHERE id_project = ".$row1["id"]; 
            elseif ($completion < 101)
                $sql2="SELECT * FROM ttask WHERE completion < $completion AND id_project = ".$row1["id"]; 
            else
                $sql2="SELECT * FROM ttask WHERE completion = 100 AND id_project = ".$row1["id"]; 
            if ($result2=mysql_query($sql2))
            while ($row2=mysql_fetch_array($result2)){
                if ((user_belong_task ($id_user, $row2["id"],1) == 1)){
                    $task[$total_task] = $row2["id"];
                    $task_name[$total_task] = $row2["name"];
                    $task_parent[$total_task] = $row2["id_parent_task"];
                    $task_project[$total_task] = $project[$total_project];
                    $task_workunit[$total_task] = give_wu_task ($row2["id"]);
                    $task_completion[$total_task] = $row2["completion"];
                    $total_task++;
                }
            }
            $total_project++;
        }
    }
    // Add project items
    for ($ax=0; $ax < $total_project; $ax++){
        fwrite ($dotfile, 'PROY'.$project[$ax].' [label="'.wordwrap($project_name[$ax],12,'\\n').'", style="filled", color="grey", URL="'.$config["base_url"].'/index.php?sec=projects&sec2=operation/projects/task&id_project='.$project[$ax].'"];');
        fwrite ($dotfile, "\n");
    }
    // Add task items
    for ($ax=0; $ax < $total_task; $ax++){

        $temp = 'TASK'.$task[$ax].' [label="'.wordwrap($task_name[$ax],12,'\\n').'"';
        if ($task_completion[$ax] < 10)
            $temp .= 'color="red"';
        elseif ($task_completion[$ax] < 100)
            $temp .= 'color="yellow"';
        elseif ($task_completion[$ax] == 100)
            $temp .= 'color="green"';
        $temp .= "URL=\"".$config["base_url"]."/index.php?sec=projects&sec2=operation/projects/task_detail&id_project=".$task_project[$ax]."&id_task=".$task[$ax]."&operation=view\"";
        $temp .= "];";
        fwrite ($dotfile, $temp);


    
        fwrite ($dotfile, "\n");
    }

    // Make project attach to user "me"
    for ($ax=0; $ax < $total_project; $ax++){
        fwrite ($dotfile, 'me -> PROY'.$project[$ax].';');
        fwrite ($dotfile, "\n");
        
    }

    // Make project first parent task relation visible
    for ($ax=0; $ax < $total_task; $ax++){
        if ($task_parent[$ax] == 0){
            fwrite ($dotfile, 'PROY'.$task_project[$ax].' -> TASK'.$task[$ax].';');
            fwrite ($dotfile, "\n");
        }
    }

    
    // Make task-subtask parent task relation visible
    for ($ax=0; $ax < $total_task; $ax++){
        if ($task_parent[$ax] != 0){
            fwrite ($dotfile, 'TASK'.$task_parent[$ax].' -> TASK'.$task[$ax].';');
            fwrite ($dotfile, "\n");
        }
    }
    
    fwrite ($dotfile,"}");
    fwrite ($dotfile, "\n");
    // exec ("twopi -Tpng $dotfilename -o $pngfilename");

    exec ("twopi -Timap -o$mapfilename -Tpng -o$pngfilename $dotfilename");

    Header('Content-type: image/png');
    $imgPng = imageCreateFromPng($pngfilename);
    imageAlphaBlending($imgPng, true);
    imageSaveAlpha($imgPng, true);
    imagePng($imgPng);
    unlink ($pngfilename);
    unlink ($dotfilename);
}

// ****************************************************************************
//   MAIN Code
//   parse get parameters
// ****************************************************************************


if (isset($_GET["id_audit"]))
	$id_audit = $_GET["id_audit"];
else
	$id_audit = 0;
if (isset($_GET["id_group"]))
	$id_group = $_GET["id_group"];
else
	$id_group = 0;
if (isset($_GET["period"]))
	$period = $_GET["period"];
else
	$period = 129600; // Month
if (isset($_GET["width"]))
	$width= $_GET["width"];
else 
	$width= 280;
if (isset($_GET["height"]))
	$height= $_GET["height"];
else
	$height= 50;

$id_user = give_parameter_get ("id_user",0);
$id_project = give_parameter_get ("id_project",0);
$graphtype = get_parameter ("graphtype",0);
$completion = get_parameter ("completion",0);
$project_kind = get_parameter ("project_kind","");
$id_task = get_parameter ("id_task",0);
$max = give_parameter_get ("max" , 0);
$min = give_parameter_get ("min" , 0);
$labela = give_parameter_get ("labela" , "");
$labelb = give_parameter_get ("labelb" , "");
$valuea = give_parameter_get ("a" , 0);
$valueb = give_parameter_get ("b" , 0);
$valuec = give_parameter_get ("c" , 0);
$lite = give_parameter_get ("lite" , 0);
$date_from = give_parameter_get ( "date_from", 0);
$date_to   = give_parameter_get ( "date_to", 0);
$mode = give_parameter_get ( "mode", 1);
$percent = give_parameter_get ( "percent", 0);
$days = give_parameter_get ( "days", 0);


if ( $_GET["type"] == "progress")
	progress_bar ($percent, $width, $height);
elseif ($_GET["type"] == "incident_a")
	incident_peruser ($width, $height);
elseif ($_GET["type"] == "workunit_task")
    graph_workunit_task($width, $height, $id_task);
elseif ($_GET["type"] == "histogram")
    generic_histogram ($width, $height, $mode, $valuea, $valueb, $max, $labela, $labelb);
elseif ($_GET["type"] == "workunit_user")
    graph_workunit_user ($width, $height, $id_user, $date_from);
elseif ($_GET["type"] == "workunit_project_user")
    graph_workunit_project_user ($width, $height, $id_user, $date_from);
elseif ($_GET["type"] == "project_tree")
    project_tree ($id_project, $id_user);
elseif ($_GET["type"] == "all_project_tree")
    all_project_tree ($id_user, $completion, $project_kind);
?>
