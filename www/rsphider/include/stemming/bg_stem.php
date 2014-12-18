<?php

/* o------------------------------------------------------------------------------o
 *
 *  This script was originally written in PEARL by  Ljiljana Dolamic and Jacques Savoy
 *
 *  Improvements, PHP5 implementation and adapted for Sphider-plus application
 *   by Rolf Kellner [Tec] Feb. 2010
 *
 * o------------------------------------------------------------------------------o */

define('bg_article', '/(ът|то|те|та)$/');
define('bg_plural', '/(ища|ище|ове|та)$/');
define('bg_normal', '/(еи|ии|аой)$/');

class bg_stemmer{

	public function stem($word) {
		//$word = lower_case($word);
		$word = self::Remove_Article($word);
		$word = self::Remove_Plural($word);
		$word = self::Normalize($word);
		$word = self::Palatalization($word);
		return $word;
	}

	private function Remove_Article($word) {
		$word = preg_replace(bg_article, '', $word);
		if (preg_match('/(ят)$/', $word)){
			if (preg_match("/(a|e|и|о|у|ъ)$/", substr($word, 0, -4))) { //  word ends with vowal + ят
				$word = preg_replace('/(ят)$/', 'й', $word);
			} else {
				$word = preg_replace('/(ят)$/', '', $word);
			}
		}
		return $word;
	}

	private function Remove_Plural($word) {

		$word = preg_replace(bg_plural, '', $word);
		$word = preg_replace('/(овци)$/', 'о', $word);
		$word = preg_replace('/(евци)$/', 'е', $word);
		$word = preg_replace('/(\.\.е\.и)$/', '.я.', $word);


		if (preg_match('/(еве)$/', $word)){
			if (preg_match("/(a|e|и|о|у|ъ)$/", substr($word, 0, -6))) { //  word ends with vowal + еве
				$word = preg_replace('/(еве)$/', 'й', $word);
			} else {
				$word = preg_replace('/(еве)$/', '', $word);
			}
		}
		return $word;
	}

	private function Normalize($word) {

		$word = preg_replace(bg_normal, '', $word);
		$word = preg_replace('/(йн)$/', 'н', $word);
		$word = preg_replace('/(LеC)$/', 'LC', $word);
		$word = preg_replace('/(LъL)$/', 'LL', $word);

		if (preg_match('/(я)$/', $word)){
			if (preg_match("/(a|e|и|о|у|ъ)$/", substr($word, 0, -2))) { //  word ends with vowal +  я
				$word = preg_replace('/(я)$/', 'й', $word);
			} else {
				$word = preg_replace('/(я)$/', '', $word);
			}
		}
		return $word;
	}

	private function Palatalization($word) {
		$word = preg_replace('/(ц|ч)$/', 'к', $word);
		$word = preg_replace('/(з|ж)$/', 'г', $word);
		$word = preg_replace('/(с|ш)$/', 'х', $word);
		return $word;
	}

}
?>