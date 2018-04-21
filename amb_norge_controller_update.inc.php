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


if($_POST['doUpdate'] == "true") {
	$updated = doUpdate($infos['telefonliste']);
	#die("Ikke implementert enda!");
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
		$ambLink = "https://ambassador.ukm.no/fortsett/".$nummer;
		$text = "Hei! Tusen takk for den jobben du har gjort som UKM-ambassadør i år. Vil du være med som ambassadør i et år til? Svar UKM Hurra på denne meldingen, eller trykk på denne lenken: ".$ambLink;
		$sms = new SMS( 'UKMambassador', get_option('pl_id') );	
		$sms->text($text)->to($nummer)->from('UKMNorge');

		// TODO: Fjern DIE og fjern utkommentert kode.
		die("Sending av telefonnummer er ikke implementert enda! Før første kjøring må denne .");
		
		// Sett som deaktivert i databasen
		$sql = new SQLins("ukm_ambassador", array('amb_phone' => $nummer));
		$sql->add('deleted', 'true');
		
		#$sql->run();
		
		// Faktisk send melding, men kun hvis vi er i prod.
		if ( UKM_HOSTNAME == 'ukm.no' ) {
			#$sms->ok();
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
	while( $row = mysql_fetch_assoc($res) ) {
		$telefonliste[] = $row['amb_phone'];
	}

	return $telefonliste;
}