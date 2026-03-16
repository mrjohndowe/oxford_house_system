<?php include __DIR__.'/extras/header.php'; ?>

<style>

.viewer{
background:#fff;
border-radius:10px;
box-shadow:0 0 8px rgba(0,0,0,.1);
overflow:hidden;
}

.viewer iframe{
width:100%;
height:90vh;
border:none;
}

.placeholder{
padding:40px;
text-align:center;
color:#666;
position:relative;
bottom:885;
}
.logo {
    position: absolute;
    bottom: 30%;
    left: 25%;
}
.system-description{
    max-width:1100px;
    margin:40px auto;
    text-align:left;
    padding:30px;
    background:#ffffff;
    border-radius:18px;
    box-shadow:0 10px 30px rgba(0,0,0,.08);
    color:#17365d;
}

.system-logo{
    display:block;
    margin:0 auto 20px auto;
    max-width:130px;
    height:auto;
}

.system-description h1{
    text-align:center;
    margin:0 0 18px 0;
    font-size:34px;
    color:#17365d;
}

.system-intro{
    text-align:center;
    max-width:900px;
    margin:0 auto 30px auto;
    font-size:17px;
    line-height:1.7;
    color:#4b5f75;
}

.system-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));
    gap:18px;
    margin-top:20px;
}

.system-card{
    background:#f5f8fc;
    border:1px solid #d9e4f0;
    border-radius:14px;
    padding:18px;
    box-shadow:0 4px 12px rgba(0,0,0,.04);
}

.system-card h2{
    margin:0 0 10px 0;
    font-size:18px;
    color:#17365d;
}

.system-card p{
    margin:0;
    font-size:14px;
    line-height:1.65;
    color:#4b5f75;
}

.system-footer-note{
    margin-top:26px;
    text-align:center;
    font-size:15px;
    color:#17365d;
    background:#eef4fb;
    border:1px solid #d7e4f1;
    border-radius:12px;
    padding:14px 18px;
}

</style>

<div class="viewer">

<iframe id="fileViewer"></iframe>


<div id="placeholder" class="placeholder">
    <div class="system-description">
        <img src="images/oxford_house_logo.png" alt="Oxford House Logo" class="system-logo">

        <h1>Oxford House Central Document & Management System</h1>

        <p class="system-intro">
            Welcome to the Oxford House Central System, a unified platform designed to manage
            house-level, chapter-level, and state-level documents, reporting, access control,
            and administrative workflows across all Oxford House locations.
        </p>

        <div class="system-grid">
            <div class="system-card">
                <h2>Centralized Access</h2>
                <p>
                    The system provides secure login access for house users, managers, central admins,
                    and super admins. Each user only sees the houses and tools they are assigned to,
                    while central administrators can access all connected houses from one dashboard.
                </p>
            </div>

            <div class="system-card">
                <h2>House-Based Database Separation</h2>
                <p>
                    Every house operates with its own dedicated database for clean separation of records,
                    while the central system maintains user accounts, permissions, audit logs, and
                    cross-house reporting from a master database.
                </p>
            </div>

            <div class="system-card">
                <h2>Full Department Coverage</h2>
                <p>
                    Built-in modules support President, Secretary, Treasury, Comptroller, Coordinator,
                    HSR, Chapter, HSC, and State workflows. This allows each house to manage reports,
                    ledgers, meeting minutes, audits, schedules, contracts, checklists, and forms
                    in one organized environment.
                </p>
            </div>

            <div class="system-card">
                <h2>Cross-House Administration</h2>
                <p>
                    Central administration tools allow authorized staff to create houses, assign users,
                    manage permissions, switch between houses, monitor system activity, and review
                    reporting across the entire platform.
                </p>
            </div>

            <div class="system-card">
                <h2>Automatic Schema Carry-Over</h2>
                <p>
                    Any compatible files placed inside the <strong>chapter</strong> or <strong>state</strong>
                    folders can automatically carry their database structure into all active house databases.
                    This helps keep new forms and modules synchronized without rebuilding existing houses.
                </p>
            </div>

            <div class="system-card">
                <h2>Audit & Security Tracking</h2>
                <p>
                    The system records major activity such as logins, failed logins, house switching,
                    page access, form submissions, and user/account changes. This provides accountability,
                    traceability, and a stronger operational security history.
                </p>
            </div>

            <div class="system-card">
                <h2>Document Viewer & Navigation</h2>
                <p>
                    The built-in sidebar groups documents by department and allows users to search files,
                    switch houses, and open forms quickly from one interface. This makes the platform
                    easier to use for both day-to-day house operations and central oversight.
                </p>
            </div>

            <div class="system-card">
                <h2>Designed for Growth</h2>
                <p>
                    The Oxford House Central System is structured to expand easily. New houses, new users,
                    new forms, and future state or chapter modules can be added while preserving
                    existing records and maintaining consistent navigation and access control.
                </p>
            </div>
        </div>

        <div class="system-footer-note">
            <strong>Use the menu on the right</strong> to browse available files and open documents for the
            currently selected house.
        </div>
    </div>
</div>

</div>


<script>

function openFile(file){

let viewer = document.getElementById("fileViewer");
let placeholder = document.getElementById("placeholder");

viewer.src = file;

if(placeholder){
placeholder.style.display="none";
}

}

</script>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "Oxford House Central System",
  "url": "http://139.99.22.116/oxford/login.php",
  "logo": "http://139.99.22.116/oxford/images/oxford_house_logo.png",
  "description": "Oxford House Central System is a secure platform for managing house, chapter, and state documents, reports, meeting minutes, and financial records with centralized administration and multi-house database integration."
}
</script>
<head>
<meta name="description" content="Oxford House Central System is a secure platform for managing house, chapter, and state documents, reports, meeting minutes, and financial records with centralized administration and multi-house database integration.">
<meta name="keywords" content="Oxford House system, Oxford House management, house reporting system, chapter meeting minutes, recovery house management software, Oxford House administration">
<meta name="author" content="Mr John Dowe - Software Developer">   
<link rel="stylesheet" href="style.css">
</head>

<?php include __DIR__.'/extras/footer.php'; ?>