<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\JsonResponse;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        
        /** @var $session \Symfony\Component\HttpFoundation\Session\Session */
        $session = $request->getSession();

        if (class_exists('\Symfony\Component\Security\Core\Security')) {
            $authErrorKey = Security::AUTHENTICATION_ERROR;
            $lastUsernameKey = Security::LAST_USERNAME;
        } else {
            // BC for SF < 2.6
            $authErrorKey = SecurityContextInterface::AUTHENTICATION_ERROR;
            $lastUsernameKey = SecurityContextInterface::LAST_USERNAME;
        }

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);

        if ($this->has('security.csrf.token_manager')) {
            $csrfToken = $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue();
        } else {
            // BC for SF < 2.4
            $csrfToken = $this->has('form.csrf_provider')
                ? $this->get('form.csrf_provider')->generateCsrfToken('authenticate')
                : null;
        }

        return $this->renderLogin(array(
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfToken,
        ));
    }
    
    public function loginBarcodeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if($request->request->get('data')){
            //Get all the necessary variable
            $inputData = $request->request->get('data');
            $barcode = $inputData['barcode'];
            //Check if barcode is valid and the related user is enabled
            $user = $em->getRepository('UserBundle:User')->findOneBy(array('barcode' => $barcode));
            //$user = $em->getRepository('UserBundle:User')->find(1);
            if($user && $user->isEnabled()){
                //$user = $this->getDoctrine()->getManager()->getRepository('UserBundle:User')->find(1);
                $token = new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken($user, 'somePassword', "main", array("ROLE_SUPER_ADMIN"));
                $utils = $this->get('app.utils');
                $utils->getSecurityHandler()->setToken($token);

                $event = new \Symfony\Component\Security\Http\Event\InteractiveLoginEvent($request, $token);
                $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);

                $user = $utils->getSecurityHandler()->getToken()->getUser();
                $data = array('barcode' => $barcode, 'login' => true, 'redirect' => 'http://127.0.0.1/Academ/web/app_dev.php');
                 
                $response = new JsonResponse('[ok] Sucessfull login: '.$barcode);
                $response->setData($data);
                return $response;
                
            }else{
                $data = array('barcode' => $barcode, 'login' => false, 'redirect' => 'http://127.0.0.1/Academ/web/app_dev.php');
                 
                $response = new JsonResponse('[error] login faild: '.$barcode);
                $response->setData($data);
                return $response;
            }
           
        }
    }
    
    public function loginKeyBoardAction()
    {
        return $this->render('FOSUserBundle:Security:login_keyboard.html.twig');
    }

    /**
     * Renders the login template with the given parameters. Overwrite this function in
     * an extended controller to provide additional data for the login template.
     *
     * @param array $data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderLogin(array $data)
    {
        return $this->render('FOSUserBundle:Security:login.html.twig', $data);
    }

    public function checkAction()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    public function logoutAction()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }
}
