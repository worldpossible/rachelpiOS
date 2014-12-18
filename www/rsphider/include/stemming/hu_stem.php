<?php

/* o------------------------------------------------------------------------------o
 *
 *   Hungarian stemmer trying to remove the suffixes corresponding to
 *   the different cases, the possessive and the number (plural) for Hungarian nouns.
 *   Original by J. Savoy (University of Neuchatel)
 *
 *  Improvements, PHP5 implementation and adapted for Sphider-plus application
 *   by Rolf Kellner [Tec] Feb. 2010
 *
 * o------------------------------------------------------------------------------o */

define ('hu_regex_vowel', '/(a|á|e|é|i|o|ö|ó|ő|u|ü|ű|y)$/');

class hu_stemmer{

	public function stem($word) {
		if (strlen($word) > 2) {
			$word = self::hu_remove_case($word);
			$word = self::hu_remove_possessive($word);
			$word = self::hu_remove_plural($word);
			$word = self::hu_normalize($word);
		}
		return($word);
	}

	function hu_normalize($word){
		/* -{aoe} */
		$word = preg_replace('/(a|á|e|é|o|ö|i)$/', '', $word);
		return($word);
	}

	/* Remove one of the various suffixes corresponding to a given case */
	function hu_remove_case($word){

		if (strlen($word) >=5) {

			/* -kent  modal */
			$word1 = preg_replace('/(kent)$/', '', $word);
			if ($word1 != $word) return $word1;
		}

		if (strlen($word) >=4) {
			/* -n{ae}k dative  */
			$word1 = preg_replace('/(nak|nek)$/', '', $word);
			if ($word1 != $word) return $word1;

			/* -C(ae}l instrumentive  (the consonant C is duplicated) */
			if (substr($word, -2, 1) == substr($word, -3, 1) && preg_match(hu_regex_vowel, substr($word, 0, -1)) && preg_match('/al|el$/', $word)){
				$word1 = substr($word, 0, -3);
				return $word1;
			}

			$word1 = preg_replace('/(val|vel|ert|rol|ban|ben|bol|böl|nal|nelhoz|höz|hez|tol|töl)$/', '', $word) ;
			if ($word1 != $word) return $word1;
		}

		if (strlen($word) >=3) {

			/* -{aeo}t  accusative */
			$word1 = preg_replace('/(at|ot|et)$/', '', $word);
			if ($word1 != $word) return $word1;

			/* -C(ae} transformative  (the consonant C is duplicated) */
			if (substr($word, -1, 1) == substr($word, -2, 1) && preg_match(hu_regex_vowel, substr($word, 0, -1)) && preg_match('/a|e$/', $word)){
				$word1 = substr($word, 0, -2);
				return $word1;
			}

			/* -v(ae} transformative  */
			$word1 = preg_replace('/(va|ve)$/', '', $word);
			if ($word1 != $word) return $word1;


			/* C-{oe}n superessive (the consonant C is duplicated)  */
			if (substr($word, -2, 1) == substr($word, -3, 1) && preg_match(hu_regex_vowel, substr($word, 0, -1)) && preg_match('/on|en$/', $word)){
				$word1 = substr($word, 0, -3);
				return $word1;
			}

			/* and so on  */
			$word = preg_replace('/(ra|re|ba|be|ul|ig|t|n)$/', '', $word);

			return($word);
		}
	}

	/* remove the possessive suffix added to the end of a noun */
	function hu_remove_possessive($word){

		/*  We need to make the distinction between four possibilities:
		 - a single object (object:singular or o:sing)
		 is the property of one(p:sing) or more(p:plur) beings;
		 - two (or more) objects (object:plural or o:plur)
		 are the property of a single (p:sing) or not (p:plur)
		 */

		if (strlen($word) >=5) {

			/* C-{ao}tok  your (p:plur; o:singl) (with a consonant C) */
			if (preg_match(hu_regex_vowel, substr($word, 0, -3)) && preg_match('/otok|atok$/', $word)){
				$word1 = substr($word, 0, -4);
				return $word1;
			}

			/* C-etek  your (p:plur; o:singl) (with a consonant C) */
			if (preg_match(hu_regex_vowel, substr($word, 0, -4)) && preg_match('/etek$/', $word)){
				$word1 = substr($word, 0, -4);
				return $word1;
			}

			$word1 = preg_replace('/(itek|itok)$/', '', $word);
			if ($word1 != $word) return $word1;
		}

		if (strlen($word) >=4) {
			/* C-{u"u}nk  our (p:plur; o:sing) (with a consonant C) */
			if (preg_match(hu_regex_vowel, substr($word, 0, -3)) && preg_match('/unk|ünk$/', $word)){
				$word1 = substr($word, 0, -4);
				return $word1;
			}

			/* C-t{oe}k  your (p:plur; o:sing)  OR V-juk  their (p:plur; o:sing) ((with a consonant C) */
			if (preg_match(hu_regex_vowel, substr($word, 0, -3)) && preg_match('/tok|tek|juk$/', $word)){
				$word1 = substr($word, 0, -4);
				return $word1;
			}

			/* -ink  our (p:plur; o:plur) */
			$word1 = preg_replace('/(ink)$/', '', $word);
			if ($word1 != $word) return $word1;
		}

		if (strlen($word) >=3) {

			/* C-{aoe}m  my (p:sing; o:sing) (with a consonant C)  OR   C-{aoe}d  your (p:sing; o:sing) (with a consonant C) */
			if (preg_match(hu_regex_vowel, substr($word, 0, -2)) && preg_match('/am|em|om|ad|ed|od$/', $word)){
				$word1 = substr($word, 0, -3);
				return $word1;
			}

			/* C-uk  their  (p:plur; o:sing) (with a consonant C)  OR  V-nk  our (p:plur; o:sing) (with a vowel V) OR  V-j(ae)  her/his (p:sing; o:sing) (with a vowel V) */
			if (preg_match(hu_regex_vowel, substr($word, 0, -2)) && preg_match('/uk|nk|ja|je$/', $word)){
				$word1 = substr($word, 0, -2);
				return $word1;
			}

			/* -im  my   (p:sing; o:plur)  */
			/* -id  your (p:sing; o:plur)  */
			/* -ik  their (p:plur; o:plur)  */
			$word1 = preg_replace('/(im|id|ik)$/', '', $word);
			if ($word1 != $word) return $word1;
		}

		if (strlen($word) >=2) {

			/* C-(ae}  her/his (p:sing; o:sing) (with a consonant C)  */
			if (preg_match(hu_regex_vowel, substr($word, 0, -1)) && preg_match('/a|e|m|d$/', $word)){
				$word1 = substr($word, 0, -1);
				return $word1;
			}

			/* -i his/her (p:sing; o:plur) */
			$word = preg_replace('/(i)$/', '', $word);
		}
		return($word);
	}


	/* to remove the plural suffix, usually the -k */
	function hu_remove_plural($word){

		if (strlen($word) >=3) {
			/* -{aoe}k  plural */
			$word1 = preg_replace('/(ak|ok|ek)$/', '', $word);
			if ($word1 != $word) return $word1;
		}

		$word = preg_replace('/(k)$/', '', $word);
		return($word);
	}



}
?>