# FastSitePHP Change Log

FastSitePHP uses [Semantic Versioning](https://docs.npmjs.com/about-semantic-versioning). This change log includes Framework release history and new website features or major changes.

## Website (December 24, 2019)

* Spanish `es` translations complete for all JSON files on the main site
  * https://fastsitephp.com/es/
  * **Thanks Tibaldo Pirela Reyes** for helping with translations! https://github.com/tpirelar

## 1.1.2 (December 16, 2019)

* Updates for easier nginx suppport using a basic nginx install
  * Change affected [Application->requestedPath()] and [AppMin->requestedPath()] so handle empty string "" for PATH_INFO

## Website (December 12, 2019)

* Created a script that allows easy web server setup with Apache, PHP, and the FastSitePHP Starter Site

~~~
wget https://www.fastsitephp.com/downloads/create-fast-site.sh
sudo bash create-fast-site.sh
~~~

## 1.1.1 (December 12, 2019)

* Brazilian Portuguese `pt-BR` language support added for `L10N` - formatting dates, times, and numbers
  * https://www.fastsitephp.com/en/api/Lang_L10N
  * Previously `pt-BR` would have fallen back to `pt`
  * **Thanks Marcelo dos Santos Mafra** for finding and providing this! https://github.com/msmafra
* Class `Lang\L10N`
  * https://www.fastsitephp.com/en/api/Lang_L10N
  * Fixed bug with `Lang\L10N` class so that a 404 page is correctly sent by default
  * **Thanks eGirlAsm** for finding the bug! https://github.com/eGirlAsm
  * Added link updates for unicode-cldr in the header docs
* Changed default 404 page title message from 'Page Not Found' to '404 - Page Not Found' for clarity - Property [$app->not_found_page_title]
  * https://www.fastsitephp.com/en/api/Application

## 1.1.0 (December 10, 2019)

* New Class `FastSitePHP\FileSystem\Sync`
* Class `FastSitePHP\Lang\I18N` 
  * Added new static function: `I18n::getUserDefaultLang()`
  * Fixed edge case error when multple calls are made to `I18n::langFile()` and a file is missing after the first call.

## 1.0.0 (November 14, 2019)

* Initial public release
