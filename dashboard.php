<?php include('includes/header-top.php'); ?>
<?php include('includes/header-bottom.php'); ?>
<?php include('includes/menu.php'); ?>

<style>

/* ============================================================
   GENERAL DASHBOARD
============================================================ */

.dash-wrapper {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI",
                 Roboto, Helvetica, Arial, sans-serif;
    margin-bottom: 30px;
}

.dash-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    background: #ffffff;
    padding: 24px;
    border-radius: 14px;
    border: 1px solid #eef2f5;
    box-shadow: 0 5px 25px rgba(0,0,0,.035);
    margin-bottom: 25px;
}

.dash-welcome h2 {
    margin: 0 0 5px;
    font-size: 26px;
    font-weight: 750;
    color: #1e293b;
}

.dash-welcome p {
    margin: 0;
    color: #64748b;
    font-size: 14px;
}

.stat-banner-card {
    background: linear-gradient(135deg,#0f172a,#1e293b);
    color: #fff;
    padding: 18px 28px;
    border-radius: 12px;
    min-width: 260px;
    box-shadow: 0 12px 28px rgba(15,23,42,.15);
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.stat-banner-card .label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #94a3b8;
    font-weight: 750;
    margin-bottom: 7px;
}

.stat-banner-card .value {
    font-size: 32px;
    font-weight: 850;
    line-height: 1;
}

.grid-section-title {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #64748b;
    font-weight: 750;
    margin-bottom: 15px;
    padding-left: 5px;
}


/* ============================================================
   STUDENT GRID
============================================================ */

.dash-grid {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -10px;
}

.dash-grid-col {
    flex: 0 0 100%;
    max-width: 100%;
    padding: 0 10px;
    margin-bottom: 20px;
}

@media (min-width:576px) {
    .dash-grid-col {
        flex: 0 0 50%;
        max-width: 50%;
    }
}

@media (min-width:992px) {
    .dash-grid-col {
        flex: 0 0 33.333333%;
        max-width: 33.333333%;
    }
}

@media (min-width:1200px) {
    .dash-grid-col {
        flex: 0 0 25%;
        max-width: 25%;
    }
}

.session-card {
    position: relative;
    overflow: hidden;

    background: #fff;

    border: 1px solid #e2e8f0;

    border-radius: 14px;

    padding: 23px;

    box-shadow:
        0 5px 12px rgba(0,0,0,.025);

    transition: all .25s ease;
}

.session-card:hover {
    transform: translateY(-4px);

    border-color: #cbd5e1;

    box-shadow:
        0 16px 30px rgba(15,23,42,.08);
}

.session-card:before {
    content: "";

    position: absolute;

    left: 0;
    top: 0;
    bottom: 0;

    width: 4px;

    background:
        linear-gradient(
            180deg,
            #10cfbd,
            #079d90
        );
}

.card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 10px;
}

