<?

// Integria 1.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.


// TOPI uses icons from famfamfam, licensed under CC Atr. 2.5
// Silk icon set 1.3 (cc) Mark James, http://www.famfamfam.com/lab/icons/silk/
// TOPI uses Pear Image::Graph code
// TOPI shares much of it's code with project Babel Enterprise and Pandora FMS,
// also a Free Software Project coded by some of the people who makes ToPI.
// Gantt php class example and configuration file
// Copyright (C) 2005 Alexandre Miguel de Andrade Souza <alexandremasbr@gmail.com>


require "config.php";
require "languages/language_".$config["language_code"].".php";
require "functions.php"; // Including funcions.
require "functions_db.php";
session_start(); 
global $config;
//error_reporting(0);
$config["ISO_encoding"]="iso-8859-1";

define("END_TO_START", "END_TO_START");
define("START_TO_START", "START_TO_START");
define("END_TO_END", "END_TO_END");
define("START_TO_END", "START_TO_END");

// Security checks for this project

if (check_login() != 0) {
    audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
    require ("../general/noaccess.php");
    exit;
}
$id_user = $_SESSION['id_usuario'];
$id_project = give_parameter_get ("id_project", -1);
if ($id_project != 1)
	$project_name = give_db_value ("name", "tproject", "id", $id_project);
else
	$project_name = "";

if ( $id_project == -1 ){
    // Doesn't have access to this page
    audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager withour project");
    include ("../general/noaccess.php");
    exit;
}

if (user_belong_project ($id_user, $id_project)==0){
	audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager of unauthorized project");
	include ("../general/noaccess.php");
	exit;
}

class gantt {
	var $img;

