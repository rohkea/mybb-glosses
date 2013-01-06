<?php
/**
 * Gloss code for linguistic discussions
 */
 
// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook('pre_output_page','gloss_add_css');
$plugins->add_hook("parse_message_end", "gloss_parse");

function gloss_info()
{
	/**
	 * Array of information about the plugin.
	 * name: The name of the plugin
	 * description: Description of what the plugin does
	 * website: The website the plugin is maintained at (Optional)
	 * author: The name of the author of the plugin
	 * authorsite: The URL to the website of the author (Optional)
	 * version: The version number of the plugin
	 * guid: Unique ID issued by the MyBB Mods site for version checking
	 * compatibility: A CSV list of MyBB versions supported. Ex, "121,123", "12*". Wildcards supported.
	 */
	return array(
		"name"          => "Gloss code",
		"description"   => "[gloss] code for linguistic texts",
		"website"       => "http://github.com/rohkea/mybb-glosses",
		"author"        => "Dmitry Kushnariov",
		"authorsite"    => "http://github.com/rohkea/",
		"version"       => "1.00",
		"guid"          => "",
		"compatibility" => "*"
	);
}

class GlossMarkupParser {
	
	public $strip_spaces;
	public $above;
	private $pos;
	public $parts;
	
	function gloss_map_function($gloss) {
		return '<span class="gloss-subunit gloss-annotation"><span class="gloss-hidden-text">{</span>'
			. $gloss
			. '<span class="gloss-hidden-text">}</span></span>';
	}
	
	function gloss_get_html($text, $glosses) {
		$glosses = explode('}{', ltrim(rtrim($glosses, '}'), '{'));
		$text = '<span class="gloss-subunit">'  . $text . '</span>';
		$glosses_html = implode('', array_map(array($this, 'gloss_map_function'), $glosses));
		return '<span class="glossed-unit-' . $this->pos . '">'
		 . ($this->above ? $glosses_html . $text : $text . $glosses_html)
		 . '</span>';
	}

	function flush_parts() {
		$r = '';
		if (count($this->parts) > 0 && $this->parts[0]{0} != '<') {
			$r = array_shift($this->parts);
		}
		
		if (count($this->parts) > 1)
			$r .= '<span class="gloss-nobr-' . $this->pos . '">' . implode('', $this->parts) . '</span>';
		elseif (count($this->parts) > 0)
			$r .= implode('', $this->parts);
		else
			$r .= '';
		$this->parts = array();
		return $r;
	}

	function callback($m) {
		if ($m[2] && $m[3]) {
			$white = $m[1];
			$code = $this->gloss_get_html($m[2], $m[3]);
		}
		else if($m[6] && $m[7]) {
			$white = $m[5];
			$code = $this->gloss_get_html($m[6], $m[7]);
		}
	  	else {
			$white = $m[9];
			$code = str_replace(' ', '&nbsp;', $m[10]);
	  	}
		
		if ($white)
			$res = $this->flush_parts();
		else
			$res = '';
		
		if (!$this->strip_spaces && $white)
			$this->parts[] = str_replace(' ', '&nbsp;', $white);
		$this->parts[] = $code;
	  	
		return $res;
	}
	
	public function convert_line($text) {
		$this->parts = array();
		$gloss = preg_replace_callback(
			"#( *)``(\S.+?)``((\{[^}]+\})+)|( *)([^\{\s<>]+)((\{[^}]+\})+)|( *)(\S+)#",
			array($this, 'callback'),
			$text);
		$gloss .= $this->flush_parts();
		return $gloss;
	}

	public function convert($text, $dir, $strip_spaces, $above, $classes = '') {
		$this->above = $above;
		$this->pos = $above ? 'above' : 'below';
		$this->strip_spaces = $strip_spaces;
		$lines = array_map(array($this, 'convert_line'), explode("\n", $text));
		
		return ($dir == 'rtl' ? '<p style="direction: rtl;"' : '<p style="direction: ltr;"')
			. ($classes ? " class='$classes'" : '')
			. '>'
			. implode('<br />', $lines)
			. '</p>';
	}
	
	public static function conv($t, $args) {
		$gmp = new self();
		
		$dir = 'ltr';
		$strip_spaces = false;
		$above = false;
		$classes = array();
		
		foreach ($args as $arg) {
			if ($arg == 'ltr' || $arg == 'rtl') {
				$dir = $arg;
			}
			elseif ($arg == 'nospaces') {
				$strip_spaces = true;
			}
			elseif ($arg == 'above') {
				$above = true;
			}
			else {
				$class = str_replace('=', '-', trim(preg_replace('[^=a-zA-Z0-9]', '', $arg)));
				if ($class) {
					$classes[] = 'gloss-' . $class;
				}
			}
		}
		
		return $gmp->convert($t, $dir, $strip_spaces, $above, implode(' ', $classes));
	}
	
}

function gloss_parse_callback($m) {
	return GlossMarkupParser::conv($m[2], explode(' ', $m[1]));
}

function gloss_parse($s)
{
	$re = '/\[gloss(\s+.*?)?\s*\](.*?)\[\/gloss\]/si';
	return preg_replace_callback($re, 'gloss_parse_callback',$s);
}

function gloss_add_css($page) {
	global $mybb;
	return str_replace('</head>', '<link rel="stylesheet" type="text/css" href="'.$mybb->settings['bburl'].'/inc/plugins/gloss/gloss.css" /></head>', $page);
}
