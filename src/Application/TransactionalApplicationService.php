<?php
declare(strict_types=1);
namespace App\Application;
use App\Application\ApplicationService;

class TransactionalApplicationService { // implements ApplicationService {
    private TransactionalSession $session;
    private ApplicationService $service;

    public function __construct(
        ApplicationService $service,
        TransactionalSession $session
    ) {
        $this->session = $session;
        $this->service = $service;
    }

    public function execute(BaseRequest $request): BaseResponse {
        $operation = function() use($request) {
            return $this->service->execute($request);
        };
        return $this->session->executeAtomically($operation->bindTo($this));
    }
}
    