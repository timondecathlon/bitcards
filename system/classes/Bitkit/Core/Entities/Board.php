<?php

namespace Bitkit\Core\Entities;

class Board extends \Bitkit\Core\Entities\Post
{

    public function setTable() : string
    {
        return 'core_boards';
    }

    public function getBoardsByUserId(int $user_id) : int
    {
        $sql_board = $this->pdo->prepare("SELECT id FROM core_boards WHERE user_id=$user_id");
        $sql_board->execute();
        $boards_info  = $sql_board->fetch(\PDO::FETCH_LAZY);
        $this->id = $boards_info->id;
        return $this->id;
    }
}