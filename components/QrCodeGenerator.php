<?php
namespace app\components;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeGenerator
{
    /**
     * @param $url
     * @param $size
     * @return string
     * @throws \Exception
     */
    public function generate($url, $size = 300)
    {
        $qrCode = new QrCode($url);
        $qrCode->setSize($size);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return $result->getString();
    }

    /**
     * @param $url
     * @param $size
     * @return string
     * @throws \Exception
     */
    public function generateBase64($url, $size = 300)
    {
        $qrCodeImage = $this->generate($url, $size);
        return 'data:image/png;base64,' . base64_encode($qrCodeImage);
    }
}