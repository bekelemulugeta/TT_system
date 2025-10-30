
<?php
require_once("adminn.php");

$re = '0000-00-00';
$queryyy = "SELECT branch_name, tt, lanip, tt_reg_date 
            FROM `tt_registration` 
            WHERE tt_resolved_date = ? 
            ORDER BY tt_reg_date ASC";
$stmt = mysqli_prepare($link, $queryyy);
mysqli_stmt_bind_param($stmt, 's', $re);
mysqli_stmt_execute($stmt);
$result111 = mysqli_stmt_get_result($stmt);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Home</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="admin_home.css">
    <style>
        .ping-result {
            font-weight: bold;
        }
        .ping-btn {
            padding: 4px 8px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        .ping-btn:hover {
            background-color: #0056b3;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<?php if (mysqli_num_rows($result111) > 0): ?>
<div class="container">
 

    
      <div class="table-column">

        <h1>Overview of Active TTs</h1>
   <!-- Message box -->
<div id="tt-message" style="padding:10px; margin-bottom:10px; border-radius:5px; display:none;"></div>

<?php if (isset($success_message)): ?>
<script>
    $(document).ready(function() {
        $("#tt-message")
            .text("<?= htmlspecialchars($success_message, ENT_QUOTES); ?>")
            .css({
                "background-color": "#d4edda",
                "color": "#155724",
                "border": "1px solid #c3e6cb",
                "display": "block"
            });
    });
</script>
<?php endif; ?>

        <table id="tblData" class="table table-bordered">
            <thead>
                <tr>
                    <th>Branch Name</th>
                    <th>TT</th>
                    <th>LAN IP</th>
                    <th>TT reg. date</th>
                    <th>Days</th>
                    <th>Live</th>
                    <th>Action</th>
                    <th>Close</th>
                 
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_array($result111)):
                    $today = new DateTime();
                    $tt_date = new DateTime($row['tt_reg_date']);
                    $interval = $today->diff($tt_date);
                    $days = $interval->days;

                    $day_color = 'green';
                    if ($days >= 3 && $days < 6) {
                        $day_color = '#efcc00';
                    } elseif ($days >= 6) {
                        $day_color = 'red';
                    }
                ?>
                    <tr data-ip="<?= htmlspecialchars($row['lanip']); ?>">
                        <td><?= htmlspecialchars($row['branch_name']); ?></td>
                        <td><?= htmlspecialchars($row['tt']); ?></td>
                        <td><?= htmlspecialchars($row['lanip']); ?></td>
                        <td><?= htmlspecialchars($row['tt_reg_date']); ?></td>
                        <td style="color:<?= $day_color; ?>;"><?= $days; ?></td>
                        <td class="ping-result"></td>
                        <td>
    <div class="ping-dropdown">
        <button class="ping-btn">Ping ▼</button>
        <div class="ping-options" style="display:none; position:absolute; background:#f1f1f1; border:1px solid #ccc; z-index:100;">
            <div class="ping-option" data-bytes="32">32 bytes</div>
            <div class="ping-option" data-bytes="1450">1450 bytes</div>
            <div class="ping-option" data-bytes="2000">2000 bytes</div>
            <div class="ping-option" data-bytes="5000">5000 bytes</div>
        </div>
    </div>
</td>

<td>
<form class="close-form">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" name="tt" value="<?= htmlspecialchars($row['tt'], ENT_QUOTES, 'UTF-8') ?>">
    <button type="button" class="close-btn">Close</button>
</form>


</td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

 <!-- Div for manual Ping, Traceroute and Scan output -->
<div class="ping-column">
    <h3>Network Tools</h3>

    <!-- Input and action buttons -->
    <div style="margin-bottom:10px;">
        <input type="text" id="manual-ip" placeholder="Enter IP or Host" style="width:200px; padding:4px;">
        <select id="manual-bytes" style="padding:4px;">
            <option value="32">32 bytes</option>
            <option value="1400" selected>1400 bytes</option>
            <option value="2000">2000 bytes</option>
            <option value="5000">5000 bytes</option>
        </select>
        <button id="manual-ping-btn" class="ping-btn">Ping</button>
<button id="manual-trace-btn" style="background-color:#28a745;">Trace</button>


        <!-- NEW: Scan controls -->
<button id="manual-scan-btn" style="background-color:#6f42c1;">Scan</button>
<button id="manual-scan-stop-btn" style="background-color:#dc3545; display:none;">Stop Scan</button>
<span id="scan-hint" style="margin-left:8px; font-size:12px; color:#666;">Allowed: single IP, CIDR, or start-end (1.1.1.1-1.1.1.20). Server enforces limits.</span>
    </div>

    <!-- Output areas -->
    <div id="ping-result-content" style="margin-top:10px; font-family:monospace; white-space:pre-wrap; border:1px solid #ccc; padding:6px; min-height:100px;">
        Click a Ping button or submit an IP to see results here.
    </div>

    <div id="trace-result-content" style="margin-top:10px; font-family:monospace; white-space:pre-wrap; border:1px solid #ccc; padding:6px; min-height:100px;">
        Click the Trace button to see results here.
    </div>

    <div id="scan-result-content" style="margin-top:10px; font-family:monospace; white-space:pre-wrap; border:1px solid #ccc; padding:6px; min-height:120px;">
    Click the Scan button to see results here.
</div>

</div>






</div>


<script>
function pingAllBranches() {
    $("#tblData tbody tr").each(function(){
        var row = $(this);
        var ip = row.data("ip");
        var resultCell = row.find(".ping-result");

        $.ajax({
            url: "ping_active_tt.php",
            type: "POST",
            data: { ip: ip, quick: 1 }, // quick mode for status only
            success: function(response) {
                resultCell.text(response);
                if(response.toLowerCase() === "up"){
                    resultCell.css("color", "green");
                } else if(response.toLowerCase() === "down"){
                    resultCell.css("color", "red");
                } else {
                    resultCell.css("color", "black");
                }
            },
            error: function() {
                resultCell.text("Error");
                resultCell.css("color", "black");
            }
        });
    });
}

// Run automatic ping every 5 minutes
$(document).ready(function(){
    pingAllBranches(); // initial run
    setInterval(pingAllBranches, 300000); // 300,000 ms = 5 min
});

// Manual ping - show result in div next to the table
$(document).on("click", ".ping-btn", function(){
    var ip = $(this).closest("tr").data("ip");
    var resultDiv = $("#ping-result-content");

    resultDiv.html("<em>Starting ping to " + ip + "...</em><br>");

    // Close previous connection if exists
    if (window.pingSource) {
        window.pingSource.close();
    }

    // Create a new SSE connection
    window.pingSource = new EventSource("ping_live.php?ip=" + ip);

    window.pingSource.onmessage = function(e) {
        resultDiv.append(e.data + "<br>");
        resultDiv.scrollTop(resultDiv[0].scrollHeight); // auto-scroll
    };

    window.pingSource.onerror = function() {
        resultDiv.append("<span style='color:red;'>Ping finished .</span><br>");
        window.pingSource.close();
    };
});

// Manual ping from input field
$(document).on("click", "#manual-ping-btn", function(){
    var ip = $("#manual-ip").val().trim();
    var bytes = $("#manual-bytes").val(); // get selected bytes
    if(ip === ""){
        alert("Please enter an IP address.");
        return;
    }

    var resultDiv = $("#ping-result-content");
    resultDiv.html("<em>Starting ping to " + ip + " with " + bytes + " bytes...</em><br>");

    // Close previous SSE if exists
    if (window.pingSource) window.pingSource.close();

    // Open new SSE with bytes param
    window.pingSource = new EventSource("ping_live.php?ip=" + ip + "&bytes=" + bytes);

    window.pingSource.onmessage = function(e) {
        resultDiv.append(e.data + "<br>");
        resultDiv.scrollTop(resultDiv[0].scrollHeight);
    };

    window.pingSource.onerror = function() {
        resultDiv.append("<span style='color:red;'>Ping finished.</span><br>");
        window.pingSource.close();
    };
});






// Show/hide dropdown on hover
$(document).on("mouseenter", ".ping-dropdown", function() {
    $(this).find(".ping-options").show();
});
$(document).on("mouseleave", ".ping-dropdown", function() {
    $(this).find(".ping-options").hide();
});

// Handle byte selection and start ping
$(document).on("click", ".ping-option", function(){
    var bytes = $(this).data("bytes");
    var ip = $(this).closest("tr").data("ip");
    var resultDiv = $("#ping-result-content");

    resultDiv.html("<em>Starting ping to " + ip + " with " + bytes + " bytes...</em><br>");

    // Close previous SSE if exists
    if (window.pingSource) window.pingSource.close();

    // Open new SSE with bytes param
    window.pingSource = new EventSource("ping_live.php?ip=" + ip + "&bytes=" + bytes);

    window.pingSource.onmessage = function(e) {
        resultDiv.append(e.data + "<br>");
        resultDiv.scrollTop(resultDiv[0].scrollHeight);
    };

    window.pingSource.onerror = function() {
        resultDiv.append("<span style='color:red;'>Ping finished.</span><br>");
        window.pingSource.close();
    };
});



// Manual traceroute
$(document).on("click", "#manual-trace-btn", function(){
    var ip = $("#manual-ip").val().trim();
    var resultDiv = $("#trace-result-content");

    if(ip === ""){
        alert("Please enter an IP or hostname.");
        return;
    }

    resultDiv.html("<em>Starting traceroute to " + ip + "...</em><br>");

    // Close any previous SSE if exists
    if (window.traceSource) window.traceSource.close();

    // Open SSE connection to trace_live.php
    window.traceSource = new EventSource("trace_live.php?ip=" + encodeURIComponent(ip));

    window.traceSource.onmessage = function(e) {
        resultDiv.append(e.data + "<br>");
        resultDiv.scrollTop(resultDiv[0].scrollHeight);
    };

    window.traceSource.onerror = function() {
        resultDiv.append("<span style='color:red;'>Trace finished.</span><br>");
        window.traceSource.close();
    };
});

// Trigger traceroute on Enter key press
$("#manual-ip").on("keypress", function(e) {
    if (e.which === 13) {
        // By default, Enter triggers ping — leave ping logic as-is
        // To trigger trace on Enter, you can add a separate key combo if desired
    }
});

// Close TT via AJAX
$(document).on("click", ".close-btn", function() {
    var form = $(this).closest("form");
    var tt = form.find("input[name='tt']").val();
    var csrf = form.find("input[name='csrf_token']").val();
    var row = form.closest("tr");
    var msgBox = $("#tt-message");

    $.ajax({
        url: "close_tt.php",
        type: "POST",
        data: { tt: tt, csrf_token: csrf },
        success: function(response) {
            response = response.trim();

            if (response === "success") {
                // Remove the row
                row.fadeOut(500, function(){ $(this).remove(); });

                // Show success message
                msgBox
                    .text("TT closed successfully.")
                    .css({
                        "background-color": "#d4edda",
                        "color": "#155724",
                        "border": "1px solid #c3e6cb",
                        "display": "block"
                    });
            } else {
                // Show error message
                msgBox
                    .text(response)
                    .css({
                        "background-color": "#f8d7da",
                        "color": "#721c24",
                        "border": "1px solid #f5c6cb",
                        "display": "block"
                    });
            }

            // Hide message after 3 seconds
            setTimeout(function(){
                msgBox.fadeOut(500);
            }, 3000);
        },
        error: function() {
            msgBox
                .text("An error occurred while closing the TT.")
                .css({
                    "background-color": "#f8d7da",
                    "color": "#721c24",
                    "border": "1px solid #f5c6cb",
                    "display": "block"
                });

            // Hide message after 3 seconds
            setTimeout(function(){
                msgBox.fadeOut(500);
            }, 3000);
        }
    });
});



// Minimal client to show only UP (green) or DOWN (red)
function startMinimalScan(ip) {
    var output = $("#scan-result-content");
    output.empty();
    output.append("<div>Scanning: " + $('<div>').text(ip).html() + "</div>");

    var upCount = 0, downCount = 0, total = 0;

    // Close previous scan if exists
    if (window.minScanSource) try { window.minScanSource.close(); } catch(e){}

    // Show stop button, hide start button
    $("#manual-scan-btn").hide();
    $("#manual-scan-stop-btn").show();

    window.minScanSource = new EventSource("scan_live.php?ip=" + encodeURIComponent(ip));

    window.minScanSource.onmessage = function(e) {
        var parts = e.data.split('|');
        if (parts.length < 2) return;
        var status = parts[0];
        var host = parts[1];

        // Scan completion
        if (status === 'DONE' && host === 'scan_complete') {
            output.append("<div style='margin-top:8px;color:#666;'>Scan finished — total: " + total + ", UP: " + upCount + ", DOWN: " + downCount + "</div>");
            try { window.minScanSource.close(); } catch(e){}
            $("#manual-scan-btn").show();
            $("#manual-scan-stop-btn").hide();
            return;
        }

        total++;
        var line = $("<div>").text(host + " — " + status);
        if (status === 'UP') {
            line.css("color","green");
            upCount++;
        } else {
            line.css("color","red");
            downCount++;
        }
        output.append(line);

        // Update top summary
        $("#scan-summary").remove();
        output.prepend("<div id='scan-summary' style='font-weight:bold;margin-bottom:6px;'>Scanned: " + total + " | UP: " + upCount + " | DOWN: " + downCount + "</div>");

        // Autoscroll
        output.scrollTop(output[0].scrollHeight);
    };

    window.minScanSource.onerror = function() {
        // EventSource closed by server or network issue
        try { window.minScanSource.close(); } catch(e){}
        output.append("<div style='color:#666'>Connection closed.</div>");
        $("#manual-scan-btn").show();
        $("#manual-scan-stop-btn").hide();
    };
}

// Start scan button
$(document).on("click", "#manual-scan-btn", function(){
    var ip = $("#manual-ip").val().trim();
    if (!ip) { alert("Enter IP/CIDR/range"); return; }
    startMinimalScan(ip);
});

// Stop scan button
$(document).on("click", "#manual-scan-stop-btn", function(){
    if (window.minScanSource) {
        try { window.minScanSource.close(); } catch(e){}
        $("#scan-result-content").append("<div style='color:red;'>Scan stopped by user.</div>");
    }
    $(this).hide();
    $("#manual-scan-btn").show();
});


// Example: bind to your scan button
$(document).on("click", "#manual-scan-btn", function(){
    var ip = $("#manual-ip").val().trim();
    if (!ip) { alert("Enter IP/CIDR/range"); return; }
    startMinimalScan(ip);
});

</script>
<?php else: ?>
    <p>No Active TT</p>
<?php endif;
mysqli_close($link);
?>
</body>
</html>
