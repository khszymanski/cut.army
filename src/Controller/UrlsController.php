<?php

namespace App\Controller;

use App\Entity\Url;
use App\Form\UrlFormType;
use App\Repository\UrlRepository;
use Doctrine\ORM\EntityManagerInterface;
use Hashids\Hashids;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class UrlsController extends AbstractController
{
    private $urlRepository;
    private $em;

    public function __construct(UrlRepository $urlRepository, EntityManagerInterface $em)
    {
        $this->urlRepository = $urlRepository;
        $this->em = $em;
    }

    #[Route('/{hid}', name: 'app_urls_show')]
    public function show($hid): Response
    {
        $hashids = new Hashids();
        $id = $hashids->decode($hid);
        

        $url = $this->urlRepository->find(reset($id));

        return new RedirectResponse($url->getUrl());
    }

    #[Route('/', name: 'app_urls_index')]
    public function index(Request $request): Response
    {
        $url = new Url();
        $form = $this->createForm(UrlFormType::class, $url);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newURL = $form->getData();


            $url = $newURL->getUrl();
            $createdAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));

            $newURL->setUrl($url);
            $newURL->setCreatedAt($createdAt);

            $this->em->persist($newURL);
            $this->em->flush();

            $id = $newURL->getId();

            $hashids = new Hashids();

            return $this->render('index.html.twig', [
                'form' => $form,
                'newURL' => $hashids->encode($id)
            ]);
        }


        return $this->render('index.html.twig', [
            'form' => $form
        ]);
    }
}
