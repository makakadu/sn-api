<?php
declare(strict_types=1);
namespace App\Application\Users\Settings\UpdatePrivacy;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\Exceptions\MalformedRequestException;
use App\Application\Exceptions\ValidationException;

class UpdatePrivacy implements \App\Application\ApplicationService {
    use \App\Application\AppServiceTrait;
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
        
        if(!count($request->payload)) {
            throw new MalformedRequestException('Request should contain params');
        }
        
        foreach($request->payload as $key => $value) {
            try {
                \Assert\Assertion::boolean($value, "'$key' param should be a boolean");
            } catch (\Assert\InvalidArgumentException $ex) {
                throw new ValidationException($ex->getMessage());
            }
            if($key === "isHidden") {
                $value ? $requester->hide() : $requester->unhide();
            }
            elseif($key === "isClosed") {
                $value ? $requester->close() : $requester->open();
            }
            elseif($key === "isAgeHidden") {
                $value ? $requester->hideAge() : $requester->showAge();
            }
        }
        
        return new UpdatePrivacyResponse('OK');
    }
}