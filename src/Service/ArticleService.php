<?php

namespace App\Service;

use Monolog\Logger;
use PDO;
use App\Model\Article;

class ArticleService
{
    private Logger $logger;
    private PDO $pdo;

    public function __construct(Logger $logger, PDO $pdo)
    {
        $this->logger = $logger;
        $this->pdo = $pdo;
    }

    public function getAllArticles(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM articles');
        $articles = $stmt->fetchAll(PDO::FETCH_CLASS, Article::class);
        $this->logger->info('Fetched all articles.');
        return $articles;
    }

    public function getArticleById(int $id): ?Article
    {
        $stmt = $this->pdo->prepare('SELECT * FROM articles WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, Article::class);
        $article = $stmt->fetch();
        $this->logger->info('Fetched article with ID: ' . $id);
        return $article ?: null;
    }

    public function createArticle(array $data): void
    {
        $stmt = $this->pdo->prepare
        ('INSERT INTO articles
    (
     title,
     content,
     created_at, 
     updated_at
     ) VALUES (:title,
               :content,
               :created_at, 
               :updated_at)'
        );
        $stmt->execute([
            'title' => $data['title'],
            'content' => $data['content'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        $this->logger->info('Created a new article with title: ' . $data['title']);
    }

    public function updateArticle(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare
        (
            'UPDATE articles SET title = :title,
                    content = :content,
                    updated_at = :updated_at WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'content' => $data['content'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        $this->logger->info('Updated article with ID: ' . $id);
    }

    public function deleteArticle(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM articles WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $this->logger->info('Deleted article with ID: ' . $id);
    }
}
