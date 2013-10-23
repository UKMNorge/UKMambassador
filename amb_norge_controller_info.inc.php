<?php
	$sql = new SQL("SELECT COUNT(`amb_id`) AS `num`
					FROM `ukm_ambassador`");
	$ant_amb = $sql->run('field', 'num');
	
	$sql = new SQL("SELECT COUNT(`amb`.`amb_id`) AS `num`
					FROM `ukm_ambassador` AS `amb`
					JOIN `ukm_ambassador_skjorte` AS `skjorte`
						ON (`amb`.`amb_id` = `skjorte`.`amb_id`)
					WHERE `skjorte`.`sendt` = 'false'");
	$ant_venter = $sql->run('field', 'num');
	
	$fylker = new SQL("SELECT `id`
					   FROM `smartukm_fylke`");
	$fylker = $fylker->run();
	$fylkedata = array();
	while( $r = mysql_fetch_assoc( $fylker ) ) {
		$fylke_monstring = new fylke_monstring( $r['id'], get_option('season') );
		$fylke_monstring = $fylke_monstring->monstring_get();
		
		$fylkedata[] = array('navn' => $fylke_monstring->get('pl_name'),
							 'link' => $fylke_monstring->get('link'),
							 'ambassadorer' => $fylke_monstring->ambassadorer()
						  );
	}
	
	
	$infos = array('ant_ambassadorer' => $ant_amb,
			   'venter_pakke' => $ant_venter,
			   'fylker' => $fylkedata
			   );
