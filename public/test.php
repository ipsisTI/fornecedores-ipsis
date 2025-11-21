<?php
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'PHP está funcionando!',
    'post_data' => $_POST,
    'files' => $_FILES
]);
