<?php

/* o------------------------------------------------------------------------------o
 *
 *  This script was originally written by Roberto Mirizzi  in February 2007.
 *
 *  PHP5 implementation of Martin Porter's stemming algorithm for Italian language.
 *  Adapted for Sphider-plus application by Rolf Kellner [Tec] Feb. 2010
 *
 * o------------------------------------------------------------------------------o */

class it_stemmer {
	private $vocali = array('a','e','i','o','u','�','�','�','�','�');
	private $consonanti = array('b','c','d','f','g','h','j','k','l','m','n','p','q','r','s','t','v','w','x','y','z','I','U');
	private $accenti_acuti = array('�','�','�','�','�');
	private $accenti_gravi = array('�','�','�','�','�');

	private $suffissi_step_0 = array('ci','gli','la','le','li','lo','mi','ne','si','ti','vi','sene','gliela','gliele','glieli','glielo','gliene','mela','mele','meli','melo','mene','tela','tele','teli','telo','tene','cela','cele','celi','celo','cene','vela','vele','veli','velo','vene');

	private $suffissi_step_1_a = array('anza','anze','ico','ici','ica','ice','iche','ichi','ismo','ismi','abile','abili','ibile','ibili','ista','iste','isti','ist�','ist�','ist�','oso','osi','osa','ose','mente','atrice','atrici','ante','anti');
	private $suffissi_step_1_b = array('azione','azioni','atore','atori');
	private $suffissi_step_1_c = array('logia','logie');
	private $suffissi_step_1_d = array('uzione','uzioni','usione','usioni');
	private $suffissi_step_1_e = array('enza','enze');
	private $suffissi_step_1_f = array('amento','amenti','imento','imenti');
	private $suffissi_step_1_g = array('amente');
	private $suffissi_step_1_h = array('it�');
	private $suffissi_step_1_i = array('ivo','ivi','iva','ive');

	private $suffissi_step_2 = array('ammo','ando','ano','are','arono','asse','assero','assi','assimo','ata','ate','ati','ato','ava','avamo','avano','avate','avi','avo','emmo','enda','ende','endi','endo','er�','erai','eranno','ere','erebbe','erebbero','erei','eremmo','eremo','ereste','eresti','erete','er�','erono','essero','ete','eva','evamo','evano','evate','evi','evo','Yamo','iamo','immo','ir�','irai','iranno','ire','irebbe','irebbero','irei','iremmo','iremo','ireste','iresti','irete','ir�','irono','isca','iscano','isce','isci','isco','iscono','issero','ita','ite','iti','ito','iva','ivamo','ivano','ivate','ivi','ivo','ono','uta','ute','uti','uto','ar','ir');

	private $ante_suff_a = array('ando','endo');
	private $ante_suff_b = array('ar','er','ir');

	public function __construct() {
		usort($this->suffissi_step_0,create_function('$a,$b','return strlen($a)>strlen($b) ? -1 : 1;'));
		usort($this->suffissi_step_1_a,create_function('$a,$b','return strlen($a)>strlen($b) ? -1 : 1;'));
		usort($this->suffissi_step_2,create_function('$a,$b','return strlen($a)>strlen($b) ? -1 : 1;'));
	}

	private function trim($str) {
		return trim($str);
	}

	private function to_lower($str) {
		return lower_case($str);
	}

	private function replace_acc_acuti($str) {
		return str_replace($this->accenti_acuti, $this->accenti_gravi, $str); //strtr
	}

	private function put_u_after_q_to_upper($str) {
		return str_replace("qu", "qU", $str);
	}

	private function i_u_between_vow_to_upper($str) {
		$pattern = '/([aeiou�����])([iu])([aeiou�����])/e';
		$replacement = "'$1'.strtoupper('$2').'$3'";
		return preg_replace($pattern, $replacement, $str);
	}

	private function return_RV($str) {
		/*
		 If the second letter is a consonant, RV is the region after the next following vowel,
		 or if the first two letters are vowels, RV is the region after the next consonant, and otherwise
		 (consonant-vowel case) RV is the region after the third letter. But RV is the end of the word if these positions cannot be found.
		 example,
		 m a c h o [ho]     o l i v a [va]     t r a b a j o [bajo]     � u r e o [eo] prezzo sprezzante
		 */

		if(strlen($str)<2) return '';//$str;

		if(in_array($str[1],$this->consonanti)) {
			$str = substr($str,2);
			$str = strpbrk($str, implode($this->vocali));
			return substr($str,1); //secondo me devo mettere 1
		}
		else if(in_array($str[0],$this->vocali) && in_array($str[1],$this->vocali)) {
			$str = strpbrk($str, implode($this->consonanti));
			return substr($str,1);
		}
		else if(in_array($str[0],$this->consonanti) && in_array($str[1],$this->vocali)) {
			return substr($str,3);
		}

	}

