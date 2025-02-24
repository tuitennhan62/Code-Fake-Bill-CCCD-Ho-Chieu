<?php
require $_SERVER['DOCUMENT_ROOT'].'/files/config.php';
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
if($webinfo['fakebillfree'] > 0){
    DB::query("UPDATE settings SET fakebillfree=fakebillfree-1 WHERE id = '1'");
}
function canletrai($image,$fontsize,$y,$textColor,$font,$text,$x_tcb){


    // Thiết lập kích thước font chữ
    $fontSize = $fontsize;


    imagettftext($image, $fontSize, 0, $x_tcb, $y, $textColor, $font, $text);

}
function cangiua($image, $fontsize, $y, $textColor, $font, $text) {
    $fontSize = $fontsize;
    $textBoundingBox = imagettfbbox($fontSize, 0, $font, $text);
    $textWidth = $textBoundingBox[2] - $textBoundingBox[0];
    $imageWidth = imagesx($image);
    $x = ($imageWidth - $textWidth) / 2; // Căn giữa theo chiều ngang
    imagettftext($image, $fontSize, 0, $x, $y, $textColor, $font, $text);
}

function canchinhgiuawithpaddingleft($image, $fontsize, $y, $textColor, $font, $text, $paddingLeft = 0) {
    $fontSize = $fontsize;
    $textBoundingBox = imagettfbbox($fontSize, 0, $font, $text);
    $textWidth = $textBoundingBox[2] - $textBoundingBox[0];
    $imageWidth = imagesx($image);
    $x = ($imageWidth - $textWidth) / 2; // Căn giữa theo chiều ngang
    imagettftext($image, $fontSize, 0, $x + $paddingLeft, $y, $textColor, $font, $text);
}

