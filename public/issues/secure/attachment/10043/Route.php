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
 * @package    Zend_Controller
 * @subpackage Router
 * @copyright  Copyright (c) 2005-2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */

/** Zend_Controller_Router_Exception */
require_once 'Zend/Controller/Router/Route/Interface.php';

/**
 * Route
 *
 * @package    Zend_Controller
 * @subpackage Router
 * @copyright  Copyright (c) 2005-2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 * @see        http://manuals.rubyonrails.com/read/chapter/65
 */
class Zend_Controller_Router_Route implements Zend_Controller_Router_Route_Interface
{
    
    const URL_VARIABLE = ':';
    const REGEX_DELIMITER = '#';
    const DEFAULT_REGEX = '[a-z0-9\-\_\.\~\%]+';

    protected $_parts = array();
    protected $_defaults;
    protected $_requirements;

    public function __construct($map, array $defaults = array(), array $requirements = array())
    {
        $this->_defaults     = $defaults;
        $this->_requirements = $requirements;
        
        $pattern = '#\{*?(\:[a-zA-Z0-9_\x7f-\xff]+)\}*#'; // map vars follow similar naming rules to PHP $vars
        $mapParts = preg_split($pattern, trim($map, '/'), -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        
        foreach ($mapParts as $mapPart) {
            if ($mapPart[0] === ':') { // mapPart is a variable
                $mapPart = ltrim($mapPart, ':');
                $this->_parts[] = array(
                    'type'  => 'var',
                    'name'  => $mapPart,
                    'regex' => (isset($requirements[$mapPart]) ? $requirements[$mapPart] : self::DEFAULT_REGEX));
            } else { // mapPart is part of the path
                $this->_parts[] = array(
                    'type'  => 'path',
                    'name'  => $mapPart,
                    'regex' => preg_quote($mapPart, self::REGEX_DELIMITER));
            }
        }
    }
    
    
    public function match($path)
    {
        $path = trim($path, '/');
        $out = $this->_defaults;
        $offset = 0;
        
        for ($i=0, $partsCount=count($this->_parts); $i<$partsCount; $i++) {
            
            $part = $this->_parts[$i];
            
            if ($part['type'] === 'var') { // if the current part is a var try a regex match
                
                if (isset($this->_parts[$i+1])) {
                    $nextPart = $this->_parts[$i+1];
                    if ($nextPart['type'] === 'var') {
                        if (array_key_exists($nextPart['name'], $this->_defaults)) $optionalRegex = '|$';
                        else $optionalRegex = '';
                    } elseif ($nextPart['name'] === '/'  && // the next path is optional only if it is a /
                              isset($this->_parts[$i+2]) && // and the var after it has a default value
                              array_key_exists($this->_parts[$i+2]['name'], $this->_defaults)) {
                        $optionalRegex = '|$';
                    } else $optionalRegex = ''; // next path is required
                } else {
                    // if nothing is after this part we do not account for optional vars/paths
                    $nextPart['regex'] = '';
                    $optionalRegex = '';
                }
                
                $regex = self::REGEX_DELIMITER
                       . '^(' . $part['regex'] . ')'
                       . '(' . $nextPart['regex'] . $optionalRegex . ')'
                       . self::REGEX_DELIMITER . 'i';
                
                if (preg_match($regex, $path, $matches, PREG_OFFSET_CAPTURE)) {
                    $out[$part['name']] = $matches[1][0];
                    $offset = strlen($matches[1][0]);
                    if ($matches[2][0] !== '') { // if preg_match found the next part, process it and skip over the next loop
                        if ($nextPart['type'] === 'var') {
                            $out[$nextPart['name']] = $matches[2][0];
                            $offset += strlen($matches[2][0]);
                        } else $offset += strlen($nextPart['name']);
                        $i++;
                    }
                } elseif (array_key_exists($part['name'], $this->_defaults) && !isset($this->_requirements[$part['name']])) {
                    continue;
                } else return false;
                
            } else { // if the current part is a path try a string comparison
                $nameLength = strlen($part['name']);
                if ($part['name'] === substr($path, $offset, $nameLength)) {
                    $offset = $nameLength;
                } elseif ($part['name'] === '/' &&
                          isset($this->_parts[$i+1]) &&
                          array_key_exists($this->_parts[$i+1]['name'], $this->_defaults)) {
                    break;
                } else return false;
            }
            
            $path = substr($path, $offset);
            $offset = 0;
        }
        
        return ($path !== false) ? false : $out;
    }
    
    
    public function assemble($data = array())
    {
        $out = '';
        foreach ($this->_parts as $part) {
            if ($part['type'] === 'var') {
                if (isset($data[$part['name']])) {
                    $out .= $data[$part['name']];
                } elseif (isset($this->_defaults[$part['name']])) {
                    $out .= $this->_defaults[$part['name']];
                } else throw new Zend_Controller_Router_Exception($part['name'] . ' is not specified');
            } else $out .= $part['name'];
        }
        return $out;
    }
    
}