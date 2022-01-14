<?php

namespace App\Application;

interface Errors {
    const RESOURCE_NOT_FOUND = ['code' => 30, 'message' => 'Resource does not exist'];
    const USER_NOT_FOUND = ['code' => 31, 'message' => 'Target user does not exist'];
    
    
    const ALREADY_FRIENDS = 20;//['code' => 20, 'message' => 'Friendship is already accepted'];
    const MUTUAL_OFFER_RECEIVED = 21;//['code' => 21, 'Mutual friendship offer is already received from specified user'];
    const OFFER_ALREADY_SENT = 22;//['code' => 22, 'message' => 'Friendship already offered to specified user'];
    const OFFERED_FRIENDSHIP_TO_HIMSELF = 23;// ['code' => 23, 'It is impossible to offer friendship to himself'];
    
    const PROFILE_IS_HIDDEN = ['code' => 40, 'message' => 'Resource owner has hidden his profile from unauthenticated visitors'];
    //const PROFILE_IS_CLOSED = ['code' => 41, 'message' => 'User closed profile from non friends'];
    //const PROHIBITED_BY_PRIVACY = ['code' => 42, 'message' => 'Access(to action or resource) is restricted by privacy settings'];
    const BANNED_BY_OWNER = ['code' => 43, 'message' => 'Requester in blacklist of resource owner'];
    const BANNED_BY_TARGET_USER = ['code' => 44, 'message' => 'Requester in blacklist of target user'];
    const TARGET_USER_IN_BAN_LIST = ['code' => 45, 'message' => 'Target user in your blacklist'];
    
    const COMMENTS_DISABLED = ['code' => 46, 'message' => 'Comments disabled'];
    
    const RESOURCE_OWNER_IS_INACTIVE = ['code' => 47, 'message' => 'Resource is temporarily unaccessible. Owner of resource was suspended, deleted or blocked'];
    const SECTION_DISABLED = ['code' => 50, 'message' => 'Section in group is disabled'];

    
    const BANNED_BY_USER = 40;
    const PROHIBITED_BY_PRIVACY = 41;
    const LIMIT_REACHED = 3123;
    const LOL_KEK = 222;
    const NO_RIGHTS = 231231;
    const INACTIVE = 2;
    const AUTHENTICATED_USER_NOT_FOUND = 32;
    const ALREADY_SUBSCRIBED = 33;
    const SOFTLY_DELETED = 34;
    
    const EDIT_TIME_EXPIRED = 60;
    
    const EMAIL_TAKEN = 70;
    const USERNAME_TAKEN = 71;
    const EMAIL_OR_USERNAME_TAKEN = 72;
    
    const ALREADY_REACTED = 80;
}
