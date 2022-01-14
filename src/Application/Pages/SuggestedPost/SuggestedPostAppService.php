<?php
declare(strict_types=1);
namespace App\Application\Pages\SuggestedPost;

use App\Domain\Model\Pages\Post\SuggestedPost\SuggestedPostRepository;
use App\Domain\Model\Pages\Page\PageRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Pages\Post\SuggestedPost\SuggestedPost;

trait SuggestedPostAppService {
    
    private SuggestedPostRepository $suggestedPosts;

    function __construct(UserRepository $users, PageRepository $pages, SuggestedPostRepository $suggestedPosts) {
        parent::__construct($users, $pages);
        $this->suggestedPosts = $suggestedPosts;
    }

    function findSuggestedPostOrFail(string $postId, bool $asTarget): ?SuggestedPost {
        $post = $this->suggestedPosts->getById($postId);
        
        $found = true;
        if(!$post) {
            $found = false;
        }
        if(!$found && $asTarget) {
            throw new NotExistException("Photo $postId not found");
        } elseif(!$found && !$asTarget) {
            throw new UnprocessableRequestException("Photo $postId not found");
        }
        return $post;
    }
    


}