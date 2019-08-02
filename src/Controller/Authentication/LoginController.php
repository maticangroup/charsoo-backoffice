<?php

namespace App\Controller\Authentication;

use App\FormModels\Authentication\UserModel;
use App\FormModels\ModelSerializer;
use App\General\AuthUser;
use Grpc\Server;
use Matican\Core\Entities\Authentication;
use Matican\Core\Servers;
use Matican\Core\Transaction\Response;
use Matican\Core\Transaction\ResponseStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Matican\Core\Transaction\Request as Req;
use Symfony\Component\Validator\Constraints\Json;


class LoginController extends AbstractController
{
    /**
     * @Route("/login", name="authentication_login")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function read(Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $userModel UserModel
         */
        $userModel = ModelSerializer::parse($inputs, UserModel::class);

        if (!empty($inputs['password_button'])) {
//            dd('password_button');
            $userModel->setUserMobile($inputs['userMobile']);
            $request = new Req(Servers::Authentication, Authentication::User, 'send_password');
            $request->add_instance($userModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
            } else {
                $this->addFlash('f', $response->getMessage());
            }
//            dd($userModel);
//            return $this->redirect($this->generateUrl('authentication_login'));
        }

        if (!empty($inputs['login_button'])) {
//            dd('login_button');
            $userModel->setUserMobile($inputs['userMobile']);
            $userModel->setUserPassword($inputs['userPassword']);
//            dd($userModel);
            $request = new Req(Servers::Authentication, Authentication::User, 'login');
            $request->add_instance($userModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {

                $userModel = ModelSerializer::parse($response->getContent(), UserModel::class);
                $userModel->setUserPassword('');

                AuthUser::login($userModel);

                AuthUser::purge_role_permissions();

                $this->addFlash('s', $response->getMessage());

                return $this->redirect($this->generateUrl("default"));
            } else {
                $this->addFlash('f', $response->getMessage());
//                return $this->redirect($this->generateUrl('authentication_login'));
            }
        }


        return $this->render('authentication/login/read.html.twig', [
            'controller_name' => 'LoginController',
            'userModel' => $userModel,
        ]);
    }

    /**
     * @Route("/logout", name="authentication_logout")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function logout()
    {
        $currentUserID = AuthUser::current_user()->getUserId();
        $request = new Req(Servers::Authentication, Authentication::User, 'logout');
        $userModel = new UserModel();
        $userModel->setUserId($currentUserID);
        $request->add_instance($userModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            AuthUser::logout();
            return $this->redirect($this->generateUrl("authentication_login"));
        } else {
            return $this->redirect($this->generateUrl("default"));
        }
    }
}
