<?php

namespace Society\database;

use Society\session\Session;

abstract class Database
{

    abstract public static function initClass(): static;
    abstract public static function check(): void;
    abstract public static function insert(string $table, string $column, string $info, ?Session $session = null): void;
    abstract public static function delete(): void;

}