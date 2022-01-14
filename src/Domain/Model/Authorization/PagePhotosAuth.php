<?php
declare(strict_types=1);
namespace App\Domain\Model\Authorization;

use App\Application\Exceptions\ForbiddenException;
use App\Application\Exceptions\NotExistException;
use App\Domain\Model\Authorization\UserPostsAuth;
use App\Domain\Model\Authorization\UserAlbumPhotosAuth;
use App\Domain\Model\Users\Photos\Photo;
use App\Domain\Model\Users\Post\Comments\CommentRepository;
use App\Domain\Model\Users\ProfilePicture\ProfilePicture;
use App\Domain\Model\Users\User\User;
use App\Domain\Model\Users\PrivacyService\PrivacyService;
use App\Domain\Model\Users\Photos\Comments\Comment as PhotoComment;
use App\Domain\Model\Users\Photos\Reaction as PhotoReaction;
use App\Domain\Model\Users\Photos\Comments\Reaction as CommentReaction;
use App\Domain\Model\Users\Post\Comments\Comment as PostComment;
use App\Domain\Model\Users\Videos\Comments\Comment as VideoComment;

class PagePhotosAuth {
}