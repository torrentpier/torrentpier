<?php

declare(strict_types=1);

namespace App\Models;

use TorrentPier\Database\Database;

/**
 * Base Model class for all models
 * Provides basic database operations using Nette Database
 */
abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $attributes = [];
    protected array $original = [];
    
    public function __construct(
        protected Database $db,
        array $attributes = []
    ) {
        $this->fill($attributes);
        $this->syncOriginal();
    }
    
    /**
     * Find a model by its primary key
     */
    public static function find(int|string $id): ?static
    {
        $instance = new static(DB());
        $data = $instance->db->table($instance->table)
            ->where($instance->primaryKey, $id)
            ->fetch();
        
        if (!$data) {
            return null;
        }
        
        return new static(DB(), (array) $data);
    }
    
    /**
     * Find a model by a specific column
     */
    public static function findBy(string $column, mixed $value): ?static
    {
        $instance = new static(DB());
        $data = $instance->db->table($instance->table)
            ->where($column, $value)
            ->fetch();
        
        if (!$data) {
            return null;
        }
        
        return new static(DB(), (array) $data);
    }
    
    /**
     * Get all models
     */
    public static function all(): array
    {
        $instance = new static(DB());
        $rows = $instance->db->table($instance->table)->fetchAll();
        
        $models = [];
        foreach ($rows as $row) {
            $models[] = new static(DB(), (array) $row);
        }
        
        return $models;
    }
    
    /**
     * Fill the model with an array of attributes
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
        
        return $this;
    }
    
    /**
     * Save the model to the database
     */
    public function save(): bool
    {
        if ($this->exists()) {
            return $this->update();
        }
        
        return $this->insert();
    }
    
    /**
     * Insert a new record
     */
    protected function insert(): bool
    {
        $this->db->table($this->table)->insert($this->attributes);
        
        if (!isset($this->attributes[$this->primaryKey])) {
            $this->attributes[$this->primaryKey] = $this->db->getInsertId();
        }
        
        $this->syncOriginal();
        return true;
    }
    
    /**
     * Update an existing record
     */
    protected function update(): bool
    {
        $dirty = $this->getDirty();
        
        if (empty($dirty)) {
            return true;
        }
        
        $this->db->table($this->table)
            ->where($this->primaryKey, $this->getKey())
            ->update($dirty);
        
        $this->syncOriginal();
        return true;
    }
    
    /**
     * Delete the model
     */
    public function delete(): bool
    {
        if (!$this->exists()) {
            return false;
        }
        
        $this->db->table($this->table)
            ->where($this->primaryKey, $this->getKey())
            ->delete();
        
        return true;
    }
    
    /**
     * Check if the model exists in the database
     */
    public function exists(): bool
    {
        return isset($this->original[$this->primaryKey]);
    }
    
    /**
     * Get the primary key value
     */
    public function getKey(): int|string|null
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }
    
    /**
     * Get attributes that have been changed
     */
    public function getDirty(): array
    {
        $dirty = [];
        
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $value !== $this->original[$key]) {
                $dirty[$key] = $value;
            }
        }
        
        return $dirty;
    }
    
    /**
     * Sync the original attributes with the current
     */
    protected function syncOriginal(): void
    {
        $this->original = $this->attributes;
    }
    
    /**
     * Get an attribute
     */
    public function __get(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }
    
    /**
     * Set an attribute
     */
    public function __set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }
    
    /**
     * Check if an attribute exists
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }
    
    /**
     * Convert the model to an array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}