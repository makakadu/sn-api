<?php //
declare(strict_types=1);
namespace App\Application\Pages\UpdateBan;

use App\Application\BaseRequest;

class UpdateBanRequest implements BaseRequest {
    public string $requesterId;
    public string $banId;
    
    public function __construct(string $requesterId, string $banId) {
        $this->requesterId = $requesterId;
        $this->banId = $banId;
    }
}
