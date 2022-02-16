<?php
declare(strict_types=1);
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\GetUsers\GetUsersRequest;
use App\Application\UpdateUser\UpdateUserRequest;
use Assert\Assert;
use App\Application\Users\Ban\Create\CreateRequest as CreateBanRequest;
use App\Application\Users\Ban\Update\UpdateRequest as UpdateBanRequest;
use App\Application\TransactionalApplicationService;
use App\Application\Users\CommentPhotos\Create\CreateRequest as CreateCommentPhotoRequest;
use App\Application\Common\TempPhoto\Create\CreateRequest as CreateTempPhotoRequest;
use App\Application\Common\Reactions\GetReacted\GetReactedRequest;
use App\Application\Users\Saves\CreateCollection\CreateCollectionRequest;
use App\Application\Users\Saves\GetCollections\GetCollectionsRequest;
use App\Application\Users\Saves\GetItems\GetItemsRequest;
use App\Application\Users\Saves\CreateItem\CreateItemRequest;
use App\Application\Users\GetUser\GetUserRequest;
use App\Application\Users\CommentPhotos\Get\GetRequest as GetCommentPhotoRequest;
use App\Application\Users\GetContacts\GetContactsRequest;
use App\Application\Users\Search\SearchRequest;
use App\Application\Users\PatchUser\PatchUserRequest;

class UserController extends AbstractController {
    /**
     * @Inject
     * @var \App\Application\TransactionalSession
     */
    private $transactionalSession;
    
    /**
     * @Inject
     * @var \App\Application\Users\GetUser\GetUser
     */
    private $getProfile;
    
    /**
     * @Inject
     * @var \App\Application\Users\Search\Search
     */
    private $search;
    
    /**
     * @Inject
     * @var \App\Application\Users\GetContacts\GetContacts
     */
    private $getContacts;
    
    /**
     * @Inject
     * @var \App\Application\Users\CommentPhotos\Create\Create
     */
    private $createCommentPhoto;
    
    /**
     * @Inject
     * @var \App\Application\Users\CommentPhotos\Get\Get
     */
    private $getCommentPhoto;
    
    /**
     * @Inject
     * @var \App\Application\Common\Reactions\GetReacted\GetReacted
     */
    private $getReacted;
    
    /**
     * @Inject
     * @var \App\Application\Users\Saves\GetCollections\GetCollections
     */
    private $getCollections;
    
    /**
     * @Inject
     * @var \App\Application\Users\Saves\GetItems\GetItems
     */
    private $getCollectionItems;
    
    /**
     * @Inject
     * @var \App\Application\Users\Saves\CreateItem\CreateItem
     */
    private $createCollectionItem;
    
    /**
     * @Inject
     * @var \App\Application\Users\Saves\CreateCollection\CreateCollection
     */
    private $createCollection;
    
    /**
     * @Inject
     * @var \App\Application\Common\TempPhoto\Create\Create
     */
    private $createTempPhoto;
    
    /**
     * @Inject
     * @var \App\Application\Users\Ban\Create\Create
     */
    private $createBan;
    
    /**
     * @Inject
     * @var \App\Application\Users\Ban\Update\Update
     */
    private $updateBan;
    
//    /**
//     * @Inject
//     * @var \App\Application\GetUsers\GetUsers
//     */
//    private $getUsers;
//    /**
//     * @Inject
//     * @var \App\Application\GetUserProperty\GetUserProperty
//     */
//    private $getUserProperty;
//    /**
//     * @Inject
//     * @var \App\Application\GetUserAvatar\GetUserAvatar
//     */
//    private $getAvatar;
    
