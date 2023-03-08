<?php
include (DB.'output_pullsheets.db.php');
include (INCLUDES.'db_tables.inc.php');

$style_sequence_map = array(
	1 =>  array("01A", "01B", "01C", "01D"),
	2 =>  array("02A", "03A", "03B"),
	3 =>  array("11A", "11B", "11C", "12A", "12B"),
	4 =>  array("04A", "04B", "04C"),
	5 =>  array("02B", "03C", "06B", "06C", "07A", "07B"),
	6 =>  array("02C", "03D", "08A", "08B"),
	7 =>  array("09A", "09B", "09C"),
	8 =>  array("05A", "05B", "05C", "05D"),
	9 =>  array("10A", "10C"),
	10 => array("14B", "14C", "15A"),
	11 => array("18A", "18B"),
	12 => array("19A", "19B", "19C"),
	13 => array("13A", "13B", "13C"),
	14 => array("15B", "15C", "16A", "16B"),
	15 => array("20A", "20B", "20C"),
	16 => array("21A"),
	17 => array("21B1", "21B", "21B2"),
	18 => array("17B", "17C", "17D", "22B", "22D"),
	19 => array("25A", "25C", "26B", "26C", "26D"),
	20 => array("25B"),
	21 => array("24A", "24B", "24C", "26A"),
	22 => array("28A", "28B", "28C", "28D"),
	23 => array("29A", "29B", "29C", "29D"),
	24 => array("30A", "30C", "30D", "31A", "32A"),
	25 => array("33A", "33B"),
	26 => array("27A3", "27A4", "27A5", "27A6", "27A7", "27A8"),
	27 => array("34A", "34B", "34C"),
	28 => array("23A", "23B", "23D", "23E", "23F", "23G"),
	29 => array("21C"),
	30 => array("22A")
);

$style_table_map = array();
do {
	$a = explode(",", $row_tables['tableStyles']);
	foreach (array_unique($a) as $value) {
		if (array_key_exists($value, $style_table_map)) {
			print "The style id $value is present in multiple tables {$row_tables['tableNumber']} and {$style_table_map[$value]}";
		}
		$style_table_map[$value] = $row_tables['tableNumber'];
	}
} while ($row_tables = mysqli_fetch_assoc($tables));

$query_style_brew = "SELECT $brewing_db_table.id as brewing_id, $styles_db_table.id as styles_id 
FROM $brewing_db_table, $styles_db_table 
WHERE $brewing_db_table.brewCategorySort = $styles_db_table.brewStyleGroup 
AND $brewing_db_table.brewSubCategory = $styles_db_table.brewStyleNum 
AND $styles_db_table.brewStyleVersion = 'BJCP2021'";
$style_brew = mysqli_query($connection, $query_style_brew) or die(mysqli_error($connection));
$style_brew_map = array();
while ($row_style_brew = mysqli_fetch_assoc($style_brew)) {
	$style_brew_map[$row_style_brew['brewing_id']] = $row_style_brew['styles_id'];
}

// entry sequence map
$query_style_id_catsubcat = "SELECT $styles_db_table.id as styles_id,
CONCAT($styles_db_table.brewStyleGroup, $styles_db_table.brewStyleNum) as styles_catsubcat
FROM $styles_db_table 
WHERE $styles_db_table.brewStyleVersion = 'BJCP2021'";
$style_id_catsubcat = mysqli_query($connection, $query_style_id_catsubcat) or die(mysqli_error());
$style_id_catsubcat_map = array();
while ($row_style_id_catsubcat = mysqli_fetch_assoc($style_id_catsubcat)) {
	$style_id_catsubcat_map[$row_style_id_catsubcat['styles_catsubcat']] = $row_style_id_catsubcat['styles_id'];
}

$style_entry_map = array();  // style => [entid, entid, entid...]
foreach ($style_brew_map as $brewing_id => $styles_id) {
	if (! array_key_exists($styles_id, $style_entry_map)) {
		$style_entry_map[$styles_id] = array();
	}
	$style_entry_map[$styles_id][] = $brewing_id;
}
$entry_sequence_map = array();
foreach ($style_sequence_map as $table => $style_sequence) {
	$sequence = 0;
	foreach ($style_sequence as $style_catsubcat) {
		// Skip styles for which we have not entries
		if (array_key_exists($style_id_catsubcat_map[$style_catsubcat], $style_entry_map)) {
			foreach ($style_entry_map[$style_id_catsubcat_map[$style_catsubcat]] as $brewing_id) {
				$sequence++;
				$entry_sequence_map[$brewing_id] = $sequence;
			}
		}
	}
}

?>