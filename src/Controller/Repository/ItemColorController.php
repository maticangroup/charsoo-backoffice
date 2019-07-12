<?php

namespace App\Controller\Repository;

use App\FormModels\ModelSerializer;
use App\FormModels\Repository\ItemColorModel;
use App\FormModels\Repository\ItemColorStatusModel;
use Matican\Core\Entities\Repository;
use Matican\Core\Servers;
use Matican\Core\Transaction\ResponseStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Matican\Core\Transaction\Request as Req;

/**
 * @Route("/repository/color", name="repository_item_color")
 */
class ItemColorController extends AbstractController
{
    /**
     * @Route("/create", name="_repository_item_color_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function create(Request $request)
    {
        $inputs = $request->request->all();

        /**
         * @var $colorModel ItemColorModel
         */
        $colorModel = ModelSerializer::parse($inputs, ItemColorModel::class);

        if (!empty($inputs)) {
            $request = new Req(Servers::Repository, Repository::Color, 'new');
            $request->add_instance($colorModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
            }
            $this->addFlash('f', $response->getMessage());
        }

        $allColorsRequest = new Req(Servers::Repository, Repository::Color, 'all');
        $allColorsResponse = $allColorsRequest->send();

        /**
         * @var $colors ItemColorModel[]
         */
        $colors = [];
        foreach ($allColorsResponse->getContent() as $color) {
            $colors[] = ModelSerializer::parse($color, ItemColorModel::class);
        }


        return $this->render('repository/item_color/create.html.twig', [
            'controller_name' => 'ItemColorController',
//            'colorModel' => $colorModel,
            'colors' => $colors
        ]);
    }

    /**
     * @Route("/edit/{id}", name="_repository_item_color_edit")
     * @param $id
     * @param Request $request
     * @return Response
     * @throws \ReflectionException
     */
    public function edit($id, Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $colorModel ItemColorModel
         */
        $colorModel = ModelSerializer::parse($inputs, ItemColorModel::class);
        $colorModel->setItemColorID($id);
        $request = new Req(Servers::Repository, Repository::Color, 'fetch');
        $request->add_instance($colorModel);
        $response = $request->send();
//        dd($response);
        $colorModel = ModelSerializer::parse($response->getContent(), ItemColorModel::class);

        if (!empty($inputs)) {
            /**
             * @var $colorModel ItemColorModel
             */
            $colorModel = ModelSerializer::parse($inputs, ItemColorModel::class);
            $colorModel->setItemColorID($id);
            $request = new Req(Servers::Repository, Repository::Color, 'update');
            $request->add_instance($colorModel);
            $response = $request->send();
//dd($response);
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
                return $this->redirect($this->generateUrl('repository_item_color_repository_item_color_create'));
            }
            $this->addFlash('f', $response->getMessage());
        }

        $allColorsRequest = new Req(Servers::Repository, Repository::Color, 'all');
        $allColorsResponse = $allColorsRequest->send();


        /**
         * @var $colors ItemColorModel[]
         */
        $colors = [];
        foreach ($allColorsResponse->getContent() as $color) {
            $colors[] = ModelSerializer::parse($color, ItemColorModel::class);
        }


        return $this->render('repository/item_color/edit.html.twig', [
            'controller_name' => 'ItemColorController',
            'colorModel' => $colorModel,
            'colors' => $colors,
        ]);
    }


    /**
     * @Route("/status/{id}/{machine_name}", name="_repository_item_color_status")
     * @param $id
     * @param $machine_name
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function changeColorAvailability($id, $machine_name)
    {
        $colorStatusModel = new ItemColorStatusModel();
        if ($machine_name == 'active') {
            $colorStatusModel->setColorId($id);
            $colorStatusModel->setMachineName('deactive');
        } else {
            $colorStatusModel->setColorId($id);
            $colorStatusModel->setMachineName('active');
        }
//        dd($colorStatusModel);
        $request = new Req(Servers::Repository, Repository::Color, 'change_status');
        $request->add_instance($colorStatusModel);
        $response = $request->send();
//        dd($response);
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_item_color_repository_item_color_create'));
    }

}
