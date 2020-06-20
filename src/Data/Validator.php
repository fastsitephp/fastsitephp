<?php

/**
 * Copyright Conrad Sollitt and Authors. For full details of copyright
 * and license, view the LICENSE file that is distributed with FastSitePHP.
 *
 * @package  FastSitePHP
 * @link     https://www.fastsitephp.com
 * @author   Conrad Sollitt (http://conradsollitt.com)
 * @license  MIT License
 */

namespace FastSitePHP\Data;

use \DateTime;
use \DOMDocument;
use \Exception;
use FastSitePHP\Encoding\Base64Url;
use FastSitePHP\Net\IP;

/**
 * FastSitePHP Data Validation API
 *
 * For many apps validating client side (webpage or app) provides instant
 * feedback to users and limits need for extra web request, however users
 * can bypass validation by using DevTools or other methods so for data
 * that needs to be validated using server-side validation is important.
 *
 * This class allows for many rules to be easily defined and run against
 * an object (or Associative Array/Dictionary).
 *
 * Common rules can simply be copied from HTML Input controls.
 */
class Validator
{
    private $rules = array();
    private $custom_rules = array();
    private $error_text = null;

    /**
     * Add an array of rules for use with the [validation()] function
     * in the format of [Field, Display_Name, Rules].
     *
     * Field = String of the Array Key or Object Property to validate
     * Display_Name = Field Name to include on Error Text for User - String or Null to use Field Name
     * Rules = String of rules to be parsed or an array of defined rules
     *
     * @param array $rules
     * @return $this
     * @throws Exception
     */
    public function addRules(array $rules)
    {
        // Validate and add each rule
        foreach ($rules as $rule) {
            if (!is_array($rule)
                || count($rule) !== 3
                || !is_string($rule[0])
                || !($rule[1] === null || is_string($rule[1]))
                || !(is_string($rule[2]) || is_array($rule[2]))
            ) {
                $error = 'Invalid Rules for [%s->%s()]. Each rule must be an array in the format of [string, string|null, string|array]. Refer to examples and documentation for proper usage.';
                $error = sprintf($error, __CLASS__, __FUNCTION__);
                throw new \Exception($error);
            }
            $this->rules[] = $rule;
        }
        return $this;
    }

    /**
     * Define a Custom Rule using a callback function for use with
     * the [validation()] function.
     *
     * The name must contain a dash '-' character and cannot
     * contain spaces [ ], quotes ["], or equals [=] characters.
     *
     * Examples of valid rule names:
     *     'custom-rule'
     *     'check-password'
     *     'db-unique-email'
     *
     * @param string $name
     * @param \Closure $callback
     * @return $this
     * @throws Exception
     */
    public function customRule($name, \Closure $callback)
    {
        if (in_array($name, $this->supportedRules(), true)) {
            $error = 'Custom Rule [%s] matches one of the standard rule names [%s] and cannot be used.';
            $error = sprintf($error, $name, implode(', ', $this->supportedRules()));
            throw new \Exception($error);
        } elseif (isset($this->custom_rules[$name])) {
            $error = 'Custom Rule [%s] was already defined. Review validation code to make sure it is setup correctly.';
            $error = sprintf($error, $name);
            throw new \Exception($error);
        } elseif (
            strpos($name, '-') === false
            || strpos($name, ' ') !== false
            || strpos($name, '"') !== false
            || strpos($name, '=') !== false
        ) {
            $error = 'Invalid Rule Name at [%s->%s([%s])]. Rule Names must contain a dash [-] character and cannot contain spaces [ ], quotes ["], or equals [=] characters.';
            $error = sprintf($error, __CLASS__, __FUNCTION__, (is_string($name) ? $name : gettype($name)));
            throw new \Exception($error);
        }

        $this->custom_rules[$name] = $callback;
        return $this;
    }

    /**
     * Return an array of standard rules supported by this class.
     *
     * @return array
     */
    public function supportedRules()
    {
        return array(
            'exists', 'required', 'type', 'minlength', 'maxlength',
            'length', 'min', 'max', 'pattern', 'list',
        );
    }