	/**
	 * All the information to be sent to class
	 * the keys of array will be allocated to class variables
	 * See documentation of others variables to know what information 
	 * sent to this array
	 *
	 * @var array
	 */
	var $definitions = array();
	var $img_width= 600;
	var $img_height = 300;
	var $img_bg_color = array();
	var $grid_color = array();
	var $workday_color = array();
	var $title_color = array();
	var $title_string = "";
	var $planned = array();
	var $planned_adjusted = array();
	var $real = array();
	var $limit = array();
	var $dependency = array();
	var $milestones = array();
	var $groups = array();
	var $progress = array();
	var $y;
	var $cell;
	var $dependency_planned;
	/**
	 * The ONLY function to be accessed. All information to the class have to be passed to array
	 * $definitions. The class will use the informations to generate the gantt graphic
	 *
	 * @param array $definitions
	 * @return gantt
	 */
	function gantt($definitions) {
		$this->definitions = $definitions;
		//allocate the variables of array definitions to class variables
		foreach ($definitions as $key=>$value) {
			$this->$key = $value;
		}
		$this->definesize();

		//create the image
		$this->img = imagecreatetruecolor($this->img_width,$this->img_height);
		//imagealphablending($this->img,true);

		$this->background();
		$this->title();
		$this->grid();
		$this->groups(); // draws groups and phases
		if (is_array($this->dependency_planned)) {
			$this->dependency($this->dependency_planned,'p');
		}
		if (is_array($this->dependency)) {
			$this->dependency($this->dependency);
		}
		if ($this->definitions['today']['data']) {
			$this->today();
		}
        if (isset($this->definitions['status_report']['data']))
			$this->last_status_report();


		$this->legend();

		$this->draw();
	}
	function today(){
		$y= $this->definitions['grid']['y']+40;
		$rows = $this->rows();
		$y2 = ($rows*$this->definitions['row']['height'])+$y;
		$x = (($this->definitions['today']['data'] - $this->limit['start'])/(60*60*24))*$this->cell +$this->definitions['grid']['x'];
		//imageline($this->img,$x,$y,$x,$y2,IMG_COLOR_STYLED);
		$this->line_styled($x,$y,$x,$y2,$this->definitions['today']['color'],$this->definitions['today']['alpha'],$this->definitions['today']['pixels']);
	}
	function last_status_report(){
		$y= $this->definitions['grid']['y']+40;
		$rows = $this->rows();


		$y2 = ($rows*$this->definitions['row']['height'])+$y;
		$x = (($this->definitions['status_report']['data'] - $this->limit['start'])/(60*60*24))*$this->cell +$this->definitions['grid']['x'];

		$this->line_styled($x,$y,$x,$y2,$this->definitions['status_report']['color'],$this->definitions['status_report']['alpha'],$this->definitions['status_report']['pixels']);
	}
	function line_styled($x,$y,$x2,$y2,$color,$alpha,$pixels){
		$w  = imagecolorallocatealpha($this->img, 255, 255, 255,100);
		//$red = imagecolorallocate($im, 255, 0, 0);
		$color = $this->color_alocate($color,$alpha);
		for ($i=0;$i<$pixels;$i++){
			$style[] = $color;
		}
		for ($i=0;$i<$pixels;$i++){
			$style[] = $w;
		}

		imagesetstyle($this->img,$style);
		imageline($this->img,$x,$y,$x,$y2,IMG_COLOR_STYLED);
	}
	function groups()	{
		$start_grid = $this->definitions['grid']['x'];
		$this->y = $this->definitions['grid']['y'] + 40;

		foreach ($this->groups['group'] as $cod=>$phases) {

			if ($this->definitions["not_show_groups"] != true) {


				$y = &$this->y;
				$x = (($this->groups['group'][$cod]['start'] - $this->limit['start'])/(60*60*24))*$this->cell +$start_grid;

				$x2 = (($this->groups['group'][$cod]['end']-$this->groups['group'][$cod]['start'])/(60*60*24))*$this->cell +$x;
				//echo "$x : $x2";
				$this->rectangule($x,$y,$x2,$y+6,$this->groups['color'],$this->groups['alpha']);
				$y2 = $y+7;
				$this->polygon(array($x,$y2,$x+10, $y2,$x,$y+15),3,$this->groups['color'],$this->groups['alpha']);
				$this->polygon(array($x2-10,$y2,$x2, $y2,$x2,$y+15),3,$this->groups['color'],$this->groups['alpha']);

				$y2 = $y +$this->definitions['row']['height']/2;


				// title of group
				$this->rectangule(0,$y,$start_grid-1,$y+$this->definitions['row']['height']/2,$this->groups['bg_color']);
				$this->text($this->groups['group'][$cod]['name'],5,$y+$this->definitions['row']['height']/4-6,$this->definitions["group"]['text_color']);

				//border
				$this->border(0,$y,$start_grid,$y2,$this->title_color);
				$this->border($start_grid,$y,$this->img_width-1,$y2,$this->title_color);

				// increase y
				$y += $this->definitions['row']['height']/2;
			}

			//loop group phases
			$this->phases($cod);
			$this->milestones($cod);

		}
	}
	function phases($group){
		$start_grid = $this->definitions['grid']['x'];
		$y = &$this->y;
		//print_r($this->progress);
		foreach ($this->groups['group'][$group]['phase'] as $phase=>$cod) {

			// name of phase
			// SANCHO: Fix to setup font size to 1 (2º field) and start in Xpos at 7px
			// SANCHO: Added two line render
			$phasename = iconv("UTF-8", "iso-8859-1", $this->planned['phase'][$cod]['name']);
			if (strlen($phasename) > 18){
				$phasename1 = substr($phasename,0,18);
				$phasename2 = substr($phasename,18,18);
				imagestring($this->img,2,7,$y+10,$phasename1,$this->definitions["phase"]['text_color']);
				imagestring($this->img,2,7,$y+20,$phasename2,$this->definitions["phase"]['text_color']);
			} else 			
				imagestring($this->img,2,7,$y+15,$phasename,$this->definitions["phase"]['text_color']);

			// planned
			$x = (($this->planned['phase'][$cod]['start'] - $this->limit['start'])/(60*60*24))*$this->cell +$start_grid;
			$x2 = (($this->planned['phase'][$cod]['end']-$this->planned['phase'][$cod]['start'])/(60*60*24))*$this->cell +$x;
			$w1 = $y + $this->definitions['planned']['y'];
			$w2 = $w1 + $this->definitions['planned']['height'];
			$this->definitions['planned']['points'][$cod]['x1'] = $x;
			$this->definitions['planned']['points'][$cod]['x2'] = $x2;
			$this->definitions['planned']['points'][$cod]['y1'] = $w1;
			$this->definitions['planned']['points'][$cod]['y2'] = $w2;
			$this->rectangule($x,$w1,$x2,$w2,$this->planned['color'],$this->planned['alpha']);

			// adjusted
			if (isset($this->planned_adjusted['phase'][$cod]['start']))
				$t = (($this->planned_adjusted['phase'][$cod]['start'] - $this->limit['start'])/(60*60*24))*$this->cell +$start_grid;
			else	
				$t = $start_grid;
			if (isset($this->planned_adjusted['phase'][$cod]['end']))
				$t2 = (($this->planned_adjusted['phase'][$cod]['end']-$this->planned_adjusted['phase'][$cod]['start'])/(60*60*24))*$this->cell +$t;
			else 
				$t2 = $t;
			$w1 = $y + $this->definitions['planned_adjusted']['y'];
			$w2 = $w1 + $this->definitions['planned_adjusted']['height'];
			$this->definitions['planned_adjusted']['points'][$cod]['x1'] = $t;
			$this->definitions['planned_adjusted']['points'][$cod]['x2'] = $t2;
			$this->definitions['planned_adjusted']['points'][$cod]['y1'] = $w1;
			$this->definitions['planned_adjusted']['points'][$cod]['y2'] = $w2;
			$this->rectangule($t,$w1,$t2,$w2,$this->planned_adjusted['color'],$this->planned_adjusted['alpha']);

			//real
			if (isset($this->real['phase'][$cod]['start'])) {
				$z = (($this->real['phase'][$cod]['start'] - $this->limit['start'])/(60*60*24))*$this->cell +$start_grid;
				$z2 = (($this->real['phase'][$cod]['end']-$this->real['phase'][$cod]['start'])/(60*60*24))*$this->cell +$z;
				$w1 = $y + $this->definitions['real']['y'];
				$w2 = $w1 + $this->definitions['real']['height'];
				$this->rectangule($z,$w1,$z2,$w2,$this->real['color'],$this->real['alpha']);
				$this->border($z,$w1,$z2,$w2,$this->definitions['real']['hachured_color']);
				//hachured
				for ($i=$z;$i<($z2-5);$i+=6){
					$this->line($i,$w2,$i+5,$w1,$this->definitions['real']['hachured_color']);
				}
			}
			//progress
			if (isset($this->progress['phase'][$cod]['progress'])) {
				//echo $t."<Br>";
				if ($this->progress['bar_type']=='planned') {
					$this->rectangule($x,$y+$this->progress['y'],(($x2-$x)*($this->progress['phase'][$cod]['progress']/100))+$x,$y+$this->progress['y']+$this->progress['height'],$this->progress['color'],$this->progress['alpha']);
				} else {
				$this->rectangule($t,$y+$this->progress['y'],(($t2-$t)*($this->progress['phase'][$cod]['progress']/100))+$t,$y+$this->progress['y']+$this->progress['height'],$this->progress['color'],$this->progress['alpha']);
				}
			}
			//box
			$x2 = (($this->planned['phase'][$cod]['end']-$this->planned['phase'][$cod]['start'])/(60*60*24))*$this->cell +$start_grid ;
			$y2 = $y +$this->definitions['row']['height'];
			$this->border($start_grid,$y,$this->img_width-1,$y2,$this->title_color);
			$this->border(0,$y,$start_grid,$y2,$this->title_color);
			$y += $this->definitions['row']['height'];
		}
	}
	function milestones($group){
		$y = &$this->y;
		if (isset($this->groups['group'][$group]['milestone'])) {
			foreach ($this->groups['group'][$group]['milestone'] as $milestone=>$cod) {
				$x = (($this->milestones['milestone'][$cod]['data'] - $this->limit['start'])/(60*60*24))*$this->cell +$this->definitions['grid']['x'];
				// title of group
				$this->rectangule(0,$y,$this->definitions['grid']['x']-1,($y+$this->definitions['row']['height']/2),$this->milestone['title_bg_color']);
				$this->border(0,$y,$this->definitions['grid']['x'],$y+$this->definitions['row']['height']/2,$this->title_color);
				$this->text($this->definitions['milestones']['milestone'][$cod]['title'],15,$y+$this->definitions['row']['height']/4-6,$this->definitions["milestone"]['text_color']);

				//grid box
				$this->border($this->definitions['grid']['x'],$y,$this->img_width-1,$y+$this->definitions['row']['height']/2,$this->title_color);

				//milestone
				$this->polygon(array($x,$y+15,$x+12,$y+15,$x+6,$y),3,$this->milestones['color'],$this->milestones['alpha']);
				$y += $this->definitions['row']['height']/2;
				//echo "$x : $x2";
				//$this->rectangule($x,$y,$x2,$y+6,$this->groups['color']);
			}
		}
	}
	function dependency($dependency,$type='a'){
		imagesetthickness($this->img,2);
		foreach ($dependency as $cod=>$details) {
			$from = $details['phase_from'];
			$to = $details['phase_to'];
			if ($type == 'a') {
				if (isset($this->definitions['planned_adjusted']['points'][$from]['x1']))
					$x[0]=$this->definitions['planned_adjusted']['points'][$from]['x1'];
				if (isset($this->definitions['planned_adjusted']['points'][$from]['x2']))
					$x[1] = $this->definitions['planned_adjusted']['points'][$from]['x2'] ;
				if (isset($this->definitions['planned_adjusted']['points'][$from]['y1']))
					$y[0]=$this->definitions['planned_adjusted']['points'][$from]['y1']+1;
				if (isset($this->definitions['planned_adjusted']['points'][$from]['y2']))
					$y[1]= $this->definitions['planned_adjusted']['points'][$from]['y2'];
				if (isset($this->definitions['planned_adjusted']['points'][$to]['x1']))
					$x[2] = $this->definitions['planned_adjusted']['points'][$to]['x1'];
				if (isset($this->definitions['planned_adjusted']['points'][$to]['x2']))
					$x[3] = return_value ($this->definitions['planned_adjusted']['points'][$to]['x2']) ;
				if (isset($this->definitions['planned_adjusted']['points'][$to]['y1']))
					$y[2]= $this->definitions['planned_adjusted']['points'][$to]['y1']+1;
				if (isset($this->definitions['planned_adjusted']['points'][$to]['y2']))
					$y[3]= $this->definitions['planned_adjusted']['points'][$to]['y2'] ;
			} elseif ($type == 'p'){
				if (isset($this->definitions['planned']['points'][$from]['x1']))
					$x[0] = return_value ($this->definitions['planned']['points'][$from]['x1']);
				if (isset($this->definitions['planned']['points'][$from]['x2']))
					$x[1] = return_value ($this->definitions['planned']['points'][$from]['x2']) ;
				$y[0]= return_value ($this->definitions['planned']['points'][$from]['y1']+1);
				$y[1]= return_value ($this->definitions['planned']['points'][$from]['y2']) ;
				$x[2] = return_value($this->definitions['planned']['points'][$to]['x1']);
				$x[3] = return_value ($this->definitions['planned']['points'][$to]['x2']) ;
				$y[2]= return_value ($this->definitions['planned']['points'][$to]['y1']+1);
				$y[3]= return_value ($this->definitions['planned']['points'][$to]['y2']);
			}
			switch ($details['type']) {
				case 'END_TO_START':
				//echo 'teste';
				$ydif = 7;

				if (isset($this->definitions['dependency']['alpha']))
					$alfa_dep = $this->definitions['dependency']['alpha'];
				else
					$alfa_dep = 0;
				$this->line($x[1],$y[1],$x[1],$y[1]+$ydif,$this->definitions['dependency_color']['END_TO_START'],$alfa_dep);
				$this->line($x[1],$y[1]+$ydif,$x[2],$y[1]+$ydif,$this->definitions['dependency_color']['END_TO_START'],$alfa_dep);
				$this->line($x[2],$y[1]+$ydif,$x[2],$y[2],$this->definitions['dependency_color']['END_TO_START'],$alfa_dep);

				$this->polygon(array($x[2]-4,$y[2]-4,$x[2]+4,$y[2]-4,$x[2],$y[2]),3,$this->definitions['dependency_color']['END_TO_START'],$alfa_dep);
				break;

				case 'END_TO_END':
				// Creo que no va ni de coña esto
				$xdif = 10;
				$ydif = 0;
				if ($x[3]>=$x[1]) {
					$this->line($x[1],$y[1],$x[3],$y[1],$this->definitions['dependency_color']['END_TO_END'],$definitions['dependency']['alpha']);
					$this->line($x[3],$y[1],$x[3],$y[2],$this->definitions['dependency_color']['END_TO_END'],$definitions['dependency']['alpha']);
					$this->polygon(array($x[3]+4,$y[2]-4,$x[3]-4,$y[2]-4,$x[3],$y[2]),3,$this->definitions['dependency_color']['END_TO_END'],$definitions['dependency']['alpha']);
				} else {
					$this->line($x[1],$y[1],$x[1],$y[2],$this->definitions['dependency_color']['END_TO_END'],$this->definitions['dependency']['alpha']);
					$this->line($x[1],$y[2],$x[3],$y[2],$this->definitions['dependency_color']['END_TO_END'],$this->definitions['dependency']['alpha']);
					$this->polygon(array($x[3]+4,$y[2]+4,$x[3]+4,$y[2]-4,$x[3],$y[2]),3,$this->definitions['dependency_color']['END_TO_END'],$this->definitions['dependency']['alpha']);
				}
				break;
				case 'START_TO_START':

				$ydif = 8;
				$this->line($x[0]+1,$y[1],$x[0]+1,$y[1]+$ydif,$this->definitions['dependency_color']['START_TO_START']);
				$this->line($x[0]+1,$y[1]+$ydif,$x[2],$y[1]+$ydif,$this->definitions['dependency_color']['START_TO_START']);
				$this->line($x[2],$y[1]+$ydif,$x[2],$y[2],$this->definitions['dependency_color']['START_TO_START']);
				$this->polygon(array($x[2]-4,$y[2]-4,$x[2]+4,$y[2]-4,$x[2],$y[2]),3,$this->definitions['dependency_color']['START_TO_START']);
				break;
				case 'START_TO_END':
				//echo 'teste';
				$xdif = 5;

				$ydif = 3;

				$this->line($x[0]+1,$y[1],$x[0]+1,$y[1]+$ydif,$this->definitions['dependency_color']['START_TO_END']);
				$this->line($x[0]+1,$y[1]+$ydif,$x[3],$y[1]+$ydif,$this->definitions['dependency_color']['START_TO_END']);
				$this->line($x[3],$y[1]+$ydif,$x[3],$y[2],$this->definitions['dependency_color']['START_TO_END']);
				$this->polygon(array($x[3]+4,$y[2]-4,$x[3]-4,$y[2]-4,$x[3],$y[2]),3,$this->definitions['dependency_color']['START_TO_END']);
				break;

				default:
				break;
			}
		}
	}
	function line($x1,$y1,$x2,$y2,$color,$alpha = 0) {
		$color = $this->color_alocate($color,$alpha);
		imageline($this->img,$x1,$y1,$x2,$y2,$color);

	}
	function legend(){
		//legend
		$x = 20;
		$x2 = 30;
		$xdiff = 10;
		$ydiff = $this->definitions['legend']['ydiff'];
		$planned = 0;

		$y = $this->img_height - $this->definitions['legend']['y'];
		$y_ = $this->definitions['legend']['y_'];
		if (isset( $this->planned['phase']))
			foreach ($this->planned['phase'] as $cod=>$detail) {
				if ($this->planned['phase'][$cod]['start']) {
					$planned++;
				}
			}
		//$planned = 0;
		if ($planned > 0) {
			//echo "$planned";

			//planned

			$this->rectangule($x,$y+5,$x2,$y+10,$this->planned['color'],$this->planned['alpha']);
			$this->text($this->definitions['planned']['legend'],$x2+$xdiff,$y,$this->definitions["legend"]['text_color']);
			$y +=$ydiff;
			if ($this->img_height-$y < $y_) {
				$y = $y = $this->img_height - $this->definitions['legend']['y'];
				$x += $this->definitions['legend']['x'];
				$x2 += $this->definitions['legend']['x'];
			}
		}
		// planned_adjusted
        if (isset($this->planned_adjusted['phase']))
            if (isset($planned_adjusted))
		      $planned_adjusted = count($this->planned_adjusted['phase']);
		//$planned_adjusted = 0;
        if (isset($planned_adjusted))
		    if ($planned_adjusted > 0) {
			    $this->rectangule($x,$y+5,$x2,$y+10,$this->planned_adjusted['color'],$this->planned_adjusted['alpha']);
			    $this->text($this->definitions['planned_adjusted']['legend'],$x2+$xdiff,$y,$this->definitions["legend"]['text_color']);
			    $y +=$ydiff;
			    if ($this->img_height-$y < $y_) {
				    $y = $y = $this->img_height - $this->definitions['legend']['y'];
				    $x += $this->definitions['legend']['x'];
				    $x2 += $this->definitions['legend']['x'];
			    }
		    }




		//real
		$real = count($this->real['phase']);
		//$real = 0;
		if ($real >0) {
			$this->rectangule($x,$y+5,$x2,$y+10,$this->real['color'],$this->real['alpha']);
			$this->text($this->definitions['real']['legend'],$x2+$xdiff,$y,$this->definitions["legend"]['text_color']);
			for ($i=$x;$i<($x2);$i+=4){
				$this->line($i,$y+10,$i+5,$y+5,$this->definitions['real']['hachured_color']);
			}
			$y +=$ydiff;
			if ($this->img_height-$y < $y_) {
				$y = $y = $this->img_height - $this->definitions['legend']['y'];
				$x += $this->definitions['legend']['x'];
				$x2 += $this->definitions['legend']['x'];
			}
		}
		// progress
		$progress = count($this->progress['phase']);
		//$progress = 0;
		if ($progress>0) {
			$this->rectangule($x,$y+5,$x2,$y+10,$this->progress['color'],$this->progress['alpha']);
			$this->text($this->definitions['progress']['legend'],$x2+$xdiff,$y,$this->definitions["legend"]['text_color']);
			$y +=$ydiff;
			if ($this->img_height-$y < $y_) {
				$y = $y = $this->img_height - $this->definitions['legend']['y'];
				$x += $this->definitions['legend']['x'];
				$x2 += $this->definitions['legend']['x'];
			}
		}


		//milestone
		$milestone = count($this->milestones['milestone']);
		//$milestone = 0;
		if ($milestone > 0) {
			$this->polygon(array($x,$y+15,$x+12,$y+15,$x+6,$y),3,$this->milestones['color'],$this->milestones['alpha']);
			$this->text($this->definitions['milestone']['legend'],$x2+$xdiff,$y,$this->definitions["legend"]['text_color']);
			$y +=$ydiff;
			if ($this->img_height-$y < $y_) {
				$y = $y = $this->img_height - $this->definitions['legend']['y'];
				$x += $this->definitions['legend']['x'];
				$x2 += $this->definitions['legend']['x'];
			}
		}
		//today
		if (isset($this->definitions['today']['data'])) {
			$this->line_styled($x+5,$y+3,$x+5,$y+15,$this->definitions['today']['color'],$this->definitions['today']['alpha'],$this->definitions['today']['pixels']);
			//$this->text($this->definitions['milestone']['legend'],$x2+$xdiff,$y);
			$this->text($this->definitions['today']['legend'],$x2+$xdiff,$y,$this->definitions['legend']['text_color']);
			$y +=$ydiff;
			if ($this->img_height-$y < $y_) {
				$y = $y = $this->img_height - $this->definitions['legend']['y'];
				$x += $this->definitions['legend']['x'];
				$x2 += $this->definitions['legend']['x'];
			}
		}
		//last status report


		if (isset($this->definitions['status_report']['data'])) {
			$this->line_styled($x+5,$y+3,$x+5,$y+15,$this->definitions['status_report']['color'],$this->definitions['status_report']['alpha'],$this->definitions['status_report']['pixels']);
			$this->text($this->definitions['status_report']['legend'],$x2+$xdiff,$y,$this->definitions["legend"]['text_color']);
		}

	}
	function rows()	{
		$rows = count($this->planned['phase']);
		if (isset($this->definitions["not_show_groups"]))
			if ($this->definitions["not_show_groups"] != true) {
				$rows += count($this->groups['group'])/2;
			}
		$rows += count($this->milestones['milestone'])/2;
		return $rows;
	}
	function grid()	{
		$months = $this->months($this->limit['start'],$this->limit['end']);
		$n_days = (($this->limit['end']-$this->limit['start'])/(86400))+1;
		$x = $this->definitions['grid']['x'];
		$x1 = $this->definitions['grid']['x'];
		$y= $this->definitions['grid']['y'];
		$rows = $this->rows();
		//echo $rows;
		$y2 = ($rows*$this->definitions['row']['height'])+$y + 40;
		foreach ($months as $month => $startdate) {
			$n_m = next($months);

			$this->border(0,$y,$x,$y+40,$this->title_color);
			if (date("Y",$n_m)> '1969'){ //to bypass a bug in php for windows
				if ($n_m > mktime(0,0,0,2,19,date("Y",$n_m))) {
					$n_m = mktime(0,0,0,date("m",$n_m),date("d",$n_m),date("Y",$n_m));
				}
			}
			if ($n_m < $startdate) {
				$n_m = $this->limit['end']+86400;
			}

			$n_d = ($n_m-$this->limit['start'])/(86400);
			//echo $n_d."<br>";
			if ($n_m >= $this->limit['end']) {
				$x2 = $this->img_width-1;
			} else {
				$x2 = $n_d*$this->cell+$x1;
			}

			//echo $x2."<br>";
			//echo  "<br>";
			$this->rectangule($x,$y,$x2,$y+20,$this->workday_color);
			if ($this->limit['detail']=='m') {
				$ydiff = 15;
			} else{
				$ydiff = 5;
			}

			$this->border($x,$y,$x2,$y+20,$this->title_color);

			if ($this->limit['detail']=='m') {
				$this->rectangule($x,$y+20,$x2,$y2,$this->workday_color);
				$this->border($x,$y,$x2,$y+40,$this->title_color);

			}
			if ($x2 - $x > 45) {
				$this->text($month,$x+($x2-$x)/2-26,$y+$ydiff);
			}
			$x = $x2;
		}

		$x = $this->definitions['grid']['x'];

		//$workdays = $this->workdays($this->limit['start'],$this->limit['end']);
		//print_r($workdays);

		$start = $this->limit['start'];
		$end = $this->limit['end'];
		if ($this->limit['detail']=='m') {
			while( $start <= $end )	{
				$month = date("m",$start);
				$day = date("d",$start);
				$year= date("Y",$start);
				$x2=$x+$this->cell;
				if( date('w', $start ) != 6 && date( 'w', $start) != 0 ){
					//$this->rectangule($x,$y+20,$x2,$y+40,$this->workday_color);
					$this->rectangule($x,$y+41,$x2,$y2,$this->workday_color);
				}else {
					//$this->rectangule($x,$y+20,$x2,$y+40,$this->grid_color);
					$this->rectangule($x,$y+41,$x2,$y2,$this->grid_color);
				}

				//$this->border($x,$y+20,$x2,$y+40,$this->title_color);
				//$day = date("d",$start);
				//$this->text($day,$x+4,$y+23);

				//$this->border($x,$y+41,$x2,$y2,$this->title_color);
				// para corrigir um bug do php que ajusta a data nesse dia
				if ($day == '19' && $month == '2'  ) {
					$start = mktime(0,0,0,2,20,$year);
				} else {
					$start += 86400;
				}


				$x=$x2;
			}
		}
		//day
		if ($this->limit['detail']=='d') {
			while( $start <= $end )	{
				$month = date("m",$start);
				$day = date("d",$start);
				$year= date("Y",$start);
				$x2=$x+$this->cell;
				if( date('w', $start ) != 6 && date( 'w', $start) != 0 ){
					$this->rectangule($x,$y+20,$x2,$y+40,$this->workday_color);
					$this->rectangule($x,$y+41,$x2,$y2,$this->workday_color);
				}else {
					$this->rectangule($x,$y+20,$x2,$y+40,$this->grid_color);
					$this->rectangule($x,$y+41,$x2,$y2,$this->grid_color);
				}

				$this->border($x,$y+20,$x2,$y+40,$this->title_color);
				//$day = date("d",$start);
				$this->text($day,$x+4,$y+23);

				//$this->border($x,$y+41,$x2,$y2,$this->title_color);
				// para corrigir um bug do php que ajusta a data nesse dia
				if ($day == '19' && $month == '2'  ) {
					$start = mktime(0,0,0,2,20,$year);
				} else {
					$start += 86400;
				}


				$x=$x2;
			}
		}
		// week
		if ($this->limit['detail']=='w') {
			while( $start < $end )	{
				$month = date("m",$start);
				$day = date("d",$start);
				$year= date("Y",$start);
				$n_w = (7-date( 'w', $start))*86400+$start;
				if ($n_w > $end || $n_w > $end) {
					$n_w = $end+86400;
				}
				$days = date( 'w', $n_w)-date( 'w', $start);
				if ($days <= 0) {
					$days += 7;
				}
				$x2=$x+$this->cell*$days;
				//$n_w = (7-date( 'w', $start))*86400+$start;



				$this->rectangule($x,$y+20,$x+$this->cell,$y2,$this->grid_color);
				$this->rectangule($x+$this->cell,$y+20,$x2-$this->cell,$y2,$this->workday_color);
				$this->rectangule($x2-$this->cell,$y+20,$x2,$y2,$this->grid_color);
				$this->border($x,$y+20,$x2,$y+40,$this->title_color);
				//$day = date("d",$start);
				$this->text(date( 'd', $start)."-".date( 'd', $n_w-86400),$x+($x2-$x)/2-15,$y+23);

				//$this->border($x,$y+41,$x2,$y2,$this->title_color);
				// para corrigir um bug do php que ajusta a data nesse dia
				$start = $n_w;
				if (date("d",$start) == '19' && date("m",$start) == '2'  ) {
					$start = mktime(0,0,0,2,20,$year);
				}


				$x=$x2;
			}
		}
	}
	function definesize()	{

		if ($this->limit['detail']=='m') {
			$this->cell = $this->limit['cell']['m'];
			$this->limit['start']= mktime(0,0,0,date('m',$this->limit['start']),1,date('Y',$this->limit['start']));

			$this->limit['end']= mktime(0,0,0,date('m',$this->limit['end'])+1,1,date('Y',$this->limit['end']));

		} elseif ($this->limit['detail']=='w') {
			$this->cell = $this->limit['cell']['w'];
			//echo date('w',$this->limit['start']);
			$this->limit['start']= mktime(0,0,0,date('m',$this->limit['start']),date('d',$this->limit['start'])-date('w',$this->limit['start']),date('Y',$this->limit['start']));
			//echo date('w',$this->limit['start']);
			$this->limit['end']= mktime(0,0,0,date('m',$this->limit['end']),date('d',$this->limit['end'])+(6-date('w',$this->limit['end'])),date('Y',$this->limit['end']));



		}elseif ($this->limit['detail']=='d') {
			$this->cell = $this->limit['cell']['d'];
		}

		$n_days = (($this->limit['end']-$this->limit['start'])/(86400));
		$this->img_width = $this->definitions['grid']['x']+ceil($n_days*$this->cell);
		$rows = $this->rows();
		$this->img_height = $this->definitions['grid']['y'] + 45+ $this->definitions['legend']['y']  + $rows*$this->definitions['row']['height'];

	}
	function months($start,$end){
		setlocale(LC_TIME,$this->definitions['locale']);
		while( $start <= $end )	{
			$month = strftime("%b %Y",$start);
			$months[$month] = $start;
			$m = date("m",$start);
			$y = date("Y",$start);
			if ($m == '12') {
				$n_m = '1';
				$y = $y +1;
			}else {
				$n_m = $m +1;
			}
			//echo "$n_m / $y <br>";
			$start = mktime(0,0,0,$n_m,1,$y);
			$fev = mktime(0,0,0,2,1,2005);
		}
		//print_r($months);
		//$fev = date("d m Y",$fev);
		//echo "$fev";

		return $months;
	}
	function border($x1,$y1,$x2,$y2,$color){
		imagerectangle($this->img,$x1,$y1,$x2,$y2,$color);
	}
	function rectangule($x1,$y1,$x2,$y2,$color,$alpha = 0){
		$color = $this->color_alocate($color,$alpha);
		imagefilledrectangle($this->img,$x1,$y1,$x2,$y2,$color);
	}
	function title(){
		$color = $this->color_alocate($this->definitions['title_color']);
		$this->rectangule(0,0,$this->img_width,$this->definitions['grid']['y'],$this->definitions['title_bg_color']);
		$xdiff = strlen($this->definitions['title_string'])*3;
		
		if (isset($this->definitions['title']['ttfont']['file'])) {
			$font_size = $this->definitions['title']['ttfont']['size'];
			imagettftext($this->img, $font_size,0, $this->img_width/2-$xdiff,$this->definitions['title_y']+$font_size, $color,$this->definitions['title']['ttfont']['file'],$this->title_string);
		} else{
			imagestring($this->img,$this->definitions['title_font'],$this->img_width/2-$xdiff,$this->definitions['title_y'],$this->title_string,$color);
		}

	}
	function text($string,$x,$y,$color = 0){
		if ($color==0) {
			$color = $this->definitions['text']['color'];
		}
		
		$color = $this->color_alocate($color,0);
        //  print_r($color);
	if (isset($this->definitions['text']['ttfont']['size']))
         	$font_size = $this->definitions['text']['ttfont']['size'];
	else
		$font_size = 3;
         if (isset($this->definitions['text']['ttfont']['file'])){
		
		imagettftext($this->img, $font_size,0, $x,$y+$font_size, $color,$this->definitions['text']['ttfont']['file'],$string);
         } else {
         	imagestring($this->img, $this->definitions['text_font'], $x,$y, $string,$color);
         }
	}
	// alocatte the color for background
	function background(){
		$bg = imagecolorallocate($this->img,$this->img_bg_color[0],$this->img_bg_color[1],$this->img_bg_color[2]);
		imagefill($this->img,0,0,$bg);
	}
	function color_alocate($color,$alpha = 40){
		return imagecolorallocatealpha($this->img,$color[0],$color[1],$color[2],$alpha);
	}
	function polygon($points, $n_points, $color,$alpha=0){
		$color = $this->color_alocate($color,$alpha);
		imagefilledpolygon($this->img,$points,$n_points,$color);
	}
	//generate the image
	function draw($image_type= 'png')	{

		//echo  "ok, chegou até aqui";
        if (isset($this->definitions['image'])){
		    if ($this->definitions['image']['type']) {
			    $image_type = $this->definitions['image']['type'];
		    }
		    if (isset($this->definitions['image']['filename']))
			    if ($this->definitions['image']['filename']) {
				    $filename = $this->definitions['image']['filename'];
			    }
		    if ($this->definitions['image']['jpg_quality']) {
			    $jpg_quality = $this->definitions['image']['jpg_quality'];
		    } else {
			    $jpg_quality = 100;
		    }
		    if (isset($this->definitions['image']['wbmp_foreground'])) {
			    $foreground = $this->color_alocate($this->definitions['image']['wbmp_foreground']);
		    } else {
			    $foreground = null;
		    }
        }

		switch ($image_type) {
			case 'png':
			if (function_exists("imagepng")) {
				header("Content-type: image/png");
				if (isset($filename)) {
					imagepng($this->img,$filename);
				} else {
					imagepng($this->img);
				}

			}
			break;
			case 'gif':
			if (function_exists("imagegif")) {
				header("Content-type: image/gif");
				if ($filename) {
					imagegif($this->img,$filename);
				} else {
					imagegif($this->img);
				}
				//imagegif($this->img,$filename);
			}
			break;
			case 'jpg':
			if (function_exists("imagejpeg")) {
				header("Content-type: image/jpeg");
				imagejpeg($this->img,$filename, $jpg_quality);
			}
			break;
			case 'wbmp':
			if (function_exists("imagewbmp")) {
				header("Content-type: image/vnd.wap.wbmp");
				if ($filename) {
					imagewbmp($this->img,$filename,$foreground);
				} else {
					imagewbmp($this->img,'',$foreground);
				}

			}
			break;
			default:
			die("No image support for $image_type in this PHP server");
			break;
		}

		imagepng($this->img);
		imagedestroy($this->img);
	}

}