    /**
     * @Inject
     * @var \App\Application\Users\PatchUser\PatchUser
     */
    private $patchUser;
//    /**
//     * @Inject
//     * @var \App\Application\ChangeAvatar\ChangeAvatar
//     */
//    private $changeAvatar;
//    
//    /**
//     * @Inject
//     * @var \App\Application\AddAlbumToUser\AddAlbumToUser
//     */
//    private $addAlbumToUser;
//    
//    /**
//     * @Inject
//     * @var \App\Domain\Model\KekRepository
//     */
//    private $kekes;
//    
//    /**
//     * @Inject
//     * @var \App\Domain\Model\AuthenticationService
//     */
//    private $authService;
    
    private string $fakeId = '01fkeg2e04mp80vj7z62dff436';

    function createCollectionItem(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        
        $this->failIfRequiredParamWasNotPassed(
            $parsedBody, ['saved-type', 'saved-id']
        );
        $requestDTO = new CreateItemRequest(
            $this->fakeId, //$request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
            $parsedBody['saved-type'],
            $parsedBody['saved-id']
        );
        $useCase = new TransactionalApplicationService(
            $this->createCollectionItem, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function createCollection(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        
        $this->failIfRequiredParamWasNotPassed(
            $parsedBody, ['name', 'who_can_see']
        );
        $requestDTO = new CreateCollectionRequest(
            $this->fakeId, //$request->getAttribute('token')['data']->userId,
            $parsedBody['name'],
            isset($parsedBody['description']) ? $parsedBody['description'] : "",
            $parsedBody['who_can_see']
        );
        $useCase = new TransactionalApplicationService(
            $this->createCollection, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function getSavesCollections(Request $request, Response $response): Response {
        $queryParams = $request->getQueryParams();
        
        $requestDTO = new GetCollectionsRequest(
            $this->fakeId,
            $request->getAttribute('id'),
            isset($queryParams['offset-id']) ? $queryParams['offset-id'] : null,
            isset($queryParams['count']) ? $queryParams['count'] : 0,
            isset($queryParams['order']) ? $queryParams['order'] : null,
        );
        $useCase = new TransactionalApplicationService(
            $this->getCollections, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function getCollectionItems(Request $request, Response $response): Response {
        $queryParams = $request->getQueryParams();
        
        $requestDTO = new GetItemsRequest(
            $this->fakeId,
            $request->getAttribute('id'),
            isset($queryParams['offset-id']) ? $queryParams['offset-id'] : null,
            isset($queryParams['count']) ? $queryParams['count'] : 0,
            isset($queryParams['order']) ? $queryParams['order'] : null,
            isset($queryParams['comments-order']) ? $queryParams['comments-order'] : null,
            isset($queryParams['comments-type']) ? $queryParams['comments-type'] : null,
            isset($queryParams['comments-count']) ? $queryParams['comments-count'] : null
        );
        $useCase = new TransactionalApplicationService(
            $this->getCollectionItems, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function getReacted(Request $request, Response $response): Response {
        $queryParams = $request->getQueryParams();
        
        $requestDTO = new GetReactedRequest(
            $this->fakeId,
            $request->getAttribute('id'),
            isset($queryParams['comments-count']) ? $queryParams['comments-count'] : null,
            isset($queryParams['comments-type']) ? $queryParams['comments-type'] : null,
            isset($queryParams['comments-order']) ? $queryParams['comments-order'] : null,
            isset($queryParams['offset-id']) ? $queryParams['offset-id'] : null,
            isset($queryParams['count']) ? $queryParams['count'] : null,
            isset($queryParams['types']) ? $queryParams['types'] : null
        );
        $useCase = new TransactionalApplicationService(
            $this->getReacted, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function createTempPhoto(Request $request, Response $response) {
        $requestDTO = new CreateTempPhotoRequest(
            $this->fakeId, //$request->getAttribute('token')['data']->userId,
            isset($request->getUploadedFiles()['photo']) ? $request->getUploadedFiles()['photo'] : null
        );
        $useCase = new TransactionalApplicationService(
            $this->createTempPhoto, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO));
    }

    function createCommentPhoto(Request $request, Response $response) {
        $uploadedFiles = $request->getUploadedFiles();
        if(!isset($uploadedFiles['photo'])) {
            throw new MalformedRequestException("File 'photo' should be passed");
        }
        $requestDTO = new CreateCommentPhotoRequest(
            $request->getAttribute('token')['data']->userId,
            $uploadedFiles['photo']
        );
        $useCase = new TransactionalApplicationService(
            $this->createCommentPhoto, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO), 201);
    }
    
    function getCommentPhoto(Request $request, Response $response) {
        $requestDTO = new GetCommentPhotoRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id')
        );
        $useCase = new TransactionalApplicationService(
            $this->getCommentPhoto, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO));
    }
    
    
    function createBan(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody() ?? [];
        
        $this->failIfRequiredParamWasNotPassed($parsedBody, ['user_id']);
        $requestDTO = new CreateBanRequest(
            $this->fakeId,
            $parsedBody['user_id']
        );
        $useCase = new TransactionalApplicationService(
            $this->createBan, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO));
    }
    
    function updateBan(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody() ?? [];
        
        $requestDTO = new UpdateBanRequest(
            $this->fakeId,
            $request->getAttribute('id'),
            $parsedBody
        );
        $useCase = new TransactionalApplicationService(
            $this->updateBan, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO));
    }
    
    function getProfile(Request $request, Response $response): Response {
        //print_r($request->getAttribute('token'));
        $requestDTO = new GetUserRequest(
            $request->getAttribute('token')['data']['userId'],
            $request->getAttribute('id')
        );
        $useCase = new TransactionalApplicationService(
            $this->getProfile, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function search(Request $request, Response $response): Response {
        $queryParams = $request->getQueryParams();
        
        $requestDTO = new SearchRequest(
            $request->getAttribute('token')['data']['userId'],
            isset($queryParams['query']) ? $queryParams['query'] : '',
            isset($queryParams['cursor']) ? $queryParams['cursor'] : null,
            isset($queryParams['count']) ? $queryParams['count'] : null,
            isset($queryParams['full-profile']) ? $queryParams['full-profile'] : null,
        );
        $useCase = new TransactionalApplicationService(
            $this->search, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function getContacts(Request $request, Response $response): Response {
        $queryParams = $request->getQueryParams();
        
        $requestDTO = new GetContactsRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('userId'),
            isset($queryParams['common-with']) ? $queryParams['common-with'] : null,
            isset($queryParams['cursor']) ? $queryParams['cursor'] : null,
            isset($queryParams['count']) ? $queryParams['count'] : null,
        );
        $useCase = new TransactionalApplicationService(
            $this->getContacts, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function patchUser(Request $request, Response $response) {
        $userId = $request->getAttribute('id');
        $requesterId = $request->getAttribute('token')['data']->userId;

        $payload = $request->getParsedBody();
        $requestDTO = new PatchUserRequest($requesterId, $userId, $payload);
        
        $useCase = new TransactionalApplicationService(
            $this->patchUser, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }

//    function createKek(Request $request, Response $response) {
//        
//        
//        foreach($request->getParsedBody() as $field => $value) {
//            echo $field . ' - - - - - ';
//            print_r($value);
//        }
//        exit();
//        
//        $signUpData = [];
//        $signUpData['email'] = 'dasdad';
//        $signUpData['firstname'] = 'sdfasdfsdf asfasdfasdfsdfasdfasdf asdf asdfasdfsdfafsdfsdfsdfsdfsadf';
//        $signUpData['lastname'] = 'd';
//        $signUpData['password'] = 'd';
//        $signUpData['gender'] = 'broiler747';
//        
//        Assert::lazy()
//            ->that($signUpData['email'], 'email')->string()->email()
//            ->that($signUpData['firstname'], 'firstName')->string()->minLength(2)->maxLength(30)
//            //->that('f', 'firstName')->string()->minLength(2)->maxLength(30)
//            ->that($signUpData['lastname'], 'lastname')->string()->minLength(2)->maxLength(30)
//            ->that($signUpData['password'], 'password')->string()->minLength(12)->maxLength(30)
//            ->that($signUpData['gender'], 'gender')->string()->regex('/^(male|female)$/')
//            ->verifyNow();
//        
//        return $this->prepareResponse($response, ['message' => 'ok'], 201);
//    }
//    
//    function getProperty(Request $request, Response $response) {
//        $userId = $request->getAttribute('id');
//        $propertyName = $request->getAttribute('propertyName');
//        $responseDTO = $this->getUserProperty->execute($userId, $propertyName);
//        return $this->prepareResponse($response, $responseDTO);
//    }
//
//    function getAvatar(Request $request, Response $response) {
//        $userId = $request->getAttribute('id');
//        return $this->prepareResponse($response, $this->getAvatar->execute($userId));
//    }
//    
//    function getUser(Request $request, Response $response) {
//        $requesteeId = $request->getAttribute('id');
//        $requesterId = $this->authService->currentUserId();
//
//        $responseDTO = $this->getUser->execute($requesterId, $requesteeId);
//        return $this->prepareResponse($response, $responseDTO);
//    }
//
//    function getUsers(Request $request, Response $response) {
//        $requesterId = $this->authService->currentUserId();
//        $page = $request->getQueryParams()['page'] ?? null;
//        $count = $request->getQueryParams()['count'] ?? null;
//        $ids = $request->getQueryParams()['ids'] ?? null;
//        $username = $request->getQueryParams()['username'] ?? null;
//        $requestDTO = new GetUsersRequest($requesterId, $page, $count, $ids, $username);
//        $responseDTO = $this->getUsers->execute($requestDTO);
//        return $this->prepareResponse($response, $responseDTO);
//    }
//

//    
//    function addAlbumToUser(Request $request, Response $response) {
//        $userId = $request->getAttribute('id');
//        $albumId = $request->getParsedBody()['albumId'];
//        $this->addAlbumToUser->execute($albumId, $userId);
//        return $this->prepareResponse($response, ['message' => 'ok']);
//    }
//
//    function changeAvatar(Request $request, Response $response) {
//        //echo 'kek';exit();
//        $requesteeId = $request->getAttribute('id');
//        $requesterId = $this->authService->currentUserId();
//        $this->changeAvatar->execute($requesterId, $requesteeId);
//        return $this->prepareResponse($response, ['message' => 'avatar has changed']);
//        //} catch (\OverflowException $ex) { // количество изображений в альбоме "Фотографии со страницы" достигло максимума
//    }
//
//    function getAvatarChangePage(Request $request, Response $response) {
//        $responseBody = $this->templateEngine->render('loadAvatar.twig', array());
//        $response->getBody()->write($responseBody);
//        $this->emitter->emit($response);
//        exit();
//    }
//
//    function changeInfo(int $changingUserId, string $attributeName) {
//        $this->rejectNotJSONContentType();
//        $newAttributeValue = $this->decodeJSONRequestBody()['newValue'] ?? '';
//
//        $requestDTO = new ChangeInfoRequest($changingUserId, $attributeName, $newAttributeValue);
//        $useCase = new ChangeInfo($this->users, $this->rbac, $this->sessionService);
//
//        try{
//            $responseDTO = $useCase->execute($requestDTO);
//            $responseData = (array)$responseDTO;
//            $statusCode = 200;
//        } catch (NotPermittedException $ex) {
//            $responseData['errors'][] = $ex->getMessage();
//            $statusCode = 401;
//        } catch (NotAuthenticatedException $ex) {
//            $responseData['errors'][] = $ex->getMessage();
//            $statusCode = 403;
//        }
//
//        $headers = [];
//        $this->emitJSON($statusCode, $headers, $responseData);
//    }
}
