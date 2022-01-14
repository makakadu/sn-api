<?php
declare(strict_types=1);
namespace App\Application\Pages\Subscription;

use App\Domain\Model\Pages\Subscription\SubscriptionRepository;
use App\Domain\Model\Pages\Page\PageRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Subscription\Subscription;

abstract class SubscriptionAppService extends \App\Application\PageAppService {
    
    protected SubscriptionRepository $subscriptions;

    function __construct(UserRepository $users, PageRepository $pages, SubscriptionRepository $subscriptions) {
        parent::__construct($users, $pages);
        $this->subscriptions = $subscriptions;
    }

    function findPhotoOrFail(string $subscriptionId, bool $asTarget): ?Subscription {
        $subscription = $this->subscriptions->getById($subscriptionId);
        
        $found = true;
        if(!$subscription) {
            $found = false;
        }
        if(!$found && $asTarget) {
            throw new NotExistException("Photo $subscriptionId not found");
        } elseif(!$found && !$asTarget) {
            throw new UnprocessableRequestException("Photo $subscriptionId not found");
        }
        return $subscription;
    }

}