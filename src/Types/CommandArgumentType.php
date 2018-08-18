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
 * {@inheritdoc}
 * @internal
 */
class CommandArgumentType extends ArgumentType {
    /**
     * @internal
     */
    function __construct(\CharlotteDunois\Livia\LiviaClient $client) {
        parent::__construct($client, 'command');
    }
    
    /**
     * {@inheritdoc}
     * @return bool|string|\React\Promise\ExtendedPromiseInterface
     */
    function validate(string $value, \CharlotteDunois\Livia\CommandMessage $message, ?\CharlotteDunois\Livia\Arguments\Argument $arg = null) {
        $commands = $this->client->registry->findCommands($value);
        if(\count($commands) === 1) {
            return true;
        }
        
        if(\count($commands) === 0) {
            return false;
        }
        
        return \CharlotteDunois\Livia\Utils\DataHelpers::disambiguation($commands, 'commands', 'name').\PHP_EOL;
    }
    
    /**
     * {@inheritdoc}
     * @return mixed|null|\React\Promise\ExtendedPromiseInterface
     */
    function parse(string $value, \CharlotteDunois\Livia\CommandMessage $message, ?\CharlotteDunois\Livia\Arguments\Argument $arg = null) {
        $commands = $this->client->registry->findCommands($value);
        if(\count($commands) > 0) {
            return $commands[0];
        }
        
        return null;
    }
}