    /**
     * Return an array of types supported the [type] rule.
     *
     * @return array
     */
    public function supportedTypes()
    {
        return array(
            'text',
            'password',
            'tel',
            'number',
            'range',
            'date',
            'time',
            'datetime',
            'datetime-local',
            'email',
            'url',
            'unicode-email',
            'int',
            'float',
            'json',
            'base64',
            'base64url',
            'xml',
            'bool',
            'timezone',
            'ip',
            'ipv4',
            'ipv6',
            'cidr',
            'cidr-ipv4',
            'cidr-ipv6',
        );
    }

    /**
     * Get or set an Array of error message templates used when validation
     * fails. All error text can be overridden by the calling application.
     *
     * Different data types for the [type] validation can have specific
     * messages under the 'types' option. See a full list of types
     * in the function [supportedTypes()].
     *
     * To customize text for your applications simply copy and modify
     * the array from this function's source code.
     *
     * @param array|null $error_text
     * @return array|$this
     */
    public function errorText(array $error_text = null)
    {
        // Get User Defined or Default Error Text
        if ($error_text === null) {
            if ($this->error_text !== null) {
                return $this->error_text;
            }
            return array(
                'empty_value' => 'empty',
                'exists'      => '[{field}] was not submitted with the request.',
                'required'    => '[{field}] is a required field.',
                'type'        => '[{field}] must be a valid {param}.',
                'minlength'   => '[{field}] is too small and must be at least [{param}] characters in length. The size entered was [{size}] characters.',
                'maxlength'   => '[{field}] is too large and is limited to [{param}] characters. The size entered was [{size}] characters.',
                'length'      => '[{field}] must be exactly [{param}] characters in length. The size entered was [{size}] characters.',
                'min'         => '[{field}] is too small and must be at least [{param}]. The value entered was [{value}].',
                'max'         => '[{field}] is too large and is limited to [{param}]. The value entered was [{value}].',
                'pattern'     => '[{field}] does not match the required pattern.',
                'list'        => '[{field}] must be one of the following values [{param}].',
                'custom-rule' => '[{field}] is invalid.',
                'types' => array(
                    // NOTE - some types such as 'number' and 'date' are handled by the 'type' rule above
                    'datetime'       => '[{field}] must be a valid Date and Time.',
                    'datetime-local' => '[{field}] must be a valid Date and Time.',
                    'url'            => '[{field}] must be a valid URL that starts with either [http] or [https].',
                    'unicode-email'  => '[{field}] must be a valid Email.',
                    'int'            => '[{field}] must be a valid Integer/Whole Number.',
                    'float'          => '[{field}] must be a valid Decimal Number.',
                    'json'           => '[{field}] must be a valid JSON String.',
                    'base64'         => '[{field}] must be a valid Base64 String.',
                    'base64url'      => '[{field}] must be a valid Base64-URL String.',
                    'xml'            => '[{field}] must be a valid XML String.',
                    'bool'           => '[{field}] must be a valid Boolean Value such as [true] or [false].',
                    'ip'             => '[{field}] must be a valid IP Address.',
                    'ipv4'           => '[{field}] must be a valid IPv4 Address.',
                    'ipv6'           => '[{field}] must be a valid IPv6 Address.',
                    'cidr'           => '[{field}] must be a valid IP/CIDR Address.',
                    'cidr_ipv4'      => '[{field}] must be a valid IPv4/CIDR Address.',
                    'cidr_ipv6'      => '[{field}] must be a valid IPv6/CIDR Address.',
                ),
            );
        }

        // Set custom error text
        $this->$error_text = $error_text;
        return $this;
    }

    /**
     * Validate an object using rules defined from [addRules() or customRule()].
     *
     * The object to validate, must be either an Associative Array (Dictionary)
     * or an Object
     *
     * Returns an array of ( error_messages[], error_fields[] ) where
     *     error_messages[] = Error Messages to Display to End Users
     *     error_fields[]   = Dictionary with each field that had an error and an array of error messages for the field.
     * If count(error_messages[]) = 0 then all validations passed.
     *
     * @param array|object $data
     * @return array
     * @throws Exception
     */
    public function validate($data)
    {
        if (!(is_array($data) || is_object($data))) {
            $error = 'Object for validation must be either an array or an object. Instead a [%s] was passed to the function.';
            $error = sprintf($error, gettype($data));
            throw new \Exception($error);
        }

        $errors = array();
        $error_fields = array();

        foreach ($this->rules as $item) {
            // Get Field, Display Name, and Rules
            list($field, $display_name, $rules) = $item;
            $display_name = ($display_name ?: $field);
            $rules = $this->parseRuleText($field, $rules);

            // Validate the field
            $field_errors = $this->validateField($data, $field, $display_name, $rules);
            if ($field_errors) {
                $errors = array_merge($errors, $field_errors);
                if (isset($error_fields[$field])) {
                    $error_fields[$field] = array_merge($error_fields[$field], $field_errors);
                } else {
                    $error_fields[$field] = $field_errors;
                }
            }
        }

        // Return an array of [Error-Messages, Error-Fields]
        return array($errors, $error_fields);
    }

