<?php
function sumBetween($start, $end)
{
    // Predefined array
    $arr = [10, 20, 30, 40, 50, 60, 70, 80, 90, 100];
    if ($start <= 0 || $end <= 0) {
        return -1;
    }
    if ($start >= $end) {
        return 0;
    }
    $inArrayStart = in_array($start, $arr);
    $inArrayEnd = in_array($end, $arr);

    if (!$inArrayStart && !$inArrayEnd) {
        return 0;
    }
    $sum = 0;
    foreach ($arr as $val) {
        if ($val >= $start && $val <= $end) {
            $sum += $val;
        }
    }
    return $sum;
}

$result = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start = (int)$_POST['startNumber'];
    $end = (int)$_POST['endNumber'];
    $result = sumBetween($start, $end);
}
?>

<html>
<head>
    <title>Sum Of Two Integers</title>
</head>
<body>
<form method="post">
    <input type="number" name="startNumber" id="startNumber"
           placeholder="Start Number" required>
    <input type="number" name="endNumber" id="endNumber"
           placeholder="End Number" required>
    <button type="submit">Calculate</button>
</form>

<?php if ($result !== null): ?>
    <h3>Result: <?= $result ?></h3>
<?php endif; ?>
</body>
</html>
