<?php
function custom_shuffle($array)
{
    $count = count($array);

    for ($i = 0; $i < $count; $i++) {
        // Create a pseudo-random index using microtime
        $randomIndex = (int)(microtime(true) * 1000000) % $count;

        // Swap elements
        $temp = $array[$i];
        $array[$i] = $array[$randomIndex];
        $array[$randomIndex] = $temp;

        // Tiny pause so microtime changes
        usleep(1);
    }

    return $array;
}

$arr = range(1, 50);
$shuffled = custom_shuffle($arr);

echo "<pre>";
print_r($shuffled);
echo "</pre>";
?>