<?php

error_reporting(E_ERROR | E_PARSE);

echo "Hello\n";
echo "'" . en_stemmer::stem("informal") . "'\n";
echo "Goodbye\n";

/* o------------------------------------------------------------------------------o
 *
 *  This script is based on Martin Porter's stemming algorithm.
 *   First PHP implementation by Jon Abernathy
 *  Improvements,  PHP5 implementation and adapted for Sphider-plus application
 *   by Rolf Kellner [Tec] March 2010
 *
 * o------------------------------------------------------------------------------o */


class en_stemmer {

	public function stem( $word ){
		 
		if ( strlen($word) > 2 ) {
			//$word = lower_case($word);
			$word = self::step_1($word);
			$word = self::step_2($word);
			$word = self::step_3($word);
			$word = self::step_4($word);
			$word = self::step_5($word);
		}

		return $word;
	}

	//  Step1, if the word is in plural form, it is reduced to singular form.
	//  Then, any -ed or -ing endings are removed as appropriate, and finally,
	//  words ending in "y" with a vowel in the stem have the "y" changed to "i".
	function step_1( $word ){
		// Step 1a
		if ( substr($word, -1) == 's' ) {
			if ( substr($word, -4) == 'sses' ) {
				$word = substr($word, 0, -2);
			} elseif ( substr($word, -3) == 'ies' ) {
				$word = substr($word, 0, -2);
			} elseif ( substr($word, -2, 1) != 's' ) {
				// If second-to-last character is not "s"
				$word = substr($word, 0, -1);
			}
		}
		// Step 1b
		if ( substr($word, -3) == 'eed' ) {
			if (self::count_voco(substr($word, 0, -3)) > 0 ) {
				// Convert '-eed' to '-ee'
				$word = substr($word, 0, -1);
			}
		} else {
			if ( preg_match('/([aeiou]|[^aeiou]y).*(ed|ing)$/', $word) ) { // vowel in stem
				// Strip '-ed' or '-ing'
				if ( substr($word, -2) == 'ed' ) {
					$word = substr($word, 0, -2);
				} else {
					$word = substr($word, 0, -3);
				}
				if ( substr($word, -2) == 'at' || substr($word, -2) == 'bl' ||
				substr($word, -2) == 'iz' ) {
					$word .= 'e';
				} else {
					$last_char = substr($word, -1, 1);
					$next_to_last = substr($word, -2, 1);
					// Strip ending double consonants to single, unless "l", "s" or "z"
					if ( self::is_consonant($word, -1) &&
					$last_char == $next_to_last &&
					$last_char != 'l' && $last_char != 's' && $last_char != 'z' ) {
						$word = substr($word, 0, -1);
					} else {
						// If VC, and cvc (but not w,x,y at end)
						if ( self::count_voco($word) == 1 && self::co_vo_co($word) ) {
							$word .= 'e';
						}
					}
				}
			}
		}
		// Step 1c: Turn y into i when another vowel in stem
		if ( preg_match('/([aeiou]|[^aeiou]y).*y$/', $word) ) { // vowel in stem
			$word = substr($word, 0, -1) . 'i';
		}
		return $word;
	}

