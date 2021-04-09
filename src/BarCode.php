<?php

declare(strict_types=1);

namespace App;

final class BarCode
{

    public const JPEG   = "jpeg";
    public const JPG    = "jpg";
    public const PNG    = "png";
    public const GIF    = "gif";

    /** @var object */
    private $dimensions;

    /** @var object */
    private $position;

    /** @var object */
    private $padding;

    /** @var object */
    private $colors;

    public function __construct()
    {
        $this->dimensions =  (object) [
            "width"     => 0,   // largura da barra
            "height"    => 0    // altura da barra
        ];

        $this->position = (object) [
            "x1" => 0,  // x canto superior esquerdo
            "y1" => 0,  // y canto superior esquerdo
            "x2" => 0,  // x canto inferior direito
            "y2" => 0,  // y canto inforior direito
        ];

        $this->padding = (object) [
            "top"       => 3,   // padding top
            "bottom"    => 3,   // padding bottom
            "left"      => 5,   // padding left
            "right"     => 5    // padding rigth
        ];

        $this->colors = (object) [
            "white" => fn($image) =>  imagecolorallocate($image,  255, 255, 255),
            "black" => fn($image) =>  imagecolorallocate($image,    0,   0,   0)
        ];
    }

    /**
     * @return resource
     */
    public function getResource(string $code)
    {
        $width  = strlen($code) + $this->padding->left + $this->padding->right;
        $height = 50;

        $image = imagecreatetruecolor($width, $height);

        $this->dimensions->width  = ($width   - ($this->padding->left   + $this->padding->right   )) / strlen($code);
        $this->dimensions->height = $height;

        $this->position->x1 = $this->padding->left;
        $this->position->x2 = $this->padding->left + $this->dimensions->width;

        $this->position->y1 = $this->padding->bottom;
        $this->position->y2 = $this->dimensions->height - $this->padding->top - 1;

        imagefill($image, 0, 0, ($this->colors->white)($image));

        foreach (str_split($code) as $code) {

            $color = $code == 1 ? ($this->colors->black)($image) : ($this->colors->white)($image);

            imagefilledrectangle($image, $this->position->x1, $this->position->y1, $this->position->x2, $this->position->y2, $color);

            $this->position->x1 += $this->dimensions->width;
            $this->position->x2 += $this->dimensions->width;
        }

        return $image;
    }

    /**
     * @throws \Exception
     * @return resource|string
     */
    public function draw(string $code, string $type, bool $isBase64Encoded = false)
    {
        ob_start();

        $resource = $this->getResource($code);

        switch ($type) {

            case self::JPEG || self::JPG:
                imagejpeg($resource);
                break;

            case self::PNG:
                imagepng($resource);
                break;

            case self::GIF:
                imagegif($resource);
                break;
            
            default:
                throw new \Exception("Type $type is not defined.", 1);
                break;
        }

        $content = ob_get_contents();

        ob_clean();

        if($isBase64Encoded){
            $content = base64_encode($content);
        }

        return $content;
    }
}