// Default values !

//THIS START STANDARD DEFINITIONS TO CLASS, YOU DONT NEED TO CHANGE THIS SETTINGS, ONLY IF YOU WANT
//generic  definitions to graphic, you dont need to change this. Only if you want
$definitions['title_y'] = 10; // absolute vertical position in pixels -> title string
$definitions['planned']['y'] = 6; // relative vertical position in pixels -> planned/baseline
$definitions['planned']['height']= 8; // height in pixels -> planned/baseline
$definitions['planned_adjusted']['y'] = 25; // relative vertical position in pixels -> adjusted planning
$definitions['planned_adjusted']['height']= 8; // height in pixels -> adjusted planning
$definitions['real']['y']=26; // relative vertical position in pixels -> real/realized time 
$definitions['real']['height']=5; // height in pixels -> real/realized time 
$definitions['progress']['y']=15; // relative vertical position in pixels -> progress
$definitions['progress']['height']=6; // height in pixels -> progress 
$definitions['img_bg_color'] = array(204, 204, 255); //color of background
$definitions['title_color'] = array(255, 255, 255); //color of title
$definitions['text']['color'] = array(0, 0, 0); //color of title
$definitions['title_bg_color'] = array(0, 0, 128); //color of background of title
$definitions['milestone']['title_bg_color'] = array(204, 204, 230); //color of background of title of milestone
$definitions['today']['color']=array(0, 204, 0); //color of today line
$definitions['status_report']['color']=array(255, 50, 0); //color of last status report line
$definitions['real']['hachured_color']=array(204,0, 0);// color of hachured of real. to not have hachured, set to same color of real
$definitions['workday_color'] = array(255, 255, 255	); //white -> default color of the grid to workdays
$definitions['grid_color'] = array(218, 218, 218); //default color of weekend days in the grid
$definitions['groups']['color'] = array(0, 0, 0);// set color of groups
$definitions['groups']['bg_color'] = array(180,180, 180);// set color of background to groups title
$definitions['planned']['color']=array(255, 143, 4);// set color of initial planning/baseline
$definitions['planned_adjusted']['color']=array(0, 0, 204); // set color of adjusted planning
$definitions['real']['color']=array(255, 255,255);//set color of work done
$definitions['progress']['color']=array(0,255,0); // set color of progress/percentage completed
$definitions['milestones']['color'] = array(254, 54, 50); //set the color to milestone icon

