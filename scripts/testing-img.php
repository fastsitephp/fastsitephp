<?php
// Test Script for manually testing Image class.
// In the future this code can be used as a starting point 
// when creating the full unit tests. In the meantime this 
// manually helps confirm the class works as expected.

// Autoloader and Setup App
require __DIR__ . '/../autoload.php';
$app = new \FastSitePHP\Application();
$app->setup('UTC');
$app->show_detailed_errors = true;
set_time_limit(0);

// Uncomment to test errors
// try {
//     // $path = 'C:\Users\Public\Pictures\Test.txt';
//     $path = 'C:\Users\Public\Pictures\Invalid.jpg';
//     // $path = 'C:\Users\Public\Pictures\Desert.jpg.png'; // JPG renamed as PNG
//     $img = new \FastSitePHP\Media\Image();
//     $img->open($path);
//     echo 'ERROR - no exception thrown';
// } catch (\Exception $e) {
//     echo $e->getMessage();
// }
// exit();

$path = 'C:\Users\Public\Pictures\Desert.jpg';
$name_left = 'Img_Left.jpg';
$name_right = 'Img_Right_Resized.jpg';
$name_height = 'Img_Resize_Height.jpg';
$name_cropped = 'Img_Cropped.jpg';
$name_cropped2 = 'Img_Cropped2.jpg';

$img = new \FastSitePHP\Media\Image();
$img->open($path);
$save_types = array('jpg', 'jpeg', 'gif', 'png', 'webp');
foreach ($save_types as $file_type) {
    $save_path = 'C:\Users\Public\Pictures\Test\Test.' . $file_type;
    $img->save($save_path);
    // $img->saveQuality(10)->pngCompression(9)->save($save_path);

    $img2 = new \FastSitePHP\Media\Image();
    $img2->open($save_path);
}

$img = new \FastSitePHP\Media\Image();
$img
    ->open($path)
    ->rotateLeft()
    ->save($name_left);

$img = new \FastSitePHP\Media\Image();
$img
    ->open($path)
    ->rotateRight()
    ->resize(100, 100)
    ->save($name_right);

$img = new \FastSitePHP\Media\Image();
$img
    ->open($path)
    ->resize(null, 300)
    ->save($name_height);

$left = 400;
$top = 200;
$width = 350;
$height = 275;

$img = new \FastSitePHP\Media\Image();
$img
    ->open($path)
    ->crop($left, $top, $width, $height)
    ->save($name_cropped);

$target_width = ceil($width / 2);
$target_height = ceil($height / 2);

$img = new \FastSitePHP\Media\Image();
$img
    ->open($path)
    ->crop($left, $top, $width, $height, $target_width, $target_height)
    ->save($name_cropped2);

// Overwrite original file with a larger size by width.
// Uncomment to test:
//
// $img = new \FastSitePHP\Media\Image();
// $img
//     ->open($path)
//     ->resize(2000)
//     ->save(); 

echo 'Finished, check files and manually reset or delete files';