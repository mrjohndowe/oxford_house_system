<?php
// This PHP code is a template for a Medication Count & Daily Tracking Sheet.
// It generates an HTML document with tables for recording medication counts and daily tracking information.
header('Content-Type: text/html; charset=utf-8');
$lineCount = 25; // Number of rows for medication entries
$headerCount = 8; // Number of columns in the medication count table
$columnWidths = [5, 15, 15, 15, 5, 5, 5, 5]; // Column widths in percentage

echo '<!DOCTYPE html>';
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medication Count & Daily Tracking Sheet</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #474747;
            color: #fff;
        }
        h2, h3 {
            margin-top: 25px;
        }
        hr {
            margin: 40px 0;
        }
    </style>  
</head>
<body>


    <center><h2>Medication Count</h2></center>
    <!-- <p><strong>Date:</strong> ____________________________</p> -->
    <p><strong>Resident Name:</strong> ________________________________________________________________</p>

    <!-- <h3>Medication Count</h3> -->
    <table>
        <tr>
            <th style="border:1px solid #000; width:<?php echo $columnWidths[0]; ?>%;">Date</th>
            <th style="border:1px solid #000; width:<?php echo $columnWidths[1]; ?>%;">Medication Name</th>
            <th style="border:1px solid #000; width:<?php echo $columnWidths[2]; ?>%;">Dosage</th>
            <th style="border:1px solid #000; width:<?php echo $columnWidths[3]; ?>%;">Frequency</th>
            <th style="border:1px solid #000; width:<?php echo $columnWidths[4]; ?>%;">Previous Count</th>
            <th style="border:1px solid #000; width:<?php echo $columnWidths[5]; ?>%;">Current Count</th>
            <th style="border:1px solid #000; width:<?php echo $columnWidths[6]; ?>%;">Member Initials</th>
            <th style="border:1px solid #000; width:<?php echo $columnWidths[7]; ?>%;">Witness Initials</th>
        </tr>
    <?php
     for ($i = 0; $i < $lineCount; $i++) {
        echo '<tr>';
        for ($j = 0; $j < $headerCount; $j++) {
            echo '<td style="border:1px solid #000; ">&nbsp;</td>';
        }
        echo '</tr>';
    }
    ?>
    </table>

   