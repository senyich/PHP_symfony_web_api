Документация для NFTController

1. Добавление нового NFT
Метод: POST /api/nfts/add-nft
Описание:
Создает новый NFT и назначает его владельца. Обязательные поля: name, pattern, collection, owner_id.

cURL-запрос:

```bash
curl -X POST 'http://localhost:8000/api/nfts/add-nft' \
  -H "Content-Type: application/json" \
  -d '{
    "name": "CryptoPunk #123",
    "pattern": "Zigzag",
    "collection": "CryptoPunks",
    "owner_id": 42
  }'
```

Успешный ответ (201 Created):
```json
{
  "status": "success",
  "message": "NFT created successfully",
  "nft": {
    "id": 789,
    "name": "CryptoPunk #123",
    "pattern": "Zigzag",
    "collection": "CryptoPunks",
    "owner_id": 42
  }
}
```

Ошибки:

400 Bad Request (отсутствуют поля):
```json
{
  "status": "error",
  "message": "Missing required fields: name, pattern, collection, owner_id"
}
```

404 Not Found (владелец не найден):
```json
{
  "status": "error",
  "message": "Owner not found"
}
```

500 Internal Server Error (ошибка сервера):
```json
{
  "status": "error",
  "message": "Failed to create NFT: [детали ошибки]"
}```

Документация для UserController
1. Регистрация пользователя
Метод: POST /api/users/register
Описание:
Создает нового пользователя. Обязательные поля: name, password. Возвращает JWT-токен для аутентификации.

cURL-запрос:

```bash
curl -X POST 'http://localhost:8000/api/users/register' \
  -H "Content-Type: application/json" \
  -d '{
    "name": "john_doe",
    "password": "secure_password123"
  }'
```
Успешный ответ (201 Created):

```json
{
  "status": "success",
  "message": "User registered successfully",
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 42,
    "name": "john_doe",
    "orders_count": 0
  }
}
```
Ошибки:

400 Bad Request (отсутствуют поля):
```json
{"status":"error","message":"Missing required fields: name, password"}
```
409 Conflict (пользователь существует):
```json
{"status":"error","message":"Username already exists"}
```
2. Аутентификация пользователя
Метод: POST /api/users/login
Описание:
Авторизует пользователя. Обязательные поля: name, password. Возвращает JWT-токен.
cURL-запрос:
```bash
curl -X POST 'http://localhost:8000/api/users/login' \
  -H "Content-Type: application/json" \
  -d '{
    "name": "john_doe",
    "password": "secure_password123"
  }'
```
Успешный ответ (200 OK):
```json
{
  "status": "success",
  "message": "Login successful",
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 42,
    "name": "john_doe",
    "orders_count": 3
  }
}
```
Ошибки:
400 Bad Request (отсутствуют поля):
```json
{"status":"error","message":"Missing required fields: name, password"}
```
401 Unauthorized (неверные данные):
```json
{"status":"error","message":"Invalid credentials"}
```
3. Покупка NFT по ордеру
Метод: POST /api/users/buy-order/{orderId}
Описание:
Покупка NFT через ордер. Требует заголовок Authorization: Bearer <token>.

cURL-запрос:

```bash
curl -X POST 'http://localhost:8000/api/users/buy-order/789' \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..." \
  -H "Content-Type: application/json"
```
Успешный ответ (200 OK):

```json
{
  "status": "success",
  "message": "Order purchased successfully",
  "nft": {
    "id": 101,
    "name": "CryptoPunk #101",
    "new_owner_id": 42
  },
  "balance": 85.50
}
```
Ошибки:

401 Unauthorized (нет токена):
```json
{"status":"error","message":"Authorization token required"}
```
403 Forbidden (покупка своего ордера):
```json
{"status":"error","message":"You cannot buy your own order"}
```
404 Not Found (ордер/пользователь не найден):
```json
{"status":"error","message":"Order not found"}
```
400 Bad Request (недостаточно средств):
```json
{"status":"error","message":"Insufficient funds"}
```
4. Публикация ордера на продажу NFT
Метод: POST /api/users/publish-order
Описание:
Создание ордера на продажу NFT. Обязательные поля: price, nft_id.

cURL-запрос:

```bash
curl -X POST 'http://localhost:8000/api/users/publish-order' \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..." \
  -H "Content-Type: application/json" \
  -d '{
    "price": 150.75,
    "nft_id": 101
  }'
```
Успешный ответ (201 Created):
```json
{
  "status": "success",
  "message": "Order published successfully",
  "order": {
    "id": 202,
    "price": 150.75,
    "created_time": "2025-07-02 14:30:00",
    "nft_id": 101
  }
}
```
Ошибки:
403 Forbidden (не владелец NFT):
```json
{"status":"error","message":"You are not the owner of this NFT"}
```
404 Not Found (NFT не найден):
```
json
{"status":"error","message":"NFT not found"}
```
5. Получение информации о пользователе
Метод: GET /api/users/get-user-fullinfo
Описание:
Возвращает профиль пользователя, его NFT и ордера. Требует заголовок Authorization.

cURL-запрос:

```bash
curl -X GET 'http://localhost:8000/api/users/get-user-fullinfo' \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
```
Успешный ответ (200 OK):

```json
{
  "status": "success",
  "user": {
    "id": 42,
    "name": "john_doe",
    "ordersCount": 2
  },
  "orders": [
    {
      "id": 202,
      "price": 150.75,
      "createdTime": "2025-07-02 14:30:00",
      "nft": {"id": 101}
    }
  ],
  "nfts": [
    {
      "id": 101,
      "name": "CryptoPunk #101",
      "pattern": "Zigzag",
      "collection": "CryptoPunks",
      "owner_id": 42,
      "order_id": 202
    }
  ]
}
```
Ошибки:

401 Unauthorized (невалидный токен):
```json
{"status":"error","message":"Invalid token"}
```
404 Not Found (пользователь не найден):
```json
{"status":"error","message":"User not found"}
```

6. Выход из системы
Метод: POST /api/users/logout
Описание:
Инвалидирует токен пользователя (реализация на клиенте).

cURL-запрос:
```bash
curl -X POST 'http://localhost:8000/api/users/logout' \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
```
Успешный ответ (200 OK):

```json
{
  "status": "success",
  "message": "Logout successful"
}
```
Примечания:
Аутентификация:
Все защищенные методы требуют заголовок:
Authorization: Bearer <your_jwt_token>

Формат данных:

Цены передаются как числа с плавающей точкой (например, 150.75)

Даты в формате YYYY-MM-DD HH:MM:SS

Ошибки 500:

```json
{"status":"error","message":"Internal server error details"}
```
Возникают при непредвиденных сбоях сервера.