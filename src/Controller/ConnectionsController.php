<?php
declare(strict_types=1);
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\TransactionalApplicationService;
use App\Application\Users\Connection\Accept\AcceptRequest;
use App\Application\Users\Connection\Create\CreateRequest as CreateConnectionRequest;
use App\Application\Users\Connection\Delete\DeleteRequest;
use App\Application\Users\ConnectionsLists\Create\CreateRequest as CreateListRequest;
use App\Application\Users\Connection\Get\GetRequest;
use App\Application\Users\Connection\GetPart\GetPartRequest;


class ConnectionsController extends AbstractController {
    /**
     * @Inject
     * @var \App\Application\TransactionalSession
     */
    private $transactionalSession;
    /**
     * @Inject
     * @var \App\Application\Users\Connection\Create\Create
     */
    private $create;
    /**
     * @Inject
     * @var \App\Application\Users\Connection\Get\Get
     */
    private $get;
    /**
     * @Inject
     * @var \App\Application\Users\Connection\GetPart\GetPart
     */
    private $getPart;
    /**
     * @Inject
     * @var \App\Application\Users\ConnectionsLists\Create\Create
     */
    private $createList;
    /**
     * @Inject
     * @var \App\Application\Users\Connection\Delete\Delete
     */
    private $delete;
    
    /**
     * @Inject
     * @var \App\Application\Users\Connection\Accept\Accept
     */
    private $accept;
    
    // private $fakeId = "01fkeg2e04mp80vj7z62dff436";
    
    function create(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        $this->failIfRequiredParamWasNotPassed($parsedBody, ['target_user_id', 'subscribe']);

        $requestDTO = new CreateConnectionRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getParsedBody()['target_user_id'],
            $request->getParsedBody()['subscribe'],
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
        return $this->prepareResponse($response, $responseDTO, 201);
    }
    
    function getPart(Request $request, Response $response) {
        $queryParams = $request->getQueryParams();

        $requestDTO = new GetPartRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('userId'),
            isset($queryParams['cursor-id']) ? $queryParams['cursor-id'] : null,
            isset($queryParams['hide-accepted']) ? $queryParams['hide-accepted'] : null,
            isset($queryParams['hide-pending']) ? $queryParams['hide-pending'] : null,
            isset($queryParams['count']) ? $queryParams['count'] : null,
            isset($queryParams['type']) ? $queryParams['type'] : null,
        );
        
        $useCase = new TransactionalApplicationService(
            $this->getPart, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO, 201);
    }
    
    function createList(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        $this->failIfRequiredParamWasNotPassed($parsedBody, ['name']);

        $requestDTO = new CreateListRequest(
            $request->getAttribute('token')['data']->userId,
            $parsedBody['name'],
            isset($parsedBody['connections']) ? $parsedBody['connections'] : [],
        );
        $useCase = new TransactionalApplicationService(
            $this->createList, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO, 201);
    }
    
    function accept(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody() ?? [];
        $this->failIfRequiredParamWasNotPassed($parsedBody, ['connection_id']);
        
        $requestDTO = new AcceptRequest(
            $request->getAttribute('token')['data']->userId,
            $parsedBody['connection_id']
        );
        $useCase = new TransactionalApplicationService(
            $this->accept, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
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