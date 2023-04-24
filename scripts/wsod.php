<?php
// Modify Use to check "White Screen of Death" (WSOD) Errors.
// Often WSOD Errors are Syntax Errors where the main file can be parsed.
// As of early 2023 a generic 500 error page is displayed with Chromium
// Browsers when a WSOD occurs and the response status code is 500.
//
// For safety this file only runs from localhost, to use on a server
// comment out the [if] statement.
//
// When using it's often best to run this from the web root
// or in the directory where the error script is.
//
// This script is generic and has no dependencies. It can be used
// with an PHP site or page.

if (strpos($_SERVER['HTTP_HOST'], 'localhost') === 0) {
	// Handle and show all errors
	error_reporting(-1);
	ini_set('display_errors', 'on');

	// Change below to the page that is causing the WSOD
	require 'playground.php';
    // require '../vendor/fastsitephp/src/Application.php';
    // require '../vendor/fastsitephp/src/Web/Request.php';
    // require '../vendor/fastsitephp/src/Web/Response.php';
    // require '../vendor/fastsitephp/src/Net/Common.php';
}