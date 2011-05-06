<?php 
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2010 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

class PDFTranslator {
	private $html;
	private $mpdf;
	private $breakPageCount;
	private $header;
	
    public $custom_font = "default";

	public function __construct() {
		global $config;
		
		$this->html = array(); //Save eatch element that page (for manual break), if any page overflow page size I don't worry.
		$this->breakPageCount = 0;
		$this->header = null;
		$this->footer = null;
		
		// Define fonts path for mpdf library
//		define ('_MPDF_TTFONTPATH' , $config['homedir'].'/include/fonts/');

		require_once($config["homedir"] . '/include/mpdf51/mpdf.php');
		
		$this->mpdf=new mPDF();
		
		// FOR DEBUG
		// $this->mpdf->showImageErrors = true; 
	}
	
	public function showPDF() {
		$this->writePDFfile();
	}
	
	public function writePDFfile($file = null) {
		$this->setupPDF();
		
        $default_filename = "integria_report_".date("Y-m-d_His").".pdf";

		if (!isset($file)) {
			$this->mpdf->Output($default_filename , "D");
		}
		else {
			$this->mpdf->Output($file);
		}
	}
	
	private function setupPDF() {
		$firstManualBreakPage = true;
		$secondManualBreakPage = false;

        // Get filename of font selected
        $matches = array();
        if (preg_match( "/([A-Za-z0-9]*)\..*/", $this->custom_font, $matches ))
            $myfont = $matches[1];
        else
            $myfont = "Default";

        if ($myfont != "Default"){
		    $html2 = '<style>
                body, tbody, th, td, h1, h2, h3, h4, h5, h6 {
           			font-family: "'.$myfont.'";
                }

			    body {
           			font-size: 10pt;
			    }
			    </style>';
		
		    $this->mpdf->WriteHTML($html2);
        }

		if (!empty($this->header)) {
			if ($this->header['firstPage']) {
				$this->mpdf->SetHTMLHeader($this->header['html']);
			}
		}
		
		if (!empty($this->footer)) {
			if ($this->footer['firstPage']) {
				$this->mpdf->SetHTMLFooter($this->footer['html']);
			}
		}
		
		for ($iterator = 0; $iterator < count($this->html); $iterator++) {
			$html = $this->html[$iterator];
			
			if ($firstManualBreakPage) {
				$this->mpdf->WriteHTML($html);
				
				if (!empty($this->header)) {
					//For write in second page the header is necesary write at the end of first page
					if (!$this->header['firstPage']) {
						$this->mpdf->SetHTMLHeader($this->header['html']);
					}
				}
				
				$firstManualBreakPage = false;
				$secondManualBreakPage = true;
			}
			else if ($secondManualBreakPage) {
				if (!empty($this->footer)) {
					if (!$this->footer['firstPage']) {
						$this->mpdf->SetHTMLFooter($this->footer['html']);
					}
				}
				
				$this->mpdf->WriteHTML($html);
				
				$secondManualBreakPage = false;
			}
			else {
				$this->mpdf->WriteHTML($html);
			}
			
			if ($iterator < (count($this->html) - 1)) { //The last page don't break page.
				$this->mpdf->AddPage();
			}
		}
	}
	
	public function addHTML($html = '') {
		$this->html[$this->breakPageCount] .= $html;
	}
	
	public function newPage() {
		$this->breakPageCount++;
	}
	
	public function setHeaderHTML($html, $firstPage = false) {
		$this->header = array('html' => $html, 'firstPage' => $firstPage);
	}
	
	public function setFooterHTML($html, $firstPage = false, $showLines = true) {
		$htmlFooter = '<table style="width: 100%; border-top: 1px solid black;">
			<tr>
				<td>' . $html . '</td><td align="right">{PAGENO}</td></tr></table>';
		
		$this->footer = array('html' => $htmlFooter, 'firstPage' => $firstPage, 'showLines' => $showLines);
	}
	
	public function setMetadata($title = "Integria IMS Report", $autor = "Integria IMS", $creator = "Integria IMS", $subject = "N/A", $keywords = array()) {
		$this->mpdf->SetTitle($title);
		$this->mpdf->SetAuthor($autor);
		$this->mpdf->SetCreator($creator);
		$this->mpdf->SetSubject($subject);
		$this->mpdf->SetKeywords(implode(',', $keywords));
	}
	
	public function addTableContents($title) {
		$titleEntities =  htmlentities($title); 

		$this->html[$this->breakPageCount] .= '<tocpagebreak toc-preHTML="' . $titleEntities . '" links="1" toc-bookmarkText="Contents"  />';
	}
	
	public function addBookMarkAndEntry($title, $level = 1) {
		$this->html[$this->breakPageCount] .= '<tocentry name="" content="' . $title . '" level="' . $level . '" />';
		$this->html[$this->breakPageCount] .= '<bookmark content="' . $title . '" level="' . $level . '" />';
	}
};

?>
