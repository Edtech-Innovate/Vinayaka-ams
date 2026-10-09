<?php
if (isset($_GET['university_id'], $_GET['session_id'], $_GET['admission_type_id'], $_GET['course_id'], $_GET['center'], $_GET['sub_course_id'], $_GET['student_id'])) {
    require '../../includes/db-config.php';
    session_start();

    $university_id = intval($_GET['university_id']);
    $session_id = intval($_GET['session_id']);
    $admission_type_id = intval($_GET['admission_type_id']);
    $course_id = intval($_GET['course_id']);
    $sub_course_id = intval($_GET['sub_course_id']);
    $user_id = intval($_GET['center']);
    $student_id = intval($_GET['student_id']);
    $elective_type = $conn->query("SELECT ID, Name FROM Sub_Courses WHERE ID = $sub_course_id AND Course_ID = $course_id AND Status=1 AND Elective_Type=0");
    if ($elective_type->num_rows > 0) {
      

        $sql_query = "SELECT s.Group_ID, groups.Name AS group_name FROM Syllabi AS s LEFT JOIN groups ON s.Group_ID = groups.ID WHERE s.Course_ID = $course_id AND s.Sub_Course_ID = $sub_course_id AND s.University_ID = $university_id ORDER BY s.Name ASC";
        $results = $conn->query($sql_query);
        $groupArr = array();

        while ($result = $results->fetch_assoc()) {
            $groupArr[] = $result['Group_ID'];
        }
      

        $subjectArr = [];
        $selected_group_ids = [];
        $check_assign = $conn->query("SELECT * FROM Student_Subjects WHERE  Student_Id = $student_id");
        if ($check_assign->num_rows > 0) {
            while ($result = $check_assign->fetch_assoc()) {
                $subjectArr[] = $result["Subject_id"];
            }
            $sub_ids = implode(",", $subjectArr);
            $selected_group_ids = [];

            $get_group_sql = $conn->query("SELECT Group_ID FROM Syllabi AS s WHERE ID IN ($sub_ids)  AND s.Course_ID = $course_id AND s.Sub_Course_ID = $sub_course_id AND s.University_ID = $university_id  GROUP BY Group_ID ORDER BY s.Name ASC ");
            while ($row = $get_group_sql->fetch_assoc()) {
                $selected_group_ids[] = $row["Group_ID"];
            }
        }
        $group_ids = implode(',', array_unique($groupArr));


//AND ID != 6
        if (!empty($group_ids)) {
            $get_name = $conn->query("SELECT ID, Name FROM groups WHERE ID IN($group_ids) AND ID != 6 GROUP BY ID");
            ?>

            <h5>Subjects</h5>
            <style>
                .fw-bold {
                    font-weight: 700;
                }

                .disabled-checkbox {
                    pointer-events: none;
                    opacity: 0.6;
                }

                .error-message {
                    color: red;
                    display: none;
                }
            </style>
            <div class="row">
                <?php
                while ($row = $get_name->fetch_assoc()) {
                    $group_id = intval($row['ID']);
                    $sql_query = $conn->query("SELECT s.Name, s.Code, s.ID, s.Category, s.Group_ID, Courses.Name as Grade, CASE WHEN s.Category=0 THEN 'Compulsory' ELSE 'Elective' END AS Category FROM Syllabi AS s LEFT JOIN Courses ON s.Course_ID = Courses.ID LEFT JOIN groups ON s.Group_ID = groups.ID WHERE s.Course_ID = $course_id AND s.Sub_Course_ID = $sub_course_id AND s.University_ID = $university_id AND Group_ID = $group_id ORDER BY s.Name ASC    ");
                    $lag_subjects = array();
                    $elective_subjects = array();

                    while ($result = $sql_query->fetch_assoc()) {
                        if ($result['Category'] === 'Compulsory') {
                            $lag_subjects[$result['ID']] = $result['Name'];
                        } else {
                            $elective_subjects[$result['ID']] = $result['Name'];
                        }
                    }

                    if ($course_id == 2 && $sub_course_id == 6 && ($row['ID'] == 2 || $row['ID'] == 1)) {
                        $checked_id_val = "checked";
                    } else {
                        $checked_id_val = "";
                    }

                    ?>
                    <div class='card-box col-md-4'>

                        <input type='checkbox' name='group_id[]' <?= $checked_id_val ?> value='<?= $row['ID'] ?>' <?php if (in_array($row['ID'], $selected_group_ids)) {
                                  echo "checked";
                              } else {
                                  echo '';
                              } ?> class="group_id" />

                        <span class='fw-bold'> <?= $row['Name'] ?></span>
                        <div class='card shadow p-2 pr-6 d-flex flex-column gap-4 ele_sub'>
                            <?php if (!empty($lag_subjects)) { ?>
                                <p class='fw-bold'>Compulsory</p>
                                <?php foreach ($lag_subjects as $index => $value) {
                                    $check_assign = $conn->query("SELECT * FROM Student_Subjects WHERE Subject_id = $index AND Student_Id = $student_id");
                                    $checked_value  =(strtolower(trim($value)) =='computer fundamentals' || strtolower(trim($value)) =='english' || ($check_assign->num_rows > 0)) ? 'checked' :'';
                                    // $checked_value = ($check_assign->num_rows > 0) ? 'checked' : 'checked';
                                        // if((strtolower(trim($value)) =='computer fundamentals' || strtolower(trim($value)) =='english')){
                                        //      $disabled_checkbox  ='disabled-checkbox';
                                        // }



                                    $disabled_checkbox  =(strtolower(trim($value)) =='computer fundamentals' || strtolower(trim($value)) =='english') ? 'disabled-checkbox' :'';

                                    ?>
                                    <div>
                                        <input type='checkbox' name='language_subjects[<?= $index ?>]' value='<?= $row['ID'] ?>'
                                            <?= $checked_value ?> class="language_subject  <?= $disabled_checkbox ?>"  />
                                        <span><?= htmlspecialchars($value) ?></span>
                                    </div>
                                <?php }
                            } ?>
                            <?php if (!empty($elective_subjects)) { ?>
                                <p class='fw-bold'>Elective</p>
                                <?php foreach ($elective_subjects as $index => $val) {
                                    $check_assign = $conn->query("SELECT * FROM Student_Subjects WHERE Subject_id = $index AND Student_Id = $student_id");
                                    $checked_value = ($check_assign->num_rows > 0) ? 'checked' : '';
                                    ?>
                                    <div>
                                        <input type='checkbox' name='subjects_elective[<?= $index ?>]' class="checkoption"
                                            value='<?= $row['ID'] ?>' <?= $checked_value ?> />
                                        <span><?= $val ?></span>
                                    </div>
                                <?php }
                            } ?>
                        </div>
                    </div>
                <?php }
        }
    } else { ?>
            <div>
                <!-- <span>Subject not available!</span> -->
            </div>
        <?php } ?>
    </div>

    <script>
        $(document).ready(function () {
            $('.checkoption').on('change', function () {
                var groupId = $(this).closest('.card-box').find('input.group_id').val();
                if ($(this).is(':checked')) {
                    $('.checkoption[name="subjects_elective[' + groupId + ']"]').not(this).prop('checked', false);
                }
            });

            $('input.group_id').on('change', function () {
                var selectedGroups = $('input.group_id:checked').length;
                if (<?= $course_id ?> == 2 && <?= $sub_course_id ?> == 6) {
                    var value = 4;
                } else {
                    var value = 3;
                }
                // console.log(selectedGroups);return false;
                if (selectedGroups >= value ) {
                    $('input.group_id:not(:checked)').prop('disabled', true).closest('.card-box').addClass('disabled-checkbox');
                } else {
                    $('input.group_id').prop('disabled', false).closest('.card-box').removeClass('disabled-checkbox');
                }
            });

            $(document).ready(function () {
                $('.ele_sub').each(function () {
                    $(this).on('change', '.checkoption', function () {
                        var $group = $(this).closest('.ele_sub');
                        
                        $group.find('.checkoption').not(this).prop('checked', false);
                    });
                });
            });

        });
    </script>
    <?php
}
?>