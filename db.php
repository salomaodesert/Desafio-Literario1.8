<?php
// Configurações do banco de dados
$host = 'localhost';        // Servidor MySQL
$dbname = 'desafio_resumo'; // Nome do banco de dados
$username = 'admin';        // Usuário do banco de dados
$password = 'admin123';     // Senha do banco de dados
$charset = 'utf8mb4';       // Conjunto de caracteres

// String de conexão (DSN)
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// Opções do PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Habilitar exceções para erros
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Padrão para fetch
    PDO::ATTR_EMULATE_PREPARES   => false,                 // Usar prepared statements nativos
];

// Diretório e arquivo de log
$logDir = __DIR__ . '/logs';
$logFile = $logDir . '/error_log.txt';

// Garantir que o diretório de logs existe
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

try {
    // Criando a conexão com o banco de dados
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // Registrar erro em um arquivo de log
    error_log("[" . date('Y-m-d H:i:s') . "] Erro ao conectar: " . $e->getMessage() . PHP_EOL, 3, $logFile);

    // Finalizar execução silenciosamente
    die();
}

/**
 * Função para executar consultas SQL de forma simplificada
 *
 * @param PDO $pdo Instância de conexão PDO
 * @param string $query A consulta SQL
 * @param array $params Os parâmetros para prepared statements (opcional)
 * @return array|false Retorna os resultados da consulta ou false em caso de falha
 */
function executeQuery(PDO $pdo, string $query, array $params = [])
{
    global $logFile;

    try {
        // Preparando e executando a consulta
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        // Retornando os resultados
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Registrar o erro no log sem exibir ao usuário
        error_log("[" . date('Y-m-d H:i:s') . "] Erro na consulta: " . $e->getMessage() . PHP_EOL, 3, $logFile);
        return false;
    }
}

// Exemplo de uso da função executeQuery
$resultados = executeQuery($pdo, "SELECT * FROM tabela_exemplo WHERE id = ?", [1]);
if ($resultados) {
    // Exibe os resultados, caso existam
    print_r($resultados);
}
?>
