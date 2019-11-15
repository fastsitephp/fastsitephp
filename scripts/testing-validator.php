<?php
// Test Script for manually testing the Data\Validator class.
// In the future parts of this code will be used for Unit Testing.

// Start Stat/Memory/Time
require __DIR__ . '/../src/Utilities/debug.php';

// Autoloader and Setup App
require __DIR__ . '/../autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;
set_time_limit(0);

$is_cli = (php_sapi_name() === 'cli');
$line_break = ($is_cli ? "\n" : '<br>');

// ----------------------------------------------------------------
// Pref Test
//
// Example with 10,000 loops:
//
// Testing Rules as: string
// float(0.19420003890991)
//
// Testing Rules as: array
// float(0.059799909591675)

// $data = ['test' => 'test data', 'num' => '123.456'];
// $rules = [
//     [
//         ['test', null, 'required type="text" maxlength="10" list="test data,test"'],
//         ['num', null, 'required type="number" min="100"'],
//     ],
//     [
//         ['test', null, ['required'=>'', 'type'=>'text', 'maxlength'=>'10', 'list'=>'test data,test']],
//         ['num', null, ['required'=>'', 'type'=>'number', 'min'=>'100']],
//     ],
// ];
// $loop_count = 10000;

// for ($n = 0; $n < 2; $n++) {
//     echo 'Testing Rules as: ' . gettype($rules[$n][0][2]);
//     $start_time = microtime(true);
    
//     for ($x = 0; $x < $loop_count; $x++) {
//         $validator = new \FastSitePHP\Data\Validator();
//         $errors = $validator
//             ->addRules($rules[$n])
//             ->validate($data);    
//     }
    
//     $script_time = microtime(true) - $start_time;
//     echo $line_break;
//     var_dump($script_time);
//     echo $line_break;
// }
// exit();
// ----------------------------------------------------------------

// -------------------------------------
// Check Time Formats
// $times = array(
//     // Valid
//     '00:00',
//     '00:00:00',
//     '01:01',
//     '01:01:00',
//     '23:59:59',

//     // Invalid
//     'abc',
//     '1:1',
//     '24:60',
//     '24:60:60',
//     '99:99:99',
// );
// $validator = new \FastSitePHP\Data\Validator();
// foreach ($times as $time) {
//     echo $time . ' = ' . json_encode($validator->checkType($time, 'time'));
//     echo "\n";
// }
// exit();
// -------------------------------------

// Supported Rules and Data Types
// $validator = new \FastSitePHP\Data\Validator();
// print_r($validator->supportedRules());
// print_r($validator->supportedTypes());
// exit();

