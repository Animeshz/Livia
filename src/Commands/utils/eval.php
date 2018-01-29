<?php
/**
 * Livia
 * Copyright 2017-2018 Charlotte Dunois, All Rights Reserved
 *
 * Website: https://charuru.moe
 * License: https://github.com/CharlotteDunois/Livia/blob/master/LICENSE
*/

return function ($client) {
    return (new class($client) extends \CharlotteDunois\Livia\Commands\Command {
        protected $timeformats = array('µs', 'ms');
        protected $lastResult;
        
        function __construct(\CharlotteDunois\Livia\LiviaClient $client) {
            parent::__construct($client, array(
                'name' => 'eval',
                'aliases' => array(),
                'group' => 'utils',
                'description' => 'Executes PHP code.',
                'guildOnly' => false,
                'ownerOnly' => true,
                'argsSingleQuotes' => false,
                'args' => array(
                    array(
                        'key' => 'script',
                        'prompt' => 'What is the fancy code you wanna run?',
                        'type' => 'string'
                    )
                ),
                'guarded' => true
            ));
        }
        
        function run(\CharlotteDunois\Livia\CommandMessage $message, \ArrayObject $args, bool $fromPattern) {
            $messages = array();
            $prev = null;
            
            return (new \React\Promise\Promise(function (callable $resolve, callable $reject) use ($message, $args, &$messages, &$prev) {
                $code = $args['script'];
                if(\mb_substr($code, -1) !== ';') {
                    $code .= ';';
                }
                
                if(\mb_strpos($code, 'return') === false && \mb_strpos($code, 'echo') === false) {
                    $code = \explode(';', $code);
                    $code[(\count($code) - 2)] = PHP_EOL.'return '.\trim($code[(\count($code) - 2)]);
                    $code = \implode(';', $code);
                }
                
                \React\Promise\resolve()->then(function () use ($code, $message, &$messages, &$prev) {
                    $messages = array();
                    $time = null;
                    
                    $doCallback = function ($result) use ($code, $message, &$messages, &$time) {
                        $endtime = \microtime(true);
                        
                        $result = $this->invokeDump($result);
                        
                        $len = \mb_strlen($result);
                        $maxlen = 1850 - \mb_strlen($code);
                        
                        if($len > $maxlen) {
                            $result = \mb_substr($result, 0, $maxlen).PHP_EOL.'...';
                        }
                        
                        $sizeformat = \count($this->timeformats) - 1;
                        $format = 0;
                        
                        $exectime = $endtime - $time;
                        while($exectime < 1.0 && $format < $sizeformat) {
                            $exectime *= 1000;
                            $format++;
                        }
                        $exectime = \ceil($exectime);
                        
                        $messages[] = $message->say($message->message->author.\CharlotteDunois\Yasmin\Models\Message::$replySeparator.'Executed after '.$exectime.$this->timeformats[$format].' (callback).'.PHP_EOL.PHP_EOL.'```php'.PHP_EOL.$result.PHP_EOL.'```'.($len > $maxlen ? PHP_EOL.'Original length: '.$len : ''));
                    };
                    
                    $prev = \set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
                    });
                    
                    $endtime = null;
                    $time = \microtime(true);
                    
                    $evalcode = 'namespace CharlotteDunois\\Livia\\Commands\\EvalNamespace\\'.\preg_replace('/[^a-z]/i', '', \bin2hex(\random_bytes(10)).\sha1(\time())).';'.
                                    PHP_EOL.$code;
                    
                    $result = eval($evalcode);
                    
                    if(!($result instanceof \React\Promise\PromiseInterface)) {
                        $endtime = \microtime(true);
                        $result = \React\Promise\resolve($result);
                    }
                    
                    \set_error_handler($prev);
                    
                    return $result->then(function ($result) use ($code, $message, &$messages, $endtime, $time) {
                        if($endtime === null) {
                            $endtime = \microtime(true);
                        }
                        
                        $this->lastResult = $result;
                        $result = $this->invokeDump($result);
                        
                        $len = \mb_strlen($result);
                        $maxlen = 1850 - \mb_strlen($code);
                        
                        if($len > $maxlen) {
                            $result = \mb_substr($result, 0, $maxlen).PHP_EOL.'...';
                        }
                        
                        $sizeformat = \count($this->timeformats) - 1;
                        $format = 0;
                        
                        $exectime = $endtime - $time;
                        while($exectime < 1.0 && $format < $sizeformat) {
                            $exectime *= 1000;
                            $format++;
                        }
                        $exectime = \ceil($exectime);
                        
                        $messages[] = $message->say($message->message->author.\CharlotteDunois\Yasmin\Models\Message::$replySeparator.'Executed in '.$exectime.$this->timeformats[$format].'.'.PHP_EOL.PHP_EOL.'```php'.PHP_EOL.$result.PHP_EOL.'```'.($len > $maxlen ? PHP_EOL.'Original length: '.$len : ''));
                        return $messages;
                    });
                })->then(function ($pr) {
                    return $pr;
                }, function ($e) use ($code, $message, &$messages, &$prev) {
                    \set_error_handler($prev);
                    
                    $e = (string) $e;
                    $len = \mb_strlen($e);
                    $maxlen = 1900 - \mb_strlen($code);
                    
                    if($len > $maxlen) {
                        $e = \mb_substr($e, 0, $maxlen).PHP_EOL.'...';
                    }
                    
                    $messages[] = $message->say($message->message->author.PHP_EOL.'```php'.PHP_EOL.$code.PHP_EOL.'```'.PHP_EOL.'Error: ```'.PHP_EOL.$e.PHP_EOL.'```');
                    return $messages;
                })->then($resolve, $reject)->done(null, array($this->client, 'handlePromiseRejection'));
            }));
        }
        
        function invokeDump($result) {
            \ob_start('mb_output_handler');
            
            $old = \ini_get('xdebug.var_display_max_depth');
            \ini_set('xdebug.var_display_max_depth', 1);
            
            \var_dump($result);
            \ini_set('xdebug.var_display_max_depth', $old);
            $result = @\ob_get_clean();
            
            if(\extension_loaded('xdebug')) {
                $result = \explode("\n", \str_replace("\r", "", $result));
                \array_shift($result);
                $result = \implode(PHP_EOL, $result);
            }
            
            while(@\ob_end_clean());
            
            $email = $this->client->user->email;
            $tokenregex = preg_quote($this->client->token, '/');
            $emailregex = (!empty($email) ? \preg_quote($email, '/') : null);
            
            $result = \preg_replace('/string\(\d+\) "'.$tokenregex.'"'.($emailregex !== null ? '|string\(\d+\) "'.$emailregex.'"' : '').'/iu', 'string(10) "[redacted]"', $result);
            $result = \preg_replace('/'.$tokenregex.($emailregex !== null ? '|'.$emailregex : '').'/iu', '[redacted]', $result);
            
            return $result;
        }
    });
};
