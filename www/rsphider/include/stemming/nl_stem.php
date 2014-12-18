<?php

/* o------------------------------------------------------------------------------o
 *
 *  This script is based on Martin Porter's stemming algorithm for Dutch language.
 *  Improvements:
 *    - Convert s/f to z/v when removing double vowel in last syllable.
 *    - Include more consonants in undoubling operation.
 *    - Correctly remove apostrophe-s (e.g. "pagina's").
 *    - Correctly strip accented suffixes (e.g. industriële)
 *
 *   and PHP5 implementation and adaptation for Sphider-plus application
 *   by Rolf Kellner [Tec] Feb. 2010
 *
 * o------------------------------------------------------------------------------o */

class nl_stemmer{

	public function stem($word) {
		global $_dutchstemmer_step2;

		$_dutchstemmer_step2= FALSE;

		//$word = lower_case($word);

		// Step 0: early (accented) suffix removal
		$word = self::dutchstemmer_step0($word, $r1, $r2);

		// Remove accents
		$word = str_replace(array('ä', 'ë', 'ï', 'ö', 'ü', 'á', 'é', 'í', 'ó', 'ú'),
		array('a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u'),
		$word);

		// Put initial y, y after a vowel, and i between vowels into upper case (treat as consonants).
		$word = preg_replace(array('/^y|(?<=[aeiouyè])y/u', '/(?<=[aeiouyè])i(?=[aeiouyè])/u'),
		array('Y', 'I'),
		$word);

		/* R1 is the region after the first non-vowel following a vowel, or is the
		 null region at the end of the word if there is no such non-vowel. */
		if (preg_match('/[aeiouyè][^aeiouyè]/u', $word, $matches, PREG_OFFSET_CAPTURE)) {
			$r1 = $matches[0][1] + 2;
		}

		/* R2 is the region after the first non-vowel following a vowel in R1, or is
		 the null region at the end of the word if there is no such non-vowel. */
		if (preg_match('/[aeiouyè][^aeiouyè]/u', $word, $matches, PREG_OFFSET_CAPTURE, $r1)) {
			$r2 = $matches[0][1] + 2;
		}

		// Steps 1-4: suffix removal
		$word = self::dutchstemmer_step1($word, $r1, $r2);
		$word = self::dutchstemmer_step2($word, $r1, $r2);
		$word = self::dutchstemmer_step3($word, $r1, $r2);
		$word = self::dutchstemmer_step4($word, $r1, $r2);

		$word = str_replace(array('Y', 'I'), array('y', 'i'), $word);

		return $word;
	}

	function dutchstemmer_undouble($word) {
		return preg_match('/(bb|dd|gg|kk|ll|mm|nn|pp|rr|ss|tt|zz)$/u', $word) ? substr($word, 0, -1) : $word;
	}

	function dutchstemmer_step0($word) {
		// Step 0: accented suffixes
		return preg_replace('/eën$/u', 'e', preg_replace('/(ieel|iële|ieën)$/u', 'ie', $word));
	}

	function dutchstemmer_step1($word, $r1, $r2) {
		// Step 1:
		// Search for the longest among the following suffixes, and perform the action indicated
		if ($r1) {
			// -heden
			if (preg_match('/heden$/u', $word, $matches, 0, $r1)) {
				return preg_replace('/heden$/u', 'heid', $word, -1, $count);
			}
			// -en(e)
			else if (preg_match('/(?<=[^aeiouyè]|gem)ene?$/u', $word, $matches, 0, $r1)) {
				return self::dutchstemmer_undouble(preg_replace('/ene?$/u', '', $word, -1, $count));
			}
			// -s(e)
			else if (preg_match('/(?<=[^jaeiouyè])se?$/u', $word, $matches, 0, $r1)) {
				return rtrim(preg_replace('/se?$/u', '', $word, -1, $count), "'");
			}
		}
		return $word;
	}

	function dutchstemmer_step2($word, $r1, $r2) {
		// Step 2:
		// Delete suffix e if in R1 and preceded by a non-vowel, and then undouble the ending
		if ($r1) {
			if (preg_match('/(?<=[^aeiouyè])e$/u', $word, $matches, 0, $r1)) {
				// TODO: this should be here to make any sense
				// global $_dutchstemmer_step2;
				$_dutchstemmer_step2= TRUE;
				return self::dutchstemmer_undouble(preg_replace('/e$/u', '', $word, -1, $count));
			}
		}
		return $word;
	}

	function dutchstemmer_step3($word, $r1, $r2) {
		global $_dutchstemmer_step2;

		// Step 3a: heid
		// delete heid if in R2 and not preceded by c, and treat a preceding en as in step 1(b)
		if ($r2) {
			if (preg_match('/(?<!c)heid$/u', $word, $matches, 0, $r2)) {
				$word = preg_replace('/heid$/u', '', $word, -1, $count);
				if (preg_match('/en$/u', $word, $matches, 0, $r1)) {
					$word = self::dutchstemmer_undouble(preg_replace('/en$/u', '', $word, -1, $count));
				}
			}
		}

		// Step 3b: d-suffixes (*)
		// Search for the longest among the following suffixes, and perform the action indicated.
		if ($r2) {
			// -baar
			if (preg_match('/baar$/u', $word, $matches, 0, $r2)) {
				$word = preg_replace('/baar$/u', '', $word, -1, $count);
			}
			// -lijk
			else if (preg_match('/lijk$/u', $word, $matches, 0, $r2)) {
				$word = self::dutchstemmer_step2(preg_replace('/lijk$/u', '', $word, -1, $count), $r1, $r2);
			}
			// -end / -ing
			else if (preg_match('/(end|ing)$/u', $word, $matches, 0, $r2)) {
				$word = preg_replace('/(end|ing)$/u', '', $word, -1, $count);
				// -ig
				if (preg_match('/(?<!e)ig$/u', $word, $matches, 0, $r2)) {
					$word = preg_replace('/ig$/u', '', $word, -1, $count);
				}
			}
			// -ig
			else if (preg_match('/(?<!e)ig$/u', $word, $matches, 0, $r2)) {
				$word = preg_replace('/ig$/u', '', $word, -1, $count);
			}
			// -bar
			else if ($_dutchstemmer_step2&& preg_match('/bar$/u', $word, $matches, 0, $r2)) {
				$word = preg_replace('/bar$/u', '', $word, -1, $count);
			}
		}
		return $word;
	}

	function dutchstemmer_step4($word, $r1, $r2) {
		// Step 4: undouble vowel
		// If the words ends CVD, where C is a non-vowel, D is a non-vowel other than
		// I, and V is double a, e, o or u, remove one of the vowels from V
		// (for example, maan -> man, brood -> brod).
		if (preg_match('/[^aeiouyè](aa|ee|oo|uu)[^Iaeiouyè]$/u', $word)) {
			$word = substr($word, 0, -2) . str_replace(array('s', 'f'), array('z', 'v'), substr($word, -1));
		}
		return $word;
	}
}

?>
