<?php
declare(strict_types=1);
namespace App\Application\Users\UpdateUser;

//use App\Domain\Model\ValidationException;
//use App\Permissions\Permissions;
//use App\Permissions\Assertions\IsUserPropertyAssertion;
use App\Application\ApplicationService;
use App\Application\BaseRequest;
use App\Application\BaseResponse;
use App\Application\Exceptions\UnprocessableRequestException;
use Assert\Assertion;
use Assert\Assert;
use App\Application\Exceptions\ValidationException;
use PragmaRX\Countries\Package\Countries;

class UpdateUser implements ApplicationService {
    use \App\Application\AppServiceTrait;
    
    const FIRST_NAME = 'firstname';
    const LAST_NAME = 'lastname';
    const GENDER = 'gender';
    const USERNAME = 'username';
    const EMAIL = 'email';
    const BIRTHPLACE = 'birthplace';
    const ADDRESS = 'address';
    const BIRTHDAY = 'birthday';
    
    /** @var array<string> $fields*/
    private array $fields = [
        self::FIRST_NAME,
        self::LAST_NAME,
        self::GENDER,
        self::USERNAME,
        self::EMAIL, 
        self::BIRTHPLACE,
        self::ADDRESS,
        self::BIRTHDAY
    ];

    public function execute(BaseRequest $request): BaseResponse {
//        try {
//            $this->validate($request);
//        } catch (\Assert\InvalidArgumentException $ex) {
//            throw new ValidationException(['code' => 1488, 'message' => $ex->getMessage()]);
//        }
//        $requester = $this->findRequesterOrFailIfNotFoundOrInactive($request->requesterId);
//        $updatingUser = $this->findUserOrFail($request->updatingUserId, true, null);
//        
//        //$this->failIfUserIsInactive($updatingUser, true);
//        //$this->failIfNotOwnProfile($requester, $updatingUser);
//        
//        foreach($request->payload as $field => $value) {
//            $setter = 'change'.ucfirst($field);
//            $updatingUser->$setter($value);
//        }
//        $this->users->flush();
        return new UpdateUserResponse('kek');
    }
    
    private function validate(UpdateUserRequest $request): void {
        $payload = $request->payload;
        foreach($payload as $field => $value) {
            if(!in_array($field, $this->fields)) {
                unset($request->payload[$field]);
            }
        }
        
        foreach($request->payload as $field => $value) {
            switch($field) {
                case self::FIRST_NAME:
                    Assertion::minLength($value, 1);
                    Assertion::maxLength($value, 40);
                    break;
                case self::LAST_NAME:
                    Assertion::minLength($value, 1);
                    Assertion::maxLength($value, 40);
                    break;
                case self::GENDER:
                    Assertion::choice($value, ['male', 'female', 'boeing 737']);
                    break;
                case self::USERNAME:
                    Assertion::minLength($value, 3);
                    Assertion::maxLength($value, 15);
                    break;
                case self::EMAIL:
                    Assertion::email($value);
                    break;
                case self::BIRTHDAY:
                    $this->checkBirthday($value);
                    break;
                case self::ADDRESS:
//                    Assertion::isArray($value, 'Address should be array with city and country');
//                    $this->checkAddress($value);
                case self::BIRTHPLACE:
//                    Assertion::isArray($value, 'Birthplace should be array with city and country');
//                    $this->checkAddress($value);
            }
        }
    }
    
    private function checkBirthday(string $value): void {
        Assertion::date($value, 'd-m-Y');

        $birthday = new \DateTime($value);
        $currentDate = new \DateTime('now');
        $userAge = (int)$currentDate->diff($birthday)->format('%R%y');
        
        if($userAge < -110) {
            throw new ValidationException('To old');
        } else if($userAge > -14) {
            throw new ValidationException('To young');
        }
    }

}