//if you want a ttf font set this values
// just donwload a ttf font and set the path 
// find ttf fonts at http://www.webpagepublicity.com/free-fonts.html -> more than 6500 free fonts
/*
$definitions['text']['ttfont']['file'] = $config["fontpath"]; // set path and filename of ttf font -> coment to use gd fonts
$definitions['text']['ttfont']['size'] = '8'; // used only with ttf
$definitions['title']['ttfont']['file'] = $config["fontpath"]; // set path and filename of ttf font -> coment to use gd fonts
$definitions['title']['ttfont']['size'] = '10'; // used only with ttf
//define font colors
//$definitions['title']['ttfont']['file'] = './ActionIs.ttf'; // set path and filename of ttf font -> coment to use gd fonts
//$definitions['title']['ttfont']['size'] = '11'; // used only with ttf
*/
// these are default value if not set a ttf font
$definitions['text_font'] = 2; //define the font to text -> 1 to 4 (gd fonts)
$definitions['title_font'] = 4;  //define the font to title -> 1 to 4 (gd fonts)

//define font colors
$definitions["group"]['text_color'] = array(0,0,0);
$definitions["legend"]['text_color'] = array(0,0,0);
$definitions["milestone"]['text_color'] = array(0,0,0);
$definitions["phase"]['text_color'] = array(0,0,0);