$fontPath =  $_SERVER['DOCUMENT_ROOT'].'/fonts';
if(isset($_GET['type'])){

    $username;
    if(isset($_POST['key'])){
        $username = xss_clean(DB::queryFirstField("SELECT username FROM users WHERE serial_key = '".trim($_POST['key'])."' LIMIT 1"));
    }

      if(!empty($username) || $_GET['type'] == 'demo'){
         $timestampHetHan = strtotime(DB::queryFirstField("SELECT date_bill FROM `users` WHERE username = '$username'"));
            if (time() < $timestampHetHan) {
                $tiengoc = 0;
            }
        $sodu = DB::queryFirstField("SELECT sodu FROM `users` WHERE username = '$username'");
        if($_GET['type'] == 'demo'){
            $tiengoc = 0;
            $watermark = 1;
        } else {
            DB::query("UPDATE settings SET luottaobill=luottaobill+1 WHERE id = '1'");
            $sodu = DB::queryFirstField("SELECT sodu FROM `users` WHERE username = '$username'");
            if($sodu >= $tiengoc){
            $watermark = 0;
            DB::query("UPDATE users SET sodu=sodu-$tiengoc WHERE username='$username'");
            DB::insert('notifications', [
  'notifications' => 'Đã tạo 1 bill, số tiền trừ '.$tiengoc,
  'username' => $username,
  'amount' => $tiengoc
]);
            }
        }
        if($_POST['theme'] == 'ios'){
            $theme = 'bg-wifi.png';
        }

         if($_POST['theme'] == 'ios4g'){
            $theme = 'bg-4g.png';
        }


        if(empty(trim($_POST['amount']))){
            die ('<span style="color:red">Vui lòng không bỏ trống dữ liệu</span>');
        }
        if($sodu >= $tiengoc){
            {
//                $theme = 'bg-wifi.png'
                $sourceImage = imagecreatefrompng($theme);

                 if($watermark == 1){
                        canchinhgiua($sourceImage, 25, 400, imagecolorallocate($sourceImage, 255,0,0), $_SERVER['DOCUMENT_ROOT'].'/fonts/San Francisco/SanFranciscoText-Semibold.otf', 'Ảnh này chỉ để xem demo');
                        canchinhgiua($sourceImage, 25, 1140, imagecolorallocate($sourceImage, 255,0,0), $_SERVER['DOCUMENT_ROOT'].'/fonts/San Francisco/SanFranciscoText-Semibold.otf', 'Vui lòng ấn vào nút'."\n".'"Tải ảnh gốc" để xóa dòng chữ này'."\n".'Đây chỉ là demo để xem trước khi tải');
                 }

              if (strpos($_POST['theme'], 'ios') !== false) {
                    imagettftext($sourceImage, 40, 0, 150, 110, imagecolorallocate($sourceImage, 255, 255, 255), $_SERVER['DOCUMENT_ROOT'] . '/fonts/San Francisco/SanFranciscoText-Semibold_Fix.otf', $_POST['time_dt']);
              }


                $padding = 25;
                canchinhgiuawithpaddingleft($sourceImage, 62, 703, imagecolorallocate($sourceImage, 255,255,255), $_SERVER['DOCUMENT_ROOT'].'/fonts/BinancePlex-SemiBold.otf', $_POST['vnd'], $padding);
                canchinhgiua($sourceImage, 36, 790, imagecolorallocate($sourceImage, 255,255,255), $_SERVER['DOCUMENT_ROOT'].'/fonts/BinancePlex-Regular (1).otf', 'Bán thành công '.$_POST['amount'].' USDT');
                $xD = (1290 - strlen($_POST['vnd'])*46)/2;
                canletrai($sourceImage, 46, 695, imagecolorallocate($sourceImage, 255,255,255), $_SERVER['DOCUMENT_ROOT'].'/fonts/BinancePlex-SemiBold.otf', '₫',$xD + $padding);

//            canletrai($sourceImage, 39, 1465, imagecolorallocate($sourceImage, 0, 0, 0), $fontPath.'/SF-Pro-Text-Semibold.otf', 'Ngân hàng '.$_POST['bank_nhan'],72);
//            canletrai($sourceImage, 39, 1540, imagecolorallocate($sourceImage, 0, 0, 0), $fontPath.'/SF-Pro-Text-Semibold.otf', $_POST['stk_nhan'],72);
//            canletrai($sourceImage, 39, 1920, imagecolorallocate($sourceImage, 0, 0, 0), $fontPath.'/SF-Pro-Text-Semibold.otf', $_POST['time_bill'],72);
//            canletrai($sourceImage, 39, 2130, imagecolorallocate($sourceImage, 0, 0, 0), $fontPath.'/SF-Pro-Text-Semibold.otf', $_POST['magiaodich'],72);


            $overlayImagePath = 'pin_'.str_replace('4g','',$_POST['theme']).'/'.$_POST['pin'].'.png';

            // Đọc ảnh sẽ được chèn
            $overlayImage = imagecreatefrompng($overlayImagePath);

              if (strpos($_POST['theme'], 'ios') !== false) {
                // Kích thước mới của ảnh sẽ được chèn (tùy chỉnh theo yêu cầu)
            //Đây là kích thước chuẩn
            //$newWidth = 93;
            //$newHeight = 45;
            
            //Đây là kích thước theo ý khách
            $newWidth = 97;
            $newHeight = 46;
            $pinx = 1079;
            $piny = 62;
            }
              if (strpos($_POST['theme'], 'android') !== false) {
                // Kích thước mới của ảnh sẽ được chèn (tùy chỉnh theo yêu cầu)
            $newWidth = 50;
            $newHeight = 50;
             $pinx = 1190;
            $piny = 45;
            }
            // Chọn hàm tùy thuộc vào phiên bản PHP và yêu cầu của bạn
            // Nếu sử dụng PHP >= 7.3, bạn có thể sử dụng imagescale
            if (function_exists('imagescale')) {
                $scaledOverlayImage = imagescale($overlayImage, $newWidth, $newHeight);
            } else {
                // Sử dụng imagecopyresampled nếu không sử dụng được imagescale
                $scaledOverlayImage = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($scaledOverlayImage, $overlayImage, 0, 0, 0, 0, $newWidth, $newHeight, imagesx($overlayImage), imagesy($overlayImage));
            }

            // Vị trí (x, y) để chèn ảnh sẽ được chèn (tùy chỉnh theo yêu cầu)


            // Chèn ảnh vào ảnh cơ sở
            imagecopy($sourceImage, $scaledOverlayImage, $pinx, $piny, 0, 0, $newWidth, $newHeight);

           ob_start();

            // Hiển thị ảnh vào output buffer
            imagepng($sourceImage, null, 9);

            // Lấy nội dung đối tượng đầu ra (output buffer)
            $imageData = ob_get_clean();

            // Chuyển đổi ảnh thành base64
            $base64Image = base64_encode($imageData);

            // In thẻ img với src là dữ liệu base64
            echo '<img onclick="taiAnh()" class="w-full" src="data:image/png;base64,' . $base64Image . '" alt="Generated Image">';

            // Giải phóng bộ nhớ
            imagedestroy($sourceImage);
            imagedestroy($scaledOverlayImage);
            imagedestroy($overlayImage);
            }
        } else {
            echo '<span style="color:red">Số dư không đủ</span>';
        }
    } else {
        echo '<span style="color:red">Vui lòng đăng nhập</span>';
    }
}