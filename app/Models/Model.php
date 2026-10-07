<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

abstract class Model
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    public static function all(string $orderBy = 'id DESC'): array
    {
        return Database::fetchAll("SELECT * FROM " . static::$table . " ORDER BY {$orderBy}");
    }

    public static function find(int $id): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id LIMIT 1",
            ['id' => $id]
        );
    }

    public static function findBy(string $column, mixed $value): ?array
    {
        return Database::fetchOne(
            "SELECT * FROM " . static::$table . " WHERE {$column} = :val LIMIT 1",
            ['val' => $value]
        );
    }

    public static function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO " . static::$table . " ({$columns}) VALUES ({$placeholders})";
        Database::query($sql, $data);

        return (int)Database::lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $setClauses = [];
        $params = [static::$primaryKey => $id];

        foreach ($data as $col => $val) {
            $setClauses[] = "{$col} = :set_{$col}";
            $params["set_{$col}"] = $val;
        }

        $sql = "UPDATE " . static::$table . " SET " . implode(', ', $setClauses) . " WHERE " . static::$primaryKey . " = :" . static::$primaryKey;
        Database::query($sql, $params);

        return true;
    }

    public static function delete(int $id): bool
    {
        Database::query(
            "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = :id",
            ['id' => $id]
        );
        return true;
    }
}
