<?php

/* o------------------------------------------------------------------------------o
 *
 *  This script is based on Martin Porter's stemming algorithm.
 *
 *  PHP5 implementation and adapted for Sphider-plus application
 *   by Rolf Kellner [Tec] Feb. 2010
 *
 * o------------------------------------------------------------------------------o */
class pt_stemmer {

	function is_vowel($c) {
		return ($c == 'a' || $c == 'e' || $c == 'i' || $c == 'o' || $c == 'u' ||
		$c == '�' || $c == '�' || $c == '�' || $c == '�' || $c == '�' ||
		$c == '�' || $c == '�' ||$c == '�' );
	}

	function getNextVowelPos($word, $start = 0) {
		$len = strlen($word);
		for ($i = $start; $i < $len; $i++)
		if (pt_stemmer::is_vowel($word[$i])) return $i;
		return $len;
	}

	function getNextConsonantPos($word, $start = 0) {
		$len = strlen($word);
		for ($i = $start; $i < $len; $i++)
		if (!pt_stemmer::is_vowel($word[$i])) return $i;
		return $len;
	}

	function endsin($word, $suffix) {
		if (strlen($word) < strlen($suffix)) return false;
		return (substr($word, -strlen($suffix)) == $suffix);
	}

	function endsinArr($word, $suffixes) {
		foreach ($suffixes as $suff) {
			if (pt_stemmer::endsin($word, $suff)) return $suff;
		}
		return '';
	}

	function removeAccent($word) {
		//return $word;
		return str_replace( array('�','�','�','�','�','�','�','�','�','�','�','�','�'),
		array('a','e','i','o','u','a','e','o','c','a','o','u','n'), $word);
	}

