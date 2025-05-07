<?php
// Cabeçalhos para CORS e JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Inclui conexão com o banco
require 'db.php';

// Captura a URL da requisição
$request_uri = $_SERVER['REQUEST_URI'];

// Remove a query string (ex: ?limit=10)
$request_uri = parse_url($request_uri, PHP_URL_PATH);

// Remove o /index.php da frente
$endpoint = preg_replace('#^/index\.php#', '', $request_uri);

// Limpa barra extra final
$endpoint = rtrim($endpoint, '/');

// Se ficar vazio, define como /
if ($endpoint === '') {
    $endpoint = '/';
}

// Rotas
switch ($endpoint) {
    case '/btc':
        handleBTCRequests();
        break;
    case '/history':
        handleHistoryRequests();
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found', 'endpoint' => $endpoint]);
        break;
}

// Funções para tratar rotas
function handleBTCRequests() {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            getCurrentBTCPrice();
            break;
        case 'POST':
            saveBTCPrice();
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

function handleHistoryRequests() {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            getPriceHistory();
            break;
        case 'DELETE':
            clearHistory();
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}

// GET /btc - Obtém preço atual do Bitcoin
function getCurrentBTCPrice() {
    try {
        $price = fetchBTCPriceFromAPI();
        echo json_encode([
            'status' => 'success',
            'data' => ['price' => $price],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// POST /btc - Salva manualmente um preço
function saveBTCPrice() {
    global $pdo;

    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    if (!isset($data['price'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Price is required']);
        return;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO prices (price) VALUES (?)");
        $stmt->execute([$data['price']]);

        http_response_code(201);
        echo json_encode([
            'status' => 'success',
            'message' => 'Price saved',
            'id' => $pdo->lastInsertId()
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// GET /history - Lista o histórico de preços
function getPriceHistory() {
    global $pdo;

    try {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        $stmt = $pdo->prepare("SELECT id, timestamp, price FROM prices ORDER BY timestamp DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'count' => count($history),
            'data' => $history
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// DELETE /history - Limpa todos os registros
function clearHistory() {
    global $pdo;

    try {
        $pdo->exec("TRUNCATE TABLE prices");
        
        echo json_encode([
            'status' => 'success',
            'message' => 'History cleared'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Auxiliar - Busca o preço atual do BTC na Binance
function fetchBTCPriceFromAPI() {
    $api_url = 'https://api.binance.com/api/v3/ticker/price?symbol=BTCUSDT';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception('Binance API error: ' . curl_error($ch));
    }
    curl_close($ch);

    $data = json_decode($response, true);
    if (!isset($data['price'])) {
        throw new Exception('Invalid response from Binance API');
    }

    return $data['price'];
}
?>
