<?php
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
    http_response_code(500);
    die('Database connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

/* =========================
   DEFAULT SECTIONS
========================= */
$sections = [
    "Bedding & Sleep Setup" => [
        ["item" => "Mattress", "budget" => "120.00"],
        ["item" => "Mattress protector", "budget" => "10.00"],
        ["item" => "Sheet set – 2 sets", "budget" => "20.00"],
        ["item" => "Comforter or duvet", "budget" => "25.00"],
        ["item" => "Pillows – 2–4", "budget" => "10.00"],
        ["item" => "Throw blanket", "budget" => "10.00"],
    ],
    "Laundry & Clothing Organization" => [
        ["item" => "Laundry basket/hamper", "budget" => "8.00"],
        ["item" => "Hangers – 20–30 pack", "budget" => "5.00"],
        ["item" => "Small dresser or storage cubes", "budget" => "20.00"],
        ["item" => "Shoe rack or under-bed storage", "budget" => "10.00"],
        ["item" => "Lint roller", "budget" => "3.00"],
    ],
    "Room Cleaning & Hygiene" => [
        ["item" => "Small trash can", "budget" => "5.00"],
        ["item" => "Trash bags", "budget" => "3.00"],
        ["item" => "Disinfecting wipes", "budget" => "3.00"],
        ["item" => "Air freshener or odor absorber", "budget" => "3.00"],
        ["item" => "Mini vacuum or broom/dustpan", "budget" => "10.00"],
        ["item" => "Laundry detergent", "budget" => "5.00"],
        ["item" => "Dryer sheets", "budget" => "3.00"],
        ["item" => "Tissues", "budget" => "2.00"],
    ],
    "Privacy & Comfort" => [
        ["item" => "Blackout curtains", "budget" => "15.00"],
        ["item" => "Fan or small air purifier", "budget" => "15.00"],
        ["item" => "Bedside lamp", "budget" => "8.00"],
        ["item" => "Extension cord + surge protector", "budget" => "8.00"],
        ["item" => "Long phone charger", "budget" => "6.00"],
        ["item" => "Earplugs or white noise option", "budget" => "3.00"],
    ],
    "Storage & Organization" => [
        ["item" => "Nightstand or bedside shelf", "budget" => "15.00"],
        ["item" => "Desk + chair (optional)", "budget" => "40.00"],
        ["item" => "Drawer organizers", "budget" => "5.00"],
        ["item" => "Under-bed bins", "budget" => "8.00"],
        ["item" => "Wall hooks or over-door hooks", "budget" => "5.00"],
    ],
    "Personal Kitchen Basics" => [
        ["item" => "Microwave-safe bowl/plate", "budget" => "5.00"],
        ["item" => "Fork/spoon/knife set", "budget" => "3.00"],
        ["item" => "Water bottle", "budget" => "5.00"],
        ["item" => "Coffee maker or kettle (optional)", "budget" => "15.00"],
        ["item" => "Personal snacks", "budget" => "10.00"],
        ["item" => "Mini fridge (optional)", "budget" => "60.00"],
    ],
    "Grooming & Personal Care" => [
        ["item" => "Shower caddy", "budget" => "5.00"],
        ["item" => "Towels – 2 bath, 2 hand", "budget" => "10.00"],
        ["item" => "Toiletries", "budget" => "10.00"],
        ["item" => "Razor + shaving cream", "budget" => "5.00"],
        ["item" => "Nail clippers", "budget" => "2.00"],
        ["item" => "Basic first aid items", "budget" => "5.00"],
    ],
    "Optional but Nice-to-Have" => [
        ["item" => "Small TV or monitor", "budget" => "60.00"],
        ["item" => "LED lights or simple décor", "budget" => "8.00"],
        ["item" => "Bookshelf", "budget" => "20.00"],
        ["item" => "Scented candles (if allowed)", "budget" => "5.00"],
        ["item" => "Bluetooth speaker", "budget" => "15.00"],
        ["item" => "Notebook or planner", "budget" => "3.00"],
    ],
];

/* =========================
   HELPERS
========================= */
function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function buildDefaultItems(array $sections): array
{
    $items = [];

    foreach ($sections as $sectionName => $sectionItems) {
        foreach ($sectionItems as $entry) {
            $budget = number_format((float)$entry['budget'], 2, '.', '');
            $items[] = [
                'section' => $sectionName,
                'name' => $entry['item'],
                'price' => $budget,
                'original_price' => $budget,
                'status' => '',
                'is_custom' => 0,
            ];
        }
    }

    return $items;
}

function regroupItemsBySection(array $items): array
{
    $grouped = [];
    foreach ($items as $item) {
        $section = (string)($item['section'] ?? 'Other');
        if (!isset($grouped[$section])) {
            $grouped[$section] = [];
        }
        $grouped[$section][] = $item;
    }
    return $grouped;
}

function normalizeItems(array $items): array
{
    $normalized = [];

    foreach ($items as $item) {
        $name = trim((string)($item['name'] ?? ''));
        $section = trim((string)($item['section'] ?? 'Other'));
        if ($name === '') {
            continue;
        }

        $price = is_numeric($item['price'] ?? null) ? (float)$item['price'] : 0.00;
        $originalPrice = is_numeric($item['original_price'] ?? null) ? (float)$item['original_price'] : $price;
        $status = (string)($item['status'] ?? '');
        if (!in_array($status, ['bought', 'acquired', 'notneeded', ''], true)) {
            $status = '';
        }

        $normalized[] = [
            'section' => $section,
            'name' => $name,
            'price' => number_format($price, 2, '.', ''),
            'original_price' => number_format($originalPrice, 2, '.', ''),
            'status' => $status,
            'is_custom' => !empty($item['is_custom']) ? 1 : 0,
        ];
    }

    return $normalized;
}

/* =========================
   TABLE SETUP
========================= */
$pdo->exec("
    CREATE TABLE IF NOT EXISTS bedroom_essentials_checklists (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        checklist_key VARCHAR(100) NOT NULL,
        title VARCHAR(255) NOT NULL DEFAULT 'Bedroom Essentials – Checklist',
        items_json LONGTEXT NOT NULL,
        dark_mode TINYINT(1) NOT NULL DEFAULT 0,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_checklist_key (checklist_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$checklistKey = 'default_bedroom_essentials';

/* =========================
   AJAX SAVE / RESET
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $action = (string)$_POST['action'];

        if ($action === 'save') {
            $rawItems = json_decode((string)($_POST['items_json'] ?? '[]'), true);
            if (!is_array($rawItems)) {
                throw new RuntimeException('Invalid checklist data.');
            }

            $items = normalizeItems($rawItems);
            $darkMode = !empty($_POST['dark_mode']) ? 1 : 0;

            $stmt = $pdo->prepare("
                INSERT INTO bedroom_essentials_checklists (checklist_key, title, items_json, dark_mode)
                VALUES (:checklist_key, :title, :items_json, :dark_mode)
                ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    items_json = VALUES(items_json),
                    dark_mode = VALUES(dark_mode)
            ");
            $stmt->execute([
                ':checklist_key' => $checklistKey,
                ':title' => 'Bedroom Essentials – Checklist',
                ':items_json' => json_encode($items, JSON_UNESCAPED_UNICODE),
                ':dark_mode' => $darkMode,
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Checklist saved successfully.',
                'items' => $items,
            ]);
            exit;
        }

        if ($action === 'reset') {
            $defaultItems = buildDefaultItems($sections);

            $stmt = $pdo->prepare("
                INSERT INTO bedroom_essentials_checklists (checklist_key, title, items_json, dark_mode)
                VALUES (:checklist_key, :title, :items_json, 0)
                ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    items_json = VALUES(items_json),
                    dark_mode = 0
            ");
            $stmt->execute([
                ':checklist_key' => $checklistKey,
                ':title' => 'Bedroom Essentials – Checklist',
                ':items_json' => json_encode($defaultItems, JSON_UNESCAPED_UNICODE),
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Checklist reset successfully.',
                'items' => $defaultItems,
                'dark_mode' => 0,
            ]);
            exit;
        }

        throw new RuntimeException('Invalid action.');
    } catch (Throwable $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
        ]);
        exit;
    }
}

/* =========================
   LOAD SAVED DATA
========================= */
$defaultItems = buildDefaultItems($sections);

$stmt = $pdo->prepare("
    SELECT items_json, dark_mode
    FROM bedroom_essentials_checklists
    WHERE checklist_key = :checklist_key
    LIMIT 1
");
$stmt->execute([':checklist_key' => $checklistKey]);
$savedRow = $stmt->fetch();

if ($savedRow && !empty($savedRow['items_json'])) {
    $savedItems = json_decode((string)$savedRow['items_json'], true);
    if (is_array($savedItems) && !empty($savedItems)) {
        $renderItems = normalizeItems($savedItems);
    } else {
        $renderItems = $defaultItems;
    }
    $savedDarkMode = !empty($savedRow['dark_mode']);
} else {
    $renderItems = $defaultItems;
    $savedDarkMode = false;
}

$groupedSections = regroupItemsBySection($renderItems);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Bedroom Essentials Checklist</title>

<style>
/* ——— GENERAL ——— */
body {
    font-family: Arial;
    margin: 50px 350px;
    font-size: 13px;
    transition: background 0.3s, color 0.3s;
}
body.dark {
    background: #1e1e1e;
    color: #e0e0e0;
}
h1 { text-align: center; margin-bottom: 8px; font-size: 20px; }
h2 { margin: 12px 0 4px; font-size: 15px; border-bottom: 1px solid #ccc; }

/* ——— ITEM ROW ——— */
.item { display: flex; align-items: center; width: 100%; padding: 4px 0; }
.item label { flex: 1; }
.dots { flex: 1; border-bottom: 1px dotted #999; margin: 0 6px; min-height: 18px; display: flex; align-items: center; }
.price-input {
    width: 70px;
    text-align: right;
    border: 1px solid #aaa;
    padding: 2px;
    font-size: 12px;
}

/* ——— ICON BUTTONS ——— */
.icon-btn {
    font-size: 20px;
    cursor: pointer;
    margin: 0 4px;
    padding: 2px;
    border-radius: 4px;
    position: relative;
}
.icon-btn:hover { transform: scale(1.2); }

/* ——— TOOLTIP ——— */
.tooltip {
    visibility: hidden;
    opacity: 0;
    position: absolute;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 10;
    transition: opacity 0.2s;
    top: -45px;
    left: 0;
    color: white;
}
.tooltip::after {
    content: "";
    position: absolute;
    bottom: -8px;
    left: 10px;
    border-width: 8px 8px 0 8px;
    border-style: solid;
    border-color: inherit transparent transparent transparent;
}
.icon-btn:hover .tooltip {
    visibility: visible;
    opacity: 1;
}

/* ——— STATUS COLORS ——— */
.status-bought { color: #00aa00; font-weight: bold; }
.status-acquired { color: #0077ff; font-weight: bold; }
.status-notneeded { color: #dd0000; font-weight: bold; }

/* ——— CONTROLS ——— */
.controls {
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.btn {
    padding: 6px 12px;
    border: none;
    cursor: pointer;
    border-radius: 4px;
    font-size: 13px;
}
.reset-btn { background: #dd0000; color: white; }
.dark-btn { background: #333; color: white; }
.print-btn { background: #0077ff; color: white; }
.add-btn { background: #28a745; color: white; }
.save-status {
    margin-left: auto;
    display: flex;
    align-items: center;
    font-size: 12px;
    color: #666;
}
body.dark .save-status { color: #bbb; }

/* ——— PRINT MODE ——— */
@media print {
    .controls, .icon-btn, .tooltip, #addItemModal { display: none !important; }
    body { margin: 0; }
}

/* ——— MODAL ——— */
#addItemModal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.6);
}
.modal-content {
    background: white;
    width: 350px;
    margin: 120px auto;
    padding: 20px;
    border-radius: 8px;
}
body.dark .modal-content {
    background: #2b2b2b;
    color: white;
}
.modal-content input, .modal-content select {
    width: 100%;
    padding: 6px;
    margin-bottom: 10px;
    box-sizing: border-box;
}
.close-modal {
    float: right;
    cursor: pointer;
    font-size: 18px;
}
</style>

<script>
const SAVE_URL = window.location.href;
let saveTimer = null;

function getStatusMarkup(status) {
    if (status === "bought") return "<span class='status-bought'>Bought ✔️</span>";
    if (status === "acquired") return "<span class='status-acquired'>Acquired 📦</span>";
    if (status === "notneeded") return "<span class='status-notneeded'>Not Needed 🚫</span>";
    return "";
}

/* ——— TOTAL ——— */
function updateTotal() {
    let total = 0;
    document.querySelectorAll(".price-input").forEach(input => {
        let val = parseFloat(input.value);
        if (!isNaN(val)) total += val;
    });
    document.getElementById("totalAmount").innerText = total.toFixed(2);
    scheduleSave();
}

/* ——— STATUS ——— */
function setStatus(row, status) {
    let price = row.querySelector(".price-input");
    let dots = row.querySelector(".dots");

    row.querySelectorAll(".icon-btn").forEach(btn => btn.classList.remove("active"));
    let btn = row.querySelector(".btn-" + status);
    if (btn) btn.classList.add("active");

    price.value = "0.00";
    dots.innerHTML = getStatusMarkup(status);

    updateTotal();
}

/* ——— RESET ——— */
function resetAll() {
    if (!confirm("Reset the checklist back to the original defaults?")) {
        return;
    }

    fetch(SAVE_URL, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
        body: new URLSearchParams({ action: "reset" }).toString()
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            alert(data.message || "Reset failed.");
            return;
        }
        renderChecklist(data.items);
        document.body.classList.remove("dark");
        setSaveStatus("Reset and saved");
    })
    .catch(() => {
        alert("Unable to reset checklist.");
    });
}

/* ——— DARK MODE ——— */
function toggleDarkMode() {
    document.body.classList.toggle("dark");
    scheduleSave();
}

/* ——— SAVE STATUS ——— */
function setSaveStatus(text) {
    const el = document.getElementById("saveStatus");
    if (el) el.textContent = text;
}

/* ——— COLLECT DATA ——— */
function collectChecklistData() {
    const data = [];
    document.querySelectorAll(".item").forEach(row => {
        data.push({
            section: row.dataset.section || "Other",
            name: row.querySelector("label").innerText.trim(),
            price: row.querySelector(".price-input").value || "0.00",
            original_price: row.querySelector(".price-input").dataset.originalPrice || (row.querySelector(".price-input").value || "0.00"),
            status: row.querySelector(".icon-btn.active")?.dataset.status || "",
            is_custom: row.dataset.custom === "1" ? 1 : 0
        });
    });
    return data;
}

/* ——— SAVE TO DATABASE ——— */
function saveState() {
    const payload = new URLSearchParams();
    payload.append("action", "save");
    payload.append("items_json", JSON.stringify(collectChecklistData()));
    payload.append("dark_mode", document.body.classList.contains("dark") ? "1" : "0");

    setSaveStatus("Saving...");

    fetch(SAVE_URL, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
        body: payload.toString()
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            setSaveStatus("Save failed");
            return;
        }
        setSaveStatus("Saved");
    })
    .catch(() => {
        setSaveStatus("Save failed");
    });
}

function scheduleSave() {
    setSaveStatus("Saving...");
    clearTimeout(saveTimer);
    saveTimer = setTimeout(saveState, 500);
}

/* ——— MODAL ——— */
function openModal() {
    document.getElementById("addItemModal").style.display = "block";
}
function closeModal() {
    document.getElementById("addItemModal").style.display = "none";
    document.getElementById("modalItemName").value = "";
    document.getElementById("modalItemPrice").value = "";
}

/* ——— ADD NEW ITEM ——— */
function addNewItem() {
    let name = document.getElementById("modalItemName").value.trim();
    let price = document.getElementById("modalItemPrice").value.trim();
    let section = document.getElementById("modalItemSection").value;

    if (!name || !price) {
        alert("Please enter both a name and a price.");
        return;
    }

    let sectionHeader = [...document.querySelectorAll("h2")]
        .find(h => h.innerText.trim() === section);

    if (!sectionHeader) {
        alert("Section not found.");
        return;
    }

    let newRow = document.createElement("div");
    newRow.className = "item";
    newRow.dataset.section = section;
    newRow.dataset.custom = "1";
    newRow.innerHTML = `
        <label>${escapeHtml(name)}</label>

        <span class="icon-btn btn-bought" data-status="bought" onclick="setStatus(this.closest('.item'), 'bought')" style="color:#00aa00;">
            ✔️
            <span class="tooltip" style="background:#00aa00;">Bought ✔️</span>
        </span>

        <span class="icon-btn btn-acquired" data-status="acquired" onclick="setStatus(this.closest('.item'), 'acquired')" style="color:#0077ff;">
            📦
            <span class="tooltip" style="background:#0077ff;">Acquired 📦</span>
        </span>

        <span class="icon-btn btn-notneeded" data-status="notneeded" onclick="setStatus(this.closest('.item'), 'notneeded')" style="color:#dd0000;">
            🚫
            <span class="tooltip" style="background:#dd0000;">Not Needed 🚫</span>
        </span>

        <div class="dots"></div>

        <span>$</span>
        <input type="number" step="0.01" class="price-input" data-original-price="${parseFloat(price).toFixed(2)}" value="${parseFloat(price).toFixed(2)}" oninput="updateTotal()">
    `;

    let insertAfter = sectionHeader;
    let next = sectionHeader.nextElementSibling;
    while (next && next.classList.contains("item")) {
        insertAfter = next;
        next = next.nextElementSibling;
    }
    insertAfter.insertAdjacentElement("afterend", newRow);

    closeModal();
    updateTotal();
}

/* ——— ESCAPE HTML ——— */
function escapeHtml(str) {
    return str.replace(/[&<>"']/g, function(m) {
        return ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[m];
    });
}

/* ——— RENDER FROM RESPONSE ——— */
function renderChecklist(items) {
    const container = document.getElementById("checklistContainer");
    container.innerHTML = "";

    const grouped = {};
    items.forEach(item => {
        const section = item.section || "Other";
        if (!grouped[section]) grouped[section] = [];
        grouped[section].push(item);
    });

    Object.keys(grouped).forEach(sectionName => {
        const h2 = document.createElement("h2");
        h2.textContent = sectionName;
        container.appendChild(h2);

        grouped[sectionName].forEach(item => {
            const row = document.createElement("div");
            row.className = "item";
            row.dataset.section = sectionName;
            row.dataset.custom = String(item.is_custom ? 1 : 0);

            row.innerHTML = `
                <label>${escapeHtml(item.name)}</label>

                <span class="icon-btn btn-bought ${item.status === 'bought' ? 'active' : ''}" data-status="bought" onclick="setStatus(this.closest('.item'), 'bought')" style="color:#00aa00;">
                    ✔️
                    <span class="tooltip" style="background:#00aa00;">Bought ✔️</span>
                </span>

                <span class="icon-btn btn-acquired ${item.status === 'acquired' ? 'active' : ''}" data-status="acquired" onclick="setStatus(this.closest('.item'), 'acquired')" style="color:#0077ff;">
                    📦
                    <span class="tooltip" style="background:#0077ff;">Acquired 📦</span>
                </span>

                <span class="icon-btn btn-notneeded ${item.status === 'notneeded' ? 'active' : ''}" data-status="notneeded" onclick="setStatus(this.closest('.item'), 'notneeded')" style="color:#dd0000;">
                    🚫
                    <span class="tooltip" style="background:#dd0000;">Not Needed 🚫</span>
                </span>

                <div class="dots">${getStatusMarkup(item.status || "")}</div>

                <span>$</span>
                <input type="number" step="0.01" class="price-input" data-original-price="${parseFloat(item.original_price || item.price || 0).toFixed(2)}" value="${parseFloat(item.price || 0).toFixed(2)}" oninput="updateTotal()">
            `;
            container.appendChild(row);
        });
    });

    updateTotal();
}

/* ——— INIT ——— */
window.onload = () => {
    <?php if ($savedDarkMode): ?>
    document.body.classList.add("dark");
    <?php endif; ?>
    updateTotal();
    setSaveStatus("Loaded");
};
</script>

</head>
<body>

<div class="controls">
    <button class="btn add-btn" onclick="openModal()">Add Item</button>
    <button class="btn reset-btn" onclick="resetAll()">Reset</button>
    <button class="btn dark-btn" onclick="toggleDarkMode()">Dark Mode</button>
    <button class="btn print-btn" onclick="window.print()">Print</button>
    <div class="save-status" id="saveStatus">Loaded</div>
</div>

<h1>Bedroom Essentials – Checklist</h1>

<div id="checklistContainer">
<?php foreach ($groupedSections as $sectionName => $items): ?>
    <h2><?= h($sectionName) ?></h2>

    <?php foreach ($items as $item): ?>
        <?php
            $status = (string)($item['status'] ?? '');
            $dots = '';
            if ($status === 'bought') {
                $dots = "<span class='status-bought'>Bought ✔️</span>";
            } elseif ($status === 'acquired') {
                $dots = "<span class='status-acquired'>Acquired 📦</span>";
            } elseif ($status === 'notneeded') {
                $dots = "<span class='status-notneeded'>Not Needed 🚫</span>";
            }
        ?>
        <div class="item" data-section="<?= h($item['section']) ?>" data-custom="<?= !empty($item['is_custom']) ? '1' : '0' ?>">

            <label><?= h($item['name']) ?></label>

            <span class="icon-btn btn-bought <?= $status === 'bought' ? 'active' : '' ?>" data-status="bought" onclick="setStatus(this.closest('.item'), 'bought')" style="color:#00aa00;">
                ✔️
                <span class="tooltip" style="background:#00aa00;">Bought ✔️</span>
            </span>

            <span class="icon-btn btn-acquired <?= $status === 'acquired' ? 'active' : '' ?>" data-status="acquired" onclick="setStatus(this.closest('.item'), 'acquired')" style="color:#0077ff;">
                📦
                <span class="tooltip" style="background:#0077ff;">Acquired 📦</span>
            </span>

            <span class="icon-btn btn-notneeded <?= $status === 'notneeded' ? 'active' : '' ?>" data-status="notneeded" onclick="setStatus(this.closest('.item'), 'notneeded')" style="color:#dd0000;">
                🚫
                <span class="tooltip" style="background:#dd0000;">Not Needed 🚫</span>
            </span>

            <div class="dots"><?= $dots ?></div>

            <span>$</span>
            <input
                type="number"
                step="0.01"
                class="price-input"
                data-original-price="<?= h((string)$item['original_price']) ?>"
                value="<?= h((string)$item['price']) ?>"
                oninput="updateTotal()"
            >

        </div>
    <?php endforeach; ?>
<?php endforeach; ?>
</div>

<div id="totalBox" style="margin-top:20px; font-size:16px; font-weight:bold; text-align:right;">
    Total Estimated Budget: $<span id="totalAmount">0.00</span>
</div>

<div id="addItemModal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeModal()">✖</span>
        <h3>Add New Item</h3>

        <input id="modalItemName" type="text" placeholder="Item name">
        <input id="modalItemPrice" type="number" step="0.01" placeholder="Price">

        <select id="modalItemSection">
            <?php foreach (array_keys($groupedSections) as $sectionName): ?>
                <option value="<?= h($sectionName) ?>"><?= h($sectionName) ?></option>
            <?php endforeach; ?>
        </select>

        <button class="btn add-btn" style="width:100%;" onclick="addNewItem()">Add Item</button>
    </div>
</div>

<script>updateTotal();</script>

</body>
</html>