.card-title {
    margin: 0;

    font-size: 15px;

    font-weight: 750;

    color: #334155;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}

.card-subtitle {
    font-size: 11px;
    color: #94a3b8;
    margin: 3px 0 0;
}

.card-badge {
    flex-shrink: 0;

    font-size: 9px;

    font-weight: 750;

    background: rgba(16,207,189,.11);

    color: #079d90;

    padding: 5px 10px;

    border-radius: 20px;

    text-transform: uppercase;

    letter-spacing: .4px;
}

.card-divider {
    border-top: 1px dashed #e2e8f0;
    margin: 17px 0;
}

.card-counter {
    display: flex;
    align-items: baseline;
}

.card-num {
    font-size: 32px;

    font-weight: 850;

    color: #0f172a;

    line-height: 1;
}

.card-unit {
    font-size: 12px;

    color: #64748b;

    margin-left: 8px;
}


/* ============================================================
   HIERARCHY
============================================================ */

.my-reporting-wrapper {
    margin: 45px 0 35px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI",
                 Roboto, Helvetica, Arial, sans-serif;
}


/* HEADER */

.my-reporting-header {
    position: relative;

    overflow: hidden;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 25px;

    padding: 28px 32px;

    border-radius: 20px;

    color: #fff;

    background:
        radial-gradient(
            circle at 85% 15%,
            rgba(16,207,189,.25),
            transparent 30%
        ),
        linear-gradient(
            135deg,
            #07111f,
            #0f172a 55%,
            #172554
        );

    box-shadow:
        0 18px 45px rgba(15,23,42,.16);
}

.my-reporting-title {
    position: relative;
    z-index: 2;
}

.my-reporting-title h3 {
    margin: 0;

    color: #fff;

    font-size: 23px;

    font-weight: 800;
}

.my-reporting-title p {
    margin: 7px 0 0;

    color: #9eacbf;

    font-size: 12px;
}

.my-reporting-summary {
    position: relative;

    z-index: 2;

    display: flex;

    gap: 10px;
}

.reporting-summary-item {
    min-width: 110px;

    padding: 12px 15px;

    text-align: center;

    border-radius: 13px;

    background: rgba(255,255,255,.08);

    border: 1px solid rgba(255,255,255,.1);

    backdrop-filter: blur(8px);
}

.reporting-summary-item strong {
    display: block;

    color: #fff;

    font-size: 21px;

    font-weight: 850;
}

.reporting-summary-item span {
    display: block;

    margin-top: 5px;

    color: #9aa8bc;

    font-size: 8px;

    font-weight: 750;

    text-transform: uppercase;

    letter-spacing: .8px;
}


/* MAIN HIERARCHY CARD */

.my-reporting-card {
    margin-top: 18px;

    padding: 30px;

    background: #fff;

    border: 1px solid #e7edf3;

    border-radius: 20px;

    box-shadow:
        0 8px 30px rgba(15,23,42,.055);
}


/* SECTION */

.reporting-section-title {
    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 10px;

    margin-bottom: 24px;
}

.reporting-section-title h4 {
    display: flex;

    align-items: center;

    gap: 9px;

    margin: 0;

    color: #172033;

    font-size: 15px;

    font-weight: 800;
}

.reporting-section-title h4:before {
    content: "";

    width: 5px;

    height: 19px;

    border-radius: 5px;

    background:
        linear-gradient(
            180deg,
            #10cfbd,
            #079d90
        );
}

.reporting-section-title span {
    padding: 6px 10px;

    border-radius: 20px;

    background: #f8fafc;

    border: 1px solid #e5eaf0;

    color: #64748b;

    font-size: 8px;

    font-weight: 750;

    text-transform: uppercase;

    letter-spacing: .6px;
}


/* ============================================================
   ABOVE USER
============================================================ */

.reporting-chain {
    display: flex;

    flex-direction: column;

    align-items: center;
}

.reporting-user {
    position: relative;

    width: 100%;

    max-width: 850px;

    min-height: 82px;

    padding: 14px 18px;

    display: flex;

    align-items: center;

    gap: 14px;

    background:
        linear-gradient(
            135deg,
            #fff,
            #fbfdff
        );

    border: 1px solid #e1e8ef;

    border-radius: 16px;

    box-shadow:
        0 4px 12px rgba(15,23,42,.035);

    transition: all .25s ease;
}

.reporting-user:hover {
    transform: translateY(-3px);

    border-color: #10cfbd;

    box-shadow:
        0 14px 30px rgba(15,23,42,.09);
}

.reporting-user.current-user {
    border: 2px solid #10cfbd;

    background:
        linear-gradient(
            135deg,
            #effffb,
            #fff
        );

    box-shadow:
        0 12px 35px rgba(16,207,189,.14);
}


/* AVATAR */

.reporting-avatar {
    width: 55px;

    height: 55px;

    min-width: 55px;

    display: flex;

    align-items: center;

    justify-content: center;

    overflow: hidden;

    border-radius: 15px;

    background:
        linear-gradient(
            135deg,
            #10cfbd,
            #087f77
        );

    color: #fff;

    font-size: 16px;

    font-weight: 800;

    box-shadow:
        0 6px 15px rgba(16,207,189,.2);
}

.reporting-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}


/* INFO */

.reporting-info {
    flex: 1;

    min-width: 0;
}

.reporting-name {
    color: #172033;

    font-size: 14px;

    font-weight: 800;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}

.reporting-designation {
    margin-top: 4px;

    color: #64748b;

    font-size: 10px;
}

.reporting-meta {
    display: flex;

    align-items: center;

    flex-wrap: wrap;

    gap: 6px;

    margin-top: 6px;
}

.reporting-role {
    padding: 4px 9px;

    border-radius: 20px;

    color: #078f84;

    background: rgba(16,207,189,.1);

    border: 1px solid rgba(16,207,189,.13);

    font-size: 8px;

    font-weight: 800;
}

.reporting-level {
    color: #94a3b8;

    font-size: 8px;

    font-weight: 700;
}

.reporting-contact {
    min-width: 160px;

    text-align: right;
}

.reporting-email {
    display: block;

    max-width: 160px;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    color: #475569;

    font-size: 9px;

    text-decoration: none;
}

.reporting-mobile {
    display: block;

    margin-top: 4px;

    color: #94a3b8;

    font-size: 9px;
}

.reporting-you {
    padding: 6px 10px;

    border-radius: 20px;

    background:
        linear-gradient(
            135deg,
            #10cfbd,
            #079d90
        );

    color: #fff;

    font-size: 8px;

    font-weight: 800;

    text-transform: uppercase;

    letter-spacing: .8px;
}


/* CONNECTOR */

.reporting-connector {
    position: relative;

    width: 2px;

    height: 32px;

    background: #d8e2eb;
}

.reporting-connector:after {
    content: "";

    position: absolute;

    bottom: -1px;

    left: 50%;

    width: 7px;

    height: 7px;

    border-right: 2px solid #10cfbd;

    border-bottom: 2px solid #10cfbd;

    transform:
        translateX(-50%)
        rotate(45deg);
}


/* ============================================================
   BELOW HIERARCHY
============================================================ */

.below-hierarchy-wrapper {
    margin-top: 45px;

    padding-top: 32px;

    border-top: 1px dashed #dce4eb;
}

