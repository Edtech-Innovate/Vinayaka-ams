<link href="../../assets/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css" media="screen" />
<link href="../../assets/plugins/bootstrap-tag/bootstrap-tagsinput.css" rel="stylesheet" type="text/css" />

<?php require '../../includes/db-config.php'; ?>

<!-- MODAL HEADER -->
<div class="modal-header clearfix text-left"
     style="background:#f7f9fc; border-bottom:1px solid #e6e9ef; padding:18px 20px;">
  
  <button type="button" class="close" data-dismiss="modal">
    <i class="pg-icon">close</i>
  </button>

  <h5 style="font-size:18px; font-weight:600; color:#2c3e50; margin:0;">
    Add <span class="semi-bold">Subject</span>
  </h5>
</div>

<form id="form-add-sub-course" action="/app/subject/store" method="POST" enctype="multipart/form-data">

<div class="modal-body" style="padding:20px; background:#fff;">

<!-- COURSE + SUB COURSE -->
<div class="row" style="margin-bottom:15px;">

  <div class="col-md-6">
    <label style="font-weight:600; font-size:13px; color:#34495e; margin-bottom:6px; display:block;">
      Courses
    </label>

    <select id="course" name="course" class="form-control"
      style="height:42px; border:1px solid #dcdfe6; border-radius:6px; font-size:14px;"
      onfocus="this.style.borderColor='#4a90e2'; this.style.boxShadow='0 0 0 3px rgba(74,144,226,0.15)'"
      onblur="this.style.borderColor='#dcdfe6'; this.style.boxShadow='none'"
      onchange="getCourses(this.value)">

      <option value="">Choose</option>
      <?php
      $course_sql = $conn->query("SELECT ID, Name FROM Courses WHERE Status=1");
      while ($row = $course_sql->fetch_assoc()) {
      ?>
        <option value="<?= $row['ID'] ?>"><?= $row['Name'] ?></option>
      <?php } ?>
    </select>
  </div>

  <div class="col-md-6">
    <label style="font-weight:600; font-size:13px; color:#34495e; margin-bottom:6px; display:block;">
      Sub-Courses
    </label>

    <select id="sub_course" name="sub_course" class="form-control"
      style="height:42px; border:1px solid #dcdfe6; border-radius:6px; font-size:14px;"
      onfocus="this.style.borderColor='#4a90e2'; this.style.boxShadow='0 0 0 3px rgba(74,144,226,0.15)'"
      onblur="this.style.borderColor='#dcdfe6'; this.style.boxShadow='none'"
      onchange="getDuration(this.value)">

      <option value="">Choose Sub-Course</option>
    </select>
  </div>

</div>

<!-- CATEGORY -->
<div class="row" style="margin-bottom:15px;">
  <div class="col-md-12">
    <label style="font-weight:600; font-size:13px; color:#34495e; margin-bottom:6px; display:block;">
      Category
    </label>

    <select id="category" name="category" class="form-control"
      style="height:42px; border:1px solid #dcdfe6; border-radius:6px; font-size:14px;"
      onfocus="this.style.borderColor='#4a90e2'; this.style.boxShadow='0 0 0 3px rgba(74,144,226,0.15)'"
      onblur="this.style.borderColor='#dcdfe6'; this.style.boxShadow='none'">

      <option value="">Choose Category</option>
      <option value="0">Compulsory</option>
      <option value="1">Elective</option>
    </select>
  </div>
</div>

<!-- GROUP + NAME -->
<div class="row" style="margin-bottom:15px;">

  <div class="col-md-6">
    <label style="font-weight:600; font-size:13px; color:#34495e; margin-bottom:6px; display:block;">
      Group
    </label>

    <select id="group" name="group" class="form-control"
      style="height:42px; border:1px solid #dcdfe6; border-radius:6px; font-size:14px;"
      onfocus="this.style.borderColor='#4a90e2'; this.style.boxShadow='0 0 0 3px rgba(74,144,226,0.15)'"
      onblur="this.style.borderColor='#dcdfe6'; this.style.boxShadow='none'">

      <option value="">Choose Group</option>
      <?php
      $get_group = $conn->query("SELECT ID, Name FROM groups WHERE Status = 1");
      while ($row = $get_group->fetch_assoc()) {
      ?>
        <option value="<?= $row['ID'] ?>"><?= $row['Name'] ?></option>
      <?php } ?>
    </select>
  </div>

  <div class="col-md-6">
    <label style="font-weight:600; font-size:13px; color:#34495e; margin-bottom:6px; display:block;">
      Name
    </label>

    <input type="text" name="name" class="form-control"
      style="height:42px; border:1px solid #dcdfe6; border-radius:6px; font-size:14px;"
      onfocus="this.style.borderColor='#4a90e2'; this.style.boxShadow='0 0 0 3px rgba(74,144,226,0.15)'"
      onblur="this.style.borderColor='#dcdfe6'; this.style.boxShadow='none'"
      required>
  </div>

</div>

