<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to version 1.0 of the Zend Framework
 * license, that is bundled with this package in the file LICENSE, and
 * is available through the world-wide-web at the following URL:
 * http://www.zend.com/license/framework/1_0.txt. If you did not receive
 * a copy of the Zend Framework license and are unable to obtain it
 * through the world-wide-web, please send a note to license@zend.com
 * so we can mail you a copy immediately.
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */


/** Zend_Pdf_Element */
require_once 'Zend/Pdf/Element.php';


/**
 * PDF file 'string' element implementation
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */
class Zend_Pdf_Element_String extends Zend_Pdf_Element
{
    /**
     * Object value
     *
     * @var string
     */
    public $value;

    /**
     * Object constructor
     *
     * @param string $val
     */
    public function __construct($val)
    {
        $this->value   = (string)$val;
    }


    /**
     * Return type of the element.
     *
     * @return integer
     */
    public function getType()
    {
        return Zend_Pdf_Element::TYPE_STRING;
    }


    /**
     * Return object as string
     *
     * @param Zend_Pdf_Factory $factory
     * @return string
     */
    public function toString($factory = null)
    {
        return '(' . self::escape((string)$this->value) . ')';
    }


    /**
     * Escape string according to the PDF rules
     *
     * @param string $inStr
     * @return string
     */
    static public function escape($inStr)
    {
        $outStr = '';
        $lastNL = 0;

        for ($count = 0; $count < strlen($inStr); $count++) {
            if (strlen($outStr) - $lastNL > 128)  {
                $outStr .= "\\\n";
                $lastNL = strlen($outStr);
            }

            $nextCode = ord($inStr{$count});
            switch ($nextCode) {
                // "\n" - line feed (LF)
                case 10:
                    $outStr .= '\\n';
                    break;

                // "\r" - carriage return (CR)
                case 13:
                    $outStr .= '\\r';
                    break;

                // "\t" - horizontal tab (HT)
                case 9:
                    $outStr .= '\\t';
                    break;

                // "\b" - backspace (BS)
                case 8:
                    $outStr .= '\\b';
                    break;

                // "\f" - form feed (FF)
                case 12:
                    $outStr .= '\\f';
                    break;

                // '(' - left paranthesis
                case 40:
                    $outStr .= '\\(';
                    break;

                // ')' - right paranthesis
                case 41:
                    $outStr .= '\\)';
                    break;

                // '\' - backslash
                case 92:
                    $outStr .= '\\\\';
                    break;

                default:
                    if ($nextCode >= 32 && $nextCode <= 126 ) {
                        // Visible ASCII symbol
                        $outStr .= $inStr{$count};
                    } else {
                        $outStr .= sprintf('\\%03o', $nextCode);
                    }
                    break;
            }
        }

        return $outStr;
    }


    /**
     * Unescape string according to the PDF rules
     *
     * @param string $inStr
     * @return string
     */
    static public function unescape($inStr)
    {
        $outStr = '';

        for ($count = 0; $count < strlen($inStr); $count++) {
            if ($inStr{$count} != '\\' || $count == strlen($inStr)-1)  {
                $outStr .= $inStr{$count};
            } else { // Escape sequence
                switch ($inStr{++$count}) {
                    // '\\n' - line feed (LF)
                    case 'n':
                        $outStr .= "\n";
                        break;

                    // '\\r' - carriage return (CR)
                    case 'r':
                        $outStr .= "\r";
                        break;

                    // '\\t' - horizontal tab (HT)
                    case 't':
                        $outStr .= "\t";
                        break;

                    // '\\b' - backspace (BS)
                    case 'b':
                        $outStr .= "\x08";
                        break;

                    // '\\f' - form feed (FF)
                    case 'f':
                        $outStr .= "\x0C";
                        break;

                    // '\\(' - left paranthesis
                    case '(':
                        $outStr .= '(';
                        break;

                    // '\\)' - right paranthesis
                    case ')':
                        $outStr .= ')';
                        break;

                    // '\\\\' - backslash
                    case '\\':
                        $outStr .= '\\';
                        break;

                    // "\\\n" or "\\\n\r"
                    case "\n":
                        // skip new line symbol
                        if ($inStr{$count+1} == "\r") {
                            $count++;
                        }
                        break;

                    default:
                        if (ord($inStr{$count}) >= ord('0') &&
                            ord($inStr{$count}) <= ord('9')) {
                            // Character in octal representation
                            // '\\xxx'
                            $nextCode = '0' . $inStr{$count};

                            if (ord($inStr{$count+1}) >= ord('0') &&
                                ord($inStr{$count+1}) <= ord('9')) {
                                $nextCode .= $inStr{++$count};

                                if (ord($inStr{$count+1}) >= ord('0') &&
                                    ord($inStr{$count+1}) <= ord('9')) {
                                    $nextCode .= $inStr{++$count};
                                }
                            }

                            $outStr .= chr($nextCode);
                        } else {
                            $outStr .= $inStr{$count};
                        }
                        break;
                }
            }
        }

        return $outStr;
    }

}

