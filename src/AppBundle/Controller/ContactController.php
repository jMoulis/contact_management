<?php

namespace AppBundle\Controller;

use AppBundle\Api\ApiProblem;
use AppBundle\Api\ApiProblemException;
use AppBundle\Entity\Contact;
use AppBundle\Form\ContactType;
use AppBundle\Form\UpdateContactType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends BaseController
{
    /**
     * Fetch Form datas From Client then persist it in the database
     *
     * @Route("/api/contacts", name="api_contact_new")
     * @Method("POST")
     */
    public function newAction(Request $request)
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);

        $this->processForm($request, $form);

        if (!$form->isValid()) {

            $this->throwApiProblemValidationException($form);
        }

        $contact->setCreatedAt(new \DateTime());

        $em = $this->getDoctrine()->getManager();
        $em->persist($contact);
        $em->flush();

        $location = $this->generateUrl('api_contacts_show', [
            'firstname' => $contact->getFirstname()
        ]);

        $response = $this->createApiResponse($contact, 201);
        $response->headers->set('Location', $location);

        return $response;
    }

    /**
     * @Route("/api/contacts/{firstname}", name="api_contacts_show")
     * @Method("GET")
     */
    public function showAction($firstname)
    {
        $contact = $this->getDoctrine()
            ->getRepository('AppBundle:Contact')
            ->findOneBy(['firstname' => $firstname]);

        if (!$contact) {
            throw $this->createNotFoundException('No contact '. $firstname .' bummer');
        }
        $response = $this->createApiResponse($contact, 200);

        return $response;
    }

    /**
     * @Route("/api/contacts")
     * @Method("GET")
     */
    public function listAction()
    {
        $contacts = $this->getDoctrine()
            ->getRepository('AppBundle:Contact')
            ->findAll();

        $response = $this->createApiResponse(['contacts' => $contacts], 200);

        return $response;
    }

    /**
     * @Route("/api/contacts/{firstname}", name="api_contacts_update")
     * @Method({"PUT", "PATCH"})
     */
    public function updateAction(Request $request, $firstname)
    {
        $contact = $this->getDoctrine()
            ->getRepository('AppBundle:Contact')
            ->findOneBy(['firstname' => $firstname]);

        if (!$contact) {
            throw $this->createNotFoundException('No contact '. $firstname .' bummer');
        }

        $form = $this->createForm(UpdateContactType::class, $contact);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            $this->throwApiProblemValidationException($form);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($contact);
        $em->flush();

        $response = $this->createApiResponse($contact, 200);

        return $response;
    }

    /**
     * @Route("/api/contacts/{firstname}", name="api_contacts_delete")
     * @Method("DELETE")
     */
    public function deleteAction($firstname)
    {
        $contact = $this->getDoctrine()
            ->getRepository('AppBundle:Contact')
            ->findOneBy(['firstname' => $firstname]);

        if ($contact) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($contact);
            $em->flush();
        }

        return new Response(null, 204);
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
