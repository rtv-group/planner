<?
class CounterWidget extends CWidget
{
    private $type = '';
    private $config;

    private $imgFolder = '/widgets-content/counter/';
    private $imageCacheTime = 1;

    private function getOutput()
    {
        if(!file_exists(dirname(Yii::app()->basePath) . DIRECTORY_SEPARATOR . $this->imgFolder . 'runtime')) {
            mkdir(dirname(Yii::app()->basePath) . DIRECTORY_SEPARATOR . $this->imgFolder . 'runtime');
        }

        return $this->imgFolder . 'runtime' . DIRECTORY_SEPARATOR . $this->config->output;
    }
    /*$config example
    * "header": "НОВИЙ РІК",
    * "below_header":"Почнеться через",
    * "footer":"ДНІВ",
    * "test":"1",
    * "test_current_time":"2016-12-30 22:40:11",
    * "logo": "logo",
    * "output": "counter.png",
    * "bg":"background.png"
    */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    private function checkConfig()
    {
        if (!isset($this->config->header)) {
            throw new Error (implode('',
                ['Widget ', __CLASS__, ' config error. No header text.']
            ));
        }

        if (!isset($this->config->below_header)) {
            throw new Error (implode('',
                ['Widget ', __CLASS__, ' config error. No below_header text.']
            ));
        }

        if (!isset($this->config->footer)) {
            throw new Error (implode('',
                ['Widget ', __CLASS__, ' config error. No footer text.']
            ));
        }

        if (!isset($this->config->footer2)) {
            throw new Error (implode('',
                ['Widget ', __CLASS__, ' config error. No footer2 text.']
            ));
        }

        if (!isset($this->config->logo)) {
            throw new Error (implode('',
                ['Widget ', __CLASS__, ' config error. No logo.']
            ));
        }

        if (!isset($this->config->output)) {
            throw new Error (implode('',
                ['Widget ', __CLASS__, ' config error. No output image name.']
            ));
        }

        if (!isset($this->config->bg)) {
            throw new Error (implode('',
                ['Widget ', __CLASS__, ' config error. No bg image name.']
            ));
        }

        $bg = dirname(Yii::app()->basePath)
          . DIRECTORY_SEPARATOR . $this->imgFolder
          . DIRECTORY_SEPARATOR . $this->config->bg;

        if (!file_exists($bg)) {
            throw new Error (implode('',
                ['Widget ', __CLASS__, ' configured background unexist.']
            ));
        }
    }

    private function generateImage()
    {
        $image = @imagecreatetruecolor(400, 300);
        imagesavealpha($image, true);
        if (!$image) {
            throw new Error (implode('',
                ['Widget ', __CLASS__, ' impossible to create image stream.']
            ));
        }

        $color = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $color);

        $logo = dirname(Yii::app()->basePath)
          . DIRECTORY_SEPARATOR . $this->imgFolder
          . DIRECTORY_SEPARATOR . 'logo.png';

        if (!file_exists($logo)) {
            throw new Error (implode('',
                ['Widget ', __CLASS__, ' logo unexist.']
            ));
        }

        $bg = dirname(Yii::app()->basePath)
          . DIRECTORY_SEPARATOR . $this->imgFolder
          . DIRECTORY_SEPARATOR . $this->config->bg;

        $bg = imagecreatefrompng($bg);
        $logo = imagecreatefrompng($logo);

        $textColor = imagecolorallocatealpha($image, 255, 255, 255, 0);

        imagecopy($image, $bg, 0, 0, 0, 0, 400, 300);
        imagecopy($image, $logo, 168, 224, 0, 0, imagesx($logo), imagesx($logo));
        putenv('GDFONTPATH=' . dirname(Yii::app()->basePath)
          . DIRECTORY_SEPARATOR . 'css'
          . DIRECTORY_SEPARATOR . 'fonts');

        $font = getenv('GDFONTPATH') . DIRECTORY_SEPARATOR . 'arialbd.ttf';

        $counter = $this->daysLeft();

        if ($this->daysLeft() < 5) {
            $counter = $this->hoursLeft();
        }

        $counterXpos = 174 - (strlen($counter) - 1) * 10;

        imagettftext($image, 24, 0, 120, 60, $textColor, $font, $this->config->header);
        imagettftext($image, 24, 0, 70, 95, $textColor, $font, $this->config->below_header);
        imagettftext($image, 54, 0, $counterXpos, 170, $textColor, $font, $counter);
        if ($this->daysLeft() < 5) {
            imagettftext($image, 24, 0, 150, 204, $textColor, $font, $this->config->footer2);
        } else {
            imagettftext($image, 24, 0, 166, 204, $textColor, $font, $this->config->footer);
        }

        if (isset($this->config->test_current_time)) {
            imagettftext($image, 12, 0, 10, 20, $textColor, $font, date('y-m-d H:i:s'));
        }

        imagepng($image, dirname(Yii::app()->basePath) . $this->getOutput(), 1);
        imagedestroy($image);

        return;
    }

    private function daysLeft()
    {
        $datetime1 = new DateTime();
        if (isset($this->config->test_current_time)) {
            $datetime1 = date_create($this->config->test_current_time);
        }
        $datetime2 = date_create('2017-01-01 00:00:00');

        $interval = date_diff($datetime1, $datetime2);

        return $interval->format('%d');
    }

    private function hoursLeft()
    {
        $datetime1 = new DateTime();
        if (isset($this->config->test_current_time)) {
            $datetime1 = date_create($this->config->test_current_time);
        }
        $datetime2 = date_create('2017-01-01 00:00:00');

        $interval = date_diff($datetime1, $datetime2);

        return $interval->format('%d') * 24 + $interval->format('%H');
    }

    public function run()
    {
        $this->checkConfig();

        if (($this->type !== '') && method_exists($this, $this->type)) {
            call_user_func([$this, $this->type]);
            return;
        }

        throw new Error (implode('',
            ['Widget ', __CLASS__, ' does not contain method ', $this->type, '.']
        ));
    }

    public function preview()
    {
        $file = dirname(Yii::app()->basePath) . DIRECTORY_SEPARATOR . $this->getOutput();
        if (file_exists ($file)) {
            $fileCreated = filemtime (dirname(Yii::app()->basePath) . DIRECTORY_SEPARATOR . $this->getOutput());

            if ((time() - $fileCreated) < $this->imageCacheTime) {
                echo sprintf('<img class="widget-preview-img" src="%s" alt="Counter"/>', $this->getOutput());
                exit;
            }
        }

        $this->generateImage();
        echo sprintf('<img class="widget-preview-img" src="%s" alt="Counter"/>', $this->getOutput());
        exit;
    }

    public function showData()
    {
        $file = dirname(Yii::app()->basePath) . DIRECTORY_SEPARATOR . $this->getOutput();
        if (file_exists ($file)) {
            $fileCreated = filemtime (dirname(Yii::app()->basePath) . DIRECTORY_SEPARATOR . $this->getOutput());

            if ((time() - $fileCreated) > $this->imageCacheTime) {
                $this->generateImage();
            }
        } else {
            $this->generateImage();
        }

        return ['img' => $this->getOutput()];
    }
}