    /**
     * Validate a specific field and rules, this gets called
     * once per each item in [addRules()].
     *
     * @param array|object $data
     * @param string $field
     * @param string $display_name
     * @param array $rules
     * @return array - List of Error messages if Validation failed
     * @throws Exception
     */
    private function validateField($data, $field, $display_name, $rules)
    {
        // Get value
        list($exists, $value) = $this->value($data, $field);

        // Validate if field is required to exist
        if (isset($rules['exists']) && !$exists) {
            return array($this->buildErrorMessage('exists', $display_name, '', $value));
        }

        // Validate if field is required to have a value
        $is_empty = ($value === null || $value === '');
        if (isset($rules['required'])) {
            if ($is_empty) {
                return array($this->buildErrorMessage('required', $display_name, '', $value));
            }
        } elseif ($is_empty) {
            // Skip additional validation if field
            // is empty and not required.
            return array();
        }

        // Validate Additional Rules
        $errors = array();
        foreach ($rules as $rule => $param) {
            $is_valid = true;
            switch ($rule) {
                case 'exists':
                case 'required':
                    // Skip as these are checked above
                    break;
                case 'type':
                    $is_valid = $this->checkType($value, $param);
                    break;
                case 'minlength':
                    $min_length = $this->intParam($rule, $param, $field);
                    $is_valid = (strlen($value) >= $min_length);
                    break;
                case 'maxlength':
                    $max_length = $this->intParam($rule, $param, $field);
                    $is_valid = (strlen($value) <= $max_length);
                    break;
                case 'length':
                    $length = $this->intParam($rule, $param, $field);
                    $is_valid = (strlen($value) === $length);
                    break;
                case 'min':
                    $min = $this->numParam($rule, $param, $field);
                    $value = $this->numValue($value);
                    $is_valid = ($value !== null && $value >= $min);
                    break;
                case 'max':
                    $max = $this->numParam($rule, $param, $field);
                    $value = $this->numValue($value);
                    $is_valid = ($value !== null && $value <= $max);
                    break;
                case 'pattern':
                    // Match based on how the [pattern] attribute would be defined
                    // on an HTML5 <input> control. Extra code is used to provide
                    // developer friendly error messages. See comments related to
                    // [preg_match()] and error handling in [Application->checkParam()].
                    $pattern = '/^' . $param . '$/u';
                    $current_error_level = error_reporting(0);
                    $result = preg_match($pattern, $value);
                    error_reporting($current_error_level);
                    if ($result === false) {
                        $last_error = error_get_last();
                        $last_error = (isset($last_error['message']) ? $last_error['message'] : null);
                        $preg_match_error = (isset($last_error) ? sprintf('Error message from PHP: %s', $last_error) : 'Specific error message from [preg_match()] cannot be obtained because a function defined by this site for [set_error_handler()] did not return false.');
                        $error = 'Error with [pattern] for field [%s], the regular expression "%s" is not a valid pattern. %s';
                        $error = sprintf($error, $field, $param, $preg_match_error);
                        throw new \Exception($error);
                    }
                    $is_valid = ($result === 1);
                    break;
                case 'list':
                    $param = explode(',', $param);
                    $param = array_map('trim', $param);
                    $is_valid = in_array($value, $param, true);
                    break;
                default:
                    // Validate against custom rule or throw exception if
                    // the rule is not defined or returns and invalid result.
                    if (!isset($this->custom_rules[$rule])) {
                        throw new \Exception('Error - Unhandled Validation Rule: ' . $rule);
                    }
                    $callback = $this->custom_rules[$rule];
                    $is_valid = call_user_func($callback, $value);
                    if (is_bool($is_valid) || is_string($is_valid)) {
                        $rule = 'custom-rule';
                    } else {
                        throw new \Exception('Custom Rule [' . $rule . '] needs to return either a bool or string');
                    }
            }

            // Add error message if validation failed
            if ($is_valid === false) {
                $errors[] = $this->buildErrorMessage($rule, $display_name, $param, $value);
            } elseif (is_string($is_valid)) {
                $errors[] = $is_valid;
            }
        }
        return $errors;
    }

