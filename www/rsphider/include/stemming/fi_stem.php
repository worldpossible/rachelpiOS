<?php

/* o------------------------------------------------------------------------------o
 *
 *  Finnish stemmer to remove inflectional suffixes
 *
 *  PHP5 implementation and adapted for Sphider-plus application
 *   by Rolf Kellner [Tec] Feb. 2010
 *
 * o------------------------------------------------------------------------------o */


class fi_stemmer{

	function is_vowel($c) {
		return ($c == 'a' || $c == 'ä' || $c == 'å' || $c == 'e' || $c == 'i' || $c == 'o' || $c == 'ö' || $c == 'u' || $c == 'y' );
	}

	function removeFinnishAccent($word){
		$word = str_replace(array('ä', 'å', 'ö'), array('a', 'a', 'o'), $word);
		return $word;
	}

	public function stem($word) {
		if (strlen($word) > 2) {
			$word = self::removeFinnishAccent($word);
			$word = self::fi_stemmer_step1($word);
			$word = self::fi_stemmer_step2($word);
			$word = self::fi_stemmer_step3($word);
			$word = self::norm_finnish($word);
			$word = self::norm2_finnish($word);
		}
		return $word;
	}

	function norm_finnish($word){
		if (strlen($word) >= 4) {   /* -hde  -> -ksi  */
			$word = preg_replace('/(hde)$/', 'ksi', $word);
		}

		if (strlen($word) >= 3) {   /* -ei  -> -  */
			$word = preg_replace('/(ei|at|in|en)$/', '', $word);
			return $word;
		}

		if (strlen($word) >= 2) {   /* plural    -t  OR  -(aeiouy)i */
			if (preg_match('/(t|s|j|e|a|ä|å|ö)$/', $word)) {
				$word = preg_replace('/(t|s|j|e|a|ä|å|ö)$/', '', $word);
			}
			else {
				$word = preg_replace('/(ai|ei|ii|oi|ui|yi)$/', '', $word);
			}
		}
		return $word;
	}

	function norm2_finnish($word){
		if (strlen($word) >= 7) {   /* -e, -o,  -u */
			$word = preg_replace('/(e|o|u)$/', '', $word);
		}
		if (strlen($word) >= 3) {   /* plural    -i  */
			$word = preg_replace('/(i)$/', '', $word);
			$word = self::removeDoubleKPT($word);
		}
		return $word;
	}

	function removeDoubleKPT($word){
		if (strlen($word) > 3) { /*  remove double kk pp tt  */
			$word = str_replace("kk", "k", $word);
			$word = str_replace("tt", "t", $word);
		}
		return $word;
	}

	function fi_stemmer_step1($word){

		if (strlen($word) >= 7) {    /*    -kin  -ko */
			$word1 = preg_replace('/(kin|ko)$/', '', $word);
			if ($word1 != $word) return $word1;
		}

		if (strlen($word) >= 10) {
			/*    -dellinen  for adjective  &&    -dellisuus  for adverb  */
			$word = preg_replace('/(dellinen|dellisuus)$/', '', $word);
		}
		return $word;
	}

	function fi_stemmer_step2($word){
		if (strlen($word) >= 4) {
			$word = preg_replace('/(lla|tse|sti|ni)$/', '', $word);
			$word = preg_replace('/(aa)$/', 'a', $word);
		}
		return $word;
	}

	function fi_stemmer_step3($word){
		if (strlen($word) >= 7) {/* genetive -nnen  -s  &&  essive -ntena  -s   &&   -tten  -s   &&  genitive plural   -eiden  -s  */
			$word1 = preg_replace('/(nnen|ntena|tten|eiden|ssaan)$/', 's', $word);
			if ($word1 != $word) return $word1;
		}

		if (strlen($word) >= 5) {
			/* komitatiivi plural   -neen  && illatiivi   -siin,  etc.  && illatiivi   -seen,  etc.  */
			$word1 = preg_replace('/(neen|niin|siin|seen)$/', '', $word);
			if ($word1 != $word) return $word1;

			/* illatiivi   -hVn,  V=vowel */
			$word1 = preg_replace('/(han|hän|hån|hen|hin|hon|hön|hun|hyn)$/', '', $word);
			if ($word1 != $word) return $word1;

			/* genitive plural   -teen,  */
			$word1 = preg_replace('/(teen)$/', '', $word);
			if ($word1 != $word) return $word1;

			/* genitive plural   -den  && -ksen -s  */
			$word1 = preg_replace('/(den|ksen)$/', 's', $word);
			if ($word1 != $word) return $word1;

			/*  and so on */
			$word1 = preg_replace('/(inen|ssa|sta|staan|taan|eita|lla|lta|tta|ksi|lle)$/', '', $word);
			if ($word1 != $word) return $word1;
			//  Sphider-plus likes accents
			$word1 = preg_replace('/(impi|impa|impä|immi|imma|immä|eja|ejä)$/', '', $word);
			if ($word1 != $word) return $word1;
			$word1 = preg_replace('/(mme|nsä|stään|iä|än|älleen|ä|äni|änsä|itä|tä|inä|issä|älle|ällä)$/', '', $word);
			if ($word1 != $word) return $word1;
			$word1 = preg_replace('/(änä|ässä|ästä|ästään|ät|ää|ään|eellä|eeltä|eenä|eessä|eestä|eissä)$/', '', $word);
			if ($word1 != $word) return $word1;
		}

		if (strlen($word) >= 4) {
			return (preg_replace('/(na|ne|nein)$/', '', $word));
		}

		if (strlen($word) >= 3) {
			/* partitiivi   -(t,j)a  */
			$word1 = preg_replace('/(ta|ja)$/', '', $word);
			if ($word1 != $word) return $word1;

			/* illatiivi   -an, -en, -on, -in, -un, -yn, etc.  */
			$word1 = preg_replace('/(an|än|ån|en|on|ön|in|un|yn)$/', '', $word);
			if ($word1 != $word) return $word1;

			/* genetiivi or instruktiivi   -n  */
			$word = preg_replace('/(n)$/', '', $word);
		}
		return $word;
	}

}

?>