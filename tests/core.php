<?php
// =====================================================================================
//
// This file is included by many of the PHP files in this directory. It has several 
// functions which each take a number of parameters and is used to verify that the 
// class has the functions/properties defined in the expected manner and no additional
// classes or properties defined unless they are included in the unit tests.
// An example of where this is called is [test-app.php]. It will typically
// be called as the first few unit tests when checking a new class.
//
// The curly braces used in this parts of this file [example: $object->{$prop}]
// is a feature of PHP named Variable variables. It is not commonly used in most 
// PHP Scripts or well known; it allows for the object property to be read 
// dynamically by name at runtime:
//   http://docs.php.net/manual/en/language.variables.variable.php
//
// =====================================================================================

/**
 * Verify all Properties of a Class using Reflection
 *
 * @param mixed $object                 Object for the class to check
 * @param array $null_properties        Array of public properties with a default value of null
 * @param array $true_properties        Array of public properties with a default value of true
 * @param array $false_properties       Array of public properties with a default value of false
 * @param array $string_properties      Array of public properties with a default string value
 * @param array $array_properties       Array of public properties with a default empty array
 * @param array $private_properties     Array of private properties
 * @param array $int_properties         Array of public properties with a default 0 value
 * @return string                       Text string with success and list of all properties or error message
 */
function checkObjectProperties($object, array $null_properties, array $true_properties, array $false_properties, array $string_properties, array $array_properties, array $private_properties, array $int_properties = array())
{
    // Verify public properties with a default value of null
    foreach ($null_properties as $prop) {
	    if (!property_exists($object, $prop)) {
		    return sprintf('%s->%s does not exist', get_class($object), $prop);
	    } elseif ($object->{$prop} !== null) {
		    return sprintf('%s->%s should be set to null by default.', get_class($object), $prop);
	    }
    }

    // Verify public properties with a default value of true
    foreach ($true_properties as $prop) {
        if (!property_exists($object, $prop)) {
		    return sprintf('%s->%s does not exist', get_class($object), $prop);
	    } elseif ($object->{$prop} !== true) {
            return sprintf('%s->%s should be set to true by default.', get_class($object), $prop);
        }
    }

    // Verify public properties with a default value of false
    foreach ($false_properties as $prop) {
        if (!property_exists($object, $prop)) {
		    return sprintf('%s->%s does not exist', get_class($object), $prop);
	    } elseif ($object->{$prop} !== false) {
            return sprintf('%s->%s should be set to false by default.', get_class($object), $prop);
        }
    }

    // Verify public properties with a default string value
    foreach ($string_properties as $key => $value) {
        if (!property_exists($object, $key)) {
		    return sprintf('%s->%s does not exist', get_class($object), $key);
	    } elseif ($object->{$key} !== $value) {
            return sprintf('%s->%s should be set to %s by default.', get_class($object), $key, $value);
        }
    }

    // Verify public properties with a default empty array
    foreach ($array_properties as $prop) {
        if (!property_exists($object, $prop)) {
		    return sprintf('%s->%s does not exist', get_class($object), $prop);
	    } elseif (!(is_array($object->{$prop}) && count($object->{$prop}) === 0)) {
            return sprintf('%s->%s should be an empty array by default.', get_class($object), $prop);
        }
    }

    // Verify public properties with a default 0 value
    foreach ($int_properties as $prop) {
        if (!property_exists($object, $prop)) {
		    return sprintf('%s->%s does not exist', get_class($object), $prop);
	    } elseif ($object->{$prop} !== 0) {
            return sprintf('%s->%s should be set to 0 by default.', get_class($object), $prop);
        }
    }

    // Use Reflection to check private properties
    foreach ($private_properties as $prop) {
	    if (!property_exists($object, $prop)) {
		    return sprintf('%s->%s does not exist', get_class($object), $prop);
	    } else {
            try {
	            $prop_info = new \ReflectionProperty($object, "{$prop}");
	            if (!$prop_info->isPrivate()) {
	                return sprintf('%s->%s should be defined as a private.', get_class($object), $prop);
	            }
            } catch (\Exception $e) {
	            return sprintf('%s->%s should be a private property. There was an error checking for it: %s', get_class($object), $prop, $e->getMessage());
            }
        }
    }
    
    // All checked properties sorted by name
    $all_properties = array_merge($null_properties, $true_properties, $false_properties, array_keys($string_properties), $array_properties, $int_properties, $private_properties);
    asort($all_properties);    

    // Use Reflection to check for any other properties not already checked
    $class = new \ReflectionClass($object);
    $props = $class->getProperties();
    foreach ($props as $prop) {
        if (!in_array($prop->getName(), $all_properties)) {
            return sprintf('%s->%s is a new property not yet handled in this unit test function.', get_class($object), $prop->getName());
        }
    }
    
    // Success
    return sprintf('All properties matched for [%s]: %s', get_class($object), implode(', ', $all_properties));
}

/**
 * Verify all Methods of a Class using Reflection
 *
 * @param mixed $object
 * @param array $private_methods
 * @param array $public_methods
 * @return string
 */
function checkObjectMethods($object, array $private_methods, array $public_methods)
{
    // Check that all specified private methods exist and are marked as private
    foreach ($private_methods as $method) {
        if (!method_exists($object, $method)) {
            return sprintf('%s->%s does not exist as a private method.', get_class($object), $method);
        }

        $method = new \ReflectionMethod($object, $method);
        if (!$method->isPrivate()) {
            return sprintf('%s->%s should be marked as private or the unit test needs to be updated.', get_class($object), $method);
        }
    }

    // Check public methods
    foreach ($public_methods as $method) {
        if (!method_exists($object, $method)) {
            return sprintf('%s->%s does not exist as a public method.', get_class($object), $method);
        }

        $method = new \ReflectionMethod($object, $method);
        if (!$method->isPublic()) {
            return sprintf('%s->%s should be marked as public or the unit test needs to be updated.', get_class($object), $method);
        }
    }

    // All checked functions sorted by name
    $all_methods = array_merge($private_methods, $public_methods);
    asort($all_methods);

    // Find any other methods not already checked
    $class = new \ReflectionClass($object);
    $methods = $class->getMethods();
    foreach ($methods as $method) {
        if (!in_array($method->name, $all_methods)) {
            return sprintf('%s->%s is a new method not yet handled in this unit test function.', get_class($object), $method->name);
        }
    }

    // Success
    return sprintf('All methods matched for [%s]: %s', get_class($object), implode(', ', $all_methods));
}