.hierarchy-level {
    position: relative;

    display: flex;

    flex-wrap: wrap;

    justify-content: center;

    align-items: flex-start;

    gap: 24px;

    width: 100%;

    padding-top: 30px;
}

.hierarchy-level.has-parent:before {
    content: "";

    position: absolute;

    top: 0;

    left: 10%;

    right: 10%;

    height: 2px;

    background:
        linear-gradient(
            90deg,
            transparent,
            #d8e2eb 10%,
            #d8e2eb 90%,
            transparent
        );
}

.hierarchy-node {
    position: relative;

    flex: 1 1 240px;

    max-width: 340px;

    min-width: 220px;
}

.hierarchy-level.has-parent
.hierarchy-node:before {
    content: "";

    position: absolute;

    top: -30px;

    left: 50%;

    width: 2px;

    height: 30px;

    background: #d8e2eb;

    transform: translateX(-50%);
}

.hierarchy-level.has-parent
.hierarchy-node:after {
    content: "";

    position: absolute;

    top: -33px;

    left: 50%;

    width: 7px;

    height: 7px;

    border-radius: 50%;

    background: #10cfbd;

    transform: translateX(-50%);

    box-shadow:
        0 0 0 4px rgba(16,207,189,.1);
}


/* CHILD CARD */

.below-user {
    position: relative;

    display: flex;

    align-items: center;

    gap: 11px;

    min-height: 82px;

    padding: 13px 14px;

    background: #fff;

    border: 1px solid #e2e8f0;

    border-radius: 15px;

    box-shadow:
        0 4px 12px rgba(15,23,42,.035);

    transition: all .25s ease;
}

.below-user:hover {
    transform: translateY(-4px);

    border-color: #10cfbd;

    box-shadow:
        0 15px 30px rgba(15,23,42,.09);
}

.below-user:before {
    content: "";

    position: absolute;

    top: 13px;

    bottom: 13px;

    left: 0;

    width: 3px;

    border-radius: 0 5px 5px 0;

    background:
        linear-gradient(
            180deg,
            #10cfbd,
            #079d90
        );
}


/* ROLE COLORS */

.role-accountant:before {
    background:
        linear-gradient(
            180deg,
            #6366f1,
            #818cf8
        );
}

.role-operation:before {
    background:
        linear-gradient(
            180deg,
            #10b981,
            #34d399
        );
}

.role-counsellor:before {
    background:
        linear-gradient(
            180deg,
            #f59e0b,
            #fbbf24
        );
}

.role-sub-counsellor:before {
    background:
        linear-gradient(
            180deg,
            #ec4899,
            #f472b6
        );
}


/* CHILD AVATAR */

.below-avatar {
    width: 46px;

    height: 46px;

    min-width: 46px;

    display: flex;

    align-items: center;

    justify-content: center;

    overflow: hidden;

    border-radius: 13px;

    background:
        linear-gradient(
            135deg,
            #64748b,
            #334155
        );

    color: #fff;

    font-size: 13px;

    font-weight: 800;
}

.below-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}


/* CHILD INFO */

.below-info {
    flex: 1;

    min-width: 0;
}

.below-name {
    color: #1e293b;

    font-size: 12px;

    font-weight: 800;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}

.below-designation {
    margin-top: 4px;

    color: #94a3b8;

    font-size: 9px;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;
}

.below-meta {
    display: flex;

    align-items: center;

    flex-wrap: wrap;

    gap: 5px;

    margin-top: 6px;
}

.below-role {
    padding: 4px 8px;

    border-radius: 20px;

    color: #64748b;

    background: #f1f5f9;

    border: 1px solid #e2e8f0;

    font-size: 7px;

    font-weight: 800;
}

.below-level {
    color: #b2bcc8;

    font-size: 7px;

    font-weight: 700;
}

.below-count {
    padding: 5px 8px;

    border-radius: 20px;

    color: #078f84;

    background: rgba(16,207,189,.09);

    font-size: 7px;

    font-weight: 800;

    white-space: nowrap;
}


/* EMPTY */

.reporting-empty {
    padding: 45px 20px;

    text-align: center;

    border: 1px dashed #dce4eb;

    border-radius: 15px;

    background:
        linear-gradient(
            135deg,
            #fafcff,
            #f8fafc
        );
}

.reporting-empty-icon {
    width: 58px;

    height: 58px;

    margin: 0 auto 13px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background: #f1f5f9;

    font-size: 23px;
}

.reporting-empty strong {
    display: block;

    color: #475569;

    font-size: 13px;

    font-weight: 800;
}

.reporting-empty p {
    margin: 5px 0 0;

    color: #94a3b8;

    font-size: 10px;
}


/* CONTACT */

.dash-footer-contact {
    text-align: center;

    margin-top: 40px;

    margin-bottom: 15px;

    padding-top: 20px;

    border-top: 1px dashed #e2e8f0;

    font-size: 13px;

    color: #64748b;
}

.dash-footer-contact a {
    color: #10cfbd;

    font-weight: 650;

    text-decoration: none;
}


