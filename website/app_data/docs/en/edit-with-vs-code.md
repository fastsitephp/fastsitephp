# Use Visual Studio Code for PHP Development
<style>
    .logo-images { display:inline-flex; flex-direction:column; }
    .logo-images img { display:inline; width:150px; height:150px; }
    .logo-images img[alt='Visual Studio Code'] { height:80px; width:80px; margin-top:30px; margin-right:30px; }
    .logo-images span { font-size:100px; margin-right: 40px; margin-top: -5px; }
    @media (min-width:500px) {
        .logo-images { flex-direction:row; }
    }
</style>
<div class="logo-images">
    <img src="../../img/logos/vs-code.png" alt="Visual Studio Code">
    <span>+</span>
    <img src="../../img/logos/php.svg" alt="PHP">
</div>

---
## Overview
According to a [2019 Survey from StackOverflow](https://insights.stackoverflow.com/survey/2019#development-environments-and-tools), Microsoft’s Visual Studio Code is the most popular Code Editor for Developers. It can be installed for free on Windows, Mac, and Linux and includes built-in support for PHP with features such as syntax highlighting and IntelliSense (code completion).

Several widely used plugins are recommended here for development with PHP.

https://code.visualstudio.com/

![Microsoft Visual Studio Code](https://dydn9njgevbmp.cloudfront.net/img/docs/edit_with_vs_code/0_VS_Code_Editor.png)

---
## PHP Server Extension

When you install PHP on your computer you can then use the PHP Server extension with VS Code to launch a site. It works perfectly with FastSitePHP, simply right-click on the [index.php] file and select [PHP Server: Serve Project] or click on the PHP Server icon in the upper-right corner of the screen.

https://marketplace.visualstudio.com/items?itemName=brapifra.phpserver

![VS Code PHP Server Extension](https://dydn9njgevbmp.cloudfront.net/img/docs/edit_with_vs_code/1_Run_PHP_Server.png)

&nbsp;

You will then see FastSitePHP launch (or the starter site) in your default browser.

![View Site](https://dydn9njgevbmp.cloudfront.net/img/docs/edit_with_vs_code/2_View_Site.png)

---
## Code Runner Extension

https://marketplace.visualstudio.com/items?itemName=formulahendry.code-runner

With Code Runner you can run PHP files, JavaScript files, Python files or scripts from over 30 other languages. Simply right-click on the file and select [Run Code] or click the [Run] button in the upper-right corner of the screen.

![Code Runner Extension](https://dydn9njgevbmp.cloudfront.net/img/docs/edit_with_vs_code/3_Code_Runner.png)

&nbsp;

Console output will be displayed in the pane below your code. It’s much easier to copy content from here than a terminal or command prompt and you don’t have to switch back and forth between a terminal window for running scripts.

![Code Runner Output](https://dydn9njgevbmp.cloudfront.net/img/docs/edit_with_vs_code/4_Code_Runner_Output.png)

---
## Additional Extensions

Find more here:

https://code.visualstudio.com/docs/languages/php