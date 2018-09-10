<?php
require_once('amb_norge_controller_info.inc.php');

if(isset($_GET['generate'])) {
	global $objPHPExcel;
	require_once('UKM/inc/excel.inc.php');
	$objPHPExcel = new PHPExcel();
	
	exorientation('portrait');
	
	$objPHPExcel->getProperties()->setCreator('UKM Norges arrangørsystem');
	$objPHPExcel->getProperties()->setLastModifiedBy('UKM Norges arrangørsystem');
	$objPHPExcel->getProperties()->setTitle('UKM-Ambassadører');
	$objPHPExcel->getProperties()->setKeywords('UKM-Ambassadører');
	
	## Sett standard-stil
	$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri');
	$objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
	
	####################################################################################
	## OPPRETTER ARK
	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->setActiveSheetIndex(0)->getTabColor()->setRGB('A0CF67');
	
	exSheetName('Ark1');
	
	// HEADERS
	$row = 1;
	exCell('A'.$row, 'ID');
	exCell('B'.$row, 'Fornavn');
	exCell('C'.$row, 'Etternavn');
	exCell('D'.$row, 'Størrelse');
	exCell('E'.$row, 'Adresse');
	exCell('F'.$row, 'Postnummer');
	exCell('G'.$row, 'Poststed');
	exCell('H'.$row, 'Mobil');
	exCell('I'.$row, 'E-post');


	$sql = new SQL("SELECT *
					FROM `ukm_ambassador` AS `amb`
					JOIN `ukm_ambassador_skjorte` AS `skjorte`
						ON (`amb`.`amb_id` = `skjorte`.`amb_id`)
					WHERE `skjorte`.`sendt` = 'false'");
	$venter = $sql->run();
	
	while( $r = SQL::fetch( $venter ) ) {
		$row++;
		$amb = new ambassador( $r['amb_faceID'] );
		
		exCell('A'.$row, $amb->ID);
		exCell('B'.$row, $amb->firstname);
		exCell('C'.$row, $amb->lastname);
		exCell('D'.$row, $amb->size);
		exCell('E'.$row, $amb->adresse);
		exCell('F'.$row, $amb->postnr);
		exCell('G'.$row, $amb->poststed);
		exCell('H'.$row, $amb->phone);
		exCell('I'.$row, $amb->email);
		
		$sqlUpdate = new SQLins('ukm_ambassador_skjorte', array('amb_id' => $r['amb_ID']));
		$sqlUpdate->add('sendt', 'true');
		$sqlUpdate->run();
	}
	
	
	// WRITE
	$excel = new StdClass;
	$excel->link = exWrite($objPHPExcel,'UKM_Ambassadorer_alle_'.date('Y-m-d_His'));
	$excel->created = time();
	
	$infos['excel'] = $excel;
	
}

?>