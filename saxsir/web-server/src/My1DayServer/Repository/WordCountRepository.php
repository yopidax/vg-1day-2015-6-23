<?php

namespace My1DayServer\Repository;

class WordCountRepository
{
    protected $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // 新しいwordを登録する

    // wordが既に登録済みか判定する

    // 受け取った単語をカウントアップする
}
