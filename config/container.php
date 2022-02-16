<?php

use function DI\autowire;
use function DI\create;
use function DI\get;

use Slim\Factory\AppFactory;

use App\Infrastructure\Domain\Model\Users\User\Doctrine\UserDoctrineRepository;
use App\Infrastructure\Domain\Model\Identity\UnconfirmedUser\Doctrine\UnconfirmedUserDoctrineRepository;
use App\Domain\Model\Users\User\UserRepository;
use App\Domain\Model\Identity\UnconfirmedUser\UnconfirmedUserRepository;
use App\Domain\Model\AuthenticationService;
use App\Infrastructure\Authentication\AuthService;
use App\Permissions\RbacInterface;
use App\Permissions\LaminasRbac;
use App\AbstractTemplateEngine;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Cache\MemcachedCache;
use Doctrine\Common\Cache\FilesystemCache;
use Psr\Container\ContainerInterface;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use App\Auth\JwtAuthHS256;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use App\Controller\VKAuthController;
use App\Application\FBAuthenticator;
use App\Domain\Model\Dialogue\MessageRepository;
use App\Infrastructure\Domain\Model\Dialogue\Doctrine\MessageDoctrineRepository;
//use App\Domain\Model\Users\Friendship\FriendshipRepository;
use App\Infrastructure\Domain\Model\Users\Friendship\Doctrine\FriendshipDoctrineRepository;
use App\Domain\Model\Dialogue\DialogueRepository;
use App\Infrastructure\Domain\Model\Dialogue\Doctrine\DialogueDoctrineRepository;
use App\Domain\Model\Users\Photos\PhotoRepository;
use App\Infrastructure\Domain\Model\Users\Photos\Doctrine\PhotoDoctrineRepository;
use App\Domain\Model\Users\Post\PostRepository as UserPostRepository;
use App\Infrastructure\Domain\Model\Users\Post\Doctrine\PostDoctrineRepository as UserPostDoctrineRepository;
use App\Domain\Model\Groups\Post\PostRepository as GroupPostRepository;
use App\Infrastructure\Domain\Model\Groups\Post\Doctrine\PostDoctrineRepository as GroupPostDoctrineRepository;

use App\Domain\Model\KekRepository;
use App\Infrastructure\Domain\Model\Kek\Doctrine\KekDoctrineRepository;
use App\DomainOld\Model\LolRepository;
use App\Infrastructure\Domain\Model\Lol\Doctrine\LolDoctrineRepository;
use App\Domain\Model\ImageService;
use App\Infrastructure\Domain\Model\SimpleImageImageService;
use App\Infrastructure\Domain\Model\Dialogue\Doctrine\MessagesHistoryDoctrineRepository;
use App\Domain\Model\Dialogue\MessagesHistoryRepository;
use App\Middlewares\JwtAuthMiddleware;
use App\Application\TransactionalSession;
use App\Application\DoctrineSession;
use App\Middlewares\ErrorsHandler;
use App\Factory\LoggerFactory;
use App\Middlewares\ServerErrorsHandlerMiddleware;
use App\Middlewares\ErrorsHandlerMiddleware;
use App\Application\RequestParamsValidator;
use App\Infrastructure\Application\BeberleiRequestParamsValidator;
use App\Domain\Model\Test\CheburekRepository;
use App\Infrastructure\Domain\Model\Test\CheburekDoctrineRepository;
use App\Domain\Model\Test\ArbidolRepository;
use App\Infrastructure\Domain\Model\Test\ArbidolDoctrineRepository;
use App\Domain\Model\Users\MediaRepository as UserMediaRepository;
use App\Infrastructure\Domain\Model\Users\Media\Doctrine\MediaDoctrineRepository as UserMediaDoctrineRepository;
use App\Domain\Model\Users\Post\Comment\CommentRepository as UserPostCommentRepository;
use App\Infrastructure\Domain\Model\Users\Post\Comment\Doctrine\CommentDoctrineRepository as UserPostCommentDoctrineRepository;
use App\Domain\Model\Users\Post\Comment\Reply\ReplyRepository as UserPostReplyRepository;
use App\Infrastructure\Domain\Model\Users\Post\Comment\Doctrine\ReplyDoctrineRepository as UserReplyDoctrineRepository;
use App\Domain\Model\Common\PostRepository as CommonPostRepository;
use App\Infrastructure\Domain\Model\Common\Post\Doctrine\CommonPostDoctrineRepository;
use App\Domain\Model\Common\CommentRepository as CommonCommentRepository;
use App\Infrastructure\Domain\Model\Common\Doctrine\CommentDoctrineRepository as CommonCommentDoctrineRepository;
use App\Domain\Model\Users\Connection\ConnectionRepository;
use App\Infrastructure\Domain\Model\Users\Connection\Doctrine\ConnectionDoctrineRepository;
use App\Domain\Model\Users\Subscription\SubscriptionRepository;
use App\Infrastructure\Domain\Model\Users\Subscription\Doctrine\SubscriptionDoctrineRepository;
use App\Domain\Model\Users\Albums\AlbumRepository;
use App\Infrastructure\Domain\Model\Users\Albums\Doctrine\AlbumDoctrineRepository;
use App\Domain\Model\Users\ConnectionsList\ConnectionsListRepository;
use App\Infrastructure\Domain\Model\Users\ConnectionsList\Doctrine\ConnectionsListDoctrineRepository;
use App\Domain\Model\Common\PhotoService;
use Pusher\Pusher;

