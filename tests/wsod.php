<?php
// Use to check "White Screen of Death" (WSOD) Errors.
// For safety this file only runs from localhost.

if (strpos($_SERVER['HTTP_HOST'], 'localhost') === 0) {
	// Handle and show all errors
	error_reporting(-1);
	ini_set('display_errors', 'on');
	
	// Change below to the page that is causing the WSOD
	require 'test-app.php';
	// require 'test-app-render.php';
    // require 'test-web-request-and-response.php';
}