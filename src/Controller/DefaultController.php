<?php

namespace App\Controller;

use App\Document\Account;
use App\Repositories\AccountRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class DefaultController extends AbstractApiController
{
    /**
     * @Route("/reportsodm", name="reportsodm" , methods={"GET","POST"})
     */
    public function home(Request $request): Response
    {
        $id = $request->get('id');
        $all = $request->get('All');
        $dm = $this->getDocumentManager();

        /** @var AccountRepository $repository */
        $repository = $dm->getRepository(Account::class);

        if($all){
            $datos = $repository->getReportsWithODM();
        } else {
            $datos = $repository->getReportsWithODM($id);
        }
        return $this->render('reportsODM/index.html.twig', [
            'controller_name' => 'ReportsController',
            'datos' => $datos
        ]);
    }

}