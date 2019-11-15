<?php

// This file runs from a development environment and simply redirects
// to the [website/public] directory which would be used as the
// public root directory on a web server.

// To run from a command line or terminal program you can use the following:
//     cd {this-directory}
//     php -S localhost:3000
//
// Then open your web browser to:
//     http://localhost:3000/

header('Location: website/public/');
