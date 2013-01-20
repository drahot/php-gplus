<?php

namespace Gplus;

/**
 * JSON Helper Function Class
 * @author drahot
 */
final class JSONHelper
{
    
    /**
     * private constructor
     * @return void
     */ 
    private function __construct()
    {
    }   
    
    /**
     * JSON encode
     * @param array $data 
     * @return string
     */ 
    public static function encode(array $data)
    {
        $jsonStr = json_encode($data);
        return self::fixJSON($jsonStr);
    }

    /**
     * JSON decode
     * @param string $jsonStr 
     * @return array
     */
    public static function decode($jsonStr)
    {
        $pos = strpos($jsonStr, "\n\n");
        if ($pos !== false) {
            $jsonStr = substr($jsonStr, $pos + 2);
        }
        $strMode = 0;
        $jsonName = false;
        $c = 0;
        while (true) {
            if ($c >= strlen($jsonStr)) {
                break;
            }

            if ((substr($jsonStr, $c - 1, 1) !== "\\" || substr($jsonStr, $c - 2, 2) !== "\\\\") && 
                $jsonStr[$c] === "\"") {
                $strMode = $strMode ^ 1;
                ++$c;
                continue;
            }

            if ($strMode == 0) {
                // echo $c, PHP_EOL;
                // echo $c+2, PHP_EOL;
                // echo substr($jsonStr, $c, 2), PHP_EOL;
                if (in_array(substr($jsonStr, $c, 2), array(",,", "[,", ",]"))) {
                    $jsonStr = substr($jsonStr, 0, $c + 1) . '"null"'. substr($jsonStr, $c + 1);
                    $c += 2 + 4;
                    $c += 1;
                    continue;
                }
                if (in_array(substr($jsonStr, $c, 6), array(",true,", "[true,", ",true]", "[true]"))) {
                    $jsonStr = substr($jsonStr, 0, $c + 1) . '"true"'. substr($jsonStr, $c + 1 + 4);
                    $c += 2 + 4;
                    $c += 1;
                    continue;
                }
                if (in_array(substr($jsonStr, $c, 7), array(",false,", "[false,", ",false]", "[false]"))) {
                    $jsonStr = substr($jsonStr, 0, $c + 1) . '"true"'. substr($jsonStr, $c + 1 + 5);
                    $c += 2 + 5;
                    $c += 1;
                    continue;
                }
                if ($jsonStr[$c] === '{' && $jsonStr[$c + 1] !== '"') {
                    $jsonStr = substr($jsonStr, 0, $c + 1) . '"' . substr($jsonStr, $c + 1);
                    $jsonName = true;
                    $c += 2;
                    continue;
                }
                if ($jsonName && $jsonStr[$c] === ':' && $jsonStr[$c - 1] !== '"') {
                    $jsonStr = substr($jsonStr, 0, $c) . '"' . substr($jsonStr, $c);
                    $jsonName = false;
                    $c += 2;
                    continue;
                }
            }
            ++$c;
        }
        return json_decode($jsonStr);
    }

    /**
     * Fix JSON 
     * @param string $jsonStr
     * @return JSON
     */ 
    public static function fixJSON($jsonStr)
    {
        $jsonStr = str_replace('"null"', "null", $jsonStr);
        $jsonStr = str_replace('"false"', "false", $jsonStr);
        return str_replace('"true"', "true", $jsonStr);
    }

}