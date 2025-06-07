<?= json_encode(
	[
		'table' => $tables, 
		'check'=>$checks
	],
	JSON_PRETTY_PRINT
) ?>