<?php

/* o------------------------------------------------------------------------------o
 *
 *  This script was originally written in PEARL by  Ljiljana Dolamic and Jacques Savoy
 *
 *  Improvements, PHP5 implementation and adapted for Sphider-plus application
 *   by Rolf Kellner [Tec] Feb. 2010
 *
 * o------------------------------------------------------------------------------o */

define('cz_case1', '/(atech|ětem|etem|atům|ech|ich|ích|ého|ěmi|emi|ému|ěte|ete|ěti|eti)$/');
define('cz_case2', '/(ího|iho|ími|ímu|imu|ách|ata|aty|ých|ama|ami|ové|ovi|ými|em|es|ém)$/');
define('cz_case3', '/(ím|ům|at|ám|os|us|ým|mi|ou)$/');
define('cz_case4', '/(a|e|i|o|u|y|á|é|í|ý|ě)$/');

class cz_stemmer{

	public function stem($word) {
		//$word = lower_case($word);
		$word = self::Remove_Case($word);
		$word = self::Remove_Possessives($word);
		$word = self::Normalize($word);
		return $word;
	}

	private function Remove_Case($word) {
		$word1 = preg_replace(cz_case1, '', $word);
		if ($word1 != $word) return $word1;
		$word1 = preg_replace(cz_case2, '', $word);
		if ($word1 != $word) return $word1;
		$word1 = preg_replace(cz_case3, '', $word);
		if ($word1 != $word) return $word1;
		$word = preg_replace(cz_case4, '', $word);
		return $word;
	}

	private function Remove_Possessives($word) {
		$word = preg_replace('/(ov|in|ův)$/', '', $word);
		return $word;
	}

	private function Normalize($word) {
		$word = preg_replace('/(čt)$/', 'ck', $word);
		$word = preg_replace('/(št)$/', 'sk', $word);
		$word = preg_replace('/(c|č)$/', 'k', $word);
		$word = preg_replace('/(z|ž)$/', 'h', $word);
		$word = preg_replace('/(\.ů\.)$/', '.o.', $word);
		return $word;
	}

}
?>