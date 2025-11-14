<?php

namespace App\Controller;

use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{

    #[Route('/profile', name: 'app_profile',methods: 'GET')]
    public function getProfilePage(ImageRepository $repository): Response
    {
        $user = $this->getUser();
        $images = $repository->findByUser($user);
        return $this->render("profile/profile.html.twig",[
            "user" => $user,
            "images" => $images
        ]);
    }

}
