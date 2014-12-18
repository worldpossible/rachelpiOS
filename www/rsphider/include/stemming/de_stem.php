<?php

/* o------------------------------------------------------------------------------o
 *
 *  This script was originally written by Reiner Miericke in 2007
 *   as a result of Martin Porter's stemming algorithm for German language.
 *
 *  Improvements, PHP5 implementation and adapted for Sphider-plus application
 *   by Rolf Kellner [Tec] Feb. 2010
 *
 * o------------------------------------------------------------------------------o */

define('DE_VOKALE', 'aeiouyäöü');
define('DE_WORT_MUSTER', '/^[A-ZÄÖÜa-zßäöü]+$/');

class de_stemmer{

	private function de_region_n($word) {
		$r = strcspn($word, DE_VOKALE);
		return $r + strspn($word, DE_VOKALE, $r) + 1;
	}

	private function de_stem_preprocess($word) {
		//$word = lower_case($word);
		$word = str_replace("ß", "ss", $word);
		// replace ß by ss, and put u and y between vowels into upper case
		$word = preg_replace(   array(  '/ß/',
                                        '/(?<=['. DE_VOKALE .'])u(?=['. DE_VOKALE .'])/u',
                                        '/(?<=['. DE_VOKALE .'])y(?=['. DE_VOKALE .'])/u'
                                        ),
                                        array(  'ss', 'u', 'y'  ), $word );
                                        return $word;
	}

	private function de_stem_postprocess($word) {

		if (!self::de_exception($word)) {	// check for exceptions
			$word = strtr($word, array( 'ä' => 'a', 'á' => 'a',
                                            'ë' => 'e', 'é' => 'e',
                                            'ï' => 'i', 'í' => 'i',
                                            'ö' => 'o', 'ó' => 'o',
                                            'ü' => "ü", 'ú' => 'u'
                                            )
                                            );
		}
		return $word;
	}

