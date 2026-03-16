<?php
/**
 * Oxford House Chapter Financial Audit
 * Single-file PHP/MySQL app
 * - Fillable form closely matching the uploaded Chapter Financial Audit sheet
 * - Auto-save to MySQL
 * - History dropdown by audit date
 * - Reload/edit prior records
 * - Print button
 * - Auto-calculated totals
 * - Signature blocks with drawing pads
 * - Upload scanned financial statement attachment
 */
declare(strict_types=1);

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../../extras/master_config.php';

$logoPath = '../../images/oxford_house_logo.png';
$uploadDir = __DIR__ . '/uploads/chapter_financial_audits';
$uploadWebPath = 'uploads/chapter_financial_audits';

if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0775, true);
}

function h(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function normalize_money(mixed $value): string
{
    $value = trim((string)$value);
    if ($value === '') {
        return '';
    }
    $value = str_replace([',', '$', ' '], '', $value);
    if (!is_numeric($value)) {
        return '';
    }
    return number_format((float)$value, 2, '.', '');
}

function normalize_text(mixed $value): string
{
    return trim((string)$value);
}

function posted_array(string $key, int $count): array
{
    $out = [];
    $source = $_POST[$key] ?? [];
    for ($i = 0; $i < $count; $i++) {
        $out[$i] = isset($source[$i]) ? trim((string)$source[$i]) : '';
    }
    return $out;
}

function decode_json_array(?string $json, int $count): array
{
    if (!$json) {
        return array_fill(0, $count, '');
    }
    $decoded = json_decode($json, true);
    if (!is_array($decoded)) {
        return array_fill(0, $count, '');
    }
    $out = [];
    for ($i = 0; $i < $count; $i++) {
        $out[$i] = isset($decoded[$i]) ? (string)$decoded[$i] : '';
    }
    return $out;
}

function sum_money_array(array $values): float
{
    $sum = 0.0;
    foreach ($values as $value) {
        $clean = normalize_money($value);
        if ($clean !== '') {
            $sum += (float)$clean;
        }
    }
    return $sum;
}

function safe_upload_name(string $name): string
{
    $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name) ?? 'upload';
    return trim($name, '_') !== '' ? $name : 'upload';
}

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
    die('Database connection failed: ' . h($e->getMessage()));
}

$pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS chapter_financial_audits (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    audit_date DATE DEFAULT NULL,
    chapter_number VARCHAR(50) NOT NULL DEFAULT '',
    bank_statement_ending_date DATE DEFAULT NULL,
    bank_statement_ending_balance DECIMAL(12,2) DEFAULT 0.00,
    loans_to_house_names LONGTEXT NULL,
    loans_to_house_amounts LONGTEXT NULL,
    loans_to_house_notes TEXT NULL,
    deposits_check_numbers LONGTEXT NULL,
    deposits_dates LONGTEXT NULL,
    deposits_payees LONGTEXT NULL,
    deposits_amounts LONGTEXT NULL,
    total_deposits DECIMAL(12,2) DEFAULT 0.00,
    amount_loaned DECIMAL(12,2) DEFAULT 0.00,
    monthly_payment DECIMAL(12,2) DEFAULT 0.00,
    total_amount_paid DECIMAL(12,2) DEFAULT 0.00,
    total_amount_due DECIMAL(12,2) DEFAULT 0.00,
    loans_from_state_notes TEXT NULL,
    checks_numbers LONGTEXT NULL,
    checks_dates LONGTEXT NULL,
    checks_payees LONGTEXT NULL,
    checks_amounts LONGTEXT NULL,
    total_checks DECIMAL(12,2) DEFAULT 0.00,
    balance_after_audit DECIMAL(12,2) DEFAULT 0.00,
    treasurer_signature LONGTEXT NULL,
    comptroller_signature LONGTEXT NULL,
    chair_signature LONGTEXT NULL,
    attachment_original_name VARCHAR(255) DEFAULT NULL,
    attachment_stored_name VARCHAR(255) DEFAULT NULL,
    attachment_path VARCHAR(255) DEFAULT NULL,
    attachment_mime VARCHAR(100) DEFAULT NULL,
    attachment_uploaded_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_audit_date_chapter (audit_date, chapter_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

$existingColumns = $pdo->query('SHOW COLUMNS FROM chapter_financial_audits')->fetchAll(PDO::FETCH_COLUMN);
$attachmentColumns = [
    'attachment_original_name' => "ALTER TABLE chapter_financial_audits ADD COLUMN attachment_original_name VARCHAR(255) DEFAULT NULL",
    'attachment_stored_name' => "ALTER TABLE chapter_financial_audits ADD COLUMN attachment_stored_name VARCHAR(255) DEFAULT NULL",
    'attachment_path' => "ALTER TABLE chapter_financial_audits ADD COLUMN attachment_path VARCHAR(255) DEFAULT NULL",
    'attachment_mime' => "ALTER TABLE chapter_financial_audits ADD COLUMN attachment_mime VARCHAR(100) DEFAULT NULL",
    'attachment_uploaded_at' => "ALTER TABLE chapter_financial_audits ADD COLUMN attachment_uploaded_at DATETIME DEFAULT NULL",
];
foreach ($attachmentColumns as $column => $sql) {
    if (!in_array($column, $existingColumns, true)) {
        $pdo->exec($sql);
    }
}

if (($_POST['action'] ?? '') === 'autosave') {
    $recordId = (int)($_POST['record_id'] ?? 0);

    $auditDate = normalize_text($_POST['audit_date'] ?? '');
    $chapterNumber = normalize_text($_POST['chapter_number'] ?? '');
    $bankStatementEndingDate = normalize_text($_POST['bank_statement_ending_date'] ?? '');
    $bankStatementEndingBalance = normalize_money($_POST['bank_statement_ending_balance'] ?? '');

    $loansToHouseNames = posted_array('loans_to_house_names', 5);
    $loansToHouseAmounts = posted_array('loans_to_house_amounts', 5);
    $loansToHouseNotes = normalize_text($_POST['loans_to_house_notes'] ?? '');

    $depositsCheckNumbers = posted_array('deposits_check_numbers', 5);
    $depositsDates = posted_array('deposits_dates', 5);
    $depositsPayees = posted_array('deposits_payees', 5);
    $depositsAmounts = posted_array('deposits_amounts', 5);

    $amountLoaned = normalize_money($_POST['amount_loaned'] ?? '');
    $monthlyPayment = normalize_money($_POST['monthly_payment'] ?? '');
    $totalAmountPaid = normalize_money($_POST['total_amount_paid'] ?? '');
    $totalAmountDue = normalize_money($_POST['total_amount_due'] ?? '');
    $loansFromStateNotes = normalize_text($_POST['loans_from_state_notes'] ?? '');

    $checksNumbers = posted_array('checks_numbers', 5);
    $checksDates = posted_array('checks_dates', 5);
    $checksPayees = posted_array('checks_payees', 5);
    $checksAmounts = posted_array('checks_amounts', 5);

    $treasurerSignature = normalize_text($_POST['treasurer_signature'] ?? '');
    $comptrollerSignature = normalize_text($_POST['comptroller_signature'] ?? '');
    $chairSignature = normalize_text($_POST['chair_signature'] ?? '');

    $attachmentOriginalName = normalize_text($_POST['current_attachment_original_name'] ?? '');
    $attachmentStoredName = normalize_text($_POST['current_attachment_stored_name'] ?? '');
    $attachmentPath = normalize_text($_POST['current_attachment_path'] ?? '');
    $attachmentMime = normalize_text($_POST['current_attachment_mime'] ?? '');
    $attachmentUploadedAt = normalize_text($_POST['current_attachment_uploaded_at'] ?? '');

    if (isset($_FILES['financial_statement_attachment']) && (int)($_FILES['financial_statement_attachment']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['financial_statement_attachment'];
        if ((int)$file['error'] !== UPLOAD_ERR_OK) {
            json_response(['ok' => false, 'message' => 'Attachment upload failed.'], 400);
        }

        $originalName = (string)($file['name'] ?? '');
        $tmpPath = (string)($file['tmp_name'] ?? '');
        $size = (int)($file['size'] ?? 0);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];

        if (!in_array($extension, $allowedExtensions, true)) {
            json_response(['ok' => false, 'message' => 'Only PDF, JPG, JPEG, PNG, and WEBP files are allowed.'], 400);
        }
        if ($size > 20 * 1024 * 1024) {
            json_response(['ok' => false, 'message' => 'Attachment must be 20MB or smaller.'], 400);
        }

        $storedName = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '_' . safe_upload_name($originalName);
        $destination = $uploadDir . '/' . $storedName;
        if (!move_uploaded_file($tmpPath, $destination)) {
            json_response(['ok' => false, 'message' => 'Could not save uploaded attachment.'], 500);
        }

        $attachmentOriginalName = $originalName;
        $attachmentStoredName = $storedName;
        $attachmentPath = $uploadWebPath . '/' . $storedName;
        $attachmentMime = mime_content_type($destination) ?: 'application/octet-stream';
        $attachmentUploadedAt = date('Y-m-d H:i:s');
    }

    $totalDeposits = number_format(sum_money_array($depositsAmounts), 2, '.', '');
    $totalChecks = number_format(sum_money_array($checksAmounts), 2, '.', '');
    $balanceAfterAudit = number_format(((float)($bankStatementEndingBalance !== '' ? $bankStatementEndingBalance : 0)) + (float)$totalDeposits - (float)$totalChecks, 2, '.', '');

    $payload = [
        ':audit_date' => $auditDate !== '' ? $auditDate : null,
        ':chapter_number' => $chapterNumber,
        ':bank_statement_ending_date' => $bankStatementEndingDate !== '' ? $bankStatementEndingDate : null,
        ':bank_statement_ending_balance' => $bankStatementEndingBalance !== '' ? $bankStatementEndingBalance : '0.00',
        ':loans_to_house_names' => json_encode($loansToHouseNames, JSON_UNESCAPED_UNICODE),
        ':loans_to_house_amounts' => json_encode($loansToHouseAmounts, JSON_UNESCAPED_UNICODE),
        ':loans_to_house_notes' => $loansToHouseNotes,
        ':deposits_check_numbers' => json_encode($depositsCheckNumbers, JSON_UNESCAPED_UNICODE),
        ':deposits_dates' => json_encode($depositsDates, JSON_UNESCAPED_UNICODE),
        ':deposits_payees' => json_encode($depositsPayees, JSON_UNESCAPED_UNICODE),
        ':deposits_amounts' => json_encode($depositsAmounts, JSON_UNESCAPED_UNICODE),
        ':total_deposits' => $totalDeposits,
        ':amount_loaned' => $amountLoaned !== '' ? $amountLoaned : '0.00',
        ':monthly_payment' => $monthlyPayment !== '' ? $monthlyPayment : '0.00',
        ':total_amount_paid' => $totalAmountPaid !== '' ? $totalAmountPaid : '0.00',
        ':total_amount_due' => $totalAmountDue !== '' ? $totalAmountDue : '0.00',
        ':loans_from_state_notes' => $loansFromStateNotes,
        ':checks_numbers' => json_encode($checksNumbers, JSON_UNESCAPED_UNICODE),
        ':checks_dates' => json_encode($checksDates, JSON_UNESCAPED_UNICODE),
        ':checks_payees' => json_encode($checksPayees, JSON_UNESCAPED_UNICODE),
        ':checks_amounts' => json_encode($checksAmounts, JSON_UNESCAPED_UNICODE),
        ':total_checks' => $totalChecks,
        ':balance_after_audit' => $balanceAfterAudit,
        ':treasurer_signature' => $treasurerSignature,
        ':comptroller_signature' => $comptrollerSignature,
        ':chair_signature' => $chairSignature,
        ':attachment_original_name' => $attachmentOriginalName !== '' ? $attachmentOriginalName : null,
        ':attachment_stored_name' => $attachmentStoredName !== '' ? $attachmentStoredName : null,
        ':attachment_path' => $attachmentPath !== '' ? $attachmentPath : null,
        ':attachment_mime' => $attachmentMime !== '' ? $attachmentMime : null,
        ':attachment_uploaded_at' => $attachmentUploadedAt !== '' ? $attachmentUploadedAt : null,
    ];

    if ($recordId > 0) {
        $payload[':id'] = $recordId;
        $stmt = $pdo->prepare(<<<SQL
UPDATE chapter_financial_audits SET
    audit_date = :audit_date,
    chapter_number = :chapter_number,
    bank_statement_ending_date = :bank_statement_ending_date,
    bank_statement_ending_balance = :bank_statement_ending_balance,
    loans_to_house_names = :loans_to_house_names,
    loans_to_house_amounts = :loans_to_house_amounts,
    loans_to_house_notes = :loans_to_house_notes,
    deposits_check_numbers = :deposits_check_numbers,
    deposits_dates = :deposits_dates,
    deposits_payees = :deposits_payees,
    deposits_amounts = :deposits_amounts,
    total_deposits = :total_deposits,
    amount_loaned = :amount_loaned,
    monthly_payment = :monthly_payment,
    total_amount_paid = :total_amount_paid,
    total_amount_due = :total_amount_due,
    loans_from_state_notes = :loans_from_state_notes,
    checks_numbers = :checks_numbers,
    checks_dates = :checks_dates,
    checks_payees = :checks_payees,
    checks_amounts = :checks_amounts,
    total_checks = :total_checks,
    balance_after_audit = :balance_after_audit,
    treasurer_signature = :treasurer_signature,
    comptroller_signature = :comptroller_signature,
    chair_signature = :chair_signature,
    attachment_original_name = :attachment_original_name,
    attachment_stored_name = :attachment_stored_name,
    attachment_path = :attachment_path,
    attachment_mime = :attachment_mime,
    attachment_uploaded_at = :attachment_uploaded_at
WHERE id = :id
SQL);
        $stmt->execute($payload);
        json_response([
            'ok' => true,
            'record_id' => $recordId,
            'total_deposits' => $totalDeposits,
            'total_checks' => $totalChecks,
            'balance_after_audit' => $balanceAfterAudit,
            'attachment_original_name' => $attachmentOriginalName,
            'attachment_path' => $attachmentPath,
        ]);
    }

    $stmt = $pdo->prepare(<<<SQL
INSERT INTO chapter_financial_audits (
    audit_date, chapter_number, bank_statement_ending_date, bank_statement_ending_balance,
    loans_to_house_names, loans_to_house_amounts, loans_to_house_notes,
    deposits_check_numbers, deposits_dates, deposits_payees, deposits_amounts, total_deposits,
    amount_loaned, monthly_payment, total_amount_paid, total_amount_due, loans_from_state_notes,
    checks_numbers, checks_dates, checks_payees, checks_amounts, total_checks,
    balance_after_audit, treasurer_signature, comptroller_signature, chair_signature,
    attachment_original_name, attachment_stored_name, attachment_path, attachment_mime, attachment_uploaded_at
) VALUES (
    :audit_date, :chapter_number, :bank_statement_ending_date, :bank_statement_ending_balance,
    :loans_to_house_names, :loans_to_house_amounts, :loans_to_house_notes,
    :deposits_check_numbers, :deposits_dates, :deposits_payees, :deposits_amounts, :total_deposits,
    :amount_loaned, :monthly_payment, :total_amount_paid, :total_amount_due, :loans_from_state_notes,
    :checks_numbers, :checks_dates, :checks_payees, :checks_amounts, :total_checks,
    :balance_after_audit, :treasurer_signature, :comptroller_signature, :chair_signature,
    :attachment_original_name, :attachment_stored_name, :attachment_path, :attachment_mime, :attachment_uploaded_at
)
SQL);
    $stmt->execute($payload);
    json_response([
        'ok' => true,
        'record_id' => (int)$pdo->lastInsertId(),
        'total_deposits' => $totalDeposits,
        'total_checks' => $totalChecks,
        'balance_after_audit' => $balanceAfterAudit,
        'attachment_original_name' => $attachmentOriginalName,
        'attachment_path' => $attachmentPath,
    ]);
}

$historyRows = $pdo->query('SELECT id, audit_date, chapter_number FROM chapter_financial_audits ORDER BY audit_date DESC, id DESC')->fetchAll();

$form = [
    'id' => 0,
    'audit_date' => '',
    'chapter_number' => '',
    'bank_statement_ending_date' => '',
    'bank_statement_ending_balance' => '',
    'loans_to_house_names' => array_fill(0, 5, ''),
    'loans_to_house_amounts' => array_fill(0, 5, ''),
    'loans_to_house_notes' => '',
    'deposits_check_numbers' => array_fill(0, 5, ''),
    'deposits_dates' => array_fill(0, 5, ''),
    'deposits_payees' => array_fill(0, 5, ''),
    'deposits_amounts' => array_fill(0, 5, ''),
    'total_deposits' => '0.00',
    'amount_loaned' => '',
    'monthly_payment' => '',
    'total_amount_paid' => '',
    'total_amount_due' => '',
    'loans_from_state_notes' => '',
    'checks_numbers' => array_fill(0, 5, ''),
    'checks_dates' => array_fill(0, 5, ''),
    'checks_payees' => array_fill(0, 5, ''),
    'checks_amounts' => array_fill(0, 5, ''),
    'total_checks' => '0.00',
    'balance_after_audit' => '0.00',
    'treasurer_signature' => '',
    'comptroller_signature' => '',
    'chair_signature' => '',
    'attachment_original_name' => '',
    'attachment_stored_name' => '',
    'attachment_path' => '',
    'attachment_mime' => '',
    'attachment_uploaded_at' => '',
];

if (isset($_GET['id']) && ctype_digit((string)$_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM chapter_financial_audits WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_GET['id']]);
    $row = $stmt->fetch();
    if ($row) {
        $form['id'] = (int)$row['id'];
        $form['audit_date'] = (string)($row['audit_date'] ?? '');
        $form['chapter_number'] = (string)($row['chapter_number'] ?? '');
        $form['bank_statement_ending_date'] = (string)($row['bank_statement_ending_date'] ?? '');
        $form['bank_statement_ending_balance'] = number_format((float)($row['bank_statement_ending_balance'] ?? 0), 2, '.', '');
        $form['loans_to_house_names'] = decode_json_array($row['loans_to_house_names'] ?? null, 5);
        $form['loans_to_house_amounts'] = decode_json_array($row['loans_to_house_amounts'] ?? null, 5);
        $form['loans_to_house_notes'] = (string)($row['loans_to_house_notes'] ?? '');
        $form['deposits_check_numbers'] = decode_json_array($row['deposits_check_numbers'] ?? null, 5);
        $form['deposits_dates'] = decode_json_array($row['deposits_dates'] ?? null, 5);
        $form['deposits_payees'] = decode_json_array($row['deposits_payees'] ?? null, 5);
        $form['deposits_amounts'] = decode_json_array($row['deposits_amounts'] ?? null, 5);
        $form['total_deposits'] = number_format((float)($row['total_deposits'] ?? 0), 2, '.', '');
        $form['amount_loaned'] = number_format((float)($row['amount_loaned'] ?? 0), 2, '.', '');
        $form['monthly_payment'] = number_format((float)($row['monthly_payment'] ?? 0), 2, '.', '');
        $form['total_amount_paid'] = number_format((float)($row['total_amount_paid'] ?? 0), 2, '.', '');
        $form['total_amount_due'] = number_format((float)($row['total_amount_due'] ?? 0), 2, '.', '');
        $form['loans_from_state_notes'] = (string)($row['loans_from_state_notes'] ?? '');
        $form['checks_numbers'] = decode_json_array($row['checks_numbers'] ?? null, 5);
        $form['checks_dates'] = decode_json_array($row['checks_dates'] ?? null, 5);
        $form['checks_payees'] = decode_json_array($row['checks_payees'] ?? null, 5);
        $form['checks_amounts'] = decode_json_array($row['checks_amounts'] ?? null, 5);
        $form['total_checks'] = number_format((float)($row['total_checks'] ?? 0), 2, '.', '');
        $form['balance_after_audit'] = number_format((float)($row['balance_after_audit'] ?? 0), 2, '.', '');
        $form['treasurer_signature'] = (string)($row['treasurer_signature'] ?? '');
        $form['comptroller_signature'] = (string)($row['comptroller_signature'] ?? '');
        $form['chair_signature'] = (string)($row['chair_signature'] ?? '');
        $form['attachment_original_name'] = (string)($row['attachment_original_name'] ?? '');
        $form['attachment_stored_name'] = (string)($row['attachment_stored_name'] ?? '');
        $form['attachment_path'] = (string)($row['attachment_path'] ?? '');
        $form['attachment_mime'] = (string)($row['attachment_mime'] ?? '');
        $form['attachment_uploaded_at'] = (string)($row['attachment_uploaded_at'] ?? '');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chapter Financial Audit</title>
<style>
:root { --page-width: 8.5in; --ink:#111; --border:#555; }
* { box-sizing: border-box; }
body { margin:0; padding:18px; background:#d8d8d8; color:var(--ink); font-family:Arial, Helvetica, sans-serif; }
.toolbar { width:var(--page-width); margin:0 auto 12px; display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap; }
.toolbar-left, .toolbar-right { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.toolbar select, .toolbar button, .toolbar a { height:36px; padding:0 12px; border:1px solid #999; border-radius:6px; background:#fff; font-size:14px; text-decoration:none; color:#111; display:inline-flex; align-items:center; }
.page { width:var(--page-width); background:#fff; margin:0 auto; padding:.38in .42in .35in; box-shadow:0 0 0 1px #bbb, 0 10px 30px rgba(0,0,0,.08); }
.top { display:grid; grid-template-columns:1.35in 1fr; gap:14px; }
.logo-box img { max-width:1.15in; max-height:1.15in; object-fit:contain; }
.title { text-align:center; font-size:21px; font-weight:700; margin:2px 0 10px; }
.meta-row { display:flex; justify-content:center; gap:30px; margin-bottom:10px; font-size:14px; font-weight:700; }
.line-input { min-width:145px; border:0; border-bottom:1px solid #111; font-size:15px; font-weight:700; padding:1px 4px; background:transparent; outline:none; }
.line-input.short { min-width:90px; }
.instructions { margin:0 0 12px; padding-left:18px; font-size:13px; line-height:1.45; font-weight:600; }
.statement-row { display:grid; grid-template-columns:1fr auto; gap:12px; margin:7px 0; }
.statement-label { font-size:15px; font-weight:800; }
.boxed-input { min-width:165px; border:2px solid #666; padding:3px 6px; font-size:15px; font-weight:700; background:#fff; height:34px; }
.grid-two, .grid-bottom { display:grid; grid-template-columns:1fr 1.55fr; gap:18px; margin-top:14px; }
.grid-bottom { margin-top:18px; }
.section-title { text-align:center; font-size:14px; font-weight:800; margin:0 0 3px; text-transform:uppercase; }
table.form-table { width:100%; border-collapse:collapse; table-layout:fixed; font-size:12px; }
.form-table th, .form-table td { border:1px solid var(--border); padding:0; }
.form-table th { text-align:center; padding:2px 3px; }
.cell-input { width:100%; height:25px; border:0; padding:2px 5px; font-size:12px; outline:none; background:transparent; }
.cell-input.center { text-align:center; }
.cell-input.right { text-align:right; }
.notes-box { border:1px solid var(--border); border-top:0; min-height:78px; display:grid; grid-template-columns:58px 1fr; }
.notes-label { border-right:1px solid var(--border); padding:7px 8px; font-size:12px; font-weight:700; }
.notes-box textarea { width:100%; min-height:77px; border:0; resize:none; padding:6px 8px; font-size:12px; outline:none; font-family:Arial, Helvetica, sans-serif; }
.total-inline { margin-top:4px; margin-left:auto; width:62%; display:grid; grid-template-columns:1fr 100px; border:1px solid var(--border); font-size:12px; font-weight:700; }
.total-inline .label { padding:3px 8px; text-align:right; }
.total-inline .value { border-left:1px solid var(--border); padding:3px 8px; background:#f8f8f8; }
.money-prefix { display:grid; grid-template-columns:26px 1fr; height:25px; }
.money-prefix span { border-right:1px solid var(--border); text-align:center; font-weight:700; font-size:12px; display:flex; align-items:center; justify-content:center; }
.money-prefix input { border:0; width:100%; height:100%; padding:2px 5px; font-size:12px; outline:none; }
.equation { display:grid; grid-template-columns:1fr 26px 1fr 26px 1fr 26px 1fr; gap:10px; align-items:start; margin:18px 0 8px; }
.eq-box { text-align:center; }
.eq-value { border:2px solid #666; height:50px; display:flex; align-items:center; padding:4px 8px; font-size:18px; font-weight:700; background:#fff; }
.eq-value input { width:100%; border:0; outline:none; font-size:18px; font-weight:700; background:transparent; }
.eq-label { font-size:12px; line-height:1.25; margin-top:4px; font-weight:700; }
.eq-symbol { font-size:36px; font-weight:700; text-align:center; padding-top:2px; }
.signatures { display:grid; grid-template-columns:1fr 1fr 1fr; gap:28px; margin-top:6px; }
.sig-box { text-align:center; }
.sig-pad { width:100%; height:80px; background:#fff; display:block; touch-action:none; }
.sig-line { border-top:1px solid #333; padding-top:3px; font-size:11px; font-weight:700; }
.sig-actions { margin-top:4px; }
.sig-actions button { height:24px; padding:0 10px; font-size:11px; border:1px solid #999; background:#fff; border-radius:4px; }
.upload-section { margin-top:18px; border:1px solid var(--border); padding:12px; }
.upload-title { font-size:14px; font-weight:800; text-transform:uppercase; margin-bottom:8px; text-align:center; }
.upload-row { display:flex; justify-content:space-between; align-items:center; gap:14px; flex-wrap:wrap; }
.attachment-meta { font-size:12px; line-height:1.4; }
.attachment-meta a { color:#0b57d0; text-decoration:none; font-weight:700; }
.footer-note { margin-top:18px; text-align:center; font-size:13px; font-weight:700; line-height:1.45; }
@media print { body { background:#fff; padding:0; } .toolbar, .upload-section, .sig-actions { display:none !important; } .page { width:100%; margin:0; box-shadow:none; } }
</style>
</head>
<body>
<div class="toolbar">
<div class="toolbar-left">
<label for="history_select"><strong>History:</strong></label>
<select id="history_select">
<option value="">Select saved record by date</option>
<?php foreach ($historyRows as $row): ?>
<option value="<?= (int)$row['id'] ?>" <?= ((int)$form['id'] === (int)$row['id']) ? 'selected' : '' ?>><?= h(($row['audit_date'] ?: 'No Date') . ' - Chapter ' . ($row['chapter_number'] !== '' ? $row['chapter_number'] : 'N/A')) ?></option>
<?php endforeach; ?>
</select>
<span id="saveStatus">Ready</span>
</div>
<div class="toolbar-right">
<?php if ($form['attachment_path'] !== ''): ?><a href="<?= h($form['attachment_path']) ?>" target="_blank">Open Attachment</a><?php endif; ?>
<button type="button" id="saveNowBtn">Save Now</button>
<button type="button" onclick="window.print()">Print</button>
</div>
</div>

<form id="auditForm" class="page" method="post" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="action" value="autosave">
<input type="hidden" name="record_id" id="record_id" value="<?= (int)$form['id'] ?>">
<input type="hidden" name="treasurer_signature" id="treasurer_signature" value="<?= h($form['treasurer_signature']) ?>">
<input type="hidden" name="comptroller_signature" id="comptroller_signature" value="<?= h($form['comptroller_signature']) ?>">
<input type="hidden" name="chair_signature" id="chair_signature" value="<?= h($form['chair_signature']) ?>">
<input type="hidden" name="current_attachment_original_name" id="current_attachment_original_name" value="<?= h($form['attachment_original_name']) ?>">
<input type="hidden" name="current_attachment_stored_name" id="current_attachment_stored_name" value="<?= h($form['attachment_stored_name']) ?>">
<input type="hidden" name="current_attachment_path" id="current_attachment_path" value="<?= h($form['attachment_path']) ?>">
<input type="hidden" name="current_attachment_mime" id="current_attachment_mime" value="<?= h($form['attachment_mime']) ?>">
<input type="hidden" name="current_attachment_uploaded_at" id="current_attachment_uploaded_at" value="<?= h($form['attachment_uploaded_at']) ?>">

<div class="top">
<div class="logo-box"><img src="<?= h($logoPath) ?>" alt="Oxford House Logo"></div>
<div class="heading-area">
<div class="title">Oxford House Chapter Financial Audit</div>
<div class="meta-row">
<div>Date : <input class="line-input" type="date" name="audit_date" value="<?= h($form['audit_date']) ?>"></div>
<div>Chapter # <input class="line-input short" type="text" name="chapter_number" value="<?= h($form['chapter_number']) ?>"></div>
</div>
<ol class="instructions">
<li>The Treasurer, Comptroller &amp; Chapter Chair do the audit together.</li>
<li>Use the bank statement, checkbook, and meeting minutes for references.</li>
<li>Document all deposits and checks that are not listed on the most recent bank statement.</li>
<li>Count the checks in the checkbook to ensure there are no missing checks.</li>
<li>The final audited balance should match the ending balance on the checkbook register.</li>
<li>If fraud, theft, or embezzlement has occurred, immediately notify OHI staff and the bank.</li>
</ol>
</div>
</div>

<div class="statement-row"><div class="statement-label">BANK STATEMENT ENDING DATE</div><div><input class="line-input" type="date" name="bank_statement_ending_date" value="<?= h($form['bank_statement_ending_date']) ?>"></div></div>
<div class="statement-row"><div class="statement-label">BANK STATEMENT ENDING BALANCE</div><div><input class="boxed-input" type="text" inputmode="decimal" name="bank_statement_ending_balance" id="bank_statement_ending_balance" value="<?= h($form['bank_statement_ending_balance']) ?>"></div></div>

<div class="grid-two">
<div>
<div class="section-title">Loans to Houses</div>
<table class="form-table"><thead><tr><th>House Name</th><th>Amount</th></tr></thead><tbody>
<?php for ($i=0; $i<5; $i++): ?>
<tr><td><input class="cell-input" type="text" name="loans_to_house_names[]" value="<?= h($form['loans_to_house_names'][$i]) ?>"></td><td><input class="cell-input right" type="text" inputmode="decimal" name="loans_to_house_amounts[]" value="<?= h($form['loans_to_house_amounts'][$i]) ?>"></td></tr>
<?php endfor; ?>
</tbody></table>
<div class="notes-box"><div class="notes-label">Notes:</div><textarea name="loans_to_house_notes"><?= h($form['loans_to_house_notes']) ?></textarea></div>
</div>
<div>
<div class="section-title">Deposits Not on the Statement</div>
<table class="form-table"><thead><tr><th>Check #</th><th>Date</th><th>To Whom/Purpose</th><th>Amount</th></tr></thead><tbody>
<?php for ($i=0; $i<5; $i++): ?>
<tr><td><input class="cell-input center" type="text" name="deposits_check_numbers[]" value="<?= h($form['deposits_check_numbers'][$i]) ?>"></td><td><input class="cell-input center" type="text" name="deposits_dates[]" value="<?= h($form['deposits_dates'][$i]) ?>"></td><td><input class="cell-input" type="text" name="deposits_payees[]" value="<?= h($form['deposits_payees'][$i]) ?>"></td><td><input class="cell-input right deposit-amount" type="text" inputmode="decimal" name="deposits_amounts[]" value="<?= h($form['deposits_amounts'][$i]) ?>"></td></tr>
<?php endfor; ?>
</tbody></table>
<div class="total-inline"><div class="label">Total Deposits</div><div class="value">$ <span id="total_deposits_display"><?= h($form['total_deposits']) ?></span></div></div>
</div>
</div>

<div class="grid-bottom">
<div>
<div class="section-title">Loans from State</div>
<table class="form-table"><tbody>
<tr><td>Amount Loaned</td><td><div class="money-prefix"><span>$</span><input type="text" inputmode="decimal" name="amount_loaned" value="<?= h($form['amount_loaned']) ?>"></div></td></tr>
<tr><td>Monthly Payment</td><td><div class="money-prefix"><span>$</span><input type="text" inputmode="decimal" name="monthly_payment" value="<?= h($form['monthly_payment']) ?>"></div></td></tr>
<tr><td>Total Amount Paid</td><td><div class="money-prefix"><span>$</span><input type="text" inputmode="decimal" name="total_amount_paid" value="<?= h($form['total_amount_paid']) ?>"></div></td></tr>
<tr><td>Total Amount Due</td><td><div class="money-prefix"><span>$</span><input type="text" inputmode="decimal" name="total_amount_due" value="<?= h($form['total_amount_due']) ?>"></div></td></tr>
</tbody></table>
<div class="notes-box"><div class="notes-label">Notes:</div><textarea name="loans_from_state_notes"><?= h($form['loans_from_state_notes']) ?></textarea></div>
</div>
<div>
<div class="section-title">Checks Not on the Statement</div>
<table class="form-table"><thead><tr><th>Check #</th><th>Date</th><th>To Whom/Purpose</th><th>Amount</th></tr></thead><tbody>
<?php for ($i=0; $i<5; $i++): ?>
<tr><td><input class="cell-input center" type="text" name="checks_numbers[]" value="<?= h($form['checks_numbers'][$i]) ?>"></td><td><input class="cell-input center" type="text" name="checks_dates[]" value="<?= h($form['checks_dates'][$i]) ?>"></td><td><input class="cell-input" type="text" name="checks_payees[]" value="<?= h($form['checks_payees'][$i]) ?>"></td><td><input class="cell-input right check-amount" type="text" inputmode="decimal" name="checks_amounts[]" value="<?= h($form['checks_amounts'][$i]) ?>"></td></tr>
<?php endfor; ?>
</tbody></table>
<div class="total-inline"><div class="label">Total Checks</div><div class="value">$ <span id="total_checks_display"><?= h($form['total_checks']) ?></span></div></div>
</div>
</div>

<div class="equation">
<div class="eq-box"><div class="eq-value">$ <input type="text" id="ending_balance_display" value="<?= h($form['bank_statement_ending_balance']) ?>" readonly></div><div class="eq-label">Bank Statement<br>Ending Balance</div></div>
<div class="eq-symbol">+</div>
<div class="eq-box"><div class="eq-value">$ <input type="text" id="equation_deposits_display" value="<?= h($form['total_deposits']) ?>" readonly></div><div class="eq-label">Deposits Total</div></div>
<div class="eq-symbol">-</div>
<div class="eq-box"><div class="eq-value">$ <input type="text" id="equation_checks_display" value="<?= h($form['total_checks']) ?>" readonly></div><div class="eq-label">Checks Total</div></div>
<div class="eq-symbol">=</div>
<div class="eq-box"><div class="eq-value">$ <input type="text" name="balance_after_audit" id="balance_after_audit" value="<?= h($form['balance_after_audit']) ?>" readonly></div><div class="eq-label">Balance After<br>Audit</div></div>
</div>

<div class="signatures">
<div class="sig-box"><canvas class="sig-pad" id="treasurer_pad" width="220" height="80"></canvas><div class="sig-line">Treasurer Signature</div><div class="sig-actions"><button type="button" data-clear="treasurer">Clear</button></div></div>
<div class="sig-box"><canvas class="sig-pad" id="comptroller_pad" width="220" height="80"></canvas><div class="sig-line">Comptroller Signature</div><div class="sig-actions"><button type="button" data-clear="comptroller">Clear</button></div></div>
<div class="sig-box"><canvas class="sig-pad" id="chair_pad" width="220" height="80"></canvas><div class="sig-line">Chair Signature</div><div class="sig-actions"><button type="button" data-clear="chair">Clear</button></div></div>
</div>

<div class="upload-section">
<div class="upload-title">Scanned Financial Statement Attachment</div>
<div class="upload-row">
<div><input type="file" name="financial_statement_attachment" id="financial_statement_attachment" accept=".pdf,.jpg,.jpeg,.png,.webp"><div class="attachment-meta">Accepted: PDF, JPG, JPEG, PNG, WEBP. Max 20MB.</div></div>
<div class="attachment-meta" id="attachmentInfo"><?php if ($form['attachment_path'] !== ''): ?>Current file: <a href="<?= h($form['attachment_path']) ?>" target="_blank"><?= h($form['attachment_original_name']) ?></a><br>Uploaded: <?= h($form['attachment_uploaded_at']) ?><?php else: ?>No scanned financial statement attached.<?php endif; ?></div>
</div>
</div>

<div class="footer-note">Financial audit must be completed every month and a copy of the<br>financial audit and bank statement must be set to the State Association</div>
</form>

<script>
const form = document.getElementById('auditForm');
const historySelect = document.getElementById('history_select');
const recordIdInput = document.getElementById('record_id');
const saveNowBtn = document.getElementById('saveNowBtn');
const saveStatus = document.getElementById('saveStatus');
const attachmentInput = document.getElementById('financial_statement_attachment');
const attachmentInfo = document.getElementById('attachmentInfo');
let autosaveTimer = null;
let saving = false;

function cleanMoney(value) { value = String(value || '').replace(/[$,\s]/g, ''); const num = parseFloat(value); return Number.isFinite(num) ? num : 0; }
function calculateTotals() {
  const ending = cleanMoney(document.getElementById('bank_statement_ending_balance').value);
  let deposits = 0; document.querySelectorAll('.deposit-amount').forEach(i => deposits += cleanMoney(i.value));
  let checks = 0; document.querySelectorAll('.check-amount').forEach(i => checks += cleanMoney(i.value));
  const balance = ending + deposits - checks;
  document.getElementById('total_deposits_display').textContent = deposits.toFixed(2);
  document.getElementById('total_checks_display').textContent = checks.toFixed(2);
  document.getElementById('ending_balance_display').value = ending.toFixed(2);
  document.getElementById('equation_deposits_display').value = deposits.toFixed(2);
  document.getElementById('equation_checks_display').value = checks.toFixed(2);
  document.getElementById('balance_after_audit').value = balance.toFixed(2);
}
function setStatus(text) { saveStatus.textContent = text; }
function scheduleAutosave() { clearTimeout(autosaveTimer); setStatus('Unsaved changes'); autosaveTimer = setTimeout(() => saveForm(false), 900); }
function escapeHtml(value) { return String(value).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c])); }
async function saveForm(manual) {
  if (saving) return;
  saving = true;
  calculateTotals();
  setStatus(manual ? 'Saving...' : 'Auto-saving...');
  try {
    const response = await fetch(window.location.pathname, { method:'POST', body:new FormData(form) });
    const data = await response.json();
    if (!data.ok) throw new Error(data.message || 'Save failed');
    recordIdInput.value = data.record_id;
    document.getElementById('total_deposits_display').textContent = data.total_deposits;
    document.getElementById('total_checks_display').textContent = data.total_checks;
    document.getElementById('equation_deposits_display').value = data.total_deposits;
    document.getElementById('equation_checks_display').value = data.total_checks;
    document.getElementById('balance_after_audit').value = data.balance_after_audit;
    if (data.attachment_original_name && data.attachment_path) {
      document.getElementById('current_attachment_original_name').value = data.attachment_original_name;
      document.getElementById('current_attachment_path').value = data.attachment_path;
      attachmentInfo.innerHTML = 'Current file: <a href="' + escapeHtml(data.attachment_path) + '" target="_blank">' + escapeHtml(data.attachment_original_name) + '</a>';
      attachmentInput.value = '';
    }
    setStatus(manual ? 'Saved' : 'Auto-saved');
  } catch (e) {
    console.error(e);
    setStatus('Save failed');
  } finally {
    saving = false;
  }
}
form.querySelectorAll('input, textarea').forEach(el => {
  if (el.type === 'hidden' || el.type === 'file') return;
  el.addEventListener('input', () => { calculateTotals(); scheduleAutosave(); });
  el.addEventListener('change', () => { calculateTotals(); scheduleAutosave(); });
});
attachmentInput.addEventListener('change', () => { if (attachmentInput.files.length) saveForm(true); });
historySelect.addEventListener('change', () => { if (historySelect.value) window.location.href = window.location.pathname + '?id=' + encodeURIComponent(historySelect.value); else window.location.href = window.location.pathname; });
saveNowBtn.addEventListener('click', () => saveForm(true));
function setupSignature(prefix) {
  const canvas = document.getElementById(prefix + '_pad');
  const hidden = document.getElementById(prefix + '_signature');
  const ctx = canvas.getContext('2d');
  ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.strokeStyle = '#111';
  if (hidden.value) { const img = new Image(); img.onload = () => ctx.drawImage(img, 0, 0, canvas.width, canvas.height); img.src = hidden.value; }
  let drawing = false;
  function point(e) { const r = canvas.getBoundingClientRect(); const s = e.touches ? e.touches[0] : e; return { x:(s.clientX-r.left)*(canvas.width/r.width), y:(s.clientY-r.top)*(canvas.height/r.height) }; }
  function start(e) { drawing = true; const p = point(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); e.preventDefault(); }
  function move(e) { if (!drawing) return; const p = point(e); ctx.lineTo(p.x, p.y); ctx.stroke(); hidden.value = canvas.toDataURL('image/png'); scheduleAutosave(); e.preventDefault(); }
  function end() { if (!drawing) return; drawing = false; hidden.value = canvas.toDataURL('image/png'); scheduleAutosave(); }
  canvas.addEventListener('mousedown', start); canvas.addEventListener('mousemove', move); window.addEventListener('mouseup', end);
  canvas.addEventListener('touchstart', start, { passive:false }); canvas.addEventListener('touchmove', move, { passive:false }); canvas.addEventListener('touchend', end);
  document.querySelector('[data-clear="' + prefix + '"]').addEventListener('click', () => { ctx.clearRect(0,0,canvas.width,canvas.height); hidden.value = ''; scheduleAutosave(); });
}
setupSignature('treasurer'); setupSignature('comptroller'); setupSignature('chair'); calculateTotals();
</script>
</body>
</html>