	function stem($word) {

		//$word = lower_case($word);

		// � and � should be treated as a vowel followed by a consonant
		$word = str_replace('�', 'a~', $word);
		$word = str_replace('�', '~o', $word);

		$len = strlen($word);
		if ($len <=2){
			return $word;
		}

		$r1 = $r2 = $rv = $len;

		//R1 is the region after the first non-vowel following a vowel, or is the null region at the end of the word if there is no such non-vowel.
		for ($i = 0; $i < ($len-1) && $r1 == $len; $i++) {
			if (pt_stemmer::is_vowel($word[$i]) && !pt_stemmer::is_vowel($word[$i+1])) {
				$r1 = $i+2;
			}
		}

		//R2 is the region after the first non-vowel following a vowel in R1, or is the null region at the end of the word if there is no such non-vowel.
		for ($i = $r1; $i < ($len -1) && $r2 == $len; $i++) {
			if (pt_stemmer::is_vowel($word[$i]) && !pt_stemmer::is_vowel($word[$i+1])) {
				$r2 = $i+2;
			}
		}

		if ($len > 3) {
			if(!pt_stemmer::is_vowel($word[1])) {
				// If the second letter is a consonant, RV is the region after the next following vowel
				$rv = pt_stemmer::getNextVowelPos($word, 2) +1;
			} elseif (pt_stemmer::is_vowel($word[0]) && pt_stemmer::is_vowel($word[1])) {
				// or if the first two letters are vowels, RV is the region after the next consonant
				$rv = pt_stemmer::getNextConsonantPos($word, 2) + 1;
			} else {
				//otherwise (consonant-vowel case) RV is the region after the third letter. But RV is the end of the word if these positions cannot be found.
				$rv = 3;
			}
		}

		$r1_txt = substr($word, $r1);
		$r2_txt = substr($word, $r2);
		$rv_txt = substr($word, $rv);

		$word_orig = $word;

		//  Step 1: Standard ending removal
		if (($suf = pt_stemmer::endsinArr($r2_txt, array('amentos', 'imentos', 'amento', 'imento', 'adoras', 'adores', 'a�o~es', 'ismos', 'istas', 'adora', 'a�a~o', 'antes', '�ncia', 'ezas', 'icos', 'icas', 'ismo', '�vel', '�vel', 'ista', 'osos', 'osas', 'ador', 'ante', 'eza', 'ico', 'ica', 'oso', 'osa'))) != '') {
			$word = substr($word, 0, -strlen($suf));    # rule1
		} elseif (($suf = pt_stemmer::endsinArr($r2_txt, array('log�a', 'log�as'))) != '') {
			$word = substr($word, 0, -strlen($suf)) . 'log';
		} elseif (($suf = pt_stemmer::endsinArr($r2_txt, array('uci�n', 'uciones'))) != '') {
			$word = substr($word, 0, -strlen($suf)) . 'u';
		} elseif (($suf = pt_stemmer::endsinArr($r2_txt, array('�ncia', '�ncias'))) != '') {
			$word = substr($word, 0, -strlen($suf)) . 'ente';
		} elseif (($suf = pt_stemmer::endsinArr($r2_txt, array('ativamente', 'ivamente', 'osamente', 'icamente', 'adamente'))) != '') {
			$word = substr($word, 0, -strlen($suf));
		} elseif (($suf = pt_stemmer::endsinArr($r1_txt, array('amente'))) != '') {
			$word = substr($word, 0, -strlen($suf));
		} elseif (($suf = pt_stemmer::endsinArr($r2_txt, array('antemente', 'avelmente', '�velmente', 'mente'))) != '') {
			$word = substr($word, 0, -strlen($suf));
		} elseif (($suf = pt_stemmer::endsinArr($r2_txt, array('abilidade', 'abilidades', 'icidade', 'icidades', 'ividad', 'ividades', 'idade', 'idades'))) != '') {
			$word = substr($word, 0, -strlen($suf));
		} elseif (($suf = pt_stemmer::endsinArr($r2_txt, array('ativa', 'ativo', 'ativas', 'ativos', 'iva', 'ivo', 'ivas', 'ivos'))) != '') {
			$word = substr($word, 0, -strlen($suf));
		} elseif (($suf = pt_stemmer::endsinArr($rv_txt, array('eira', 'eiras'))) != '') {
			$word = substr($word, 0, -strlen($suf)) . 'ir';
		}

		if ($word != $word_after0) {
			$r1_txt = substr($word, $r1);
			$r2_txt = substr($word, $r2);
			$rv_txt = substr($word, $rv);
		}
		$stem = $word;

		if ($stem == $word_orig) {
			// Do step 2 if no ending was removed by step 1: now remove verb suffixes
			if (($suf = pt_stemmer::endsinArr($rv_txt, array(   'ar�amos', 'er�amos', 'ir�amos', '�ssemos', '�ssemos', '�ssemos', 'ar�eis', 'er�eis', 'ir�eis', '�sseis', '�sseis', '�sseis',
                                                                    '�ramos', '�ramos', '�ramos', '�vamos', 'aremos', 'eremos', 'iremos', 'ariam', 'eriam', 'iriam', 'assem', 'essem', 'issem',
                                                                    'ara~o', 'era~o', 'ira~o', 'arias', 'erias', 'irias', 'ardes', 'erdes', 'irdes', 'asses', 'esses', 'isses', 'astes', 'estes', 'istes',
                                                                    '�reis', 'areis', '�reis', 'ereis', '�reis', 'ireis', '�veis', '�amos', 'armos', 'ermos', 'irmos', 'aria', 'eria', 'iria',
                                                                    'asse', 'esse', 'isse', 'aste', 'este', 'iste', 'arei', 'erei', 'irei', 'aram', 'eram', 'iram', 'avam', 'arem', 'erem', 'irem',
                                                                    'ando', 'endo', 'indo', 'adas', 'idas', 'ar�s', 'aras', 'er�s', 'eras', 'ir�s', 'avas', 'ares', 'eres', 'ires', '�eis',
                                                                    'ados', 'idos', '�mos', 'amos', 'emos', 'imos', 'iras', 'ada', 'ida', 'ar�', 'ara', 'er�', 'era', 'ir�', 'ava', 'iam',
                                                                    'ado', 'ido', 'ias', 'ais', 'eis', 'ira', 'ia', 'ei', 'am', 'em', 'ar', 'er', 'ir', 'as', 'es', 'is', 'eu', 'iu', 'ou'
                                                                    ) ))){
                                                                    	$word = substr($word, 0, -strlen($suf));
                                                                    }

                                                                    if ($word != $word_after1) {
                                                                    	$r1_txt = substr($word, $r1);
                                                                    	$r2_txt = substr($word, $r2);
                                                                    	$rv_txt = substr($word, $rv);
                                                                    }
                                                                    $stem = $word;
		}

		if ($stem != $word_orig) {
			//  Step 3
			if(pt_stemmer::endsin($rv_txt,'ci')){
				$word = substr($word, 0, -1);
				$r1_txt = substr($word, $r1);
				$r2_txt = substr($word, $r2);
				$rv_txt = substr($word, $rv);
			}
		} else {
			// Step 4 conditioned
			if (($suf = pt_stemmer::endsinArr($rv_txt, array('os', 'a', 'i', 'o', '�', '�', '�'))) != '') {
				$word = substr($word, 0, -strlen($suf));
				$r1_txt = substr($word, $r1);
				$r2_txt = substr($word, $r2);
				$rv_txt = substr($word, $rv);
			}
		}

		// Always perform step 5
		if (($suf = pt_stemmer::endsinArr($rv_txt, array(' cie', ' ci�', ' ci�', 'gue', 'gu�', 'gu�', 'e', '�', '�'))) != '') {
			$word = substr($word, 0, -strlen($suf));
		}

		$word = str_replace('a~', '�', $word);
		$word = str_replace('~o','�', $word);

		return pt_stemmer::removeAccent($word);
	}
}

?>