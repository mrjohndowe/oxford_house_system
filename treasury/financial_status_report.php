<?php
/**
 * Oxford House Financial Status Report
 * Single-file PHP form with:
 * - MySQL save/update
 * - Auto-save via AJAX
 * - History search by house name + from/to dates
 * - Load prior report into form
 * - Automatic calculations for totals sections
 */

declare(strict_types=1);

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../extras/master_config.php';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

/* =========================
   HELPERS
========================= */
function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function field(array $source, string $key, string $default = ''): string {
    return htmlspecialchars((string)($source[$key] ?? $default), ENT_QUOTES, 'UTF-8');
}

function raw_field(array $source, string $key, string $default = ''): string {
    return (string)($source[$key] ?? $default);
}

function normalize_date(?string $value): ?string {
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }

    $formats = ['Y-m-d', 'm/d/Y', 'n/j/Y', 'm-d-Y', 'n-j-Y'];
    foreach ($formats as $format) {
        $dt = DateTime::createFromFormat($format, $value);
        if ($dt && $dt->format($format) === $value) {
            return $dt->format('Y-m-d');
        }
    }

    $ts = strtotime($value);
    if ($ts !== false) {
        return date('Y-m-d', $ts);
    }

    return null;
}

function money_clean(?string $value): string {
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }
    $value = str_replace([',', '$'], '', $value);
    return preg_replace('/[^0-9.\-]/', '', $value) ?? '';
}

function money_float(?string $value): float {
    $clean = money_clean($value);
    if ($clean === '' || !is_numeric($clean)) {
        return 0.0;
    }
    return (float)$clean;
}

function money_format_db(float $value): string {
    return number_format($value, 2, '.', '');
}

