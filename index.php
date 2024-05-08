<?php

    require 'functions.php';
    
    $content = file_get_contents('php://input');
    $update = json_decode($content, true);

    $chat_id = $update['message']['chat']['id'];
    $text = $update['message']['text'];
    $message_id = $update['message']['message_id'];
    $message = $update['message']['text'];

    $callback_chat_id = $update['callback_query']['message']['chat']['id'];
    $callback_data = $update['callback_query']['data'];
    $callback_message_id = $update['callback_query']['message']['message_id'];
    $callback_message = $update['callback_query']['message']['text'];
    $bot_id = $update['callback_query']['from']['id'];

    session_id(str_pad(($chat_id), 13, 'F'));
    session_start();
    
    switch($text){
        case '/start':
        case 'Home | 🏠':
            session_destroy();
            msg('sendMessage' ,array('chat_id'=>$chat_id, 'text'=>'Please Wait... ⏳', 'reply_markup' => keyboard('remove')));
            msg(
                'sendMessage',
                array(
                    'chat_id'=>$chat_id,
                    'text'=>text('welcome'),
                    'reply_markup'=>keyboard('home')
                )
            );
            break;
        case 'Change Category | 🔄️':
            $_SESSION['type'] = 'cstm';
            // $_SESSION['cats'] = '';
            msg('sendMessage' ,array('chat_id'=>$chat_id, 'text'=>'Please Wait... ⏳', 'reply_markup' => keyboard('remove')));
            msg(
                'sendMessage',
                array(
                    'chat_id'=>$chat_id,
                    'text'=> text('custom_cat'),
                    'reply_markup'=>keyboard('cat')
                )
            );
            break;
        // default:
        //     if($_SESSION['type'] == 'cstm'){
        //         $cat = $_SESSION['cats'];
        //         $joke = Joke($cat, $text);
        //         $jokeCat = $joke['category'];
        //         $jokeText = $joke['joke'];
        //         msg(
        //             'sendMessage',
        //             array(
        //                 'chat_id'=>$chat_id,
        //                 'text'=> JokeMsg($jokeCat, $jokeText)
        //             )
        //         );
        //         msg(
        //             'sendMessage',
        //             array(
        //                 'chat_id'=>$chat_id,
        //                 'text'=> text('custom_text'),
        //             )
        //         );
        //     }
        //     break;
    }
    
    switch ($callback_data) {
        case 'home':
            session_destroy();
            msg(
                'editMessageText',
                array(
                    'chat_id'=>$callback_chat_id,
                    'message_id'=>$callback_message_id,
                    'text'=>text('welcome'),
                    'reply_markup'=>keyboard('home')
                )
            );
            break;
        case 'random':
            $_SESSION['type'] = 'random';
            msg(
                'editMessageText',
                array(
                    'chat_id'=>$callback_chat_id,
                    'message_id'=>$callback_message_id,
                    'text'=> text('random'),
                    'reply_markup'=>keyboard('cat')
                )
            );
            break;
        case 'custom':
            $_SESSION['type'] = 'cstm';
            msg(
                'editMessageText',
                array(
                    'chat_id'=>$callback_chat_id,
                    'message_id'=>$callback_message_id,
                    'text'=> text('custom_cat'),
                    'reply_markup'=>keyboard('cat')
                )
            );
            break;
        case 'info':
            msg(
                'editMessageText',
                array(
                    'chat_id'=>$callback_chat_id,
                    'message_id'=>$callback_message_id,
                    'text'=> text('info'),
                    'link_preview_options' => ['url' => 'https://t.me/Meytttii'],
                    'reply_markup'=>keyboard('info')
                )
            );
        default:
            if ($_SESSION['type'] == 'random') {
                msg(
                    'deleteMessage',
                    array(
                        'chat_id'=>$callback_chat_id,
                        'message_id'=>$callback_message_id
                    )
                );
                $cat = $callback_data;
                $text = '';
                $joke = Joke($cat, $text);
                $error = $joke['error'];
                $jokeCat = $joke['category'];
                $jokeText = $joke['joke'];
                switch ($error){
                    case 'true':
                        msg(
                            'sendMessage',
                            array(
                                'chat_id'=>$callback_chat_id,
                                'text'=> text('random_error'),
                            )
                        );
                        break;
                    default:
                        msg(
                            'sendMessage',
                            array(
                                'chat_id'=>$callback_chat_id,
                                'text'=> JokeMsg($jokeCat, $jokeText)
                            )
                        );
                        break;
                }
                msg(
                    'sendMessage',
                    array(
                        'chat_id'=>$callback_chat_id,
                        'text'=> text('random'),
                        'reply_markup'=>keyboard('cat')
                    )
                );
            }
            elseif ($_SESSION['type'] == 'cstm') {
                $_SESSION['cats'] = $callback_data;
                msg(
                    'deleteMessage',
                    array(
                        'chat_id'=>$callback_chat_id,
                        'message_id'=>$callback_message_id
                    )
                );
                msg(
                    'sendMessage',
                    array(
                        'chat_id'=>$callback_chat_id,
                        'text'=> text('custom_text'),
                        'reply_markup'=>keyboard('custom_text')
                    )
                );
            }
            break; 
    }
?>