define('PATH_TO_ROOT', dirname(__DIR__));

return [
    'settings' => function () {
        return require __DIR__ . '/settings.php';
    },
            
    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        return AppFactory::create();
    },
            
    Pusher::class => function (ContainerInterface $container) {
        $options = array(
          'cluster' => 'eu',
          'useTLS' => true
        );
        return new Pusher(
          'e6cbd9d805e4f4e0a599',
          '43fde7fde62ae7692e2c',
          '1334400',
          $options
        );
    },
//            
//      $options = array(
//    'cluster' => 'eu',
//    'useTLS' => true
//  );
//  $pusher = new Pusher\Pusher(
//    'e6cbd9d805e4f4e0a599',
//    '43fde7fde62ae7692e2c',
//    '1334400',
//    $options
//  );
//    
//    WarningsAndNoticesMiddleware::class => function (ContainerInterface $container) {
//        return new WarningsAndNoticesMiddleware($container->get(LoggerFactory::class), $_ENV['env'] === 'prod');
//    },
        
    LoggerFactory::class => function (ContainerInterface $container) {
        return new LoggerFactory($container->get('settings')['logger']);
    },
            
    ServerErrorsHandlerMiddleware::class => function (ContainerInterface $container) {
        return new ServerErrorsHandlerMiddleware(
            $container->get(ResponseFactoryInterface::class),
            $container->get(LoggerFactory::class),
            $container->get('settings')['env'] === 'prod'
        );
    },
            
    ErrorsHandlerMiddleware::class => function (ContainerInterface $container) {
        return new ErrorsHandlerMiddleware(
            $container->get(ResponseFactoryInterface::class),
            $container->get(LoggerFactory::class),
            $container->get('settings')['env'] === 'prod'
        );
    },
            
    ErrorsHandler::class => function (ContainerInterface $container) {
        $app = $container->get(App::class);
        $settings = $container->get('settings')['error'];

        return new ErrorsHandler(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            (bool)$settings['display_error_details'],
            (bool)$settings['log_errors'],
            (bool)$settings['log_error_details']
        );
    },
            
    FBAuthenticator::class => function (ContainerInterface $container) {
        return new FBAuthenticator(
            $_ENV['facebook_app_id'],
            $_ENV['facebook_app_secret'],
            $_ENV['facebook_redirect_uri']
        );
    },
            
    'secret' => function(ContainerInterface $container) {
        return $_ENV['secret'];
    },
    
    PhotoService::class => function (ContainerInterface $container) {
        $imagesStorage = $container->get('settings')['root'].'/api/images/';
        $forPhotosStorage = $imagesStorage . 'forphotos/';
        $forVideosStorage = $imagesStorage . 'forvideos/';
        $hostingApiKey = $_ENV['img_hosting_key'];
        return new PhotoService($imagesStorage, $forPhotosStorage, $forVideosStorage, $hostingApiKey);
    },
            
    \App\Domain\Model\Common\VideoService::class => function (ContainerInterface $container) {
        return new \App\Domain\Model\Common\VideoService(
            $container->get(PhotoService::class),
            $container->get('settings')['youtube_api_key']
        );
    },
            
    RequestParamsValidator::class => create(BeberleiRequestParamsValidator::class),
    
    JwtAuthMiddleware::class => function (ContainerInterface $container) {
        $options = [
            //'issuer' => 'localhost:1338',
            
            'secret' => $_ENV['secret'],
            'lifetime' => 1,
            'ignore' => ['^\/users\/\w+\/?$', '/kekes', '/auth/login', '/auth/me']
        ];
        return new JwtAuthMiddleware($container->get(AuthenticationService::class), $options);
    },
    VKAuthController::class => function(ContainerInterface $container) {
        return new VKAuthController($_ENV['app_id'], $_ENV['vk_secret'], $_ENV['redirect_uri']);
    },

