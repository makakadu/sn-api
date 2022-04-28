<?php
declare(strict_types=1);
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Chats\Create\CreateRequest;
use App\Application\Chats\CreateMessage\CreateMessageRequest;
use App\Application\TransactionalApplicationService;
use App\Application\Chats\GetPart\GetPartRequest;
use App\Application\Chats\GetMessage\GetMessageRequest;
use App\Application\Chats\GetMessagesPart\GetMessagesPartRequest;
use App\Application\Chats\Get\GetRequest;
use App\Application\Chats\Patch\PatchRequest;
use App\Application\Chats\DeleteMessage\DeleteMessageRequest;
use App\Application\Chats\PatchMessage\PatchMessageRequest;
use App\Application\Chats\DeleteHistory\DeleteHistoryRequest;
use App\Application\Chats\GetActionsPart\GetActionsPartRequest;

class ChatsController extends AbstractController {
    /**
     * @Inject
     * @var \App\Application\TransactionalSession
     */
    private $transactionalSession;
    
    /**
     * @Inject
     * @var \App\Application\Chats\Create\Create
     */
    private $create;
    
    /**
     * @Inject
     * @var \App\Application\Chats\Patch\Patch
     */
    private $patch;
    
    /**
     * @Inject
     * @var \App\Application\Chats\Get\Get
     */
    private $get;
    
    /**
     * @Inject
     * @var \App\Application\Chats\GetPart\GetPart
     */
    private $getPart;
    
    /**
     * @Inject
     * @var \App\Application\Chats\CreateMessage\CreateMessage
     */
    private $createMessage;
    
    /**
     * @Inject
     * @var \App\Application\Chats\PatchMessage\PatchMessage
     */
    private $patchMessage;
    
    /**
     * @Inject
     * @var \App\Application\Chats\GetMessage\GetMessage
     */
    private $getMessage;
    
    /**
     * @Inject
     * @var \App\Application\Chats\DeleteMessage\DeleteMessage
     */
    private $deleteMessage;
    
    /**
     * @Inject
     * @var \App\Application\Chats\DeleteHistory\DeleteHistory
     */
    private $deleteHistory;
    
    /**
     * @Inject
     * @var \App\Application\Chats\GetMessagesPart\GetMessagesPart
     */
    private $getMessages;
    
    /**
     * @Inject
     * @var \App\Application\Chats\GetActionsPart\GetActionsPart
     */
    private $getActionsPart;
    
    /**
     * @Inject
     * @var \App\Application\Chats\TypingMessage\TypingMessage
     */
    private $typingMessage;
 
    function create(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        
        $this->failIfRequiredParamWasNotPassed(
            $parsedBody, ['client_id', 'type', 'participants']
        );
        $requestDTO = new CreateRequest(
            $request->getAttribute('token')['data']->userId,
            $parsedBody['client_id'],
            $parsedBody['message_client_id'],
            $parsedBody['participants'],
            $parsedBody['type'],
            isset($parsedBody['first_message']) ? $parsedBody['first_message'] : null,
            $parsedBody['place_id']
        );
        $responseDTO = $this->create->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO, 201);
    }
 
    function typingMessage(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        
        $this->failIfRequiredParamWasNotPassed($parsedBody, ['chat_id']);
        $requestDTO = new \App\Application\Chats\TypingMessage\TypingMessageRequest(
            $request->getAttribute('token')['data']->userId,
            $parsedBody['chat_id']
        );
        $responseDTO = $this->typingMessage->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO, 200);
    }
    
    function patch(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        $this->failIfRequiredParamWasNotPassed($parsedBody, ['property', 'value']);
        $requestDTO = new PatchRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
            $parsedBody['property'],
            $parsedBody['value'],
            isset($parsedBody['place_id']) ? $parsedBody['place_id'] : null
        );
        $responseDTO = $this->patch->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO, 200);
    }
    
    function getPart(Request $request, Response $response): Response {
        $queryParams = $request->getQueryParams();
        
        $requestDTO = new GetPartRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
            isset($queryParams['interlocutor-id']) ? $queryParams['interlocutor-id'] : null,
            isset($queryParams['cursor']) ? $queryParams['cursor'] : null,
            isset($queryParams['count']) ? $queryParams['count'] : null,
            isset($queryParams['type']) ? $queryParams['type'] : null,
            isset($queryParams['messages-count']) ? $queryParams['messages-count'] : null,
            isset($queryParams['only-unread']) ? $queryParams['only-unread'] : null,
            isset($queryParams['fields']) ? $queryParams['fields'] : null,
            isset($queryParams['hide-empty']) ? $queryParams['hide-empty'] : null,
            'ASC'
        );
        $responseDTO = $this->getPart->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function get(Request $request, Response $response): Response {
        $queryParams = $request->getQueryParams();
        $requestDTO = new GetRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
            isset($queryParams['messages-count'])
                ? $queryParams['messages-count'] : null,
        );
        $responseDTO = $this->get->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function getMessagesPart(Request $request, Response $response): Response {
        $queryParams = $request->getQueryParams();
        $requestDTO = new GetMessagesPartRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
            isset($queryParams['cursor']) ? $queryParams['cursor'] : null,
            isset($queryParams['count']) ? $queryParams['count'] : null,
            isset($queryParams['order']) ? $queryParams['order'] : null
        );
        $responseDTO = $this->getMessages->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function getActionsPart(Request $request, Response $response): Response {
        $queryParams = $request->getQueryParams();
        $requestDTO = new GetActionsPartRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
            isset($queryParams['types']) ? $queryParams['types'] : null,
            isset($queryParams['cursor']) ? $queryParams['cursor'] : null,
            isset($queryParams['count']) ? $queryParams['count'] : null
        );
        $responseDTO = $this->getActionsPart->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function createMessage(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody() ?? [];
        $this->failIfRequiredParamWasNotPassed(
            $parsedBody, ['client_id', 'text']
        );
        $requestDTO = new CreateMessageRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
            $parsedBody['client_id'],
            $parsedBody['text'],
            $parsedBody['place_id']
        );
        $responseDTO = $this->createMessage->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO, 201);
    }
    
    function patchMessage(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody() ?? [];
        $this->failIfRequiredParamWasNotPassed(
            $parsedBody, ['property', 'value', 'place_id']
        );
        $requestDTO = new PatchMessageRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
            $parsedBody['property'],
            $parsedBody['value'],
            $parsedBody['place_id']
        );
        $responseDTO = $this->patchMessage->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO, 200);
    }
    
    function deleteMessage(Request $request, Response $response) {
        $requestDTO = new DeleteMessageRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
            $request->getAttribute('messageId')
        );
        $responseDTO = $this->deleteMessage->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO, 200);
    }
    
    function deleteHistory(Request $request, Response $response) {
        $requestDTO = new DeleteHistoryRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id')
        );
        $responseDTO = $this->deleteHistory->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO, 200);
    }
    
    function getMessage(Request $request, Response $response): Response { 
        $requestDTO = new GetMessageRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
        );
        $responseDTO = $this->getMessage->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
}
