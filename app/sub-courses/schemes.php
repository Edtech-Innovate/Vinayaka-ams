<?php
  if(isset($_GET['id'])){
    require '../../includes/db-config.php';
    $id = intval($_GET['id']);

    $sub_course = $conn->query("SELECT Sub_Courses.Name, Sub_Courses.University_ID, Sub_Courses.Min_Duration, Sub_Courses.discount, Modes.Name as Mode FROM Sub_Courses LEFT JOIN Modes ON Sub_Courses.Mode_ID = Modes.ID WHERE Sub_Courses.ID = $id");
    $sub_course = $sub_course->fetch_assoc();
    $durations = $sub_course['Min_Duration'];
    $mode = ucwords($sub_course['Mode']);
    $default_discount = isset($sub_course['discount']) ? (int)$sub_course['discount'] : 0;

    // Alloted Schemes
    $allotedSchemes = array();
    $alloted_schemes = $conn->query("SELECT Scheme_ID FROM Scheme_Sub_Courses WHERE Sub_Course_ID = $id");
    while($alloted_scheme = $alloted_schemes->fetch_assoc()){
      $allotedSchemes[] = $alloted_scheme['Scheme_ID'];
    }
  ?>
  <!-- Modal -->
  <div class="modal-header clearfix text-left">
    <button aria-label="" type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="pg-icon">close</i>
    </button>
    <h5 class="text-black font-weight-bold">Allot <span class="semi-bold">Scheme & Fee</span></h5>
  </div>
  <form role="form" id="form-allot-scheme" action="/app/sub-courses/allot-schemes" method="POST" enctype="multipart/form-data">
    <div class="modal-body">
      <?php
        $schemes = $conn->query("SELECT ID, Name, Fee_Structure FROM Schemes WHERE University_ID = ".$sub_course['University_ID']);
        while($scheme = $schemes->fetch_assoc()){ ?>
          <div class="row m-t-20">
            <div class="col-md-12">
              <div class="form-check m-t-20">
                <input type="checkbox" id="scheme_<?=$scheme['ID']?>" value="<?=$scheme['ID']?>" <?php echo in_array($scheme['ID'], $allotedSchemes) ? 'checked' : '' ?> name="schemes[]">
                <label for="scheme_<?=$scheme['ID']?>"><?=$scheme['Name']?></label>
              </div>
            </div>
          </div>
        
          <?php  
          // Fee Structure
          $feeStructureIds = implode(",", json_decode($scheme['Fee_Structure'], true));
          $structures = $conn->query("SELECT ID, Name, Fee_Applicable_ID FROM Fee_Structures WHERE Is_Constant = 1 AND ID IN ($feeStructureIds) AND University_ID = ".$sub_course['University_ID']);
          while($fee_structure = $structures->fetch_assoc()){
            
            // Flexible check: Case-insensitive and trimmed name comparison
            $fee_name = trim($fee_structure['Name']);
            $is_course_fee = (strcasecmp($fee_name, 'Course Fee') === 0);
            $is_subharti = ((int)$sub_course['University_ID'] === 3);
            $col_class = $is_course_fee ? 'col-md-4' : 'col-md-6';

            // Alloted
            $amount = "";
            $discount = $default_discount;
            $applicable = array();
            $allotedFee = $conn->query("SELECT Fee, Applicable_In FROM Fee_Constant WHERE Sub_Course_ID = $id AND Scheme_ID = ".$scheme['ID']. " AND Fee_Structure_ID =".$fee_structure['ID']);
            if($allotedFee->num_rows>0){
              $allotedFee = $allotedFee->fetch_assoc();
              $amount = $allotedFee['Fee'];
              $applicable = json_decode($allotedFee['Applicable_In'], true);
            }
            ?>
            <div class="row">
              <div class="<?=$col_class?>">
                <div class="form-group form-group-default required">
                  <label><?=$fee_structure['Name']?></label>
                  <input type="tel" name="fee[<?=$scheme['ID']?>][<?=$fee_structure['ID']?>]" class="form-control" value="<?=$amount?>" placeholder="ex: 5000" onkeypress="return isNumberKey(event)">
                </div>
              </div>

              <?php if ($is_course_fee) { ?>
                <div class="col-md-4">
                  <div class="form-group form-group-default">
                    <label style="display: flex; align-items: center; justify-content: space-between;">
                        <span>Discount</span>
                        <span class="custom-tooltip-wrapper" style="position: relative; display: inline-block;">
                            <i class="pg-icon" style="cursor: pointer; font-size: 14px; vertical-align: middle;">info</i>
                            <span class="custom-tooltip-text">Discount is only available for Subharti University</span>
                        </span>
                    </label>
                    <input type="tel" name="discount[<?=$scheme['ID']?>][<?=$fee_structure['ID']?>]" class="form-control" value="<?=$is_subharti ? $discount : 0?>" placeholder="ex: 0" <?=$is_subharti ? '' : 'readonly'?> onkeypress="return isNumberKey(event)">
                  </div>
                </div>
              <?php } ?>

              <div class="<?=$col_class?>">
                <?php if($fee_structure['Fee_Applicable_ID']==2 || $fee_structure['Fee_Applicable_ID']==3){  
                  if($fee_structure['Fee_Applicable_ID']==2){
                    $applicableArray = !empty($applicable) && array_key_exists(2, $applicable) ? $applicable[2] : array();
                    echo '<div class="form-check complete">';
                    for($i=1; $i<=$durations; $i++){ ?>  
                      <input type="checkbox" id="applicable_in_<?=$scheme['ID']?>_<?=$fee_structure['ID'].$i?>" value="<?=$i?>" <?php echo in_array($i, $applicableArray) ? 'checked' : '' ?> name="applicable_in[<?=$scheme['ID']?>][<?=$fee_structure['ID']?>][<?=$fee_structure['Fee_Applicable_ID']?>][]">
                      <label for="applicable_in_<?=$scheme['ID']?>_<?=$fee_structure['ID'].$i?>">
                        <?=$mode.' '.$i?>
                      </label>  
                    <?php }
                    echo '</div>';
                  }elseif($fee_structure['Fee_Applicable_ID']==3){
                    $applicableArray = !empty($applicable) && array_key_exists(3, $applicable) ? $applicable[3] : array();
                    ?>
                    <div class="form-group form-group-default required">
                      <label>Applicable In</label>
                      <select class="full-width" style="border: transparent;" name="applicable_in[<?=$scheme['ID']?>][<?=$fee_structure['ID']?>][<?=$fee_structure['Fee_Applicable_ID']?>][]">
                        <option value="">Choose</option>
                        <?php  
                          $admission_types = $conn->query("SELECT ID, Name FROM Admission_Types WHERE University_ID = ".$sub_course['University_ID']);
                          while($admission_type = $admission_types->fetch_assoc()){
                            $selected = in_array($admission_type['ID'], $applicableArray) ? 'selected' : '';
                            echo '<option value="'.$admission_type['ID'].'" '.$selected.'>'.$admission_type['Name'].'</option>';
                          }
                        ?>     
                      </select>
                    </div>     
                  <?php }
                } ?>
              </div>
            </div>
        <?php } ?>
      <?php } ?>
    </div>
    <div class="modal-footer clearfix text-end">
      <div class="col-md-12 m-t-10 sm-m-t-10">
         <button aria-label="" type="button" data-dismiss="modal" class=" btn btn-secondary mr-2">
         <i class="ti ti-circle-dashed-x mr-2"></i> Close</button>
         <button aria-label="" type="submit" class="btn btn-primary ">
         <i class="ti ti-circle-check mr-2"></i> Allot</button>
      </div>
    </div>
  </form>

  <style>
    .custom-tooltip-wrapper .custom-tooltip-text {
      visibility: hidden;
      width: 180px;
      background-color: #333;
      color: #fff;
      text-align: center;
      border-radius: 4px;
      padding: 5px;
      position: absolute;
      z-index: 1000;
      bottom: 125%;
      left: 50%;
      transform: translateX(-50%);
      opacity: 0;
      transition: opacity 0.3s;
      font-size: 11px;
      line-height: 1.2;
    }
    .custom-tooltip-wrapper:hover .custom-tooltip-text {
      visibility: visible;
      opacity: 1;
    }
  </style>

  <script>
    $("#form-allot-scheme").on("submit", function(e) {
      $(':input[type="submit"]').prop('disabled', true);
      var formData = new FormData(this);
      formData.append('id', '<?=$id?>');
      $.ajax({
        url: this.action,
        type: 'post',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(data) {
          if (data.status == 200) {
            $('.modal').modal('hide');
            notification('success', data.message);
            $('#sub-courses-table').DataTable().ajax.reload(null, false);
          } else {
            $(':input[type="submit"]').prop('disabled', false);
            notification('danger', data.message);
          }
        }
      });
      e.preventDefault();
    });
  </script>
<?php } ?>