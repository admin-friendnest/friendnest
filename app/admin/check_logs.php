<?php
$logs = json_decode(file_get_contents(__DIR__ . '/../data/login_logs.json'), true);
echo json_encode(['count' => count($logs)]);
