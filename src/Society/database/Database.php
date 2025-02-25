<?php

namespace Society\database;

use Society\session\Session;

abstract class Database
{

    abstract public static function initClass(): static;
    abstract public static function check(): void;
    abstract public static function update(string $table, string $column, string $condition, string $info): void;

}