//    ResponseInterface::class => function(ContainerInterface $container) {
//        return new JsonResponse([]);
//    },

    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(App::class)->getResponseFactory();
    },

    JwtAuthHS256::class => function (ContainerInterface $container) {
        //$config = $container->get(Configuration::class);

        $jwtSettings = $settings = $container->get('settings')['jwt'];
        
        $issuer = 'localhost:1338';
        $secret = $jwtSettings['secret'];
        $lifetime = $jwtSettings['lifetime'];
        
        //$privateKey = $_ENV['private_key'];
        //$publicKey = $_ENV['public_key'];

        return new JwtAuthHS256($issuer, $secret, $lifetime);
    },
            
    \App\Application\Kek::class => create(\App\Application\KekChild::class),
            
    \App\Application\GetUserProperty\GetUserProperty::class => autowire()
            //->property('users', get(UserRepository::class))
            ->property('friendships', get(ConnectionRepository::class)),
            //->property('rbac', get(RbacInterface::class))
            //->property('authService', get(AuthService::class)),

    RbacInterface::class => create(LaminasRbac::class),

    ImageService::class => function (ContainerInterface $container) {
        return new SimpleImageImageService('/var/www/withSlim/public/images/storage');
    },
            
    TransactionalSession::class => function (ContainerInterface $container) {
        return new DoctrineSession($container->get(EntityManager::class));
    },
    SessionService::class => create(),
    App\Application\Kek::class => create(App\Application\Lol::class),

    UserRepository::class => autowire(UserDoctrineRepository::class),
    UnconfirmedUserRepository::class => autowire(UnconfirmedUserDoctrineRepository::class),
    CheburekRepository::class => autowire(CheburekDoctrineRepository::class),
    ArbidolRepository::class => autowire(ArbidolDoctrineRepository::class),
    ConnectionRepository::class => autowire(FriendshipDoctrineRepository::class),
    PhotoRepository::class => autowire(PhotoDoctrineRepository::class),
    UserPostRepository::class => autowire(UserPostDoctrineRepository::class),
    GroupPostRepository::class => autowire(GroupPostDoctrineRepository::class),
    PhotoAlbumRepository::class => autowire(AlbumDoctrineRepository::class),
    DialogueRepository::class => autowire(DialogueDoctrineRepository::class),
    MessageRepository::class => autowire(MessageDoctrineRepository::class),
    KekRepository::class => autowire(KekDoctrineRepository::class),
    LolRepository::class => autowire(LolDoctrineRepository::class),
    MessagesHistoryRepository::class => autowire(MessagesHistoryDoctrineRepository::class),
    UserMediaRepository::class => autowire(UserMediaDoctrineRepository::class),
    UserPostCommentRepository::class => autowire(UserPostCommentDoctrineRepository::class),
    CommonPostRepository::class => autowire(CommonPostDoctrineRepository::class),
    CommonCommentRepository::class => autowire(CommonCommentDoctrineRepository::class),
    ConnectionRepository::class => autowire(ConnectionDoctrineRepository::class),
    SubscriptionRepository::class => autowire(SubscriptionDoctrineRepository::class),
    ConnectionsListRepository::class => autowire(ConnectionsListDoctrineRepository::class),
    \App\Domain\Model\Users\Post\Comment\CommentRepository::class => autowire(\App\Infrastructure\Domain\Model\Users\Post\Comment\Doctrine\CommentDoctrineRepository::class),
    \App\Domain\Model\Users\ProfilePicture\ProfilePictureRepository::class => autowire(\App\Infrastructure\Domain\Model\Users\ProfilePicture\Doctrine\ProfilePictureDoctrineRepository::class),
    \App\Domain\Model\Users\Videos\VideoRepository::class =>autowire(\App\Infrastructure\Domain\Model\Users\Videos\Doctrine\VideoDoctrineRepository::class),
    \App\Domain\Model\Users\Photos\Comment\CommentRepository::class => autowire(\App\Infrastructure\Domain\Model\Users\Photos\Comment\Doctrine\CommentDoctrineRepository::class),
    \App\Domain\Model\Users\Photos\Comment\ReactionRepository::class => autowire(\App\Infrastructure\Domain\Model\Users\Photos\Comment\Doctrine\ReactionDoctrineRepository::class),
    \App\Domain\Model\Users\Photos\ReactionRepository::class => autowire(\App\Infrastructure\Domain\Model\Users\Photos\Doctrine\ReactionDoctrineRepository::class),
    \App\Domain\Model\Groups\Photos\PhotoRepository::class => autowire(\App\Infrastructure\Domain\Model\Groups\Photos\Doctrine\PhotoDoctrineRepository::class),
    \App\Domain\Model\Groups\Videos\VideoRepository::class => autowire(\App\Infrastructure\Domain\Model\Groups\Videos\Doctrine\VideoDoctrineRepository::class),
    \App\Domain\Model\Pages\Videos\VideoRepository::class => autowire(\App\Infrastructure\Domain\Model\Pages\Videos\Doctrine\VideoDoctrineRepository::class),
    \App\Domain\Model\Pages\Photos\PhotoRepository::class => autowire(\App\Infrastructure\Domain\Model\Pages\Photos\Doctrine\PhotoDoctrineRepository::class),
    \App\Domain\Model\Users\Ban\BanRepository::class => autowire(\App\Infrastructure\Domain\Model\Users\Ban\Doctrine\BanDoctrineRepository::class),
    \App\Domain\Model\Pages\Post\PostRepository::class => autowire(\App\Infrastructure\Domain\Model\Pages\Post\Doctrine\PostDoctrineRepository::class),
    \App\Domain\Model\Common\PostRepository::class => autowire(\App\Infrastructure\Domain\Model\Common\Doctrine\PostDoctrineRepository::class),
    \App\Domain\Model\Groups\Group\GroupRepository::class => autowire(\App\Infrastructure\Domain\Model\Groups\Group\Doctrine\GroupDoctrineRepository::class),
    \App\Domain\Model\Pages\Page\PageRepository::class => autowire(\App\Infrastructure\Domain\Model\Pages\Page\Doctrine\PageDoctrineRepository::class),
    \App\Domain\Model\Common\Comments\Comment::class => autowire(\App\Infrastructure\Domain\Model\Common\Comments\Doctrine\CommentDoctrineRepository::class),
    \App\Domain\Model\Users\Post\Photo\PhotoRepository::class => autowire(\App\Infrastructure\Domain\Model\Users\Post\Photo\Doctrine\PhotoDoctrineRepository::class),
    \App\Domain\Model\Users\Comments\Photo\PhotoRepository::class => autowire(\App\Infrastructure\Domain\Model\Users\Comments\Photo\Doctrine\PhotoDoctrineRepository::class),
    \App\Domain\Model\Users\Playlists\VideoPlaylistRepository::class => autowire(\App\Infrastructure\Domain\Model\Users\Playlists\Doctrine\PlaylistDoctrineRepository::class),
    \App\Domain\Model\ProfileCommentRepository::class => autowire(\App\Infrastructure\Domain\Model\Users\Connection\Doctrine\ProfileCommentDoctrineRepository::class),
    \App\Domain\Model\Users\Photos\AlbumPhoto\AlbumPhotoRepository::class => autowire(\App\Infrastructure\Domain\Model\Users\Photos\AlbumPhoto\Doctrine\AlbumPhotoDoctrineRepository::class),
    \App\Domain\Model\Users\Photos\ProfilePicture\ProfilePictureRepository::class => autowire(\App\Infrastructure\Domain\Model\Users\Photos\ProfilePicture\Doctrine\ProfilePictureDoctrineRepository::class),
    \App\Domain\Model\Pages\Photos\AlbumPhoto\AlbumPhotoRepository::class => autowire(\App\Infrastructure\Domain\Model\Pages\Photos\AlbumPhoto\Doctrine\AlbumPhotoDoctrineRepository::class),
    \App\Domain\Model\Pages\Photos\PagePicture\PagePictureRepository::class => autowire(\App\Infrastructure\Domain\Model\Pages\Photos\PagePicture\Doctrine\PagePictureDoctrineRepository::class),
    \App\Domain\Model\Groups\Photos\AlbumPhoto\AlbumPhotoRepository::class => autowire(\App\Infrastructure\Domain\Model\Groups\Photos\AlbumPhoto\Doctrine\AlbumPhotoDoctrineRepository::class),
    \App\Domain\Model\Groups\Photos\GroupPicture\GroupPictureRepository::class => autowire(\App\Infrastructure\Domain\Model\Groups\Photos\GroupPicture\Doctrine\GroupPictureDoctrineRepository::class),
    \App\Domain\Model\Common\ReactionRepository::class => autowire(\App\Infrastructure\Domain\Model\Common\Reactions\Doctrine\ReactionDoctrineRepository::class),
    \App\Domain\Model\Users\SavesCollection\SavesCollectionRepository::class => autowire(\App\Infrastructure\Domain\Model\Users\SavesCollection\Doctrine\SavesCollectionDoctrineRepository::class),
    \App\Domain\Model\Users\SavesCollection\SavedItemRepository::class => autowire(\App\Infrastructure\Domain\Model\Users\SavesCollection\Doctrine\SavedItemDoctrineRepository::class),
    \App\Domain\Model\Pages\Post\SuggestedPost\SuggestedPostRepository::class => autowire(\App\Infrastructure\Domain\Model\Pages\Post\SuggestedPost\Doctrine\SuggestedPostDoctrineRepository::class),
    \App\Domain\Model\Pages\Post\Comment\CommentRepository::class => autowire(\App\Infrastructure\Domain\Model\Pages\Post\Comment\Doctrine\CommentDoctrineRepository::class),
    \App\Domain\Model\Pages\Comments\AttachmentRepository::class => autowire(\App\Infrastructure\Domain\Model\Pages\Comments\Doctrine\AttachmentDoctrineRepository::class),
    \App\Domain\Model\Users\Post\AttachmentRepository::class => autowire(\App\Infrastructure\Domain\Model\Users\Post\Doctrine\AttachmentDoctrineRepository::class),
    \App\Domain\Model\Pages\Ban\BanRepository::class => autowire(\App\Infrastructure\Domain\Model\Pages\Ban\Doctrine\BanDoctrineRepository::class),
    \App\Domain\Model\Groups\Ban\BanRepository::class => autowire(\App\Infrastructure\Domain\Model\Groups\Ban\Doctrine\BanDoctrineRepository::class),
    \App\Domain\Model\Groups\Membership\MembershipRepository::class => autowire(\App\Infrastructure\Domain\Model\Groups\Membership\Doctrine\MembershipDoctrineRepository::class),
            
    \App\Infrastructure\Domain\Model\UniqueIndex\UserRepository::class => autowire(\App\Infrastructure\Domain\Model\UniqueIndex\UserRepository::class),
    \App\Infrastructure\Domain\Model\UniqueIndex\PageRepository::class => autowire(\App\Infrastructure\Domain\Model\UniqueIndex\PageRepository::class),
    \App\Infrastructure\Domain\Model\UniqueIndex\CommentRepository::class => autowire(\App\Infrastructure\Domain\Model\UniqueIndex\CommentRepository::class),
    \App\Infrastructure\Domain\Model\UniqueIndex\ReactionRepository::class => autowire(\App\Infrastructure\Domain\Model\UniqueIndex\ReactionRepository::class),
    \App\Domain\Model\Users\Comments\AttachmentRepository::class => autowire(\App\Infrastructure\Domain\Model\Users\Comments\Attachment\Doctrine\AttachmentDoctrineRepository::class),
    \App\Domain\Model\Users\Comments\ProfileCommentRepository::class => autowire(\App\Infrastructure\Domain\Model\Users\Comments\Doctrine\ProfileCommentDoctrineRepository::class),
    \App\Domain\Model\Users\Photos\Cover\CoverRepository::class => autowire(\App\Infrastructure\Domain\Model\Users\Photos\Cover\Doctrine\CoverDoctrineRepository::class),
    \App\Domain\Model\Chats\ChatRepository::class => autowire(\App\Infrastructure\Domain\Model\Chats\Doctrine\ChatDoctrineRepository::class),
    \App\Domain\Model\Chats\MessageRepository::class => autowire(\App\Infrastructure\Domain\Model\Chats\Doctrine\MessageDoctrineRepository::class),
            
    UserPostReplyRepository::class => autowire(UserReplyDoctrineRepository::class),
            
    AuthenticationService::class => autowire(AuthService::class),

