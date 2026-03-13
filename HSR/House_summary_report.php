<?php
declare(strict_types=1);

function old(string $key, string $default = ''): string
{
    return htmlspecialchars($_POST[$key] ?? $default, ENT_QUOTES, 'UTF-8');
}

function checked(string $key, string $value): string
{
    return (($_POST[$key] ?? '') === $value) ? 'checked' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>House Summary Report</title>
    <style>
        :root {
            --page-width: 8.5in;
            --page-height: 11in;
            --text: #111;
            --border: #111;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: #dcdcdc;
            color: var(--text);
            font-family: "Arial Narrow", Arial, Helvetica, sans-serif;
        }

        body {
            padding: 18px;
        }

        .actions {
            width: var(--page-width);
            margin: 0 auto 14px auto;
            display: flex;
            gap: 10px;
        }

        .actions button {
            border: 1px solid #333;
            background: #fff;
            padding: 8px 14px;
            font-size: 14px;
            cursor: pointer;
        }

        .page {
            width: var(--page-width);
            min-height: var(--page-height);
            margin: 0 auto 18px auto;
            background: #fff;
            position: relative;
            padding: 12px 14px 52px 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            overflow: hidden; /* prevents lines from exceeding page width */
        }

        .banner {
            width: 100%;
            display: block;
            margin: 0 0 30px 0;
        }

        .title {
            font-size: 18px;
            font-weight: 400;
            margin: 8px 0 24px 0;
        }

        .line-row,
        .triple-row,
        .double-row {
            width: 100%;
            margin: 0 0 20px 0;
            white-space: nowrap;
            font-size: 15px;
            line-height: 1.35;
        }

        .double-row {
            display: flex;
            gap: 18px;
            align-items: baseline;
            flex-wrap: nowrap;
        }

        .triple-row {
            display: flex;
            gap: 12px;
            align-items: baseline;
            flex-wrap: nowrap;
        }

        .field-wrap {
            display: inline-flex;
            align-items: baseline;
            min-width: 0;
            flex: 1 1 auto;
        }

        .label {
            white-space: nowrap;
        }

        .line-input,
        .small-input,
        .medium-input,
        .large-input,
        .money-input {
            border: 0;
            border-bottom: 1px solid var(--border);
            outline: 0;
            background: transparent;
            font: inherit;
            color: inherit;
            height: 24px;
            padding: 0 3px;
            border-radius: 0;
            min-width: 24px;
            max-width: 100%;
        }

        .line-input { width: 100%; }
        .small-input { width: 90px; }
        .medium-input { width: 145px; }
        .large-input { width: 220px; }
        .money-input { width: 120px; }

        .money-wrap {
            display: inline-flex;
            align-items: baseline;
            gap: 2px;
        }

        .money-sign {
            font-size: 15px;
            display: inline-block;
            min-width: 10px;
        }

        .yn-group {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-left: 4px;
        }

        .yn-group label,
        .check-inline label {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 15px;
        }

        input[type="checkbox"],
        input[type="checkbox"] {
            width: 14px;
            height: 14px;
            margin: 0;
            vertical-align: middle;
        }

        .owed-lines {
            margin: 14px 0 22px 0;
        }

        .owed-line {
            width: 100%;
            max-width: 100%;
            border: 0;
            border-bottom: 1px solid var(--border);
            outline: 0;
            background: transparent;
            font: inherit;
            display: block;
            height: 28px;
            margin-bottom: 16px;
        }

        .concern-label {
            margin: 22px 0 10px 0;
            font-size: 15px;
        }

        .concern-line {
            width: 100%;
            border: 0;
            border-bottom: 1px solid var(--border);
            outline: 0;
            background: transparent;
            font: inherit;
            display: block;
            height: 28px;
            margin-bottom: 12px;
        }

        .page-number {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 16px;
            text-align: center;
            font-size: 18px;
        }

        .section-spacer {
            height: 8px;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .actions {
                display: none;
            }

            .page {
                margin: 0;
                box-shadow: none;
                break-after: page;
            }

            .page:last-of-type {
                break-after: auto;
            }
        }
    </style>
</head>
<body>
<form method="post">
    <div class="actions">
        <button type="submit">Refresh Form</button>
        <button type="button" onclick="window.print()">Print</button>
    </div>

    <div class="page">
        <img class="banner" src="../images/oxford_banner.png" alt="Oxford House Banner">

        <div class="title">House Summary Report</div>

        <div class="line-row">
            <span class="label">House Name </span>
            <input class="line-input" type="text" name="house_name" value="<?= old('house_name') ?>">
        </div>

        <div class="line-row">
            <span class="label">What Chapter is your house in </span>
            <input class="line-input" type="text" name="chapter" value="<?= old('chapter') ?>">
        </div>

        <div class="line-row">
            <span class="label">House Meeting day and time </span>
            <input class="line-input" type="text" name="meeting_day_time" value="<?= old('meeting_day_time') ?>">
        </div>

        <div class="double-row">
            <span class="field-wrap">
                <span class="label">House President </span>
                <input class="medium-input" type="text" name="house_president" value="<?= old('house_president') ?>">
            </span>
        </div>

        <div class="triple-row">
            <span class="field-wrap">
                <span class="label">Capacity </span>
                <input class="small-input" type="text" name="capacity" value="<?= old('capacity') ?>">
            </span>
            <span class="field-wrap">
                <span class="label">Occupied Beds </span>
                <input class="small-input" type="text" name="occupied_beds" value="<?= old('occupied_beds') ?>">
            </span>
            <span class="field-wrap">
                <span class="label">Vacant Beds </span>
                <input class="small-input" type="text" name="vacant_beds" value="<?= old('vacant_beds') ?>">
            </span>
        </div>

        <div class="triple-row">
            <span class="field-wrap">
                <span class="label">Applications Recieved </span>
                <input class="small-input" type="text" name="applications_received" value="<?= old('applications_received') ?>">
            </span>
            <span class="field-wrap">
                <span class="label">New Members </span>
                <input class="medium-input" type="text" name="new_members" value="<?= old('new_members') ?>">
            </span>
        </div>

        <div class="triple-row">
            <span class="field-wrap">
                <span class="label">Voluntary departures </span>
                <input class="small-input" type="text" name="voluntary_departures" value="<?= old('voluntary_departures') ?>">
            </span>
            <span class="field-wrap">
                <span class="label">Relapse Departurers </span>
                <input class="small-input" type="text" name="relapse_departurers" value="<?= old('relapse_departurers') ?>">
            </span>
            <span class="field-wrap">
                <span class="label">Other </span>
                <input class="small-input" type="text" name="other_departures" value="<?= old('other_departures') ?>">
            </span>
        </div>

        <div class="double-row">
            <span class="field-wrap">
                <span class="label">Number of members attending 3 meets or more </span>
                <input class="small-input" type="text" name="members_attending_3_meets" value="<?= old('members_attending_3_meets') ?>">
            </span>
            <span class="field-wrap">
                <span class="label">Members on Contract </span>
                <input class="small-input" type="text" name="members_on_contract" value="<?= old('members_on_contract') ?>">
            </span>
        </div>

        <div class="line-row">
            <span class="label">Names Of memebers left owing Money: and How much?</span>
        </div>

        <div class="owed-lines">
            <input class="owed-line" type="text" name="owing_line_1" value="<?= old('owing_line_1') ?>">
            <input class="owed-line" type="text" name="owing_line_2" value="<?= old('owing_line_2') ?>">
            <input class="owed-line" type="text" name="owing_line_3" value="<?= old('owing_line_3') ?>">
        </div>

        <div class="double-row">
            <span class="field-wrap">
                <span class="label">Amount in Checking </span>
                <span class="money-wrap">
                    <span class="money-sign"> $</span>
                    <input class="money-input" type="text" name="amount_checking" value="<?= old('amount_checking') ?>">
                </span>
            </span>
            <span class="field-wrap">
                <span class="label">Amount in Saving </span>
                <span class="money-wrap">
                    <span class="money-sign"> $ </span>
                    <input class="money-input" type="text" name="amount_saving" value="<?= old('amount_saving') ?>">
                </span>
            </span>
        </div>

        <div class="section-spacer"></div>

        <div class="double-row">
            <span class="field-wrap">
                <span class="label">Number of Members </span>
                <input class="small-input" type="text" name="number_of_members" value="<?= old('number_of_members') ?>">
            </span>
            <span class="field-wrap">
                <span class="label">Number of Members behind </span>
                <input class="small-input" type="text" name="number_of_members_behind" value="<?= old('number_of_members_behind') ?>">
            </span>
        </div>

        <div class="double-row">
            <span class="field-wrap">
                <span class="label">Total Behind </span>
                <span class="money-wrap">
                    <span class="money-sign">$</span>
                    <input class="money-input" type="text" name="total_behind" value="<?= old('total_behind') ?>">
                </span>
            </span>
            <span class="field-wrap">
                <span class="label">Are your bills caught up?</span>
                <span class="yn-group">
                    <label><input type="checkbox" name="bills_caught_up" value="Yes" <?= checked('bills_caught_up', 'Yes') ?>> Yes</label>
                    <label><input type="checkbox" name="bills_caught_up" value="No" <?= checked('bills_caught_up', 'No') ?>> No</label>
                </span>
            </span>
        </div>

        <!-- <div class="double-row">
            <span class="field-wrap">
                <span class="label">Checking Account Amount </span>
                <span class="money-wrap">
                    <span class="money-sign">$</span>
                    <input class="money-input" type="text" name="checking_account_amount" value="<?= old('checking_account_amount') ?>">
                </span>
            </span>
            <span class="field-wrap">
                <span class="label">Savings Account Amount </span>
                <span class="money-wrap">
                    <span class="money-sign">$</span>
                    <input class="money-input" type="text" name="savings_account_amount" value="<?= old('savings_account_amount') ?>">
                </span>
            </span>
        </div> -->

        <div class="page-number">1</div>
    </div>

    <div class="page">
        <div class="double-row">
            <span class="field-wrap">
                <span class="label">Are all bills Current</span>
                <span class="yn-group">
                    <label><input type="checkbox" name="bills_current" value="Y" <?= checked('bills_current', 'Y') ?>> Y</label>
                    <label><input type="checkbox" name="bills_current" value="N" <?= checked('bills_current', 'N') ?>> N</label>
                </span>
            </span>
            <span class="field-wrap">
                <span class="label">Members behind </span>
                <input class="small-input" type="text" name="members_behind" value="<?= old('members_behind') ?>">
            </span>
        </div>

       <!--  <div class="line-row">
            <span class="label">Total amount owed to house current Members </span>
            <input class="medium-input" type="text" name="total_owed_current_members" value="<?= old('total_owed_current_members') ?>">
        </div> -->

        <div class="line-row">
            <span class="label">Answering machine checked </span>
            <input class="small-input" type="text" name="answering_machine_checked" value="<?= old('answering_machine_checked') ?>">
        </div>

        <div class="line-row">
            <span class="label">OHI Donation</span>
            <span class="yn-group">
                <label><input type="checkbox" name="ohi_donation" value="Y" <?= checked('ohi_donation', 'Y') ?>> Y</label>
                <label><input type="checkbox" name="ohi_donation" value="N" <?= checked('ohi_donation', 'N') ?>> N</label>
            </span>
        </div>

        <div class="line-row">
            <span class="label">Email Checked Daily</span>
            <span class="yn-group">
                <label><input type="checkbox" name="email_checked_daily" value="Y" <?= checked('email_checked_daily', 'Y') ?>> Y</label>
                <label><input type="checkbox" name="email_checked_daily" value="N" <?= checked('email_checked_daily', 'N') ?>> N</label>
            </span>
        </div>

        <div class="double-row">
            <span class="field-wrap">
                <span class="label">House audit done</span>
                <span class="yn-group">
                    <label><input type="checkbox" name="house_audit_done" value="Y" <?= checked('house_audit_done', 'Y') ?>> Y</label>
                    <label><input type="checkbox" name="house_audit_done" value="N" <?= checked('house_audit_done', 'N') ?>> N</label>
                </span>
            </span>
            <span class="field-wrap">
                <span class="label">Bank Statement attached</span>
                <span class="yn-group">
                    <label><input type="checkbox" name="bank_statement_attached" value="Y" <?= checked('bank_statement_attached', 'Y') ?>> Y</label>
                    <label><input type="checkbox" name="bank_statement_attached" value="N" <?= checked('bank_statement_attached', 'N') ?>> N</label>
                </span>
            </span>
        </div>

        <div class="line-row">
            <span class="label">Presentation Done and Date </span>
            <input class="medium-input" type="text" name="presentation_done_date" value="<?= old('presentation_done_date') ?>">
        </div>

        <div class="line-row">
            <span class="label">Number of Members with Jobs </span>
            <input class="medium-input" type="text" name="members_with_jobs" value="<?= old('members_with_jobs') ?>">
        </div>

        <div class="line-row">
            <span class="label">Members directly from jail prison in last 30 days </span>
            <input class="small-input" type="text" name="members_from_jail_prison_30_days" value="<?= old('members_from_jail_prison_30_days') ?>">
        </div>

        <div class="line-row">
            <span class="label">Number of re-entry members arrested while at Oxford </span>
            <input class="small-input" type="text" name="reentry_members_arrested" value="<?= old('reentry_members_arrested') ?>">
        </div>

        <div class="line-row">
            <span class="label">Number of member that abused Opioids </span>
            <input class="small-input" type="text" name="members_abused_opioids" value="<?= old('members_abused_opioids') ?>">
        </div>

        <div class="line-row">
            <span class="label">Number of members on MAT </span>
            <input class="small-input" type="text" name="members_on_mat" value="<?= old('members_on_mat') ?>">
        </div>

        <div class="concern-label">How is your house doing? Any Concerns?</div>
        <input class="concern-line" type="text" name="concerns_1" value="<?= old('concerns_1') ?>">
        <input class="concern-line" type="text" name="concerns_2" value="<?= old('concerns_2') ?>">
        <input class="concern-line" type="text" name="concerns_3" value="<?= old('concerns_3') ?>">
        <input class="concern-line" type="text" name="concerns_4" value="<?= old('concerns_4') ?>">
        <input class="concern-line" type="text" name="concerns_5" value="<?= old('concerns_5') ?>">
        <input class="concern-line" type="text" name="concerns_6" value="<?= old('concerns_6') ?>">
        <div class="concern-label">Is there anything you would like from chapter?</div>
        <input class="concern-line" type="text" name="concerns_1" value="<?= old('concerns_1') ?>">
        <input class="concern-line" type="text" name="concerns_2" value="<?= old('concerns_2') ?>">
        <input class="concern-line" type="text" name="concerns_3" value="<?= old('concerns_3') ?>">
        <input class="concern-line" type="text" name="concerns_4" value="<?= old('concerns_4') ?>">
        <input class="concern-line" type="text" name="concerns_5" value="<?= old('concerns_5') ?>">
        <input class="concern-line" type="text" name="concerns_6" value="<?= old('concerns_6') ?>">

        <div class="page-number">2</div>
    </div>
</form>
</body>
</html>
