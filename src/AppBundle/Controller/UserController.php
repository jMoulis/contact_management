<?php

namespace AppBundle\Controller;

use AppBundle\Api\ApiProblem;
use AppBundle\Api\ApiProblemException;
use AppBundle\Entity\User;
use AppBundle\Form\UpdateUserType;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;


class UserController extends BaseController
{
    /**
     * @Route("/api/register", name="user_register")
     * @Method("GET")
     */
    public function registerAction()
    {
        if ($this->isUserLoggedIn()) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        return $this->render('user/register.twig', array('user' => new User()));
    }

    /**
     * @Route("/api/users", name="api_user_new")
     * @Method("POST")
     */
    public function newAction(Request $request)
    {
        //$this->denyAccessUnlessGranted('ROLE_USER');
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            $this->throwApiProblemValidationException($form);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $this->loginUser($user);

        $response = $this->createApiResponse([], 201);
        $response->headers->set('Location', '/login');

        return $response;
    }

    /**
     * @Route("/api/users/{username}", name="api_users_show")
     * @Method("GET")
     */
    public function showAction($username)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(['username' => $username]);

        if (!$user) {
            throw $this->createNotFoundException('No contact '. $username .' bummer');
        }
        $response = $this->createApiResponse($user, 200);

        return $response;
    }

    /**
     * @Route("/api/users", name="api_users_collection")
     * @Method("GET")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $filter = $request->query->get('filter');

        $repository = $this->getDoctrine()
            ->getRepository(User::class);

        $qb = $repository->finAllQueryBuilder();

        $paginatedCollection = $this->get('pagination_factory')
            ->createCollection($qb, $request, 'api_users_collection');

        $response = $this->createApiResponse($paginatedCollection);

        return $response;
    }

    /**
     * @Route("/api/users/{username}", name="api_users_update")
     * @Method({"PUT", "PATCH"})
     */
    public function updateAction(Request $request, $username)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(['username' => $username]);

        if (!$user) {
            throw $this->createNotFoundException('No contact '. $username .' bummer');
        }

        $form = $this->createForm(UpdateUserType::class, $user);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            $this->throwApiProblemValidationException($form);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $response = $this->createApiResponse($user, 200);

        return $response;
    }

    /**
     * @Route("/api/users/{username}", name="api_users_delete")
     * @Method("DELETE")
     */
    public function deleteAction($username)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(['username' => $username]);

        if ($user) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/login", name="security_login_form")
     */
    public function loginAction(Request $request)
    {
        if ($this->isUserLoggedIn()) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        return $this->render('user/login.twig', array(
            'error'         => $this->container->get('security.authentication_utils')->getLastAuthenticationError(),
            'last_username' => $this->container->get('security.authentication_utils')->getLastUserName()
        ));
    }

    /**
     * @Route("/login_check", name="security_login_check")
     */
    public function loginCheckAction()
    {
        throw new \Exception('Should not get here - this should be handled magically by the security system!');
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logoutAction()
    {
        throw new \Exception('Should not get here - this should be handled magically by the security system!');
    }

    private function processForm(Request $request, FormInterface $form)
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {

            $apiProblem = new ApiProblem(400, ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT);

            throw new ApiProblemException($apiProblem);
        }

        $clearMissing = $request->getMethod() != 'PATCH';
        $form->submit($data, $clearMissing);
    }

    private function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }
        return $errors;
    }

    private function throwApiProblemValidationException(FormInterface $form)
    {
        $errors = $this->getErrorsFromForm($form);

        $apiProblem = new ApiProblem(
            400,
            ApiProblem::TYPE_VALIDATION_ERROR
        );
        $apiProblem->set('errors', $errors);

        throw new ApiProblemException($apiProblem);
    }
}