/* ============================================================
   MOBILE
============================================================ */

@media(max-width:767px) {

    .dash-header {
        padding: 20px;
    }

    .stat-banner-card {
        width: 100%;
        min-width: 0;
    }

    .my-reporting-header {
        padding: 22px;

        flex-direction: column;

        align-items: flex-start;

        border-radius: 16px;
    }

    .my-reporting-title h3 {
        font-size: 19px;
    }

    .my-reporting-summary {
        width: 100%;
    }

    .reporting-summary-item {
        flex: 1;
        min-width: 0;
    }

    .my-reporting-card {
        padding: 20px 12px;

        border-radius: 16px;
    }

    .reporting-user {
        padding: 12px;

        min-height: 72px;
    }

    .reporting-avatar {
        width: 45px;

        height: 45px;

        min-width: 45px;
    }

    .reporting-contact {
        display: none;
    }

    .hierarchy-level {
        flex-direction: column;

        align-items: stretch;

        gap: 15px;

        padding-top: 20px;

        padding-left: 28px;
    }

    .hierarchy-level.has-parent:before {
        top: 0;

        left: 12px;

        right: auto;

        width: 2px;

        height: 100%;
    }

    .hierarchy-level.has-parent
    .hierarchy-node:before {
        top: 50%;

        left: -28px;

        width: 28px;

        height: 2px;

        transform: none;
    }

    .hierarchy-level.has-parent
    .hierarchy-node:after {
        top: calc(50% - 3px);

        left: -29px;
    }

    .hierarchy-node {
        max-width: none;

        min-width: 0;
    }

    .below-user {
        min-height: 72px;
    }

    .below-count {
        display: none;
    }
}

@media(max-width:420px) {

    .my-reporting-header {
        padding: 18px;
    }

    .my-reporting-summary {
        gap: 6px;
    }

    .reporting-summary-item {
        padding: 10px 7px;
    }

    .reporting-summary-item strong {
        font-size: 18px;
    }

    .reporting-summary-item span {
        font-size: 7px;
    }

    .reporting-you {
        display: none;
    }
}

</style>


<div class="page-container">

    <?php include('includes/topbar.php'); ?>

    <div class="page-content-wrapper">

        <div class="content">

            <div class="jumbotron" data-pages="parallax">

                <div class="container-fluid sm-p-l-0 sm-p-r-0">

                    <div class="inner">

                        <ol class="breadcrumb">

                            <li class="breadcrumb-item active">
                                Dashboard
                            </li>

                        </ol>

                    </div>

                </div>

            </div>


            <div class="container-fluid">

<?php

/* ============================================================
   STUDENT DASHBOARD
============================================================ */

if (
    isset($_SESSION['Role']) &&
    $_SESSION['Role'] == 'Student'
) {

    include('dashboards/student.php');

}


/* ============================================================
   ALL UNIVERSITY STAFF

   IMPORTANT FIX:
   Counsellor and Sub-Counsellor are now included.
============================================================ */

elseif (
    isset($_SESSION['Role']) &&
    in_array(
        strtolower(trim($_SESSION['Role'])),
        array(
            'center',
            'administrator',
            'university head',
            'counsellor',
            'sub-counsellor',
            'sub counsellor',
            'accountant',
            'operation',
            'operations'
        )
    )
) {


    /* ========================================================
       UNIVERSITY ID
    ======================================================== */

    $university_id = isset($_SESSION['university_id'])
        ? intval($_SESSION['university_id'])
        : 0;


    /* ========================================================
       CURRENT USER ID
    ======================================================== */

    $current_user_id = 0;

    if (
        isset($_SESSION['ID']) &&
        intval($_SESSION['ID']) > 0
    ) {
        $current_user_id = intval($_SESSION['ID']);
    }
    elseif (
        isset($_SESSION['User_ID']) &&
        intval($_SESSION['User_ID']) > 0
    ) {
        $current_user_id = intval($_SESSION['User_ID']);
    }


    /* ========================================================
       HIERARCHY FUNCTION & USER IDS
    ======================================================== */

    /**
     * Function to recursively fetch all subordinate User_IDs 
     * based on the University_User reporting hierarchy.
     */
    function getSubordinateUserIds($conn, $university_id, $parent_id) {
        $subordinates = [$parent_id];
        $queue = [$parent_id];

        while (!empty($queue)) {
            $current_id = array_shift($queue);
            
            $safe_uni = intval($university_id);
            $safe_curr = intval($current_id);
            $query = "SELECT User_ID FROM University_User WHERE University_ID = $safe_uni AND Reporting = $safe_curr";
            $result = mysqli_query($conn, $query);
            
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $child_id = intval($row['User_ID']);
                    if (!in_array($child_id, $subordinates)) {
                        $subordinates[] = $child_id;
                        $queue[] = $child_id;
                    }
                }
            }
        }
        return $subordinates;
    }

    $allowed_user_ids = getSubordinateUserIds($conn, $university_id, $current_user_id);
    $user_ids_string = implode(',', array_map('intval', $allowed_user_ids));


    /* ========================================================
       TOTAL STUDENTS
    ======================================================== */

    $grand_total = 0;

    if ($university_id > 0 && !empty($user_ids_string)) {

        $total_query = "
            SELECT COUNT(ID) AS Grand_Total
            FROM Students
            WHERE University_ID = $university_id
              AND Added_For IN ($user_ids_string)
        ";

        $total_result = mysqli_query(
            $conn,
            $total_query
        );

        if ($total_result) {
            $total_row = mysqli_fetch_assoc(
                $total_result
            );

            $grand_total = intval(
                $total_row['Grand_Total']
            );
        }
    }


    /* ========================================================
       SESSION-WISE STUDENTS
    ======================================================== */

    $query = "
        SELECT
            asess.ID AS Session_ID,
            asess.Name AS Session_Name,
            COUNT(s.ID) AS Total_Students
        FROM Admission_Sessions asess
        LEFT JOIN Students s
            ON s.Admission_Session_ID = asess.ID
            AND s.University_ID = asess.University_ID
            AND s.Added_For IN ($user_ids_string)
        WHERE asess.University_ID = $university_id
        GROUP BY
            asess.ID,
            asess.Name
        ORDER BY
            asess.ID DESC
    ";

    $result = mysqli_query(
        $conn,
        $query
    );

    /* ========================================================
       EXISTING DASHBOARD
    ======================================================== */

