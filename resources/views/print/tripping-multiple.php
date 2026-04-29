<!DOCTYPE html>
<html>
<head>
    <title>Multiple Site Visits</title>

    <style>
        body {
            font-family: Arial;
            padding: 20px;
        }

        .page {
            padding: 20px;
        }

        .page-break {
            page-break-after: always;
        }

        .line {
            margin-bottom: 6px;
        }

        .title {
            text-align: center;
            margin-bottom: 20px;
        }

        @media print {
            button { display: none; }
        }
    </style>
</head>

<body>

<?php foreach ($trips as $trip): ?>

<div class="page">

    <div class="title">
        <h2>SITE VISIT REPORT</h2>
        <h4>ArkCrest Realty Corporation</h4>
        <p>Status: <?php echo strtoupper($trip->status); ?></p>
    </div>

    <hr>

    <div class="line">Agent: <?php echo $trip->agent_name; ?></div>
    <div class="line">Client: <?php echo $trip->client_name; ?></div>
    <div class="line">Email: <?php echo $trip->client_email; ?></div>
    <div class="line">Phone: <?php echo $trip->client_phone_code . ' ' . $trip->client_phone; ?></div>
    <div class="line">Address: <?php echo $trip->client_address; ?></div>

    <hr>

    <div class="line">Property: <?php echo $trip->property_name; ?></div>
    <div class="line">Company: <?php echo $trip->company_name; ?></div>
    <div class="line">Type: <?php echo $trip->tripping_type; ?></div>

    <div class="line">Date: <?php echo $trip->tripping_date; ?></div>
    <div class="line">Time: <?php echo $trip->tripping_time; ?></div>
    <div class="line">Status: <?php echo $trip->status; ?></div>

</div>

<div class="page-break"></div>

<?php endforeach; ?>

<button onclick="window.print()">Print</button>

</body>
</html>