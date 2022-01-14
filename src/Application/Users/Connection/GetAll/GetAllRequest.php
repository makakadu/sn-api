<?php
declare(strict_types=1);
namespace App\Application\Users\Connection\GetAll;

class GetAllRequest {
    /** @var mixed $signUpData */
    public $signUpData;

    /** @param array<mixed> $bodyParams */
    public function __construct(array $bodyParams) {
        foreach($bodyParams as $param) {
            
        }
    }
}
