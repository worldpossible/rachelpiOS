<?php

/******************************************************************

Projectname:   CAPTCHA class
Version:       2.0
Author:        Pascal Rehfeldt <Pascal@Pascal-Rehfeldt.com>

modified for Sphider-plus by Tec 2009.11.26

******************************************************************/

    //Start the session
    session_start();

    //Create a CAPTCHA
    $captcha = new captcha();

    //Store the String in a session
    $_SESSION['CAPTCHAString'] = $captcha->getCaptchaString();

    class captcha {

        var $Length;
        var $CaptchaString;
        var $fontpath;
        var $fonts;

        function captcha($length = 6)
        {

            header('Content-type: image/png');

            $this->Length   = $length;

            $this->fontpath = 'images/';
            $this->fonts    = $this->getFonts();
            $errormgr       = new error;

            if ($this->fonts == FALSE)
            {

                //$errormgr = new error;
                $errormgr->addError('No fonts available!');
                $errormgr->displayError();
                die();

            }

            if (function_exists('imagettftext') == FALSE)
            {

                $errormgr->addError('');
                $errormgr->displayError();
                die();

            }

            $this->stringGen();

            $this->makeCaptcha();

        } //captcha

        function getFonts()
        {

            $fonts = array();

            if ($handle = @opendir($this->fontpath))
            {

                while (($file = readdir($handle)) !== FALSE)
                {

                    $extension = strtolower(substr($file, strlen($file) - 3, 3));

                    if ($extension == 'ttf')
                    {

                        $fonts[] = $file;

                    }

                }

                closedir($handle);

            }
            else
            {

                return FALSE;

            }

            if (count($fonts) == 0)
            {

                return FALSE;

            }
            else
            {

                return $fonts;

            }

        } //getFonts

        function getRandFont()
        {

            return $this->fontpath . $this->fonts[mt_rand(0, count($this->fonts) - 1)];

        } //getRandFont

        function stringGen()
        {

            $uppercase  = range('A', 'Z');
            //$lowercase  = range('a', 'z');
            $numeric    = range(0, 9);

            $CharPool   = array_merge($uppercase, $numeric);
            $PoolLength = count($CharPool) - 1;

            for ($i = 0; $i < $this->Length; $i++)
            {

                $this->CaptchaString .= $CharPool[mt_rand(0, $PoolLength)];

            }

        } //StringGen

        function makeCaptcha()
        {

            $imagelength = $this->Length * 25 + 45;
            $imageheight = 75;

            $image       = imagecreate($imagelength, $imageheight);

            //$bgcolor     = imagecolorallocate($image, 222, 222, 222);
            $bgcolor     = imagecolorallocate($image, 240, 240, 240);
            //$bgcolor     = imagecolorallocate($image, 255, 255, 255);

            $stringcolor = imagecolorallocate($image, 30, 30, 30);

            $filter      = new filters;

            $filter->signs($image, $this->getRandFont());

            for ($i = 0; $i < strlen($this->CaptchaString); $i++)
            {

                imagettftext($image, 25, mt_rand(-15, 15), $i * 25 + 10,
                mt_rand(30, 70),
                $stringcolor,
                $this->getRandFont(),
                $this->CaptchaString{$i});

            }

            //$filter->noise($image, 10);
            //$filter->blur($image, 6);

            imagepng($image);

            imagedestroy($image);

        } //MakeCaptcha

        function getCaptchaString()
        {

            return $this->CaptchaString;

        } //GetCaptchaString

    } //class: captcha


    class error {

        var $errors;

        function error()
        {

            $this->errors = array();

        } //error

        function addError($errormsg)
        {

            $this->errors[] = $errormsg;

        } //addError

        function displayError()
        {

            $iheight     = count($this->errors) * 20 + 10;
            $iheight     = ($iheight < 130) ? 50 : $iheight;

            $image       = imagecreate(160, $iheight);

            $errorsign   = imagecreatefromjpeg('images/no_fonts.jpg');
            imagecopy($image, $errorsign, 1, 1, 1, 1, 160, 50);

            $bgcolor     = imagecolorallocate($image, 255, 255, 255);

            $stringcolor = imagecolorallocate($image, 0, 0, 0);

            for ($i = 0; $i < count($this->errors); $i++)
            {

                $imx = ($i == 0) ? $i * 20 + 5 : $i * 20;


                $msg = 'Error[' . $i . ']: ' . $this->errors[$i];

                imagestring($image, 5, 190, $imx, $msg, $stringcolor);

            }

            imagepng($image);

            imagedestroy($image);

        } //displayError

        function isError()
        {

            if (count($this->errors) == 0)
            {

                return FALSE;

            }
            else
            {

                return TRUE;

            }

        } //isError

    } //class: error


    class filters {

        function noise(&$image, $runs = 30)
        {

            $w = imagesx($image);
            $h = imagesy($image);

            for ($n = 0; $n < $runs; $n++)
            {

                for ($i = 1; $i <= $h; $i++)
                {

                    $randcolor = imagecolorallocate($image,
                    mt_rand(0, 255),
                    mt_rand(0, 255),
                    mt_rand(0, 255));

                    imagesetpixel($image,
                    mt_rand(1, $w),
                    mt_rand(1, $h),
                    $randcolor);

                }

            }

        } //noise

        function signs(&$image, $font, $cells = 3)
        {

            $w = imagesx($image);
            $h = imagesy($image);

            for ($i = 0; $i < $cells; $i++)
            {

                $centerX     = mt_rand(1, $w);
                $centerY     = mt_rand(1, $h);
                $amount      = mt_rand(1, 15);
                $stringcolor = imagecolorallocate($image, 175, 175, 175);

                for ($n = 0; $n < $amount; $n++)
                {

                    $signs = range('A', 'Z');
                    $sign  = $signs[mt_rand(0, count($signs) - 1)];

                    imagettftext($image, 25,
                    mt_rand(-15, 15),
                    $centerX + mt_rand(-50, 50),
                    $centerY + mt_rand(-50, 50),
                    $stringcolor, $font, $sign);

                }

            }

        } //signs

        function blur(&$image, $radius = 3)
        {

            $radius  = round(max(0, min($radius, 50)) * 2);

            $w       = imagesx($image);
            $h       = imagesy($image);

            $imgBlur = imagecreate($w, $h);

            for ($i = 0; $i < $radius; $i++)
            {

                imagecopy     ($imgBlur, $image,   0, 0, 1, 1, $w - 1, $h - 1);
                imagecopymerge($imgBlur, $image,   1, 1, 0, 0, $w,     $h,     50.0000);
                imagecopymerge($imgBlur, $image,   0, 1, 1, 0, $w - 1, $h,     33.3333);
                imagecopymerge($imgBlur, $image,   1, 0, 0, 1, $w,     $h - 1, 25.0000);
                imagecopymerge($imgBlur, $image,   0, 0, 1, 0, $w - 1, $h,     33.3333);
                imagecopymerge($imgBlur, $image,   1, 0, 0, 0, $w,     $h,     25.0000);
                imagecopymerge($imgBlur, $image,   0, 0, 0, 1, $w,     $h - 1, 20.0000);
                imagecopymerge($imgBlur, $image,   0, 1, 0, 0, $w,     $h,     16.6667);
                imagecopymerge($imgBlur, $image,   0, 0, 0, 0, $w,     $h,     50.0000);
                imagecopy     ($image  , $imgBlur, 0, 0, 0, 0, $w,     $h);

            }

            imagedestroy($imgBlur);

        } //blur

    } //class: filters

?>