**Update rates**

```bash
curl --location 'http://localhost:8000/api/fetch-prices' \
--header 'Content-Type: application/json' \
--data '{
"symbols": ["BTCUSDT", "ETHUSDT", "BNBUSDT"],
"interval": "1h",
"startTime": "2024-03-01",
"endTime": "2024-03-02"
}'
```

**Get specific symbols**
```bash
curl -X GET "http://localhost:8000/api/get-prices?symbols=BTCUSDT,ETHUSDT"
```
Get all stored prices:
```bash
curl -X GET "http://localhost:8000/api/get-prices"
```