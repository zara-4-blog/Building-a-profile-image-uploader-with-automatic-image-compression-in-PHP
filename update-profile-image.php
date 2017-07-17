<?php

require('vendor/autoload.php');

use Zara4\API\Client;
use Zara4\API\ImageProcessing\LocalImageRequest;
use Zara4\API\ImageProcessing\ResizeMode;

// --- --- ---

$metaData = file_exists('metadata.json') ? json_decode(file_get_contents('metadata.json')) : [];
$id = isset($metaData->{'id'}) ? $metaData->{'id'} : null;


//
// Delete the existing uploaded profile image
//
if ($id && file_exists('uploaded-profile-'.$id)) {
  unlink('uploaded-profile-'.$id);
}


//
// Delete the existing metadata
//
if (file_exists('metadata.json')) {
  unlink('metadata.json');
}


if (isset($_POST['reset'])) {
  header('Location: index.php');
  die;
}


// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---


//
// Check we got the inputs we need
//
if (!file_exists($_FILES['file-to-upload']['tmp_name']) || !is_uploaded_file($_FILES['file-to-upload']['tmp_name'])) {
  die('Bad Request - No file uploaded');
}



$uploadedFilePath = $_FILES['file-to-upload']['tmp_name'];
$resizeMode = $_POST['resize-mode'];

// Replace with your API credentials
// You can obtain your API credentials from https://zara4.com/account/api-clients/live-credentials
// For the purpose of testing you can also use sandbox test credentials https://zara4.com/account/api-clients/test-credentials
$apiClientId = isset($_SERVER['ZARA4_API_CLIENT_ID']) && $_SERVER['ZARA4_API_CLIENT_ID']
  ? $_SERVER['ZARA4_API_CLIENT_ID'] : 'put-your-api-client-id-here';

$apiClientSecret = isset($_SERVER['ZARA4_API_CLIENT_SECRET']) && $_SERVER['ZARA4_API_CLIENT_SECRET']
  ? $_SERVER['ZARA4_API_CLIENT_SECRET'] : 'put-your-api-client-secret-here';


// Create Zara 4 API client
$apiClient = new Client($apiClientId, $apiClientSecret);



// --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- ---



//
// Compress the image
//
try {
  $originalImage  = new LocalImageRequest($uploadedFilePath);

  // Instruct for image resize by crop
  $originalImage->resizeMode = $resizeMode;
  $originalImage->width = 256;
  $originalImage->height = 256;

  // Perform resize and compression
  $processedImage = $apiClient->processImage($originalImage);

  $id = uniqid();

  // Download compressed image
  $apiClient->downloadProcessedImage($processedImage, 'uploaded-profile-'.$id);

  // --- --- ---

  $metaDataObj = [
    'id' => $id,
    'percentage-saving' => $processedImage->percentageSaving(),
    'original-file-size' => $processedImage->originalFileSize(),
    'compressed-file-size' => $processedImage->compressedFileSize(),
  ];

  file_put_contents('metadata.json', json_encode($metaDataObj));

  // --- --- ---

  // Redirect back to profile page
  header('Location: index.php');
}


// Out of quota
catch (\Zara4\API\ImageProcessing\QuotaLimitException $e) {

  // Either use test API sandbox credentials (see https://zara4.com/account/api-clients/test-credentials)
  // or view are compression packages at https://zara4.com/pricing
  die('Compression Failed - Out of compression quota');
}


// Submitted image it too large
catch (\Zara4\API\ImageProcessing\FileSizeTooLargeException $e) {
  die('Compression Failed - Image too large');
}


// Submitted file is not an image
catch (\Zara4\API\ImageProcessing\InvalidImageFormatException $e) {
  die('Compression Failed - Not a recognised image format (supports jpgg, png, gif and svg)');
}