for ($n = 0; $n < 2; $n++) {
    if ($n === 0) {
        // Show Errors on all fields
        $data = array(
            'user_name' => 'Test Test Test',
            'small_value' => 'a',
            'country' => 'a',
            'site_password' => 'secret2',
            'confirm' => 'secret',
            'any_number1' => 'abc',
            'any_number2' => 'def',
            'int_number' => '123.456',
            'float_number' => 'abcd',
            'date_value' => '2018-00-99',
            'time_value' => '99:99:99',
            'datetime1_value' => '2018-10-11',
            'datetime2_value' => '2018-10-11 99:99',
            'datetime3_value' => '2018',
            'datetime4_value' => '2018-10-11 01:01:01 PM',
            'email' => 'a@a',            
            'url1' => 'mailto:a@a', // This validates with PHP: filter_var('mailto:a@a', FILTER_VALIDATE_URL)
            'url2' => 'example.com', // Missing HTTP/HTTPS
            'uemail' => 'a@a',
            'bool1' => '-1',
            'bool2' => '123',
            'bool3' => 'Y',
            'bool4' => 'Z',
            'bool5' => 'abc',
            'bool6' => '11',
            'bool7' => 'NULL',
            'bool8' => '00',  
            'bool9' => 'null',
            'bool10' => 'dsfsd',
            'timezone' => 'Unknown',
            'ip1' => '127.0.0.1.0',
            'ip2' => ':::',
            'ip3' => '172.16.0.0/12', // Valid CIDR
            'ipv4' => 'fe80::3030:70d9:5af2:cc72', // IPv4 and IPv6 Values Valid but swapped
            'ipv6' => '10.10.10.10',
            'cidr1' => '172.16.0.0', // Valid IP but missing CIDR
            'cidr2' => '',
            'cidr_ipv4' => 'fc00::/7',
            'cidr_ipv6' => '169.254.0.0/16',
            'json1' => '}{',
            'json2' => '{"test":"test",}',
            // The first Base64 and Base64Url are valid but swapped
            // Raw Data Value: chr(105) . chr(175) . chr(191)
            'base64' => 'aa-_',
            'base64url' => 'aa+/',
            'base64_crlf' => '$$$',
            'base64url_crlf' => '$$$',
            'xml' => '<xml>',
            'min_num1' => '9',
            'min_num2' => '10.1',
            'min_num3' => 'abc',
            'max_num1' => '5000',
            'max_num2' => '5000.2',
            'max_num3' => 'abc',
            'pattern' => '0123456789',
            'list' => 'Item1',
            'multiple_rules' => '123',
        );
    } else {
        // Pass all Tests
        $data = array(
            'user_name' => 'Test Test',
            'small_value' => 'aa',
            'user_age' => 10,
            'country' => 'AA',
            'site_user' => 'admin',
            'site_password' => 'secret',
            'confirm' => 'secret',
            'any_number1' => '123456789',
            'any_number2' => '123.456789',
            'int_number' => '123',
            'float_number' => '123.456',
            'date_value' => '2018-10-11',
            'time_value' => '23:59:59',
            'datetime1_value' => '2018-10-11 13:01:01',
            'datetime2_value' => '2018-10-11 13:01',
            'datetime3_value' => '2018-10-11T13:01:01',
            'datetime4_value' => '2018-10-11T13:01',
            'email' => 'a@example.com',
            'url1' => 'https://www.example.com',
            'url2' => 'http://example.com',
            'uemail' => '测试@example.com',
            'bool1' => 'true',
            'bool2' => 'on',
            'bool3' => '1',
            'bool4' => 'yes',
            'bool5' => 'FALSE',
            'bool6' => 'off',
            'bool7' => '0',
            'bool8' => 'no',   
            'bool9' => '',
            'bool10' => null,   
            'timezone' => 'Asia/Shanghai',
            'ip1' => '127.0.0.1',
            'ip2' => '::1',
            'ip3' => '172.16.0.0',
            'ipv4' => '10.10.10.10',
            'ipv6' => 'fe80::3030:70d9:5af2:cc72',
            'cidr1' => '172.16.0.0/12',
            'cidr2' => 'fe80::/10',
            'cidr_ipv4' => '169.254.0.0/16',
            'cidr_ipv6' => 'fc00::/7',
            'json1' => '[123,"abc"]',
            'json2' => '{"test":"test"}',
            'base64' => 'aa+/',
            'base64url' => 'aa-_',
            'base64_crlf' => chunk_split(base64_encode(str_repeat('a', 100))),
            'base64url_crlf' => chunk_split(FastSitePHP\Encoding\Base64Url::encode(str_repeat('a', 100))),
            'xml' => '<xml><node /></xml>',
            'min_num1' => '10',
            'min_num2' => '10.2',
            'min_num3' => 1,
            'max_num1' => '4999',
            'max_num2' => '5000.1',        
            'max_num3' => 100,
            'pattern' => '012-345-6789',  
            'list' => 'Item 1',  
            'submitted' => null,
            'multiple_rules' => 'abcABC',
        );
    }

    $validator = new \FastSitePHP\Data\Validator();
    $validator
        ->addRules(array(
            array('user_name', 'Name', 'required type="text" maxlength="10"'),
            array('small_value', 'Small Value', 'required minlength=2'),
            array('user_age', 'Age', array('required'=>true, 'min'=>10)),
            array('country', 'Country', 'required length=2'),
            array('site_user', 'Site User', 'check-user'),
            array('site_password', 'Password', 'check-password'),
            array('confirm', null, 'confirm-password'),
        ))
        ->addRules(array(
            array('any_number1', 'Any Number 1', 'type=number'),
            array('any_number2', null, 'type=number'),
            array('int_number', null, 'type=int'),
            array('float_number', null, 'type=float'),
            array('date_value', null, 'type="date"'),
            array('time_value', null, 'type=time'),
            array('datetime1_value', null, 'type=datetime'),
            array('datetime2_value', null, 'type=datetime'),
            array('datetime3_value', null, 'type=datetime-local'),
            array('datetime4_value', null, 'type=datetime-local'),
            array('email', null, 'type=email'),
            array('url1', null, 'type=url'),
            array('url2', null, 'type=url'),
            array('uemail', 'Unicode Email', 'type=unicode-email'),
            array('bool1', null, 'type=bool'),
            array('bool2', null, 'type=bool'),
            array('bool3', null, 'type=bool'),
            array('bool4', null, 'type=bool'),
            array('bool5', null, 'type=bool'),
            array('bool6', null, 'type=bool'),
            array('bool7', null, 'type=bool'),
            array('bool8', null, 'type=bool'),
            array('bool9', null, 'type=bool'),
            array('bool10', null, 'type=bool'),
            array('timezone', null, 'type=timezone'),
            array('ip1', null, 'type=ip'),
            array('ip2', null, 'type=ip'),
            array('ip3', null, 'type=ip'),
            array('ipv4', null, 'type=ipv4'),
            array('ipv6', null, 'type=ipv6'),
            array('cidr1', null, 'type=cidr'),
            array('cidr2', null, 'type=cidr'),
            array('cidr_ipv4', null, 'type=cidr-ipv4'),
            array('cidr_ipv6', null, 'type=cidr-ipv6'),
            array('json1', null, 'type=json'),
            array('json2', null, 'type=json'),
            array('base64', null, 'type=base64'),
            array('base64url', null, 'type=base64url'),
            array('base64_crlf', null, 'type=base64'),
            array('base64url_crlf', null, 'type=base64url'),
            array('xml', null, 'type=xml'),
            array('min_num1', null, 'type=number min=10'),
            array('min_num2', null, 'type=number min=10.2'),
            array('min_num3', null, 'min=1'),
            array('max_num1', null, 'type=number max=4999'),
            array('max_num2', null, 'type=number max=5000.1'),
            array('max_num3', null, 'max=100'),
            array('pattern', null, 'pattern=[0-9]{3}-[0-9]{3}-[0-9]{4}'),
            array('list', null, 'list="Item 1 , Item 2"'),
            array('submitted', null, 'exists'),
            array('multiple_rules', null, 'pattern=[a-zA-Z]+ minlength=6'),
        ))
        ->customRule('check-user', function($value) {
            return ($value === 'admin');
        })
        ->customRule('check-password', function($value) {
            return ($value === 'secret' ? true : 'Invalid Password');
        })
        ->customRule('confirm-password', function($value) use ($data) {
            $confirm = (isset($data['site_password']) ? $data['site_password'] : null);
            return ($value === $confirm ? true : 'Both [Password] and [Confirm Password] must match.');
        });


    list($errors, $error_fields) = $validator->validate($data);
    // list($errors, $error_fields) = $validator->validate((object)$data);
    var_dump($errors);
    echo 'Error Count: ' . count($errors);
    echo $line_break;
    var_dump(implode(', ', array_keys($error_fields)));
    echo 'Error Fields: ' . count($error_fields);
    echo $line_break;
    echo json_encode($error_fields, JSON_PRETTY_PRINT);
    echo $line_break;
    echo $line_break;
}

// Show Memory Info
$showDebugInfo($is_cli);
