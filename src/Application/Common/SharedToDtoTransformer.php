<?php
declare(strict_types=1);
namespace App\Application\Common;

use App\Domain\Model\Users\Post\Shared\SharedVisitor;
use App\Domain\Model\Common\Shares\SharedProfilePhoto;

class SharedToDtoVisitor implements SharedVisitor {
    
    public function visitSharedGroupPhoto(SharedGroupPhoto $sharedPhoto) {
        $photo = $sharedPhoto->photo();
        
        $creator = $photo->creator();
        $group = $photo->group();

        return [
            'type' => 'group_photo',
            'id' => $sharedPhoto->id(),
            'creator' => $creator ?[
                'id' => $creator->id(), 
                'name' => $creator->fullname()
            ] : null, // Может быть такое, что создателя уже не существует
            'group' => [ 'id' => $creator->id(), 'name' => $group->name() ],
            'onBehalfOfGroup' => $photo->onBehalfOfGroup(), // Это очень важно возвратить, благодаря этому можно понять показывать ли создателя фото или только группу
            'src' => $sharedPhoto->medium()
        ];
    }

    public function visitSharedGroupPost(SharedGroupPost $sharedPost) {
        
    }

    public function visitSharedGroupVideo(SharedGroupVideo $sharedVideo) {
        
    }

    public function visitSharedPagePhoto(SharedPagePhoto $sharedPhoto) {
        
    }

    public function visitSharedPagePost(SharedPagePost $sharedPost) {
        
    }

    public function visitSharedPageVideo(SharedPageVideo $sharedVideo) {
        
    }

    public function visitSharedProfilePhoto(SharedProfilePhoto $sharedPhoto) {
        $creator = $sharedPhoto->creator();
        
        $creatorDTO = $creator
            ? ['id' => $creator->id(), 'name' => $creator->fullname()] : null;
        
        return [
            'type' => 'profile_photo',
            'id' => $sharedPhoto->id(),
            'creator' => $creatorDTO,
            'src' => $sharedPhoto->medium()
        ];
    }

    public function visitSharedProfilePost(SharedProfilePost $sharedPost) { // Если постом кто-то поделился(а это именно такой пост), то это значит, что он не содержит никакого shared
        $creator = $sharedPost->creator();
        
        $attachments = [];
        if(count($sharedPost->attachments())) {
            $visitor = new AttachmentToDtoVisitor();
            foreach ($sharedPost->attachments() as $attachment) {
                $attachments[] = $attachment->accept($visitor);
            }
        }
        
        return [
            'type' => 'profile_post',
            'id' => $sharedPost->id(),
            'creator' => [
                'id' => $creator->id()
            ],
            'text' => $sharedPost->text(),
            'attachments' => $attachments
        ];
    }

    public function visitSharedProfileVideo(SharedProfileVideo $sharedVideo) {
        
    }
}