    /**
     * Return [true] if valid - Value matches the specified data type.
     *
     * This function is marked public for validation with Unit Testing.
     * Calling [validate()] after [addFields(), etc] is the intended
     * use of this class.
     *
     * @param mixed $value
     * @param string $type
     * @return bool
     * @throws Exception
     */
    public function checkType($value, $type)
    {
        switch ($type) {
            // --------------------------------------------------
            // HTML5 Supported <input type="type"> Attributes
            case 'text':
            case 'password':
            case 'tel':
                // Simply ignore basic types as only strings, numbers, etc are allowed.
                // 'text', 'password', 'tel' is used for clarity and to allow copy/paste of
                // HTML code without having to remove the attribute.
                // Browsers validate 'tel' by using the [pattern] attribute as 'tel'
                // is mainly used for keyboard hints on mobile devices.
                return true;
            case 'number':
            case 'range':
                if (is_int($value) || is_float($value)) {
                    return true;
                }
                return filter_var($value, FILTER_VALIDATE_INT) !== false || filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
            case 'date':
                // Accepted format: 'YYYY-MM-DD'
                $date = \DateTime::createFromFormat('Y-m-d', $value);
                $date_errors = \DateTime::getLastErrors();
                $is_valid = ($date !== false && ($date_errors['warning_count'] + $date_errors['error_count'] === 0));
                return $is_valid;
            case 'time':
                // Accepted formats: 'HH:MM:SS' or 'HH:MM' (24-Hour Time)
                $pattern = '/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/';
                return preg_match($pattern, $value) === 1;
            case 'datetime':
            case 'datetime-local':
                // Browsers support 'datetime-locale' and use format of 'yyyy-MM-ddThh:mm'
                // and then display the value formatted to the user's locale. This function is
                // expanded upon to accept a space ' ' in addition to 'T' and to allow for seconds.
                // https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/datetime-local
                $formats = array('Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d\TH:i:s', 'Y-m-d\TH:i');
                foreach ($formats as $format) {
                    $date = \DateTime::createFromFormat($format, $value);
                    $date_errors = \DateTime::getLastErrors();
                    $is_valid = ($date !== false && ($date_errors['warning_count'] + $date_errors['error_count'] === 0));
                    if ($is_valid) {
                        return true;
                    }
                }
                return false;
            case 'email':
                // See also 'unicode-email' as this function does not handle
                // Unicode Characters and is based on RFC 5321.
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'url':
                // FILTER_VALIDATE_URL accepts many types of URL's as valid such as
                // 'mailto:' so this function also requires 'http/https' at the start.
                return (
                    filter_var($value, FILTER_VALIDATE_URL) !== false
                    && (stripos($value, 'http://') === 0 || stripos($value, 'https://') === 0)
                );

            // --------------------------------------------------------------
            // Custom Data Types (not defined from HTML5 Input Element)
            case 'unicode-email':
                // For Unicode Emails, minimal string and regex email validation
                // is used to check if the value looks like an email. When used
                // it's up to the SMTP Server to validate and accept the email.
                $pattern = '/.+@.+\..+/';
                return (
                    strpos($value, ' ') === false
                    && strpos($value, '<') === false
                    && strpos($value, '>') === false
                    && strpos($value, "\r") === false
                    && strpos($value, "\n") === false
                    && preg_match($pattern, $value) === 1 ? true : false
                );
            case 'int':
                return is_int($value) || filter_var($value, FILTER_VALIDATE_INT) !== false;
            case 'float':
                return (
                    is_int($value)
                    || is_float($value)
                    || filter_var($value, FILTER_VALIDATE_FLOAT) !== false
                );
            case 'json':
                $data = json_decode($value);
                $has_error = ($data === null && json_last_error() !== JSON_ERROR_NONE);
                return !$has_error;
            case 'base64':
                return base64_decode($value, true) !== false;
            case 'base64url':
                return Base64Url::decode((string)$value) !== false;
            case 'xml':
                if (!class_exists('DOMDocument')) {
                    $error = 'Unable to validate XML because PHP extension [libxml] is not installed on this server.';
                    throw new \Exception($error);
                }
                $parsed = false;
                try {
                    $doc = new \DOMDocument();
                    if ($doc->loadXML($value) === true) {
                        $parsed = true;
                    };
                } catch (\Exception $e) {
                    return false;
                }
                return $parsed;
            case 'bool':
                // Returns true if the value is:
                //   true value = ['1', 'true', 'on', 'yes']
                //   false value = ['0', 'false', 'off', 'no', null]
                // If any other value then false is returned
                if (is_bool($value)) {
                    return true;
                }
                $result = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                return is_bool($result);
            case 'timezone':
                return in_array($value, \DateTimeZone::listIdentifiers(), true);
            case 'ip':
                return filter_var($value, FILTER_VALIDATE_IP) !== false;
            case 'ipv4':
                return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
            case 'ipv6':
                return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
            case 'cidr':
                $info = IP::cidr((string)$value);
                return isset($info['Address_Type']);
            case 'cidr-ipv4':
                $info = IP::cidr((string)$value);
                return isset($info['Address_Type']) && $info['Address_Type'] === 'IPv4';
            case 'cidr-ipv6':
                $info = IP::cidr((string)$value);
                return isset($info['Address_Type']) && $info['Address_Type'] === 'IPv6';
            default:
                throw new \Exception(sprintf('Unsupported type [%s] for validation. Refer to documentation or Source code for Supported Types.', $type));
        }
    }

