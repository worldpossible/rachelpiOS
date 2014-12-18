<?php

/* o------------------------------------------------------------------------------o
 *
 *  This script was originally written by  G.Ntais, first php port ny P.Kyriakidis
 *   Copyright (c) 2008 Panos Kyriakakis
 *   as a result of Martin Porter's stemming algorithm for Greek language.
 *
 *  Adapted for Sphider-plus application by Rolf Kellner [Tec] Feb. 2010
 *
 * o------------------------------------------------------------------------------o */

class el_stemmer{
	private $step1list;
	private $step1regexp;
	private $v;
	private $v2;

	public function __construct() {

		$this->step1list = array();
		$this->step1list['φαγια']='φα';
		$this->step1list['φαγιου']='φα';
		$this->step1list['φαγιων']='φα';
		$this->step1list['σκαγια']='σκα';
		$this->step1list['σκαγιου']='σκα';
		$this->step1list['σκαγιων']='σκα';
		$this->step1list['ολογιου']='ολο';
		$this->step1list['ολογια']='ολο';
		$this->step1list['ολογιων']='ολο';
		$this->step1list['σογιου']='σο';
		$this->step1list['σογια']='σο';
		$this->step1list['σογιων']='σο';
		$this->step1list['τατογια']='τατο';
		$this->step1list['τατογιου']='τατο';
		$this->step1list['τατογιων']='τατο';
		$this->step1list['κρεασ']='κρε';
		$this->step1list['κρεατοσ']='κρε';
		$this->step1list['κρεατα']='κρε';
		$this->step1list['κρεατων']='κρε';
		$this->step1list['περασ']='περ';
		$this->step1list['περατοσ']='περ';
		$this->step1list['περατα']='περ';
		$this->step1list['περατων']='περ';
		$this->step1list['τερασ']='τερ';
		$this->step1list['τερατοσ']='τερ';
		$this->step1list['τερατα']='τερ';
		$this->step1list['τερατων']='τερ';
		$this->step1list['φωσ']='φω';
		$this->step1list['φωτοσ']='φω';
		$this->step1list['φωτα']='φω';
		$this->step1list['φωτων']='φω';
		$this->step1list['καθεστωσ']='καθεστ';
		$this->step1list['καθεστωτοσ']='καθεστ';
		$this->step1list['καθεστωτα']='καθεστ';
		$this->step1list['καθεστωτων']='καθεστ';
		$this->step1list['γεγονοσ']='γεγον';
		$this->step1list['γεγονοτοσ']='γεγον';
		$this->step1list['γεγονοτα']='γεγον';
		$this->step1list['γεγονοτων']='γεγον';
		$this->step1regexp = '/(.*)('.implode('|',array_keys($this->step1list)).')$/u';

		$this->v = '[αεηιουω]';	// vowel
		$this->v2 = '[αεηιοω]'; //vowel without y
	}

