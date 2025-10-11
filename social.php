<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/services/UserService.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
  http_response_code(401);
  echo json_encode(['success' => false, 'error' => 'Not authenticated']);
  exit;
}

$action = $_POST['action'] ?? '';
$targetId = (int)($_POST['user_id'] ?? 0);
$me = (int)getCurrentUserId();
$userService = new UserService();

if (!$targetId || $targetId === $me) {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'Invalid target']);
  exit;
}

switch ($action) {
  case 'follow':
    $ok = $userService->followUser($me, $targetId);
    echo json_encode(['success' => (bool)$ok, 'following' => true]);
    break;
  case 'unfollow':
    $ok = $userService->unfollowUser($me, $targetId);
    echo json_encode(['success' => (bool)$ok, 'following' => false]);
    break;
  default:
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
}
