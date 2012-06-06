<?php
/**
 * Superampify
 *
 * @author eskerda
 * @copyright 2012 Interstel Com.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

class Utils{
    static function assocArrayToXML($base_xml,$ar) { 
        $f = create_function('$f,$c,$a',' 
                foreach($a as $k=>$v) { 
                    if(is_array($v)) {
                        $ch=$c->addChild($k); 
                        $f($f,$ch,$v); 
                    } else { 
                        $c->addAttribute($k,$v); 
                    } 
                }'); 
        $f($f,$base_xml,$ar); 
        return $base_xml;
    }

    static function simpleXMLToArray(SimpleXMLElement $xml,$attributesKey=null,$childrenKey=null,$valueKey=null){ 

        if($childrenKey && !is_string($childrenKey)){$childrenKey = '@children';} 
        if($attributesKey && !is_string($attributesKey)){$attributesKey = '@attributes';} 
        if($valueKey && !is_string($valueKey)){$valueKey = '@values';} 

        $return = array(); 
        $name = $xml->getName(); 
        $_value = trim((string)$xml); 
        if(!strlen($_value)){$_value = null;}; 

        if($_value!==null){ 
            if($valueKey){$return[$valueKey] = $_value;} 
            else{$return = $_value;} 
        } 

        $children = array(); 
        $first = true; 
        foreach($xml->children() as $elementName => $child){ 
            $value = self::simpleXMLToArray($child,$attributesKey, $childrenKey,$valueKey); 
            if(isset($children[$elementName])){ 
                if(is_array($children[$elementName])){ 
                    if($first){ 
                        $temp = $children[$elementName]; 
                        unset($children[$elementName]); 
                        $children[$elementName][] = $temp; 
                        $first=false; 
                    } 
                    $children[$elementName][] = $value; 
                }else{ 
                    $children[$elementName] = array($children[$elementName],$value); 
                } 
            } 
            else{ 
                $children[$elementName] = $value; 
            } 
        } 
        if($children){ 
            if($childrenKey){$return[$childrenKey] = $children;} 
            else{$return = array_merge($return,$children);} 
        } 

        $attributes = array(); 
        foreach($xml->attributes() as $name=>$value){ 
            $attributes[$name] = trim($value); 
        } 
        if($attributes){ 
            if($attributesKey){$return[$attributesKey] = $attributes;} 
            else{$return = array_merge($return, $attributes);} 
        } 

        return $return; 
    } 
}