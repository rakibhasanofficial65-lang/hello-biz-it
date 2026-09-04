<?php

declare(strict_types=1);

/**
 * Upload a local uploaded file to Vercel Blob.
 *
 * Returns the final Blob URL on success.
 * Throws Exception on failure.
 */

function uploadToVercelBlob(
    string $tmpFile,
    string $blobPath,
    string $contentType
): string {

    $token = getenv('BLOB_READ_WRITE_TOKEN');

    if (!$token) {
        throw new Exception(
            'BLOB_READ_WRITE_TOKEN is not configured.'
        );
    }

    if (!is_file($tmpFile)) {
        throw new Exception(
            'Uploaded file could not be found.'
        );
    }

    $url = 'https://blob.vercel-storage.com/' .
        str_replace('%2F', '/', rawurlencode($blobPath));

    $fileHandle = fopen($tmpFile, 'rb');

    if ($fileHandle === false) {
        throw new Exception(
            'Unable to open uploaded file.'
        );
    }

    $ch = curl_init($url);

    curl_setopt_array($ch, [

        CURLOPT_CUSTOMREQUEST => 'PUT',

        CURLOPT_UPLOAD => true,

        CURLOPT_INFILE => $fileHandle,

        CURLOPT_INFILESIZE => filesize($tmpFile),

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: ' . $contentType,
            'x-vercel-blob-access: public'
        ],

        CURLOPT_TIMEOUT => 60,

        CURLOPT_CONNECTTIMEOUT => 15,

    ]);

    $response = curl_exec($ch);

    $httpCode = curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );

    $curlError = curl_error($ch);

    curl_close($ch);

    fclose($fileHandle);

    if ($response === false) {

        throw new Exception(
            'Vercel Blob upload failed: ' . $curlError
        );
    }

    if ($httpCode < 200 || $httpCode >= 300) {

        throw new Exception(
            'Vercel Blob returned HTTP ' .
            $httpCode .
            ': ' .
            $response
        );
    }

    $data = json_decode(
        $response,
        true
    );

    if (
        !is_array($data) ||
        empty($data['url'])
    ) {

        throw new Exception(
            'Invalid response from Vercel Blob.'
        );
    }

    return $data['url'];
}
