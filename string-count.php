<?php
$str = "Joh dikhta hai humko lagta hai, hai. Aur joh nahi dikhta humko lagta hai, nahi hai. Lekin kabhi kabhi joh dikhta hai, woh nahi hota. Aur joh nahi dikhta, woh hota hai.";
$charCount = [];
$i = 0;
while (isset($str[$i])) {
    $ch = strtolower($str[$i]);

    if ($ch != " " && $ch != "\n" && $ch != "\t") {
        if (!isset($charCount[$ch])) {
            $charCount[$ch] = 0;
        }
        $charCount[$ch]++;
    }
    $i++;
}
$output = "";
foreach ($charCount as $char => $count) {
    $output .= $char . " : " . $count . " | " . str_repeat("*", $count) . ",";
}
$output = rtrim($output, ",");

echo $output;
?>
