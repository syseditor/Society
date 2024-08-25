<?php

namespace Society\database;

abstract class Database
{
    public abstract function check(string $dbName): void;
}