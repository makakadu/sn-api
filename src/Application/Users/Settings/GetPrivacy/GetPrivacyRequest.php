<?php //
declare(strict_types=1);
namespace App\Application\Users\GetPrivacy;

use App\Application\BaseRequest;

class GetPrivacyRequest implements BaseRequest {
    public string $requesterId;
    
    public function __construct(string $requesterId) {
        $this->requesterId = $requesterId;
    }
}
