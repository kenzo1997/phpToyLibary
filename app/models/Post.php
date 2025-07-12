<?php

namespace app\models;

class Post
{
    public function __construct(public string $title, public string $content, public int $views)
    {
    }

    public function getTitle() {
        return $this->title;
    }
}
