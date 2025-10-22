<?php
include_once("config.php");

$downBranches = [];
$upBranches = [];

// Current timestamp
$lastChecked = date('Y-m-d H:i:s');

// Fetch all branches
$query = "SELECT branch_name, lanip FROM service_info ORDER BY branch_name ASC";
$result = mysqli_query($link, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $lanip = $row['lanip'];
    $branch_name = $row['branch_name'];

    // Remove /24 if exists
    $ipOnly = explode('/', $lanip)[0];

    // Split into octets
$octets = explode('.', $ipOnly);
if (count($octets) === 4) {
    // Only replace last octet if it is 0
    if ($octets[3] === '0') {
        $octets[3] = '1';
    }
    $pingIp = implode('.', $octets);
} else {
    $pingIp = $ipOnly; // fallback
}


    // Ping the branch (Windows)
    $status = null;
    exec("ping -n 1 -w 1000 $pingIp", $output, $status);
    $output = []; // clear output

    $branchData = [
        'name' => $branch_name,
        'ip' => $pingIp,
        'status' => ($status !== 0) ? 'Down' : 'Up',
        'last_checked' => $lastChecked
    ];

    if ($status !== 0) {
        $downBranches[] = $branchData;
    } else {
        $upBranches[] = $branchData;
    }
}

// Combine: down first, then up
$allBranches = array_merge($downBranches, $upBranches);

// Return JSON
header('Content-Type: application/json');
echo json_encode($allBranches);
?>
