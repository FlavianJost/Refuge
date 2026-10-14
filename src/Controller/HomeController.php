<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[AsController]
#[Route(path: '/', name: 'homepage', methods: ['GET'])]
class HomeController
{
    public function __invoke(Environment $twig) : Response
    {
        return new Response($twig->render('home.html.twig', [

        ]), Response::HTTP_OK);
    }
}
