<?php
include (DB.'output_pullsheets.db.php');
include (INCLUDES.'db_tables.inc.php');

$style_sequence_map = array(
	1 =>  array("01A", "01B", "01C", "01D"),
	2 =>  array("02A", "05A", "05B", "05C"),
	3 =>  array("03B", "05D", "27A7"),
	4 =>  array("04A", "04B", "04C"),
	5 =>  array("02B", "06A", "07A", "07B"),
	6 =>  array("02C", "03D", "08A", "08B"),
	7 =>  array("06C", "09A", "09C"),
	8 =>  array("10A", "10B", "10C"),
	9 =>  array("11A", "11B", "11C", "12A", "12B", "12C"),
	10 =>  array("14B", "14C", "15A"),
	11 => array("18A", "18B"),
	12 => array("19A", "19C", "20A", "20B"),
	13 => array("13A", "13B", "13C"),
	14 => array("15B", "15C", "16A", "16B", "16C", "16D"),
	15 => array("20C"),
	16 => array("21A"),
	17 => array("21B7"),
	18 => array("21B1", "21B", "21B2", "21B4", "21B5", "21B6"),
	19 => array("22A", "22B", "22C", "22D"),
	20 => array("17A", "17B", "17C", "17D"),
	21 => array("25B", "25C", "26C"),
	22 => array("24A", "24B", "24C", "25A", "26A"),
	23 => array("26B", "26D"),
	24 => array("23A", "23C", "23E", "23F"),
	25 => array("29A", "29B", "29C", "PRX4"),
	26 => array("30A", "30B", "30C"),
	27 => array("33A", "33B"),
	28 => array("28A", "28B", "28C"),
	29 => array("06B", "31A", "32B", "34A", "34B", "34C")
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
AND $styles_db_table.brewStyleVersion = 'BJCP2015'";
$style_brew = mysqli_query($connection, $query_style_brew) or die(mysqli_error($connection));
$style_brew_map = array();
do {
	$style_brew_map[$row_style_brew['brewing_id']] = $row_style_brew['styles_id'];
} while ($row_style_brew = mysqli_fetch_assoc($style_brew));

// entry sequence map
$query_style_id_catsubcat = "SELECT $styles_db_table.id as styles_id,
CONCAT($styles_db_table.brewStyleGroup, $styles_db_table.brewStyleNum) as styles_catsubcat
FROM $styles_db_table 
WHERE $styles_db_table.brewStyleVersion = 'BJCP2015'";
$style_id_catsubcat = mysqli_query($connection, $query_style_id_catsubcat) or die(mysqli_error());
$style_id_catsubcat_map = array();
do {
	$style_id_catsubcat_map[$row_style_id_catsubcat['styles_catsubcat']] = $row_style_id_catsubcat['styles_id'];
} while ($row_style_id_catsubcat = mysqli_fetch_assoc($style_id_catsubcat));

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