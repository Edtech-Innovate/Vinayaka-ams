<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>
<link href="/assets/plugins/bootstrap-datepicker/css/datepicker3.css" rel="stylesheet" type="text/css" media="screen">
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-bottom.php'); ?>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/menu.php'); ?>

<!-- START PAGE-CONTAINER -->
<div class="page-container">
  <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/topbar.php'); ?>
  
  <!-- START PAGE CONTENT WRAPPER -->
  <div class="page-content-wrapper">
    <!-- START PAGE CONTENT -->
    <div class="content">
      
      <!-- START JUMBOTRON -->
      <div class="jumbotron" data-pages="parallax">
        <div class="container-fluid sm-p-l-0 sm-p-r-0">
          <div class="inner">
            <!-- START BREADCRUMB -->
            <ol class="breadcrumb d-flex flex-wrap justify-content-between align-self-start">
              <?php 
              $breadcrumbs = array_filter(explode("/", $_SERVER['REQUEST_URI']));
              for ($i = 1; $i <= count($breadcrumbs); $i++) {
                if (count($breadcrumbs) == $i) : 
                  $active = "active";
                  $crumb = explode("?", $breadcrumbs[$i]);
                  echo '<li class="breadcrumb-item ' . $active . '">' . htmlspecialchars($crumb[0]) . '</li>';
                endif;
              }
              ?>
            </ol>
            <!-- END BREADCRUMB -->
          </div>
        </div>
      </div>
      <!-- END JUMBOTRON -->

      <!-- START CONTAINER FLUID -->
      <div class="container-fluid container-fixed-lg sm-p-l-10 sm-p-r-10">
        <div class="row">
          <div class="col-xl-12">
            
            <!-- BLOG POST COMPONENT -->
            <div class="card card-default p-5 shadow-sm">
              <div class="card-body">
                <div class="row">
                  <div class="col-lg-8 offset-lg-2">
                    
                    <!-- Blog Meta -->
                    <div class="text-muted small mb-2">
                      <span>Published on July 25, 2026</span> &bull; <span>By Admin</span>
                    </div>

                    <!-- Main Heading -->
                    <h1 class="display-5 font-weight-bold text-dark mb-3">
                      Vertical Head (University Head): Role Permissions & Management Guide
                    </h1>

                    <!-- Sub-heading -->
                    <h4 class="text-secondary font-weight-normal mb-4">
                      A complete reference for administrative permissions, user controls, and step-by-step assignment workflows.
                    </h4>

                    <hr class="my-4">

                    <!-- Content Sections -->
                    <h3 class="text-dark font-weight-semibold mt-4 mb-3">Role Overview</h3>
                    <p class="text-dark mb-4" style="font-size: 18px; line-height: 1.6;">
                      The <strong>Vertical Head (University Head)</strong> is a managerial role equipped with extensive supervisory permissions over subordinate staff and core student administration within assigned university verticals.
                    </p>

                    <h3 class="text-dark font-weight-semibold mt-4 mb-3">Key Permissions Summary</h3>
                    <ul class="text-dark mb-4 pl-4" style="list-style-type: disc; font-size: 18px; line-height: 1.8;">
                      <li class="mb-2"><strong>Role Creator:</strong> Admin</li>
                      <li class="mb-2"><strong>User Management (Create, Update, Delete):</strong> Counsellor, Center Master, Sub-Center, Center, Accountant, Operations, and Others</li>
                      <li class="mb-2"><strong>Student Actions:</strong> Can Punch Students (<strong>Yes</strong>), Can Edit Students (<strong>Yes</strong>)</li>
                      <li class="mb-2"><strong>Ledger Access:</strong> Center Ledger (<strong>Yes</strong>), Students Ledger (<strong>No</strong>)</li>
                    </ul>

                    <h3 class="text-dark font-weight-semibold mt-5 mb-3">
                      How to Create and Assign a Vertical Head
                    </h3>

                    <p class="text-dark mb-4" style="font-size: 18px; line-height: 1.6;">
                      Follow these simple steps to provision and assign verticals effectively within the administrative panel:
                    </p>

                    <ol class="text-dark mb-4 pl-4" style="list-style-type: decimal; font-size: 18px; line-height: 1.8;">
                      <li class="mb-2">Go to the <strong>Users panel</strong> and select the <strong>vertical-heads</strong> section.</li>
                      <li class="mb-2">Click on the <strong>`+` icon</strong> to add a new record.</li>
                      <li class="mb-2">Fill in the required details, then click on the <strong>`+` icon</strong> in the actions column to <strong>allot verticals</strong>.</li>
                      <li class="mb-2">Use the built-in interface options if you need to edit or delete existing entries as an Admin.</li>
                    </ol>

                    <blockquote class="blockquote bg-light p-4 border-left border-primary my-4">
                      <p class="mb-0 font-italic text-dark" style="font-size: 18px;">
                        "Maintaining clear role hierarchies and precise permission controls ensures operational transparency and security across all institutional branches."
                      </p>
                    </blockquote>

                  </div>
                </div>
              </div>
            </div>
            <!-- END BLOG POST COMPONENT -->

          </div>
        </div>
      </div>
      <!-- END CONTAINER FLUID -->
      
    </div>
    <!-- END PAGE CONTENT -->

    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>
  </div>
</div>
<!-- END PAGE-CONTAINER -->