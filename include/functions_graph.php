<?PHP
	include "config.php";
	
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


function generic_pie_graph ($width=300, $height=200, $data, $legend) {
	require ("../include/config.php");
	require_once 'Image/Graph.php';
	require ("../include/languages/language_".$language_code.".php");
	if (sizeof($data) > 0){
		// create the graph
		$driver=& Image_Canvas::factory('png',array('width'=>$width,'height'=>$height,'antialias' => 'native'));
		$Graph = & Image_Graph::factory('graph', $driver);
		// add a TrueType font
		$Font =& $Graph->addNew('font', $config_fontpath);
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

if ( $_GET["type"] == "progress"){
		$percent = $_GET["percent"];
		$width = $_GET["width"];
		$height = $_GET["height"];
		progress_bar ($percent, $width, $height);
}

?>
