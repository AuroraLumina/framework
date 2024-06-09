<?php

/**
 * Validator class provides a set of static methods for validating various types of input values.
 */
class Validator
{
    /**
     * Checks if the given value is a number.
     *
     * @param mixed $value The value to be checked.
     * @return bool True if the value is a number, false otherwise.
     */
    public static function isNumber(mixed $value): bool
    {
        return is_numeric($value);
    }

    /**
     * Checks if the given value is a numeric value (float or int).
     *
     * @param mixed $value The value to be checked.
     * @return bool True if the value is a numeric value, false otherwise.
     */
    public static function isNumeric(mixed $value): bool
    {
        return is_float($value) || is_int($value) || (is_string($value) && filter_var($value, FILTER_VALIDATE_FLOAT) !== false);
    }

    /**
     * Checks if the given value is null.
     *
     * @param mixed $value The value to be checked.
     * @return bool True if the value is null, false otherwise.
     */
    public static function isNull(mixed $value): bool
    {
        return is_null($value);
    }

    /**
     * Checks if the given value is a valid email address.
     *
     * @param string $value The value to be checked.
     * @return bool True if the value is a valid email address, false otherwise.
     */
    public static function isEmail(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Checks if the given value is a valid URL.
     *
     * @param string $value The value to be checked.
     * @return bool True if the value is a valid URL, false otherwise.
     */
    public static function isUrl(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Checks if the given type exists as a class, interface, or trait.
     *
     * @param string $type The type to be checked.
     * @return bool True if the type exists, false otherwise.
     */
    public static function isType(string $type): bool
    {
        return class_exists($type) || interface_exists($type) || trait_exists($type);
    }
}