    /**
     * Parse a Rule Text String and return an Array, example:
     *     'required type=text list="Item 1,Item 2"'
     * Returns
     *     ['required'=>'', 'type'=>'text', 'list'=>'Item 1,Item 2']
     *
     * @param string $field
     * @param string|array $rule_text
     * @return array
     * @throws Exception
     */
    private function parseRuleText($field, $rule_text)
    {
        // Are the rules already an array?
        if (is_array($rule_text)) {
            return $rule_text;
        }

        // Check if the value contains a double-quote character. If not
        // then a simple string splitting can be used to get the rules
        // otherwise more complex parsing is needed.
        if (strpos($rule_text, '"') === false) {
            $rules = array();
            $rule_list = explode(' ', $rule_text);
            foreach ($rule_list as $rule) {
                $rule = trim($rule);
                if ($rule !== '') {
                    $pos = strpos($rule, '=');
                    $name = $rule;
                    $value = '';
                    if ($pos !== false) {
                        $value = substr($rule, $pos+1);
                        $name = substr($rule, 0, $pos);
                    }
                    $rules[$name] = $value;
                }
            }
            return $rules;
        }

        // Values used while parsing the string
        $rules = array();
        $in_quote = false;
        $found_equal = false;
        $name = '';
        $value = '';

        // Loop through each character in the string one at at time
        for ($n = 0, $m = strlen($rule_text); $n < $m; $n++) {
            // Get the current character
            $char = $rule_text[$n];

            // Handle Current Character
            if ($char === ' ' && !$in_quote) {
                // Add parsed rule and reset for next rule
                if ($name !== '') {
                    $rules[$name] = $value;
                }
                $found_equal = false;
                $name = '';
                $value = '';
            } elseif ($char === '=' && !$found_equal) {
                // Seperator for [name=value]
                $found_equal = true;
            } elseif ($char === '"') {
                // Handle quoted string
                $in_quote = !$in_quote;
            } elseif ($found_equal) {
                // Add char to value
                $value .= $char;
            } else {
                // Add char to name
                $name .= $char;
            }
        }

        // Quoted strings must have both the starting and ending quote.
        // If the loop finished with the last character still in a quoted
        // string then the format is invalid.
        if ($in_quote) {
            $error = 'Invalid rule format for field [%s] and rule text [%s]. A quoted-string string was started by using the ["] character however an ending ["] was not added to the string. All quoted-strings must have both the starting and ending quote characters.';
            $error = sprintf($error, $field, $rule_text);
            throw new \Exception($error);
        }

        // Add the last rule to the array
        if ($name !== '') {
            $rules[$name] = $value;
        }
        return $rules;
    }

