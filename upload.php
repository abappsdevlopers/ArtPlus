<?php
// السماح بالوصول من المتصفح (CORS)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['file'])) {
        echo json_encode(["error" => ["message" => "لم يتم اختيار أي ملف للرفع"]]);
        exit;
    }

    // 🔒 بيانات حساب Cloudinary الخاص بك (مخفية تماماً داخل السيرفر)
    $cloudName = "eu5brxhw";
    $apiKey    = "753684772817155";
    $apiSecret = "NLkAPj0ytM4-ig3_qHq-tomR9_c";

    $fileTmpPath = $_FILES['file']['tmp_name'];
    $timestamp = time();

    // 🔑 إنشاء التوقيع الرقمي المشفر لضمان الأمان الفائق
    // تذكر: الترتيب الأبجدي للمتغيرات إجباري لـ Cloudinary
    $params = [
        "timestamp" => $timestamp
    ];
    
    ksort($params);
    $queryString = http_build_query($params);
    $signature = sha1($queryString . $apiSecret);

    // تجهيز الطلب لإرساله إلى سيرفرات Cloudinary
    $targetUrl = "https://api.cloudinary.com/v1_1/" . $cloudName . "/image/upload";
    
    $cFile = new CURLFile($fileTmpPath, $_FILES['file']['type'], $_FILES['file']['name']);
    
    $postData = [
        "file"      => $cFile,
        "api_key"   => $apiKey,
        "timestamp" => $timestamp,
        "signature" => $signature
    ];

    // إرسال الملف عبر cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $targetUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // إرجاع النتيجة للمتصفح
    http_response_code($httpCode);
    echo $result;
    exit;
} else {
    echo json_encode(["error" => ["message" => "طريقة الطلب غير مدعومة"]]);
}
?>