<?php

namespace Hatimeria\ExtJSBundle\Controller;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Hatimeria\ExtJSBundle\Response\Success;
use Hatimeria\ExtJSBundle\Response\Failure;

class SecurityController extends Controller
{
    /**
     * @Template()
     */
    public function loginAction()
    {
        // code from fos user security controller
        $request = $this->container->get('request');
        /* @var $request \Symfony\Component\HttpFoundation\Request */
        $session = $request->getSession();
        /* @var $session \Symfony\Component\HttpFoundation\Session */

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        if ($error) {
            $failure = new Failure($this->get('translator')->trans("form.login.failed", array(), "HatimeriaAdminBundle"));
            $content = json_encode($failure->toArray());
            
            return new Response($content);
        }
        
        return array();
    }
    
    /**
     * Access denied page for error detected by javascript
     * 
     * @Template()
     */
    public function error403Action()
    {
        return array();
    }
    
    public function afterLoginAction()
    {
        $success = new Success();
        
        $content = json_encode($success->toArray());
        
        return new Response($content);
    }
}