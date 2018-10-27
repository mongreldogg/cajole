<?php

namespace Core;

class Template {
	
	private static $templates = [];
	private static $globals = [];
	private static $languageCallback = [null];
	
	public static function Get($templateName){
		$path = BASE_DIR.'/templates/'.$templateName.'.tpl';
		if(isset(self::$templates[$path]))
			return self::$templates[$path];
		elseif(@self::$templates[$path] = file_get_contents($path))
			return self::$templates[$path];
		else
			throw new \Exception("Template $path not found or should be non-empty!");
	}
	
	public static function Tag($tag, &$from, $replace_with="", &$prop_val=null){
		
		$tag_pattern = "/\[$tag(=[a-zA-Z0-9\,\.]*)?\](.*)?\[\/$tag\].*/Usx";
		
		$matches = null;
		
		@preg_match($tag_pattern, $from, $matches);
		$from = preg_replace($tag_pattern, $replace_with, $from, 1);
		
		$prop_val = substr($matches[1], 1);
		
		return $matches[2];
		
	}
	
	public static function Replace($in_array, $text) {
		
		$replace_array = array();
		
		foreach ($in_array as $key => $value) {
			
			$replace_array[$key] = "{".$key."}";
			
		}
		
		return str_replace($replace_array, $in_array, $text);
		
	}
	
	public static function Render(&$contents, $variables){
		
		$contents = self::Replace(array_merge(self::$globals, $variables), $contents);
		if(@$pack = PACK_HTML) $contents = preg_replace('/(\t|\r|\n)+(\s+)?/', '', $contents);
		return $contents;
		
	}
	
	public static function SetGlobal($name, $value) {
		self::$globals[$name] = $value;
	}

	public static function GetLanguage($getLangCallback = null){
		if(is_callable($getLangCallback)) self::$languageCallback[0] = $getLangCallback;
		elseif(is_callable(self::$languageCallback[0])){
			return self::$languageCallback[0]();
		}
		else return DEFAULT_LANGUAGE;
	}

	private static $defaultView = 'main';

	public static function DefaultView($templateName){
		self::$defaultView = $templateName;
	}
	
	public static function GenerateView($content, $prepare = null){
		$main = Template::Get(self::$defaultView);
		$main = Template::Replace(['content' => $content], $main);
		$proto = 'http';
		if(Request::IsSafe()) $proto = 'https';
		Template::SetGlobal('base_href', $proto.'://'.Request::Domain().ROOT_DIR);
		Template::SetGlobal('base_proto', $proto);
		$keywords = [];
		while($keyword = Template::Tag('keywords', $main)){
			$keywords[] = $keyword;
		}
		Template::SetGlobal('keywords', implode(',', $keywords));
		$description = '';
		while($descr = Template::Tag('description', $main)) $description = $descr;
		Template::SetGlobal('description', $description);
		$title = [];
		while($t = Template::Tag('title', $main)) $title[] = $t;
		$title = implode(TITLE_DELIMITER, $title);
		Template::SetGlobal('title', $title);
		$rendered = Template::Render($main, []);
		if(is_callable($prepare)) {
			self::$globals = [];
			$rendered = $prepare($rendered);
			$rendered = Template::Render($rendered, []);
		}
		Response::HTML($rendered);
	}
	
}
