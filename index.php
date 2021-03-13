<?php

// Include the plugin file
require 'main.php';

// Define Parameters
$src_dir = "images/original/";
$dest_dir = "images/modified/";
$background = array(255, 255, 255);	// Optional

// Invoke the function
echo square_app_utility($src_dir, $dest_dir, $background);

?>