<?php

namespace App\Controller\Authentication;

use App\FormModels\Authentication\UserModel;
use App\FormModels\ModelSerializer;
use App\General\AuthUser;
use Grpc\Server;
use Matican\Core\Entities\Authentication;
use Matican\Core\Servers;
use Matican\Core\Transaction\ResponseStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Matican\Core\Transaction\Request as Req;


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

                $rolePermissionRequest = new Req(Servers::Authentication, Authentication::Role, 'get_roles_permissions');
                $response = $rolePermissionRequest->send();
                AuthUser::login($userModel);
                AuthUser::cachePermissions($response->getContent());

                $this->addFlash('s', $response->getMessage());
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
     * @Route("/login/handle-login", name="authentication_login_handle_login")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function handleLogin(Request $request)
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
            return $this->redirect($this->generateUrl('authentication_login'));
        }

        if (!empty($inputs['login_button'])) {
//            dd('login_button');
            $userModel->setUserMobile($inputs['userMobile']);
            $userModel->setUserPassword($inputs['userPassword']);
//            dd($userModel);
            $request = new Req(Servers::Authentication, Authentication::User, 'login');
            $request->add_instance($userModel);
            $response = $request->send();
            dd($response);
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
            } else {
                $this->addFlash('f', $response->getMessage());
                return $this->redirect($this->generateUrl('authentication_login'));
            }
        }


    }

    /**
     * @Route("/login/{user_mobile}", name="authentication_login_user")
     * @param Request $request
     * @param $password
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function login(Request $request, $password)
    {

    }
}
