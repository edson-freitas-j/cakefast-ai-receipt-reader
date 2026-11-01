<?php

$apiKey = getenv('OPENAI_API_KEY');

if (!$apiKey) {
    die("ERRO: OPENAI_API_KEY não configurada.\n");
}


$imagePath = $argv[1] ?? null;

if (!$imagePath) {
    die("ERRO: informe o caminho da imagem JPEG.\n");
}


if (!file_exists($imagePath)) {
    die("ERRO: imagem não encontrada.\n");
}

$fileSize = filesize($imagePath);

if ($fileSize === false) {
    die("ERRO: não foi possível determinar o tamanho da imagem.\n");
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($imagePath);

if ($mimeType !== 'image/jpeg') {
    die("ERRO: tipo de imagem inesperado: " . $mimeType . "\n");
}

$imageData = file_get_contents($imagePath);

if ($imageData === false) {
    die("ERRO: não foi possível ler a imagem.\n");
}

$base64Image = base64_encode($imageData);

$data = [
    'model' => 'gpt-5-mini',
    'input' => [
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'input_text',

                  
                   'text' => 'Qual é a data da compra indicada neste cupom? Responda somente com a data. Se não for possível identificar com segurança, responda: NAO IDENTIFICADO.'



                  
                ],
                [
                    'type' => 'input_image',
                    'image_url' => 'data:' . $mimeType . ';base64,' . $base64Image
                ]
            ]
        ]
    ]
];

$ch = curl_init('https://api.openai.com/v1/responses');

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ],
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_TIMEOUT => 120
]);

$response = curl_exec($ch);

if ($response === false) {
    die("ERRO CURL: " . curl_error($ch) . "\n");
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);



echo "Imagem: " . $imagePath . "\n";
echo "Tamanho: " . $fileSize . " bytes\n";
echo "MIME: " . $mimeType . "\n";
echo "HTTP STATUS: " . $httpCode . "\n\n";

echo $response;