// set to 1 to a continuous line
$definitions['status_report']['pixels'] = 15; //set the number of pixels to line interval
$definitions['today']['pixels'] = 10; //set the number of pixels to line interval



// set colors to dependency lines -> both  dependency planned(baseline) and dependency (adjusted planning)
$definitions['dependency_color'][END_TO_START]=array(0, 0, 0);//black
$definitions['dependency_color'][START_TO_START]=array(0, 0, 0);//black
$definitions['dependency_color'][END_TO_END]=array(0, 0, 0);//black
$definitions['dependency_color'][START_TO_END]=array(0, 0, 0);//black

//set the alpha (tranparency) to colors of bars/icons/lines
$definitions['planned']['alpha'] = 40; //transparency -> 0-100
$definitions['planned_adjusted']['alpha'] = 40; //transparency -> 0-100
$definitions['real']['alpha'] = 20; //transparency -> 0-100
$definitions['progress']['alpha'] = 30; //transparency -> 0-100
$definitions['groups']['alpha'] = 40; //transparency -> 0-100
$definitions['today']['alpha']= 80; //transparency -> 0-100
$definitions['status_report']['alpha']= 10; //transparency -> 0-100
$definitions['dependency']['alpha']= 80; //transparency -> 0-100
$definitions['milestones']['alpha']= 10; //transparency -> 0-100


