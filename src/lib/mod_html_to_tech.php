<?
require_once("subs.php");
##########################################################
#
#			 Mod html_to_tech - By Jannis Breitwieser
#
##########################################################


##########################################################
#
# class html_to_tech
#
# Methods:
#		Konstruktor
#		add_content - Fügt Inhalt hinzu, falls noch nicht konvertiert wurde
#		get_output
#		set_title_page(TRUE/FALSE);
#


class html_to_tech {
	
	var $input;
	var $output;
	var $converted;
	var $titlepage;
	
	// Konstruktor
	function html_to_tech($createintput = "") {
		$this->converted = false;
		$this->output = "";
		$this->input = $createintput;
		$this->titlepage = FALSE;
	}
	
	
	//
	//	Add_content
	//
	function add_content($string) {
		if (!$this->converted) {
			$this->input .="$string";
		}
	}
	
	//
	//	Get_output
	//
	function get_output() {
		return $this->output;
	}
	
	
	//
	//	CONVERT
	//
	
	function convert() {
		
		$to_convert = $this->input;
		
		
		// Hier Converstion rules festlegen
		$conversion_rules = array(
			array(orig=> "/<b>(.*?)<\/b>/i",rep => "\\textbf\{$1}"),
			array(orig=> "/<i>(.*?)<\/i>/i",rep => "\\textit\{$1}"),
		);
		
		foreach ($conversion_rules as $temp) {
			echo("RULE:");
			print_r($temp);
			echo("Before convert:\n $to_convert\n");
			$to_convert = preg_replace($temp[orig],$temp[rep],$to_convert);
			echo("After convert:\n$to_convert\n");
			echo "\n";
			
		}
		
		$this->output = $to_convert;
		
	}
	
	
	function set_title_page($what=FALSE) {
		if ($what) {
			$this->titlepage = TRUE;
		}
		else {
			$this->titlepage = FALSE;
		}
	}
	
	
	
	//
	//	Get_title_page
	//
	function get_tex_titlepage() {
		return "
				%Inhaltsverzeichnis
				\tableofcontents
				\clearpage
		";
	}
	//
	//
	//	Get_Tex_Header
	//
	function get_tex_header() {
		
		return "
			% Deklarationen
			\documentclass[12pt,a4paper]{article}
			\usepackage[ngerman]{babel}
			\usepackage{url}
			\usepackage{umlaut}
			\usepackage{typearea}
			\areaset{15.5cm}{24.5cm}
			\renewcommand{\baselinestretch}{1.50}\normalsize
			
			
			% Dokumentanfang
			\begin{document}
		";
	}
	
	
	//
	//	Get_Tex_footer
	//
	function get_tex_footer() {
		return "
			\end{document}
		";
	}
	
	
	
	
	
}






?>