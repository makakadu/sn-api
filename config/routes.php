<?php
declare(strict_types=1);

use App\Controller\UserController;
use App\Controller\AuthController;
use App\Controller\SignUpController;
use App\Controller\VKAuthController;
use App\Controller\FBAuthController;
use App\Controller\DialoguesController;
use App\Controller\MessagesController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Controller\SettingsController;
use App\Controller\FriendsController;
use App\Controller\PhotosController;
use App\Controller\VideosController;
use App\Controller\UserPostsController;
use App\Controller\UserAlbumsController;
use App\Middlewares\JwtAuthMiddleware;
use App\Controller\ConnectionsController;
use App\Controller\UserSubscriptionsController;
use App\Controller\CommonController;
use App\Controller\PageController;
use App\Controller\PagePostsController;
use App\Controller\ChatsController;

return function (App $app) {
    
    $app->group('/v1', function (Group $group) {
        $group->get('/', function (Request $request, Response $response, $args) {
            $response->getBody()->write("Root");
            return $response;
        });
        $userController = UserController::class;

        $group->post('/api/tokens', \App\Controller\TokenCreateControllerHS256::class);
        $group->get('/changeAvatar', "$userController:getAvatarChangePage");
        //$group->get('/signup', SignUpController::class . ':getSignUpFormNew');
    //    $group->post('/signup', SignUpController::class . ':signUp')->add(JwtAuthMiddleware::class);
        $group->get('/chpr', SettingsController::class . ":getPrivacyChangePage");

        $group->group('/users', function (Group $group) use($userController) {
            $group->get('', "$userController:getProfile");
            $propExp = 'status|gender|firstName|country|city|birthday|isOnline';
            $group->put("/{id}/{propertyName:$propExp}", "$userController:changeProperty");
            $group->get('/{id}', "$userController:getProfile")->add(JwtAuthMiddleware::class);
            $group->post('/{id}/avatar', "$userController:changeAvatar");
            $group->get('/{id}/avatar', "$userController:getAvatar");
            $group->get('/{id}/privacy', SettingsController::class . ":getPrivacySettings");
            $group->put('/{id}/change_privacy', SettingsController::class . ":changePrivacySetting");  
            $group->put('/{id}/profile_type', SettingsController::class . ":changeProfileType"); 
            $group->get('/{id}/settings', SettingsController::class . ":getSettings")->add(JwtAuthMiddleware::class);
            $group->patch('/{id}/settings', SettingsController::class . ":editSettings");
            $group->post("/{id}/posts", UserPostsController::class.':create');
            $group->post("/{id}/albums", UserAlbumsController::class.':createUserAlbum');
            $group->get("/{id}/photos", PhotosController::class.':getAll');
            $group->get("/{id}/albums", UserAlbumsController::class.':getAlbums');
            $group->get("/{id}/albums/{albumId}", UserAlbumsController::class.':removeAlbum');
            //$group->patch("/{id}", UserController::class.":changeProperty2");
            $group->patch("/{id}", "$userController:patchUser");
            $group->get("/{id}/{propertyName:$propExp}", "$userController:getProperty");
            $changeSettingExp = 'language|themeIsDark';
            $group->patch("/{id}/{setting:$changeSettingExp}", SettingsController::class . ":changeSetting");
            $group->get("/{id}/reacted", "$userController:getReacted");
            $group->get("/{id}/saves-collections", "$userController:getSavesCollections");
            $group->get("/{id}/posts", UserPostsController::class . ":getPart")->add(JwtAuthMiddleware::class);
            $group->get("/{userId}/connections", ConnectionsController::class . ':getPart');//->add(JwtAuthMiddleware::class);
            $group->get("/{userId}/contacts", UserController::class . ':getContacts');//->add(JwtAuthMiddleware::class);
            $group->get("/{userId}/subscriptions", UserSubscriptionsController::class . ':getPart');
            $group->get("/{userId}/subscribers", UserSubscriptionsController::class . ':getSubscribersPart');
            $group->get('/{id}/chats', ChatsController::class . ":getPart");
        });   
        
        $group->group('/chats', function (Group $group) {
            $group->post('', ChatsController::class . ":create");
            $group->post('/{id}/messages', ChatsController::class . ":createMessage");
            $group->get('/{id}', ChatsController::class . ":get");
            $group->get('/{id}/messages', ChatsController::class . ":getMessagesPart");
            $group->get('/{id}/actions', ChatsController::class . ":getActionsPart");
            $group->patch('/{id}', ChatsController::class . ":patch");
            $group->delete('/{id}/messages/{messageId}', ChatsController::class . ":deleteMessage");
            $group->delete('/{id}/messages', ChatsController::class . ":deleteHistory");
//            $group->patch('/{id}', UserController::class . ":updateBan");
        });
        $group->post('/typing-message', ChatsController::class . ":typingMessage");
        
        $group->group('/messages', function (Group $group) {
            $group->get('/{id}', ChatsController::class.':getMessage');
            $changePropExp = 'isRead|text';
            $group->put("/{messageId}/{propertyName:$changePropExp}", MessagesController::class.':changeMessage');
            $group->delete('/{messageId}', MessagesController::class.':removeMessage');
            $group->patch('/{id}', ChatsController::class.':patchMessage');
        });

        $group->group('/user-bans', function (Group $group) {
            $group->post('', UserController::class . ":createBan");
            $group->patch('/{id}', UserController::class . ":updateBan");
        });

        $group->group('/saves-collections', function (Group $group) use($userController)  {
            $group->post("/{id}/items", "$userController:createCollectionItem");
            $group->post("", "$userController:createCollection");
            $group->get("/{id}/items", "$userController:getCollectionItems");
        });

        $group->group('/user-subscriptions', function (Group $group) {
            $group->post('', UserSubscriptionsController::class . ":create");
            $group->patch('/{id}', UserSubscriptionsController::class . ":update");
            $group->delete('/{id}', UserSubscriptionsController::class . ":delete");
            $group->get('/{id}', UserSubscriptionsController::class . ":get");
        });//->add(JwtMiddlewareHS256::class);

        $group->group('/user-post-photos', function (Group $group) {
            $group->post('', \App\Controller\UserPostsController::class . ":createPhoto");
            $group->get('/{id}', \App\Controller\UserPostsController::class . ":getPhoto");
            $group->patch('/{id}', \App\Controller\UserPostsController::class . ":updatePhoto");
        });

        $group->group('/user-comment-photos', function (Group $group) {
            $group->post('', \App\Controller\UserController::class . ":createCommentPhoto");
            $group->get('/{id}', \App\Controller\UserController::class . ":getCommentPhoto");
            $group->patch('/{id}', \App\Controller\UserController::class . ":updateCommentPhoto");
        });

        $group->get('/posts', CommonController::class . ':getPosts');
        $group->get('/feed', CommonController::class . ':feed');

        $group->get('/privacy', SettingsController::class . ':getPrivacy');
        $group->patch('/privacy', SettingsController::class . ':updatePrivacy');

        $propExp = 'info|tagged_photos|hidden_friends';
        $group->patch("/privacy/{propertyName:$propExp}", SettingsController::class . ':updatePrivacySetting');


        $group->group('/connections', function (Group $group) {
            $group->post("", ConnectionsController::class . ':create');
            $group->get("/{id}", ConnectionsController::class . ':get');
            $group->patch("/{id}", ConnectionsController::class . ':update');
            $group->delete("/{id}", ConnectionsController::class . ':delete');
        });

        $group->patch('/accept-connection', ConnectionsController::class . ':accept');
        $group->patch('/accept-membership', \App\Controller\GroupController::class . ':acceptMembership');
        $group->patch('/delete-membership', \App\Controller\GroupController::class . ':deleteMembership');

        $group->group('/connections-lists', function (Group $group) {
            $group->post("", ConnectionsController::class . ':createList');
        });


        $group->get("/search", UserController::class . ':search')->add(JwtAuthMiddleware::class);

        $group->post("/saved-photos", PhotosController::class.':savePhoto');
        $group->post("/profile-avatars", PhotosController::class.':createProfilePhoto');

        $group->group('/groups', function (Group $group) use($userController) {
            $group->post('', \App\Controller\GroupController::class . ":create");
            $group->post('/{id}/posts', \App\Controller\GroupPostsController::class . ":create");
            $group->post('/{id}/managers', \App\Controller\GroupController::class . ":createManager");
            $group->post('/{id}/bans', \App\Controller\GroupController::class . ":createBan");
            $group->patch('/{id}/settings', \App\Controller\GroupSettingsController::class . ":patch");
            $group->post('/{id}/memberships', \App\Controller\GroupController::class . ":createMembership");
    //        $propExp = 'status|gender|firstName|country|city|birthday|isOnline';
    //        $group->put("/{id}/{propertyName:$propExp}", "$userController:changeProperty");
    //        $group->get('/{id}', "$userController:getUser");//->add(JwtAuthHS256Middleware::class);
    //        $group->post('/{id}/avatar', "$userController:changeAvatar");
        });

        $group->group('/group-posts', function (Group $group) {
            //$group->post('', \App\Controller\GroupPostsController::class . ":create");
            $group->post('/{id}/comments', \App\Controller\GroupPostsController::class . ":createComment");
            $group->post('/{id}/reactions', \App\Controller\GroupPostsController::class . ":createReaction");
        });

        $group->group('/temp-photos', function (Group $group) {
            $group->post('', \App\Controller\UserController::class . ":createTempPhoto");
        });

        $group->group('/pages', function (Group $group) {
            $group->post('', PageController::class . ":create");
            $group->patch('/{id}', PageController::class . ":patch");
            $group->post('/{id}/managers', PageController::class . ":createManager");
            $group->post('/{id}/bans', PageController::class . ":createBan");
            $group->post('/{id}/posts', PagePostsController::class . ":create");
            $group->get('/{id}/posts', PagePostsController::class . ":getPart");
            $group->post('/{id}/suggested-posts', PagePostsController::class . ":createSuggested");
            $group->get('/{id}/suggested-posts', PagePostsController::class . ":getPartOfSuggested");
            $group->post('/{id}/posts-from-suggested', PagePostsController::class . ":createPostFromSuggested");
        });

        $group->group('/auth', function (Group $group) {
            //$group->map(['GET', 'POST'], '/me', AuthController::class.':checkAuth');
            $group->get('/me', AuthController::class.':getMe');//->add(JwtAuthMiddleware::class);
            $group->post('/login', AuthController::class.':signIn')->add(JwtAuthMiddleware::class);
            $group->delete('/login', AuthController::class.':signOut');
            $group->get('/vk', VKAuthController::class);
            $group->get('/fb', AuthController::class.':facebookSignIn');
            $group->post('/signup', SignUpController::class . ':signUp')->add(JwtAuthMiddleware::class);
        });//->add($echoSrakaMiddleware);

        $group->group('/friendship_requests', function (Group $group) {
            $group->post('', FriendsController::class.':offerFriendship');
            $group->delete('/{id:\d+-\d+}', FriendsController::class.':denyFriendship');

    //        $group->delete('', function ($request, $response, $args) {
    //            echo 'lol';exit();
    //        });
        });//->add($echoSrakaMiddleware);

        $group->group('/friendships', function (Group $group) {
            $group->post('', FriendsController::class.':createFriendship');
            $group->patch('/{id}', FriendsController::class.':acceptFriendship');
            $group->delete('/{id}', FriendsController::class.':destroyFriendship');
        });

        $group->group('/dialogues', function (Group $group) {
            $group->post('', DialoguesController::class.':createDialogue');
            $group->get('', DialoguesController::class.':getDialogues');
            $group->get('/{dialogueId}', DialoguesController::class.':getDialogue');
            $group->delete('/{dialogueId}', DialoguesController::class.':removeDialogue');
            $group->post('/{dialogueId}/messages', MessagesController::class.':createMessage');
            $group->get('/{dialogueId}/messages', MessagesController::class.':getMessages');
            //$group->get('/{dialogueId}/messages-histories/{historyId}/messages', DialoguesController::class.':getHistoryMessages');
        });



        $group->group('/messages-histories', function (Group $group) {
            //$group->get('/{historyId}', DialoguesController::class.':getMessage');
            $group->get('/{historyId}/messages', DialoguesController::class.':getHistoryMessages');
            $group->get('', DialoguesController::class.':getMessagesHistories');
            $group->delete('/{historyId}/messages/{messageId}', DialoguesController::class.':removeMessageFromHistory');
        });

        // /profile-photo-albums/{id}/photos

        $group->group('/profile-pictures', function (Group $group) {       
            $group->post("", PhotosController::class.':createProfilePicture');
            $group->get("/{id}", PhotosController::class.':getProfilePicture')->add(JwtAuthMiddleware::class);
            $group->post("/{id}/reactions", PhotosController::class.':createReaction');
    //        $group->get("/{id}", PhotosController::class.':getPhotos');
    //        $group->patch("/{id}/album", PhotosController::class.':movePhoto');
        });
        
        $group->group('/profile-covers', function (Group $group) {       
            $group->post("", PhotosController::class.':createCover');
            $group->get("/{id}", PhotosController::class.':getCover')->add(JwtAuthMiddleware::class);
        });
    //    
    //    $group->group('/user-photos', function (Group $group) {
    //        //$group->get('/{photoId}', PhotosController::class.':getPhoto');
    //        //$changePropExp = 'isRead|text';
    //        $group->post("/", PhotosController::class.':createPhoto');
    //        
    //        $group->get("/{id}", PhotosController::class.':getPhoto');
    //        $group->get("/{id}", PhotosController::class.':getPhotos');
    //        $group->patch("/{id}/album", PhotosController::class.':movePhoto');
    //    });

        $group->group('/user-photos', function (Group $group) {
            $group->post("", PhotosController::class.':create');
            $group->delete("/{id}", PhotosController::class.':delete');
            $group->get("/{id}", PhotosController::class.':get');
            $group->patch("/{id}", PhotosController::class.':update');
            $group->post("/{id}/comments", PhotosController::class.':createComment');
            $group->post("/{id}/reactions", PhotosController::class.':createReaction');
        });

        $group->group('/user-photo-comments', function (Group $group) {
            $group->post("/{id}/reactions", PhotosController::class.':createCommentReaction');
            $group->patch("/{id}", PhotosController::class.':updateComment');
            $group->delete("/{id}", PhotosController::class.':deleteComment');
        });

        $group->group('/user-photo-reactions', function (Group $group) {
            $group->patch("/{id}", PhotosController::class.':updateReaction');
            $group->delete("/{id}", PhotosController::class.':deleteReaction');
        });

        $group->group('/user-photo-comment-reactions', function (Group $group) {
            $group->post("/{id}/reactions", PhotosController::class.':createCommentReaction');
            $group->patch("/{id}", PhotosController::class.':updateCommentReaction');
            $group->delete("/{id}", PhotosController::class.':deleteCommentReaction');
        });

        $group->group('/user-videos', function (Group $group) {
            $group->post("", VideosController::class.':create');
            $group->get("/{id}", VideosController::class.':get');
            $group->post('/{id}/reactions', VideosController::class.':createReaction');
        });

        $group->group('/user-video-playlists', function (Group $group) {
            $group->post("", VideosController::class.':createPlaylist');
            $group->patch("/{id}", VideosController::class.':updatePlaylist');
           // $group->get("/{id}", VideosController::class.':get');
        });

        $group->group('/user-posts', function (Group $group) {
            $group->get("/{id}", UserPostsController::class.':get')->add(JwtAuthMiddleware::class);
            $group->get("/{postId}/comments", UserPostsController::class.':getPostComments')->add(JwtAuthMiddleware::class);
            $group->post("", UserPostsController::class.':create');
            $group->patch("/{id}", UserPostsController::class.':patch');
            $group->put("/{id}", UserPostsController::class.':put');
    //        $group->get("", UserPostsController::class.':getProfilePosts');
            $group->post('/{id}/comments', UserPostsController::class.':createComment');
            $group->post('/{postId}/comments/{commentId}/replies', UserPostsController::class.':replyUserPostComment');
            $group->post('/{id}/reactions', UserPostsController::class.':createReaction');
            $group->patch('/{postId}/reactions/{reactionId}', UserPostsController::class.':editReaction');
            $group->delete('/{postId}/reactions/{reactionId}', UserPostsController::class.':deleteReaction');
            $group->get('/{postId}/reactions/{reactionId}', UserPostsController::class.':getPostReaction')->add(JwtAuthMiddleware::class);
            $group->get('/{postId}/reactions', UserPostsController::class.':getPostReactions')->add(JwtAuthMiddleware::class);
            $group->get('/{postId}/likes', UserPostsController::class.':getLikes')->add(JwtAuthMiddleware::class);
            $group->patch("/{id}/{propertyName:deleted|deleted_by_global_moderation}", UserPostsController::class.':delete');
        });

        $group->group('/page-posts', function (Group $group) {
            $group->get("/{id}", PagePostsController::class.':get')->add(JwtAuthMiddleware::class);
            $group->post('/{id}/comments', PagePostsController::class.':createComment');
            $group->get("/{id}/comments", PagePostsController::class.':getPostComments')->add(JwtAuthMiddleware::class);
            $group->post("", PagePostsController::class.':create');
            $group->patch("/{id}", PagePostsController::class.':patch');
            $group->put("/{id}", PagePostsController::class.':put');
    //        $group->get("", UserPostsController::class.':getProfilePosts');

            $group->post('/{postId}/comments/{commentId}/replies', PagePostsController::class.':replyUserPostComment');
            $group->post('/{id}/reactions', PagePostsController::class.':createReaction');
            $group->patch('/{postId}/reactions/{reactionId}', PagePostsController::class.':updateReaction');
            $group->delete('/{postId}/reactions/{reactionId}', PagePostsController::class.':deleteReaction');
            $group->get('/{postId}/likes', PagePostsController::class.':getLikes');
            $group->delete('/{id}', PagePostsController::class.':delete');
        });

        $group->group('/suggested-posts', function (Group $group) {
            $group->patch("/{id}", PagePostsController::class.':patchSuggested');
        });

        $group->group('/page-post-comments', function (Group $group) {
            //$group->get("/{id}", UserPostsController::class.':getPost')->add(JwtAuthMiddleware::class);
            //$group->patch("/{id}", UserPostsController::class.':updatePost');
            $group->post('/{id}/replies', UserPostsController::class.':replyComment');
            $group->put('/{id}', PagePostsController::class.':updateComment');
            $group->delete('/{id}', UserPostsController::class.':removeComment');
            $group->post('/{id}/reactions', PagePostsController::class.':createCommentReaction');
            $group->patch('/{commentId}/reactions/{reactionId}', PagePostsController::class.':updateCommentReaction');
            //$group->post('/{postId}/comments/{commentId}/replies', UserPostsController::class.':replyUserPostComment');
        });

        $group->group('/user-post-comments', function (Group $group) {
            //$group->get("/{id}", UserPostsController::class.':getPost')->add(JwtAuthMiddleware::class);
            //$group->patch("/{id}", UserPostsController::class.':updatePost');
            $group->post('/{commentId}/replies', UserPostsController::class.':replyComment');
            $group->delete('/{commentId}', UserPostsController::class.':removeComment');
            $group->patch('/{commentId}', UserPostsController::class.':patchComment');
            $group->put('/{commentId}', UserPostsController::class.':putComment');
            $group->post('/{id}/reactions', UserPostsController::class.':createCommentReaction');
            $group->get('/{commentId}/reactions/{reactionId}', UserPostsController::class.':getCommentReaction')->add(JwtAuthMiddleware::class);
            $group->get('/{commentId}/reactions', UserPostsController::class.':getCommentReactions')->add(JwtAuthMiddleware::class);
            $group->patch('/{commentId}/reactions/{reactionId}', UserPostsController::class.':editCommentReaction');
            $group->delete('/{commentId}/reactions/{reactionId}', UserPostsController::class.':deleteCommentReaction');
            //$group->post('/{postId}/comments/{commentId}/replies', UserPostsController::class.':replyUserPostComment');
            $group->get("/{commentId}/replies", UserPostsController::class.':getCommentReplies')->add(JwtAuthMiddleware::class);
            $group->get("/{id}", UserPostsController::class.':getComment')->add(JwtAuthMiddleware::class)->add(JwtAuthMiddleware::class);
        });

        $group->group('/group_posts', function (Group $group) {
            $group->get("/{id}", UserPostsController::class.':getGroupPost');
        });


        $group->group('/user-albums', function (Group $group) {
            $group->post("", UserAlbumsController::class.':create');
            $group->delete("/{id}", UserAlbumsController::class.':delete');
            $group->patch("/{id}", UserAlbumsController::class.':update');
            $group->patch("/{id}/deleted", UserAlbumsController::class.':delete');
            $group->get("/{id}", UserAlbumsController::class.':getUserAlbum')->add(JwtAuthMiddleware::class);
            $group->get("/{id}/photos", UserAlbumsController::class.':getPhotos');
            $group->post("/{albumId}/photos", PhotosController::class.':addPhotoToAlbum');
        });
    });
   
};
