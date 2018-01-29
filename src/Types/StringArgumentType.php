<?php
/**
 * Livia
 * Copyright 2017-2018 Charlotte Dunois, All Rights Reserved
 *
 * Website: https://charuru.moe
 * License: https://github.com/CharlotteDunois/Livia/blob/master/LICENSE
*/

namespace CharlotteDunois\Livia\Types;

/**
 * @inheritDoc
 * @internal
 */
class StringArgumentType extends ArgumentType {
    /**
     * @internal
     */
    function __construct(\CharlotteDunois\Livia\LiviaClient $client) {
        parent::__construct($client, 'string');
    }
    
    /**
     * @inheritDoc
     */
    function validate(string $value, \CharlotteDunois\Livia\CommandMessage $message, \CharlotteDunois\Livia\Arguments\Argument $arg = null) {
        if(\mb_strlen($value) === 0) {
            return false;
        }
        
        if($arg->min !== null && \mb_strlen($value) < $arg->min) {
            return 'Please enter something above or exactly '.$arg->min.' characters in length.';
        }
        
        if($arg->max !== null && \mb_strlen($value) > $arg->max) {
            return 'Please enter a number below or exactly '.$arg->max.' characters in length.';
        }
        
        return true;
    }
    
    /**
     * @inheritDoc
     */
    function parse(string $value, \CharlotteDunois\Livia\CommandMessage $message, \CharlotteDunois\Livia\Arguments\Argument $arg = null) {
        return $value;
    }
}
