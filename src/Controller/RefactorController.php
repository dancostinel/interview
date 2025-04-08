<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\TxtService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RefactorController extends AbstractController
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/public/input.txt')] private string $inputJson,
        #[Autowire(env: 'json:EU_COUNTRY_CODES')] private array $euCountryCodes,
    ) {
    }

    #[Route(path: '/parse-txt-file')]
    public function parseFile(TxtService $txtService): Response
    {
        $uploadedFile = new UploadedFile($this->inputJson, 'input.txt');
        try {
            $commissions = $txtService->processRequest($uploadedFile);
        } catch (\Exception $exception) {
            return new Response($exception->getMessage());
        }

        return new Response(implode(', ', $commissions));
    }
}
