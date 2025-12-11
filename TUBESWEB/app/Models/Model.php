<?php

abstract class Model
{
    /** @var PDO */
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

}
