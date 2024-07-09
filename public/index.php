<?php

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';

session_start();

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    Logger::class => function() {
        $logger = new Logger('app');
        $logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log',
            Logger::DEBUG));
        return $logger;
    },
    PDO::class => function() {
        $dsn = 'sqlite:' . __DIR__ . '/../database.sqlite';
        return new PDO($dsn);
    },
    Twig::class => function() {
        return Twig::create(__DIR__ . '/../src/View', ['cache' => false]);
    },
]);

try {
    $container = $containerBuilder->build();
} catch (Exception $e) {
    echo 'Could not build the container. Please check your configuration.';
    exit(1);
}

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addErrorMiddleware(true, true, true);

$app->map(['GET', 'POST'], '/articles/create',
    function (Request $request, Response $response) use ($container) {
    $twig = $container->get(Twig::class);
    if ($request->getMethod() === 'POST') {
        $data = $request->getParsedBody();
        $articleService = $container->get(\App\Service\ArticleService::class);
        try {
            $articleService->createArticle($data);
            return $response->withHeader('Location', '/articles')->withStatus(302);
        } catch (Exception $e) {
            $container->get(Logger::class)->error($e->getMessage());
            $response->getBody()->write('An error occurred while creating the article.');
            return $response->withStatus(500);
        }
    }
    return $twig->render($response, 'articles/create.html.twig');
});

$app->map(['GET', 'POST'], '/articles/edit/{id}',
    function (Request $request, Response $response, array $args) use ($container) {
    $twig = $container->get(Twig::class);
    $articleService = $container->get(\App\Service\ArticleService::class);
    $article = $articleService->getArticleById((int)$args['id']);
    if ($request->getMethod() === 'POST') {
        $data = $request->getParsedBody();
        try {
            $articleService->updateArticle((int)$args['id'], $data);
            return $response->withHeader('Location', '/articles')->withStatus(302);
        } catch (Exception $e) {
            $container->get(Logger::class)->error($e->getMessage());
            $response->getBody()->write('An error occurred while updating the article.');
            return $response->withStatus(500);
        }
    }
    return $twig->render($response, 'articles/edit.html.twig', ['article' => $article]);
});

$app->delete('/articles/{id}',
    function (Request $request, Response $response, array $args) use ($container) {
    $articleService = $container->get(\App\Service\ArticleService::class);
    try {
        $articleService->deleteArticle((int)$args['id']);
        return $response->withHeader('Location', '/articles')->withStatus(302);
    } catch (Exception $e) {
        $container->get(Logger::class)->error($e->getMessage());
        $response->getBody()->write('An error occurred while deleting the article.');
        return $response->withStatus(500);
    }
});

$app->get('/articles', function (Request $request, Response $response) use ($container) {
    $twig = $container->get(Twig::class);
    $articleService = $container->get(\App\Service\ArticleService::class);
    try {
        $articles = $articleService->getAllArticles();
        return $twig->render($response, 'articles/index.html.twig',
            ['articles' => $articles, 'session' => $_SESSION]);
    } catch (Exception $e) {
        $container->get(Logger::class)->error($e->getMessage());
        $response->getBody()->write('An error occurred while fetching articles.');
        return $response->withStatus(500);
    }
});

$app->get('/articles/{id}', function (Request $request, Response $response, array $args) use ($container) {
    $twig = $container->get(Twig::class);
    $articleService = $container->get(\App\Service\ArticleService::class);
    $commentService = $container->get(\App\Service\CommentService::class);
    try {
        $article = $articleService->getArticleById((int)$args['id']);
        $comments = $commentService->getCommentsByArticleId((int)$args['id']);
        return $twig->render($response, 'articles/show.html.twig',
            ['article' => $article, 'comments' => $comments, 'session' => $_SESSION]);
    } catch (Exception $e) {
        $container->get(Logger::class)->error($e->getMessage());
        $response->getBody()->write('An error occurred while fetching the article.');
        return $response->withStatus(500);
    }
});

$app->post('/articles/{id}/comment',
    function (Request $request, Response $response, array $args) use ($container) {
    if (!isset($_SESSION['user_id'])) {
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
    $data = $request->getParsedBody();
    $data['article_id'] = $args['id'];
    $data['user_id'] = $_SESSION['user_id'];
    $commentService = $container->get(\App\Service\CommentService::class);
    try {
        $commentService->addComment($data);
        return $response->withHeader('Location', '/articles/' . $args['id'])->withStatus(302);
    } catch (Exception $e) {
        $container->get(Logger::class)->error($e->getMessage());
        $response->getBody()->write('An error occurred while adding the comment.');
        return $response->withStatus(500);
    }
});

$app->post('/articles/{id}/like',
    function (Request $request, Response $response, array $args) use ($container) {
    if (!isset($_SESSION['user_id'])) {
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
    $data['article_id'] = $args['id'];
    $data['user_id'] = $_SESSION['user_id'];
    $commentService = $container->get(\App\Service\CommentService::class);
    try {
        $commentService->likeArticle($data);
        return $response->withHeader('Location', '/articles/' . $args['id'])->withStatus(302);
    } catch (Exception $e) {
        $container->get(Logger::class)->error($e->getMessage());
        $response->getBody()->write('An error occurred while liking the article.');
        return $response->withStatus(500);
    }
});

$app->map(['GET', 'POST'], '/login',
    function (Request $request, Response $response) use ($container) {
    $twig = $container->get(Twig::class);
    if ($request->getMethod() === 'POST') {
        $data = $request->getParsedBody();
        $username = $data['username'];
        $password = $data['password'];
        $pdo = $container->get(PDO::class);
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username AND password = :password');
        $stmt->execute(['username' => $username, 'password' => $password]);
        $user = $stmt->fetchObject(\App\Model\User::class);
        if ($user) {
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['username'] = $user->getUsername();
            return $response->withHeader('Location', '/articles')->withStatus(302);
        } else {
            return $twig->render($response, 'login.html.twig', ['error' => 'Invalid credentials']);
        }
    }
    return $twig->render($response, 'login.html.twig');
});

$app->get('/logout', function (Request $request, Response $response) {
    session_destroy();
    return $response->withHeader('Location', '/login')->withStatus(302);
});

$app->get('/', function ($request, $response) {
    return $response->withHeader('Location', '/articles')->withStatus(302);
});

$app->run();
