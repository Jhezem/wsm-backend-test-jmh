<?php

namespace App\Repositories;

use App\Document\Account;
use App\Document\Metric;
use App\Tools\MongoClient;
use App\Tools\MongoReflection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Doctrine\ODM\MongoDB\UnitOfWork;

class AccountRepository extends DocumentRepository
{

    private MongoClient $mongoClient;

    public function __construct(DocumentManager $dm, UnitOfWork $uow, ClassMetadata $classMetadata)
    {
        parent::__construct($dm, $uow, $classMetadata);
        $this->mongoClient = new MongoClient($this->getDocumentManager()->getClient()->__toString(), $this->getDocumentManager()->getConfiguration()->getDefaultDB());
    }

    /**
     * @throws \ReflectionException
     */
    public function getReports(string|null $accountId = null)
    {

        $query = [
            ['$match' => [
                'status' => 'ACTIVE'
            ]],
            ['$lookup' => [
                'from' => MongoReflection::getCollectionName(Metric::class),
                'as' => 'metrics',
                'let' => ['id' => '$accountId'],
                'pipeline' => [
                    ['$match' => [
                        '$expr' => [
                            '$eq' => ['$accountId', '$$id']
                        ]
                    ]], ['$group' => [
                        '_id' => '$accountId',
                        'spend' => ['$sum' => '$spend'],
                        'impressions' => ['$sum' => '$impressions'],
                        'clicks' => ['$sum' => '$clicks']
                    ]
                    ], [
                        '$addFields' => [
                            'costPerClick' => ['$divide' => ['$spend', '$clicks']]
                        ]
                    ],
                ]
            ]],
            [
                '$unwind' => [
                    'path' => '$metrics',
                    'preserveNullAndEmptyArrays' => true
                ]
            ]
        ];

        if($accountId){

            $accountIdQuery = [
                '$match' => [
                    'accountId' => $accountId
                ]
            ];

           $query[] = $accountIdQuery;
        }


        $result = $this->mongoClient->aggregate(MongoReflection::getCollectionName(Account::class), $query)->toArray();
        return $this->flatArray(MongoClient::toArray($result));
    }

    private function flatArray($element){
        $result=[];

        foreach ($element as $row){


            $data = [
                "accountId" => $row["accountId"],
                "accountName" => $row["accountName"],
                "spend" => $row["metrics"]["spend"] ?? 0,
                "impressions" => $row["metrics"]["impressions"] ?? 0,
                "clicks" => $row["metrics"]["clicks"] ?? 0,
                "costPerClick" => isset($row["metrics"]["costPerClick"]) ? round($row["metrics"]["costPerClick"],2) : 0,
            ];
            $result[] = $data;
        }

        usort($result, function ($item1, $item2) {
            return $item2['costPerClick'] <=> $item1['costPerClick'];
        });
        return $result;
    }


     public function getReportsWithODM( string | null $accountId = null){

         $dm = $this->getDocumentManager();
          $builder = $dm->createAggregationBuilder(Account::class);
        $builder
                        ->match()
                        ->field('status')->equals('ACTIVE')
                        ->lookup('Metrics')
                        ->foreignField('accountId')
                        ->localField('accountId')
                        ->alias('metrics')
                        ->group()
                        ->field('id')
                        ->expression('$accountId')
                        ->field('spend')
                        ->sum( $builder->expr()
                        ->sum('$metrics.spend')
                        )     
                        ->field('impressions')
                        ->sum( $builder->expr()
                        ->sum('$metrics.impressions')
                        )                      
                        ->field('clicks')
                        ->sum( $builder->expr()
                        ->sum('$metrics.clicks'));     
                         
     $result = $builder->getAggregation()->getIterator()->toArray();

        return $result;
     }

}