	public function stem($word) {

		$stem='';
		$suffix='';
		$firstch='';

		$test1 = true;

		if( mb_strlen($word, 'utf-8') < 4 ) {
			return( $word );
		}

		//Step1
		if( preg_match($this->step1regexp,$word,$fp) ) {
			$stem = $fp[1];
			$suffix = $fp[2];
			$word = $stem . $this->step1list[$suffix];
			$test1 = false;
		}

		// Step 2a
		$re = '/^(.+?)(αδεσ|αδων)$/u';
		if( preg_match($re,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$re = '/(οκ|μαμ|μαν|μπαμπ|πατερ|γιαγι|νταντ|κυρ|θει|πεθερ)$/u';
			if( !preg_match($re,$word) ) {
				$word = $word . "αδ";
			}
		}

		//step 2b
		$re = '/^(.+?)(εδεσ|εδων)$/u';
		if( preg_match($re,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$exept2 = '/(οπ|ιπ|εμπ|υπ|γηπ|δαπ|κρασπ|μιλ)$/u';
			if( preg_match($exept2,$word) ) {
				$word = $word . 'εδ';
			}
		}

		//step 2c
		$re = '/^(.+?)(ουδεσ|ουδων)$/u';
		if( preg_match($re,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;

			$exept3 = '/(αρκ|καλιακ|πεταλ|λιχ|πλεξ|σκ|σ|φλ|φρ|βελ|λουλ|χν|σπ|τραγ|φε)$/u';
			if( preg_match($exept3,$word) ) {
				$word = $word . 'ουδ';
			}
		}

		//step 2d
		$re = '/^(.+?)(εωσ|εων)$/u';
		if( preg_match($re,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$test1 = false;

			$exept4 = '/^(θ|δ|ελ|γαλ|ν|π|ιδ|παρ)$/u';
			if( preg_match($exept4,$word) ) {
				$word = $word . 'ε';
			}
		}

		//step 3
		$re = '/^(.+?)(ια|ιου|ιων)$/u';
		if( preg_match($re,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;

			$re = '/'.$this->v.'$/u';
			$test1 = false;
			if( preg_match($re,$word) ) {
				$word = $stem . 'ι';
			}
		}

		//step 4
		$re = '/^(.+?)(ικα|ικο|ικου|ικων)$/u';
		if( preg_match($re,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;

			$test1 = false;
			$re = '/'.$this->v.'$/u';
			$exept5 = '/^(αλ|αδ|ενδ|αμαν|αμμοχαλ|ηθ|ανηθ|αντιδ|φυσ|βρωμ|γερ|εξωδ|καλπ|καλλιν|καταδ|μουλ|μπαν|μπαγιατ|μπολ|μποσ|νιτ|ξικ|συνομηλ|πετσ|πιτσ|πικαντ|πλιατσ|ποστελν|πρωτοδ|σερτ|συναδ|τσαμ|υποδ|φιλον|φυλοδ|χασ)$/u';
			if( preg_match($re,$word) || preg_match($exept5,$word) ) {
				$word = $word . 'ικ';
			}
		}

		//step 5a
		$re = '/^(.+?)(αμε)$/u';
		$re2 = '/^(.+?)(αγαμε|ησαμε|ουσαμε|ηκαμε|ηθηκαμε)$/u';
		if ($word == "αγαμε") {
			$word = "αγαμ";
		}

		if( preg_match($re2,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$test1 = false;
		}

		if( preg_match($re,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$test1 = false;

			$exept6 = '/^(αναπ|αποθ|αποκ|αποστ|βουβ|ξεθ|ουλ|πεθ|πικρ|ποτ|σιχ|χ)$/u';
			if( preg_match($exept6,$word) ) {
				$word = $word . "αμ";
			}
		}

		//step 5b
		$re2 = '/^(.+?)(ανε)$/u';
		$re3 = '/^(.+?)(αγανε|ησανε|ουσανε|ιοντανε|ιοτανε|ιουντανε|οντανε|οτανε|ουντανε|ηκανε|ηθηκανε)$/u';

		if( preg_match($re3,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$test1 = false;

			$re3 = '/^(τρ|τσ)$/u';
			if( preg_match($re3,$word) ) {
				$word = $word .  "αγαν";
			}
		}

		if( preg_match($re2,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$test1 = false;

			$re2 = '/'.$this->v2.'$/u';
			$exept7 = '/^(βετερ|βουλκ|βραχμ|γ|δραδουμ|θ|καλπουζ|καστελ|κορμορ|λαοπλ|μωαμεθ|μ|μουσουλμ|ν|ουλ|π|πελεκ|πλ|πολισ|πορτολ|σαρακατσ|σουλτ|τσαρλατ|ορφ|τσιγγ|τσοπ|φωτοστεφ|χ|ψυχοπλ|αγ|ορφ|γαλ|γερ|δεκ|διπλ|αμερικαν|ουρ|πιθ|πουριτ|σ|ζωντ|ικ|καστ|κοπ|λιχ|λουθηρ|μαιντ|μελ|σιγ|σπ|στεγ|τραγ|τσαγ|φ|ερ|αδαπ|αθιγγ|αμηχ|ανικ|ανοργ|απηγ|απιθ|ατσιγγ|βασ|βασκ|βαθυγαλ|βιομηχ|βραχυκ|διατ|διαφ|ενοργ|θυσ|καπνοβιομηχ|καταγαλ|κλιβ|κοιλαρφ|λιβ|μεγλοβιομηχ|μικροβιομηχ|νταβ|ξηροκλιβ|ολιγοδαμ|ολογαλ|πενταρφ|περηφ|περιτρ|πλατ|πολυδαπ|πολυμηχ|στεφ|ταβ|τετ|υπερηφ|υποκοπ|χαμηλοδαπ|ψηλοταβ)$/u';
			if( preg_match($re2,$word) || preg_match($exept7,$word) ){
				$word = $word .  "αν";
			}
		}

		//step 5c
		$re3 = '/^(.+?)(ετε)$/u';
		$re4 = '/^(.+?)(ησετε)$/u';

		if( preg_match($re4,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$test1 = false;
		}

		if( preg_match($re3,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$test1 = false;

			$re3 = $this->v2.'$';
			$exept8 =  '/(οδ|αιρ|φορ|ταθ|διαθ|σχ|ενδ|ευρ|τιθ|υπερθ|ραθ|ενθ|ροθ|σθ|πυρ|αιν|συνδ|συν|συνθ|χωρ|πον|βρ|καθ|ευθ|εκθ|νετ|ρον|αρκ|βαρ|βολ|ωφελ)$/u';
			$exept9 = '/^(αβαρ|βεν|εναρ|αβρ|αδ|αθ|αν|απλ|βαρον|ντρ|σκ|κοπ|μπορ|νιφ|παγ|παρακαλ|σερπ|σκελ|συρφ|τοκ|υ|δ|εμ|θαρρ|θ)$/u';

			if( preg_match($re3,$word) || preg_match($exept8,$word) || preg_match($exept9,$word) ){
				$word = $word .  "ετ";
			}
		}

		//step 5d
		$re = '/^(.+?)(οντασ|ωντασ)$/u';
		if( preg_match($re,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$test1 = false;

			$exept10 = '/^(αρχ)$/u';
			$exept11 = '/(κρε)$/u';
			if( preg_match($exept10,$word) ){
				$word = $word . "οντ";
			}
			if( preg_match($exept11,$word) ){
				$word = $word . "ωντ";
			}
		}

		//step 5e
		$re = '/^(.+?)(ομαστε|ιομαστε)$/u';
		if( preg_match($re,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$test1 = false;

			$exept11 = '/^(ον)$/u';
			if( preg_match($exept11,$word) ){
				$word = $word .  "ομαστ";
			}
		}

		//step 5f
		$re = '/^(.+?)(εστε)$/u';
		$re2 = '/^(.+?)(ιεστε)$/u';

		if( preg_match($re2,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$test1 = false;

			$re2 = '/^(π|απ|συμπ|ασυμπ|ακαταπ|αμεταμφ)$/u';
			if( preg_match($re2,$word) ) {
				$word = $word . "ιεστ";
			}
		}

		if( preg_match($re,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$test1 = false;

			$exept12 = '/^(αλ|αρ|εκτελ|ζ|μ|ξ|παρακαλ|αρ|προ|νισ)$/u';
			if( preg_match($exept12,$word) ){
				$word = $word . "εστ";
			}
		}

		//step 5g
		$re = '/^(.+?)(ηκα|ηκεσ|ηκε)$/u';
		$re2 = '/^(.+?)(ηθηκα|ηθηκεσ|ηθηκε)$/u';

		if( preg_match($re2,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$test1 = false;
		}

		if( preg_match($re,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$test1 = false;

			$exept13 = '/(σκωλ|σκουλ|ναρθ|σφ|οθ|πιθ)$/u';
			$exept14 = '/^(διαθ|θ|παρακαταθ|προσθ|συνθ|)$/u';
			if( preg_match($exept13,$word) || preg_match($exept14,$word) ){
				$word = $word . "ηκ";
			}
		}


		//step 5h
		$re = '/^(.+?)(ουσα|ουσεσ|ουσε)$/u';
		if( preg_match($re,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$test1 = false;

			$exept15 = '/^(φαρμακ|χαδ|αγκ|αναρρ|βρομ|εκλιπ|λαμπιδ|λεχ|μ|πατ|ρ|λ|μεδ|μεσαζ|υποτειν|αμ|αιθ|ανηκ|δεσποζ|ενδιαφερ|δε|δευτερευ|καθαρευ|πλε|τσα)$/u';
			$exept16 = '/(ποδαρ|βλεπ|πανταχ|φρυδ|μαντιλ|μαλλ|κυματ|λαχ|ληγ|φαγ|ομ|πρωτ)$/u';
			if( preg_match($exept15,$word) || preg_match($exept16,$word) ){
				$word = $word . "ουσ";
			}
		}

		//step 5i
		$re = '/^(.+?)(αγα|αγεσ|αγε)$/u';

		if( preg_match($re,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$test1 = false;

			$exept17 = '/^(ψοφ|ναυλοχ)$/u';
			$exept20 = '/(κολλ)$/u';
			$exept18 = '/^(αβαστ|πολυφ|αδηφ|παμφ|ρ|ασπ|αφ|αμαλ|αμαλλι|ανυστ|απερ|ασπαρ|αχαρ|δερβεν|δροσοπ|ξεφ|νεοπ|νομοτ|ολοπ|ομοτ|προστ|προσωποπ|συμπ|συντ|τ|υποτ|χαρ|αειπ|αιμοστ|ανυπ|αποτ|αρτιπ|διατ|εν|επιτ|κροκαλοπ|σιδηροπ|λ|ναυ|ουλαμ|ουρ|π|τρ|μ)$/u';
			$exept19 = '/(οφ|πελ|χορτ|λλ|σφ|ρπ|φρ|πρ|λοχ|σμην)$/u';

			if( (preg_match($exept18,$word) || preg_match($exept19,$word))
			&& !(preg_match($exept17,$word) || preg_match($exept20,$word)) ) {
				$word = $word . "αγ";
			}
		}


		//step 5j
		$re = '/^(.+?)(ησε|ησου|ησα)$/u';
		if( preg_match($re,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$test1 = false;

			$exept21 = '/^(ν|χερσον|δωδεκαν|ερημον|μεγαλον|επταν)$/u';
			if( preg_match($exept21,$word) ){
				$word = $word . "ησ";
			}
		}

		//step 5k
		$re = '/^(.+?)(ηστε)$/u';
		if( preg_match($re,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$test1 = false;

			$exept22 = '/^(ασβ|σβ|αχρ|χρ|απλ|αειμν|δυσχρ|ευχρ|κοινοχρ|παλιμψ)$/u';
			if( preg_match($exept22,$word) ){
				$word = $word . "ηστ";
			}
		}

		//step 5l
		$re = '/^(.+?)(ουνε|ησουνε|ηθουνε)$/u';
		if( preg_match($re,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$test1 = false;

			$exept23 = '/^(ν|ρ|σπι|στραβομουτσ|κακομουτσ|εξων)$/u';
			if( preg_match($exept23,$word) ){
				$word = $word . "ουν";
			}
		}

		//step 5l
		$re = '/^(.+?)(ουμε|ησουμε|ηθουμε)$/u';
		if( preg_match($re,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem;
			$test1 = false;

			$exept24 = '/^(παρασουσ|φ|χ|ωριοπλ|αζ|αλλοσουσ|ασουσ)$/u';
			if( preg_match($exept24,$word) ){
				$word = $word . "ουμ";
			}
		}

		// step 6
		$re = '/^(.+?)(ματα|ματων|ματοσ)$/u';
		$re2 = '/^(.+?)(α|αγατε|αγαν|αει|αμαι|αν|ασ|ασαι|αται|αω|ε|ει|εισ|ειτε|εσαι|εσ|εται|ι|ιεμαι|ιεμαστε|ιεται|ιεσαι|ιεσαστε|ιομασταν|ιομουν|ιομουνα|ιονταν|ιοντουσαν|ιοσασταν|ιοσαστε|ιοσουν|ιοσουνα|ιοταν|ιουμα|ιουμαστε|ιουνται|ιουνταν|η|ηδεσ|ηδων|ηθει|ηθεισ|ηθειτε|ηθηκατε|ηθηκαν|ηθουν|ηθω|ηκατε|ηκαν|ησ|ησαν|ησατε|ησει|ησεσ|ησουν|ησω|ο|οι|ομαι|ομασταν|ομουν|ομουνα|ονται|ονταν|οντουσαν|οσ|οσασταν|οσαστε|οσουν|οσουνα|οταν|ου|ουμαι|ουμαστε|ουν|ουνται|ουνταν|ουσ|ουσαν|ουσατε|υ|υσ|ω|ων)$/u';
		if( preg_match($re,$word,$fp) ) {
			$stem = $fp[1];
			$word = $stem . "μα";
		}

		if( preg_match($re2,$word,$fp) && $test1 ) {
			$stem = $fp[1];
			$word = $stem;
		}

		// step 7 (παραθετικα)
		$re = '/^(.+?)(εστερ|εστατ|οτερ|οτατ|υτερ|υτατ|ωτερ|ωτατ)$/u';
		if( preg_match($re,$word,$fp) ){
			$stem = $fp[1];
			$word = $stem;
		}

		return( $word );
	}
}

?>