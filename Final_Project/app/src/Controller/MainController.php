<?php

namespace App\Controller;

use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{

    #[Route('/main', name: 'app_main', methods: ['GET'])]
    public function getMain(ImageRepository $repository): Response
    {
        $images = $repository->findLatest(10); // последние 10 изображений

        return $this->render('main/main.html.twig', [
            'images' => $images,
        ]);
    }

    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function search(Request $request, ImageRepository $repository): Response
    {
        $keyword = $request->query->get('query', '');
        $images = [];

        if ($keyword) {
            $images = $repository->findByKeywords($keyword);
        }

        return $this->render('main/main.html.twig', [
            'images' => $images,
            'query' => $keyword
        ]);
    }

}