<!-- CODE + SEMESTER -->
<div class="row" style="margin-bottom:15px;">

  <div class="col-md-6">
    <label style="font-weight:600; font-size:13px; color:#34495e; margin-bottom:6px; display:block;">
      Code
    </label>

    <input type="text" name="code" class="form-control"
      style="height:42px; border:1px solid #dcdfe6; border-radius:6px; font-size:14px;"
      onfocus="this.style.borderColor='#4a90e2'; this.style.boxShadow='0 0 0 3px rgba(74,144,226,0.15)'"
      onblur="this.style.borderColor='#dcdfe6'; this.style.boxShadow='none'"
      required>
  </div>

  <div class="col-md-6">
    <label style="font-weight:600; font-size:13px; color:#34495e; margin-bottom:6px; display:block;">
      Duration
    </label>

    <select id="semester" name="semester" class="form-control"
      style="height:42px; border:1px solid #dcdfe6; border-radius:6px; font-size:14px;"
      onfocus="this.style.borderColor='#4a90e2'; this.style.boxShadow='0 0 0 3px rgba(74,144,226,0.15)'"
      onblur="this.style.borderColor='#dcdfe6'; this.style.boxShadow='none'">

      <option value="">Choose</option>
    </select>
  </div>

</div>

<!-- PAPER TYPE + CREDIT -->
<div class="row" style="margin-bottom:15px;">

  <div class="col-md-6">
    <label style="font-weight:600; font-size:13px; color:#34495e; margin-bottom:6px; display:block;">
      Paper Type
    </label>

    <select id="paper_type" name="paper_type" class="form-control"
      style="height:42px; border:1px solid #dcdfe6; border-radius:6px; font-size:14px;"
      onfocus="this.style.borderColor='#4a90e2'; this.style.boxShadow='0 0 0 3px rgba(74,144,226,0.15)'"
      onblur="this.style.borderColor='#dcdfe6'; this.style.boxShadow='none'">

      <option value="">Choose</option>
      <option value="Theory">Theory</option>
      <option value="Practical">Practical</option>
      <option value="Seminar">Seminar</option>
      <option value="Workshop">Workshop</option>
    </select>
  </div>

  <div class="col-md-6">
    <label style="font-weight:600; font-size:13px; color:#34495e; margin-bottom:6px; display:block;">
      Credit
    </label>

    <input type="number" name="credit" class="form-control"
      style="height:42px; border:1px solid #dcdfe6; border-radius:6px; font-size:14px;"
      onfocus="this.style.borderColor='#4a90e2'; this.style.boxShadow='0 0 0 3px rgba(74,144,226,0.15)'"
      onblur="this.style.borderColor='#dcdfe6'; this.style.boxShadow='none'"
      required>
  </div>

</div>

<!-- MARKS -->
<div class="row" style="margin-bottom:15px;">

  <div class="col-md-6">
    <label style="font-weight:600; font-size:13px; color:#34495e; margin-bottom:6px; display:block;">
      Min Marks
    </label>

    <input type="number" name="min_marks" class="form-control"
      style="height:42px; border:1px solid #dcdfe6; border-radius:6px; font-size:14px;"
      onfocus="this.style.borderColor='#4a90e2'; this.style.boxShadow='0 0 0 3px rgba(74,144,226,0.15)'"
      onblur="this.style.borderColor='#dcdfe6'; this.style.boxShadow='none'"
      required>
  </div>

  <div class="col-md-6">
    <label style="font-weight:600; font-size:13px; color:#34495e; margin-bottom:6px; display:block;">
      Max Marks
    </label>

    <input type="number" name="max_marks" class="form-control"
      style="height:42px; border:1px solid #dcdfe6; border-radius:6px; font-size:14px;"
      onfocus="this.style.borderColor='#4a90e2'; this.style.boxShadow='0 0 0 3px rgba(74,144,226,0.15)'"
      onblur="this.style.borderColor='#dcdfe6'; this.style.boxShadow='none'"
      required>
  </div>

</div>

</div>

<!-- FOOTER -->
<div class="modal-footer text-end"
     style="background:#f7f9fc; border-top:1px solid #e6e9ef; padding:15px 20px;">

  <button type="submit"
    class="btn btn-primary"
    style="background:#4a90e2; border:none; padding:10px 18px; border-radius:6px; font-size:14px;"
    onmouseover="this.style.background='#357bd8'"
    onmouseout="this.style.background='#4a90e2'">

    Save
  </button>

</div>

</form>

<script>
function getCourses(id) {
    $.ajax({
        url: '/app/regions/get-data?id=' + id + '&type=Course',
        type: 'GET',
        success: function (data) {
            let modal = document.getElementById('lgmodal');
            let select = modal ? modal.querySelector('#sub_course') : document.querySelector('#sub_course');
            if (select) {
                select.innerHTML = data;
                select.value = "";
            }
        }
    });
}

function getDuration(sub_course_id) {

    let modal = document.getElementById('lgmodal');
    let courseEl = modal.querySelector('#course');

    let course_id = courseEl ? courseEl.value : "";

    if (!course_id || !sub_course_id) {
        document.getElementById('semester').innerHTML = '<option value="">Choose</option>';
        return;
    }

    $.ajax({
        url: '/app/regions/get-data?course_id=' + course_id +
             '&sub_course_id=' + sub_course_id + '&type=Semester',
        type: 'GET',
        success: function (data) {
            document.getElementById('semester').innerHTML = data;
        }
    });
}
  
  $("#form-add-sub-course").on("submit", function (e) {
    e.preventDefault();

    var formData = new FormData(this);

    $.ajax({
        url: this.action,
        type: "POST",
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (data) {

            if (data.status == 200) {

                $('.modal').modal('hide');
                notification('success', data.message);

                //REDIRECT HERE
                window.location.href = "/lms-settings/subjects";

            } else {
                notification('danger', data.message);
            }
        },
        error: function () {
            notification('danger', 'Server error occurred');
        }
    });
});
</script>