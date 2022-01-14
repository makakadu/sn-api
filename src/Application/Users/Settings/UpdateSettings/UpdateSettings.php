<?php
declare(strict_types=1);
namespace App\Application\Users\Settings\UpdateSettings;

use App\Application\ApplicationService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use Assert\Assertion;
use App\Application\Exceptions\ValidationException;
use App\Domain\Model\Users\User\UserRepository;

class UpdateSettings implements ApplicationService {
    use \App\Application\AppServiceTrait;
    
    function __construct(UserRepository $users) {
        $this->users = $users;
    }
    
//    const LANGUAGE = 'language';
//    const THEME = 'theme';
//    /** @var array<string> $$updateableFields */
//    private array $updateableFields = [self::LANGUAGE, self::THEME];
//    /** @var array<string> $languages */
//    public array $languages = ['ru', 'en', 'de', 'fr', 'ua'];
    
    public function execute(BaseRequest $request): BaseResponse {
        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
//        $validatedPayload = $this->validatePayload($request->payload);
        
        $user = $this->findUserOrFail($request->userId, true, null);
        if(!$requester->equals($user)) {
            throw new \App\Application\Exceptions\ForbiddenException(\App\Application\Errors::NO_RIGHTS, "No rights");
        }
        
        foreach($request->payload as $property => $newValue) {
            if($property === 'language') {
                $user->changeLanguage($newValue);
            }
            elseif($property === 'theme') {
                $user->changeTheme($newValue);
            }
        }

        return new UpdateSettingsResponse('ok');
    }
    
//    /** @param mixed $payload */
//    private function validatePayload(array $payload): array {        
//        try {
//            $this->failIfInappropriateValue($payload);
//        } catch (\Assert\InvalidArgumentException $ex) {
//            throw new ValidationException($ex->getMessage());
//        }
//        
//        return $payload;
//    }
    
    /** @param array<mixed> $payload */
    private function failIfInappropriateValue(array $payload): void {
        foreach($payload as $field => $value) {
            switch($field) {
                case self::LANGUAGE:
                    $supportedLanguages = \implode(', ', $this->languages);
                    $errorMessage = "Language has to consist of two letters "
                        . "(ISO 639-1). Supported app languages: $supportedLanguages";
                    Assertion::string($value, $errorMessage);
                    Assertion::length($value, 2, $errorMessage);
                    Assertion::choice($value, $this->languages, $errorMessage);
                    break;
                case self::THEME:
                    Assertion::choice($value, [0, 1], 'Pass 0 for light theme or 1 for dark theme');
                    break;
                default:
            }
        }
    }
}
