# trabalhovtcapi
📄 README.md
Bitcoin Price API
API REST para consultar, salvar e gerenciar preços do Bitcoin em tempo real usando PHP e MySQL.

🚀 Instalação
Clone o projeto:

bash
Copiar código
git clone https://seu-repo.git
cd seu-repo
Suba a aplicação usando Docker Compose:

bash
Copiar código
docker-compose up -d
A aplicação estará disponível em:
http://localhost:8000

O banco MySQL estará rodando internamente, acessível apenas via container.

⚙️ Estrutura dos containers
Serviço	Descrição
web	Apache + PHP rodando a API
db	MySQL 8 com banco btc_prices e usuário satoshi

📚 Endpoints da API
Base URL:

bash
Copiar código
http://localhost:8000/index.php
🔹 GET /btc
Obtém o preço atual do Bitcoin diretamente da Binance.

Exemplo de requisição:

bash
Copiar código
curl http://localhost:8000/index.php/btc
Resposta de exemplo:

json
Copiar código
{
  "status": "success",
  "data": {
    "price": "62000.50"
  },
  "timestamp": "2025-05-06 14:32:21"
}
🔹 POST /btc
Salva um novo preço manualmente no banco de dados.

Exemplo de requisição:

bash
Copiar código
curl -X POST http://localhost:8000/index.php/btc \
  -H "Content-Type: application/json" \
  -d '{"price": "62500.00"}'
Resposta de exemplo:

json
Copiar código
{
  "status": "success",
  "message": "Price saved",
  "id": 12
}
🔹 GET /history
Obtém o histórico dos últimos preços salvos.

Parâmetros opcionais:

limit (número de registros)

Exemplo de requisição:

bash
Copiar código
curl "http://localhost:8000/index.php/history?limit=5"
Resposta de exemplo:

json
Copiar código
{
  "status": "success",
  "count": 5,
  "data": [
    {
      "id": 12,
      "timestamp": "2025-05-06 14:32:21",
      "price": "62500.00"
    },
    ...
  ]
}
🔹 DELETE /history
Apaga todo o histórico de preços do banco de dados.

Exemplo de requisição:

bash
Copiar código
curl -X DELETE http://localhost:8000/index.php/history
Resposta de exemplo:

json
Copiar código
{
  "status": "success",
  "message": "History cleared"
}
🛠️ Tecnologias utilizadas
PHP 8.1+

Apache

MySQL 8

Docker e Docker Compose

API pública da Binance

❗ Notas importantes
Não é necessário usar .htaccess ou mod_rewrite.

As rotas são chamadas via index.php, ex.:
http://localhost:8000/index.php/btc

Certifique-se de que o banco de dados MySQL esteja saudável antes de tentar conexões.
