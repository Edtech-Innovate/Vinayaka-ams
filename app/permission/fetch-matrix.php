<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
include($_SERVER['DOCUMENT_ROOT'] . '/includes/db-config.php');

$university_id = isset($_GET['university_id']) ? intval($_GET['university_id']) : 0;

if($university_id === 0) {
    echo "<p style='color:red; text-align:center;'>Invalid System University ID context parameter validation pattern.</p>";
    exit();
}

// Pull Matrix mappings parameters configuration structure properties
$pages_query = $conn->query("SELECT * FROM `Pages` ORDER BY `Type`, `Name`");
$access_query = $conn->query("SELECT * FROM `Page_Access` WHERE `University_ID` = $university_id");

$existing_access = [];
while($row = $access_query->fetch_assoc()) {
    $existing_access[$row['Page_ID']] = $row;
}
?>
<table>
    <thead>
        <tr>
            <th>Page Name</th>
            <th>Type / Slug</th>
            <th class="text-center">Inhouse Role</th>
            <th class="text-center">Center Role</th>
            <th class="text-center">Sub Center Role</th>
            <th class="text-center">Student Role</th>
        </tr>
    </thead>
    <tbody>
        <?php while($page = $pages_query->fetch_assoc()): 
            $pid = $page['ID'];
            $inh = isset($existing_access[$pid]) ? (int)$existing_access[$pid]['Inhouse'] : 0;
            $cnt = isset($existing_access[$pid]) ? (int)$existing_access[$pid]['Center'] : 0;
            $sub = isset($existing_access[$pid]) ? (int)$existing_access[$pid]['Sub_Center'] : 0;
            $std = isset($existing_access[$pid]) ? (int)$existing_access[$pid]['Student'] : 0;
        ?>
            <tr>
                <td><strong><?= htmlspecialchars($page['Name']) ?></strong></td>
                <td>
                    <span class="badge"><?= htmlspecialchars($page['Type']) ?></span><br>
                    <small style="color:#64748b; font-family:monospace;"><?= htmlspecialchars($page['Slug']) ?></small>
                </td>
                
                <td class="text-center">
                    <input type="checkbox" name="permissions[<?= $pid ?>][inh]" value="1" <?= $inh === 1 ? 'checked' : '' ?>><br>
                    <span class="status-pill <?= $inh === 1 ? 'status-permitted' : 'status-denied' ?>">
                        <?= $inh === 1 ? 'Permitted (1)' : 'Not Permit (0)' ?>
                    </span>
                </td>
                
                <td class="text-center">
                    <input type="checkbox" name="permissions[<?= $pid ?>][cnt]" value="1" <?= $cnt === 1 ? 'checked' : '' ?>><br>
                    <span class="status-pill <?= $cnt === 1 ? 'status-permitted' : 'status-denied' ?>">
                        <?= $cnt === 1 ? 'Permitted (1)' : 'Not Permit (0)' ?>
                    </span>
                </td>
                
                <td class="text-center">
                    <input type="checkbox" name="permissions[<?= $pid ?>][sub]" value="1" <?= $sub === 1 ? 'checked' : '' ?>><br>
                    <span class="status-pill <?= $sub === 1 ? 'status-permitted' : 'status-denied' ?>">
                        <?= $sub === 1 ? 'Permitted (1)' : 'Not Permit (0)' ?>
                    </span>
                </td>
                
                <td class="text-center">
                    <input type="checkbox" name="permissions[<?= $pid ?>][std]" value="1" <?= $std === 1 ? 'checked' : '' ?>><br>
                    <span class="status-pill <?= $std === 1 ? 'status-permitted' : 'status-denied' ?>">
                        <?= $std === 1 ? 'Permitted (1)' : 'Not Permit (0)' ?>
                    </span>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>