	private function return_R1($str){
		/*
		 R1 is the region after the first non-vowel following a vowel, or is the null region at the end of the word if there is no such non-vowel.
		 example:
		 beautiful [iful]	beauty [y]	beau [NULL]	animadversion [imadversion]	sprinkled [kled]	eucharist [harist]
		 */

		$pattern = '/['.implode($this->vocali).']+'.'['.implode($this->consonanti).']'.'(.*)/';
		preg_match($pattern,$str,$matches);

		return count($matches)>=1 ? $matches[1] : '';
	}

	private function return_R2($str) {
		/*
		 R2 is the region after the first non-vowel following a vowel in R1, or is the null region at the end of the word if there is no such non-vowel.
		 example:
		 beautiful [ul]	beauty [NULL]	beau [NULL]	animadversion [adversion]	sprinkled [NULL]	eucharist [ist]
		 */

		$R1 = $this->return_R1($str);

		$pattern = '/['.implode($this->vocali).']+'.'['.implode($this->consonanti).']'.'(.*)/';
		preg_match($pattern,$R1,$matches);

		return count($matches)>=1 ? $matches[1] : '';
	}

	private function step_0($str) {
		//Step 0: Attached pronoun
		//Always do steps 0

		$str_len = strlen($str);
		$rv = $this->return_RV($str);
		$rv_len = strlen($rv);

		$pos = 0;
		foreach($this->suffissi_step_0 as $suff) {
			if($rv_len-strlen($suff) < 0) continue;
			$pos = strpos($rv,$suff,$rv_len-strlen($suff));
			if($pos !== false) break;
		}

		$ante_suff = substr($rv,0,$pos);
		$ante_suff_len = strlen($ante_suff);

		foreach($this->ante_suff_a as $ante_a) {
			if($ante_suff_len-strlen($ante_a) < 0) continue;
			$pos_a = strpos($ante_suff,$ante_a,$ante_suff_len-strlen($ante_a));
			if($pos_a !== false) {
				return substr($str,0,$pos+$str_len-$rv_len);
			}
		}

		foreach($this->ante_suff_b as $ante_b) {
			if($ante_suff_len-strlen($ante_b) < 0) continue;
			$pos_b = strpos($ante_suff,$ante_b,$ante_suff_len-strlen($ante_b));
			if($pos_b !== false) {
				return substr($str,0,$pos+$str_len-$rv_len).'e';
			}
		}

		return $str;
	}

	private function delete_suff($arr_suff,$str,$str_len,$where,$ovunque=false) {
		if($where==='r2') $r = $this->return_R2($str);
		else if($where==='rv') $r = $this->return_RV($str);
		else if($where==='r1') $r = $this->return_R1($str);

		$r_len = strlen($r);

		if($ovunque) {
			foreach($arr_suff as $suff) {
				if($str_len-strlen($suff) < 0) continue;
				$pos = strpos($str,$suff,$str_len-strlen($suff));
				if($pos !== false) {
					$pattern = '/'.$suff.'$/';
					$ret_str = preg_match($pattern,$r) ? substr($str,0,$pos) : '';
					if($ret_str !== '') return $ret_str;
					break;
				}
			}
		}
		else {
			foreach($arr_suff as $suff) {
				if($r_len-strlen($suff) < 0) continue;
				$pos = strpos($r,$suff,$r_len-strlen($suff));
				if($pos !== false) return substr($str,0,$pos+$str_len-$r_len);
			}
		}
	}

