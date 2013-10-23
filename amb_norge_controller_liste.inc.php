<?php
require_once('amb_norge_controller_info.inc.php');




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
exCell('D'.$row, 'Mobil');
exCell('E'.$row, 'Kjønn');

exCell('F'.$row, '');

exCell('G'.$row, 'Mottatt pakke?');
exCell('H'.$row, 'Størrelse');
exCell('I'.$row, 'Adresse');
exCell('J'.$row, 'Postnummer');
exCell('K'.$row, 'Poststed');

exCell('L'.$row, '');

exCell('M'.$row, 'Født');
exCell('N'.$row, 'E-post');

exCell('O'.$row, '');

exCell('P'.$row, 'Mønstring');
exCell('Q'.$row, 'Sesong');
exCell('R'.$row, 'Fylke');

require_once('UKM/monstring.class.php');
// DATA
foreach( $infos['fylker'] as $fylke ) {
	foreach( $fylke['ambassadorer'] as $amb) {
		$row++;

		$gender = $amb->gender == 'male' ? 'Gutt' : 'Jente';
		$monstring = new monstring( $amb->pl_id );
		
		exCell('A'.$row, $amb->id);
		exCell('B'.$row, $amb->firstname);
		exCell('C'.$row, $amb->lastname);
		exCell('D'.$row, $amb->phone);
		exCell('E'.$row, $gender);
		
		exCell('F'.$row, '');
		
		exCell('G'.$row, $amb->sendt ? 'JA' : 'NEI');
		exCell('H'.$row, $amb->size);
		exCell('I'.$row, $amb->adresse);
		exCell('J'.$row, $amb->postnr);
		exCell('K'.$row, $amb->poststed);
		
		exCell('L'.$row, '');
		
		exCell('M'.$row, $amb->birthday);
		exCell('N'.$row, $amb->email);
		
		exCell('O'.$row, '');
		
		exCell('P'.$row, $monstring->get('pl_name'));
		exCell('Q'.$row, $monstring->get('season'));
		exCell('R'.$row, $fylke->navn);
	}
}

// WRITE
$excel = new StdClass;
$excel->link = exWrite($objPHPExcel,'UKM_Ambassadorer_alle_'.date('Y-m-d_His'));
$excel->created = time();

$infos['excel'] = $excel;