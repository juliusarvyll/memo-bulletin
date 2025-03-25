<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Memo Published</title>
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
            background-color: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }
        .memo-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .memo-category {
            display: inline-block;
            background-color: #eee;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-bottom: 15px;
        }
        .memo-content {
            margin-bottom: 20px;
            padding: 0 10px;
        }
        .memo-meta {
            font-size: 13px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .cta-button {
            display: inline-block;
            background-color: #4a6cf7;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            text-align: center;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin-top: 10px;">SPUP eBulletin System</h1>
    </div>

    <p>Hello,</p>

    <p>A new memo has been published in the SPUP eBulletin System.</p>

    <div class="memo-title">{{ $memo->title }}</div>

    <div class="memo-category">{{ $memo->category->name }}</div>

    <div class="memo-content">
        {!! Str::limit(strip_tags($memo->content), 300) !!}
        @if(strlen(strip_tags($memo->content)) > 300)
            <p>...</p>
        @endif
    </div>

    <a href="{{ url('/') }}" class="cta-button">View Full Memo</a>

    <div class="memo-meta">
        <p>Published by: {{ $memo->author->name }}</p>
        <p>Published on: {{ $memo->published_at->format('F j, Y, g:i a') }}</p>
    </div>

    <div class="footer">
        <p>This is an automated message from the SPUP eBulletin System. Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} SPUP eBulletin System. All rights reserved.</p>
    </div>
</body>
</html>