?>

<div class="dash-wrapper">

    <div class="dash-header">

        <div class="dash-welcome">

            <h2>

                Welcome back,
                <?php

                echo isset($_SESSION['Name'])
                    ? htmlspecialchars(
                        $_SESSION['Name']
                    )
                    : 'User';

                ?>

            </h2>

            <p>

                Overview of admissions,
                students and your reporting structure.

            </p>

        </div>


        <div class="stat-banner-card">

            <span class="label">
                Total Students Enrolled
            </span>

            <span class="value">

                <?php
                echo number_format(
                    $grand_total
                );
                ?>

            </span>

        </div>

    </div>


    <div class="grid-section-title">

        Session Wise Students Enrolled

    </div>


<?php

if (
    $result &&
    mysqli_num_rows($result) > 0
) {

    echo '<div class="dash-grid">';

    while (
        $row =
        mysqli_fetch_assoc($result)
    ) {

        $session_name =
            htmlspecialchars(
                $row['Session_Name']
            );

        $total_students =
            intval(
                $row['Total_Students']
            );

?>

        <div class="dash-grid-col">

            <div class="session-card">

                <div class="card-top">

                    <div style="max-width:75%;">

                        <h3
                            class="card-title"
                            title="<?php
                            echo $session_name;
                            ?>"
                        >

                            <?php
                            echo $session_name;
                            ?>

                        </h3>

                        <p class="card-subtitle">
                            Admission Intake
                        </p>

                    </div>

                    <span class="card-badge">
                        Active
                    </span>

                </div>


                <div class="card-divider"></div>


                <div class="card-counter">

                    <span class="card-num">

                        <?php
                        echo number_format(
                            $total_students
                        );
                        ?>

                    </span>

                    <span class="card-unit">
                        Students
                    </span>

                </div>

            </div>

        </div>

<?php

    }

    echo '</div>';

}
else {

?>

    <div
        style="
            background:#f8fafc;
            border:1px solid #e2e8f0;
            padding:20px;
            border-radius:10px;
            color:#64748b;
            font-size:13px;
        "
    >

        No admission sessions found for this
        university.

    </div>

<?php

}


/* ============================================================
   HIERARCHY FUNCTIONS
============================================================ */

function hierarchyInitials($name)
{
    $name = trim($name);

    if ($name == '') {
        return 'U';
    }

    $parts =
        preg_split(
            '/\s+/',
            $name
        );

    if (count($parts) == 1) {

        return strtoupper(
            substr(
                $parts[0],
                0,
                1
            )
        );
    }

    return strtoupper(
        substr(
            $parts[0],
            0,
            1
        )
        .
        substr(
            $parts[count($parts)-1],
            0,
            1
        )
    );
}


function hierarchyRoleClass($role)
{
    $role =
        strtolower(
            trim($role)
        );

    if (
        strpos(
            $role,
            'Accountant'
        ) !== false
    ) {

        return 'role-accountant';
    }

    if (
        strpos(
            $role,
            'Operation'
        ) !== false
    ) {

        return 'role-operation';
    }

    if (
        strpos(
            $role,
            'Sub-Counsellor'
        ) !== false ||
        strpos(
            $role,
            'sub counsell'
        ) !== false
    ) {

        return 'role-sub-counsellor';
    }

    if (
        strpos(
            $role,
            'Counsellor'
        ) !== false ||
        strpos(
            $role,
            'counsel'
        ) !== false
    ) {

        return 'role-counsellor';
    }

    return 'role-default';
}


/* ============================================================
   COUNT ALL DESCENDANTS
============================================================ */

function countHierarchyUsers($users)
{
    $count = 0;

    foreach ($users as $user) {

        $count++;

        if (
            !empty(
                $user['Children']
            )
        ) {

            $count +=
                countHierarchyUsers(
                    $user['Children']
                );
        }
    }

    return $count;
}


