<?php

namespace App\Service;

use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageService
{
    private array $stopWords = [
        'и','или','в','не','на','с','что','по','как','а','но','за','от','до','для','из','у','о','себя','же','бы','же','он','она',
        'оно','они','это','нам','нас','вас','вам','им','их','его','ее','её','ему','ей','что','где','как','зачем','почему','во','потому','поэтому',
        'если','везде','я','ты','вы','есть','которой','которая','который','которому','какой','какая','какое','какие','того','тех','иных',
        'сколько','чтобы',
        'the','and','for','with','this','that','but','not','you','are','was','from','have','had','has','they','their','there','what','which'
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private string $uploadsDir
    ) {}

    public function uploadImage(UploadedFile $file, array $options = []): Image
    {
        $ocr = $options['ocr'] ?? false;
        $user = $options['user'] ?? null;
        $isTemp = $options['temp'] ?? false;

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $targetDir = $isTemp ? $this->uploadsDir . '/temp' : $this->uploadsDir;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $file->move($targetDir, $newFilename);

        $image = new Image();
        $image->setFilename($newFilename);
        $image->setOriginalFilename($file->getClientOriginalName());
        $image->setUser($user);
        $image->setIsPrivate($options['isPrivate'] ?? false);

        if ($ocr) {
            $recognizedText = $this->doOCR($targetDir . '/' . $newFilename);
            $image->setRecognizedText($recognizedText);

            $keywords = $this->extractKeywords($recognizedText);
            $image->setKeywords($keywords);
        }

        if (!$isTemp) {
            $this->em->persist($image);
            $this->em->flush();
        }

        return $image;
    }

    private function doOCR(string $filePath): string
    {
        $output = [];
        $returnVar = null;
        exec("tesseract " . escapeshellarg($filePath) . " stdout -l eng+rus", $output, $returnVar);
        return implode("\n", $output);
    }

    private function extractKeywords(string $text): array
    {
        $words = preg_split('/[\s,.;:!?()"\'«»_-]+/u', mb_strtolower($text));
        $words = array_map(fn($w) => trim($w, "«»\"'"), $words);
        $words = array_filter($words, fn($w) => strlen($w) > 2 && !in_array($w, $this->stopWords));

        $keywords = array_slice(array_unique($words), 0, 25);

        return $keywords;
    }
}
