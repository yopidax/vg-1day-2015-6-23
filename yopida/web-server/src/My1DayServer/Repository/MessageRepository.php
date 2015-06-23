<?php

namespace My1DayServer\Repository;

class MessageRepository
{
    protected $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function isExistingMessage($id)
    {
        $sql = 'SELECT id FROM vg_message WHERE id = ?';
        $params = [$id];

        return (bool)$this->conn->fetchColumn($sql, $params);
    }

    public function getAllMessages()
    {
        $sql = 'SELECT * FROM vg_message';

        return $this->conn->fetchAll($sql);
    }

    public function getMessage($id)
    {
        $sql = 'SELECT * FROM vg_message WHERE id = ?';
        $params = [$id];

        return $this->conn->fetchAssoc($sql, $params);
    }

    public function deleteMessage($id)
    {
        $sql = 'DELETE FROM vg_message WHERE id = ?';
        $params = [$id];

        $this->conn->executeUpdate($sql, $params);
    }

    public function createMessage($data)
    {
        $datetime = \date_create(null, new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');

        $data = array_merge($data, [
            'created_at' => $datetime,
            'updated_at' => $datetime,
        ]);

        $queryResult = $this->conn->insert('vg_message', $data);
        if (!$queryResult) {
            return false;
        }

        return $this->conn->lastInsertId();
    }
}
