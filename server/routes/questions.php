<?php

require_once __DIR__ . '/../controllers/question-controller.php';
require_once __DIR__ . '/../middleware/auth.php';

// khi đặt hàm này ở đầu
// toàn bộ /api/questions chỉ dành cho admin
requireAdmin(); // NOTE: hàm này trong auth

// POST /api/questions - tạo câu hỏi mới
if ($path === '/api/questions' && $method === 'POST') {
    $response = apiCreateQuestion($db_connection);
    http_response_code($response['success'] ? 201 : 400);
    echo json_encode($response);
}
// GET /api/questions - lấy câu hỏi theo test_id
elseif ($path === '/api/questions' && $method === 'GET') {
    $testId = $_GET['test_id'] ?? null;

    if ($testId) {
        $response = apiGetQuestions($db_connection, $testId);
    } else {
        $response = ['success' => false, 'message' => 'Thiếu tham số test_id'];
    }

    http_response_code($response['success'] ? 200 : 400);
    echo json_encode($response);
}
// GET /api/questions/:id - lấy một câu hỏi
elseif (preg_match('/\/api\/questions\/(\d+)$/', $path, $matches) && $method === 'GET') {
    $response = apiGetQuestion($db_connection, $matches[1]);
    http_response_code($response['success'] ? 200 : 404);
    echo json_encode($response);
}
// DELETE /api/questions/:id - xóa câu hỏi
elseif (preg_match('/\/api\/questions\/(\d+)$/', $path, $matches) && $method === 'DELETE') {
    $response = apiDeleteQuestion($db_connection, $matches[1]);
    http_response_code($response['success'] ? 200 : 404);
    echo json_encode($response);
}
// POST /api/questions/import - import nhiều câu hỏi từ JSON
elseif ($path === '/api/questions/import' && $method === 'POST') {
    $response = apiImportQuestions($db_connection);
    http_response_code($response['success'] ? 201 : 400);
    echo json_encode($response);
}