//    AbstractTemplateEngine::class => function() {
//        $pathToCache = [];
//
//        if($_ENV['env'] === 'prod') {
//            $pathToCache = [
//                'cache' => PATH_TO_ROOT . '/var/cache/twig_cache'
//            ];
//        }
//
//        return $twigTemplate = new \App\TwigTemplateEngine(
//           PATH_TO_ROOT . '/views',
//           $pathToCache
//        );
//    },

    EmitterInterface::class => create(SapiEmitter::class),

    Doctrine\ORM\EntityManager::class => function(ContainerInterface $c) {
//        \Doctrine\DBAL\Types\Type::addType(
//            'UserId',
//            \App\Infrastructure\Domain\Model\Users\User\Doctrine\DoctrineUserId::class
//        );
        /*
//        \Doctrine\DBAL\Types\Type::addType(
//            'carbontype',
//            \App\Infrastructure\CarbonType::class
//        );*/
//        \Doctrine\DBAL\Types\Type::overrideType('datetime', \App\Infrastructure\DateTimeMicrosecondsType::class);
        //echo dirname(__DIR__, 1);exit();
//        $conn = array(
////            'driver'   => 'pdo_mysql',
//            'driver'   => 'pdo_sqlite',
//            'path' => dirname(__DIR__, 1) . '/data/newdb.db',
////            'host'     => '127.0.0.1',
////            'dbname'   => $_ENV['mysql_dbname'],
////            'user'     => $_ENV['mysql_user'],
////            'password' => $_ENV['mysql_password'],
//            'user'     => '',
//            'password' => '',
//            'charset'  => 'utf8mb4'
//        );
        
//        $conn = array(
//            'driver'   => 'pdo_mysql',
////            'host'     => '127.0.0.1',
//            'host'     => 'db4free.net',
////            'dbname'   => $_ENV['mysql_dbname'],
////            'user'     => $_ENV['mysql_user'],
////            'password' => $_ENV['mysql_password'],
//            'dbname'   => 'kekpupolddb',
//            'user'     => 'kekpupold',
////            'password' => $_ENV['mysql_password'],
//            'password' => '08051993',
//            'charset'  => 'utf8mb4'
//        );
        
        $conn = array(
            'driver'   => 'pdo_mysql',
            'host'     => 'd3y0lbg7abxmbuoi.chr7pe7iynqr.eu-west-1.rds.amazonaws.com:3306',
            'dbname'   => 'vvc5pnwjc360r4jd',
            'user'     => 'g53ekhmgjiu42wqj',
            'password' => 'nzsyhzy4uv2shjcn',
            'charset'  => 'utf8mb4'
        );
        
        $entityManager = EntityManager::create(
            $conn,// $config
            $c->get('DoctrineXMLMetadataConfiguration')
        );
        /*
        //$entityManager->getEventManager()->addEventSubscriber(new \Gedmo\SoftDeleteable\SoftDeleteableListener());
//        $entityManager->getFilters()->enable('soft-deleteable');
        
        //$filter = $entityManager->getFilters()->enable('soft-deleteable');
        //$filter->disableForEntity(\App\Domain\Model\Dialogue\Message::class);
*/
        return $entityManager;
    },
