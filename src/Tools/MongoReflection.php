<?php

namespace App\Tools;

class MongoReflection
{

    /**
     * @throws \ReflectionException
     */
    public static function getCollectionName(string $documentClass){

        $rc = new \ReflectionClass($documentClass);
        $docDocument = $rc->getDocComment();
        $collectionName = self::extractDataType($docDocument);

        return $collectionName;
    }


    public static  function  extractDataType(string $doc): ?string
    {
        $type = null;
        preg_match('/collection="\w+"/', $doc, $matches);

        if (!empty($matches)) {

            $type = explode('=', $matches[0])[1];
            $type = str_replace('"', "", $type);

        }

        return $type;
    }

}