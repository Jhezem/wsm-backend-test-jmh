<?php


namespace App\Controller;


use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AbstractApiController extends AbstractController
{


    protected $container;
    /** @var DocumentManager $documentManager */
    protected $documentManager;

    public function __construct(ContainerInterface $ci,DocumentManager $documentManager)
    {
        $this->container = $ci;
        $this->documentManager = $documentManager;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }


    /**
     * @return mixed
     */
    public function getDocumentManager()
    {
        return $this->documentManager;
    }

    /**
     * @throws MongoDBException
     */
    public function saveOrUpdate($documentReference){

        try{

            $this->documentManager->persist($documentReference);
            $this->documentManager->flush();

        }catch (\Exception $exception){
            throw new $exception;
        }

    }

    /**
     * @throws MongoDBException
     */
    public function bulkPersist(array $documentReferences){


        try{

            foreach ($documentReferences as $document){
                $this->documentManager->persist($document);
            }

            $this->documentManager->flush();
            $this->documentManager->clear();

        }catch (\Exception $exception){
            throw new $exception;
        }


    }


    public function getRequestData(Request $request, $isMultiPartForm=false){

        if($isMultiPartForm){
            return ['requestData' => $request->request->all() , 'files' => $request->files->all() ] ;
        }else{
            return json_decode($request->getContent(),true);
        }

    }

    public function Ok($data) : Response{
        return $this->json($data,Response::HTTP_OK);
    }

    public function badRequest($data) : Response{
        return $this->json($data,Response::HTTP_BAD_REQUEST);
    }

    public function noContent() : Response{
        return $this->json([],Response::HTTP_NO_CONTENT);

    }
}