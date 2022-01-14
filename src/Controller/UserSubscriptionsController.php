<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\BadRequestException;
use App\Application\CreateFriendship\CreateFriendshipRequest;
use App\Application\TransactionalApplicationService;
use App\Application\AcceptFriendship\AcceptFriendshipRequest;
use App\Application\Users\Subscription\Create\CreateRequest;
use App\Application\Users\Subscription\Update\UpdateRequest;
use App\Application\Exceptions\MalformedRequestException;
use App\Application\Users\Subscription\Delete\DeleteRequest;
use App\Application\Users\Subscription\Get\GetRequest;
use App\Application\Users\Subscription\GetPart\GetPartRequest;
use App\Application\Users\Subscription\GetSubscribers\GetSubscribersRequest;

class UserSubscriptionsController extends AbstractController {
    /**
     * @Inject
     * @var \App\Application\TransactionalSession
     */
    private $transactionalSession;
    /**
     * @Inject
     * @var \App\Application\Users\Subscription\Update\Update
     */
    private $update;
    /**
     * @Inject
     * @var \App\Application\Users\Subscription\Create\Create
     */
    private $create;
    /**
     * @Inject
     * @var \App\Application\Users\Subscription\Delete\Delete
     */
    private $delete;
    /**
     * @Inject
     * @var \App\Application\Users\Subscription\Get\Get
     */
    private $get;
    /**
     * @Inject
     * @var \App\Application\Users\Subscription\GetPart\GetPart
     */
    private $getPart;
    /**
     * @Inject
     * @var \App\Application\Users\Subscription\GetSubscribers\GetSubscribers
     */
    private $getSubscribers;

    private string $fakeId = "01fevg7fa9km0a6xegc0mmw7xe";
    
    function create(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody() ?? [];
        
        $this->failIfRequiredParamWasNotPassed($parsedBody, ['user_id']);
//        print_r($parsedBody);exit();
        $requestDTO = new CreateRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getParsedBody()['user_id']
        );
        $useCase = new TransactionalApplicationService(
            $this->create, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO, 201);
    }
    
    function get(Request $request, Response $response) {
        $requestDTO = new GetRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id')
        );
        $useCase = new TransactionalApplicationService(
            $this->get, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO, 200);
    }
    
    function getPart(Request $request, Response $response) {
        $queryParams = $request->getQueryParams();

        $requestDTO = new GetPartRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('userId'),
            isset($queryParams['cursor']) ? $queryParams['cursor'] : null,
            isset($queryParams['count']) ? $queryParams['count'] : null,
        );
        
        $useCase = new TransactionalApplicationService(
            $this->getPart, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO, 201);
    }
    
    function getSubscribersPart(Request $request, Response $response) {
        $queryParams = $request->getQueryParams();

        $requestDTO = new GetSubscribersRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('userId'),
            isset($queryParams['cursor']) ? $queryParams['cursor'] : null,
            isset($queryParams['count']) ? $queryParams['count'] : null,
        );
        
        $useCase = new TransactionalApplicationService(
            $this->getSubscribers, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO, 201);
    }
    
    function update(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody() ?? [];
        $this->failIfRequiredParamWasNotPassed($parsedBody, ['property', 'value']);

        $requestDTO = new UpdateRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
            $parsedBody['property'],
            $parsedBody['value']
        );
        $useCase = new TransactionalApplicationService(
            $this->update, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO, 201);
    }
    
    function delete(Request $request, Response $response) {
        $requestDTO = new DeleteRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id')
        );
        $useCase = new TransactionalApplicationService(
            $this->delete, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }

}