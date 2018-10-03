<?php

######
# SMS-varsling av Ambassadører
# For å sjekke at de fortsatt vil være ambassadør for UKM.
# Skriptet samler inn alle ambassadører fra databasen som ikke er deleted=true, og lager en liste man kan sende SMS til.
# Når SMS blir sendt, markeres alle mobilnummere som meldingen sendes til som deleted. 
# Hvis de svarer på SMSen eller åpner lenken, blir de registrert som deleted = false igjen.
######

$infos = array();
$infos['telefonliste'] = getTelefonliste();

/**
 * TEST-modus:
 * Bør kjøres minst èn gang i testmodus, for å sjekke at alt funker.
 *
 * Sender kun meldinga til ett nummer gitt i $_POST['testNummer'];
 */
if($_POST['testUpdate'] == "true") {
	$updated = doUpdate( array($_POST['testNummer']) );
	$infos['oppdaterteNummer'] = $updated;
}
elseif($_POST['doUpdate'] == "true") {
	$updated = doUpdate($infos['telefonliste']);
	$infos['oppdaterteNummer'] = $updated;
}

function doUpdate($telefonliste) {
	$oppdaterteNummer = 0;

	foreach($telefonliste as $nummer) {
		// Valider telefonnummeret
		if( !is_numeric($nummer) ) {
			continue;
		}

		// Sett opp melding
		$ambLink = "http://ambassador.ukm.no/fortsett/".$nummer;
		$text = date('Y')." er snart over, og vi vil si tusen takk for jobben du har gjort som UKM-ambassadør i år! Vil du være med som ambassadør i et år til? Send UKM Hurra til 1963, eller trykk på denne lenken: ".$ambLink;
		$sms = new SMS( 'UKMambassador', 0 );	
		$sms->text($text)->to($nummer)->from('UKMNorge');
		
		// Sett som deaktivert i databasen
		$sql = new SQLins("ukm_ambassador", array('amb_phone' => $nummer));
		$sql->add('deleted', 'true');
		$sql->run();
		
		// Faktisk send melding, men kun hvis vi er i prod.
		if ( 'ukm.no' == UKM_HOSTNAME ) {
			$sms->ok();
			$oppdaterteNummer++;
		}
	}

	return $oppdaterteNummer;
}

/**
 * Bygg mottakerliste
 */
function getTelefonliste() {
	$qry = new SQL("SELECT * FROM `ukm_ambassador` WHERE `deleted` = 'false'");

	$res = $qry->run();

	$telefonliste = array();
	while( $row = SQL::fetch($res) ) {
		$telefonliste[] = $row['amb_phone'];
	}

	return $telefonliste;
}