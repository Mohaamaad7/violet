<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رسالة جديدة من نموذج الاتصال</title>
</head>

<body
    style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="margin: 0;">رسالة جديدة من نموذج الاتصال</h1>
    </div>

    <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h2 style="color: #667eea; margin-top: 0;">معلومات المرسل</h2>
            <p><strong>الاسم:</strong> {{ $contactName }}</p>
            <p><strong>البريد الإلكتروني:</strong> <a href="mailto:{{ $contactEmail }}"
                    style="color: #667eea;">{{ $contactEmail }}</a></p>
            <p><strong>الهاتف:</strong> <a href="tel:{{ $contactPhone }}"
                    style="color: #667eea;">{{ $contactPhone }}</a></p>
            <p><strong>الموضوع:</strong> {{ $contactSubject }}</p>
        </div>

        <div style="background: white; padding: 20px; border-radius: 8px;">
            <h2 style="color: #667eea; margin-top: 0;">الرسالة</h2>
            <p style="white-space: pre-wrap;">{{ $contactMessage }}</p>
        </div>

        <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 2px solid #e0e0e0;">
            <p style="color: #666; font-size: 14px;">
                تم إرسال هذه الرسالة من نموذج الاتصال في موقع Flower Violet
            </p>
        </div>
    </div>
</body>

</html>