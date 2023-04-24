<?php
// Use to check "White Screen of Death" (WSOD) Errors.
// For safety this file only runs from localhost.
// As of early 2023 a generic 500 error page is displayed with Chromium
// Browsers when a WSOD occurs and the response status code is 500.

if (strpos($_SERVER['HTTP_HOST'], 'localhost') === 0) {
	// Handle and show all errors
	error_reporting(-1);
	ini_set('display_errors', 'on');
	
	// Change below to the page that is causing the WSOD
	require 'test-app.php';
	// require 'test-app-render.php';
    // require 'test-web-request-and-response.php';
}