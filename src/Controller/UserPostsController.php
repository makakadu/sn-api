<?php
declare(strict_types=1);
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Users\CommentPost\CommentPostRequest;
use App\Application\TransactionalApplicationService;
use App\Application\Users\Posts\Create\CreateRequest;
use App\Application\Users\UpdatePost\UpdatePostRequest;
use App\Application\Users\ReplyPostComment\ReplyPostCommentRequest;
use App\Application\Users\RemovePostComment\RemovePostCommentRequest;
use App\Application\Users\Posts\GetComments\GetCommentsRequest;
use App\Application\Users\RemovePost\RemovePostRequest;
use App\Application\Users\UpdatePostIsDeleted\UpdatePostIsDeletedRequest;
use App\Application\Users\Posts\Patch\PatchRequest;
use App\Application\Users\Posts\Put\Put;
use App\Application\Users\Posts\Put\PutRequest;
use App\Application\Users\Posts\CreateComment\CreateCommentRequest;
use App\Application\Users\Posts\CreateCommentReaction\CreateCommentReactionRequest;
use App\Application\Users\Posts\Get\GetRequest;
use App\Application\Users\Posts\PostPhotos\Create\CreateRequest as CreatePostPhotoRequest;
use App\Application\Users\Posts\Delete\DeleteRequest;
use App\Application\Users\Posts\CreateReaction\CreateReactionRequest;
use App\Application\Users\Posts\GetPart\GetPartRequest;
use App\Application\Users\Posts\GetReplies\GetRepliesRequest;
use App\Application\Users\Posts\GetComment\GetCommentRequest;
use App\Application\Users\Posts\UpdateReaction\UpdateReactionRequest;
use App\Application\Users\Posts\DeleteReaction\DeleteReactionRequest;
use App\Application\Users\Posts\GetPostReaction\GetPostReactionRequest;
use App\Application\Users\Posts\GetReactions\GetReactionsRequest;
use App\Application\Users\Posts\UpdateComment\UpdateCommentRequest;
use App\Application\Users\Posts\GetCommentReaction\GetCommentReactionRequest;
use App\Application\Users\Posts\DeleteCommentReaction\DeleteCommentReactionRequest;
use App\Application\Users\Posts\GetCommentReactions\GetCommentReactionsRequest;
use App\Application\Users\Posts\UpdateCommentReaction\UpdateCommentReactionRequest;
use App\Application\Users\Posts\PutComment\PutCommentRequest;
use App\Application\Users\Posts\PostPhotos\Get\GetRequest as GetPhotoRequest;;

class UserPostsController extends AbstractController {
    /**
     * @Inject
     * @var \App\Application\TransactionalSession
     */
    private $transactionalSession;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\PostPhotos\Create\Create
     */
    private $createPostPhoto;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\PostPhotos\Get\Get
     */
    private $getPostPhoto;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\Create\Create
     */
    private $create;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\GetPart\GetPart
     */
    private $getPart;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\CreateReaction\CreateReaction
     */
    private $createReaction;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\CreateComment\CreateComment
     */
    private $createComment;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\GetComments\GetComments
     */
    private $getComments;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\GetReplies\GetReplies
     */
    private $getReplies;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\CreateCommentReaction\CreateCommentReaction
     */
    private $createCommentReaction;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\Patch\Patch
     */
    private $patch;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\UpdateComment\UpdateComment
     */
    private $patchComment;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\UpdateReaction\UpdateReaction
     */
    private $editReaction;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\DeleteReaction\DeleteReaction
     */
    private $deleteReaction;
        
    /**
     * @Inject
     * @var \App\Application\Users\Posts\UpdateCommentReaction\UpdateCommentReaction
     */
    private $editCommentReaction;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\DeleteCommentReaction\DeleteCommentReaction
     */
    private $deleteCommentReaction;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\GetPostReaction\GetPostReaction
     */
    private $getPostReaction;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\GetReactions\GetReactions
     */
    private $getPostReactions;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\GetCommentReactions\GetCommentReactions
     */
    private $getCommentReactions;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\GetCommentReaction\GetCommentReaction
     */
    private $getCommentReaction;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\Put\Put
     */
    private $put;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\PutComment\PutComment
     */
    private $putComment;
    