	public function stem($word) {
		// only German words will follow this pattern
		if ( !preg_match(DE_WORT_MUSTER,$word) )
		return $word;

		$stamm = self::de_stem_preprocess($word);
		//$umlaut = preg_match('/[äöüÄÖÜ]/', $word);
		$umlaut = 0;

		/*
		 * R1 is the region after the first non-vowel following a vowel,
		 or is the null region at the end of the word if there is no such non-vowel.
		 * R2 is the region after the first non-vowel following a vowel in R1,
		 or is the null region at the end of the word if there is no such non-vowel.
		 */

		$l = strlen($stamm);
		$r1 = self::de_region_n($stamm);
		$r2 = $r1 == $l  ?  $r1  :  $r1 + self::de_region_n(substr($stamm, $r1));
		// unshure about interpreting the following rule:
		// "then R1 is ADJUSTED so that the region before it contains at least 3 letters"
		if ($r1 < 3) {
			$r1 = 3;
		}

		/*  Step0
		 remove useless ends of words
		 */
		if (preg_match('/(chen|lein)$/u', $stamm, $hits, PREG_OFFSET_CAPTURE, $r1)) {
			$stamm = substr($stamm, 0, $hits[0][1]);
		}

		$stamm = preg_replace('/(bar)$/u', '', $stamm);

		/*  Step 1
		 Search for the longest among the following suffixes,
		 (a) e   em   en   ern   er   es
		 (b) s (preceded by a valid s-ending)
		 and delete if in R1.
		 (Of course the letter of the valid s-ending is not necessarily in R1)
		 */
		if (preg_match('/(e|em|en|ern|er|es)$/u', $stamm, $hits, PREG_OFFSET_CAPTURE, $r1)) {
			$stamm = substr($stamm, 0, $hits[0][1] - $umlaut);
		} elseif (preg_match('/(?<=(b|d|f|g|h|k|l|m|n|r|t))s$/u', $stamm, $hits, PREG_OFFSET_CAPTURE, $r1)) {
			$stamm = substr($stamm, 0, $hits[0][1] - $umlaut);
		}

		/*  Step 2
		 Search for the longest among the following suffixes,
		 (a) en   er   est
		 (b) st (preceded by a valid st-ending, itself preceded by at least 3 letters)
		 and delete if in R1.
		 */
		if (preg_match('/(en|er|est)$/u', $stamm, $hits, PREG_OFFSET_CAPTURE, $r1)) {
			$stamm = substr($stamm, 0, $hits[0][1] - $umlaut);
		} elseif (preg_match('/(?<=(b|d|f|g|h|k|l|m|n|t))st$/u', $stamm, $hits, PREG_OFFSET_CAPTURE, $r1)) {
			$stamm = substr($stamm, 0, $hits[0][1] - $umlaut);
		}


		/*   Step 3:
		 d-suffixes
		 Search for the longest among the following suffixes, and perform the action indicated.
		 end   ung
		 delete if in R2
		 if preceded by ig, delete if in R2 and not preceded by e
		 ig   ik   isch
		 delete if in R2 and not preceded by e
		 lich   heit
		 delete if in R2
		 if preceded by er or en, delete if in R1
		 keit
		 delete if in R2
		 if preceded by lich or ig, delete if in R2
		 ^ means R1 ?
		 */
		if (preg_match('/(?<=eig)(end|ung)$/u', $stamm, $hits, PREG_OFFSET_CAPTURE, $r2)) {
			;
		}
		elseif (preg_match('/(end|ung)$/u', $stamm, $hits, PREG_OFFSET_CAPTURE, $r2)) {
			$stamm = substr($stamm, 0, $hits[0][1] - $umlaut);
		}
		elseif (preg_match('/(?<![e])(ig|ik|isch)$/u', $stamm, $hits, PREG_OFFSET_CAPTURE, $r2)) {
			$stamm = substr($stamm, 0, $hits[0][1] - $umlaut);
		}
		elseif (preg_match('/(?<=(er|en))(lich|heit)$/u', $stamm, $hits, PREG_OFFSET_CAPTURE, $r1)) {
			$stamm = substr($stamm, 0, $hits[0][1] - $umlaut);
		}
		elseif (preg_match('/(lich|heit)$/u', $stamm, $hits, PREG_OFFSET_CAPTURE, $r2)) {
			$stamm = substr($stamm, 0, $hits[0][1] - $umlaut);
		}
		elseif (preg_match('/(?<=lich)keit$/u', $stamm, $hits, PREG_OFFSET_CAPTURE, $r1)) {
			$stamm = substr($stamm, 0, $hits[0][1] - $umlaut);
		}
		elseif (preg_match('/(?<=ig)keit$/u', $stamm, $hits, PREG_OFFSET_CAPTURE, $r1)) {
			$stamm = substr($stamm, 0, $hits[0][1] - $umlaut);
		}
		elseif (preg_match('/keit$/u', $stamm, $hits, PREG_OFFSET_CAPTURE, $r2)) {
			$stamm = substr($stamm, 0, $hits[0][1] - $umlaut);
		}
		elseif (preg_match('/schaft$/u', $stamm, $hits, PREG_OFFSET_CAPTURE, $r1)) {
			$stamm = substr($stamm, 0, $hits[0][1] - $umlaut);
		}

		return self::de_stem_postprocess($stamm);
	}

	//first try to set up a list of exception                        */
	private function de_exception($word){
		static $de_exceptions = array (
                'schön'	=> 'schön',     // NOT'schon'
                'blüt'	=> 'blüt',	    // Blüte (NOT Blut)
                'kannt'	=> 'kenn',
                'küch'	=> 'küch',	    // Küchen (NOT Kuchen)
                'mög'	=> 'mög',
                'mocht'	=> 'mög',
                'mag'	=> 'mög',
                'ging'	=> 'geh',
                'lief'	=> 'lauf',
                'änd' 	=> 'änd'	    // ändern (NOT andern)
		);

		//return FALSE;
		if ( array_key_exists($word, $de_exceptions) ){
			$word = $de_exceptions[$word];
			return TRUE;
		} else {
			return FALSE;
		}
	}

}

?>
