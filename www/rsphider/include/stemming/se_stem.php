<?php

/* o------------------------------------------------------------------------------o
 *
 *  This script is based on Martin Porter's stemming algorithm.
 *
 *  Improvements, PHP5 implementation and adapted for Sphider-plus application
 *   by Rolf Kellner [Tec] Feb. 2010
 *
 * o------------------------------------------------------------------------------o */


define('se_pattern', '/^[a-zA-ZéåäöÅÄÖ\']+$/');

class se_stemmer{

	public function stem($word) {
		//$word = lower_case($word);
		// only Swedish words follow these rules
		if ( !preg_match(se_pattern,$word) )
		return $word;

		/* R1 is the region after the first non-vowel following a vowel, or is the
		 null region at the end of the word if there is no such non-vowel. */
		if (preg_match('/[aeiouyäåö][^aeiouyäåö]/u', $word, $matches, PREG_OFFSET_CAPTURE)) {
			$r1 = $matches[0][1] + 2;
		}

		// Steps 1-3: suffix removal
		$word = self::se_stemmer_step1($word, $r1);
		$word = self::se_stemmer_step2($word, $r1);
		$word = self::se_stemmer_step3($word, $r1);

		return $word;
	}

	function se_stemmer_step1($word, $r1) {
		// Step 1:
		// Search for the longest among the following suffixes in R1, and perform the action indicated.
		if ($r1) {
			$word = preg_replace(array_reverse(array('/a$/', '/arna$/', '/erna$/', '/heterna$/', '/orna$/', '/ad$/', '/e$/', '/ade$/', '/ande$/', '/arne$/', '/are$/', '/aste$/', '/en$/', '/anden$/', '/aren$/', '/heten$/', '/ern$/', '/ar$/', '/er$/', '/heter$/', '/or$/', '/as$/', '/arnas$/', '/ernas$/', '/ornas$/', '/es$/', '/ades$/', '/andes$/', '/ens$/', '/arens$/', '/hetens$/', '/erns$/', '/at$/', '/andet$/', '/het$/', '/ast$/')), '', $word, 1);
		}

		// Delete 's' if preceded by a valid s-ending
		$word = preg_replace('/([bcdfghjklmnoprtvy])s$/', '\\1', $word);

		return $word;
	}

	function se_stemmer_step2($word, $r1) {
		// Step 2:
		// Search for one of the following suffixes in R1, and if found delete the last letter.
		if ($r1) {
			$word = preg_match('/(dd|gd|nn|dt|gt|kt|tt)$/', $word) ? substr($word, 0, -1) : $word;
		}

		return $word;
	}

	function se_stemmer_step3($word, $r1) {
		// Step 3:
		// Search for the longest among the following suffixes in R1, and perform the action indicated.
		if ($r1) {
			$word = preg_replace('/(lig|ig|els)$/', '', $word);
			$word = preg_replace('/löst$/', 'lös', $word);
			$word = preg_replace('/fullt$/', 'full', $word);
		}

		return $word;
	}
}

?>