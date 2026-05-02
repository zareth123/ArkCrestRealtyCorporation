<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Site Visit Report - ArkCrest</title>
    <style>
        :root {
            --primary: #1e293b; /* Deep Navy */
            --accent: #b59410;  /* Corporate Gold */
            --border: #e2e8f0;
            --text-main: #334155;
            --text-label: #64748b;
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 40px;
            color: var(--text-main);
        }

        /* Container for all reports */
        .report-wrapper {
            max-width: 850px;
            margin: 0 auto;
        }

        .page {
            background: white;
            padding: 50px;
            margin-bottom: 40px;
            border-radius: 4px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border-top: 10px solid var(--primary);
            position: relative;
        }

        /* Header Styling */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--border);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header-title h2 {
            margin: 0;
            color: var(--primary);
            font-size: 24px;
            letter-spacing: 1px;
        }

        .header-title h4 {
            margin: 5px 0 0 0;
            color: var(--accent);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
        }

        .status-pill {
            background: var(--primary);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Grid Layout for details */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 11px;
            font-weight: 800;
            color: var(--accent);
            text-transform: uppercase;
            margin-bottom: 15px;
            letter-spacing: 1px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 5px;
        }

        .line {
            margin-bottom: 12px;
            display: flex;
            font-size: 14px;
        }

        .label {
            color: var(--text-label);
            width: 100px;
            flex-shrink: 0;
            font-weight: 600;
        }

        .value {
            color: var(--text-main);
            font-weight: 500;
        }

        /* Summary Box for Schedule */
        .schedule-footer {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--accent);
            display: flex;
            justify-content: space-around;
        }

        .footer-item { text-align: center; }
        .footer-item small { display: block; color: var(--text-label); font-size: 10px; text-transform: uppercase; font-weight: bold; }
        .footer-item span { color: var(--primary); font-weight: bold; font-size: 16px; }

        .page-break { page-break-after: always; }

        /* Floating Print Button */
        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--accent);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(181, 148, 16, 0.4);
            transition: transform 0.2s;
        }

        .print-btn:hover { transform: scale(1.05); }

        @media print {
            body { background: white; padding: 0; }
            .page { box-shadow: none; border: 1px solid #eee; margin: 0; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

<div class="report-wrapper">
    <?php foreach ($trips as $trip): ?>
    <div class="page">
        
        <div class="header">
            <div class="header-title">
                <h2>SITE VISIT REPORT</h2>
                <h4>ArkCrest Realty Corporation</h4>
            </div>
            <div class="status-pill">
                <?php echo strtoupper($trip->status); ?>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-column">
                <div class="section-title">Client Information</div>
                <div class="line">
                    <span class="label">Name:</span>
                    <span class="value"><?php echo $trip->client_name; ?></span>
                </div>
                <div class="line">
                    <span class="label">Email:</span>
                    <span class="value"><?php echo $trip->client_email; ?></span>
                </div>
                <div class="line">
                    <span class="label">Phone:</span>
                    <span class="value"><?php echo $trip->client_phone_code . ' ' . $trip->client_phone; ?></span>
                </div>
                <div class="line">
                    <span class="label">Address:</span>
                    <span class="value"><?php echo $trip->client_address; ?></span>
                </div>
            </div>

            <div class="info-column">
                <div class="section-title">Property & Agent</div>
                <div class="line">
                    <span class="label">Property:</span>
                    <span class="value"><?php echo $trip->property_name; ?></span>
                </div>
                <div class="line">
                    <span class="label">Company:</span>
                    <span class="value"><?php echo $trip->company_name; ?></span>
                </div>
                <div class="line">
                    <span class="label">Type:</span>
                    <span class="value"><?php echo $trip->tripping_type; ?></span>
                </div>
                <div class="line">
                    <span class="label">Agent:</span>
                    <span class="value"><?php echo $trip->agent_name; ?></span>
                </div>
            </div>
        </div>

        <div class="schedule-footer">
            <div class="footer-item">
                <small>Appointment Date</small>
                <span><?php echo is_object($trip->tripping_date) ? $trip->tripping_date->format('F d, Y') : $trip->tripping_date; ?></span>
            </div>
            <div class="footer-item">
                <small>Preferred Time</small>
                <span><?php echo $trip->tripping_time; ?></span>
            </div>
        </div>

    </div>
    <div class="page-break"></div>
    <?php endforeach; ?>
</div>

<button class="print-btn" onclick="window.print()">Print Site Visit Report</button>

</body>
</html>