	private function step_1($str) {
		//Step 1: Standard suffix removal
		//Always do steps 1

		$str_len = strlen($str);

		//delete if in R1,     if preceded by 'iv', delete if in R2 (and if further preceded by 'at', delete if in R2), otherwise, if preceded by 'os', 'ic' or 'abil', delete if in R2
		if(count($ret_str = $this->delete_suff($this->suffissi_step_1_g,$str,$str_len,'r1'))) {
			if(count($ret_str1 = $this->delete_suff(array('iv'),$ret_str,strlen($ret_str),'r2'))) {
				if(count($ret_str2 = $this->delete_suff(array('at'),$ret_str1,strlen($ret_str1),'r2'))) return $ret_str2;
				else return $ret_str1;
			}
			else if(count($ret_str1 = $this->delete_suff(array('os','ic','abil'),$ret_str,strlen($ret_str),'r2'))) {
				return $ret_str1;
			}
			else return $ret_str;
		}
		//delete if in R2
		if(count($ret_str = $this->delete_suff($this->suffissi_step_1_a,$str,$str_len,'r2',true))) return $ret_str;
		//delete if in R2,   if preceded by 'ic', delete if in R2
		if(count($ret_str = $this->delete_suff($this->suffissi_step_1_b,$str,$str_len,'r2'))) {
			if(count($ret_str1 = $this->delete_suff(array('ic'),$ret_str,strlen($ret_str),'r2'))) {
				return $ret_str1;
			}
			else return $ret_str;
		}
		//replace with 'log' if in R2
		if(count($ret_str = $this->delete_suff($this->suffissi_step_1_c,$str,$str_len,'r2'))) return $ret_str.'log';
		//replace with 'u' if in R2
		if(count($ret_str = $this->delete_suff($this->suffissi_step_1_d,$str,$str_len,'r2'))) return $ret_str.'u';
		//replace with 'ente' if in R2
		if(count($ret_str = $this->delete_suff($this->suffissi_step_1_e,$str,$str_len,'r2'))) return $ret_str.'ente';
		//delete if in RV
		if(count($ret_str = $this->delete_suff($this->suffissi_step_1_f,$str,$str_len,'rv'))) return $ret_str;
		//delete if in R2,   if preceded by 'abil', 'ic' or 'iv', delete if in R2
		if(count($ret_str = $this->delete_suff($this->suffissi_step_1_h,$str,$str_len,'r2'))) {
			if(count($ret_str1 = $this->delete_suff(array('abil','ic','iv'),$ret_str,strlen($ret_str),'r2'))) {
				return $ret_str1;
			}
			else return $ret_str;
		}
		//delete if in R2,    if preceded by 'at', delete if in R2 (and if further preceded by 'ic', delete if in R2)
		if(count($ret_str = $this->delete_suff($this->suffissi_step_1_i,$str,$str_len,'r2'))) {
			if(count($ret_str1 = $this->delete_suff(array('at'),$ret_str,strlen($ret_str),'r2'))) {
				if(count($ret_str2 = $this->delete_suff(array('ic'),$ret_str1,strlen($ret_str1),'r2'))) return $ret_str2;
				else return $ret_str1;
			}
			else return $ret_str;
		}

		return $str;
	}

	private function step_2($str,$str_step_1) {
		//Step 2: Verb suffixes
		//Do step 2 if no ending was removed by step 1

		if($str != $str_step_1) return $str_step_1;

		$str_len = strlen($str);

		if(count($ret_str = $this->delete_suff($this->suffissi_step_2,$str,$str_len,'rv'))) return $ret_str;

		return $str;
	}

	private function step_3a($str) {
		//Step 3a: Delete a final 'a', 'e', 'i', 'o',' �', '�', '�' or '�' if it is in RV, and a preceding 'i' if it is in RV ('crocchi' -> 'crocch', 'crocchio' -> 'crocch')
		//Always do steps 3a

		$vocale_finale = array('a','e','i','o','�','�','�','�');

		$str_len = strlen($str);

		if(count($ret_str = $this->delete_suff($vocale_finale,$str,$str_len,'rv'))) {
			if(count($ret_str1 = $this->delete_suff(array('i'),$ret_str,strlen($ret_str),'rv'))) {
				return $ret_str1;
			}
			else return $ret_str;
		}

		return $str;
	}

	private function step_3b($str) {
		//Step 3b: Replace final 'ch' (or 'gh') with 'c' (or 'g') if in 'RV' ('crocch' -> 'crocc')
		//Always do steps 3b

		$rv = $this->return_RV($str);

		$pattern = '/([cg])h$/';
		$replacement = '${1}';
		return substr($str,0,strlen($str)-strlen($rv)).preg_replace($pattern,$replacement,$rv);
	}

	private function step_4($str) {
		//Step 4: Finally, turn I and U back into lower case

		return strtolower($str);
	}

	public function stem($str){
		$str = $this->trim($str);
		//$str = $this->to_lower($str);
		$str = $this->replace_acc_acuti($str);
		$str = $this->put_u_after_q_to_upper($str);
		$str = $this->i_u_between_vow_to_upper($str);
		$step0 = $this->step_0($str);
		$step1 = $this->step_1($step0);
		$step2 = $this->step_2($step0,$step1);
		$step3a = $this->step_3a($step2);
		$step3b = $this->step_3b($step3a);
		$step4 = $this->step_4($step3b);

		return $step4;
	}
}

?>