<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<style>
  /* Base Container & Card Styling */
  .custom-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #ffffff;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    overflow: hidden;
  }

  /* Search Input Styling */
  .search-container {
    position: relative;
    max-width: 300px;
  }
  
  #users-search-table {
    border-radius: 8px !important;
    border: 1px solid #cbd5e1 !important;
    padding: 8px 14px 8px 36px !important;
    font-size: 0.85rem;
    transition: all 0.2s ease;
  }

  #users-search-table:focus {
    border-color: #09473c !important;
    box-shadow: 0 0 0 3px rgba(9, 71, 60, 0.1) !important;
  }

  .search-icon-inside {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
  }

  /* Action Icon Buttons */
  .btn-icon-custom {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #475569;
    transition: all 0.2s ease;
    cursor: pointer;
  }

  .btn-icon-custom:hover {
    background: #09473c;
    color: #ffffff;
    border-color: #09473c;
  }

  /* Table Custom UI */
  .table-responsive {
    border-radius: 0 0 12px 12px;
  }

  #users-table {
    margin: 0 !important;
    width: 100% !important;
  }

  #users-table thead {
    background-color: #f1f5f9 !important;
  }

  #users-table thead tr th {
    color: #334155 !important;
    font-weight: 600 !important;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    border-bottom: 1px solid #e2e8f0 !important;
    padding: 12px 16px !important;
    white-space: nowrap;
  }

  #users-table tbody td {
    vertical-align: middle !important;
    padding: 12px 16px !important;
    font-size: 0.85rem;
    color: #334155;
    border-bottom: 1px solid #f1f5f9;
  }

  #users-table tbody tr:hover {
    background-color: #f8fafc !important;
  }

  /* Modern Allotted Universities Badges Structure */
  .uni-list-container {
    max-width: 300px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
  }

  .uni-chip {
    display: inline-flex;
    align-items: center;
    padding: 4px 9px;
    border-radius: 6px;
    font-size: 0.73rem;
    font-weight: 600;
    line-height: 1.2;
    transition: all 0.2s ease-in-out;
    border: 1px solid transparent;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    cursor: default;
  }

  .uni-chip:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.08);
  }

  /* Soft Color Palettes for Universities */
  .uni-theme-0 { background-color: #ccfbf1; color: #0f766e; border-color: #99f6e4; } /* Teal */
  .uni-theme-1 { background-color: #e0e7ff; color: #4338ca; border-color: #c7d2fe; } /* Indigo */
  .uni-theme-2 { background-color: #f3e8ff; color: #6b21a8; border-color: #e9d5ff; } /* Purple */
  .uni-theme-3 { background-color: #dcfce7; color: #15803d; border-color: #bbf7d0; } /* Emerald */
  .uni-theme-4 { background-color: #fef3c7; color: #b45309; border-color: #fde68a; } /* Amber */
  .uni-theme-5 { background-color: #ffe4e6; color: #be123c; border-color: #fecdd3; } /* Rose */

  .uni-chip-vertical {
    font-size: 0.65rem;
    font-weight: 500;
    opacity: 0.85;
    margin-left: 4px;
    padding-left: 4px;
    border-left: 1px solid currentColor;
  }

  .uni-chip-more {
    display: inline-flex;
    align-items: center;
    background-color: #f1f5f9;
    color: #0f172a;
    border: 1px solid #cbd5e1;
    font-weight: 700;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.72rem;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .uni-chip-more:hover {
    background-color: #09473c;
    color: #ffffff;
    border-color: #09473c;
    box-shadow: 0 2px 4px rgba(9, 71, 60, 0.2);
  }

  .badge-soft-unallotted {
    background-color: #fef2f2 !important;
    color: #991b1b !important;
    border: 1px solid #fecaca;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.73rem;
  }

  /* Action Buttons inside Table */
  .action-icon-btn {
    font-size: 1.15rem;
    color: #64748b;
    margin: 0 3px;
    transition: all 0.15s ease;
  }

  .action-icon-btn:hover {
    color: #09473c;
    transform: scale(1.15);
  }

  .action-icon-btn.text-danger:hover {
    color: #ef4444 !important;
  }

  /* ==========================================================================
     RESPONSIVE MODAL STYLING
     ========================================================================== */
  #allottedUnisModal .modal-dialog {
    max-width: 580px;
    width: 92%;
    margin: 1.25rem auto;
  }

  #allottedUnisModal .modal-content {
    border: none;
    border-radius: 16px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
    background: #ffffff;
    overflow: hidden;
    position: relative;
  }

  #allottedUnisModal .modal-content::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #09473c 0%, #0d6efd 100%);
  }

  #allottedUnisModal .modal-header {
    padding: 1.25rem 1.5rem 1rem 1.5rem;
    background: #ffffff;
    border-bottom: 1px solid #f1f5f9;
  }

  #allottedUnisModal .modal-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.01em;
  }

  .modal-subtitle-box {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 4px;
    flex-wrap: wrap;
  }

  .modal-subtitle-box .center-name {
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 500;
  }

  .modal-subtitle-box .count-badge {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #0f172a;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 12px;
  }

  #allottedUnisModal .btn-close-custom {
    background: #f1f5f9;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 0;
    line-height: 1;
  }

  #allottedUnisModal .btn-close-custom:hover {
    background: #e2e8f0;
    color: #0f172a;
  }

  #allottedUnisModal .modal-body {
    padding: 1.25rem 1.5rem;
    background: #f8fafc;
  }

  /* Modal Search Input Box */
  .modal-search-wrapper {
    position: relative;
    margin-bottom: 1rem;
  }

  .modal-search-wrapper input {
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    padding: 9px 36px 9px 38px;
    font-size: 0.85rem;
    background: #ffffff;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    transition: all 0.2s ease;
  }

  .modal-search-wrapper input:focus {
    border-color: #09473c;
    box-shadow: 0 0 0 3px rgba(9, 71, 60, 0.12);
    outline: none;
  }

  .modal-search-wrapper .search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 1rem;
    pointer-events: none;
  }

  .modal-search-wrapper .clear-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    cursor: pointer;
    display: none;
    font-size: 0.9rem;
  }

  .modal-search-wrapper .clear-icon:hover {
    color: #475569;
  }

  /* Dynamic Unis List Container */
  #modalUnisContainer {
    max-height: 55vh;
    overflow-y: auto;
    padding-right: 4px;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  #modalUnisContainer::-webkit-scrollbar {
    width: 5px;
  }
  #modalUnisContainer::-webkit-scrollbar-track {
    background: transparent;
  }
  #modalUnisContainer::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
  }

  /* Modern Card Item Inside Modal */
  .uni-modal-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  }

  .uni-modal-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    transform: translateY(-1px);
  }

  .uni-modal-card .left-group {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
  }

  .uni-modal-card .avatar-box {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    flex-shrink: 0;
    text-transform: uppercase;
  }

  .uni-modal-card .info-box {
    min-width: 0;
  }

  .uni-modal-card .uni-name-full {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .uni-modal-card .uni-name-short {
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 1px;
    display: block;
  }

  .modal-vertical-pill {
    background-color: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    font-size: 0.68rem;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    white-space: nowrap;
    flex-shrink: 0;
    margin-left: 8px;
  }

  /* Empty Filter State */
  .uni-modal-empty {
    text-align: center;
    padding: 2rem 1rem;
    color: #64748b;
  }

  .uni-modal-empty i {
    font-size: 2rem;
    color: #cbd5e1;
    margin-bottom: 0.5rem;
    display: block;
  }

  #allottedUnisModal .modal-footer {
    padding: 0.85rem 1.5rem;
    background: #ffffff;
    border-top: 1px solid #f1f5f9;
  }

  @media (max-width: 576px) {
    #allottedUnisModal .modal-dialog {
      margin: 0.5rem auto;
      width: 95%;
    }
    #allottedUnisModal .modal-header,
    #allottedUnisModal .modal-body,
    #allottedUnisModal .modal-footer {
      padding-left: 1rem;
      padding-right: 1rem;
    }
    .uni-modal-card {
      padding: 8px 10px;
    }
    .uni-modal-card .avatar-box {
      width: 32px;
      height: 32px;
      font-size: 0.78rem;
    }
  }
