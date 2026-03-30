<?php
require_once __DIR__ . '/master_config.php';

$baseDir = dirname(__DIR__);
$ignore = ['.', '..', 'index.php', 'central_admin.php', 'login.php', 'logout.php', 'access_denied.php', 'users_admin.php', 'security.php', 'header.php', 'footer.php','.htaccess','install.php'];
$ignoreDirs = ['extras', 'images', 'css', 'js', 'assets','uploads'];

$allowedExtensions = ['php', 'pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'md'];

// $allowedExtenstions = oxford_require_role(['central_admin', 'super_admin']) ? array_merge($allowedExtensions, ['md']) : $allowedExtensions;

$customOrder = ['State', 'Chapter',  'President', 'Treasury', 'Secretary', 'Comptroller', 'HSR', 'Coordinator', 'HSC'];
$folderIcons = ['President'=>'👑','Treasury'=>'💰','Secretary'=>'📝','Comptroller'=>'📊','HSR'=>'🤝','Coordinator'=>'📌','Chapter'=>'🏠','State'=>'🗺️','HSC'=>'📚','Main'=>'📂'];
$groupedDocuments = [];
$currentFile = $_GET['file'] ?? '';

if (oxford_request_path() === 'central_admin.php') {
    oxford_require_role(['central_admin', 'super_admin']);
}
if (oxford_request_path() === 'users_admin.php') {
    oxford_require_role(['central_admin', 'super_admin']);
}

function formatDisplayName(string $name): string { return ucwords(str_replace(['-', '_'], ' ', $name)); }
function normalizeRelativePath(string $path): string { return str_replace('\\', '/', $path); }
function addDocumentToGroup(array &$groupedDocuments, string $groupName, string $relativePath, string $fileName): void {
    $name = pathinfo($fileName, PATHINFO_FILENAME);
    $groupedDocuments[$groupName][] = ['file'=>normalizeRelativePath($relativePath),'name'=>formatDisplayName($name),'ext'=>strtolower(pathinfo($fileName, PATHINFO_EXTENSION))];
}
function scanDocumentsRecursive(string $rootDir, string $currentDir, array $ignore, array $ignoreDirs, array $allowedExtensions, array &$groupedDocuments, int $depth = 0, ?string $topLevelFolder = null): void {
    $items = scandir($currentDir);
    if ($items === false) return;
    foreach ($items as $item) {
        if (in_array($item, $ignore, true)) continue;
        $fullPath = $currentDir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($fullPath)) {
            if (in_array($item, $ignoreDirs, true)) continue;
            $nextTopLevelFolder = $depth === 0 ? formatDisplayName($item) : $topLevelFolder;
            scanDocumentsRecursive($rootDir, $fullPath, $ignore, $ignoreDirs, $allowedExtensions, $groupedDocuments, $depth + 1, $nextTopLevelFolder);
            continue;
        }
        if (!is_file($fullPath)) continue;
        $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions, true)) continue;
        $relativePath = ltrim(str_replace($rootDir, '', $fullPath), DIRECTORY_SEPARATOR);
        $groupName = $topLevelFolder ?: 'Main';
        addDocumentToGroup($groupedDocuments, $groupName, $relativePath, $item);
    }
}
scanDocumentsRecursive($baseDir, $baseDir, $ignore, $ignoreDirs, $allowedExtensions, $groupedDocuments);
uksort($groupedDocuments, function ($a, $b) use ($customOrder) {
    $posA = array_search($a, $customOrder, true); $posB = array_search($b, $customOrder, true);
    $posA = ($posA === false) ? 999 : $posA; $posB = ($posB === false) ? 999 : $posB;
    return $posA === $posB ? strcasecmp($a, $b) : ($posA <=> $posB);
});
foreach ($groupedDocuments as &$docs) { usort($docs, fn($a, $b) => strcasecmp($a['name'], $b['name'])); } unset($docs);
function getFolderIcon(string $groupName, array $folderIcons): string { return $folderIcons[$groupName] ?? '📁'; }
?>
<style>
:root{--sidebar-width:320px;--oxford-blue:#17365d;--oxford-blue-light:#244d7d;--sidebar-bg:linear-gradient(180deg,#17365d,#244d7d);--hover-bg:rgba(255,255,255,.16);--item-bg:rgba(255,255,255,.08);--item-active:rgba(255,255,255,.24);--border-soft:rgba(255,255,255,.18);--shadow:-4px 0 14px rgba(0,0,0,.18);}
*{box-sizing:border-box;}body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#f4f7fb;}.oxford-sidebar{position:fixed;top:0;right:0;width:var(--sidebar-width);height:100vh;background:var(--sidebar-bg);color:#fff;padding:18px 16px 22px;box-shadow:var(--shadow);overflow-y:auto;z-index:9999;}
.oxford-logo-wrap{text-align:center;margin-bottom:10px;}.oxford-sidebar img{width:110px;background:#fff;padding:8px;border-radius:12px;margin-bottom:10px;}.oxford-context-card,.oxford-auth-card{background:rgba(255,255,255,.12);border:1px solid var(--border-soft);border-radius:14px;padding:12px;margin:0 0 14px;}.oxford-context-label{font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#d7e7fb;margin-bottom:6px;}.oxford-context-house{font-weight:700;margin-bottom:10px;line-height:1.35;}.oxford-house-switch{width:100%;border:none;border-radius:10px;padding:10px 12px;font-size:14px;color:#17365d;}.oxford-central-link,.oxford-security-link,.oxford-logout-link{display:block;margin-top:10px;text-align:center;padding:10px 12px;border-radius:10px;text-decoration:none;background:rgba(255,255,255,.18);color:#fff;font-weight:700;}.oxford-central-link:hover,.oxford-security-link:hover,.oxford-logout-link:hover{background:rgba(255,255,255,.24);}.oxford-mini{font-size:12px;color:#dce8f9;line-height:1.45;}.oxford-sidebar h2{margin:0;font-size:22px;text-align:center;}.subtext{text-align:center;font-size:13px;margin-top:8px;opacity:.9;}.sidebar-search{margin-top:18px;margin-bottom:14px;}.sidebar-search input{width:100%;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.12);color:#fff;border-radius:9px;padding:10px 12px;font-size:14px;outline:none;}.sidebar-search input::placeholder{color:rgba(255,255,255,.78);}.sidebar-menu{margin-top:10px;}.sidebar-home{display:block;padding:10px 12px;margin-bottom:12px;border-radius:8px;color:#fff;text-decoration:none;font-size:14px;background:rgba(255,255,255,.12);transition:.18s ease;}.sidebar-home:hover{background:var(--hover-bg);}.menu-group{margin-bottom:12px;border:1px solid var(--border-soft);border-radius:12px;overflow:hidden;background:rgba(255,255,255,.04);}.menu-group-header{width:100%;display:flex;align-items:center;justify-content:space-between;gap:10px;border:0;background:rgba(255,255,255,.06);color:#fff;padding:11px 12px;cursor:pointer;text-align:left;font-size:13px;font-weight:bold;letter-spacing:.3px;}.menu-group-header:hover{background:rgba(255,255,255,.11);}.menu-group-title{display:flex;align-items:center;gap:8px;min-width:0;}.menu-group-title-text{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}.menu-group-toggle{font-size:13px;transition:transform .18s ease;}.menu-group.open .menu-group-toggle{transform:rotate(180deg);}.menu-group-body{display:none;padding:8px;}.menu-group.open .menu-group-body{display:block;}.sidebar-menu a.file-link{display:block;padding:9px 10px;margin-bottom:6px;border-radius:8px;color:#fff;text-decoration:none;font-size:14px;background:var(--item-bg);cursor:pointer;transition:.18s ease;border-left:3px solid transparent;}.sidebar-menu a.file-link:hover{background:var(--hover-bg);border-left-color:#fff;}.sidebar-menu a.file-link.active{background:var(--item-active);border-left-color:#ffd76a;font-weight:bold;}.file-subpath{display:block;margin-top:3px;font-size:11px;opacity:.78;word-break:break-word;}.page-wrap{margin-right:calc(var(--sidebar-width) + 20px);padding:20px;}.back-to-top{position:fixed;right:calc(var(--sidebar-width) + 24px);bottom:24px;width:48px;height:48px;border:none;border-radius:50%;background:var(--oxford-blue);color:#fff;font-size:20px;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,.2);display:none;z-index:9998;transition:.18s ease;}.back-to-top:hover{background:var(--oxford-blue-light);transform:translateY(-2px);} @media (max-width:900px){:root{--sidebar-width:280px;}.page-wrap{margin-right:calc(var(--sidebar-width) + 14px);padding:14px;}.back-to-top{right:calc(var(--sidebar-width) + 16px);}} @media (max-width:700px){.oxford-sidebar{position:relative;width:100%;height:auto;right:auto;top:auto;box-shadow:none;}.page-wrap{margin-right:0;padding:14px;}.back-to-top{right:16px;bottom:16px;}}
</style>
<div class="oxford-sidebar">
    <div class="oxford-logo-wrap"><img src="images/oxford_house_logo.png" alt="Oxford House Logo"></div>
    <h2>Oxford House Files</h2>
    <!-- <div class="subtext">Central login + house isolation</div> -->

    <div class="oxford-auth-card">
        <div class="oxford-context-label">Signed In</div>
        <div class="oxford-context-house"><?= oxford_h($oxfordUser['full_name'] ?? '') ?></div>
        <div class="oxford-mini">Role: <b><?= oxford_h(oxford_get_role_label((string)($oxfordUser['role'] ?? ''))) ?></b><br><?= oxford_h($oxfordUser['email'] ?? '') ?></div>
        <a class="oxford-security-link" href="security.php">Security</a>
        <a class="oxford-logout-link" href="logout.php">Sign Out</a>
    </div>

    <div class="oxford-context-card">
        <div class="oxford-context-label">Current House</div>
        <div class="oxford-context-house"><?= oxford_h($currentHouseLabel ?: $currentHouseName) ?></div>
        <?php $oxfordAdmin = $oxfordUser['role'] !== 'central_admin' && $oxfordUser['role'] !== 'super_admin' ? true : false; ?>
        <form  method="get">
            <?php if ($currentFile !== ''): ?><input type="hidden" name="file" value="<?= oxford_h($currentFile) ?>"><?php endif; ?>
            <?php if(!$oxfordAdmin){ ?>  
            <select class="oxford-house-switch" name="house_id" onchange="this.form.submit()">
                <?php foreach ($allOxfordHouses as $houseOption): ?>
                    <option  value="<?= (int)$houseOption['id'] ?>" <?= (int)$houseOption['id'] === (int)$currentHouseId ? 'selected' : '' ?>><?= oxford_h($houseOption['house_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php } ?>

        </form>
        <?php if ($oxfordIsCentralAdmin): ?><a class="oxford-central-link" href="central_admin.php">Central Dashboard</a><?php endif; ?>
        <?php if ($oxfordIsCentralAdmin): ?><a class="oxford-central-link" href="users_admin.php">User & Access Manager</a><?php endif; ?>
    </div>

    <div class="sidebar-search"><input type="text" id="sidebarSearch" placeholder="Search files or folders..."></div>
    <div class="sidebar-menu" id="sidebarMenu">
        <a class="sidebar-home" href="index.php?house_id=<?= (int)$currentHouseId ?>">🏠 Home</a>
        <?php foreach ($groupedDocuments as $groupName => $docs): ?>
            <div class="menu-group" data-group="<?= htmlspecialchars(strtolower($groupName), ENT_QUOTES, 'UTF-8') ?>">
                <button type="button" class="menu-group-header" onclick="toggleMenuGroup(this)">
                    <span class="menu-group-title"><span><?= htmlspecialchars(getFolderIcon($groupName, $folderIcons)) ?></span><span class="menu-group-title-text"><?= htmlspecialchars($groupName, ENT_QUOTES, 'UTF-8') ?> Files</span></span>
                    <span class="menu-group-toggle">▼</span>
                </button>
                <div class="menu-group-body">
                    <?php foreach ($docs as $doc): $docFile = $doc['file']; $isActive = ($currentFile === $docFile); $subPath = dirname($docFile); $showSubPath = ($subPath !== '.' && $subPath !== ''); ?>
                        <a class="file-link<?= $isActive ? ' active' : '' ?>" href="?file=<?= urlencode($docFile) ?>" data-name="<?= htmlspecialchars(strtolower($doc['name']), ENT_QUOTES, 'UTF-8') ?>" data-path="<?= htmlspecialchars(strtolower($docFile), ENT_QUOTES, 'UTF-8') ?>" data-group-name="<?= htmlspecialchars(strtolower($groupName), ENT_QUOTES, 'UTF-8') ?>" onclick="if (typeof openFile === 'function') { event.preventDefault(); openFile('<?= htmlspecialchars($docFile, ENT_QUOTES, 'UTF-8') ?>'); setActiveSidebarLink(this); }">
                            <?= htmlspecialchars($doc['name'], ENT_QUOTES, 'UTF-8') ?>
                            <?php if ($showSubPath): ?><span class="file-subpath"><?= htmlspecialchars($subPath, ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<button type="button" class="back-to-top" id="backToTopBtn" title="Back to top">↑</button>
<div class="page-wrap">
<script>
function toggleMenuGroup(button){const group=button.closest('.menu-group');if(group)group.classList.toggle('open');}
function setActiveSidebarLink(linkElement){document.querySelectorAll('.file-link.active').forEach(link=>link.classList.remove('active'));if(linkElement)linkElement.classList.add('active');}
(function(){const searchInput=document.getElementById('sidebarSearch');const groups=document.querySelectorAll('.menu-group');if(searchInput){searchInput.addEventListener('input',function(){const term=this.value.trim().toLowerCase();groups.forEach(function(group){const groupName=group.getAttribute('data-group')||'';const links=group.querySelectorAll('.file-link');let visibleCount=0;links.forEach(function(link){const fileName=link.getAttribute('data-name')||'';const filePath=link.getAttribute('data-path')||'';const linkGroup=link.getAttribute('data-group-name')||'';const match=term===''||fileName.includes(term)||filePath.includes(term)||linkGroup.includes(term)||groupName.includes(term);link.style.display=match?'block':'none';if(match)visibleCount++;});if(visibleCount>0||term===''||groupName.includes(term)){group.style.display='block';if(term!=='')group.classList.add('open');}else{group.style.display='none';}});});}const backToTopBtn=document.getElementById('backToTopBtn');function toggleBackToTop(){if(!backToTopBtn)return;backToTopBtn.style.display=window.scrollY>250?'block':'none';}window.addEventListener('scroll',toggleBackToTop);toggleBackToTop();if(backToTopBtn){backToTopBtn.addEventListener('click',function(){window.scrollTo({top:0,behavior:'smooth'});});}})();
</script>

<!-- System Version -->
<div style='position:fixed;bottom:5px;right:10px;font-size:12px;color:#999;'>v4.3.0</div>
