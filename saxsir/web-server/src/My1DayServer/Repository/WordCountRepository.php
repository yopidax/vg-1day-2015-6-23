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

    /**
     * 受け取った単語をカウントアップする
     *
     * @param word [String] カウントアップする単語
     * @return count [Int] カウントアップした後の値
     */
    public function increment($word) {
      $sql = 'SELECT count FROM word_count WHERE word = ?';
      $params = [$word];
      $count = $this->conn->fetchAssoc($sql, $params)['count'];

      //HACK: idで指定した方が高速だが、そこまで求められていないので今回は対応しない
      $sql = 'UPDATE word_count SET count = ? WHERE word = ?';
      $count = $count + 1;
      $params = [$count, $word];
      $this->conn->executeUpdate($sql, $params);
      return $count;
    }
}
