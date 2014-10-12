<?php

namespace rock\cache\helpers;


use rock\cache\Exception;

class BaseJson
{
    /**
     * Validation value is json
     *
     * @param mixed $value value
     * @return bool
     */
    public static function is($value)
    {
        if (!is_string($value) || empty($value)) {
            return false;
        }
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Encodes the given value into a JSON string.
     * The method enhances `json_encode()`.
     * @param mixed $value the data to be encoded
     * @param integer $options the encoding options. For more details please refer to
     * <http://www.php.net/manual/en/function.json-encode.php>.
     * @return string the encoding result
     */
    public static function encode($value, $options = 0)
    {
        return json_encode($value, $options);
    }

    /**
     * Converting json to array
     *
     * @param string $json
     * @param bool   $asArray
     * @param bool   $throwException
     * @throws Exception
     * @return array|null
     */
    public static function decode($json, $asArray = true, $throwException = true)
    {
        if (empty($json)) {
            return null;
        }

        $decode = json_decode((string) $json, $asArray);

        if ($throwException === true) {
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    break;
                case JSON_ERROR_DEPTH:
                    throw new Exception('The maximum stack depth has been exceeded.');
                case JSON_ERROR_CTRL_CHAR:
                    throw new Exception('Control character error, possibly incorrectly encoded.');
                case JSON_ERROR_SYNTAX:
                    throw new Exception('Syntax error.');
                case JSON_ERROR_STATE_MISMATCH:
                    throw new Exception('Invalid or malformed JSON.');
                case JSON_ERROR_UTF8:
                    throw new Exception('Malformed UTF-8 characters, possibly incorrectly encoded.');
                default:
                    throw new Exception('Unknown JSON decoding error.');
            }
        } else {
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $json;
            }
        }

        return $decode;
    }
}