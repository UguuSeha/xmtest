<?php
declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Port\QuoteProvider;
use App\UI\Dto\HistoryRequestDto;
use App\UI\Event\HistoryRequestedEvent;
use App\UI\Form\HistoryRequestType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class SiteController extends AbstractController
{
    #[Route('/', name: 'site_index')]
    public function index(MessageBusInterface $eventBus, Request $request): Response
    {
        $dto = new HistoryRequestDto();
        $form = $this->createForm(HistoryRequestType::class, $dto);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $eventBus->dispatch(new HistoryRequestedEvent(
                $dto->company,
                $dto->start,
                $dto->end,
                $dto->email,
            ));

            return $this->redirectToRoute('site_history', [
                'company' => $dto->company->getSymbol(),
                'start' => $dto->start->format('Y-m-d'),
                'end' => $dto->end->format('Y-m-d'),
            ]);
        }

        return $this->renderForm('site/index.html.twig', [
            'historyRequestForm' => $form,
        ]);
    }

    #[Route('/history', name: 'site_history')]
    public function history(
        QuoteProvider $quoteProvider,
        Request $request,
    ): Response {
        $dto = new HistoryRequestDto();
        $form = $this->createForm(HistoryRequestType::class, $dto, ['include_email' => false]);
        $form->submit($request->query->all());

        if (!$form->isValid()) {
            return $this->render('site/error.html.twig');
        }

        $quotes = $quoteProvider->getHistoricalData(
            $dto->company->getSymbol(),
            $dto->start,
            $dto->end,
        );

        return $this->render('site/history.html.twig', [
            'company' => $dto->company,
            'quotes' => $quotes,
        ]);
    }
}