/* ============================================================
   GET CHILDREN RECURSIVELY
============================================================ */

function getChildrenRecursive(
    $user_id,
    &$all_users,
    &$children_map,
    &$visited
) {

    $result = array();

    if (
        isset(
            $visited[$user_id]
        )
    ) {

        return $result;
    }

    $visited[$user_id] = true;


    if (
        !isset(
            $children_map[$user_id]
        )
    ) {

        return $result;
    }


    foreach (
        $children_map[$user_id]
        as $child_id
    ) {

        if (
            !isset(
                $all_users[$child_id]
            )
        ) {

            continue;
        }


        if (
            isset(
                $visited[$child_id]
            )
        ) {

            continue;
        }


        $child =
            $all_users[
                $child_id
            ];


        $child['Children'] =
            getChildrenRecursive(
                $child_id,
                $all_users,
                $children_map,
                $visited
            );


        $result[] =
            $child;
    }


    return $result;
}


/* ============================================================
   LOAD ALL USERS FOR THIS UNIVERSITY
============================================================ */

$all_users = array();


if ($university_id > 0) {

    $hierarchy_query = "

        SELECT

            uu.University_ID,

            uu.User_ID,

            uu.Reporting,

            uu.Level,

            u.ID,

            u.Code,

            u.Name,

            u.Short_Name,

            u.Contact_Name,

            u.Email,

            u.Mobile,

            u.Alternate_Mobile,

            u.Designation,

            u.Role,

            u.Photo,

            u.Status

        FROM University_User uu

        INNER JOIN Users u
            ON u.ID = uu.User_ID

        WHERE
            uu.University_ID =
            $university_id

        ORDER BY

            uu.Level ASC,

            u.Name ASC

    ";


    $hierarchy_result =
        mysqli_query(
            $conn,
            $hierarchy_query
        );


    if ($hierarchy_result) {

        while (
            $row =
            mysqli_fetch_assoc(
                $hierarchy_result
            )
        ) {

            $uid =
                intval(
                    $row['User_ID']
                );

            $all_users[$uid] =
                $row;
        }
    }
}


/* ============================================================
   FALLBACK: FIND USER FROM SESSION NAME
============================================================ */

if (
    (
        $current_user_id <= 0 ||
        !isset(
            $all_users[
                $current_user_id
            ]
        )
    )
    &&
    isset($_SESSION['Name']) &&
    trim($_SESSION['Name']) != ''
) {

    $session_name =
        mysqli_real_escape_string(
            $conn,
            trim(
                $_SESSION['Name']
            )
        );


    $fallback_query = "

        SELECT uu.User_ID

        FROM University_User uu

        INNER JOIN Users u
            ON u.ID = uu.User_ID

        WHERE

            uu.University_ID =
            $university_id

            AND u.Name =
            '$session_name'

        LIMIT 1

    ";


    $fallback_result =
        mysqli_query(
            $conn,
            $fallback_query
        );


    if (
        $fallback_result &&
        mysqli_num_rows(
            $fallback_result
        ) > 0
    ) {

        $fallback_row =
            mysqli_fetch_assoc(
                $fallback_result
            );

        $current_user_id =
            intval(
                $fallback_row[
                    'User_ID'
                ]
            );
    }
}


/* ============================================================
   BUILD REPORTING MAP

   Example:

   User 10 -> Reporting 0

   User 20 -> Reporting 10

   User 30 -> Reporting 20
   User 40 -> Reporting 20
   User 50 -> Reporting 20

   30 / 40 / 50 are SAME LEVEL.
============================================================ */

$children_map = array();


foreach (
    $all_users
    as $uid => $user
) {

    $reporting_id =
        intval(
            $user['Reporting']
        );


    if (
        $reporting_id <= 0
    ) {

        continue;
    }


    if (
        !isset(
            $children_map[
                $reporting_id
            ]
        )
    ) {

        $children_map[
            $reporting_id
        ] = array();
    }


    $children_map[
        $reporting_id
    ][] = $uid;
}


/* ============================================================
   CURRENT USER
============================================================ */

$current_user =
    isset(
        $all_users[
            $current_user_id
        ]
    )
    ? $all_users[
        $current_user_id
    ]
    : null;


/* ============================================================
   GET REPORTING CHAIN ABOVE CURRENT USER
============================================================ */

$above_users = array();

$visited_parents = array();


if (
    $current_user !== null
) {

    $parent_id =
        $current_user_id;


    while (
        $parent_id > 0 &&
        isset(
            $all_users[
                $parent_id
            ]
        )
    ) {

        if (
            isset(
                $visited_parents[
                    $parent_id
                ]
            )
        ) {

            break;
        }


        $visited_parents[
            $parent_id
        ] = true;


        $above_users[] =
            $all_users[
                $parent_id
            ];


        $next_parent =
            intval(
                $all_users[
                    $parent_id
                ]['Reporting']
            );


        if (
            $next_parent <= 0
        ) {

            break;
        }


        if (
            !isset(
                $all_users[
                    $next_parent
                ]
            )
        ) {

            break;
        }


        $parent_id =
            $next_parent;
    }


    /*
     * Root -> Parent -> Current
     */

    $above_users =
        array_reverse(
            $above_users
        );
}


