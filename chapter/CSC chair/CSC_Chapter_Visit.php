<?php

/* =========================
   DATABASE CONFIG
========================= */
require_once __DIR__ . '/../../extras/master_config.php';

try{

$pdo=new PDO(
"mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
$dbUser,
$dbPass,
[
PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
]);

}catch(PDOException $e){
die("DB Error ".$e->getMessage());
}

/*
AUTO INSTALL TABLE
*/

$pdo->exec("
CREATE TABLE IF NOT EXISTS chapter_visit_reports (

id INT AUTO_INCREMENT PRIMARY KEY,

report_date DATE,

chapter VARCHAR(255),
location VARCHAR(255),

chair VARCHAR(255),
secretary VARCHAR(255),
treasurer VARCHAR(255),
hsc_chair VARCHAR(255),
other_person VARCHAR(255),

overall_grade INT,

bank_amount DECIMAL(10,2),
dues_amount DECIMAL(10,2),
loans_amount DECIMAL(10,2),

rating_1 INT,
rating_2 INT,
rating_3 INT,
rating_4 INT,
rating_5 INT,
rating_6 INT,
rating_7 INT,
rating_8 INT,

rating_average DECIMAL(4,2),

comments TEXT,

visit_date DATE,
followup_dates VARCHAR(255),

signature LONGTEXT,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

/*
SAVE
*/

if(isset($_POST['action']) && $_POST['action']=="autosave"){

$ratings=[
$_POST['rating_1']??0,
$_POST['rating_2']??0,
$_POST['rating_3']??0,
$_POST['rating_4']??0,
$_POST['rating_5']??0,
$_POST['rating_6']??0,
$_POST['rating_7']??0,
$_POST['rating_8']??0
];

$total=0;
$count=0;

foreach($ratings as $r){
if($r>0){
$total+=$r;
$count++;
}
}

$avg=$count?($total/$count):0;

$stmt=$pdo->prepare("
INSERT INTO chapter_visit_reports
(report_date,chapter,location,chair,secretary,treasurer,hsc_chair,other_person,
overall_grade,bank_amount,dues_amount,loans_amount,
rating_1,rating_2,rating_3,rating_4,rating_5,rating_6,rating_7,rating_8,
rating_average,comments,visit_date,followup_dates,signature)

VALUES
(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
");

$stmt->execute([

$_POST['report_date']??null,
$_POST['chapter']??"",
$_POST['location']??"",
$_POST['chair']??"",
$_POST['secretary']??"",
$_POST['treasurer']??"",
$_POST['hsc_chair']??"",
$_POST['other_person']??"",

$_POST['overall_grade']??0,

$_POST['bank_amount']??0,
$_POST['dues_amount']??0,
$_POST['loans_amount']??0,

$_POST['rating_1']??0,
$_POST['rating_2']??0,
$_POST['rating_3']??0,
$_POST['rating_4']??0,
$_POST['rating_5']??0,
$_POST['rating_6']??0,
$_POST['rating_7']??0,
$_POST['rating_8']??0,

$avg,

$_POST['comments']??"",
$_POST['visit_date']??null,
$_POST['followup_dates']??"",
$_POST['signature']??""

]);

exit;

}

/*
LOAD HISTORY
*/

if(isset($_GET['history'])){

$rows=$pdo->query("
SELECT id,report_date
FROM chapter_visit_reports
ORDER BY report_date DESC
")->fetchAll();

echo json_encode($rows);
exit;

}

/*
LOAD RECORD
*/

if(isset($_GET['load'])){

$stmt=$pdo->prepare("SELECT * FROM chapter_visit_reports WHERE id=?");
$stmt->execute([$_GET['load']]);

echo json_encode($stmt->fetch());
exit;

}

/*
ANALYTICS
*/

$analytics=$pdo->query("
SELECT chapter,
AVG(rating_average) avg_score,
COUNT(*) visits
FROM chapter_visit_reports
GROUP BY chapter
")->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>

<title>CSC Chair Chapter Visit</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body{
background:#eee;
font-family:Arial;
}

.page{
width:8.5in;
min-height:11in;
margin:auto;
background:white;
padding:.5in;
}

.header{
display:flex;
justify-content:space-between;
align-items:center;
}

.header img{
height:70px;
}

.row{
display:flex;
gap:10px;
margin-bottom:5px;
}

.row label{
width:200px;
font-weight:bold;
}

input,textarea,select{
flex:1;
padding:4px;
}

.rating-row{
display:flex;
justify-content:space-between;
border-bottom:1px solid #aaa;
padding:5px 0;
}

canvas{
border:1px solid black;
}

.analytics{
width:8.5in;
margin:auto;
background:white;
padding:20px;
margin-top:20px;
}

</style>

</head>

<body>

<div class="page">

<div class="header">

<img src="../../images/oxford_house_logo.png">

<select id="history">
<option>History</option>
</select>

</div>

<h2 style="text-align:center">
CHAPTER VISIT REPORT
</h2>

<form id="visitForm">

<div class="row">
<label>Date</label>
<input type="date" name="report_date">
</div>

<div class="row">
<label>Chapter</label>
<input name="chapter">

<label>Location</label>
<input name="location">
</div>

<div class="row">
<label>Chair</label>
<input name="chair">

<label>Secretary</label>
<input name="secretary">
</div>

<div class="row">
<label>Treasurer</label>
<input name="treasurer">

<label>HSC Chair</label>
<input name="hsc_chair">
</div>

<div class="row">
<label>Other</label>
<input name="other_person">
</div>

<div class="row">
<label>Overall Grade</label>

<select name="overall_grade">
<option></option>
<option>1</option>
<option>2</option>
<option>3</option>
<option>4</option>
<option>5</option>
</select>
</div>

<div class="row">
<label>Bank Account</label>
<input name="bank_amount">
</div>

<div class="row">
<label>Dues Owed</label>
<input name="dues_amount">
</div>

<div class="row">
<label>Loans Out</label>
<input name="loans_amount">
</div>

<h3>Chapter Meeting</h3>

<?php

$questions=[
"Reading of 3 Chapter Principles",
"Reading of Minutes",
"Presentation of Treasurer Report",
"Presentation of Chair Report",
"Presentation of HSC Report",
"Maintains Guidelines and Traditions",
"Handling of Chapter Business / Issues",
"Organization Order and Structure"
];

foreach($questions as $i=>$q){

$n=$i+1;

echo "<div class='rating-row'>
<span>$n. $q</span>
<span>";

for($r=1;$r<=5;$r++){
echo "<label>
<input type='radio' name='rating_$n' value='$r' onchange='calcAvg()'>$r
</label>";
}

echo "</span></div>";

}

?>

<div class="row">
<label>Comments</label>
<textarea name="comments"></textarea>
</div>

<div class="row">
<label>Date of 1st Visit</label>
<input type="date" name="visit_date">
</div>

<div class="row">
<label>Followup Visits</label>
<input name="followup_dates">
</div>

<h3>Signature</h3>

<canvas id="sig" width="400" height="150"></canvas>

<input type="hidden" name="signature" id="signatureData">

<br>
<button type="button" onclick="clearSig()">Clear</button>

<br><br>

<button type="button" onclick="window.print()">Print</button>

</form>

</div>


<div class="analytics">

<h3>Chapter Performance Analytics</h3>

<canvas id="chart"></canvas>

</div>

<script>

/*
AUTOSAVE
*/

function autosave(){

let form=new FormData(document.getElementById("visitForm"))

form.append("action","autosave")

fetch("",{
method:"POST",
body:form
})

}

setInterval(autosave,3000)

/*
LOAD HISTORY
*/

fetch("?history=1")
.then(r=>r.json())
.then(data=>{

let h=document.getElementById("history")

data.forEach(row=>{
h.innerHTML+=`<option value="${row.id}">
${row.report_date}
</option>`
})

})

document.getElementById("history").onchange=function(){

fetch("?load="+this.value)
.then(r=>r.json())
.then(data=>{

for(let k in data){

let el=document.querySelector(`[name=${k}]`)

if(!el)continue

if(el.type==="radio"){

document.querySelectorAll(`[name=${k}]`).forEach(r=>{
if(r.value==data[k]) r.checked=true
})

}else{

el.value=data[k]

}

}

})

}

/*
SIGNATURE PAD
*/

let canvas=document.getElementById("sig")
let ctx=canvas.getContext("2d")
let draw=false

canvas.onmousedown=()=>draw=true
canvas.onmouseup=()=>{
draw=false
saveSig()
}

canvas.onmousemove=e=>{
if(!draw)return
ctx.lineWidth=2
ctx.lineCap="round"
ctx.lineTo(e.offsetX,e.offsetY)
ctx.stroke()
ctx.beginPath()
ctx.moveTo(e.offsetX,e.offsetY)
}

function saveSig(){
document.getElementById("signatureData").value=canvas.toDataURL()
}

function clearSig(){
ctx.clearRect(0,0,canvas.width,canvas.height)
}

/*
ANALYTICS
*/

let chartData=<?php echo json_encode($analytics); ?>;

let labels=chartData.map(x=>x.chapter)
let scores=chartData.map(x=>x.avg_score)

new Chart(document.getElementById("chart"),{

type:"bar",

data:{
labels:labels,
datasets:[{
label:"Average Chapter Score",
data:scores
}]
}

})

</script>

</body>
</html>