<?php
/**
 * Base repository — every repository gets a PDO handle without
 * re-implementing the singleton lookup.
 */
abstract class BaseRepository
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = db();
    }
}
