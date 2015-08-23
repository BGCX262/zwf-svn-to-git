<?php

class Grammar {
	static public $vowels = array("A","E","I","O","U");
	
	static public function indefiniteArticle($noun) {
		return self::isVowel($noun[0]) ? "an" : "a";
	}
	
	static public function isVowel($letter) {
		return in_array($letter, self::$vowels);
	}
	
	static public function isConsonant($letter) {
		return !self::isVowel($letter);
	}
	
	static public function isPossessive($noun) {
		return (bool)preg_match("`('s)|(s')\$`i", trim($noun));
	}
	
	/**
	 * Only covers regular plural noun forms ending in "s", "s'", "s's" but not "'s".
	 * False positives are possible for singular nouns ending in 's' (e.g.: chess)
	 */
	static public function isPlural($noun) {
		return (bool)preg_match("`[^']s'?s?\$`i", trim($noun));
	}
}