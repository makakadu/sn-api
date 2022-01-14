<?php
declare(strict_types=1);
namespace App\Domain\Model\Users;

interface AccessLevels {
    const NOBODY = 0;
    const SOME_CONNECTIONS_AND_LISTS = 1;
    const CONNECTIONS = 2;
    const CONNECTIONS_OF_CONNECTIONS = 3;
    const EVERYONE = 4;  
    
//    const ALL_USERS_APART = '6.1';
//    const FRIENDS_APART = '6.2';
//    const FRIENDS_AND_THEIR_FRIENDS_APART = '6.3';
//    const HIDDEN_FRIENDS_APART = '6.4';
//    const SOME_FRIENDS_APART = '6.5';
    
//    const ALL_USERS = '1';
//    const FRIENDS = '2';
//    const FRNDS_AND_THEIR_FRNDS = '3';
//    const ONLY_I_OR_NOBODY = '4';
//    const SOME_FRIENDS = '5';
//    const SOME_FRIEND_LISTS = '6';
//    const HIDDEN_FRIENDS = '7';
//    const FRNDS_OF_FRNDS = '8';
}
