<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Ipad.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/BorrowRecord.php';

if (!isLoggedIn()) jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);

$ipadModel   = new Ipad($pdo);
$userModel   = new User($pdo);
$borrowModel = new BorrowRecord($pdo);

$ipadStats = $ipadModel->getStats();
$userStats = $userModel->getStats();
$todayCount  = $borrowModel->getTodayCount();
$overdueList = $borrowModel->getOverdueList();

$dailyStats = $borrowModel->getDailyStats(14);
$dailyLabels = [];
$dailyCounts = [];
foreach ($dailyStats as $d) {
    $dt = new DateTime($d['date']);
    $thMonths = ['','ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    $dailyLabels[] = $dt->format('j') . ' ' . $thMonths[(int)$dt->format('n')];
    $dailyCounts[] = (int)$d['count'];
}

jsonResponse([
    'success' => true,
    'ipad_stats' => $ipadStats,
    'user_stats' => $userStats,
    'today_count' => $todayCount,
    'overdue_count' => count($overdueList),
    'daily_chart' => [
        'labels' => $dailyLabels,
        'data'   => $dailyCounts,
    ],
]);