/* ============================================================
   GET ALL USERS BELOW CURRENT USER
============================================================ */

$below_users = array();


if (
    $current_user !== null
) {

    $visited_children = array();


    $below_users =
        getChildrenRecursive(
            $current_user_id,
            $all_users,
            $children_map,
            $visited_children
        );
}


$below_count =
    countHierarchyUsers(
        $below_users
    );


/* ============================================================
   RENDER CHILD HIERARCHY
============================================================ */

function renderBelowHierarchy(
    $nodes,
    $has_parent = false
) {

    if (
        empty($nodes)
    ) {

        return;
    }


    $classes =
        'hierarchy-level';


    if ($has_parent) {

        $classes .=
            ' has-parent';
    }


    echo '<div class="' .
         $classes .
         '">';


    foreach (
        $nodes as $node
    ) {

        $name =
            trim(
                $node['Name']
            );

        if ($name == '') {

            $name =
                'Unnamed User';
        }


        $designation =
            trim(
                $node['Designation']
            );

        if ($designation == '') {

            $designation =
                'Designation not available';
        }


        $role =
            trim(
                $node['Role']
            );

        if ($role == '') {

            $role = 'User';
        }


        $photo =
            trim(
                $node['Photo']
            );


        $level =
            intval(
                $node['Level']
            );


        $initials =
            hierarchyInitials(
                $name
            );


        $role_class =
            hierarchyRoleClass(
                $role
            );


        $children =
            isset(
                $node['Children']
            )
            ? $node['Children']
            : array();


        $direct_count =
            count(
                $children
            );


?>

<div class="hierarchy-node">

    <div
        class="
            below-user
            <?php echo $role_class; ?>
        "
    >

        <div class="below-avatar">

            <?php

            if ($photo != '') {

            ?>

                <img
                    src="<?php
                    echo htmlspecialchars(
                        $photo
                    );
                    ?>"
                    alt="<?php
                    echo htmlspecialchars(
                        $name
                    );
                    ?>"
                    onerror="
                        this.style.display='none';
                        this.parentNode.innerHTML='<?php
                        echo htmlspecialchars(
                            $initials,
                            ENT_QUOTES
                        );
                        ?>';
                    "
                >

            <?php

            }
            else {

                echo htmlspecialchars(
                    $initials
                );

            }

            ?>

        </div>


        <div class="below-info">

            <div class="below-name">

                <?php
                echo htmlspecialchars(
                    $name
                );
                ?>

            </div>


            <div class="below-designation">

                <?php
                echo htmlspecialchars(
                    $designation
                );
                ?>

            </div>


            <div class="below-meta">

                <span class="below-role">

                    <?php
                    echo htmlspecialchars(
                        $role
                    );
                    ?>

                </span>


                <?php
                if ($level > 0) {
                ?>

                    <span class="below-level">

                        Level
                        <?php echo $level; ?>

                    </span>

                <?php
                }
                ?>

            </div>

        </div>


        <?php

        if ($direct_count > 0) {

        ?>

            <span class="below-count">

                <?php
                echo $direct_count;
                ?>

                Report<?php
                echo $direct_count == 1
                    ? ''
                    : 's';
                ?>

            </span>

        <?php

        }

        ?>

    </div>


    <?php

    /*
     * Render children recursively.
     */

    if (
        !empty($children)
    ) {

        renderBelowHierarchy(
            $children,
            true
        );
    }

    ?>

</div>

<?php

    }


    echo '</div>';
}

?>


<!-- ============================================================
     REPORTING HIERARCHY
============================================================ -->

<div class="my-reporting-wrapper">


    <div class="my-reporting-header">

        <div class="my-reporting-title">

            <h3>

                <span
                    style="
                        display:inline-flex;
                        align-items:center;
                        justify-content:center;
                        width:35px;
                        height:35px;
                        margin-right:8px;
                        border-radius:10px;
                        background:rgba(16,207,189,.15);
                        color:#10cfbd;
                    "
                >
                    ⌘
                </span>

                My Reporting Structure

            </h3>


            <p>

                Your reporting line and team hierarchy

            </p>

        </div>


        <div class="my-reporting-summary">


            <div class="reporting-summary-item">

                <strong>

                    <?php
                    echo count(
                        $above_users
                    );
                    ?>

                </strong>

                <span>
                    Levels Above
                </span>

            </div>


            <div class="reporting-summary-item">

                <strong>

                    <?php
                    echo number_format(
                        $below_count
                    );
                    ?>

                </strong>

                <span>
                    Team Members
                </span>

            </div>

        </div>

    </div>


    <div class="my-reporting-card">


<?php

