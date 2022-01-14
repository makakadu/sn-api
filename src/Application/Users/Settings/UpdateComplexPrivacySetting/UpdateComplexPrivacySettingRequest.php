<?php
declare(strict_types=1);
namespace App\Application\Users\Settings\UpdateComplexPrivacySetting;

use App\Application\BaseRequest;

class UpdateComplexPrivacySettingRequest implements BaseRequest {
    public string $requesterId;
    /** @var mixed $name */
    public $name;
    /** @var mixed $data */
    public $data;
    
    /**
     * @param mixed $name
     * @param mixed $data
     */
    function __construct(string $requesterId, $name, $data) {
        $this->requesterId = $requesterId;
        $this->name = $name;
        $this->data = $data;
    }

}
