<?php

class Clasiffier
{
    public $pathToFiles;
    public $cropWidth;
    public $cropHeight;
    public $cropOffsetX;
    public $cropOffsetY;
    public $acceptedCharacters;
    public $DS;

    public function __construct()
    {
        $config = @file_get_contents('config.json');

        if ($config) {
            $config = json_decode($config);
        } else {
            die('No config file was found');
        }

        $this->pathToFiles = $config->pathToFiles;
        $this->cropWidth = $config->cropWidth;
        $this->cropHeight = $config->cropHeight;
        $this->cropOffsetX = $config->cropOffsetX;
        $this->cropOffsetY = $config->cropOffsetY;
        $this->contrast = $config->contrast;
        $this->acceptedCharacters = $this->whitelistOCR(range('A','Z'), range('0','9'), '-:');
        $this->DS = $config->directory_separator;
        $this->gsCmd = $config->ghostscript_cmd;

        $this->init();
    }

    /**
     * Init function
     * @author Joel Mora
     */
    public function init()
    {
        $pdfs = scandir($this->pathToFiles);

        foreach ($pdfs as $i => $pdf) {

            $pathInfo = pathinfo($pdf);
            $filename = $pathInfo['filename'];
            $fullPathPdf = $this->getFullPath($filename . '.pdf');

            if (!file_exists($fullPathPdf)) {
                continue;
            }

            if ($pathInfo['extension'] == 'pdf') {
                $this->processPdf($pdf);
            }
        }
    }

    /**
     * Process the letter
     * @param $fileName
     * @author Joel Mora
     */
    public function processPdf($fileName)
    {
        $pathInfo = pathinfo($fileName);
        $filename = $pathInfo['filename'];
        $fullPathJpg = $this->getFullPath($filename . '.jpg');
        $fullPathPdf = $this->getFullPath($filename . '.pdf');
        $fullPathCroppedJpg = $this->getFullPath($filename . '_crop.jpg');

        //pdf to image
        exec("gs -sDEVICE=jpeg -dNOPAUSE -dQUIET -dBATCH -SOutputFile=$fullPathJpg -r144 $fullPathPdf");

        //crop the image
        $cropBox = $this->getCropBox();
        exec("convert $fullPathJpg -crop $cropBox $fullPathCroppedJpg");

        //resize the image
        $doubleSize = $this->cropWidth * 2.5 . 'x' . $this->cropHeight * 2.5;
        exec("convert $fullPathCroppedJpg -resize $doubleSize $fullPathCroppedJpg");

        //ocr
        $output = array();

        exec("tesseract $fullPathCroppedJpg stdout -c tessedit_char_whitelist=$this->acceptedCharacters", $output);

        foreach ($output as $line) {
            $tidMatch = array();

            //find TID code
            if (preg_match('/\w{2}-(\w)-\w{4}-\w/', $line, $tidMatch) === 1) {

                switch ($tidMatch[1]) {
                    //long
                    case 'B':
                        $type = 'B-first';
                        $this->writeFileOnFolder($fullPathPdf, $filename, $type);
                        break;
                    
                    //long
                    case 'E':
                        $type = 'E-long';
                        $this->writeFileOnFolder($fullPathPdf, $filename, $type);
                        break;

                    //short
                    case 'D':
                        $type = 'D-short';
                        $this->writeFileOnFolder($fullPathPdf, $filename, $type);
                        break;

                    //final
                    case 'F':
                        $type = 'F-long';
                        $this->writeFileOnFolder($fullPathPdf, $filename, $type);
                        break;

                    //unknown
                    default:
                        $type = 'Unclasiffied';
                        $this->writeFileOnFolder($fullPathPdf, $filename, $type);
                        break;
                }
            } else {
            }
        }

       //delete jpgs
       unlink($fullPathJpg);
       unlink($fullPathCroppedJpg);
    }

    /**
     * Transform a range of accepted character into 1 parameter to the CLI
     * @return mixed
     * @author Joel Mora
     */
    private function whitelistOCR()
    {
        $concatenate = function ($carry, $item) {
            return $carry . join('', (array)$item);
        };
        return array_reduce(func_get_args(), $concatenate, '');
    }

    /**
     * Get the crop box parameter from each individual crop variable
     * @return string
     * @author Joel Mora
     */
    private function getCropBox()
    {
        return $this->cropWidth . 'x' . $this->cropHeight . '+' . $this->cropOffsetX . '+' . $this->cropOffsetY;
    }

    /**
     * Get the absolute path given a filename
     * @param $fileName
     * @return string
     * @author Joel Mora
     */
    private function getFullPath($fileName)
    {
        return $this->pathToFiles . $this->DS . $fileName;
    }

    /**
     * Move the pdf file to the inner folder
     * @param $fullPathPdf
     * @param $filename
     * @param $subFolder
     * @author Joel Mora
     */
    public function writeFileOnFolder($fullPathPdf, $filename, $subFolder)
    {
        @mkdir($this->getFullPath($subFolder));
        @rename($fullPathPdf, $this->pathToFiles . $this->DS . $subFolder . $this->DS . $filename . '.pdf');
    }

}

new Clasiffier();