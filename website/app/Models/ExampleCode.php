<?php
namespace App\Models;

use FastSitePHP\Application;
use FastSitePHP\Lang\I18N;

/**
 * Model for Example Code which is used in Quick Reference and other pages.
 */
class ExampleCode
{
    private $code_text = null;
    private $code_list = null;

    /**
     * Class Constructor
     * @param Application $app
     */
    function __construct(Application $app = null)
    {
        if ($app !== null) {
            $this->readFile($app);
            $this->parseCode($this->code_text);
        }
    }

    /**
     * Return Array of Code Objects (\stdClass) parsed from the loaded and parsed file.
     *
     * @param null|string $class
     * @return array
     */
    public function getCode($class = null)
    {
        // When returning all example code include only items that contain a
        // title otherwise they are not meant for the Quick Reference page.
        if ($class === null) {
            $filtered = [];
            foreach ($this->code_list as $code) {
                if ($code->title != null) {
                    $filtered[] = $code;
                }
            }
            return $filtered;
        } else {
            $filtered = [];
            foreach ($this->code_list as $code) {
                if ($code->class != null && in_array($class, $code->class, true)) {
                    $filtered[] = $code;
                }
            }
            return $filtered;
        }
    }

    /**
     * Read an example code file based on the user's selected language
     * @param Application $app
     */
    public function readFile(Application $app)
    {
        $file_path = $app->config['APP_DATA'] . 'sample-code/home-page-{lang}-examples.php';
        $this->code_text = I18N::textFile($file_path, $app->lang);
    }

    /**
     * Parse code blocks as an array of objects from a simple example code file
     * @param array $code
     */
    public function parseCode($code)
    {
        // Split code based on specific comments to seperate related
        // code, then remove first section before start of code.
        $code_blocks = explode('// EXAMPLE_CODE_START', $code);
        array_splice($code_blocks, 0, 1);

        // Process each block of code
        $example_code = array();
        foreach ($code_blocks as $code) {
            // Remove code after the end of the example
            $pos = strpos($code, '// EXAMPLE_CODE_END');
            $code = substr($code, 0, $pos);

            // Normalize line endings [CRLF -> LF]
            $code = str_replace("\r\n", "\n", $code);

            // Split to an array on new lines and remove
            // the first item as it will be blank.
            $code = explode("\n", $code);
            array_splice($code, 0, 1);

            // Does the first line start with 4 spaces?
            // If so then trim 4 spaces off of each line.
            if (isset($code[0]) && strpos($code[0], '    ') === 0) {
                for ($n = 0, $m = count($code); $n < $m; $n++) {
                    if (strpos($code[$n], '    ') === 0) {
                        $code[$n] = substr($code[$n], 4);
                    }
                }
            }

            // Get attributes and remove the attribute lines
            // Currently this code requires attributes to be
            // defined in the order listed below.
            $attr = [
                'title' => null,
                'class' => null,
            ];
            $search = [
                'title' => '// TITLE: ',
                'class' => '// CLASS: ',
                'find_replace' => '// FIND_REPLACE: ',
            ];
            foreach ($search as $key => $value) {
                if (strpos($code[0], $value) === 0) {
                    $attr[$key] = trim(substr($code[0], strlen($value)));
                    array_splice($code, 0, 1);
                }
            }

            // Join lines back to a string
            $code = implode("\n", $code);

            // Does the Code have Find/Replace Strings?
            if (array_key_exists('find_replace', $attr)) {
                $obj = json_decode($attr['find_replace']);
                foreach ($obj as $find => $replace) {
                    $code = str_replace($find, $replace, $code);
                }
            }

            // Add to array
            $obj = new \stdClass;
            $obj->title = $attr['title'];
            $obj->class = $attr['class'];
            if ($obj->class !== null) {
                $obj->class = explode(', ', $obj->class);
            }
            $obj->code = $code;
            $example_code[] = $obj;
        }
        $this->code_list = $example_code;
    }
}