	//  Step 2 maps double suffixes to single ones when the second-to-last character
	//  matches the given letters. So "-ization" (which is "-ize" plus "-ation"
	//  becomes "-ize". Mapping to a single character occurence speeds up the script
	//  by reducing the number of possible string searches.
	function step_2( $word ){
		switch ( substr($word, -2, 1) ) {
			case 'a':
				if ( self::replace($word, 'ational', 'ate', 0) ) {
					return $word;
				}
				if ( self::replace($word, 'tional', 'tion', 0) ) {
					return $word;
				}
				break;
			case 'c':
				if ( self::replace($word, 'enci', 'ence', 0) ) {
					return $word;
				}
				if ( self::replace($word, 'anci', 'ance', 0) ) {
					return $word;
				}
				break;
			case 'e':
				if ( self::replace($word, 'izer', 'ize', 0) ) {
					return $word;
				}
				break;
			case 'l':
				// This condition is a departure from the original algorithm;
				// I adapted it from the departure in the ANSI-C version.
				if ( self::replace($word, 'bli', 'ble', 0) ) {
					return $word;
				}
				if ( self::replace($word, 'alli', 'al', 0) ) {
					return $word;
				}
				if ( self::replace($word, 'entli', 'ent', 0) ) {
					return $word;
				}
				if ( self::replace($word, 'eli', 'e', 0) ) {
					return $word;
				}
				if ( self::replace($word, 'ousli', 'ous', 0) ) {
					return $word;
				}
				break;
			case 'o':
				if ( self::replace($word, 'ization', 'ize', 0) ) {
					return $word;
				}
				if ( self::replace($word, 'isation', 'ize', 0) ) {
					return $word;
				}
				if ( self::replace($word, 'ation', 'ate', 0) ) {
					return $word;
				}
				if ( self::replace($word, 'ator', 'ate', 0) ) {
					return $word;
				}
				break;
			case 's':
				if ( self::replace($word, 'alism', 'al', 0) ) {
					return $word;
				}
				if ( self::replace($word, 'iveness', 'ive', 0) ) {
					return $word;
				}
				if ( self::replace($word, 'fulness', 'ful', 0) ) {
					return $word;
				}
				if ( self::replace($word, 'ousness', 'ous', 0) ) {
					return $word;
				}
				break;
			case 't':
				if ( self::replace($word, 'aliti', 'al', 0) ) {
					return $word;
				}
				if ( self::replace($word, 'iviti', 'ive', 0) ) {
					return $word;
				}
				if ( self::replace($word, 'biliti', 'ble', 0) ) {
					return $word;
				}
				break;
			case 'g':
				// This condition is a departure from the original algorithm;
				// I adapted it from the departure in the ANSI-C version.
				if ( self::replace($word, 'logi', 'log', 0) ) { //*****
					return $word;
				}
				break;
		}
		return $word;
	}

	//  Step 3 works in a similar stragegy to step 2, though checking the last character.
	function step_3( $word ){
		switch ( substr($word, -1) ) {
			case 'e':
				if ( self::replace($word, 'icate', 'ic', 0) ) {
					return $word;
				}
				if ( self::replace($word, 'ative', '', 0) ) {
					return $word;
				}
				if ( self::replace($word, 'alize', 'al', 0) ) {
					return $word;
				}
				break;
			case 'i':
				if ( self::replace($word, 'iciti', 'ic', 0) ) {
					return $word;
				}
				break;
			case 'l':
				if ( self::replace($word, 'ical', 'ic', 0) ) {
					return $word;
				}
				if ( self::replace($word, 'ful', '', 0) ) {
					return $word;
				}
				break;
			case 's':
				if ( self::replace($word, 'ness', '', 0) ) {
					return $word;
				}
				break;
		}
		return $word;
	}

	//  Step 4 works similarly to steps 3 and 2, above, though it removes
	//  the endings in the context of VCVC
	//  (vowel-consonant-vowel-consonant combinations).
	function step_4( $word ){
		switch ( substr($word, -2, 1) ) {
			case 'a':
				if ( self::replace($word, 'al', '', 1) ) {
					return $word;
				}
				break;
			case 'c':
				if ( self::replace($word, 'ance', '', 1) ) {
					return $word;
				}
				if ( self::replace($word, 'ence', '', 1) ) {
					return $word;
				}
				break;
			case 'e':
				if ( self::replace($word, 'er', '', 1) ) {
					return $word;
				}
				break;
			case 'i':
				if ( self::replace($word, 'ic', '', 1) ) {
					return $word;
				}
				break;
			case 'l':
				if ( self::replace($word, 'able', '', 1) ) {
					return $word;
				}
				if ( self::replace($word, 'ible', '', 1) ) {
					return $word;
				}
				break;
			case 'n':
				if ( self::replace($word, 'ant', '', 1) ) {
					return $word;
				}
				if ( self::replace($word, 'ement', '', 1) ) {
					return $word;
				}
				if ( self::replace($word, 'ment', '', 1) ) {
					return $word;
				}
				if ( self::replace($word, 'ent', '', 1) ) {
					return $word;
				}
				break;
			case 'o':
				// special cases
				if ( substr($word, -4) == 'sion' || substr($word, -4) == 'tion' ) {
					if ( self::replace($word, 'ion', '', 1) ) {
						return $word;
					}
				}
				if ( self::replace($word, 'ou', '', 1) ) {
					return $word;
				}
				break;
			case 's':
				if ( self::replace($word, 'ism', '', 1) ) {
					return $word;
				}
				break;
			case 't':
				if ( self::replace($word, 'ate', '', 1) ) {
					return $word;
				}
				if ( self::replace($word, 'iti', '', 1) ) {
					return $word;
				}
				break;
			case 'u':
				if ( self::replace($word, 'ous', '', 1) ) {
					return $word;
				}
				break;
			case 'v':
				if ( self::replace($word, 'ive', '', 1) ) {
					return $word;
				}
				break;
			case 'z':
				if ( self::replace($word, 'ize', '', 1) ) {
					return $word;
				}
				break;
		}
		return $word;
	}


