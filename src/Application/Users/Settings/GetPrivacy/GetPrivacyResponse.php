<?php
declare(strict_types=1);
namespace App\Application\Users\GetPrivacy;

use App\Application\BaseResponse;
use App\Domain\Model\Users\User\ProfilePrivacySettings;

class GetPrivacyResponse implements BaseResponse {
    public bool $isClosed;
    public bool $isHidden;
    public bool $isAgeHidden;
    /** @var array<mixed> $whoCanStartDialog */
    public array $whoCanStartDialog;
    /** @var array<mixed> $whoCanInviteToGroup */
    public array $whoCanInviteToGroup;
    /** @var array<mixed> $whoCanOfferFriendship */
    public array $whoCanOfferFriendship;
    /** @var array<mixed> $whoCanTagOnPhoto */
    public array $whoCanTagOnPhoto;
    /** @var array<mixed> $whoCanCommentPosts */
    public array $whoCanCommentPosts;
    /** @var array<mixed> $whoSeeInfo */
    public array $whoSeeInfo;
    /** @var array<mixed> $whoSeeAudio */
    public array $whoSeeAudio;
    /** @var array<mixed> $whoSeeVideos */
    public array $whoSeeVideos;
    /** @var array<mixed> $whoSeeTaggedPhotos */
    public array $whoSeeTaggedPhotos;
    /** @var array<mixed> $whoSeeGroupsList */
    public array $whoSeeGroupsList;
    /** @var array<mixed> $whoSeeHiddenFriends */
    public array $whoSeeHiddenFriends;

    public function __construct(ProfilePrivacySettings $privacy) {
        $this->isClosed = $privacy->isClosed();
        $this->isHidden = $privacy->isClosed();
        $this->isAgeHidden = $privacy->isAgeHidden();
        $this->whoCanStartDialog = $privacy->whoCanStartDialog();
        $this->whoCanInviteToGroup = $privacy->whoCanInviteToGroup();
        $this->whoCanOfferFriendship = $privacy->whoCanOfferFriendship();
        $this->whoCanTagOnPhoto = $privacy->whoCanTagOnPhoto();
        $this->whoCanCommentPosts = $privacy->whoCanCommentPosts();
        $this->whoSeeInfo = $privacy->whoSeeInfo();
        $this->whoSeeAudio = $privacy->whoSeeAudio();
        $this->whoSeeVideos = $privacy->whoSeeVideos();
        $this->whoSeeTaggedPhotos = $privacy->whoSeeTaggedPhotos();
        $this->whoSeeGroupsList = $privacy->whoSeeGroupsList();
        $this->whoSeeHiddenFriends = $privacy->whoSeeHiddenFriends();
    }
}
