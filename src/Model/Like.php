<?php

namespace App\Model;

class Like
{
    private int $id;
    private int $articleId;
    private int $userId;
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
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}