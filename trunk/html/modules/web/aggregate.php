<?php

include_gobe_module("goat");

/**
 * @abstract XML feed aggregator
 * 
 * @author Justin Johnson <justin@zebrakick.com>
 * @version 20091113 johnsonj (added twitter and atom support)
 * @version 20090919 johnsonj
 * @version 20090724 johnsonj
 * 
 * @package zk.modules.web.aggregate
 */
class Aggregate {
	const TEMPLATE_PATH      = "web/aggregate/";
	const TEMPLATE_EXTENSION = ".html";
	const DEFAULT_TEMPLATE   = "default";
	const DEFAULT_MAX        = 3;
	const DEFAULT_ERROR      = "Whoops, couldn't load feed.";
	
	static public function rss($source, $max=self::DEFAULT_MAX, $template=false) {
		$template = self::getTemplate($template, "rss");
		$source   = @simplexml_load_file(self::getSource($source));
		
		if ( $source === false || $template === false ) {
			return self::DEFAULT_ERROR;
		}
		
		$response = "";
		$count    = 0;
		
		foreach ( $source->channel->item as $entry ) {
			$response .= self::format($template, array(
				"url"   => $entry->link,
				"title" => $entry->title,
				"body"  => array($entry->description, "fieldFormaterBody")
			));
			
			if ( $max && ++$count == $max ) {
				break;
			}
		}

		return $response;
	}
	
	static public function atom($source, $max=self::DEFAULT_MAX, $template=false) {
		$template = self::getTemplate($template, "atom");
		$source   = @simplexml_load_file(self::getSource($source));
		
		if ( $source === false || $template === false ) {
			return self::DEFAULT_ERROR;
		}
		
		$response = "";
		$count    = 0;
		
		foreach ( $source->entry as $entry ) {
			$response .= self::format($template, array(
				"url"   => $entry->link,
				"title" => $entry->title,
				"body"  => array(isset($entry->summary) ? $entry->summary : $entry->content, "fieldFormaterBody")
			));
			
			if ( $max && ++$count == $max ) {
				break;
			}
		}

		return $response;
	}
	
	static public function twitter($user, $max=self::DEFAULT_MAX, $template=false) {
		if ( function_exists("include_gobe_module") ) {
			include_gobe_module("output.format");
		}
		
		$template = self::getTemplate($template, "twitter");
		$source   = @simplexml_load_file("http://twitter.com/statuses/user_timeline/$user.rss");
		
		if ( $source === false || $template === false ) {
			return self::DEFAULT_ERROR;
		}
		
		$response  = "";
		$count     = 0;
		$frontTrim = strlen($user) + 2;
		
		foreach ( $source->channel->item as $entry ) {
			$response .= self::format($template, array(
				"url"   => $entry->link,
				"date"  => array($entry->pubDate, "fieldFormaterDate"),
				"body"  => array(substr($entry->description, $frontTrim), "fieldFormaterLinks")
			));
			
			if ( $max && ++$count == $max ) {
				break;
			}
		}

		return $response;
	}
	
	static private function format($template, $fields) {
		$goat = new goat();
		
		foreach ( $fields as $field=>$data ) {
			if ( is_array($data) ) {
				$data = call_user_func(__CLASS__ . "::" . $data[1], $data[0]);
			}
			
			$goat->register_variable($field, $data);
		}
		
		return $goat->parse($template) . "\n";
	}
	
	static private function fieldFormaterBody($data) {
		return preg_replace('`\.*\s*\[\.{3}\]\s*$`', '...', $data);
	}
	
	static private function fieldFormaterLinks($data) {
		return Format::text2links($data);
	}
	
	static private function fieldFormaterDate($data) {
		return date(STD_DATETIME_COMPACT, ctype_digit($data) ? $data : strtotime($data));
	}
	
	static private function getSource($source) {
		return str_replace("{domain}", $_SERVER['HTTP_HOST'], $source);
	}
	
	static private function getTemplate($template, $format) {
		$basePath = PATH_TEMPLATES_STUBS . self::TEMPLATE_PATH;
		
		// No template provided, try to get format specific first
		if ( $template == false ) {
			$template = file_exists($basePath . $format . self::TEMPLATE_EXTENSION) 
				? $format
				: self::DEFAULT_TEMPLATE;
			$template .= self::TEMPLATE_EXTENSION;
		}
		
		// Add the default extension if the file doesn't exist as specified
		if ( !file_exists($basePath . $template) ) {
			$template .= self::TEMPLATE_EXTENSION;
		}
		
		return file_get_contents($basePath . $template);
	}
}

if ( function_exists('add_gobe_callback') ) {
	add_gobe_callback('aggregate', 'rss',     array('Aggregate', 'rss'));
	add_gobe_callback('aggregate', 'atom',    array('Aggregate', 'atom'));
	add_gobe_callback('aggregate', 'twitter', array('Aggregate', 'twitter'));
}
