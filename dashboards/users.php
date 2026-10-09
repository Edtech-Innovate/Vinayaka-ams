<?php
// Enable PHP Error Reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Ensure session and database connection are available
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($conn)) {
    include_once '../includes/db-config.php';
}
//print_r(basename($_SERVER['PHP_SELF']) == '/dashboard.php');die;
// Handle AJAX Request for Hierarchy Data internally if called directly via endpoint or action
//if ((isset($_GET['action']) && $_GET['action'] == 'get-hierarchy.php') || ($_SERVER['PHP_SELF'] == '/dashboard.php') {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
  
    
    if (!isset($_SESSION['ID']) || !isset($_SESSION['university_id'])) {
        echo json_encode(['status' => false, 'message' => 'Unauthorized session or university ID missing']);
        exit();
    }

    $university_id = intval($_SESSION['university_id']);
    $current_user_id = intval($_SESSION['ID']);
  //print_r($university_id);die;

    // Function to find reporting chain upwards
    function getChainUpward($conn, $userId, $universityId) {
        $chain = [];
        $currentId = $userId;
        
        while ($currentId) {
            $stmt = $conn->prepare("SELECT u.ID, u.Name, u.Email, u.Mobile as Contact, u.Role, u.Photo, uu.Reporting 
                                    FROM Users u 
                                    LEFT JOIN University_User uu ON u.ID = uu.User_ID AND uu.University_ID = ? 
                                    WHERE u.ID = ?");
            if (!$stmt) {
                break;
            }
            $stmt->bind_param("ii", $universityId, $currentId);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $reporting = $row['Reporting'];
                unset($row['Reporting']);
                $chain[] = $row;
                if ($reporting == $currentId) break; 
                $currentId = $reporting;
            } else {
                break;
            }
        }
        return $chain;
    }

    // Function to fetch subordinates recursively grouped by roles
    function getSubordinates($conn, $userId, $universityId) {
        $subs = [];
        $stmt = $conn->prepare("SELECT u.ID, u.Name, u.Email, u.Mobile as Contact, u.Role, u.Photo 
                                FROM University_User uu 
                                JOIN Users u ON uu.User_ID = u.ID 
                                WHERE uu.Reporting = ? AND uu.University_ID = ?");
        if (!$stmt) {
            return $subs;
        }
        $stmt->bind_param("ii", $userId, $universityId);
        $stmt->execute();
        $res = $stmt->get_result();
        
        while ($row = $res->fetch_assoc()) {
            $row['subordinates'] = getSubordinates($conn, $row['ID'], $universityId);
            $subs[] = $row;
        }
        return $subs;
    }

    $upwardChain = getChainUpward($conn, $current_user_id, $university_id);
    $upwardChain = array_reverse($upwardChain);
    $downwardTree = getSubordinates($conn, $current_user_id, $university_id);

    echo json_encode([
        'status' => true,
        'hierarchy_path' => $upwardChain,
        'subordinates' => $downwardTree
    ]);
    exit();
//}
?>

<!-- Professional, Beautiful & Responsive Hierarchical Org Chart Interface -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.1.1/css/jquery.orgchart.min.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
<style>
  :root {
    --org-primary: #2e3f67;
    --org-secondary: #587467;
    --org-bg: #f4f6f9;
    --org-card-bg: #ffffff;
    --org-border: #dcdfe6;
    --text-dark: #2c3e50;
  }

  .hierarchy-wrapper {
    background: var(--org-bg);
    padding: 15px;
    border-radius: 12px;
  }

  .chart-container {
    height: 650px;
    border: 1px solid var(--org-border);
    border-radius: 10px;
    background: radial-gradient(circle, #ffffff 0%, #f8f9fa 100%);
    overflow: auto;
    position: relative;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
  }

  .orgchart {
    background-image: none !important;
    padding: 30px !important;
  }

  .orgchart .node {
    width: 230px !important;
    background-color: var(--org-card-bg) !important;
    border: 1px solid #c5d0de !important;
    border-radius: 10px !important;
    box-shadow: 0 6px 15px rgba(46, 63, 103, 0.08) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    padding: 0 !important;
    overflow: hidden;
  }

  .orgchart .node:hover {
    box-shadow: 0 12px 25px rgba(46, 63, 103, 0.15) !important;
    transform: translateY(-3px);
    border-color: var(--org-primary) !important;
  }

  .orgchart .node .title {
    background: linear-gradient(135deg, #2e3f67 0%, #1c2841 100%) !important;
    color: #ffffff !important;
    font-weight: 600;
    font-size: 13px;
    letter-spacing: 0.5px;
    padding: 10px 8px !important;
    border-radius: 9px 9px 0 0 !important;
    text-transform: uppercase;
    border-bottom: 2px solid rgba(255,255,255,0.1);
  }

  .orgchart .node[data-role="administrator"] .title { background: linear-gradient(135deg, #111827, #1f2937) !important; }
  .orgchart .node[data-role="university head"] .title { background: linear-gradient(135deg, #1e3a8a, #1d4ed8) !important; }
  .orgchart .node[data-role="counsellor"] .title { background: linear-gradient(135deg, #0d9488, #0f766e) !important; }
  .orgchart .node[data-role="sub-counsellor"] .title { background: linear-gradient(135deg, #4f46e5, #4338ca) !important; }
  .orgchart .node[data-role="center"] .title { background: linear-gradient(135deg, #047857, #065f46) !important; }

  .orgchart .node .content {
    border: none !important;
    padding: 12px 10px !important;
    background-color: transparent !important;
    height: auto !important;
    width: 100% !important;
  }

  .node-profile-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .node-avatar {
    width: 55px;
    height: 55px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 3px 8px rgba(0,0,0,0.12);
    margin-bottom: 8px;
    background-color: #e5e7eb;
  }

  .node-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 4px;
    width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .node-meta {
    font-size: 11.5px;
    color: #4b5563;
    width: 100%;
    line-height: 1.4;
    border-top: 1px dashed #e5e7eb;
    padding-top: 6px;
    margin-top: 4px;
    text-align: left;
  }

  .node-meta div {
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .node-meta i {
    color: var(--org-primary);
    font-size: 12px;
    flex-shrink: 0;
  }

  .orgchart .lines .downLine { background-color: #94a3b8 !important; }
  .orgchart .lines .topLine { border-top-color: #94a3b8 !important; }
  .orgchart .lines .rightLine { border-right-color: #94a3b8 !important; }
  .orgchart .lines .leftLine { border-left-color: #94a3b8 !important; }

  .chart-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    flex-wrap: wrap;
    gap: 10px;
  }
  
  .chart-legend {
    display: flex;
    gap: 12px;
    font-size: 12px;
    font-weight: 500;
    align-items: center;
    flex-wrap: wrap;
  }

  .legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
  }

  .legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
  }
</style>

<div class="row mb-3">
  <div class="col-12">
    <div class="card border-primary shadow" style="border-radius:10px;">
      <div class="card-header custom-bg-primary text-white separator custom_border_r d-flex justify-content-between align-items-center py-2">
        <h5 class="fw-bold mb-0"><i class="ti-sitemap mr-2"></i> University Institutional Hierarchy</h5>
        <span class="badge badge-light text-dark px-2 py-1 font-weight-semibold">Active Session View</span>
      </div>
      <div class="card-body hierarchy-wrapper">
        
        <div class="chart-toolbar">
          <div class="chart-legend">
            <span class="text-muted font-weight-bold"><i class="ti-info-alt mr-1"></i> Levels:</span>
            <div class="legend-item"><span class="legend-dot" style="background:#111827;"></span> Admin</div>
            <div class="legend-item"><span class="legend-dot" style="background:#1e3a8a;"></span> University Head</div>
            <div class="legend-item"><span class="legend-dot" style="background:#0d9488;"></span> Counsellor</div>
            <div class="legend-item"><span class="legend-dot" style="background:#4f46e5;"></span> Sub-Counsellor</div>
            <div class="legend-item"><span class="legend-dot" style="background:#047857;"></span> Centre</div>
          </div>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-secondary" id="btn-reset" title="Reset View"><i class="ri-refresh-line"></i> Reset View</button>
          </div>
        </div>

        <div id="chart-container" class="chart-container">
          <div class="d-flex justify-content-center align-items-center h-100 text-muted">
            <div class="spinner-border text-primary mr-2" role="status" style="width: 2rem; height: 2rem;"></div>
            <span>Building interactive hierarchy tree...</span>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Ensure BASE_URL is set globally -->
<script>
window.BASE_URL = window.BASE_URL || "<?= isset($base_url) ? $base_url : '/' ?>";
  console.log(window.BASE_URL);
</script>

<!-- Dependencies -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/orgchart/3.1.1/js/jquery.orgchart.min.js"></script>

<script>
(function() {
    function initHierarchyChart() {
        if (typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.orgchart === 'undefined') {
            setTimeout(initHierarchyChart, 100);
            return;
        }

        var $ = window.jQuery;
         window.BASE_URL = 'https://ams.vinayakacc.com/public_html/';
        
        $.ajax({
            url: window.BASE_URL + 'app/hierarchy/get-hierarchy',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    function buildNodeSource(chain, index, subordinates) {
                        if (index >= chain.length) {
                            return {
                                'name': currentUNode.Name,
                                'title': currentUNode.Role,
                                'relationship': '110',
                                'className': currentUNode.Role.toLowerCase().replace(/\s+/g, '-'),
                                'data': currentUNode,
                                'children': subordinates.map(mapSubToNode)
                            };
                        }
                        let node = chain[index];
                        return {
                            'name': node.Name,
                            'title': node.Role,
                            'relationship': '111',
                            'className': node.Role.toLowerCase().replace(/\s+/g, '-'),
                            'data': node,
                            'children': [buildNodeSource(chain, index + 1, subordinates)]
                        };
                    }

                    function mapSubToNode(sub) {
                        return {
                            'name': sub.Name,
                            'title': sub.Role,
                            'className': sub.Role.toLowerCase().replace(/\s+/g, '-'),
                            'data': sub,
                            'children': sub.subordinates && sub.subordinates.length > 0 ? sub.subordinates.map(mapSubToNode) : []
                        };
                    }

                    let chain = response.hierarchy_path;
                    let subs = response.subordinates;
                    
                    if (!chain || chain.length === 0) {
                        $('#chart-container').html('<div class="d-flex justify-content-center align-items-center h-100"><p class="text-muted font-weight-bold">No organizational mapping found for the active profile.</p></div>');
                        return;
                    }

                    let currentUNode = chain[chain.length - 1];
                    let rootData = buildNodeSource(chain, 0, subs);

                    $('#chart-container').html('');

                    let oc = $('#chart-container').orgchart({
                        'data' : rootData,
                        'nodeContent': 'title',
                        'pan': true,
                        'zoom': true,
                        'zoominLimit': 2.5,
                        'zoomoutLimit': 0.5,
                        'createNode': function($node, data) {
                            let userInfo = data.data || {};
                            let photoUrl = userInfo.Photo && userInfo.Photo.trim() !== '' ? userInfo.Photo : '/assets/img/default-user.jpg';
                            let emailText = userInfo.Email ? userInfo.Email : 'N/A';
                            let contactText = userInfo.Contact ? userInfo.Contact : 'N/A';

                            if(userInfo.Role) {
                                $node.attr('data-role', userInfo.Role.toLowerCase());
                            }

                            let customContentHtml = `
                                <div class="node-profile-wrapper">
                                    <img src="${photoUrl}" class="node-avatar" alt="Avatar" onerror="this.src='https://via.placeholder.com/55?text=User'">
                                    <div class="node-name" title="${userInfo.Name || data.name}">${userInfo.Name || data.name}</div>
                                    <div class="node-meta">
                                        <div title="Email: ${emailText}"><i class="ri-mail-line"></i> <span class="text-truncate">${emailText}</span></div>
                                        <div title="Contact: ${contactText}"><i class="ri-phone-line"></i> <span>${contactText}</span></div>
                                    </div>
                                </div>
                            `;
                            $node.find('.content').html(customContentHtml);
                        }
                    });

                    $('#btn-reset').on('click', function() {
                        oc.init({ 'pan': true, 'zoom': true });
                    });
                } else {
                    $('#chart-container').html('<div class="d-flex justify-content-center align-items-center h-100 p-4 text-center"><p class="text-danger font-weight-bold">Error: ' + (response.message || 'Failed to load institutional hierarchy data.') + '</p></div>');
                }
            },
            error: function(xhr, status, error) {
                $('#chart-container').html('<div class="d-flex justify-content-center align-items-center h-100 p-4 text-center"><p class="text-danger font-weight-bold">AJAX Error: ' + error + '</p></div>');
            }
        });
    }

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        initHierarchyChart();
    } else {
        document.addEventListener('DOMContentLoaded', initHierarchyChart);
    }
})();
</script>