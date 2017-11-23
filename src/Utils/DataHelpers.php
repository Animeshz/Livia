<?php
/**
 * Livia
 * Copyright 2017 Charlotte Dunois, All Rights Reserved
 *
 * Website: https://charuru.moe
 * License: https://github.com/CharlotteDunois/Livia/blob/master/LICENSE
*/

namespace CharlotteDunois\Livia\Utils;

/**
 * Data orientated helpers.
 */
class DataHelpers {
    /**
     * If a selection is ambiguous, this will make a list of selectable items.
     * @param array|\CharlotteDunois\Yasmin\Utils\Collection  $items
     * @param string                                          $label
     * @param string|null                                     $property
     * @return string
     */
    static function disambiguation($items, $label, $property = null) {
        if($items instanceof \CharlotteDunois\Yasmin\Utils\Collection) {
            $items = $items->all();
        }
        
        $itemList = \array_map(function ($item) use ($property) {
            if($property !== null) {
                $item = (\is_array($item) ? $item[$property] : $item->$property);
            }
            
            return '`'.\str_replace(' ', "\u{00A0}", $item).'`';
        }, $items);
        
        return 'Multiple '.$label.' found, please be more specific: '.\implode(', ', $itemList);
    }
}