    /**
     * Build an error message based on the field and rule
     *
     * @param string $rule
     * @param string $field_name
     * @param mixed $param
     * @param mixed $value
     * @return string
     * @throws Exception
     */
    private function buildErrorMessage($rule, $field_name, $param, $value)
    {
        // If error text is missing then the calling site
        // re-defined it with missing values.
        $error_text = $this->errorText();
        if (!isset($error_text[$rule])) {
            $error = 'Invalid [%s->error_text], missing text for rule [%s].';
            $error = sprintf($error, __CLASS__, $rule);
            throw new \Exception($error);
        }

        // Get text and update placeholder variables
        $text = $error_text[$rule];

        if ($rule === 'type') {
            // Get specific data type errors if defined
            if (isset($error_text['types'][$param])) {
                $text = $error_text['types'][$param];
            }
            $param = ucfirst($param); // Convert 'number' to 'Number', etc
        }
        if (is_array($param)) {
            $param = implode(', ', $param);
        }
        if ((string)$value === '') {
            $value = $error_text['empty_value'];
        }

        $text = str_replace('{field}', $field_name, $text);
        $text = str_replace('{param}', $param, $text);
        $text = str_replace('{value}', $value, $text);
        $text = str_replace('{size}', strlen($value), $text);
        return $text;
    }

    /**
     * Validate Parameter before checking rule.
     * Defined from Developer so it must be valid.
     *
     * @param string $rule
     * @param string $param
     * @param string $field
     * @return int
     * @throws \Exception
     */
    private function intParam($rule, $param, $field)
    {
        if (is_int($param)) {
            return $param;
        } elseif (filter_var($param, FILTER_VALIDATE_INT)) {
            return (int)$param;
        }
        $error = 'Rule [%s] for Field [%s] requires the parameter to be an Integer, example [%s=10].';
        $error = sprintf($error, $rule, $field, $rule);
        throw new \Exception($error);
    }

    /**
     * Validate Parameter before checking rule.
     * Defined from Developer so it must be valid.
     *
     * @param string $rule
     * @param string $param
     * @param string $field
     * @return int|float
     * @throws \Exception
     */
    private function numParam($rule, $param, $field)
    {
        if (is_int($param) || is_float($param)) {
            return $param;
        } elseif (filter_var($param, FILTER_VALIDATE_FLOAT)) {
            return (float)$param;
        } elseif (filter_var($param, FILTER_VALIDATE_INT)) {
            return (int)$param;
        }
        $error = 'Rule [%s] for Field [%s] requires the parameter to be an Number, example [%s=10].';
        $error = sprintf($error, $rule, $field, $rule);
        throw new \Exception($error);
    }

    /**
     * Get Value from Array/Dict Key or Object Property
     *
     * @param array|object $data
     * @param string $key
     * @return array
     * @throws Exception
     */
    private function value($data, $key)
    {
        if (is_array($data)) {
            $exists = array_key_exists($key, $data);
            $value = ($exists ? $data[$key] : '');
        } else {
            $exists = property_exists($data, $key);
            $value = ($exists ? $data->{$key} : '');
        }

        // Throw an Exception if invalid data type because it means
        // that this class is not being used the way it was designed.
        if (!($value === null || is_scalar($value))) {
            $error = 'Validation only works with basic data types [null, string, int, float, or bool]. Field [%s] contained data of type [%s].';
            $error = sprintf($error, $key, gettype($data));
            throw new \Exception($error);
        }

        // Trim strings and return value
        if (is_string($value)) {
            $value = trim((string)$value);
        }
        return array($exists, $value);
    }

    /**
     * Convert to either [float, int, or null] based on the data
     *
     * @param mixed $value
     * @return int|float|null
     */
    private function numValue($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        } elseif (filter_var($value, FILTER_VALIDATE_FLOAT)) {
            return (float)$value;
        } elseif (filter_var($value, FILTER_VALIDATE_INT)) {
            return (int)$value;
        }
        return null;
    }
}