function json_response(array $payload, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/* =========================
   FIELD MAP
========================= */
$fields = [
    'house_name',
    'date_from',
    'date_to',
    'total_received',
    'total_to_be_deposited',
    'total_spent',
    'total_due',
    'sav_begin',
    'sav_deposit',
    'sav_withdraw',
    'sav_interest',
    'sav_end',
    'pc_begin',
    'pc_spent',
    'pc_repl',
    'pc_end',
    'receipts_reviewed',
    'eq_begin_bal',
    'eq_total_received',
    'eq_total_spent',
    'eq_ending_bal',
];

for ($i = 1; $i <= 10; $i++) {
    $fields[] = "mr_date_$i";
    $fields[] = "mr_source_$i";
    $fields[] = "mr_amount_$i";
}

for ($i = 1; $i <= 2; $i++) {
    $fields[] = "td_date_$i";
    $fields[] = "td_source_$i";
    $fields[] = "td_amount_$i";
}

for ($i = 1; $i <= 5; $i++) {
    $fields[] = "ae_date_$i";
    $fields[] = "ae_to_$i";
    $fields[] = "ae_check_$i";
    $fields[] = "ae_amount_$i";
}

for ($i = 1; $i <= 6; $i++) {
    $fields[] = "ub_to_$i";
    $fields[] = "ub_due_$i";
    $fields[] = "ub_amount_$i";
}

/* =========================
   SAVE LOGIC
========================= */
function build_form_data(array $source, array $fields): array {
    $formData = [];
    foreach ($fields as $f) {
        $formData[$f] = trim((string)($source[$f] ?? ''));
    }
    return $formData;
}

function sanitize_and_calculate(array $formData): array {
    $houseName = trim($formData['house_name'] ?? '');
    $dateFrom = normalize_date($formData['date_from'] ?? '');
    $dateTo   = normalize_date($formData['date_to'] ?? '');

    if ($houseName === '') {
        return [
            'ok' => false,
            'message' => 'House name is required.',
            'type' => 'error',
        ];
    }

    if (!$dateFrom || !$dateTo) {
        return [
            'ok' => false,
            'message' => 'Both Dates Covered fields must be valid dates.',
            'type' => 'error',
        ];
    }

    $formData['house_name'] = $houseName;
    $formData['date_from'] = $dateFrom;
    $formData['date_to'] = $dateTo;

    if (!in_array($formData['receipts_reviewed'] ?? '', ['yes', 'no'], true)) {
        $formData['receipts_reviewed'] = '';
    }

    $moneyFields = [
        'total_received',
        'total_to_be_deposited',
        'total_spent',
        'total_due',
        'sav_begin',
        'sav_deposit',
        'sav_withdraw',
        'sav_interest',
        'sav_end',
        'pc_begin',
        'pc_spent',
        'pc_repl',
        'pc_end',
        'eq_begin_bal',
        'eq_total_received',
        'eq_total_spent',
        'eq_ending_bal',
    ];

    foreach ($moneyFields as $mf) {
        $formData[$mf] = money_clean($formData[$mf] ?? '');
    }

    for ($i = 1; $i <= 10; $i++) {
        $formData["mr_amount_$i"] = money_clean($formData["mr_amount_$i"] ?? '');
    }

    for ($i = 1; $i <= 2; $i++) {
        $formData["td_amount_$i"] = money_clean($formData["td_amount_$i"] ?? '');
    }

    for ($i = 1; $i <= 5; $i++) {
        $formData["ae_amount_$i"] = money_clean($formData["ae_amount_$i"] ?? '');
    }

    for ($i = 1; $i <= 6; $i++) {
        $formData["ub_amount_$i"] = money_clean($formData["ub_amount_$i"] ?? '');
    }

    $totalReceived = 0.0;
    for ($i = 1; $i <= 10; $i++) {
        $totalReceived += money_float($formData["mr_amount_$i"]);
    }

    $totalToBeDeposited = 0.0;
    for ($i = 1; $i <= 2; $i++) {
        $totalToBeDeposited += money_float($formData["td_amount_$i"]);
    }

    $totalSpent = 0.0;
    for ($i = 1; $i <= 5; $i++) {
        $totalSpent += money_float($formData["ae_amount_$i"]);
    }

    $totalDue = 0.0;
    for ($i = 1; $i <= 6; $i++) {
        $totalDue += money_float($formData["ub_amount_$i"]);
    }

    $savBegin    = money_float($formData['sav_begin']);
    $savDeposit  = money_float($formData['sav_deposit']);
    $savWithdraw = money_float($formData['sav_withdraw']);
    $savInterest = money_float($formData['sav_interest']);
    $savEnd      = $savBegin + $savDeposit - $savWithdraw + $savInterest;

    $pcBegin = money_float($formData['pc_begin']);
    $pcSpent = money_float($formData['pc_spent']);
    $pcRepl  = money_float($formData['pc_repl']);
    $pcEnd   = $pcBegin - $pcSpent + $pcRepl;

    $eqBeginBal      = money_float($formData['eq_begin_bal']);
    $eqTotalReceived = $totalReceived;
    $eqTotalSpent    = $totalSpent;
    $eqEndingBal     = $eqBeginBal + $eqTotalReceived - $eqTotalSpent;

    $formData['total_received'] = money_format_db($totalReceived);
    $formData['total_to_be_deposited'] = money_format_db($totalToBeDeposited);
    $formData['total_spent'] = money_format_db($totalSpent);
    $formData['total_due'] = money_format_db($totalDue);
    $formData['sav_end'] = money_format_db($savEnd);
    $formData['pc_end'] = money_format_db($pcEnd);
    $formData['eq_total_received'] = money_format_db($eqTotalReceived);
    $formData['eq_total_spent'] = money_format_db($eqTotalSpent);
    $formData['eq_ending_bal'] = money_format_db($eqEndingBal);

    return [
        'ok' => true,
        'formData' => $formData,
        'houseName' => $houseName,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
    ];
}

function save_financial_report(PDO $pdo, array $formData): array {
    $prepared = sanitize_and_calculate($formData);

    if (!$prepared['ok']) {
        return $prepared;
    }

    $houseName = $prepared['houseName'];
    $dateFrom  = $prepared['dateFrom'];
    $dateTo    = $prepared['dateTo'];
    $cleanData = $prepared['formData'];

    $json = json_encode($cleanData, JSON_UNESCAPED_UNICODE);

    try {
        $check = $pdo->prepare("
            SELECT id
            FROM oxford_house_financial_reports
            WHERE house_name = ? AND date_from = ? AND date_to = ?
            LIMIT 1
        ");
        $check->execute([$houseName, $dateFrom, $dateTo]);
        $existing = $check->fetch();

        if ($existing) {
            $update = $pdo->prepare("
                UPDATE oxford_house_financial_reports
                SET report_data = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $update->execute([$json, $existing['id']]);

            return [
                'ok' => true,
                'type' => 'success',
                'message' => 'Report auto-saved.',
                'id' => (int)$existing['id'],
                'formData' => $cleanData,
                'mode' => 'updated',
                'updated_at' => date('Y-m-d H:i:s'),
            ];
        }

        $insert = $pdo->prepare("
            INSERT INTO oxford_house_financial_reports
            (house_name, date_from, date_to, report_data)
            VALUES (?, ?, ?, ?)
        ");
        $insert->execute([$houseName, $dateFrom, $dateTo, $json]);

        return [
            'ok' => true,
            'type' => 'success',
            'message' => 'Report auto-saved.',
            'id' => (int)$pdo->lastInsertId(),
            'formData' => $cleanData,
            'mode' => 'inserted',
            'updated_at' => date('Y-m-d H:i:s'),
        ];
    } catch (PDOException $e) {
        return [
            'ok' => false,
            'type' => 'error',
            'message' => 'Save failed: ' . $e->getMessage(),
        ];
    }
}

/* =========================
   AJAX AUTO SAVE
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string)($_POST['ajax_action'] ?? '') === 'autosave') {
    $formData = build_form_data($_POST, $fields);
    $result = save_financial_report($pdo, $formData);
    json_response($result, $result['ok'] ? 200 : 422);
}

/* =========================
   STATE
========================= */
$message = '';
$messageType = '';
$formData = [];
$historyRows = [];
$loadedId = null;

foreach ($fields as $f) {
    $formData[$f] = '';
}

/* =========================
   LOAD EXISTING RECORD
========================= */
if (isset($_GET['load_id']) && ctype_digit((string)$_GET['load_id'])) {
    $loadedId = (int)$_GET['load_id'];

    $stmt = $pdo->prepare("SELECT * FROM oxford_house_financial_reports WHERE id = ?");
    $stmt->execute([$loadedId]);
    $row = $stmt->fetch();

    if ($row) {
        $stored = json_decode($row['report_data'], true);
        if (!is_array($stored)) {
            $stored = [];
        }

        foreach ($fields as $f) {
            $formData[$f] = (string)($stored[$f] ?? '');
        }

        $formData['house_name'] = (string)$row['house_name'];
        $formData['date_from'] = (string)$row['date_from'];
        $formData['date_to'] = (string)$row['date_to'];

        $message = 'History record loaded.';
        $messageType = 'success';
    } else {
        $message = 'Requested history record was not found.';
        $messageType = 'error';
    }
}

/* =========================
   MANUAL SAVE RECORD
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string)($_POST['ajax_action'] ?? '') !== 'autosave') {
    $formData = build_form_data($_POST, $fields);
    $result = save_financial_report($pdo, $formData);

    if ($result['ok']) {
        $formData = $result['formData'];
        $loadedId = (int)$result['id'];
        $message = ($result['mode'] === 'updated')
            ? 'Report updated successfully.'
            : 'Report saved successfully.';
        $messageType = 'success';
    } else {
        $message = $result['message'];
        $messageType = 'error';
    }
}

/* =========================
   HISTORY SEARCH
========================= */
$historyHouse = trim((string)($_GET['history_house_name'] ?? ''));
$historyFrom  = normalize_date($_GET['history_date_from'] ?? '');
$historyTo    = normalize_date($_GET['history_date_to'] ?? '');

$where = [];
$params = [];

if ($historyHouse !== '') {
    $where[] = 'house_name LIKE ?';
    $params[] = '%' . $historyHouse . '%';
}
if ($historyFrom) {
    $where[] = 'date_from >= ?';
    $params[] = $historyFrom;
}
if ($historyTo) {
    $where[] = 'date_to <= ?';
    $params[] = $historyTo;
}

$sql = "SELECT id, house_name, date_from, date_to, created_at, updated_at
        FROM oxford_house_financial_reports";
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY date_from DESC, house_name ASC LIMIT 50';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$historyRows = $stmt->fetchAll();

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Oxford House Financial Status Report</title>
  <style>
    @page { size: Letter; margin: 0.5in; }
    html, body { height: 100%; }
    body {
      margin: 0;
      background: #f5f5f5;
      font-family: Arial, Helvetica, sans-serif;
      color: #000;
    }
    .wrap {
      max-width: 12.8in;
      margin: 0 auto;
      padding: 16px;
      box-sizing: border-box;
    }
    .topPanels {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      margin-bottom: 16px;
    }
    .panel {
      background: #fff;
      border: 2px solid #000;
      padding: 14px;
      box-sizing: border-box;
    }
    .panelTitle {
      font-size: 18px;
      font-weight: 900;
      letter-spacing: .03em;
      margin: 0 0 10px 0;
    }
    .message {
      padding: 10px 12px;
      margin-bottom: 12px;
      border: 2px solid #000;
      font-weight: 700;
      background: #fff;
    }
    .message.success { border-color: #0a7a28; color: #0a7a28; }
    .message.error { border-color: #b00020; color: #b00020; }

    .historyFormGrid {
      display: grid;
      grid-template-columns: 1.2fr 1fr 1fr auto;
      gap: 8px;
      align-items: end;
    }
    .historyTable {
      width: 100%;
      border-collapse: collapse;
      margin-top: 12px;
      font-size: 13px;
    }
    .historyTable th, .historyTable td {
      border: 1px solid #000;
      padding: 8px 6px;
      text-align: left;
      vertical-align: middle;
    }
    .historyTable th {
      background: #f2f2f2;
      font-weight: 900;
    }

    .page {
      width: 8.5in;
      min-height: 11in;
      margin: 0 auto 0.25in auto;
      background: #fff;
      box-shadow: 0 8px 24px rgba(0,0,0,.15);
      padding: 0.5in;
      box-sizing: border-box;
      position: relative;
    }
    @media print {
      body { background: #fff; }
      .wrap { max-width: none; padding: 0; }
      .topPanels, .message, .no-print { display: none !important; }
      .page { margin: 0; box-shadow: none; }
    }

    .topline {
      display: grid;
      grid-template-columns: 0.9in 1fr;
      column-gap: 0.2in;
      align-items: center;
      margin-bottom: 0.12in;
    }
    .logoBox {
      width: 0.9in;
      height: 0.9in;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .logoBox img {
      max-width: 0.9in;
      max-height: 0.9in;
      display: block;
    }
    .logoFallback {
      width: 0.86in; height: 0.86in;
      border: 3px solid #000;
      border-radius: 6px;
      box-sizing: border-box;
      position: relative;
    }
    .logoFallback:before {
      content: "H";
      font-weight: 900;
      font-size: 0.62in;
      position: absolute;
      left: 50%; top: 45%;
      transform: translate(-50%,-50%);
      line-height: 1;
    }

    .titleBlock { text-align: center; }
    .titleLine1 {
      font-weight: 900;
      letter-spacing: 0.02em;
      font-size: 28px;
      display: inline-block;
    }
    .dash {
      font-weight: 900;
      font-size: 28px;
      padding: 0 8px;
    }
    .houseLineInput {
      display: inline-block;
      width: 3.2in;
      border-bottom: 2px solid #000;
      transform: translateY(-4px);
      padding: 0 6px 2px 6px;
      box-sizing: border-box;
    }
    .houseLineInput input {
      width: 100%;
      border: none;
      outline: none;
      background: transparent;
      font-size: 20px;
      font-weight: 700;
      letter-spacing: 0.02em;
      text-transform: uppercase;
    }
    .titleLine2 {
      margin-top: 6px;
      font-weight: 900;
      letter-spacing: 0.04em;
      font-size: 26px;
    }

    .datesCovered {
      display: grid;
      grid-template-columns: auto 1.5in auto 1.5in;
      justify-content: center;
      align-items: end;
      gap: 10px;
      margin: 0.18in 0 0.18in 0;
      font-size: 14px;
      font-weight: 700;
      letter-spacing: 0.02em;
    }
    .lineInput {
      border-bottom: 2px solid #000;
      height: 22px;
      display: flex;
      align-items: flex-end;
      padding: 0 6px 2px 6px;
      box-sizing: border-box;
    }
    .lineInput input {
      width: 100%;
      border: none;
      outline: none;
      background: transparent;
      font-size: 14px;
      font-weight: 700;
      letter-spacing: 0.03em;
    }

    .grid2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.22in;
    }
    .sectionTitle {
      text-align: center;
      font-weight: 900;
      letter-spacing: 0.04em;
      font-size: 18px;
      margin: 2px 0 6px 0;
    }

    table.formTable {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      font-size: 12px;
    }
    table.formTable th {
      font-weight: 800;
      text-align: center;
      padding: 4px 2px;
      border: 2px solid #000;
      background: #fff;
    }
    table.formTable td {
      border: 2px solid #000;
      height: 26px;
      padding: 0;
      vertical-align: middle;
    }
    .cellInput {
      width: 100%;
      height: 100%;
      border: none;
      outline: none;
      background: transparent;
      padding: 3px 6px;
      box-sizing: border-box;
      font-size: 12px;
    }
    .right { text-align: right; }
    .center { text-align: center; }

    .totalsRow {
      margin-top: 6px;
      display: grid;
      grid-template-columns: 1fr auto 1.3in;
      align-items: center;
      gap: 8px;
      font-weight: 900;
      letter-spacing: 0.02em;
      font-size: 12px;
    }
    .totalsRow .box {
      border: 2px solid #000;
      height: 26px;
      display: flex;
      align-items: center;
      justify-content: flex-end;
      padding: 0 6px;
      box-sizing: border-box;
    }
    .totalsRow input {
      width: 100%;
      border: none;
      outline: none;
      background: transparent;
      font-weight: 800;
      text-align: right;
    }

    .upcomingWrap { margin-top: 0.18in; }
    .upcomingTitle {
      text-align: center;
      font-weight: 900;
      letter-spacing: 0.04em;
      font-size: 18px;
      margin: 0 0 6px 0;
    }

    .lowerGrid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0.22in;
      margin-top: 0.25in;
    }
    .miniTitle {
      text-align: center;
      font-weight: 900;
      letter-spacing: 0.04em;
      font-size: 16px;
      margin: 0 0 8px 0;
    }
    .miniTable {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      font-size: 12px;
    }
    .miniTable td {
      padding: 6px 6px;
      border: none;
    }
    .miniTable .label {
      font-weight: 700;
      width: 55%;
    }
    .miniTable .valueBox {
      border: 2px solid #000;
      height: 24px;
      padding: 0 6px;
      box-sizing: border-box;
    }
    .miniTable input {
      width: 100%;
      height: 100%;
      border: none;
      outline: none;
      background: transparent;
      font-size: 12px;
      font-weight: 700;
      text-align: right;
    }

    .receiptsRow {
      display: grid;
      grid-template-columns: 1fr auto;
      align-items: center;
      gap: 10px;
    }
    .yesNo {
      display: flex;
      gap: 10px;
      align-items: center;
      font-weight: 800;
      letter-spacing: 0.02em;
    }
    .square {
      width: 16px;
      height: 16px;
      border: 2px solid #000;
      border-radius: 3px;
      display: inline-block;
      position: relative;
    }
    .square input {
      appearance: none;
      -webkit-appearance: none;
      width: 16px;
      height: 16px;
      margin: 0;
      position: absolute;
      inset: 0;
      cursor: pointer;
      background: transparent;
    }
    .square input:checked::after {
      content: "";
      position: absolute;
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
      width: 10px;
      height: 10px;
      background: #000;
      border-radius: 2px;
    }

    .equation {
      margin-top: 0.18in;
      display: grid;
      grid-template-columns: 1fr auto 1fr auto 1fr auto 1fr;
      gap: 10px;
      align-items: end;
    }
    .eqBox {
      border: 3px solid #000;
      height: 34px;
      padding: 0 8px;
      box-sizing: border-box;
      display: flex;
      align-items: center;
      justify-content: flex-end;
      font-weight: 900;
      font-size: 16px;
    }
    .eqBox input {
      width: 100%;
      border: none;
      outline: none;
      background: transparent;
      text-align: right;
      font-weight: 900;
      font-size: 16px;
    }
    .eqOp {
      font-weight: 900;
      font-size: 26px;
      text-align: center;
      transform: translateY(-4px);
    }
    .eqLabelRow {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr 1fr;
      gap: 10px;
      margin-top: 6px;
      font-size: 11px;
      font-weight: 700;
      text-align: center;
    }
    .footnote {
      margin-top: 0.18in;
      font-size: 11px;
      font-weight: 700;
      text-align: center;
      letter-spacing: 0.01em;
    }

    .controls {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin-bottom: 12px;
      flex-wrap: wrap;
      align-items: center;
    }
    .btn {
      border: 2px solid #000;
      background: #fff;
      font-weight: 800;
      padding: 8px 12px;
      cursor: pointer;
      text-decoration: none;
      color: #000;
      display: inline-block;
    }
    .smallField {
      width: 100%;
      border: 2px solid #000;
      padding: 8px 10px;
      box-sizing: border-box;
      font-size: 14px;
      background: #fff;
    }
    .calcReadonly {
      background: transparent;
      cursor: default;
    }
    .saveStatus {
      border: 2px solid #000;
      background: #fff;
      padding: 8px 12px;
      font-weight: 800;
      min-width: 220px;
      text-align: center;
    }
    .saveStatus.saving {
      border-color: #9a6700;
      color: #9a6700;
    }
    .saveStatus.saved {
      border-color: #0a7a28;
      color: #0a7a28;
    }
    .saveStatus.error {
      border-color: #b00020;
      color: #b00020;
    }
    .saveStatus.idle {
      color: #333;
    }
  </style>
</head>
<body>
<div class="wrap">

  <?php if ($message !== ''): ?>
    <div class="message <?= h($messageType) ?>">
      <?= h($message) ?>
    </div>
  <?php endif; ?>

  <div class="topPanels no-print">
    <div class="panel">
      <div class="panelTitle">History Search</div>
      <form method="get">
        <div class="historyFormGrid">
          <div>
            <label><strong>House Name</strong></label>
            <input class="smallField" type="text" name="history_house_name" value="<?= h($historyHouse) ?>">
          </div>
          <div>
            <label><strong>From Date</strong></label>
            <input class="smallField" type="date" name="history_date_from" value="<?= h($historyFrom ?? '') ?>">
          </div>
          <div>
            <label><strong>To Date</strong></label>
            <input class="smallField" type="date" name="history_date_to" value="<?= h($historyTo ?? '') ?>">
          </div>
          <div>
            <button class="btn" type="submit">Search</button>
          </div>
        </div>
      </form>

      <table class="historyTable">
        <thead>
          <tr>
            <th>House Name</th>
            <th>From</th>
            <th>To</th>
            <th>Updated</th>
            <th>Load</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$historyRows): ?>
            <tr>
              <td colspan="5">No history records found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($historyRows as $row): ?>
              <tr>
                <td><?= h($row['house_name']) ?></td>
                <td><?= h($row['date_from']) ?></td>
                <td><?= h($row['date_to']) ?></td>
                <td><?= h($row['updated_at']) ?></td>
                <td>
                  <a class="btn" href="?load_id=<?= (int)$row['id'] ?>&history_house_name=<?= urlencode($historyHouse) ?>&history_date_from=<?= urlencode((string)($historyFrom ?? '')) ?>&history_date_to=<?= urlencode((string)($historyTo ?? '')) ?>">Load</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="panel">
      <div class="panelTitle">Instructions</div>
      <p><strong>Auto-save behavior:</strong> The form auto-saves while typing after House Name, From Date, and To Date are filled in.</p>
      <p><strong>Save behavior:</strong> If the same House Name + From Date + To Date already exists, the record is updated instead of duplicated.</p>
      <p><strong>History behavior:</strong> Search uses house name and date range so you can find prior reports quickly.</p>
      <p><strong>Note:</strong> This version stores the full report body as JSON, which avoids creating an oversized table with hundreds of columns.</p>
      <p><strong>Auto-calculation:</strong> Total Received, Total To Be Deposited, Total Spent, Total Due, Savings Ending Balance, Petty Cash Ending Cash, Equation Total Received, Equation Total Spent, and Equation Ending Balance update automatically.</p>
    </div>
  </div>

  <div class="controls no-print">
    <button class="btn" type="button" onclick="window.print()">Print</button>
    <button class="btn" type="button" onclick="clearFinancialForm()">Clear</button>
    <div id="saveStatus" class="saveStatus idle">Auto-save ready</div>
  </div>

  <form id="fsrForm" method="post" autocomplete="off">
    <div class="page">

      <div class="topline">
        <div class="logoBox">
          <?php if (file_exists(__DIR__ . '/../images/oxford_house_logo.png')): ?>
            <img src="../images/oxford_house_logo.png" alt="Oxford House logo">
          <?php else: ?>
            <div class="logoFallback" aria-label="Oxford House placeholder logo"></div>
          <?php endif; ?>
        </div>

        <div class="titleBlock">
          <div class="titleLine1">
            OXFORD HOUSE<span class="dash">-</span>
            <span class="houseLineInput">
              <input name="house_name" value="<?= field($formData, 'house_name') ?>" aria-label="House name">
            </span>
          </div>
          <div class="titleLine2">FINANCIAL STATUS REPORT</div>
        </div>
      </div>

      <div class="datesCovered">
        <div>DATES COVERED:</div>
        <div class="lineInput">
          <input type="date" name="date_from" value="<?= field($formData, 'date_from') ?>" aria-label="Dates covered from">
        </div>
        <div>to</div>
        <div class="lineInput">
          <input type="date" name="date_to" value="<?= field($formData, 'date_to') ?>" aria-label="Dates covered to">
        </div>
      </div>

      <div class="grid2">

        <div>
          <div class="sectionTitle">MONEY RECEIVED</div>
          <table class="formTable">
            <colgroup>
              <col style="width: 18%">
              <col style="width: 57%">
              <col style="width: 25%">
            </colgroup>
            <thead>
              <tr>
                <th>Date</th>
                <th>Source / Purpose</th>
                <th>Amount $</th>
              </tr>
            </thead>
            <tbody>
              <?php for ($i=1; $i<=10; $i++): ?>
                <tr>
                  <td><input class="cellInput center autosave-field" name="mr_date_<?= $i ?>" value="<?= field($formData, "mr_date_$i") ?>"></td>
                  <td><input class="cellInput autosave-field" name="mr_source_<?= $i ?>" value="<?= field($formData, "mr_source_$i") ?>"></td>
                  <td><input class="cellInput right calc-input mr-amount autosave-field" name="mr_amount_<?= $i ?>" value="<?= field($formData, "mr_amount_$i") ?>" inputmode="decimal"></td>
                </tr>
              <?php endfor; ?>
            </tbody>
          </table>
          <div class="totalsRow" style="margin-top: 10px;">
            <div style="text-align:left; font-weight:900;">TOTAL RECEIVED:</div>
            <div>$</div>
            <div class="box"><input class="calcReadonly" name="total_received" id="total_received" value="<?= field($formData, 'total_received') ?>" aria-label="Total received" readonly></div>
          </div>

          <div>
            <div class="sectionTitle" style="margin-top: 0.18in;">TO BE DEPOSITED</div>
            <table class="formTable">
              <colgroup>
                <col style="width: 18%">
                <col style="width: 57%">
                <col style="width: 25%">
              </colgroup>
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Source / Purpose</th>
                  <th>Amount $</th>
                </tr>
              </thead>
              <tbody>
                <?php for ($i=1; $i<=2; $i++): ?>
                  <tr>
                    <td><input class="cellInput center autosave-field" name="td_date_<?= $i ?>" value="<?= field($formData, "td_date_$i") ?>"></td>
                    <td><input class="cellInput autosave-field" name="td_source_<?= $i ?>" value="<?= field($formData, "td_source_$i") ?>"></td>
                    <td><input class="cellInput right calc-input td-amount autosave-field" name="td_amount_<?= $i ?>" value="<?= field($formData, "td_amount_$i") ?>" inputmode="decimal"></td>
                  </tr>
                <?php endfor; ?>
              </tbody>
            </table>
          </div>

          <div class="totalsRow" style="margin-top: 10px;">
            <div style="text-align:left; font-weight:900;">TOTAL TO BE DEPOSITED:</div>
            <div>$</div>
            <div class="box"><input class="calcReadonly" name="total_to_be_deposited" id="total_to_be_deposited" value="<?= field($formData, 'total_to_be_deposited') ?>" aria-label="Total to be deposited" readonly></div>
          </div>
        </div>

        <div>
          <div class="sectionTitle">APPROVED EXPENSES*</div>
          <table class="formTable">
            <colgroup>
              <col style="width: 16%">
              <col style="width: 44%">
              <col style="width: 16%">
              <col style="width: 24%">
            </colgroup>
            <thead>
              <tr>
                <th>Date</th>
                <th>To Whom / Purpose</th>
                <th>Check #</th>
                <th>Amount $</th>
              </tr>
            </thead>
            <tbody>
              <?php for ($i=1; $i<=5; $i++): ?>
                <tr>
                  <td><input class="cellInput center autosave-field" name="ae_date_<?= $i ?>" value="<?= field($formData, "ae_date_$i") ?>"></td>
                  <td><input class="cellInput autosave-field" name="ae_to_<?= $i ?>" value="<?= field($formData, "ae_to_$i") ?>"></td>
                  <td><input class="cellInput center autosave-field" name="ae_check_<?= $i ?>" value="<?= field($formData, "ae_check_$i") ?>"></td>
                  <td><input class="cellInput right calc-input ae-amount autosave-field" name="ae_amount_<?= $i ?>" value="<?= field($formData, "ae_amount_$i") ?>" inputmode="decimal"></td>
                </tr>
              <?php endfor; ?>
            </tbody>
          </table>

          <div class="totalsRow" style="margin-top: 10px;">
            <div style="text-align:left; font-weight:900;">TOTAL SPENT:</div>
            <div>$</div>
            <div class="box"><input class="calcReadonly" name="total_spent" id="total_spent" value="<?= field($formData, 'total_spent') ?>" aria-label="Total spent" readonly></div>
          </div>

          <div class="upcomingWrap">
            <div class="upcomingTitle">UPCOMING BILLS</div>
            <table class="formTable">
              <colgroup>
                <col style="width: 58%">
                <col style="width: 18%">
                <col style="width: 24%">
              </colgroup>
              <thead>
                <tr>
                  <th>To Whom / Purpose</th>
                  <th>Due Date</th>
                  <th>Amount $</th>
                </tr>
              </thead>
              <tbody>
                <?php for ($i=1; $i<=6; $i++): ?>
                  <tr>
                    <td><input class="cellInput autosave-field" name="ub_to_<?= $i ?>" value="<?= field($formData, "ub_to_$i") ?>"></td>
                    <td><input class="cellInput center autosave-field" name="ub_due_<?= $i ?>" value="<?= field($formData, "ub_due_$i") ?>"></td>
                    <td><input class="cellInput right calc-input ub-amount autosave-field" name="ub_amount_<?= $i ?>" value="<?= field($formData, "ub_amount_$i") ?>" inputmode="decimal"></td>
                  </tr>
                <?php endfor; ?>
              </tbody>
            </table>

            <div class="totalsRow" style="margin-top: 10px;">
              <div style="text-align:left; font-weight:900;">TOTAL DUE:</div>
              <div>$</div>
              <div class="box"><input class="calcReadonly" name="total_due" id="total_due" value="<?= field($formData, 'total_due') ?>" aria-label="Total due" readonly></div>
            </div>
          </div>
        </div>

      </div>

      <div class="lowerGrid">
        <div>
          <div class="miniTitle">SAVINGS ACCOUNT</div>
          <table class="miniTable">
            <tr>
              <td class="label">Beginning Balance</td>
              <td class="valueBox"><input class="calc-input autosave-field" name="sav_begin" id="sav_begin" value="<?= field($formData, 'sav_begin') ?>" inputmode="decimal"></td>
            </tr>
            <tr>
              <td class="label">Deposit Amount</td>
              <td class="valueBox"><input class="calc-input autosave-field" name="sav_deposit" id="sav_deposit" value="<?= field($formData, 'sav_deposit') ?>" inputmode="decimal"></td>
            </tr>
            <tr>
              <td class="label">Withdrawal Amount</td>
              <td class="valueBox"><input class="calc-input autosave-field" name="sav_withdraw" id="sav_withdraw" value="<?= field($formData, 'sav_withdraw') ?>" inputmode="decimal"></td>
            </tr>
            <tr>
              <td class="label">Interest Earned</td>
              <td class="valueBox"><input class="calc-input autosave-field" name="sav_interest" id="sav_interest" value="<?= field($formData, 'sav_interest') ?>" inputmode="decimal"></td>
            </tr>
            <tr>
              <td class="label">Ending Balance</td>
              <td class="valueBox"><input class="calcReadonly" name="sav_end" id="sav_end" value="<?= field($formData, 'sav_end') ?>" readonly></td>
            </tr>
          </table>
        </div>

        <div>
          <div class="miniTitle">PETTY CASH</div>
          <table class="miniTable">
            <tr>
              <td class="label">Beginning Cash</td>
              <td class="valueBox"><input class="calc-input autosave-field" name="pc_begin" id="pc_begin" value="<?= field($formData, 'pc_begin') ?>" inputmode="decimal"></td>
            </tr>
            <tr>
              <td class="label">Cash Spent</td>
              <td class="valueBox"><input class="calc-input autosave-field" name="pc_spent" id="pc_spent" value="<?= field($formData, 'pc_spent') ?>" inputmode="decimal"></td>
            </tr>
            <tr>
              <td class="label">Cash Replenished</td>
              <td class="valueBox"><input class="calc-input autosave-field" name="pc_repl" id="pc_repl" value="<?= field($formData, 'pc_repl') ?>" inputmode="decimal"></td>
            </tr>
            <tr>
              <td class="label">Ending Cash</td>
              <td class="valueBox"><input class="calcReadonly" name="pc_end" id="pc_end" value="<?= field($formData, 'pc_end') ?>" readonly></td>
            </tr>
            <tr>
              <td class="label">
                <div class="receiptsRow">
                  <span style="width:190px;">Receipts Reviewed</span>
                  <span class="yesNo">
                    <span>YES</span>
                    <span class="square">
                      <input class="autosave-field" type="radio" name="receipts_reviewed" value="yes" <?= (raw_field($formData, 'receipts_reviewed') === 'yes' ? 'checked' : '') ?>>
                    </span>
                    <span>NO</span>
                    <span class="square">
                      <input class="autosave-field" type="radio" name="receipts_reviewed" value="no" <?= (raw_field($formData, 'receipts_reviewed') === 'no' ? 'checked' : '') ?>>
                    </span>
                  </span>
                </div>
              </td>
              <td></td>
            </tr>
          </table>
        </div>
      </div>

      <div class="equation">
        <div class="eqBox"><input class="calc-input autosave-field" name="eq_begin_bal" id="eq_begin_bal" value="<?= field($formData, 'eq_begin_bal') ?>" aria-label="Beginning balance" inputmode="decimal"></div>
        <div class="eqOp">+</div>
        <div class="eqBox"><input class="calcReadonly" name="eq_total_received" id="eq_total_received" value="<?= field($formData, 'eq_total_received') ?>" aria-label="Total received" readonly></div>
        <div class="eqOp">-</div>
        <div class="eqBox"><input class="calcReadonly" name="eq_total_spent" id="eq_total_spent" value="<?= field($formData, 'eq_total_spent') ?>" aria-label="Total spent" readonly></div>
        <div class="eqOp">=</div>
        <div class="eqBox"><input class="calcReadonly" name="eq_ending_bal" id="eq_ending_bal" value="<?= field($formData, 'eq_ending_bal') ?>" aria-label="Ending balance" readonly></div>
      </div>

      <div class="eqLabelRow">
        <div>Beginning Balance<br><span style="font-weight:700;">(Last week's ending balance)</span></div>
        <div>Total Received</div>
        <div>Total Spent</div>
        <div>Ending Balance</div>
      </div>

      <div class="footnote">
        * Don’t forget to document automatic withdrawals.<br>
        Examples: OHI loan payment, OHI donation, Bank Fee (if applicable)
      </div>

    </div>

    <div class="controls no-print">
      <button class="btn" type="submit">Save to Database</button>
      <span style="font-weight:700;">The same House Name + From + To will update the existing record.</span>
    </div>
  </form>
</div>

<script>
(function () {
  let autoSaveTimer = null;
  let autoSaveInProgress = false;
  let pendingAutoSave = false;
  let lastSavedPayload = '';

  function parseMoney(value) {
    if (value === null || value === undefined) return 0;
    const cleaned = String(value).replace(/[^0-9.\-]/g, '');
    const num = parseFloat(cleaned);
    return isNaN(num) ? 0 : num;
  }

  function formatMoney(value) {
    return Number(value).toFixed(2);
  }

  function sumValues(selector) {
    let total = 0;
    document.querySelectorAll(selector).forEach(function (el) {
      total += parseMoney(el.value);
    });
    return total;
  }

  function setValue(id, value) {
    const el = document.getElementById(id);
    if (el) {
      el.value = formatMoney(value);
    }
  }

  function calculateAll() {
    const totalReceived = sumValues('.mr-amount');
    const totalToBeDeposited = sumValues('.td-amount');
    const totalSpent = sumValues('.ae-amount');
    const totalDue = sumValues('.ub-amount');

    const savBegin = parseMoney(document.getElementById('sav_begin')?.value);
    const savDeposit = parseMoney(document.getElementById('sav_deposit')?.value);
    const savWithdraw = parseMoney(document.getElementById('sav_withdraw')?.value);
    const savInterest = parseMoney(document.getElementById('sav_interest')?.value);
    const savEnd = savBegin + savDeposit - savWithdraw + savInterest;

    const pcBegin = parseMoney(document.getElementById('pc_begin')?.value);
    const pcSpent = parseMoney(document.getElementById('pc_spent')?.value);
    const pcRepl = parseMoney(document.getElementById('pc_repl')?.value);
    const pcEnd = pcBegin - pcSpent + pcRepl;

    const eqBeginBal = parseMoney(document.getElementById('eq_begin_bal')?.value);
    const eqTotalReceived = totalReceived;
    const eqTotalSpent = totalSpent;
    const eqEndingBal = eqBeginBal + eqTotalReceived - eqTotalSpent;

    setValue('total_received', totalReceived);
    setValue('total_to_be_deposited', totalToBeDeposited);
    setValue('total_spent', totalSpent);
    setValue('total_due', totalDue);
    setValue('sav_end', savEnd);
    setValue('pc_end', pcEnd);
    setValue('eq_total_received', eqTotalReceived);
    setValue('eq_total_spent', eqTotalSpent);
    setValue('eq_ending_bal', eqEndingBal);
  }

  function setSaveStatus(message, type) {
    const el = document.getElementById('saveStatus');
    if (!el) return;
    el.textContent = message;
    el.className = 'saveStatus ' + (type || 'idle');
  }

  function hasRequiredFields() {
    const form = document.getElementById('fsrForm');
    if (!form) return false;

    const house = (form.querySelector('[name="house_name"]')?.value || '').trim();
    const from = (form.querySelector('[name="date_from"]')?.value || '').trim();
    const to = (form.querySelector('[name="date_to"]')?.value || '').trim();

    return house !== '' && from !== '' && to !== '';
  }

  function buildPayload() {
    const form = document.getElementById('fsrForm');
    const formData = new FormData(form);
    formData.set('ajax_action', 'autosave');
    return formData;
  }

  function payloadSignature(formData) {
    return new URLSearchParams(formData).toString();
  }

  async function doAutoSave() {
    if (!hasRequiredFields()) {
      setSaveStatus('Enter House Name and both dates to auto-save', 'idle');
      return;
    }

    calculateAll();

    const payload = buildPayload();
    const signature = payloadSignature(payload);

    if (signature === lastSavedPayload) {
      setSaveStatus('All changes saved', 'saved');
      return;
    }

    if (autoSaveInProgress) {
      pendingAutoSave = true;
      return;
    }

    autoSaveInProgress = true;
    setSaveStatus('Saving...', 'saving');

    try {
      const response = await fetch(window.location.href, {
        method: 'POST',
        body: payload,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const result = await response.json();

      if (!response.ok || !result.ok) {
        throw new Error(result.message || 'Auto-save failed.');
      }

      lastSavedPayload = signature;

      if (result.formData) {
        [
          'total_received',
          'total_to_be_deposited',
          'total_spent',
          'total_due',
          'sav_end',
          'pc_end',
          'eq_total_received',
          'eq_total_spent',
          'eq_ending_bal'
        ].forEach(function (name) {
          const el = document.querySelector('[name="' + name + '"]');
          if (el && Object.prototype.hasOwnProperty.call(result.formData, name)) {
            el.value = result.formData[name];
          }
        });
      }

      setSaveStatus('Auto-saved successfully', 'saved');
    } catch (error) {
      setSaveStatus(error.message || 'Auto-save failed', 'error');
    } finally {
      autoSaveInProgress = false;
      if (pendingAutoSave) {
        pendingAutoSave = false;
        scheduleAutoSave();
      }
    }
  }

  function scheduleAutoSave() {
    clearTimeout(autoSaveTimer);
    setSaveStatus('Changes pending...', 'saving');
    autoSaveTimer = setTimeout(doAutoSave, 1200);
  }

  window.clearFinancialForm = function () {
    const form = document.getElementById('fsrForm');
    if (!form) return;
    form.reset();
    lastSavedPayload = '';
    setTimeout(function () {
      calculateAll();
      setSaveStatus('Form cleared', 'idle');
    }, 0);
  };

  document.querySelectorAll('.calc-input').forEach(function (el) {
    el.addEventListener('input', function () {
      calculateAll();
      scheduleAutoSave();
    });
    el.addEventListener('change', function () {
      calculateAll();
      scheduleAutoSave();
    });
  });

  document.querySelectorAll('.autosave-field').forEach(function (el) {
    if (!el.classList.contains('calc-input')) {
      el.addEventListener('input', scheduleAutoSave);
      el.addEventListener('change', scheduleAutoSave);
    }
  });

  document.querySelectorAll('[name="house_name"], [name="date_from"], [name="date_to"]').forEach(function (el) {
    el.addEventListener('input', scheduleAutoSave);
    el.addEventListener('change', scheduleAutoSave);
  });

  document.getElementById('fsrForm')?.addEventListener('submit', function () {
    setSaveStatus('Saving...', 'saving');
  });

  document.addEventListener('DOMContentLoaded', function () {
    calculateAll();
    setSaveStatus('Auto-save ready', 'idle');
  });

  calculateAll();
})();
</script>
</body>
</html>