//            
//    \Doctrine\DBAL\Sharding\ShardManager::class => function(ContainerInterface $c) {
//        
//        function getConnection() {
//            return \Doctrine\DBAL\DriverManager::getConnection([
//                 'wrapperClass' => \Doctrine\DBAL\Sharding\PoolingShardConnection::class,
//                 'driver'       => 'pdo_mysql',
//                 'global'       => [
//                    'driver'   => 'pdo_mysql',
//                    'user' => $_ENV['mysql_user'],
//                    'password' => $_ENV['mysql_password'],
//                    'dbname'   => 'global',
//                    'host'     => '127.0.0.1'
//                 ],
//                'shards' => [
//                    [
//                        'id'	   => 1,
//                        'driver'   => 'pdo_mysql',
//                        'user' => $_ENV['mysql_user'],
//                        'password' => $_ENV['mysql_password'],
//                        'dbname'   => 'shard1',
//                        'host'     => '127.0.0.1'
//                     ],
//                    [
//                        'id'	   => 2,
//                        'driver'   => 'pdo_mysql',
//                        'user' => $_ENV['mysql_user'],
//                        'password' => $_ENV['mysql_password'],
//                        'dbname'   => 'shard2',
//                        'host'     => '127.0.0.1'
//                    ],
//                 ],
//                 'shardChoser' => "\App\Sharding\SqlShardChoser",
//             ]);
//        }
//
//        $conn = getConnection();
//        $globalConn = getConnection();
//
//        $manager = new \App\Sharding\SqlShardManager($conn, $globalConn, [
//            'global' => Setup::createXMLMetadataConfiguration([PATH_TO_ROOT . '/src/Infrastructure/Persistence/Doctrine/Mappings/'], true),
//            'shards' => Setup::createXMLMetadataConfiguration([PATH_TO_ROOT . '/src/Infrastructure/Persistence/Doctrine/Mappings/'], true)
//        ]);
//
//        return $manager;
//    },

    'DoctrineXMLMetadataConfiguration' => function(ContainerInterface $c) {
        $config = Setup::createXMLMetadataConfiguration(
            array(
               // PATH_TO_ROOT . '/src/Infrastructure/Persistence/Doctrine/Mappings_test',
                PATH_TO_ROOT . '/src/Infrastructure/Persistence/Doctrine/Mappings',
                PATH_TO_ROOT . '/src/Infrastructure/Persistence/Doctrine/Mappings/Users',
                PATH_TO_ROOT . '/src/Infrastructure/Persistence/Doctrine/Mappings/Users/Saves',
//                PATH_TO_ROOT . '/src/Infrastructure/Persistence/Doctrine/Mappings/Groups',
//                PATH_TO_ROOT . '/src/Infrastructure/Persistence/Doctrine/Mappings/Pages',
                PATH_TO_ROOT . '/src/Infrastructure/Persistence/Doctrine/Mappings/Common',
                PATH_TO_ROOT . '/src/Infrastructure/Persistence/Doctrine/Mappings/Chats',
                //PATH_TO_ROOT . '/src/Infrastructure/Persistence/Doctrine/Mappings_OneToOneUnidirectionalCascade'
            ),
            ($_ENV['env'] == 'dev' ? true : false)
        );
        
        //$config->addFilter('soft-deleteable', 'Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter');
        //$kek = $c->get('Metadata FilesystemCache');
        
        if($_ENV['env'] == 'prod') {
            $config->setMetadataCacheImpl($c->get('Metadata FilesystemCache'));
            $config->setQueryCacheImpl($c->get('Queries FilesystemCache'));
            $config->setResultCacheImpl($c->get('Results FilesystemCache'));
            //$config->setResultCacheImpl($c->get(MemcachedCache::class));
        }

        return $config;
    },

    'Metadata FilesystemCache' => create(FilesystemCache::class)
        ->constructor(PATH_TO_ROOT . '/var/cache/doctrine_cache/metadata'),

    'Queries FilesystemCache' => create(FilesystemCache::class)
        ->constructor(PATH_TO_ROOT . '/var/cache/doctrine_cache/queries'),

    'Results FilesystemCache' => create(FilesystemCache::class)
        ->constructor(PATH_TO_ROOT . '/var/cache/doctrine_cache/results'),

    MemcachedCache::class => function(ContainerInterface $c) {
        $memcachedCache = new MemcachedCache();
        $memcachedCache->setMemcached($c->get(Memcached::class));
        return $memcachedCache;
    },

    \Memcached::class => function() {
        $memcached = new \Memcached();
        $memcached->addServer('localhost', 11211);
        return $memcached;
    }
];
