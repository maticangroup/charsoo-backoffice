<?php

namespace App\Controller\Repository;

use App\FormModels\ModelSerializer;
use App\FormModels\Repository\SizeModel;
use App\FormModels\Repository\SizeStatusModel;
use Matican\Core\Entities\Repository;
use Matican\Core\Servers;
use Matican\Core\Transaction\ResponseStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Matican\Core\Transaction\Request as Req;

/**
 * @Route("/repository/size", name="repository_size")
 */
class SizeController extends AbstractController
{
    /**
     * @Route("/create", name="_repository_size_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function create(Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $sizeModel SizeModel
         */
        $sizeModel = ModelSerializer::parse($inputs, SizeModel::class);
        if (!empty($inputs)) {
            $request = new Req(Servers::Repository, Repository::Size, 'new');
            $request->add_instance($sizeModel);
            $response = $request->send();
//            dd($response);
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('success', $response->getMessage());
            }
            $this->addFlash('failed', $response->getMessage());
        }

        $sizesRequest = new Req(Servers::Repository, Repository::Size, 'all');
        $sizesResponse = $sizesRequest->send();

        /**
         * @var $sizes SizeModel[]
         */
        $sizes = [];
        if ($sizesResponse->getContent()) {
            foreach ($sizesResponse->getContent() as $size) {
                $sizes[] = ModelSerializer::parse($size, SizeModel::class);
            }
        }


        return $this->render('repository/size/create.html.twig', [
            'controller_name' => 'SizeController',
            'sizeModel' => $sizeModel,
            'sizes' => $sizes,
        ]);
    }

    /**
     * @Route("/edit/{id}", name="_repository_size_edit")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function edit($id, Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $sizeModel SizeModel
         */
        $sizeModel = ModelSerializer::parse($inputs, SizeModel::class);
        $sizeModel->setSizeID($id);
        $request = new Req(Servers::Repository, Repository::Size, 'fetch');
        $request->add_instance($sizeModel);
        $response = $request->send();
        $sizeModel = ModelSerializer::parse($response->getContent(), SizeModel::class);

        if (!empty($inputs)) {
            $sizeModel = ModelSerializer::parse($inputs, SizeModel::class);
            $sizeModel->setSizeID($id);
            $request = new Req(Servers::Repository, Repository::Size, 'update');
            $request->add_instance($sizeModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', '');
            } else {
                $this->addFlash('f', '');
            }
        }

        $sizesRequest = new Req(Servers::Repository, Repository::Size, 'all');
        $sizesResponse = $sizesRequest->send();

        /**
         * @var $sizes SizeModel[]
         */
        $sizes = [];
        if ($sizesResponse->getContent()) {
            foreach ($sizesResponse->getContent() as $size) {
                $sizes[] = ModelSerializer::parse($size, SizeModel::class);
            }
        }


        return $this->render('repository/size/edit.html.twig', [
            'controller_name' => 'SizeController',
            'sizeModel' => $sizeModel,
            'sizes' => $sizes,
        ]);
    }

    /**
     * @Route("/status/{size_id}/{machine_name}", name="_repository_size_status")
     * @param $size_id
     * @param $machine_name
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function changeSizeAvailability($size_id, $machine_name)
    {
        $sizeStatusModel = new SizeStatusModel();
        if ($machine_name == 'active') {
            $sizeStatusModel->setSizeId($size_id);
            $sizeStatusModel->setSizeStatusMachineName('deactive');
        } else {
            $sizeStatusModel->setSizeId($size_id);
            $sizeStatusModel->setSizeStatusMachineName('active');
        }

        $request = new Req(Servers::Repository, Repository::Size, 'change_size_status');
        $request->add_instance($sizeStatusModel);
        $response = $request->send();
//        dd($response);
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_size_repository_size_create'));
    }
}