// set the legends strings
$definitions['planned']['legend'] = 'INITIAL PLANNING';
$definitions['planned_adjusted']['legend'] = 'ADJUSTED PLANNING';
$definitions['real']['legend'] = 'REALIZED';
$definitions['progress']['legend'] = 'PROGRESS';
$definitions['milestone']['legend'] = 'MILESTONE';
$definitions['today']['legend'] = 'TODAY';
$definitions['status_report']['legend'] = 'LAST STATUS REPORT';

//set the size of each day in the grid for each scale
$definitions['limit']['cell']['m'] = '4'; // size of cells (each day)
$definitions['limit']['cell']['w'] = '8'; // size of cells (each day)
$definitions['limit']['cell']['d'] = '20';// size of cells (each day)

//set the initial positions of the grid (x,y)
$definitions['grid']['x'] = 180; // initial position of the grix (x)
$definitions['grid']['y'] = 40; // initial position of the grix (y)

//set the height of each row of phases/phases -> groups and milestone rows will have half of this height
$definitions['row']['height'] = 40; // height of each row

$definitions['legend']['y'] = 85; // initial position of legent (height of image - y)
$definitions['legend']['x'] = 150; // distance between two cols of the legend
$definitions['legend']['y_'] = 35; //distance between the image bottom and legend botton
$definitions['legend']['ydiff'] = 20; //diference between lines of legend

//other settings
//$definitions['progress']['bar_type']='planned'; //  if you want set progress bar on planned bar (the x point), if not set, default is on planned_adjusted bar -> you need to adjust $definitions['progress']['y'] to progress y stay over planned bar or whatever you want; 
$definitions["not_show_groups"] = false; // if set to true not show groups, but still need to set phases to a group


?>