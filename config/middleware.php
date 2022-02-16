<?php
declare(strict_types=1);

use Slim\Middleware\ContentLengthMiddleware;
use Slim\App;
use Middlewares\TrailingSlash;
use App\Middlewares\EtagMiddleware;
use Tuupola\Middleware\JwtAuthentication;
use App\Middlewares\ServerErrorsHandlerMiddleware;
use App\Middlewares\ErrorsHandlerMiddleware;
use App\Infrastructure\MyRequestPathRule;

return function (App $app) {
    // Parse json, form data and xml
    $app->add(new \App\Middlewares\JSONValidationMiddleware());
    $app->addBodyParsingMiddleware();
    
    /**
     * The routing middleware should be added earlier than the ErrorMiddleware
     * Otherwise exceptions thrown from it will not be handled by the middleware
     */
    $app->addRoutingMiddleware();
    
    $app->add(new ContentLengthMiddleware());
    $app->add(new EtagMiddleware());
    $app->add(new JwtAuthentication([
        "secret" => $app->getContainer()->get('secret'),
        "error" => function ($response, $arguments) {
            $data["status"] = "error";
            $data["message"] = $arguments["message"];
            return $response
                ->withHeader("Content-Type", "application/json")
                ->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        },
        "rules" => [
            new MyRequestPathRule([
                'path' => '/',                               
                'ignore' => [
                    ['uri' => "/v1/users/\w+$", 'method' => 'GET'],
                    ['uri' => "/v1/users/\w+/posts", 'method' => 'GET'],
                    ['uri' => "/v1/user-posts/\w+", 'method' => 'GET'],
                    ['uri' => "/v1/user-post-comments/\w+", 'method' => 'GET'],
                    ['uri' => "/v1/profile-pictures/\w+", 'method' => 'GET'],
//                    ['uri' => "/user-albums/\w+", 'method' => 'GET'],
//                    ['uri' => "/auth/me", 'method" => 'GET'],
                    ['uri' => "/v1/auth/login", 'method' => 'POST'],
                    ['uri' => "/v1/auth/signup", 'method' => 'POST'],
//                    ['uri' => "/test"],
//                    ['uri' => "/profile-posts", 'method' => 'POST'],
//                    ['uri' => "/profile-pictures/\w+"],
//                    ['uri' => "/privacy"],
//                    ['uri' => "/connections"],
//                    ['uri' => "/user-subscriptions"],
//                    ['uri' => "/user-albums"],
//                    ['uri' => "/user-photos"],
//                    ['uri' => "/profile-pictures"],
                ]
            ]),
            new \Tuupola\Middleware\JwtAuthentication\RequestMethodRule([
                "ignore" => ["OPTIONS"]
            ])
        ],
    ]));
        
    //$app->add(JwtAuthMiddleware::class);
    //$app->add($conditionalMiddleware);
    $app->add(new TrailingSlash(false));
    
    // Catch exceptions and errors
//    $logger = new Logger('errors');
//        $app->add(\App\Middlewares\PrepareUser::class);

    $app->add(ErrorsHandlerMiddleware::class);
    //$app->add(WarningsAndNoticesMiddleware::class);
    $app->add(ServerErrorsHandlerMiddleware::class);
    
    $app->addErrorMiddleware(false, true, true);
//    $errorHandler = new ErrorsHandler($app->getCallableResolver(), $app->getResponseFactory(), $logger);
//    $errorMiddleware->setDefaultErrorHandler($errorHandler);
    
    //$app->add(ErrorsHandler::class);
};
