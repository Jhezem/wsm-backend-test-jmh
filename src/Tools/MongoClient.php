<?php

namespace App\Tools;


use Doctrine\ODM\MongoDB\Query\Query;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use MongoDB\Client;
use MongoDB\DeleteResult;
use MongoDB\Driver\Cursor;
use MongoDB\InsertManyResult;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;
use MongoDB\UpdateResult;


class MongoClient
{

    /**
     * @var Client $database
     */
    private $client;

    /**
     * @var string $database
     */
    private $database;

    public function __construct(string $server, string $database)
    {
        $this->client = new Client(
            $server
        );
        $this->database=$database;
    }

    public function find(String $collection, array $filters, array $options = []) : \Traversable{
        $db = $this->client->__get($this->database);
        $coll = $db->selectCollection($collection);

        $options = array_merge($options, ['useCursor'=>true]);

        return $coll->find($filters, $options);
    }

    public function aggregate(String $collection, array $pipeline, array $options=['useCursor'=>true]) : Cursor{
        $db = $this->client->__get($this->database);
        $coll = $db->selectCollection($collection);
        return $coll->aggregate($pipeline, $options);
    }

    public function update(String $collection, array $filters, array $update, array $options=[]) : UpdateResult
    {
        $db = $this->client->__get($this->database);
        $coll = $db->selectCollection($collection);
        return $coll->updateMany($filters, $update, $options);
    }

    public function updateOne(string $database, String $collection, array $filters, array $update, array $options=[]) : UpdateResult
    {
        $db = $this->client->__get($database);
        $coll = $db->selectCollection($collection);
        return $coll->updateOne($filters, $update, $options);
    }

    public function replaceOne(string $database, String $collection, array $filters, array $replacement, array $options=[]) : UpdateResult
    {
        $db = $this->client->__get($database);
        $coll = $db->selectCollection($collection);
        return $coll->replaceOne($filters, $replacement, $options);
    }

    public function delete(String $collection, array $filters, array $options=[]) : DeleteResult
    {
        $db = $this->client->__get($this->database);
        $coll = $db->selectCollection($collection);
        return $coll->deleteMany($filters, $options);
    }

    public function insert(string $collection, array $documents, $options = []) {
        $db = $this->client->__get($this->database);
        $coll = $db->selectCollection($collection);
        return $coll->insertMany($documents, $options);
    }

    public function bulkWrite($collection, $operations, $options) {
        $db = $this->client->__get($this->database);
        $coll = $db->selectCollection($collection);
        return $coll->bulkWrite($operations, $options);
    }
    public function countRecords($collection, $filter=[]) {
        $db = $this->client->__get($this->database);
        $coll = $db->selectCollection($collection);
        return $coll->countDocuments($filter);
    }

    /**
     * @param String $collection
     * @param array $filters
     * @param array $options
     * @return array|object|null
     */
    public function findOne(String $collection, array $filters, array $options = []){
        $db = $this->client->__get($this->database);
        $coll = $db->selectCollection($collection);

        $options = array_merge($options, ['useCursor'=>true]);

        return $coll->findOne($filters, $options);
    }


    public static function toArray($object)
    {
        if(is_a($object, BSONArray::class)) {
            return array_map(function($inner) { return static::toArray($inner); }, iterator_to_array($object));
        } elseif(is_array($object)){
            return array_map(function($inner) { return static::toArray($inner); }, $object);
        } elseif(is_a($object, ObjectId::class) || is_a($object, \MongoId::class)) {
            return (string) $object;
        } elseif(is_a($object, BSONDocument::class)) {
            $array = [];
            foreach(iterator_to_array($object) as $key => $value) {
                $array[$key] = static::toArray($value);
            }
            return $array;
        }
        return $object;
    }


}