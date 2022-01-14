<?php
declare(strict_types=1);
namespace App\Application\Common\Reactions\GetReacted;

use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\ApplicationService;
use App\Domain\Model\Users\User\UserRepository;
use App\DataTransformer\Users\ReactableTransformer;
use App\Domain\Model\Common\ReactionRepository;
use App\Application\GetRequestParamsValidator;

class GetReacted implements ApplicationService {
    use \App\Application\AppServiceTrait;
    
    private ReactableTransformer $transformer;
    private ReactionRepository $reactions;
    
    public function __construct(UserRepository $users, ReactableTransformer $transformer, ReactionRepository $reactions) {
        $this->users = $users;
        $this->transformer = $transformer;
        $this->reactions = $reactions;
    }
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $request->requesterId
            ? $this->findRequesterOrFail($request->requesterId) : null;
        
        $this->validate($request);
        
        $reactor = $this->findUserOrFail($request->reactorId, true, null);
        if(!$requester->equals($reactor)) {
            throw new ForbiddenException(Errors::NO_RIGHTS, "Cannot see list of reacted of another user");
        }

        $reacted = $this->reactions->getPartCreatedOnBehalfOfUser(
            $reactor,
            $request->count ? (int)$request->count : 20, // В query string нельзя передать int, поэтому здесь превращаем строку в int
            $request->offsetId,
            $request->types ? explode(',', $request->types) : []
        );

        return new GetReactedResponse($this->transformer->transform(
            $reacted,
            $requester,
            $request->commentsCount ? (int)$request->commentsCount : 1,
            $request->commentsType ? (string)$request->commentsType : "root",
            $request->commentsOrder ? (string)$request->commentsOrder : "ASC" 
        ));
    }
    
    function validate(GetReactedRequest $request): void {
        GetRequestParamsValidator::validateCommentsTypeParam($request->commentsType);
        GetRequestParamsValidator::validateCommentsCountParam($request->commentsCount);
        GetRequestParamsValidator::validateOffsetIdParam($request->offsetId);
        GetRequestParamsValidator::validateCountParam($request->count);
        GetRequestParamsValidator::validateCommentsOrderParam($request->commentsOrder, ['asc', 'desc']);
        
        try {
            if($request->types !== null) {
                \Assert\Assertion::string($request->types, "Param 'types' should be string with types separated by coma");
                $value = \mb_strtolower($request->types);
                $exploded = explode(',', $value);
                foreach ($exploded as $type) {
                    $acceptedTypes = ['posts', 'videos', 'photos', 'comments'];
                    \Assert\Assertion::inArray(
                        $type, $acceptedTypes,
                        "Incorrect type '$type' in 'types' param. Only these types are accepted: " . implode(', ', $acceptedTypes)
                    );
                }
            }
        }
        catch (\Assert\InvalidArgumentException $ex) {
            throw new \App\Application\Exceptions\ValidationException($ex->getMessage());
        }
    }
    
}
