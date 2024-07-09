<?php

namespace App\Service;

use Monolog\Logger;
use PDO;

class CommentService
{
    private Logger $logger;
    private PDO $pdo;

    public function __construct(Logger $logger, PDO $pdo)
    {
        $this->logger = $logger;
        $this->pdo = $pdo;
    }

    public function addComment(array $data): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO comments 
    (
     article_id,
     user_id, content,
     created_at
     ) VALUES 
           (
            :article_id,
            :user_id, :content,
            :created_at)'
        );
        $stmt->execute([
            'article_id' => $data['article_id'],
            'user_id' => $data['user_id'],
            'content' => $data['content'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $this->logger->info('Added a new comment to article ID: ' . $data['article_id']);
    }

    public function likeArticle(array $data): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO likes 
    (
     article_id,
     user_id,
     created_at
     ) VALUES 
           (
            :article_id,
            :user_id,
            :created_at)'
        );
        $stmt->execute([
            'article_id' => $data['article_id'],
            'user_id' => $data['user_id'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $this->logger->info('Liked article ID: ' . $data['article_id']);
    }

    public function getCommentsByArticleId(int $articleId): array
    {
        $stmt = $this->pdo->prepare
        (
            'SELECT c.*,
       u.username FROM comments c JOIN users u ON c.user_id = u.id 
                  WHERE c.article_id = :article_id'
        );
        $stmt->execute(['article_id' => $articleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
