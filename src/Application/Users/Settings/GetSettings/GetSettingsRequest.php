<?php //
declare(strict_types=1);
namespace App\Application\Users\Settings\GetSettings;

use App\Application\BaseRequest;

class GetSettingsRequest implements BaseRequest {
    public string $requesterId;
    public string $userId;
    
    public function __construct(string $requesterId, string $userId) {
        $this->requesterId = $requesterId;
        $this->userId = $userId;
    }

}
