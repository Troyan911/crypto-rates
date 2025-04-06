<?php

namespace App\Controller;

use App\Service\CryptoPriceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CryptoController extends AbstractController
{
    public function __construct(
        private readonly CryptoPriceService $cryptoPriceService
    )
    {
    }

    #[Route('/api/fetch-prices', methods: ['POST'])]
    public function fetchPrices(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['symbols']) || !is_array($data['symbols'])) {
            return new JsonResponse(['error' => 'Invalid symbols format. Expecting an array.'], 400);
        }

        $interval = $data['interval'] ?? '1h';
        $startTime = strtotime($data['startTime']) * 1000;
        $endTime = strtotime($data['endTime']) * 1000;

        $this->cryptoPriceService->fetchAndSavePrices(
            $data['symbols'],
            $interval,
            $startTime,
            $endTime
        );

        return new JsonResponse(['status' => 'success']);
    }

    #[Route('/api/get-prices', methods: ['GET'])]
    public function getPrices(Request $request): JsonResponse
    {
        $symbolsParam = $request->query->get('symbols');
        $symbols = $symbolsParam ? explode(',', $symbolsParam) : null;

        $data = $this->cryptoPriceService->getPricesBySymbols($symbols);

        return new JsonResponse($data);
    }
}

