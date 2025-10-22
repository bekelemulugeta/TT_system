
<?php

include_once("adminn.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Branch Status Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .down { color: red; font-weight: bold; }
        .up { color: green; }
    </style>
<link rel="stylesheet" href="branch_status.css"> 
</head>


<body>
    <div class="container">
    <h2>Branch Status Dashboard</h2>
    <table id="tblData" class="table table-bordered">
        <thead>
            <tr>
                <th>Branch Name</th>
                <th>IP</th>
                <th>Status</th>
                <th>Last Checked</th>
            </tr>
        </thead>
        <tbody id="branchTable">
            <tr><td colspan="4">Loading...</td></tr>
        </tbody>
    </table>

    <script>
        function fetchBranches() {
            fetch('ping_branches.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('branchTable');
                    tbody.innerHTML = '';
                    data.forEach(branch => {
                        const tr = document.createElement('tr');
                        const statusClass = branch.status === 'Down' ? 'down' : 'up';
                        tr.innerHTML = `
                            <td>${branch.name}</td>
                            <td>${branch.ip}</td>
                            <td class="${statusClass}">${branch.status}</td>
                            <td>${branch.last_checked}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(err => console.error('Error fetching branches:', err));
        }

        // Fetch immediately
        fetchBranches();

        // Refresh every 5 minutes
        setInterval(fetchBranches, 300000);
    </script>
</div>
</body>
</html>
