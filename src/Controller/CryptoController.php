<?php

namespace App\Controller;

use App\Entity\CryptoPrice;
use App\Repository\CryptoPriceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CryptoController extends AbstractController
{
    #[Route('/api/fetch-prices', methods: ['POST'])]
    public function fetchPrices(Request $request, EntityManagerInterface $entityManager, CryptoPriceRepository $cryptoPriceRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['symbols']) || !is_array($data['symbols'])) {
            return new JsonResponse(['error' => 'Invalid symbols format. Expecting an array.'], 400);
        }

        $interval = $data['interval'] ?? '1h';
        $startTime = strtotime($data['startTime']) * 1000;
        $endTime = strtotime($data['endTime']) * 1000;

        $cryptoPriceRepository->clearTable();

        foreach ($data['symbols'] as $symbol) {
            $prices = $this->fetchDataFromBinance($symbol, $interval, $startTime, $endTime);
            foreach ($prices as $priceData) {
                $cryptoPrice = new CryptoPrice();
                $cryptoPrice->setSymbol($symbol);
                $cryptoPrice->setPrice($priceData[4]);
                $cryptoPrice->setTimestamp((new \DateTime())->setTimestamp($priceData[0] / 1000));

                $entityManager->persist($cryptoPrice);
            }
        }

        // Разовий flush для продуктивності
        $entityManager->flush();

        return new JsonResponse(['status' => 'success']);
    }

    private function fetchDataFromBinance(string $symbol, string $interval, int $startTime, int $endTime): array
    {
        $apiUrl = "https://api.binance.com/api/v3/klines?symbol={$symbol}&interval={$interval}&startTime={$startTime}&endTime={$endTime}&limit=1000";
        $response = file_get_contents($apiUrl);

        return json_decode($response, true) ?? [];
    }

    #[Route('/api/get-prices', methods: ['GET'])]
    public function getPrices(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $symbols = $request->query->get('symbols');

        $criteria = [];
        if ($symbols) {
            $symbolsArray = explode(',', $symbols);
            $criteria = ['symbol' => $symbolsArray];
        }

        $prices = $em->getRepository(CryptoPrice::class)->findBy($criteria);

        $data = [];
        foreach ($prices as $price) {
            $data[$price->getSymbol()][] = [
                'timestamp' => $price->getTimestamp()->format('Y-m-d H:i:s'),
                'price' => $price->getPrice(),
            ];
        }

        return new JsonResponse($data);
    }
}
