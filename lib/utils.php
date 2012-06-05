<?php

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
}