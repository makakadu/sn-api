<?php
declare(strict_types=1);
namespace App\Application\Users;

use App\Domain\Model\Users\Privacy;
use Assert\Assertion;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\Exceptions\MalformedRequestException;

trait PrivacyAppServiceTrait {
    protected function failIfIncorrectPrivacyStructure(string $privacySettingName, $privacySettingData): void {
        try {
            Assertion::isArray($privacySettingData, "'$privacySettingName' parameter should be an array");
            Assertion::keyExists($privacySettingData, 'access_level', "Incorrect structure of '$privacySettingName' param, '$privacySettingName' should contain 'access_level' property");
            Assertion::integer($privacySettingData['access_level'], "Incorrect structure of '$privacySettingName' param, property 'access_level' should be an integer");

            Assertion::keyExists($privacySettingData, 'lists', "Incorrect structure of '$privacySettingName' param, '$privacySettingName' should contain 'lists' property");
            Assertion::isArray($privacySettingData['lists'], "Incorrect structure of '$privacySettingName' param, property 'lists' should be array");
            
            $allowedConnections   = $privacySettingData[Privacy::ALLOWED_CONNECTIONS]   ?? [];
            $unallowedConnections = $privacySettingData[Privacy::UNALLOWED_CONNECTIONS] ?? [];
            $allowedLists    = $privacySettingData[Privacy::ALLOWED_LISTS]    ?? [];
            $unallowedLists  = $privacySettingData[Privacy::UNALLOWED_LISTS]  ?? [];

            $connectionsIntersections = array_intersect($allowedConnections, $unallowedConnections);
            //print_r($privacySettingData);exit();
            if(count($connectionsIntersections)) {
                $connectionId = $connectionsIntersections[0];
                throw new UnprocessableRequestException(
                    1, "Incorrect structure of '$privacySettingName' param, id '$connectionId'"
                    . " cannot be in 'allowed_connections' and 'unallowed_connections' at same time"
                );
            }
            $connectionsListsIntersections = array_intersect($allowedLists, $unallowedLists);
            if(count($connectionsListsIntersections)) {
                $connectionListId = $connectionsListsIntersections[0];
                throw new UnprocessableRequestException(
                    2, "Incorrect structure of '$privacySettingName' param, id '$connectionListId' "
                    . "cannot be in 'allowed_lists' and 'unallowed_lists' at same time"
                );
            }
            Assertion::isArray($allowedConnections,   "Incorrect structure of '$privacySettingName' param, property 'allowed_connections' should be an array");
            Assertion::isArray($unallowedConnections, "Incorrect structure of '$privacySettingName' param, property 'unallowed_connections' should be an array");
            Assertion::isArray($allowedLists,    "Incorrect structure of '$privacySettingName' param, property 'allowed_lists' should be an array");
            Assertion::isArray($unallowedLists,  "Incorrect structure of '$privacySettingName' param, property 'unallowed_lists' should be an array");
        } catch (\Assert\LazyAssertionException $ex) {
            throw new MalformedRequestException($ex->getMessage());
        }
        
    }

//    function failIfListsContentInappropriateForAccessLevel(
//        int $accessLevel,
//        array $allowedFriends, array $unallowedFriends,
//        array $allowedGroups, array $unallowedGroups
//    ): void {
//        if($accessLevel === AL::NOBODY) {
//            Assertion::allTrue([
//                empty($allowedFriends), empty($unallowedFriends),
//                empty($allowedGroups), empty($unallowedGroups)
//            ], "If access level is ".AL::NOBODY." then all lists should be empty");
//        }
//        elseif($accessLevel >= AL::FRIENDS && $accessLevel <= AL::EVERYONE) {
//            $errorMessage = "If access level is ".AL::FRIENDS.", ".AL::FRIENDS_OF_FRIENDS
//                ." or ".AL::EVERYONE." then 'allowedFriends' and 'allowedGroups' should be empty";
//            Assertion::allTrue([empty($allowedFriends), empty($allowedGroups)], $errorMessage);
//        }
//        elseif($accessLevel === AL::SOME_FRIENDS_AND_LISTS) {
//            $errorMessage = "If access level is ".AL::SOME_FRIENDS_AND_LISTS
//                ." at least one allowed friend or 'group of friends' should be specified";
//            Assertion::true((count($allowedFriends) + count($allowedGroups) > 0), $errorMessage);
//        }
//    }
}