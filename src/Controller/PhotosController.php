<?php
declare(strict_types=1);
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Exceptions\UnprocessableRequestException;
use App\Application\TransactionalApplicationService;
use App\Application\Users\CreateProfilePhoto\CreateProfilePhotoRequest;
use App\Application\Users\Photos\Create\CreateRequest;
use App\Application\Users\Photos\Delete\DeleteRequest;
use App\Application\Users\ProfilePicture\Create\CreateRequest as CreateProfilePictureRequest;
use App\Application\Users\ProfilePicture\Get\GetRequest as GetProfilePictureRequest;
use App\Application\Users\Photos\CreateComment\CreateCommentRequest;
use App\Application\Users\Photos\CreateReaction\CreateReactionRequest;
use App\Application\Users\Photos\Update\UpdateRequest;
use App\Application\Users\Photos\CreateCommentReaction\CreateCommentReactionRequest;
use App\Application\Users\Photos\UpdateCommentReaction\UpdateCommentReactionRequest;
use App\Application\Users\Photos\UpdateReaction\UpdateReactionRequest;
use App\Application\Users\Photos\DeleteCommentReaction\DeleteCommentReactionRequest;
use App\Application\Users\Photos\Get\GetRequest;
use App\Application\Users\Photos\UpdateComment\UpdateCommentRequest;
use App\Application\Users\Photos\DeleteReaction\DeleteReactionRequest;
use App\Application\Users\Photos\GetAll\GetAllRequest;
use App\Application\Users\Cover\Create\CreateRequest as CreateCoverRequest;
use App\Application\Users\Cover\Get\GetRequest as GetCoverRequest;
use App\Application\Exceptions\MalformedRequestException;

class PhotosController extends AbstractController {
    /**
     * @Inject
     * @var \App\Application\TransactionalSession
     */
    private $transactionalSession;
    
    /**
     * @Inject
     * @var \App\Application\Users\ProfilePicture\Create\Create
     */
    private $createProfilePicture;
    
    /**
     * @Inject
     * @var \App\Application\Users\Cover\Create\Create
     */
    private $createCover;
    
    /**
     * @Inject
     * @var \App\Application\Users\ProfilePicture\Get\Get
     */
    private $getProfilePicture;
    
    /**
     * @Inject
     * @var \App\Application\Users\Cover\Get\Get
     */
    private $getCover;
    
    private string $fakeId = '01f5c16qts25jhsfx8efjev0qb';
    
    function createProfilePicture(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody() ?? [];
        
        $uploadedFiles = $request->getUploadedFiles();
        if(!ISSET($uploadedFiles['photo'])) {
            throw new MalformedRequestException("File 'photo' should be passed");
        }
        $this->failIfRequiredParamWasNotPassed($parsedBody, ['x', 'y', 'width']);
        
        $requestDTO = new CreateProfilePictureRequest(
            $request->getAttribute('token')['data']->userId,
            $uploadedFiles['photo'],
            $parsedBody['x'], $parsedBody['y'], $parsedBody['width']
        );
        $useCase = new TransactionalApplicationService(
            $this->createProfilePicture, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO), 201);
    }
    
    function createCover(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody() ?? [];
        
        $uploadedFiles = $request->getUploadedFiles();
        if(!ISSET($uploadedFiles['photo'])) {
            throw new MalformedRequestException(123, "File 'photo' should be passed");
        }
        $this->failIfRequiredParamWasNotPassed($parsedBody, ['x', 'y', 'width']);
        
        $requestDTO = new CreateCoverRequest(
            $request->getAttribute('token')['data']->userId,
            $uploadedFiles['photo'],
            $parsedBody['x'], $parsedBody['y'], $parsedBody['width']
        );
        $useCase = new TransactionalApplicationService(
            $this->createCover, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO), 201);
    }
    
    function getProfilePicture(Request $request, Response $response) {
        $requestDTO = new GetProfilePictureRequest(
            $request->getAttribute('token')['data']['userId'],
            $request->getAttribute('id')
        );
        $useCase = new TransactionalApplicationService(
            $this->getProfilePicture, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO));
    }
    
    function getCover(Request $request, Response $response) {
        $requestDTO = new GetCoverRequest(
            $request->getAttribute('token')['data']['userId'],
            $request->getAttribute('id')
        );
        $useCase = new TransactionalApplicationService(
            $this->getCover, $this->transactionalSession
        );
        return $this->prepareResponse($response, $useCase->execute($requestDTO));
    }
    
}
