<?php
declare(strict_types=1);
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Common\GetPosts\GetPostsRequest;
use App\Application\Common\GetPosts\GetPosts;
use App\Application\TransactionalApplicationService;
use App\Application\Common\Feed\FeedRequest;

class CommonController extends AbstractController {
    /**
     * @Inject
     * @var \App\Application\TransactionalSession
     */
    private $transactionalSession;
    
    /**
     * @Inject
     * @var \App\Application\Common\GetPosts\GetPosts
     */
    private $getPosts;
    
    /**
     * @Inject
     * @var \App\Application\Common\Feed\Feed
     */
    private $feed;
    
    private string $fakeId = '01f5c16qts25jhsfx8efjev0qb';
    
    function getPosts(Request $request, Response $response) {
        $queryParams = $request->getQueryParams();
        
        $requestDTO = new GetPostsRequest(
            $request->getAttribute('token')['data']->userId,
            isset($queryParams['text']) ? $queryParams['text'] : null,
            isset($queryParams['offset']) ? $queryParams['offset'] : null,
            isset($queryParams['count']) ? $queryParams['count'] : null,
            isset($queryParams['order']) ? $queryParams['order'] : null,
            
            isset($queryParams['comments-count']) ? $queryParams['comments-count'] : null,
            isset($queryParams['comments-order']) ? $queryParams['comments-order'] : null,
            isset($queryParams['comments-type']) ? $queryParams['comments-type'] : null,
            
            isset($queryParams['hide-from-users']) ? $queryParams['hide-from-users'] : null,
            isset($queryParams['hide-from-groups']) ? $queryParams['hide-from-groups'] : null,
            isset($queryParams['hide-from-pages']) ? $queryParams['hide-from-pages'] : null,
        );
        $useCase = new TransactionalApplicationService(
            $this->getPosts, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO));
    }
    
    function feed(Request $request, Response $response) {
        $queryParams = $request->getQueryParams();
        
        $requestDTO = new FeedRequest(
            $request->getAttribute('token')['data']->userId,
            isset($queryParams['cursor']) ? $queryParams['cursor'] : null,
            isset($queryParams['count']) ? $queryParams['count'] : null,
            isset($queryParams['comments-count']) ? $queryParams['comments-count'] : null,
            isset($queryParams['comments-order']) ? $queryParams['comments-order'] : null,
            isset($queryParams['comments-type']) ? $queryParams['comments-type'] : null,
        );
        $useCase = new TransactionalApplicationService(
            $this->feed, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO));
    }
}