    /**
     * @Inject
     * @var \App\Application\Users\Posts\Get\Get
     */
    private $get;
    /**
     * @Inject
     * @var \App\Application\Users\Posts\GetComment\GetComment
     */
    private $getComment;
    /**
     * @Inject
     * @var \App\Application\Users\Posts\Delete\Delete
     */
    private $delete;
//    
//    /**
//     * @Inject
//     * @var \App\Application\Users\UpdatePostIsDeleted\UpdatePostIsDeleted
//     */
//    private $updatePostIsDeleted;
    
    private string $fakeId = "01fkeg2e04mp80vj7z62dff436";
    
    
    function createPhoto(Request $request, Response $response): Response {
        $parsedBody = $request->getParsedBody() ?? [];
        
        $uploadedFiles = $request->getUploadedFiles();
        if(!isset($uploadedFiles['photo'])) {
            throw new MalformedRequestException("File 'photo' should be passed");
        }
        $requestDTO = new CreatePostPhotoRequest(
            $request->getAttribute('token')['data']->userId,
            $uploadedFiles['photo']
        );
        $useCase = new TransactionalApplicationService(
            $this->createPostPhoto, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO), 201);
    }
    
    function getPhoto(Request $request, Response $response) {
        $requestDTO = new GetPhotoRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id')
        );
        $useCase = new TransactionalApplicationService(
            $this->getPostPhoto, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO));
    }
    
    function create(Request $request, Response $response): Response {
        $requestBody = (array)$request->getParsedBody() ?? [];

        $this->failIfRequiredParamWasNotPassed(
            $requestBody, ['text', 'disable_comments', 'disable_reactions', 'is_public']
        );
        
        $requestDTO = new CreateRequest(
            $request->getAttribute('token')['data']->userId,
            $requestBody['text'],
            $requestBody['is_public'],
            isset($requestBody['attachments']) ? $requestBody['attachments'] : [],
            $requestBody['disable_comments'],
            $requestBody['disable_reactions'],
            isset($requestBody['shared']) ? $requestBody['shared'] : null
        );

        $useCase = new TransactionalApplicationService(
            $this->create, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO, 201);
    }
    
    function getPart(Request $request, Response $response): Response {
        $queryParams = $request->getQueryParams();
        $requestDTO = new GetPartRequest(
            $request->getAttribute('token')['data']['userId'],
            $request->getAttribute('id'),
            isset($queryParams['cursor']) ? $queryParams['cursor'] : null,
            isset($queryParams['count']) ? $queryParams['count'] : null,
            isset($queryParams['order']) ? $queryParams['order'] : null,
            isset($queryParams['comments-count'])
                ? $queryParams['comments-count'] : null,
            isset($queryParams['comments-type'])
                ? $queryParams['comments-type'] : null,
            isset($queryParams['comments-order'])
                ? $queryParams['comments-order'] : null
        );
        $useCase = new TransactionalApplicationService(
            $this->getPart, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function createReaction(Request $request, Response $response): Response {
        $parsedBody = (array)$request->getParsedBody() ?? [];
        
        $this->failIfRequiredParamWasNotPassed($parsedBody, ['type']);
        $requestDTO = new CreateReactionRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
            $parsedBody['type'],
            isset($parsedBody['on_behalf_of_page']) ? (string)$parsedBody['on_behalf_of_page'] : null
        );
        $useCase = new TransactionalApplicationService(
            $this->createReaction, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO), 201);
    }
    
    function createComment(Request $request, Response $response): Response {
        $parsedBody = (array)$request->getParsedBody() ?? [];
        
        $this->failIfRequiredParamWasNotPassed($parsedBody, ['text']);
        $requestDTO = new CreateCommentRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
            $parsedBody['text'],
            isset($parsedBody['replied_id']) ? $parsedBody['replied_id'] : null,
            isset($parsedBody['attachment']) ? $parsedBody['attachment'] : null,
            isset($parsedBody['on_behalf_of_page']) ? (string)$parsedBody['on_behalf_of_page'] : null
        );
        $useCase = new TransactionalApplicationService(
            $this->createComment, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO), 201);
    }
    
    function createCommentReaction(Request $request, Response $response): Response {
        $parsedBody = (array)$request->getParsedBody() ?? [];
        
        $this->failIfRequiredParamWasNotPassed($parsedBody, ['type']);
        $requestDTO = new CreateCommentReactionRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
            $parsedBody['type']
        );
        $useCase = new TransactionalApplicationService(
            $this->createCommentReaction, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO), 201);
    }
    
    function replyComment(Request $request, Response $response): Response {
        $commentId = $request->getAttribute('commentId');
        $requesterId = $request->getAttribute('token')['data']->userId;
        
        $parsedBody = (array)$request->getParsedBody();
        $media = $parsedBody['media'] ?? [];
        $text = $parsedBody['text'];
        $mentionedId = $parsedBody['mentionedId'];
        
        $requestDTO = new ReplyPostCommentRequest($requesterId, $commentId, $text, $media, $mentionedId);
        $useCase = new TransactionalApplicationService(
            $this->replyComment, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function getPostComments(Request $request, Response $response): Response {
        $queryParams = $request->getQueryParams();
        
        $requestDTO = new GetCommentsRequest(
            $request->getAttribute('token')['data']['userId'],
            $request->getAttribute('postId'),
            (isset($queryParams['offset-id']) ? $queryParams['offset-id'] : null),
            (isset($queryParams['count']) ? (int)$queryParams['count'] : null),
            (isset($queryParams['type']) ? $queryParams['limit'] : null),
            (isset($queryParams['order']) ? $queryParams['order'] : null),
        );
        $useCase = new TransactionalApplicationService(
            $this->getComments, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function getPostReactions(Request $request, Response $response): Response {
        $queryParams = $request->getQueryParams();
        
        $requestDTO = new GetReactionsRequest(
            $request->getAttribute('token')['data']['userId'],
            $request->getAttribute('postId'),
            (isset($queryParams['offset-id']) ? $queryParams['offset-id'] : null),
            (isset($queryParams['count']) ? (int)$queryParams['count'] : null),
            (isset($queryParams['order']) ? $queryParams['order'] : null),
        );
        
        $useCase = new TransactionalApplicationService(
            $this->getPostReactions, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function getCommentReactions(Request $request, Response $response): Response {
        $queryParams = $request->getQueryParams();
        
        $requestDTO = new GetCommentReactionsRequest(
            $request->getAttribute('token')['data']['userId'],
            $request->getAttribute('commentId'),
            (isset($queryParams['offset-id']) ? $queryParams['offset-id'] : null),
            (isset($queryParams['count']) ? (int)$queryParams['count'] : null),
            (isset($queryParams['order']) ? $queryParams['order'] : null),
        );
        $useCase = new TransactionalApplicationService(
            $this->getCommentReactions, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function getCommentReplies(Request $request, Response $response): Response {
        $queryParams = $request->getQueryParams();
        
        $requestDTO = new GetRepliesRequest(
            $request->getAttribute('token')['data']['userId'],
            $request->getAttribute('commentId'),
            (isset($queryParams['offset-id']) ? $queryParams['offset-id'] : null),
            (isset($queryParams['count']) ? (int)$queryParams['count'] : null),
        );
        $useCase = new TransactionalApplicationService(
            $this->getReplies, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function removeComment(Request $request, Response $response): Response {
        $commentId = $request->getAttribute('commentId');
        $requesterId = $request->getAttribute('token')['data']->userId;
        
        $requestDTO = new RemovePostCommentRequest($commentId, $requesterId);
        $useCase = new TransactionalApplicationService(
            $this->removeComment, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function delete(Request $request, Response $response): Response {
        $requesterId = $request->getAttribute('token')['data']->userId;
                
        $requestDTO = new DeleteRequest($requesterId, $request->getAttribute('id'));
        $useCase = new TransactionalApplicationService(
            $this->delete, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
        
    function get(Request $request, Response $response): Response {
        $queryParams = $request->getQueryParams();
        
        $requestDTO = new GetRequest(
            $request->getAttribute('token')['data']['userId'],
            $request->getAttribute('id'),
            isset($queryParams['comments-count'])
                ? $queryParams['comments-count'] : 0,
            isset($queryParams['comments-type'])
                ? $queryParams['comments-type'] : null,
            isset($queryParams['comments-order'])
                ? $queryParams['comments-order'] : null,
            isset($queryParams['fields'])
                ? $queryParams['fields'] : null,
        );
        $useCase = new TransactionalApplicationService(
            $this->get, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function getComment(Request $request, Response $response): Response {
        $queryParams = $request->getQueryParams();
        
        $requestDTO = new GetCommentRequest(
            $request->getAttribute('token')['data']['userId'],
            $request->getAttribute('id'),
            isset($queryParams['replies-count'])
                ? $queryParams['replies-count'] : null
        );
        $useCase = new TransactionalApplicationService(
            $this->getComment, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function patch(Request $request, Response $response): Response {
        $parsedBody = $request->getParsedBody() ?? [];
             
        $this->failIfRequiredParamWasNotPassed(
            $parsedBody, ['property', 'value']
        );
        $requestDTO = new PatchRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
            $parsedBody['property'],
            $parsedBody['value']
        );
        $useCase = new TransactionalApplicationService(
            $this->patch, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function patchComment(Request $request, Response $response): Response {
        $parsedBody = $request->getParsedBody() ?? [];
             
        $this->failIfRequiredParamWasNotPassed(
            $parsedBody, ['property', 'value']
        );
        $requestDTO = new UpdateCommentRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('commentId'),
            $parsedBody['property'],
            $parsedBody['value']
        );
        $useCase = new TransactionalApplicationService(
            $this->patchComment, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function editReaction(Request $request, Response $response): Response {
        $parsedBody = $request->getParsedBody() ?? [];
             
        $this->failIfRequiredParamWasNotPassed(
            $parsedBody, ['type']
        );
        $requestDTO = new UpdateReactionRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('postId'),
            $request->getAttribute('reactionId'),
            $parsedBody['type']
        );
        $useCase = new TransactionalApplicationService(
            $this->editReaction, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function editCommentReaction(Request $request, Response $response): Response {
        $parsedBody = $request->getParsedBody() ?? [];
             
        $this->failIfRequiredParamWasNotPassed(
            $parsedBody, ['type']
        );
        $requestDTO = new UpdateCommentReactionRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('commentId'),
            $request->getAttribute('reactionId'),
            $parsedBody['type']
        );
        $useCase = new TransactionalApplicationService(
            $this->editCommentReaction, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function deleteReaction(Request $request, Response $response): Response {
        $requestDTO = new DeleteReactionRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('postId'),
            $request->getAttribute('reactionId')
        );
        $useCase = new TransactionalApplicationService(
            $this->deleteReaction, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function deleteCommentReaction(Request $request, Response $response): Response {
        $requestDTO = new DeleteCommentReactionRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('commentId'),
            $request->getAttribute('reactionId')
        );
        $useCase = new TransactionalApplicationService(
            $this->deleteCommentReaction, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function getPostReaction(Request $request, Response $response): Response {
        $requestDTO = new GetPostReactionRequest(
            $request->getAttribute('token')['data']['userId'],
            $request->getAttribute('postId'),
            $request->getAttribute('reactionId')
        );
        $useCase = new TransactionalApplicationService(
            $this->getPostReaction, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function getCommentReaction(Request $request, Response $response): Response {
        $requestDTO = new GetCommentReactionRequest(
            $request->getAttribute('token')['data']['userId'],
            $request->getAttribute('commentId'),
            $request->getAttribute('reactionId')
        );
        $useCase = new TransactionalApplicationService(
            $this->getCommentReaction, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function put(Request $request, Response $response): Response {
        $parsedBody = $request->getParsedBody() ?? [];
                
        $this->failIfRequiredParamWasNotPassed(
            $parsedBody,
            ['text', 'is_public', 'attachments', 'disable_comments', 'disable_reactions']
        );
        $requestDTO = new PutRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
            $parsedBody['text'],
            $parsedBody['is_public'],
            isset($parsedBody['attachments']) ? $parsedBody['attachments'] : [],
            $parsedBody['disable_comments'],
            $parsedBody['disable_reactions']
        );
        $useCase = new TransactionalApplicationService(
            $this->put, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }

    function putComment(Request $request, Response $response): Response {
        $parsedBody = $request->getParsedBody() ?? [];
                
        $this->failIfRequiredParamWasNotPassed(
            $parsedBody,
            ['text']
        );
        $requestDTO = new PutCommentRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('commentId'),
            $parsedBody['text'],
            isset($parsedBody['attachment']) ? $parsedBody['attachment'] : null,
        );
        $useCase = new TransactionalApplicationService(
            $this->putComment, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function updatePostIsDeleted(Request $request, Response $response): Response {
        if(!array_key_exists('isDeleted', $request->getParsedBody())) {
            throw new \App\Application\Exceptions\MalformedRequestException("'isDeleted' param should be in a request body");
        }
        $requestDTO = new UpdatePostIsDeletedRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
            $request->getParsedBody()['isDeleted']
        );
        $useCase = new TransactionalApplicationService(
            $this->updatePostIsDeleted, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function getPosts(Request $request, Response $response): Response {
        $owner = $request->getQueryParams()['owner'];
        $limit = (int)$request->getQueryParams()['limit'];
        $page = (int)$request->getQueryParams()['page'];
        $payload = $this->getPosts->execute($owner, $page, $limit);
        return $this->prepareResponse($response, $payload);
    }

}
