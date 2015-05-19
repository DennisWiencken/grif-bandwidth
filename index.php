<!DOCTYPE html>
<html>
<head>
<title>Bandwidth usage</title>
<meta charset="utf-8">
</head>
<body>
<?php

$ip = $_SERVER['REMOTE_ADDR'];
$days = 5;

$dsn = 'mysql:dbname=pf;host=localhost';
$dbusr = 'bwmon';
$dbpw = 'VerySecretPassword';

try {
    $db = new PDO($dsn, $dbusr, $dbpw);
} catch (PDOException $e) {
    die('Database connection error: ' . $e->getMessage());
}

// Fail on DB error
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sql = 'SELECT IFNULL(SUM(outbytes)/1024/1024/1024, 0) AS bw_out, IFNULL(SUM(inbytes)/1024/1024/1024, 0) AS bw_in FROM inline_accounting WHERE ip = :ip AND firstseen >= DATE_SUB(NOW(), INTERVAL :days DAY);';

$stmt = $db->prepare($sql);

$stmt->execute(array( ':ip' => $ip, ':days' => $days));

$row = $stmt->fetch();

$in = floatval($row['bw_in']);
$out = floatval($row['bw_out']);
$total = floatval($in + $out);

$totalmegs = intval($total * 1024);

$in = round($in, 2);
$out = round($out, 2);
$total = round($total, 2);

echo '<br /><strong>Usage for IP ' . $ip . ' in the last ' . $days . ' days:</strong><br /><br />';
echo 'Outgoing traffic: ' . $out . ' GiB<br />';
echo 'Ingoing traffic : ' . $in . ' GiB<br />';
echo 'Total traffic   : ' . $total . ' GiB<br /><br />';
echo '<img src="progress.php?val=' . $totalmegs . '" alt="Progress bar" />';

?>
</body>
</html>
