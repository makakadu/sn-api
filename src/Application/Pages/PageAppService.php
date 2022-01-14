<?php
declare(strict_types=1);
namespace App\Application\Pages;

use App\Domain\Model\Users\User\UserRepository;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\Exceptions\NotExistException;
use App\Domain\Model\Pages\Page\PageRepository;
use App\Domain\Model\Pages\Page\Page;

trait PageAppService {
    
    private PageRepository $pages;
    
    function __construct(UserRepository $users, PageRepository $pages) {
        parent::__construct($users);
        $this->pages = $pages;
    }
    
    function findPageOrFail(string $pageId, bool $asTarget, ?string $message): Page {
        $page = $this->pages->getById($pageId);
        if(!$page) {
            $message = $message ?? "Page $pageId not found";
            if($asTarget) {
                throw new NotExistException($message);
            } else {
                throw new UnprocessableRequestException(1, $message);
            }
        }
        return $page;
    }
}