</style>

<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>

<!-- START PAGE-CONTAINER -->
<div class="page-container">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php'); ?>
  <!-- START PAGE CONTENT WRAPPER -->
  <div class="page-content-wrapper">
    <!-- START PAGE CONTENT -->
    <div class="content">
      
      <!-- HEADER & BREADCRUMB BAR -->
      <div class="container-fluid px-4 pt-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
          <ol class="breadcrumb m-0 p-0 align-items-center">
            <?php $breadcrumbs = array_filter(explode("/", $_SERVER['REQUEST_URI']));
            for ($i = 1; $i <= count($breadcrumbs); $i++) {
              if (count($breadcrumbs) == $i) : $active = "active";
                $crumb = explode("?", $breadcrumbs[$i]);
                echo '<li class="breadcrumb-item ' . $active . ' fw-semibold text-capitalize">' . $crumb[0] . '</li>';
              endif;
            }
            ?>
          </ol>

          <div class="d-flex align-items-center gap-2">
            <?php if ($_SESSION['Role'] === 'Administrator') { ?>
            <button class="btn-icon-custom" data-toggle="tooltip" title="Export Data" onclick="exportData()"> 
              <i class="ti ti-square-rounded-arrow-down fs-5"></i>
            </button>
            <?php } ?>

            <?php if (in_array($_SESSION['Role'], ['Administrator', 'University Head', 'Operations'])) { ?>
            <button class="btn-icon-custom" data-toggle="tooltip" title="Add Centre" onclick="add('center-master','lg')"> 
              <i class="ti ti-copy-plus fs-5"></i>
            </button>
            <?php } ?>
          </div>
        </div>
      </div>

      <!-- MAIN TABLE CONTAINER -->
      <div class="container-fluid px-4">
        <div class="custom-card mb-4">
          
          <!-- SEARCH CONTROLS TOP BAR -->
          <div class="p-3 bg-white border-bottom d-flex align-items-center justify-content-end">
            <div class="search-container">
              <i class="ti ti-search search-icon-inside"></i>
              <input type="text" id="users-search-table" class="form-control" placeholder="Search entries...">
            </div>
          </div>

          <!-- TABLE CONTAINER -->
          <div class="table-responsive">
            <table class="table table-hover nowrap align-middle" id="users-table">
              <thead>
                <tr>
                  <th data-orderable="false" style="width: 40px;"></th>
                  <th>Name</th>
                  <th>Username</th>
                  <th>Email</th>
                  <th>Created By</th>
                  <th data-orderable="false">Allotted Universities</th>
                  <th data-orderable="false">Password</th>
                  <th data-orderable="false">Can Create IGC(s)?</th>
                  <th data-orderable="false">Wallet Balance</th>
                  <th data-orderable="false">Status</th>
                  <th data-orderable="false">Visibility</th>
                  <th data-orderable="false">Internal ID</th>
                  <th data-orderable="false" class="text-end">Action</th>
                </tr>
              </thead>
            </table>
          </div>

        </div>
      </div>

    </div>
    <!-- END PAGE CONTENT -->

    <!-- RESPONSIVE & PROFESSIONAL ALLOTTED UNIVERSITIES MODAL -->
    <div class="modal fade" id="allottedUnisModal" tabindex="-1" role="dialog" aria-labelledby="allottedUnisModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title" id="allottedUnisModalLabel">Allotted Universities</h5>
              <div class="modal-subtitle-box">
                <span class="center-name" id="modalCenterName">Centre Details</span>
                <span class="count-badge" id="modalUniCounter">0 Allotted</span>
              </div>
            </div>
            <button type="button" class="btn-close-custom" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" onclick="closeAllottedUnisModal()">
              <i class="ti ti-x fs-6"></i>
            </button>
          </div>
          <div class="modal-body">
            <!-- Filter Search inside Modal -->
            <div class="modal-search-wrapper">
              <i class="ti ti-search search-icon"></i>
              <input type="text" id="modalUniSearch" class="form-control" placeholder="Search university or vertical..." onkeyup="filterModalUnis()">
              <i class="ti ti-x clear-icon" id="modalSearchClear" onclick="clearModalSearch()"></i>
            </div>

            <!-- Dynamic List Box -->
            <div id="modalUnisContainer">
              <!-- Dynamic items loaded here -->
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light btn-sm fw-semibold px-3 rounded-2" data-dismiss="modal" data-bs-dismiss="modal" onclick="closeAllottedUnisModal()">Close</button>
          </div>
        </div>
      </div>
    </div>
    
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
    
    <script type="text/javascript">
      window.BASE_URL = "<?= $base_url ?>";
      // Global cache for row universities
      window.rowUnisData = {};

      // Helper for hash-based deterministic color assignment
      function getUniThemeIndex(str) {
        var hash = 0;
        for (var i = 0; i < str.length; i++) {
          hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }
        return Math.abs(hash) % 6;
      }

      $(function() {

        var table = $('#users-table');
        var role = '<?=$_SESSION['Role']?>';
        var settings = {
          'processing': true,
          'serverSide': true,
          'serverMethod': 'post',
          'ajax': {
            'url': BASE_URL + '/app/center-master/server'
          },
          'columns': [
            {
              data: "Photo", width: "5%",
              "render": function(data, type, row) {
                return '<span class="thumbnail-wrapper d32 circular inline">\
                  <img src="' + data + '" alt="" data-src="' + data + '"\
                    data-src-retina="' + data + '" width="32" height="32" class="rounded-circle border">\
                </span>';
              }
            },
            {
              data: "Name", width: "15%",
              "render": function(data, type, row) {
                return '<span class="fw-semibold text-dark">' + data + '</span>';
              }
            },
            {
              data: "Code", width: "10%",
              "render": function(data, type, row) {
                return '<code class="px-2 py-1 bg-light rounded text-primary fw-bold">' + data + '</code>';
              }
            },
            {
              data: "Email", width: "10%",
            },
            {
              data: "Created_By", width: "10%",
            },
            {
              data: "Allotted_Universities", width: "20%",
              "render": function(data, type, row) {
                if (!data) {
                  return '<span class="badge-soft-unallotted">Unassigned</span>';
                }

                var unis = [];
                try {
                  if (Array.isArray(data)) {
                    unis = data;
                  } else if (typeof data === 'string') {
                    var trimmed = data.trim();
                    if (trimmed.startsWith('[') || trimmed.startsWith('{')) {
                      unis = JSON.parse(trimmed);
                    } else {
                      unis = trimmed.split(',');
                    }
                  }
                } catch (e) {
                  unis = typeof data === 'string' ? data.split(',') : [];
                }

                // Filter empty / invalid entries
                unis = unis.filter(function(u) {
                  if (!u) return false;
                  var str = (typeof u === 'object') ? (u.Name || u.Short_Name || '') : String(u);
                  str = str.trim();
                  return str !== '' && str !== 'Not Allotted';
                });

                if (unis.length === 0) {
                  return '<span class="badge-soft-unallotted">Unassigned</span>';
                }

                // Cache dataset globally for row
                window.rowUnisData[row.ID] = {
                  centerName: row.Name,
                  unis: unis
                };

                var html = '<div class="uni-list-container">';
                var maxVisible = 2;

                unis.forEach(function(uni, index) {
                  var shortName = '', fullName = '', vertical = '';
                  
                  if (typeof uni === 'object' && uni !== null) {
                    shortName = uni.Short_Name || uni.Name || 'N/A';
                    fullName = uni.Name || shortName;
                    vertical = uni.Vertical ? uni.Vertical : '';
                  } else {
                    shortName = String(uni).trim();
                    fullName = shortName;
                  }

                  var themeClass = 'uni-theme-' + getUniThemeIndex(shortName);

                  if (index < maxVisible) {
                    html += '<span class="uni-chip ' + themeClass + '" data-toggle="tooltip" title="' + fullName + '">';
                    html += shortName;
                    if (vertical) {
                      html += '<span class="uni-chip-vertical">' + vertical + '</span>';
                    }
                    html += '</span>';
                  }
                });

                if (unis.length > maxVisible) {
                  var remaining = unis.length - maxVisible;
                  html += '<span class="uni-chip-more btn-show-all-unis" data-row-id="' + row.ID + '">+' + remaining + ' more</span>';
                }

                html += '</div>';
                return html;
              }
            },
            {
              data: "Password", width: "10%",
              "render": function(data, type, row) {
                return '<div class="d-flex align-items-center bg-light px-2 py-1 rounded border" style="width:160px;">\
                  <input type="password" class="form-control border-0 bg-transparent p-0 shadow-none fs-12" readonly value="' + data + '" id="myInput' + row.ID + '">\
                  <i class="uil uil-eye cursor-pointer text-secondary ms-1" onclick="showPassword(' + row.ID + ')"></i>\
                </div>';
              }
            },
            {
              data: "CanCreateSubCenter", width: "10%",
              "render": function(data, type, row) {
                var active = data == 1 ? 'Yes' : 'No';
                var checked = data == 1 ? 'checked' : '';
                if (role !== 'Counsellor' && role !== 'Sub-Counsellor') { 
                  return '<div class="form-check form-check-inline switch switch-lg success m-0">\
                    <input onclick="changeSubCenterStatus(\'' + row.ID + '\')" type="checkbox" ' + checked + ' id="sub-center-switch-' + row.ID + '">\
                    <label for="sub-center-switch-' + row.ID + '">' + active + '</label>\
                  </div>';
                } else {
                  return '<label for="sub-center-switch-' + row.ID + '">' + active + '</label>';
                }
              }
            },
            {
              data: "totalAmount", width: "10%",
              "render": function(data) {
                return '<span class="fw-bold text-success">₹' + (data ? Number(data).toLocaleString('en-IN') : '0') + '</span>';
              }
            },
            {
              data: "Status", width: "10%",
              "render": function(data, type, row) {
                var active = data == 1 ? 'Active' : 'Inactive';
                var checked = data == 1 ? 'checked' : '';
                if (role !== 'Counsellor' && role !== 'Sub-Counsellor') { 
                  return '<div class="form-check form-check-inline switch switch-lg success m-0">\
                    <input onclick="changeStatus(\'Users\', \'' + row.ID + '\')" type="checkbox" ' + checked + ' id="status-switch-' + row.ID + '">\
                    <label for="status-switch-' + row.ID + '">' + active + '</label>\
                  </div>';
                } else {
                  return '<label for="status-switch-' + row.ID + '">' + active + '</label>';  
                }
              },
              visible: false
            },
            {
              data: "Visibility",
              width: "8%",
              "render": function(data, type, row) {
                var isVisible = (data === undefined || data === null || data == 1) ? 1 : 0;
                var active = isVisible == 1 ? 'Visible' : 'Hidden';
                var checked = isVisible == 1 ? 'checked' : '';
                
                var isAdmin = (role === 'Administrator');
                var isCreatedByAdmin = (row.Created_By && (row.Created_By.includes('VIN001') || row.Created_By.toLowerCase().includes('admin')));

                if (isAdmin && isCreatedByAdmin) {
                  return '<div class="form-check form-check-inline switch switch-lg success m-0">\
                    <input onclick="changeVisibilityStatus(\'' + row.ID + '\')" type="checkbox" ' + checked + ' id="visibility-switch-' + row.ID + '">\
                    <label for="visibility-switch-' + row.ID + '">' + active + '</label>\
                  </div>';
                } else {
                  return '<span class="badge ' + (isVisible == 1 ? 'bg-success-soft text-success' : 'bg-secondary-soft text-secondary') + '">' + active + '</span>';
                }
              },
              visible: role === 'Administrator' ? true : false
            },
            {
              data: "Internal_ID",
              "render": function (data, type, row) {
                var displayData = data ? data : '';
                var edit = '<i class="uil uil-edit ms-2 cursor-pointer action-icon-btn" title="Add Internal ID" onclick="addInternalId(\'' + row.ID + '\')"></i>';
                return '<span>' + displayData + '</span>' + edit;
              },
              visible: role == 'Administrator' ? true : false
            },
            {
  data: "ID", 
  width: "10%",
  "render": function(data, type, row) {
    if (role === 'Administrator' || role === 'Inhouse-Counsellor' || role === 'Operations' || role === 'University Head') {
      return '<div class="text-end nowrap">\
        <i class="ti ti-square-rounded-plus cursor-pointer action-icon-btn" data-toggle="tooltip" data-placement="top" title="Allot University" onclick="allot(\'' + data + '\', \'full\')"></i>\
        <i class="ti ti-edit-circle cursor-pointer action-icon-btn" data-toggle="tooltip" data-placement="top" title="Edit" onclick="edit(\'center-master\', \'' + data + '\', \'lg\')"></i>\
        <i class="ti ti-trash-x cursor-pointer action-icon-btn text-danger" data-toggle="tooltip" data-placement="top" title="Delete" onclick="destroy(\'center-master\', \'' + data + '\')"></i>\
      </div>';
    } else if (role === 'Counsellor') {
      return '<div class="text-end nowrap">\
        <i class="ti ti-square-rounded-plus cursor-pointer action-icon-btn" data-toggle="tooltip" data-placement="top" title="Allot University" onclick="allot(\'' + data + '\', \'full\')"></i>\
        <i class="ti ti-edit-circle cursor-pointer action-icon-btn" data-toggle="tooltip" data-placement="top" title="Edit" onclick="edit(\'center-master\', \'' + data + '\', \'lg\')"></i>\
      </div>';
    } else if (role === 'Sub-Counsellor') {
      return '<p class="m-0 text-center">-</p>';
    } else {
      return '<div class="text-end nowrap">\
        <i class="ti ti-edit-circle cursor-pointer action-icon-btn" data-toggle="tooltip" data-placement="top" title="Edit" onclick="edit(\'center-master\', \'' + data + '\', \'lg\')"></i>\
        <i class="ti ti-trash-x cursor-pointer action-icon-btn text-danger" data-toggle="tooltip" data-placement="top" title="Delete" onclick="destroy(\'center-master\', \'' + data + '\')"></i>\
      </div>';
    }
  }
},
          ],
          "sDom": "<t><'row p-3 align-items-center'<'col-md-6'i><'col-md-6 d-flex justify-content-end'p>>",
          "destroy": true,
          "scrollCollapse": true,
          "oLanguage": {
            "sLengthMenu": "_MENU_ ",
            "sInfo": "Showing <b>_START_ to _END_</b> of _TOTAL_ entries"
          },
          "aaSorting": [],
          "iDisplayLength": 25,
          "drawCallback": function(settings) {
            $('[data-toggle="tooltip"]').tooltip();
          },
        };

        table.dataTable(settings);

        // Search box handler
        $('#users-search-table').keyup(function() {
          table.fnFilter($(this).val());
        });

        // Event listener for modal opening
        $(document).on('click', '.btn-show-all-unis', function(e) {
          e.preventDefault();
          var rowId = $(this).attr('data-row-id');
          showAllUnisModal(rowId);
        });

      });
    </script>

    <script>
      // Display Modal with all allotted universities
      function showAllUnisModal(rowId) {
        var dataObj = window.rowUnisData ? window.rowUnisData[rowId] : null;
        if (!dataObj || !dataObj.unis) {
          console.warn('No university data found for Row ID:', rowId);
          return;
        }

        $('#modalCenterName').text(dataObj.centerName || 'Centre Details');
        $('#modalUniCounter').text(dataObj.unis.length + ' Allotted');
        $('#modalUniSearch').val('');
        $('#modalSearchClear').hide();

        var html = '';
        dataObj.unis.forEach(function(uni) {
          var shortName = '', fullName = '', vertical = 'Standard';
          
          if (typeof uni === 'object' && uni !== null) {
            shortName = uni.Short_Name || uni.Name || 'N/A';
            fullName = uni.Name || shortName;
            vertical = uni.Vertical || 'Standard';
          } else {
            shortName = String(uni).trim();
            fullName = shortName;
          }

          var themeClass = 'uni-theme-' + getUniThemeIndex(shortName);
          var initial = shortName.charAt(0).toUpperCase();

          html += '<div class="uni-modal-card search-uni-item">\
            <div class="left-group">\
              <div class="avatar-box ' + themeClass + '">' + initial + '</div>\
              <div class="info-box">\
                <p class="uni-name-full search-uni-name" title="' + fullName + '">' + fullName + '</p>\
                <span class="uni-name-short">' + shortName + '</span>\
              </div>\
            </div>\
            <span class="modal-vertical-pill">' + vertical + '</span>\
          </div>';
        });

        $('#modalUnisContainer').html(html);

        // Pure jQuery Bootstrap Modal Trigger (Compatible with BS 3 / BS 4)
        $('#allottedUnisModal').modal('show');
      }

      function closeAllottedUnisModal() {
        $('#allottedUnisModal').modal('hide');
      }

      function filterModalUnis() {
        var value = $('#modalUniSearch').val().toLowerCase().trim();
        var visibleCount = 0;

        if (value.length > 0) {
          $('#modalSearchClear').show();
        } else {
          $('#modalSearchClear').hide();
        }

        $('.search-uni-item').each(function() {
          var isMatch = $(this).text().toLowerCase().indexOf(value) > -1;
          $(this).toggle(isMatch);
          if (isMatch) visibleCount++;
        });

        $('#modalUniCounter').text(visibleCount + ' Showing');

        // Handle Empty State dynamically
        if (visibleCount === 0) {
          if ($('#modalUnisEmpty').length === 0) {
            $('#modalUnisContainer').append('<div id="modalUnisEmpty" class="uni-modal-empty"><i class="ti ti-search-off"></i><p class="m-0 fs-13">No matching universities found</p></div>');
          }
        } else {
          $('#modalUnisEmpty').remove();
        }
      }

      function clearModalSearch() {
        $('#modalUniSearch').val('');
        filterModalUnis();
      }

      function allot(id, modal) {
        $.ajax({
          url: BASE_URL + '/app/center-master/allot-university?id=' + id,
          type: 'GET',
          success: function(data) {
            $('#' + modal + '-modal-content').html(data);
            $('#' + modal + 'modal').modal('show');
          }
        });
      }

      function changeSubCenterStatus(id) {
        $.ajax({
          url: BASE_URL + '/app/center-master/sub-center-access',
          type: 'POST',
          data: { id: id },
          dataType: 'json',
          success: function(data) {
            if (data.status == 200) {
              notification('success', data.message);
              $('#users-table').DataTable().ajax.reload(null, false);
            } else {
              notification('danger', data.message);
            }
          }
        });
      }

      function changeVisibilityStatus(id) {
        var checkbox = $('#visibility-switch-' + id);
        var visibility = checkbox.is(':checked') ? 1 : 0;

        $.ajax({
          url: BASE_URL + '/app/center-master/change-visibility',
          type: 'POST',
          data: { 
            id: id, 
            Visibility: visibility 
          },
          dataType: 'json',
          success: function(data) {
            if (data.status == 200) {
              notification('success', data.message);
              $('#users-table').DataTable().ajax.reload(null, false);
            } else {
              notification('danger', data.message || 'Failed to update visibility');
              checkbox.prop('checked', !checkbox.is(':checked'));
            }
          },
          error: function() {
            notification('danger', 'An error occurred while updating visibility.');
            checkbox.prop('checked', !checkbox.is(':checked'));
          }
        });
      }

      function showPassword(id) {
        var x = document.getElementById("myInput" + id);
        if (x.type === "password") {
          x.type = "text";
        } else {
          x.type = "password";
        }
      }

      function exportData() {
        var search = $('#users-search-table').val();
        var url = search.length > 0 ? "?search=" + search : "";
        window.open('/app/center-master/export' + url);
      }
      
      function addInternalId(id) {
        $.ajax({
          url: BASE_URL + '/app/applications/internal-id/create?id=' + id,
          type: 'POST',
          data: { id: id },
          success: function (data) {
            $('#md-modal-content').html(data);
            $('#mdmodal').modal('show');
          }
        });
      }
    </script>

    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>