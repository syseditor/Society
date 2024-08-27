<?php

namespace Society\database;

abstract class Database
{
    abstract public static function check(): void;
}