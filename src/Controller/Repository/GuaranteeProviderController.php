<?php

namespace App\Controller\Repository;

use App\FormModels\ModelSerializer;
use App\FormModels\Repository\GuaranteeProviderModel;
use App\FormModels\Repository\GuaranteeProviderStatusModel;
use Matican\Core\Entities\Repository;
use Matican\Core\Servers;
use Matican\Core\Transaction\ResponseStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Matican\Core\Transaction\Request as Req;

/**
 * @Route("/repository/guarantee-provider", name="repository_guarantee_provider")
 */
class GuaranteeProviderController extends AbstractController
{

    /**
     * @Route("/create", name="_repository_guarantee_provider_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function create(Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $guaranteeProviderModel GuaranteeProviderModel
         */
        $guaranteeProviderModel = ModelSerializer::parse($inputs, GuaranteeProviderModel::class);

        if (!empty($inputs)) {
            $request = new Req(Servers::Repository, Repository::Guarantee, 'add_provider');
            $request->add_instance($guaranteeProviderModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
            } else {
                $this->addFlash('f', $response->getMessage());
            }
        }

        $allGuaranteeProviderRequest = new Req(Servers::Repository, Repository::Guarantee, 'get_providers');
        $allGuaranteeProviderResponse = $allGuaranteeProviderRequest->send();

        /**
         * @var $guaranteeProviders GuaranteeProviderModel[]
         */
        $guaranteeProviders = [];
        foreach ($allGuaranteeProviderResponse->getContent() as $guaranteeProvider) {
            $guaranteeProviders[] = ModelSerializer::parse($guaranteeProvider, GuaranteeProviderModel::class);
        }

        return $this->render('repository/guarantee_provider/create.html.twig', [
            'controller_name' => 'GuaranteeProviderController',
            'guaranteeProviderModel' => $guaranteeProviderModel,
            'guaranteeProviders' => $guaranteeProviders,
        ]);
    }


    /**
     * @Route("/edit/{id}", name="_repository_guarantee_provider_edit")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function edit($id, Request $request)
    {
        $inputs = $request->request->all();

        /**
         * @var $guaranteeProviderModel GuaranteeProviderModel
         */
        $guaranteeProviderModel = ModelSerializer::parse($inputs, GuaranteeProviderModel::class);
        $guaranteeProviderModel->setGuaranteeProviderID($id);
        $request = new Req(Servers::Repository, Repository::Guarantee, 'fetch_provider');
        $request->add_instance($guaranteeProviderModel);
        $response = $request->send();
        $guaranteeProviderModel = ModelSerializer::parse($response->getContent(), GuaranteeProviderModel::class);

        if (!empty($inputs)) {
            $guaranteeProviderModel = ModelSerializer::parse($inputs, GuaranteeProviderModel::class);
            $guaranteeProviderModel->setGuaranteeProviderID($id);
            $request = new Req(Servers::Repository, Repository::Guarantee, 'update_provider');
            $request->add_instance($guaranteeProviderModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
            } else {
                $this->addFlash('f', $response->getMessage());
            }
        }

        $allGuaranteeProviderRequest = new Req(Servers::Repository, Repository::Guarantee, 'get_providers');
        $allGuaranteeProviderResponse = $allGuaranteeProviderRequest->send();

        /**
         * @var $guaranteeProviders GuaranteeProviderModel[]
         */
        $guaranteeProviders = [];
        foreach ($allGuaranteeProviderResponse->getContent() as $guaranteeProvider) {
            $guaranteeProviders[] = ModelSerializer::parse($guaranteeProvider, GuaranteeProviderModel::class);
        }


        return $this->render('repository/guarantee_provider/edit.html.twig', [
            'controller_name' => 'GuaranteeProviderController',
            'guaranteeProviderModel' => $guaranteeProviderModel,
            'guaranteeProviders' => $guaranteeProviders,

        ]);
    }


    /**
     * @Route("/edit/{guarantee_provider_id}/{machine_name}", name="_repository_guarantee_provider_status")
     * @param $guarantee_provider_id
     * @param $machine_name
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function changeGuaranteeProviderAvailability($guarantee_provider_id, $machine_name)
    {
        $guaranteeProviderStatusModel = new GuaranteeProviderStatusModel();
        if ($machine_name == 'active') {
            $guaranteeProviderStatusModel->setGuaranteeProviderId($guarantee_provider_id);
            $guaranteeProviderStatusModel->setGuaranteeProviderStatusMachineName('deactive');
        } else {
            $guaranteeProviderStatusModel->setGuaranteeProviderId($guarantee_provider_id);
            $guaranteeProviderStatusModel->setGuaranteeProviderStatusMachineName('active');
        }

        $request = new Req(Servers::Repository, Repository::Guarantee, 'change_provider_status');
        $request->add_instance($guaranteeProviderStatusModel);
        $response = $request->send();
//        dd($response);
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_guarantee_provider_repository_guarantee_provider_create'));
    }
}
