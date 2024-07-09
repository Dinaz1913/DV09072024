<?php

namespace App\Model;

class Comment
{
    private int $id;
    private int $articleId;
    private int $userId;
    private string $content;
    private \DateTime $createdAt;
    public function getId(): int
    {
        return $this->id;
    }
    public function getArticleId(): int
    {
        return $this->articleId;
    }
    public function setArticleId(int $articleId): void
    {
        $this->articleId = $articleId;
    }
    public function getUserId(): int
    {
        return $this->userId;
    }
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }
    public function getContent(): string
    {
        return $this->content;
    }
    public function setContent(string $content): void
    {
        $this->content = $content;
    }
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}