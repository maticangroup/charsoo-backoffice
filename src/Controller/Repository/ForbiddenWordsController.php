<?php

namespace App\Controller\Repository;

use App\FormModels\ModelSerializer;
use App\FormModels\Repository\ForbiddenWordsModel;
use Matican\Core\Entities\Repository;
use Matican\Core\Servers;
use Matican\Core\Transaction\ResponseStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Matican\Core\Transaction\Request as Req;

/**
 * @Route("/repository/forbidden-words", name="repository_forbidden_words")
 */
class ForbiddenWordsController extends AbstractController
{
    /**
     * @Route("/create", name="_repository_forbidden_words_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function create(Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $forbiddenWordModel ForbiddenWordsModel
         */
        $forbiddenWordModel = ModelSerializer::parse($inputs, ForbiddenWordsModel::class);
        if (!empty($inputs)) {
            $request = new Req(Servers::Repository, Repository::ForbiddenWords, 'new');
            $request->add_instance($forbiddenWordModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('success', $response->getMessage());
            }
            $this->addFlash('failed', $response->getMessage());
        }


        $allForbiddenWordsRequest = new Req(Servers::Repository, Repository::ForbiddenWords, 'all');
        $allForbiddenWordsResponse = $allForbiddenWordsRequest->send();

        /**
         * @var $forbiddenWords ForbiddenWordsModel[]
         */
        $forbiddenWords = [];
        foreach ($allForbiddenWordsResponse->getContent() as $forbiddenWord) {
            $forbiddenWords[] = ModelSerializer::parse($forbiddenWord, ForbiddenWordsModel::class);
        }


        return $this->render('repository/forbidden_words/create.html.twig', [
            'controller_name' => 'ForbiddenWordsController',
            'forbiddenWordModel' => $forbiddenWordModel,
            'forbiddenWords' => $forbiddenWords,
        ]);
    }

    /**
     * @Route("/remove/{id}", name="_repository_forbidden_words_remove")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function removeForbiddenWords($id)
    {
        $forbiddenWordModel = new ForbiddenWordsModel();
        $forbiddenWordModel->setId($id);
        $request = new Req(Servers::Repository, Repository::ForbiddenWords, 'delete');
        $request->add_instance($forbiddenWordModel);
        $response = $request->send();
//        dd($response);
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_forbidden_words_repository_forbidden_words_create'));
    }
}
