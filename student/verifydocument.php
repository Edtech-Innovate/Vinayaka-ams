<?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/header-top.php'); ?>

<?php
session_start();

$oa_number = isset($_SESSION['OA_Number']) ? $_SESSION['OA_Number'] : '';
$username  = 'Sgtu' . $oa_number;
$password  = 'Pas' . $oa_number;
?>

<style>
  .verify-card {
    max-width: 700px;
    margin: 40px auto;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    padding: 30px;
  }

  .verify-title {
    font-size: 24px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
  }

  .verify-subtitle {
    color: #666;
    margin-bottom: 25px;
  }

  .credential-box {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px 20px;
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .credential-label {
    font-size: 13px;
    color: #777;
    margin-bottom: 3px;
  }

  .credential-value {
    font-size: 16px;
    font-weight: 600;
    color: #222;
  }

  .copy-btn {
    font-size: 13px;
    padding: 6px 14px;
  }

  .verify-action {
    text-align: center;
    margin-top: 30px;
  }

  .verify-action a {
    padding: 12px 35px;
    font-size: 16px;
    border-radius: 30px;
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

      <!-- START JUMBOTRON -->
      <div class="jumbotron" data-pages="parallax">
        <div class="container-fluid sm-p-l-0 sm-p-r-0">
          <div class="inner">
            <ol class="breadcrumb">
              <li class="breadcrumb-item active">Verify Documents</li>
            </ol>
            <h3>Please verify your documents on the main website</h3>
          </div>
        </div>
      </div>
      <!-- END JUMBOTRON -->

      <!-- CONTENT -->
      <div class="container-fluid">
        <div class="verify-card">

          <div class="verify-title">Verify Your Documents</div>
          <div class="verify-subtitle">
            Use the credentials below to log in and verify your documents.
          </div>

          <!-- Username -->
          <div class="credential-box">
            <div>
              <div class="credential-label">Username</div>
              <div class="credential-value" id="usernameText">
                <?= ($username) ?>
              </div>
            </div>
            <button class="btn btn-outline-secondary copy-btn" onclick="copyText('usernameText')">
              Copy
            </button>
          </div>

          <!-- Password -->
          <div class="credential-box">
            <div>
              <div class="credential-label">Password</div>
              <div class="credential-value" id="passwordText">
                <?= ($password) ?>
              </div>
            </div>
            <button class="btn btn-outline-secondary copy-btn" onclick="copyText('passwordText')">
              Copy
            </button>
          </div>

          <!-- Action -->
          <!--<div class="verify-action">-->
          <!--  <a-->
          <!--    href="https://www.oldcode.sgtu.ac.in/manager/studentsignin"-->
          <!--    target="_blank"-->
          <!--    class="btn btn-primary"-->
          <!--  >-->
          <!--    Click here on Main Website-->
          <!--  </a>-->
          <!--</div>-->
          <div class="verify-action">
  <a
    href="https://www.oldcode.sgtu.ac.in/manager/studentsignin"
    target="_blank"
    class="btn btn-primary verify-btn"
  >
    <span class="btn-small-text">Click here to Main Website    </span><br><br>
    <!--<span class="btn-main-text">Main Website</span>-->
  </a>
</div>


        </div>
      </div>
      <!-- END CONTENT -->

    </div>
    <!-- END PAGE CONTENT -->

    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-top.php'); ?>

    <script>
      function copyText(elementId) {
        const text = document.getElementById(elementId).innerText;
        navigator.clipboard.writeText(text).then(function () {
          alert('Copied to clipboard');
        });
      }
    </script>

    <?php include($_SERVER['DOCUMENT_ROOT'] . '/includes/footer-bottom.php'); ?>

  </div>
</div>
