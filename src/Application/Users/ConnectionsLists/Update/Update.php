<?php
declare(strict_types=1);
namespace App\Application\Users\ConnectionsLists\Update;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use Assert\Assert;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\Exceptions\ForbiddenException;
use App\Application\Users\ConnectionsLists\ConnectionsListAppService;

class Update extends ConnectionsListAppService {
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        $list = $this->findListOrFail($request->listId);
        if(!$requester->equals($list->user())) {
            throw new ForbiddenException(123, "Cannot update connections list {$list->id()} of another user");
        }
        
        foreach ($request->operations as $operation) {
            $op = $operation['op'];
            $path = $operation['path'];
            $value = $operation['value'] ?? null;
            
            if($op === "update" && $path === "/isDeleted") {
                if(count($request->operations) > 1) {
                    throw new UnprocessableRequestException(123, "If 'is_deleted' property is updating other properies cannot be updated in this request");
                }
                \Assert\Assertion::boolean("Value of 'is_deleted' param should be a boolean");
                $value ? $list->delete() : $list->restore();
            } 
            elseif($op === "update" && $path === "/name") {
                Assert::that($value)->string()->maxLength(50, "'name' should be a string up to 50 characters");
                $list->changeName($value);
            } 
            elseif($op === "add" && $path === "/connections") {
                $connection = $this->connections->getById($value);
                if($connection) {
                    $list->addConnection($connection);
                }
            }
            elseif($op === "delete" && preg_match("/^\/connections\/\w+$/", $path)) {
                $connectionId = explode('/', $path)[2];
                $list->removeConnection($connectionId);
            }
        }

        foreach ($request->payload as $key => $value) {
            if($key === "is_deleted") {
                if(count($request->payload) > 1) {
                    throw new UnprocessableRequestException(123, "If 'is_deleted' property is updating other properies cannot be updated in this request");
                }
                \Assert\Assertion::boolean("'is_deleted' should be a boolean");
                $value ? $list->delete() : $list->restore();
            }
            if($key === "name") {
                Assert::that($value)->string()->maxLength(50, "'name' should be a string up to 50 characters");
                $list->changeName($value);
            } 
            elseif($key === "connections") {
                $connections = [];
                foreach ($value as $connectionId) {
                    $connection = $this->connections->getById($connectionId);
                    if($connection) {
                        $list->addConnection($connection);
                    }
                }
            }
        }
        
        return new UpdateResponse('ok');

    }

}