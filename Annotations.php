<?php
/*
 * This file is part of the phpalchemy package.
 *
 * (c) Erik Amaru Ortiz <aortiz.erik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Lib\Util;



/**
 * Class Annotations
 *
 * @version   1.0
 * @author    Erik Amaru Ortiz <aortiz.erik@gmail.com>
 * @link      https://github.com/eriknyk/phpalchemy
 * @copyright Copyright 2012 Erik Amaru Ortiz
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @package   phpalchemy
 * @depends   Notoj\*
 */
class Annotations
{
    private static $annotationCache;

    public $defaultNamespace = '';

    public function __construct($config = null)
    {
        if ($config instanceof \Alchemy\Config) {
            //\Notoj\Notoj::enableCache($config->get('app.cache_dir') . DIRECTORY_SEPARATOR . "_annotations.php");
        }
    }

    public function setDefaultAnnotationNamespace($namespace)
    {
        $this->defaultNamespace = $namespace;
    }

    public static function getClassAnnotations($className)
    {
        if (!isset(self::$annotationCache[$className])) {
            $class = new \ReflectionClass($className);
            self::$annotationCache[$className] = self::parseAnnotations($class->getDocComment());
        }

        return self::$annotationCache[$className];
    }

    public static function getMethodAnnotations($className, $methodName)
    {
        if (!isset(self::$annotationCache[$className . '::' . $methodName])) {
            try {
                $method = new \ReflectionMethod($className, $methodName);
                $annotations = self::parseAnnotations($method->getDocComment());
            } catch (\ReflectionException $e) {
                $annotations = array();
            }

            self::$annotationCache[$className . '::' . $methodName] = $annotations;
        }

        return self::$annotationCache[$className . '::' . $methodName];
    }

    public function getMethodAnnotationsObjects($className, $methodName)
    {
        $annotations = $this->getMethodAnnotations($className, $methodName);
        $objects     = array();

        foreach ($annotations as $annotationClass => $listParams) {
            $annotationClass = ucfirst($annotationClass);
            $class = $this->defaultNamespace . $annotationClass . 'Annotation';

            if (empty($objects[$annotationClass])) {
                if (!class_exists($class)) {
                    throw new \Exception(sprintf('Annotation Class Not Found: %s', $class));
                }

                $objects[$annotationClass] = new $class();
            }

            foreach ($listParams as $params) {
                if (is_array($params)) {
                    foreach ($params as $key => $value) {
                        $objects[$annotationClass]->set($key, $value);
                    }
                } else {
                    $objects[$annotationClass]->set(0, $params);
                }
            }
        }

        return $objects;
    }

    /**
     * @param  string $docblock
     * @return array
     * @since  Method available since Release 3.4.0
     */
    private static function parseAnnotations($docblock)
    {
        $annotations = array();

        // Strip away the docblock header and footer to ease parsing of one line annotations
        $docblock = substr($docblock, 3, -2);

        if (preg_match_all('/@(?<name>[A-Za-z_-]+)[\s\t]*\((?<args>.*)\)[\s\t]*\r?$/m', $docblock, $matches)) {
            $numMatches = count($matches[0]);

            for ($i = 0; $i < $numMatches; ++$i) {
                if (isset($matches['args'][$i])) { // annotations has arguments
                    $argsParts = trim($matches['args'][$i]);
                    $name      = $matches['name'][$i];
                    $value     = self::parseArgs($argsParts);
                } else {
                    $value = array();
                }

                $annotations[$name][] = $value;
            }

        }

        return $annotations;
    }


    private static function parseArgs($content)
    {
        $data  = array();
        $len   = strlen($content);
        $i     = 0;
        $var   = '';
        $val   = '';
        $level = 1;

        $prevDelimiter = '';
        $nextDelimiter = '';
        $nextToken     = '';
        $composing     = false;
        $type          = 'plain';
        $delimiter     = null;
        $quoted        = false;
        $tokens        = array('"', '"', '{', '}', ',', '=');

        while ($i <= $len) {
            $c = substr($content, $i++, 1);

            if ($c === '\'' || $c === '"') {
                $delimiter = $c;

                if (!$composing && empty($prevDelimiter) && empty($nextDelimiter)) { //open delimiter
                    $prevDelimiter = $nextDelimiter = $delimiter;
                    $val           = '';
                    $composing     = true;
                    $quoted        = true;
                } else { // close delimiter
                    if ($c !== $nextDelimiter) {
                        throw new \InvalidArgumentException("Parse Error: enclosing error -> expected: [$nextDelimiter], given: [".$c."]");
                    }

                    // validating sintax
                    if ($i < $len) {
                        if (',' !== substr($content, $i, 1)) {
                            throw new \InvalidArgumentException("Parse Error: missing comma separator near: ...".substr($content, ($i-10), $i)."<--");
                        }
                    }

                    $prevDelimiter = $nextDelimiter = '';
                    $composing     = false;
                    $delimiter     = null;
                }
            } elseif (!$composing && in_array($c, $tokens)) {
                switch ($c) {
                    case '=':
                        $prevDelimiter = $nextDelimiter = '';
                        $level     = 2;
                        $composing = false;
                        $type      = 'assoc';
                        $quoted = false;
                        break;

                    case ',':
                        $level = 3;

                        // If composing flag is true yet, it means that the string was not enclosed, so it is parsing error.
                        if ($composing === true && !empty($prevDelimiter) && !empty($nextDelimiter)) {
                            throw new \InvalidArgumentException("Parse Error: enclosing error -> expected: [$nextDelimiter], given: [".$c."]");
                        }

                        $prevDelimiter = $nextDelimiter = '';
                        break;

                    case '{':
                        $subc = '';
                        $subComposing = true;

                        while ($i <= $len) {
                            $c = substr($content, $i++, 1);

                            if (isset($delimiter) && $c === $delimiter) {
                                throw new \InvalidArgumentException("Parse Error: Composite variable is not enclosed correctly.");
                            }

                            if ($c === '}') {
                                $subComposing = false;
                                break;
                            }
                            $subc .= $c;
                        }

                        // if the string is composing yet means that the structure of var. never was enclosed with '}'
                        if ($subComposing) {
                            throw new \InvalidArgumentException("Parse Error: Composite variable is not enclosed correctly. near: ...".$subc."'");
                        }

                        $val = self::parseArgs($subc);
                        break;
                }
            } else {
                if ($level == 1) {
                    $var .= $c;
                } elseif ($level == 2) {
                    $val .= $c;
                }
            }

            if ($level === 3 || $i === $len) {
                if ($type == 'plain' && $i === $len) {
                    $data = self::castValue($var);
                } else {
                    $data[trim($var)] = self::castValue($val, !$quoted);
                }

                $level = 1;
                $var   = $val = '';
                $composing = false;
                $quoted = false;
            }
        }

        return $data;
    }

    private static function castValue($val, $trim = false)
    {
        if (is_array($val)) {
            foreach ($val as $key => $value) {
                $val[$key] = self::castValue($value);
            }
        } elseif (is_string($val)) {
            if ($trim) {
                $val = trim($val);
            }

            $tmp = strtolower($val);

            if ($tmp === 'false' || $tmp === 'true') {
                $val = $tmp === 'true';
            } elseif (is_numeric($val)) {
                return $val + 0;
            }

            unset($tmp);
        }

        return $val;
    }
}












