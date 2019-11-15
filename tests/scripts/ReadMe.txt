#==================================================
# Overview
#==================================================

This document provides instructions on how to run unit tests using C# Source Code
[FastSitePHP_UnitTests.cs] or a Unix Shell Script [FastSitePHP_UnitTests.sh].
These files are for testing the Request Header 'Expect: 100-continue' and 
Redirect Response Body Text with FastSitePHP.

#==================================================
# C# Source Code
#==================================================

#Modify the variable [rootUrl] near the top of the file [FastSitePHP_UnitTests.cs]
#The variable needs to refer to the location where FastSitePHP will be hosted. For 
#example a server or on the local computer.

#Navigate to the directory containing the script

#To compile the C# Code from a command prompt use one of two options 
#depending on if Environment Variables are set:

C:\Windows\Microsoft.NET\Framework\v4.0.30319\csc.exe FastSitePHP_UnitTests.cs
csc FastSitePHP_UnitTests.cs

#Run and delete the program using the command prompt after compiling
FastSitePHP_UnitTests.exe
del FastSitePHP_UnitTests.exe

#==================================================
# Unix Shell Script
#==================================================

#Modify the variable [root_url] near the top of the file [FastSitePHP_UnitTests.sh]
#The variable needs to refer to the location where FastSitePHP will be hosted. For 
#example a server or on the local computer.

#Navigate to the directory containing the script

#Run command:
bash FastSitePHP_UnitTests.sh
