<?php

namespace App\Controller;

use App\Form\ImageUploadType;
use App\Service\ImageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends AbstractController
{
    public function __construct(private ImageService $imageService, private EntityManagerInterface $em) {}

    #[Route('/upload', name: 'app_upload', methods: ['GET', 'POST'])]
    public function upload(Request $request): Response
    {
        $form = $this->createForm(ImageUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            $image = $this->imageService->uploadImage($imageFile, [
                'user' => $this->getUser(),
                'isPrivate' => $form->get('isPrivate')->getData(),
                'ocr' => true
            ]);

            $keywordsRaw = $request->request->get('keywords', '');
            $keywordsArray = $keywordsRaw ? explode(',', $keywordsRaw) : [];
            $image->setKeywords($keywordsArray);

            $this->em->persist($image);
            $this->em->flush();

            $this->addFlash('success', 'Image uploaded successfully');

            return $this->redirectToRoute('app_main');
        }

        return $this->render('upload/upload.html.twig', [
            'uploadForm' => $form->createView(),
        ]);
    }

    #[Route('/upload/preview', name: 'app_image_preview', methods: ['POST'])]
    public function preview(Request $request): JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('imageFile');

        if(!$file) {
            return $this->json(['error' => 'No file uploaded'], 400);
        }

        $image = $this->imageService->uploadImage($file, [
            'user' => $this->getUser(),
            'ocr' => true,
            'temp' => true
        ]);

        return $this->json([
            'previewFilename' => $image->getFilename(),
            'keywords' => $image->getKeywords(),
        ]);
    }
}
