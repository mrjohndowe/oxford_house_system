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
}
.logo {
    position: absolute;
    bottom: 30%;
    left: 25%;
}

</style>

<div class="viewer">

<iframe id="fileViewer"></iframe>


<div id="placeholder" class="placeholder">
    <div class="logo">
        <img src="images/oxford_house_logo.png" alt="Logo">
        <br><hr>
         Welcome to the Oxford House Document Viewer!
        <br>
        Select a document from the menu to open it here.
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

<?php include __DIR__.'/extras/footer.php'; ?>