	//  Step 5 removes a final "-e" and changes "-ll" to "-l" in the context
	//  of VCVC (vowel-consonant-vowel-consonant combinations).
	function step_5( $word ){
		if ( substr($word, -1) == 'e' ) {
			$short = substr($word, 0, -1);
			// Only remove in vcvc context...
			if ( self::count_voco($short) > 1 ) {
				$word = $short;
			} elseif ( self::count_voco($short) == 1 && !self::co_vo_co($short) ) {
				$word = $short;
			}
		}
		if ( substr($word, -2) == 'll' ) {
			// Only remove in vcvc context...
			if ( self::count_voco($word) > 1 ) {
				$word = substr($word, 0, -1);
			}
		}
		return $word;
	}

	//  Checks that the specified letter (position) in the word is a consonant.
	//  Handy check adapted from the ANSI C program. Regular vowels always return
	//  FALSE, while "y" is a special case: if the prececing character is a vowel,
	//  "y" is a consonant, otherwise it's a vowel.
	// And, if checking "y" in the first position and the word starts with "yy",
	// return true even though it's not a legitimate word (it crashes otherwise).
	function is_consonant( $word, $pos ){
		// Sanity checking $pos
		if ( abs($pos) > strlen($word) ) {
			if ( $pos < 0 ) {
				// Points "too far back" in the string. Set it to beginning.
				$pos = 0;
			} else {
				// Points "too far forward." Set it to end.
				$pos = -1;
			}
		}
		$char = substr($word, $pos, 1);
		switch ( $char ) {
			case 'a':
			case 'e':
			case 'i':
			case 'o':
			case 'u':
				return false;
			case 'y':
				if ( $pos == 0 || strlen($word) == -$pos ) {
					// Check second letter of word.
					// If word starts with "yy", return true.
					if ( substr($word, 1, 1) == 'y' ) {
						return true;
					}
					return !(self::is_consonant($word, 1));
				} else {
					return !(self::is_consonant($word, $pos - 1));
				}
			default:
				return true;
		}
	}

	//  Counts (measures) the number of vowel-consonant occurences.
	//  Based on the algorithm; this handy function counts the number of
	//  occurences of vowels (1 or more) followed by consonants (1 or more),
	//  ignoring any beginning consonants or trailing vowels. A legitimate
	//  VC combination counts as 1 (ie. VCVC = 2, VCVCVC = 3, etc.).

	function count_voco( $word ){
		$m = 0;
		$length = strlen($word);
		$prev_c = false;
		for ( $i = 0; $i < $length; $i++ ) {
			$is_c = self::is_consonant($word, $i);
			if ( $is_c ) {
				if ( $m > 0 && !$prev_c ) {
					$m += 0.5;
				}
			} else {
				if ( $prev_c || $m == 0 ) {
					$m += 0.5;
				}
			}
			$prev_c = $is_c;
		}
		$m = floor($m);
		return $m;
	}

	//  Checks for a specific consonant-vowel-consonant condition.
	//  This function is named directly from the original algorithm. It
	//  looks the last three characters of the word ending as
	//  consonant-vowel-consonant, with the final consonant NOT being one
	//  of "w", "x" or "y".

	function co_vo_co( $word ){
		if ( strlen($word) >= 3 ) {
			if ( self::is_consonant($word, -1) && !self::is_consonant($word, -2) &&
			self::is_consonant($word, -3) ) {
				$last_char = substr($word, -1);
				if ( $last_char == 'w' || $last_char == 'x' || $last_char == 'y' ) {
					return false;
				}
				return true;
			}
		}
		return false;
	}

	//  Replaces suffix, if found and word measure is a minimum count.
	function replace( $word, $suffix, $replace, $m = '0' ){
		$sl = strlen($suffix);
		if ( substr($word, -$sl) == $suffix ) {
			$short = substr_replace($word, '', -$sl);
			if ( self::count_voco($short) > $m ) {
				$word = $short . $replace;
			}
			// Found this suffix, doesn't matter if replacement succeeded
			return true;
		}
		return false;
	}
}

?>
