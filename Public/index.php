<?php
	include_once "../devworx.php";
	echo \Devworx\Frontend::process();
	\Devworx\Performance::dump('logs/performance.json');
?>