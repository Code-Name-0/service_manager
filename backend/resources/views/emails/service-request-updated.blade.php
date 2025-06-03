<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Service Request Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-denied {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-completed {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }

        .highlight {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 15px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Service Request Update</h1>
        <p>Hello {{ $clientName }},</p>
        <p>We have an update regarding your service request for <strong>{{ $serviceName }}</strong>.</p>
    </div>

    <div class="content">
        <h3>Request Status: <span class="status-badge status-{{ $status }}">{{ ucfirst($status) }}</span></h3>

        <p><strong>Service:</strong> {{ $serviceName }}</p>
        <p><strong>Request ID:</strong> #{{ $serviceRequest->id }}</p>
        <p><strong>Submitted on:</strong> {{ $serviceRequest->created_at->format('F j, Y \a\t g:i A') }}</p>
        <p><strong>Updated on:</strong> {{ $serviceRequest->updated_at->format('F j, Y \a\t g:i A') }}</p>

        @if ($adminResponse)
            <div class="highlight">
                <h4>Message from our team:</h4>
                <p>{{ $adminResponse }}</p>
            </div>
        @endif

        @if ($status === 'approved')
            <p>Great news! Your service request has been approved. Our team will be in touch with you soon to discuss
                the next steps.</p>
        @elseif($status === 'completed')
            <p>Your service request has been completed successfully. Thank you for choosing our services!</p>
        @elseif($status === 'denied')
            <p>We apologize, but we are unable to proceed with your request at this time. Please see our team's message
                above for more details.</p>
        @endif

        <h4>Your Original Request:</h4>
        <div class="highlight">
            <p>{{ $serviceRequest->message }}</p>
        </div>
    </div>

    <div class="footer">
        <p>If you have any questions, please don't hesitate to contact us at <strong>support@originova.com</strong></p>
        <p>
            Best regards,<br>
            The Originova Team
        </p>
        <hr>
        <p style="font-size: 12px; color: #999;">
            This email was sent automatically. Please do not reply to this email.
        </p>
    </div>
</body>

</html>
