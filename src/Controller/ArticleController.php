<?php

namespace App\Controller;

use App\Service\ArticleService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Monolog\Logger;

class ArticleController
{
    private ArticleService $articleService;
    private Twig $twig;
    private Logger $logger;

    public function __construct
    (
        ArticleService $articleService,
        Twig $twig,
        Logger $logger
    )
    {
        $this->articleService = $articleService;
        $this->twig = $twig;
        $this->logger = $logger;
    }

    public function index
    (
        Request $request,
        Response $response
    ): Response
    {
        try {
            $articles = $this->articleService->getAllArticles();
            return $this->twig->render($response,
                'articles/index.html.twig',
                ['articles' => $articles]);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return $response->withStatus(500)->
            write('An error occurred while fetching articles.');
        }
    }

    public function show
    (
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        try {
            $article = $this->articleService
                ->getArticleById
                (
                    (int)$args['id']
                );
            return $this->twig->render
                (
                    $response,
                'articles/show.html.twig',
                ['article' => $article]
                );
        } catch (Exception $e) {
            $this->logger->error
                (
                    $e->getMessage()
                );
            return $response
                ->withStatus(500)
                ->write('An error occurred while fetching the article.');
        }
    }

    public function create
    (
        Request $request,
        Response $response
    ): Response
    {
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            try {
                $this->articleService->createArticle($data);
                return $response
                    ->withHeader
                    (
                        'Location',
                    '/articles'
                    )
                    ->withStatus(302);
            } catch (Exception $e) {
                $this->logger->
                error
                (
                    $e->getMessage()
                );
                return $response->
                withStatus(500)
                    ->write('An error occurred while creating the article.');
            }
        }
        return $this->twig->render
        (
            $response,
            'articles/create.html.twig'
        );
    }

    public function edit
    (
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        $article = $this->articleService
            ->getArticleById
            (
                (int)
            $args['id']
            );

        if ($request->getMethod() === 'POST') {
            $data = $request->
            getParsedBody();
            try {
                $this->
                articleService->
                updateArticle((int)$args['id'],
                    $data
                );
                return $response->withHeader
                ('Location',
                    '/articles')
                    ->withStatus(302);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
                return $response->withStatus(500)
                    ->write('An error occurred while updating the article.');
            }
        }
        return $this->twig->render
        (
            $response,
            'articles/edit.html.twig',
            ['article' => $article]
        );
    }

    public function delete
    (
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        try {
            $this->articleService->deleteArticle((int)$args['id']);
            return $response->withHeader
            ('Location', '/articles')
                ->withStatus(302);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return $response->withStatus(500)
                ->write('An error occurred while deleting the article.');
        }
    }
}
