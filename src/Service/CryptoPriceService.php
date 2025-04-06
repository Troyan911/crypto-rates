<?php

namespace App\Service;

use App\Entity\CryptoPrice;
use App\Repository\CryptoPriceRepository;
use Doctrine\ORM\EntityManagerInterface;

class CryptoPriceService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CryptoPriceRepository  $cryptoPriceRepository
    )
    {
    }

    public function fetchAndSavePrices(array $symbols, string $interval, int $startTime, int $endTime): void
    {
        $this->cryptoPriceRepository->clearTable();

        foreach ($symbols as $symbol) {
            $prices = $this->fetchDataFromBinance($symbol, $interval, $startTime, $endTime);
            foreach ($prices as $priceData) {
                $cryptoPrice = new CryptoPrice();
                $cryptoPrice->setSymbol($symbol);
                $cryptoPrice->setPrice($priceData[4]);
                $cryptoPrice->setTimestamp((new \DateTime())->setTimestamp($priceData[0] / 1000));

                $this->entityManager->persist($cryptoPrice);
            }
        }

        $this->entityManager->flush();
    }

    public function getPricesBySymbols(?array $symbols = null): array
    {
        $criteria = [];
        if ($symbols !== null) {
            $criteria = ['symbol' => $symbols];
        }

        $prices = $this->cryptoPriceRepository->findBy($criteria);

        $data = [];
        foreach ($prices as $price) {
            $data[$price->getSymbol()][] = [
                'timestamp' => $price->getTimestamp()->format('Y-m-d H:i:s'),
                'price' => $price->getPrice(),
            ];
        }

        return $data;
    }

    private function fetchDataFromBinance(string $symbol, string $interval, int $startTime, int $endTime): array
    {
        $apiUrl = "https://api.binance.com/api/v3/klines?symbol={$symbol}&interval={$interval}&startTime={$startTime}&endTime={$endTime}&limit=1000";
        $response = file_get_contents($apiUrl);

        return json_decode($response, true) ?? [];
    }
}