if (
    $current_user !== null
) {

?>


<!-- ============================================================
     REPORTING LINE
============================================================ -->

<div class="reporting-section-title">

    <h4>
        Reporting Line
    </h4>

    <span>
        Above You
    </span>

</div>


<?php

if (
    !empty(
        $above_users
    )
) {

?>

<div class="reporting-chain">

<?php

$chain_count =
    count(
        $above_users
    );


foreach (
    $above_users
    as $index => $user
) {

    $uid =
        intval(
            $user['User_ID']
        );


    $name =
        trim(
            $user['Name']
        );


    if ($name == '') {

        $name =
            'Unnamed User';
    }


    $designation =
        trim(
            $user['Designation']
        );


    if ($designation == '') {

        $designation =
            'Designation not available';
    }


    $role =
        trim(
            $user['Role']
        );


    if ($role == '') {

        $role =
            'User';
    }


    $email =
        trim(
            $user['Email']
        );


    $mobile =
        trim(
            $user['Mobile']
        );


    $photo =
        trim(
            $user['Photo']
        );


    $level =
        intval(
            $user['Level']
        );


    $initials =
        hierarchyInitials(
            $name
        );


    $is_current =
        (
            $uid ==
            $current_user_id
        );

?>

<div
    class="
        reporting-user
        <?php
        echo $is_current
            ? 'current-user'
            : '';
        ?>
    "
>

    <div class="reporting-avatar">

        <?php

        if ($photo != '') {

        ?>

            <img
                src="<?php
                echo htmlspecialchars(
                    $photo
                );
                ?>"
                alt="<?php
                echo htmlspecialchars(
                    $name
                );
                ?>"
                onerror="
                    this.style.display='none';
                    this.parentNode.innerHTML='<?php
                    echo htmlspecialchars(
                        $initials,
                        ENT_QUOTES
                    );
                    ?>';
                "
            >

        <?php

        }
        else {

            echo htmlspecialchars(
                $initials
            );

        }

        ?>

    </div>


    <div class="reporting-info">

        <div class="reporting-name">

            <?php
            echo htmlspecialchars(
                $name
            );
            ?>

        </div>


        <div class="reporting-designation">

            <?php
            echo htmlspecialchars(
                $designation
            );
            ?>

        </div>


        <div class="reporting-meta">

            <span class="reporting-role">

                <?php
                echo htmlspecialchars(
                    $role
                );
                ?>

            </span>


            <?php

            if ($level > 0) {

            ?>

                <span class="reporting-level">

                    Level
                    <?php
                    echo $level;
                    ?>

                </span>

            <?php

            }

            ?>

        </div>

    </div>


    <div class="reporting-contact">

        <?php

        if ($email != '') {

        ?>

            <a
                class="reporting-email"
                href="mailto:<?php
                echo htmlspecialchars(
                    $email
                );
                ?>"
            >

                <?php
                echo htmlspecialchars(
                    $email
                );
                ?>

            </a>

        <?php

        }


        if ($mobile != '') {

        ?>

            <span class="reporting-mobile">

                <?php
                echo htmlspecialchars(
                    $mobile
                );
                ?>

            </span>

        <?php

        }

        ?>

    </div>


    <?php

    if ($is_current) {

    ?>

        <span class="reporting-you">
            YOU
        </span>

    <?php

    }

    ?>

</div>


<?php

if (
    $index <
    $chain_count - 1
) {

?>

<div class="reporting-connector"></div>

<?php

}

?>


<?php

}

?>

</div>


<?php

}
else {

?>

<div class="reporting-empty">

    <div class="reporting-empty-icon">
        👤
    </div>

    <strong>
        You are at the top level
    </strong>

    <p>
        No reporting manager is assigned above you.
    </p>

</div>

<?php

}


/* ============================================================
   TEAM BELOW
============================================================ */

?>

<div class="below-hierarchy-wrapper">

    <div class="reporting-section-title">

        <h4>
            Team Hierarchy
        </h4>

        <span>

            <?php
            echo number_format(
                $below_count
            );
            ?>

            Members Below You

        </span>

    </div>


<?php

if (
    !empty(
        $below_users
    )
) {

    renderBelowHierarchy(
        $below_users,
        false
    );

}
else {

?>

<div class="reporting-empty">

    <div class="reporting-empty-icon">
        👥
    </div>

    <strong>
        No team members reporting to you
    </strong>

    <p>
        There are currently no users assigned below your reporting level.
    </p>

</div>

<?php

}

?>

</div>


<?php

}
else {

?>

<div class="reporting-empty">

    <div class="reporting-empty-icon">
        👤
    </div>

    <strong>
        Current user could not be identified
    </strong>

    <p>
        Please make sure the logged-in user's ID is stored in
        <strong>$_SESSION['ID']</strong> or
        <strong>$_SESSION['User_ID']</strong>.
    </p>

</div>

<?php

}

?>


    </div>

</div>


<div class="dash-footer-contact">

    Contact Email:

    <a href="mailto:info@vinakayacc.com">
        info@vinakayacc.com
    </a>

</div>


</div>

<?php

}

?>

            </div>

        </div>

    </div>

</div>


<?php include('includes/footer-top.php'); ?>
<?php include('includes/footer-bottom.php'); ?>
