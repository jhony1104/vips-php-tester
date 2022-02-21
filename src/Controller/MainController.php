<?php

namespace App\Controller;

use Jcupitt\Vips\Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main")
     */
    public function index(Request $request): Response
    {
        $session = $request->getSession();

        $form = $this->createFormBuilder(['config' => $session->get('config', '{"Q":75,"strip":true}')])
            ->add('file', FileType::class, [
                'attr' => [
                    'accept' => 'image/*'
                ]
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'JPEG' => 'jpeg',
                    'PNG' => 'png',
                    'WEBP' => 'webp'
                ]
            ])
            ->add('config', TextareaType::class)
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->render('main/index.html.twig', [
                'form' => $form->createView()
            ]);
        }

        $data = $form->getData();

        $session->set('config', $data['config']);

        $image = Image::newFromFile($data['file']->getPathname())->autorot()->writeToBuffer('.' . $data['type'], json_decode($data['config'], true));

        unlink($data['file']->getPathname());

        return new Response($image, 200, [
            'Content-Type' => 'image/' . $data['type'],
            'Content-Disposition' => 'attachment; filename="'.uniqid().'.'.$data['type'].'"'
        ]);
    }
}
