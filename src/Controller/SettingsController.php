<?php
declare(strict_types=1);
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Application\Users\UpdatePrivacy\UpdatePrivacyRequest;
use App\Application\Users\GetPrivacy\GetPrivacyRequest;
use App\Domain\Model\Identity\User\User;
use App\Application\TransactionalApplicationService;
use App\Application\Users\Settings\UpdateComplexPrivacySetting\UpdateComplexPrivacySetting;
use App\Application\Users\Settings\UpdateComplexPrivacySetting\UpdateComplexPrivacySettingRequest;
use App\Application\Users\Settings\UpdateSettings\UpdateSettingsRequest;
use App\Application\Users\Settings\GetSettings\GetSettingsRequest;

class SettingsController extends AbstractController {
    /**
     * @Inject
     * @var \App\Application\TransactionalSession
     */
    private $transactionalSession;
    /**
     * @Inject
     * @var \App\Application\Users\Settings\UpdatePrivacy\UpdatePrivacy
     */
    private $updatePrivacy;

    /**
     * @Inject
     * @var \App\Application\Users\Settings\UpdateSettings\UpdateSettings
     */
    private $editSettings;
    
    /**
     * @Inject
     * @var \App\Application\Users\Settings\GetSettings\GetSettings
     */
    private $getSettings;
    
    /**
     * @Inject
     * @var UpdateComplexPrivacySetting
     */
    private $updatePrivacySetting;
    
    private string $fakeId = "01fkeg2e04mp80vj7z62dff436";
    
    function editSettings(Request $request, Response $response): Response {
        $parsedBody = $request->getParsedBody() ?? [];
             
        $this->failIfRequiredParamWasNotPassed(
            $parsedBody, ['payload']
        );
        $requestDTO = new UpdateSettingsRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getAttribute('id'),
            $parsedBody['payload']
        );
        $useCase = new TransactionalApplicationService(
            $this->editSettings, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }

    function getSettings(Request $request, Response $response) {
        $requestDTO = new GetSettingsRequest(
            $request->getAttribute('token')['data']['userId'],
            $request->getAttribute('id')
        );
        $useCase = new TransactionalApplicationService(
            $this->getSettings, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function changeSetting(Request $request, Response $response) {
        $userId = $request->getAttribute('id');
        $setting = $request->getAttribute('setting');
        $newValue = $request->getParsedBody()['newValue'];
        $responseDTO = $this->changeSetting->execute($userId, $setting, $newValue);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function updatePrivacySetting(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        
        //echo $request->getAttribute("propertyName");exit();
        $this->failIfRequiredParamWasNotPassed($parsedBody, ['privacy_data']);
        
        $requestDTO = new UpdateComplexPrivacySettingRequest(
            '01f4p1meyv0eymbc9ryx62tz4d', //$request->getAttribute('token')['data']->userId,
            $request->getAttribute("propertyName"),
            $parsedBody['privacy_data']
        );
        $useCase = new TransactionalApplicationService(
            $this->updatePrivacySetting, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
//    function getPrivacyChangePage(Request $request, Response $response) {
//        $responseBody = $this->templateEngine->render('settings.html', array());
//        $response->getBody()->write($responseBody);
//        $this->emitter->emit($response);
//        exit();
//    }
//    
    function updatePrivacy(Request $request, Response $response) {
        $requestDTO = new UpdatePrivacyRequest(
            $request->getAttribute('token')['data']->userId,
            $request->getParsedBody()
        );
        $useCase = new TransactionalApplicationService(
            $this->updatePrivacy, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function getPrivacy(Request $request, Response $response) {
        $requestDTO = new GetPrivacyRequest(
            $request->getAttribute('token')['data']->userId
        );
        $useCase = new TransactionalApplicationService(
            $this->getPrivacy, $this->transactionalSession
        );
        $responseDTO = $useCase->execute($requestDTO);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function changeProfileType(Request $request, Response $response) {
        $newValue = $request->getParsedBody()['newValue'];
        $requestDTO = $this->changeProfileType->execute($newValue);
        return $this->prepareResponse($response, $responseDTO);
    }
    
    function getPrivacySettings(Request $request, Response $response) {
        $userId = $request->getAttribute('id');
        /** @var User $user */
        $user = $this->users->getById($userId);
        $responseData['settings'] = $user->allSettings();
        $responseData['profile_type'] = $user->isClosed() ? '13' : '12';
        $responseData['page_visibility'] = '10';
        $responseData['hidden_friends'] = ['5621', '109', '7657', '42344'];
        $responseData['friend_lists'] = $user->friendLists();
        $responseData['all_friends'] = [
            ['id' => '2', 'fullname' => 'Aleksey Lavrenyuk', 'microavatar' => '/images/camera_50.png'],
            ['id' => '5621', 'fullname' => 'Grisha Kekeshkin', 'microavatar' => '/images/camera_50.png'],
            ['id' => '7721', 'fullname' => 'Anna Rambler', 'microavatar' => '/images/DTc14W4ifgo.jpg'],
            ['id' => '6233', 'fullname' => 'Galya Vodichka', 'microavatar' => '/images/exeLj11eeYg.jpg'],
            ['id' => '13', 'fullname' => 'Dima Kirov', 'microavatar' => '/images/HQzxkwu763Q.jpg'],
            ['id' => '6546', 'fullname' => 'Denis Vonyuchiy', 'microavatar' => '/images/camera_50.png'],
            ['id' => '7886', 'fullname' => 'Aleksey Popov', 'microavatar' => '/images/camera_50.png'],
            ['id' => '109', 'fullname' => 'Anton Agusov', 'microavatar' => '/images/camera_50.png'],
            ['id' => '2209', 'fullname' => 'Galya Krug', 'microavatar' => '/images/F2rw23g42.jpg'],
            ['id' => '7657', 'fullname' => 'Anna Vodichka', 'microavatar' => '/images/ifTfcYYnNWQ.jpg'],
            ['id' => '234', 'fullname' => 'Gera Zubov', 'microavatar' => '/images/camera_50.png'],
            ['id' => '123123', 'fullname' => 'Artem Sraka', 'microavatar' => '/images/jlkwZmPIC9o.jpg'],
            ['id' => '42344', 'fullname' => 'Mihail Zubenko', 'microavatar' => '/images/ii4LTlFvZZ8.jpg']
        ];
        return $this->prepareResponse($response, $responseData);
    }

    function getChangePrivacySettingsPage() {
        if($this->sessionService->currentUserRoles()[0] === 'guest') {
            $currentUserId = $this->sessionService->currentUserId();
            header("Location: /users/1");
            exit();
        }
        $this->emitHTML(200, [], [],'change_privacy_settings.twig', []);
    }

    public function testPermissions() {
        $user = $this->userRepository->getById(1);
        print_r($user->getRoles()); exit();
    }

}