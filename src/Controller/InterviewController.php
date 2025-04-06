<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\ReadCsvException;
use App\Form\UploadType;
use App\Service\CsvService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InterviewController extends AbstractController
{
    #[Route(path:'/', name: 'upload_csv', methods: ['GET', 'POST'])]
    public function upload(Request $request, CsvService $csvService): Response
    {
        $form = $this->createForm(UploadType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $csvFile */
            $csvFile = $form->get('csv')->getData();
            try {
                $fees = $csvService->processRequest($csvFile);
                
                return $this->redirectToRoute('display_fees', ['fees' => $fees]);
            } catch (ReadCsvException $exception) {
                $this->addFlash('error', $exception->getMessage());

                return $this->redirectToRoute('upload_csv');
            }
        }

        return $this->render('upload.html.twig', ['form' => $form->createView()]);
    }

    #[Route(path: '/display', name: 'display_fees', methods: ['GET'])]
    public function display(Request $request): Response
    {
        $fees = $request->get('fees', []);
        $fees = array_map(static fn(array $fee) => $fee[0], $fees);

        return $this->render('display.html.twig', ['fees' => $fees]);
    }
}
