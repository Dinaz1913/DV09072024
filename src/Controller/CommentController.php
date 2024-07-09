<?php

namespace App\Controller;

use App\Service\CommentService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Monolog\Logger;
class CommentController
{
    private CommentService $commentService;
    private Logger $logger;

    public function __construct(CommentService $commentService, Logger $logger)
    {
        $this->commentService = $commentService;
        $this->logger = $logger;
    }

    public function addComment(Request $request, Response $response, array $args): Response
    {
        if (!isset($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $data = $request->getParsedBody();
        $data['article_id'] = $args['id'];
        $data['user_id'] = $_SESSION['user_id'];

        try {
            $this->commentService->addComment($data);
            return $response->withHeader('Location', '/articles/'
                . $args['id'])->withStatus(302);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return $response->withStatus(500)->write('An error occurred while adding the comment.');
        }
    }

    public function likeArticle(Request $request, Response $response, array $args): Response
    {
        if (!isset($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $data['article_id'] = $args['id'];
        $data['user_id'] = $_SESSION['user_id'];

        try {
            $this->commentService->likeArticle($data);
            return $response->withHeader('Location',
                '/articles/' . $args['id'])->withStatus(302);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return $response->withStatus(500)->write('An error occurred while liking the article.');
        }
    }
}