<?php
declare(strict_types=1);
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Users\Videos\Create\CreateRequest;
use App\Application\Users\Videos\Get\GetRequest;
use App\Application\TransactionalApplicationService;
use App\Application\Users\Playlists\Create\CreateRequest as CreatePlaylistRequest;
use App\Application\Users\Playlists\Update\UpdateRequest as UpdatePlaylistRequest;
use App\Application\Users\Videos\CreateReaction\CreateReactionRequest;

class VideosController extends AbstractController {
    
    /**
     * @Inject
     * @var \App\Application\TransactionalSession
     */
    private $transactionalSession;
    
    /**
     * @Inject
     * @var \App\Application\Users\Videos\Create\Create
     */
    private $create;
    
    /**
     * @Inject
     * @var \App\Application\Users\Videos\CreateReaction\CreateReaction
     */
    private $createReaction;
    
    /**
     * @Inject
     * @var \App\Application\Users\Playlists\Create\Create
     */
    private $createPlaylist;
    
    /**
     * @Inject
     * @var \App\Application\Users\Playlists\Update\Update
     */
    private $updatePlaylist;
    
    /**
     * @Inject
     * @var \App\Application\Users\Videos\Get\Get
     */
    private $get;
    
    private string $fakeId = "01fe43a23m6nre33a7advpk7h4";
    
    function create(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody() ?? [];
        $this->failIfRequiredParamWasNotPassed(
            $parsedBody, ['address', 'who_can_see', 'who_can_comment']
        );
        $requestDTO = new CreateRequest(
            $this->fakeId, //$request->getAttribute('token')['data']->userId,
            $parsedBody['address'],
            $parsedBody['who_can_see'],
            $parsedBody['who_can_comment'],
        );
        $useCase = new TransactionalApplicationService(
            $this->create, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO));
    }
    
    function createReaction(Request $request, Response $response): Response {
        $parsedBody = (array)$request->getParsedBody() ?? [];
        
        $this->failIfRequiredParamWasNotPassed($parsedBody, ['type']);
        $requestDTO = new CreateReactionRequest(
            $this->fakeId,
            $request->getAttribute('id'),
            $parsedBody['type'],
            isset($parsedBody['on_behalf_of_page']) ? (string)$parsedBody['on_behalf_of_page'] : null
        );
        $useCase = new TransactionalApplicationService(
            $this->createReaction, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO));
    }
    
    function createPlaylist(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody() ?? [];
        $this->failIfRequiredParamWasNotPassed(
            $parsedBody, ['name', 'privacy']
        );
        $requestDTO = new CreatePlaylistRequest(
            $this->fakeId, //$request->getAttribute('token')['data']->userId,
            $parsedBody['name'],
            isset($parsedBody['description']) ? $parsedBody['description'] : null,
            $parsedBody['privacy'],
            isset($parsedBody['videos_ids']) ? $parsedBody['videos_ids'] : [],
        );
        $useCase = new TransactionalApplicationService(
            $this->createPlaylist, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO));
    }
    
    function updatePlaylist(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody() ?? [];
        
        $this->failIfRequiredParamWasNotPassed($parsedBody, ['operations']);
        
        $requestDTO = new UpdatePlaylistRequest(
            $this->fakeId, //$request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
            $parsedBody['operations'],
        );
        $useCase = new TransactionalApplicationService(
            $this->updatePlaylist, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO));
    }
    
    function get(Request $request, Response $response) {
        $queryParams = $request->getQueryParams();
        
        $requestDTO = new GetRequest(
            $this->fakeId, //$request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
            isset($queryParams['comments-count'])
                ? $queryParams['comments-count'] : 0,
            isset($queryParams['comments-type'])
                ? $queryParams['comments-type'] : null,
            isset($queryParams['comments-order'])
                ? $queryParams['comments-order'] : null
        );
        $useCase = new TransactionalApplicationService(
            $this->get, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO));
    }
}
