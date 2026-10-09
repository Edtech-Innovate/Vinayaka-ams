<?php
// Force errors to show on the screen
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Explicitly disable creating or writing to a log file
ini_set('log_errors', '0');
ini_set('error_log', '');

include($_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php');
session_start();

// Guard Gate: Only allow Administrators
if (!isset($_SESSION['Role']) || $_SESSION['Role'] !== 'Administrator') {
    header('HTTP/1.1 403 Forbidden');
    echo "<div style='font-family:Arial; padding:20px; color:#721c24; background:#f8d7da; border:1px solid #f5c6cb; border-radius:4px; max-width:600px; margin:40px auto; text-align:center;'>
            <h2>Access Denied</h2>
            <p>You do not have the required administrative privileges to access this control page.</p>
          </div>";
    exit();
}

// Fetch all active universities for the initial dropdown select component
$uni_query = $conn->query("SELECT `ID`, `Name`, `Short_Name` FROM `Universities` WHERE `Status` = 1 ORDER BY `Name`");
$universities = [];
while($row = $uni_query->fetch_assoc()) {
    $universities[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage University Permissions Matrix</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 20px; color: #333; }
        .container { max-width: 1100px; margin: 0 auto; background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #222; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        
        /* Dropdown Control Search Area styling */
        .selection-panel { background: #f8f9fa; border: 1px solid #e2e8f0; padding: 15px; border-radius: 6px; margin-bottom: 25px; display: flex; gap: 15px; align-items: center; }
        .search-select-wrapper { position: relative; width: 350px; }
        .search-input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        .dropdown-list { position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #cbd5e1; border-top: none; max-height: 200px; overflow-y: auto; z-index: 1000; display: none; border-radius: 0 0 4px 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .dropdown-item { padding: 10px; cursor: pointer; font-size: 14px; }
        .dropdown-item:hover { background-color: #f1f5f9; }
        
        .alert { padding: 12px; margin-bottom: 20px; border-radius: 4px; display: none; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        #matrix-loading { text-align: center; font-weight: bold; color: #64748b; padding: 20px; display: none; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background-color: #f1f5f9; font-weight: 600; color: #475569; }
        .badge { background: #e2e8f0; padding: 3px 8px; border-radius: 4px; font-size: 12px; color: #475569; }
        
        .status-pill { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: bold; text-align: center; margin-top: 4px; }
        .status-permitted { background-color: #d4edda; color: #155724; }
        .status-denied { background-color: #e2e3e5; color: #383d41; }
        .text-center { text-align: center; }
        
        .btn-submit { background-color: #007bff; color: white; padding: 10px 25px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; float: right; margin-top: 20px; }
        .btn-submit:hover { background-color: #0056b3; }
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>

<div class="container">
    <h2>Vinayaka Roles and Permission Control</h2>
    
    <div class="selection-panel">
        <label for="uniSearch" style="font-weight: bold; color: #334155;">Select University:</label>
        <div class="search-select-wrapper">
            <input type="text" id="uniSearch" class="search-input" placeholder="Type to filter university options..." autocomplete="off">
            <input type="hidden" id="selected_university_id" name="university_id" value="">
            <div id="uniDropdownList" class="dropdown-list">
                <?php foreach($universities as $uni): ?>
                    <div class="dropdown-item" data-id="<?= $uni['ID'] ?>" data-name="<?= htmlspecialchars($uni['Name']) ?>">
                        <?= htmlspecialchars($uni['Name']) ?> (<?= htmlspecialchars($uni['Short_Name']) ?>)
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <span id="active-selection-label" style="font-style: italic; color:#64748b;">No active selection</span>
    </div>

    <div id="response-alert" class="alert"></div>
    <div id="matrix-loading">Fetching Data...</div>

    <form id="permissionForm" style="display: none;">
        <div id="matrix-table-container"></div>
        <div class="clearfix">
            <button type="submit" class="btn-submit">Update Permission</button>
        </div>
    </form>
</div>

<script>
const searchInput = document.getElementById('uniSearch');
const dropdownList = document.getElementById('uniDropdownList');
const selectedIdInput = document.getElementById('selected_university_id');
const items = dropdownList.getElementsByClassName('dropdown-item');
const selectionLabel = document.getElementById('active-selection-label');

// Filter/Searching Logic
searchInput.addEventListener('focus', () => { dropdownList.style.display = 'block'; });
document.addEventListener('click', (e) => { if (!e.target.closest('.search-select-wrapper')) dropdownList.style.display = 'none'; });

searchInput.addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    dropdownList.style.display = 'block';
    let count = 0;
    for (let i = 0; i < items.length; i++) {
        const text = items[i].textContent || items[i].innerText;
        if (text.toLowerCase().indexOf(filter) > -1) {
            items[i].style.display = "";
            count++;
        } else {
            items[i].style.display = "none";
        }
    }
});

// Selection Option Click Handling Logic
for (let i = 0; i < items.length; i++) {
    items[i].addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');
        
        selectedIdInput.value = id;
        searchInput.value = name;
        dropdownList.style.display = 'none';
        selectionLabel.innerHTML = `Active Selection: <strong style="color:#0284c7;">ID ${id}</strong>`;
        
        // Fetch permission parameters via AJAX
        loadPermissionsMatrix(id);
    });
}

function loadPermissionsMatrix(universityId) {
    const tableContainer = document.getElementById('matrix-table-container');
    const form = document.getElementById('permissionForm');
    const loading = document.getElementById('matrix-loading');
    
    form.style.display = 'none';
    loading.style.display = 'block';
    
    fetch('/app/permission/fetch-matrix?university_id=' + universityId)
    .then(res => res.text())
    .then(html => {
        loading.style.display = 'none';
        tableContainer.innerHTML = html;
        form.style.display = 'block';
        
        // Re-apply layout event hooks to dynamically injected layout elements
        initDynamicPillToggles();
    })
    .catch(() => {
        loading.style.display = 'none';
        alert('Failed fetching access structural configuration layouts.');
    });
}

function initDynamicPillToggles() {
    document.querySelectorAll("#permissionForm input[type='checkbox']").forEach(box => {
        box.addEventListener('change', function() {
            let pill = this.nextElementSibling;
            if(this.checked) {
                pill.textContent = "Permitted (1)";
                pill.className = "status-pill status-permitted";
            } else {
                pill.textContent = "Not Permit (0)";
                pill.className = "status-pill status-denied";
            }
        });
    });
}

// Processing Updates
document.getElementById('permissionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const alertBox = document.getElementById('response-alert');
    const formData = new FormData(this);
    formData.append('university_id', selectedIdInput.value);
    
    fetch('/app/permission/update-permissions', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        alertBox.style.display = 'block';
        alertBox.textContent = data.message;
        alertBox.className = data.status === 200 ? "alert alert-success" : "alert alert-danger";
        window.scrollTo({top: 0, behavior: 'smooth'});
    })
    .catch(() => {
        alertBox.style.display = 'block';
        alertBox.className = "alert alert-danger";
        alertBox.textContent = "Communication parameters execution exception error.";
    });
});
</script>
</body>
</html>