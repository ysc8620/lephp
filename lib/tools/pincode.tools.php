<?php
include_once 'pinstore.php';
class tools_pincode {
    
	//验证码
	const PINCODE = "pincode";
	
    //a kind of image's sizes:huge
    const HUGE = "huge";
    
    //a kind of image's sizes:normal
    const NORMAL = "normal";
    
    //a kind of image's sizes:SMALL
    const SMALL = "small";
    
    //a kind of image's sizes:TINY
    const TINY = "tiny";
    
    const BIG = "big";
    
    //image's type:jpeg
    const JPEG = "JPEG";
    
    //image's type:png
    const PNG = "PNG";
    
    //image's type:gif
    const GIF = "GIF";
    
    //image's type:wbmp
    const WBMP = "WBMP";
    
    //image is pretty
    const PRETTY = "pretty";
    
    //image is ugly
    const UGLY = "ugly";
    
    //image is ugly
    const CHINESE = "chinese";
    
    const CHINESE_IFRAME = "chinese_iframe";
    
    const ENGLISH = "english";
    
    const RULE = 'rule';
    
    const ENGLISH_COMPLEX = 'english_complex';
    
    const CODE_NAME = 'ULOGIN_IMG';
    
    //encrypting method
    private $encryptmethod;
    
    //the final encrypted string
    private $encryptstr;
    
    //the key for encrypt
    private $encrypt_key;
    
    //image's imgwidth
    private $imgwidth;
    
    //image's imgheight
    private $imgheight;
    
    //image's type:gif,jpeg,png,wbmp
    private $imgtype;
    
    //image is pretty or ugly
    private $imgoutlook;
    
    //construction function
    private $lang;
    
    public function __construct($encrypt_key) {
        
        $this->encryptstr = "";
        $this->encrypt_key = $encrypt_key;
        $this->setimginfo();
    
    }
    
    /*fname:setimginfo

         *func:set image's size and type

         *param:set image's size ,type and outlook,default, normal,png,pretty

         *ret:no return

         */
    
    public function setimginfo($size = self::NORMAL, $type = self::PNG, $outlook = self::UGLY, $lang = 'zh') {
        //set image's size
        if ($size == self::HUGE) {
            $this->imgwidth = 240;
            $this->imgheight = 90;
        } else if ($size == self::SMALL) {
            $this->imgwidth = 60;
            $this->imgheight = 30;
        } else if ($size == self::TINY) {
            $this->imgwidth = 40;
            $this->imgheight = 15;
        } else if ($size == self::BIG) {
            $this->imgwidth = 114;
            $this->imgheight = 34;
        } else if ($size == self::RULE) {
            $this->imgwidth = 450;
            $this->imgheight = 50;
        }else if($size == self::PINCODE){
        	$this->imgwidth = 114;
            $this->imgheight = 34;
        } else {
            $this->imgwidth = 80;
            $this->imgheight = 30;
        }
        $this->lang = $lang;
        if ($outlook == self::PRETTY || $outlook == self::UGLY || $outlook == self::CHINESE || $outlook == self::ENGLISH || $outlook == self::CHINESE_IFRAME || $outlook == self::RULE || $outlook == self::ENGLISH_COMPLEX) {
            $this->imgoutlook = $outlook;
        } else {
            $this->imgoutlook = self::UGLY;
        }
        $gdinfo = gd_info();
        if ($this->imgoutlook == self::PRETTY && !$gdinfo["FreeType Support"]) {
            $this->_seterror("php doesn't support freetype", -3);
            $this->imgoutlook = "";
            return false;
        }
        //set image's type
        if ($type == self::JPEG || $type == self::GIF || $type == self::WBMP || $type == self::PNG) {
            $this->imgtype = $type;
        } else {
            $this->imgtype = self::PNG;
        }
        $gdinfo = gd_info();
        if (!$gdinfo[$this->imgtype . " Support"]) {
            $this->_seterror("This type of image isn't supportted", -3);
            $this->imgtype = "";
            return false;
        }
        return true;
    }
    
    /*fname:getimginfo

         *func:get image's size and type

         *param:no parameter needed

         *ret:an array consists of image's width height and type

         */
    
    public function getimginfo() {
        
        $imginfo["width"] = $this->imgwidth;
        
        $imginfo["height"] = $this->imgheight;
        
        $imginfo["type"] = $this->type;
        
        $imginfo["outlook"] = $this->outlook;
        
        return $imginfo;
    
    }
    
    /*fname:getrand

         *func:generate a number at random

         *param:minimum and maximum of random number

         *ret:random number

         */
    
    private function getrand($min, $max) {
        
        $n = (double)rand();
        
        $n = $min + ((double)($max - $min + 1.0) * 

        ($n / (getrandmax() + 1.0)));
        
        return $n;
    
    }
    
    /*fname:generate_encrypt_string

         *func:generate an encrypting string

         *param:source string

         *ret:boolean value

         */
    
    private function generate_encrypt_string($pincode) {
        $this->encryptstr = str_replace(".", "", microtime(1));
        $source = $this->encrypt_key . $this->encryptstr;
        $source = md5($source);
        //add encryptstr=>pincode to memcache
        $pinobj = new PinStore();
        if (!$pinobj->add_pin($source, $pincode)) {
            return false;
        }
        return true;
    }
    
    public function valide_code($input, $encryptstr) {
        $key = md5($this->encrypt_key . $encryptstr);
        
        $pin_store = new PinStore();
        $code = $pin_store->get_pin($key);
        //$pin_store->del_pin($key);

        //多语言版本答案验证
        if (is_array($code)) {
            if ($input == $code['zh-cn'] || $input == $code['zh-tw']) {
                return true;
            } else {
                return false;
            }
        } else if ($code != $input) {
            return false;
        }
        return true;
    }
    
    public function set_code_key() {
        //print http header
        header('Set-Cookie: ' . self::CODE_NAME . '=' . $this->encryptstr . '; domain=chinaface.com; path=/');
    }
    
    /*fname:generate_image

         *func:generate pincode image base on the outlook

         *param:code

         *ret:boolean value

        */
    
    public function generate_image($code) {
        
        $func = "generate_image_" . $this->imgoutlook;
       
        return $this->$func($code);
    
    }
    
    /*fname:generate_image_ugly

         *func:generate pincode image which looks not so pretty

         *param:code

         *ret:boolean value

        */
    
    private function generate_image_ugly($code) {
        $pincode = (int)(1000000.0 * $code / (mt_getrandmax() + 1.0));
        
        $pincode = strval($pincode);
        
        if (!$this->generate_encrypt_string($pincode)) 

        {
            
            return false;
        
        }
        
        //print http header
        $this->set_code_key();
        
        //set-cookie must be printed befor this
        header("Content-type: image/" . $this->imgtype . "\n\n");
        
        //omit return value
        

        //create an image
        

        $img = imagecreate($this->imgwidth, $this->imgheight);
        
        //allocate color for image
        

        $color["white"] = imagecolorallocate($img, 255, 255, 255);
        
        $color["black"] = imagecolorallocate($img, 0, 0, 0);
        
        $color["gray"] = imagecolorallocate($img, 40, 40, 40);
        
        $color["point"] = imageColorallocate($img, 80, 180, 40);
        
        //(int)$num is equal to intval( $num)
        

        for($i = 0; $i < (rand() % intval(

        $this->imgwidth * $this->imgheight / 200)); $i ++) 

        {
            
            //draw a line
            

            imageline($img, rand() % $this->imgwidth, 

            rand() % $this->imgheight, 

            rand() % $this->imgwidth, 

            rand() % $this->imgheight, $color["black"]);
        
        }
        
        for($i = 0; $i < intval(

        $this->imgwidth * $this->imgheight / 20); $i ++) 

        {
            
            //draw a single pixel
            

            imagesetpixel($img, rand() % $this->imgwidth, 

            rand() % $this->imgheight, $color["black"]);
        
        }
        
        //draw pincode horizontally
        

        $font = 5;
        
        imagestring($img, $font, 

        (imagesx($img) / 2 - strlen($pincode) * imagefontwidth($font) / 2), 

        (imagesy($img) / 2 - imagefontheight($font) / 2), 

        $pincode, $color["black"]);
        
        $showimgfunc = "image" . $this->imgtype;
        
        $showimgfunc($img);
        
        //destroy image
        

        imagedestroy($img);
        
        return true;
    
    }
    
    /*fname:generate_image_pretty

         *func:generate pincode image which looks good

         *param:code

         *ret:boolean value

        */
    
    private function generate_image_pretty($code) {
        
        //files needed
        

        $ffiles[0] = "/usr/local/share/fonts/bookos.ttf";
        
        $ffiles[1] = "/usr/local/share/fonts/cour.ttf";
        
        $ffiles[2] = "/usr/local/share/fonts/georgia.ttf";
        
        $ffiles[3] = "/usr/local/share/fonts/gothic.ttf";
        
        foreach ($ffiles as $fontfile) 

        {
            
            if (!file_exists($fontfile)) 

            {
                
                $this->_seterror("Font file doesn't exist", -4);
                
                return false;
            
            }
        
        }
        
        $count = 0;
        
        $pincode = 100000;
        
        while ($pincode >= 100000 && ($count ++) < 10) 

        {
            
            $pincode = intval((1000000.0 * $code / (rand() + 1.0)));
        
        }
        
        $pinstr = strval($pincode);
        
        if (!$this->generate_encrypt_string($pinstr)) 

        {
            
            return false;
        
        }
        
        //print http header
        $this->set_code_key();
        
        //set-cookie must be printed befor this
        header("Content-type: image/" . $this->imgtype . "\n\n");
        
        //omit return value
        

        //create image
        $img = imagecreate($this->imgwidth, $this->imgheight);
        
        //get the index or the closest value of assigned color
        

        $colors[0] = imagecolorresolve($img, 255, 255, 255); //white
        

        $colors[1] = imagecolorresolve($img, 0, 0, 0); //black
        

        $colors[2] = imagecolorresolve($img, 9, 9, 53);
        
        $colors[3] = imagecolorresolve($img, 53, 9, 9);
        
        $colors[4] = imagecolorresolve($img, 10, 53, 10);
        
        $colors[5] = imagecolorresolve($img, 53, 52, 58);
        
        $colors[6] = imagecolorresolve($img, 41, 39, 29);
        
        $colors[7] = imagecolorresolve($img, 41, 44, 14);
        
        $colors[8] = imagecolorresolve($img, 16, 51, 54);
        
        $colors[9] = imagecolorresolve($img, 34, 54, 27);
        
        $colors[10] = imagecolorresolve($img, 71, 33, 16);
        
        $x = 2;
        
        $y = 20;
        
        $fakeimg = imagecreate($this->imgwidth, $this->imgheight);
        
        //draw some confusion lines
        

        for($i = 0; $i < strlen($pinstr); $i ++) {
            
            imagesetthickness($img, 3 * $i);
            
            $line_color = imagecolorallocate($img, rand(150, 255), rand(150, 255), rand(150, 255));
            
            //draw a line
            

            imageline($img, rand() % $this->imgwidth, 

            rand() % $this->imgheight, 

            rand() % $this->imgwidth, 

            rand() % $this->imgheight, $line_color);
        
        }
        
        for($i = 0; $i < strlen($pinstr); $i ++) {
            $size = $this->getrand(10, 15);
            $angle = $this->getrand(-1500, 1500) * M_PI / 180;
            $nFont = $this->getrand(0, sizeof($ffiles) - 1);
            //can not user colors[0](white), number won't be shown well in white
            $nColor = $this->getrand(1, sizeof($colors) - 1);
            $lastpos = array_fill(0, 7, 0);
            //draw virtually
            $lastpos = imagettftext($fakeimg, $size, $angle, 0, 0, 0, $ffiles[$nFont], $pinstr[$i]);
            
            if ($lastpos[0] > $lastpos[6]) {
                $leftlean = TRUE;
            } else {
                $leftlean = FALSE;
            }
            
            $drift_x = $leftlean ? $lastpos[0] - $lastpos[6] : 0;
            //draw a real number of pincode
            $lastpos = imagettftext($img, $size, $angle, $x + $drift_x, $y, $colors[$nColor], $ffiles[$nFont], $pinstr[$i]);
            $x += $leftlean ? $lastpos[2] - $lastpos[6] : $lastpos[4] - $lastpos[0] + 1;
        }
        for($i = 0; $i < intval($this->imgwidth * $this->imgheight / 70); $i ++) {
            //draw a pixel
            imagesetpixel($img, rand() % $this->imgwidth, rand() % $this->imgheight, $colors[1]);
        }
        
        $showimgfunc = "image" . $this->imgtype;
        $showimgfunc($img);
        //destroy image
        imagedestroy($img);
        
        return true;
    
    }
    
    /*fname:generate_image_pretty

         *func:generate pincode image which looks good

         *param:code

         *ret:boolean value

        */
    
    private function generate_image_chinese($code) {
        
        //files needed
        $ffiles[0] = "/usr/share/fonts/wqy-zenhei/wqy-zenhei.ttc";
        
        $ffiles[1] = "/usr/share/fonts/wqy-zenhei/wqy-zenhei.ttc";
        
        $ffiles[2] = "/usr/share/fonts/wqy-zenhei/wqy-zenhei.ttc";
        
        $ffiles[3] = "/usr/share/fonts/wqy-zenhei/wqy-zenhei.ttc";
        
        foreach ($ffiles as $fontfile) {
            if (!file_exists($fontfile)) {
                $this->_seterror("Font file doesn't exist", -4);
                return false;
            }
        
        }
        
        $string = "七万三上下不与专且世业东丝两严中为举乃久义之乎乐九习书乱争二于五亡亦京亲人仁今从仕令以仪众传伦体何作信俱元兄先光全八公六共兴其具典养兼内再农冬凡出分刘则创初别刺前力功劳北十千南及友发受变口古句可史号司同名后吐君吟吴周命和哀四国土圣在地士壮声处备夕多夜大天太夫失头奇女如妇始子字存孙孝孟学宇守宋官宜实宣家容对小少尔尚尼居山岁左师帝干平年并庄序应建异弟强归当录彼心必志忠恭戈戏成戒战所才改效文断斯方族无既日早时明易星映春是最月有朋朝木本机杨某止正此武母氏民水求汉汤泉注火爱父牛玉王琴生男百目相知石礼社祖神秋秦称究穷立童竹笔系羊群老考者而股能臣自至致苏虽蚕行衰西要见角言让记讲论识诗详语说读谷负贤贫贵赵起身辽迁过运近远连邻金长闯闻陈除雪非革音项顺风食首香马高鲁鸡麦齐";
        $str = '';
        
        for($i = 0; $i < 2; $i ++) {
            $_i = rand(1, 330);
            $str .= mb_substr($string, ($_i - 1) * 3, 3);
        }
        
        if (!$this->generate_encrypt_string($str)) {
            return false;
        }
        //$str = iconv('gb2312', 'utf-8', $str);
        //print http header
        $this->set_code_key();
        //set-cookie must be printed befor this
        header("Content-type: image/" . $this->imgtype . "\n\n");
        //omit return value
        //create image
        

        $img = imagecreate($this->imgwidth, $this->imgheight);
        //get the index or the closest value of assigned color
        $colors[0] = imagecolorresolve($img, 255, 255, 255); //white
        $colors[1] = imagecolorresolve($img, 0, 0, 0); //black
        $colors[2] = imagecolorresolve($img, 9, 9, 53);
        $colors[3] = imagecolorresolve($img, 53, 9, 9);
        $colors[4] = imagecolorresolve($img, 10, 53, 10);
        $colors[5] = imagecolorresolve($img, 53, 52, 58);
        $colors[6] = imagecolorresolve($img, 41, 39, 29);
        $colors[7] = imagecolorresolve($img, 41, 44, 14);
        $colors[8] = imagecolorresolve($img, 16, 51, 54);
        $colors[9] = imagecolorresolve($img, 34, 54, 27);
        $colors[10] = imagecolorresolve($img, 71, 33, 16);
        
        $x = 2;
        $y = 20;
        
        $fakeimg = imagecreate($this->imgwidth, $this->imgheight);
        
        //draw some confusion lines
        for($i = 0; $i < strlen($str); $i ++) {
            imagesetthickness($img, 3 * $i);
            $line_color = imagecolorallocate($img, rand(150, 255), rand(150, 255), rand(150, 255));
            //draw a line
            imageline($img, rand() % $this->imgwidth, rand() % $this->imgheight, rand() % $this->imgwidth, rand() % $this->imgheight, $line_color);
        }
        
        for($i = 0; $i < strlen($str); $i += 3) {
            $size = $this->getrand(10, 15);
            $angle = $this->getrand(-1500, 1500) * M_PI / 180;
            $nFont = $this->getrand(0, sizeof($ffiles) - 1);
            //can not user colors[0](white), number won't be shown well in white
            $nColor = $this->getrand(1, sizeof($colors) - 1);
            $lastpos = array_fill(0, 7, 0);
            //draw virtually
            $lastpos = imagettftext($fakeimg, $size, $angle, 0, 0, 0, $ffiles[$nFont], $str[$i] . $str[$i + 1] . $str[$i + 2]);
            
            if ($lastpos[0] > $lastpos[6]) {
                $leftlean = TRUE;
            } else {
                $leftlean = FALSE;
            }
            
            $drift_x = $leftlean ? $lastpos[0] - $lastpos[6] : 0;
            //draw a real number of pincode
            // write double chars by chinese
            $x += $i + rand(20, 35);
            $y = rand(18, 25);
            $size = rand(14, 18);
            $lastpos = imagettftext($img, $size, $angle, $x, $y, $colors[$nColor], $ffiles[$nFont], $str[$i] . $str[$i + 1] . $str[$i + 2]);
            /*
            $lastpos = imagettftext( $img, $size, $angle,
                    $x+$drift_x, $y, $colors[$nColor],
                    $ffiles[$nFont], $str[$i].$str[$i+1].$str[$i+2]);
*/
        //$x += $i + rand(20,80);
        //$x += $leftlean ? $lastpos[2]- $lastpos[6] : $lastpos[4] - $lastpos[0] + 1;
        }
        
        for($i = 0; $i < intval($this->imgwidth * $this->imgheight / 70); $i ++) {
            //draw a pixel
            imagesetpixel($img, rand() % $this->imgwidth, rand() % $this->imgheight, $colors[1]);
        }
        
        $showimgfunc = "image" . $this->imgtype;
        $showimgfunc($img);
        
        //destroy image
        imagedestroy($img);
        
        return true;
    
    }
    
    private function _seterror($msg, $type) {
        return false;
    }
    
    private function generate_image_english($code) {
        //files needed
       
        $ffiles[0] = "/usr/share/fonts/wqy-zenhei/wqy-zenhei.ttc";
        $ffiles[1] = "/usr/share/fonts/wqy-zenhei/wqy-zenhei.ttc";
        $ffiles[2] = "/usr/share/fonts/wqy-zenhei/wqy-zenhei.ttc";
        $ffiles[3] = "/usr/share/fonts/wqy-zenhei/wqy-zenhei.ttc";
        $string = 'QWERYUPKHGFSA2345678ZXCVBNMqweyupkhfds2345678azxcvbnm2345678';
        $pinstr = '';
        
        for($i = 0; $i < 4; $i ++) {
            $pinstr .= $string[rand(0, 59)];
        }
        
        if (!$this->generate_encrypt_string(strtolower($pinstr))) {
            return false;
        }
        
        //print http header
        $this->set_code_key();
        //set-cookie must be printed befor this
        header("Content-type:image/" . $this->imgtype . "\n\n");
        //omit return value
        //create image
        $img = imagecreatetruecolor($this->imgwidth, $this->imgheight);
        
        imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255));
        //get the index or the closest value of assigned color
        $colors[0] = imagecolorresolve($img, 255, 255, 255); //white
        $colors[1] = imagecolorresolve($img, 0, 0, 0); //black
        $colors[2] = imagecolorresolve($img, 9, 9, 53);
        $colors[3] = imagecolorresolve($img, 53, 9, 9);
        $colors[4] = imagecolorresolve($img, 10, 53, 10);
        $colors[5] = imagecolorresolve($img, 53, 52, 58);
        $colors[6] = imagecolorresolve($img, 41, 39, 29);
        $colors[7] = imagecolorresolve($img, 41, 44, 14);
        $colors[8] = imagecolorresolve($img, 16, 51, 54);
        $colors[9] = imagecolorresolve($img, 34, 54, 27);
        $colors[10] = imagecolorresolve($img, 71, 33, 16);
        
        $x = 2;
        $y = 20;
        
        $fakeimg = imagecreate($this->imgwidth, $this->imgheight);
        //draw some confusion lines
        

        for($i = 0; $i < strlen($pinstr); $i ++) {
            imagesetthickness($img, 3 * $i);
            $line_color = imagecolorallocate($img, rand(150, 255), rand(150, 255), rand(150, 255));
            //draw a line
            imageline($img, rand() % $this->imgwidth, rand() % $this->imgheight, rand() % $this->imgwidth, rand() % $this->imgheight, $line_color);
        
        }
        
        for($i = 0; $i < strlen($pinstr); $i ++) {
            // edit by hqlong at 2010/08/04 change (12,18) to (13,18)
            $size = $this->getrand(13, 18);
            // end edit
            $angle = $this->getrand(-1500, 1500) * M_PI / 180;
            $nFont = $this->getrand(0, sizeof($ffiles) - 1);
            
            //can not user colors[0](white), number won't be shown well in white
            $nColor = $this->getrand(1, sizeof($colors) - 1);
            $lastpos = array_fill(0, 7, 0);
            
            //draw virtually
            $lastpos = imagettftext($fakeimg, $size, $angle, 0, 0, 0, $ffiles[$nFont], $pinstr[$i]);
            if ($lastpos[0] > $lastpos[6]) {
                $leftlean = TRUE;
            } else {
                $leftlean = FALSE;
            }
            
            $drift_x = $leftlean ? $lastpos[0] - $lastpos[6] : 0;
            // add by hqlong at 2010/08/04 
            $x1 = $x + $drift_x;
            $i == 4 && $x1 > 85 && $x1 = 85;
            // end add hqlong
            //draw a real number of pincode
            //edit by hqlong at 2010/08/04
            $lastpos = imagettftext($img, $size, $angle, $x1, $y, //end edit hqlong
$colors[$nColor], $ffiles[$nFont], $pinstr[$i]);
            
            $x += $leftlean ? $lastpos[2] - $lastpos[6] : $lastpos[4] - $lastpos[0] + 1;
        
        }
        
        for($i = 0; $i < intval($this->imgwidth * $this->imgheight / 70); $i ++) {
            //draw a pixel
            imagesetpixel($img, rand() % $this->imgwidth, rand() % $this->imgheight, $colors[1]);
        }
        
        $flex = true;
        
        if ($flex) {
            $distortion_im = imagecreatetruecolor($this->imgwidth * 1.3, $this->imgheight);
            imagefill($distortion_im, 0, 0, imagecolorallocate($distortion_im, 255, 255, 255));
            for($i = 0; $i < $this->imgwidth; $i ++) {
                for($j = 0; $j < $this->imgheight; $j ++) {
                    $rgb = imagecolorat($img, $i, $j);
                    if ((int)($i + 20 + sin($j / $this->imgheight * 2 * M_PI) * 10) <= imagesx($distortion_im) && (int)($i + 20 + sin($j / $this->imgheight * 2 * M_PI) * 10) >= 0) {
                        imagesetpixel($distortion_im, (int)($i + 10 + sin($j / $this->imgheight * 2 * M_PI - M_PI * 0.5) * 3), $j, $rgb);
                    }
                }
            }
        }
        
        $showimgfunc = "image" . $this->imgtype;
        
        if ($flex) {
            $showimgfunc($distortion_im);
            imagedestroy($img);
            imagedestroy($distortion_im);
        } else {
            $showimgfunc($img);
            imagedestroy($img);
        
        }
        return true;
    
    }
    
    private function generate_image_english_complex($code) {
        //字体资源
        $ffiles[0] = "/usr/local/srv2/lib/X11/fonts/TTF/wqy-zenhei.ttf";
        $string = 'abcdefghkmnopqrstuvwxyz';
        $pinstr = '';
        for($i = 0; $i < 5; $i ++) {
            $pinstr .= $string[mt_rand(0, 22)];
        }
        
        if (!$this->generate_encrypt_string($pinstr)) {
            return false;
        }
        
        //print http header
        $this->set_code_key();
        //set-cookie must be printed befor this
        header("Content-type:image/" . $this->imgtype . "\n\n");
        
        //create image
        $img = imagecreatetruecolor($this->imgwidth, $this->imgheight);
        imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255));
        $colors = array(
            array(221, 40, 9), // 红
            array(26, 161, 40), // 绿
            array(30, 79, 184) // 蓝
        );
        
        $color = $colors[mt_rand(0, sizeof($colors) - 1)];
        $ft_color = imagecolorallocate($img, $color[0], $color[1], $color[2]);
        
        //随机一条干扰线
        imagesetthickness($img, 2);
        $x1 = mt_rand() % ($this->imgwidth / 4);
        $y1 = mt_rand() % $this->imgheight;
        $x2 = mt_rand() % ($this->imgwidth / 4) + $this->imgwidth / 2;
        $y2 = $this->imgheight - $y1;
        imageline($img, $x1, $y1, $x2, $y2, $ft_color);
        
        $x = 0;
        $y = 26;
        imagesetthickness($img, 2);
        for($i = 0; $i < strlen($pinstr); $i ++) {
            $size = $this->getrand(23, 25);
            $angle = $this->getrand(-10, 10);
            $coords = imagettftext($img, $size, $angle, $x, $y, $ft_color, $ffiles[0], $pinstr[$i]);
            $x += ($coords[2] - $x) + (-2);
        }
        
        $flex = true;
        if ($flex) { // flex
            $distortion_im = imagecreatetruecolor($this->imgwidth * 1.2, $this->imgheight);
            imagefill($distortion_im, 0, 0, imagecolorallocate($distortion_im, 255, 255, 255));
            for($i = 0; $i < $this->imgwidth; $i ++) {
                for($j = 0; $j < $this->imgheight; $j ++) {
                    $rgb = imagecolorat($img, $i, $j);
                    imagesetpixel($distortion_im, (int)($i + sin($j / $this->imgheight * 2 * M_PI - 0.5 * M_PI) * 3), $j, $rgb);
                }
            }
        }
        
        $showimgfunc = "image" . $this->imgtype;
        if ($flex) {
            $showimgfunc($distortion_im);
            imagedestroy($img);
            imagedestroy($distortion_im);
        } else {
            $showimgfunc($img);
            imagedestroy($img);
        }
        return true;
    }
    
    private function generate_image_chinese_iframe($code) {
        //files needed
        $ffiles[0] = "/usr/local/srv2/lib/X11/fonts/TTF/wqy-zenhei.ttf";
        
        $ffiles[1] = "/usr/local/srv2/lib/X11/fonts/TTF/wqy-zenhei.ttf";
        
        $ffiles[2] = "/usr/local/srv2/lib/X11/fonts/TTF/wqy-zenhei.ttf";
        
        $ffiles[3] = "/usr/local/srv2/lib/X11/fonts/TTF/wqy-zenhei.ttf";
        
        foreach ($ffiles as $fontfile) {
            if (!file_exists($fontfile)) {
                $this->_seterror("Font file doesn't exist", -4);
                return false;
            }
        }
        
        $string = "七万三上下不与专且世业东丝两严中为举乃久义之乎乐九习书乱争二于五亡亦京亲人仁今从仕令以仪众传伦体何作信俱元兄先光全八公六共兴其具典养兼内再农冬凡出分刘则创初别刺前力功劳北十千南及友发受变口古句可史号司同名后吐君吟吴周命和哀四国土圣在地士壮声处备夕多夜大天太夫失头奇女如妇始子字存孙孝孟学宇守宋官宜实宣家容对小少尔尚尼居山岁左师帝干平年并庄序应建异弟强归当录彼心必志忠恭戈戏成戒战所才改效文断斯方族无既日早时明易星映春是最月有朋朝木本机杨某止正此武母氏民水求汉汤泉注火爱父牛玉王琴生男百目相知石礼社祖神秋秦称究穷立童竹笔系羊群老考者而股能臣自至致苏虽蚕行衰西要见角言让记讲论识诗详语说读谷负贤贫贵赵起身辽迁过运近远连邻金长闯闻陈除雪非革音项顺风食首香马高鲁鸡麦齐";
        $str = '';
        
        for($i = 0; $i < 2; $i ++) {
            $_i = rand(1, 330);
            $str .= mb_substr($string, ($_i - 1) * 3, 3);
        }
        
        if (!$this->generate_encrypt_string_iframe($str)) {
            return false;
        }
        //$str = iconv('gb2312', 'utf-8', $str);
        //print http header
        //header("Set-Cookie: ULOGIN_IMG=".
        //set-cookie must be printed befor this
        header("Content-type: image/" . $this->imgtype . "\n\n");
        //omit return value
        //create image
        $img = imagecreate($this->imgwidth, $this->imgheight);
        
        //get the index or the closest value of assigned color
        $colors[0] = imagecolorresolve($img, 255, 255, 255); //white
        $colors[1] = imagecolorresolve($img, 0, 0, 0); //black
        $colors[2] = imagecolorresolve($img, 9, 9, 53);
        $colors[3] = imagecolorresolve($img, 53, 9, 9);
        $colors[4] = imagecolorresolve($img, 10, 53, 10);
        $colors[5] = imagecolorresolve($img, 53, 52, 58);
        $colors[6] = imagecolorresolve($img, 41, 39, 29);
        $colors[7] = imagecolorresolve($img, 41, 44, 14);
        $colors[8] = imagecolorresolve($img, 16, 51, 54);
        $colors[9] = imagecolorresolve($img, 34, 54, 27);
        $colors[10] = imagecolorresolve($img, 71, 33, 16);
        
        $x = 2;
        $y = 20;
        
        $fakeimg = imagecreate($this->imgwidth, $this->imgheight);
        
        //draw some confusion lines
        for($i = 0; $i < strlen($str); $i ++) {
            imagesetthickness($img, 3 * $i);
            $line_color = imagecolorallocate($img, rand(150, 255), rand(150, 255), rand(150, 255));
            //draw a line
            imageline($img, rand() % $this->imgwidth, rand() % $this->imgheight, rand() % $this->imgwidth, rand() % $this->imgheight, $line_color);
        }
        
        for($i = 0; $i < strlen($str); $i += 3) {
            $size = $this->getrand(10, 15);
            $angle = $this->getrand(-1500, 1500) * M_PI / 180;
            $nFont = $this->getrand(0, sizeof($ffiles) - 1);
            //can not user colors[0](white), number won't be shown well in white
            $nColor = $this->getrand(1, sizeof($colors) - 1);
            $lastpos = array_fill(0, 7, 0);
            //draw virtually
            $lastpos = imagettftext($fakeimg, $size, $angle, 0, 0, 0, $ffiles[$nFont], $str[$i] . $str[$i + 1] . $str[$i + 2]);
            if ($lastpos[0] > $lastpos[6]) {
                $leftlean = TRUE;
            } else {
                $leftlean = FALSE;
            }
            $drift_x = $leftlean ? $lastpos[0] - $lastpos[6] : 0;
            //draw a real number of pincode
            // write double chars by chinese
            $x += $i + rand(20, 35);
            $y = rand(18, 25);
            $size = rand(14, 18);
            $lastpos = imagettftext($img, $size, $angle, $x, $y, $colors[$nColor], $ffiles[$nFont], $str[$i] . $str[$i + 1] . $str[$i + 2]);
            /*
            $lastpos = imagettftext( $img, $size, $angle,
                    $x+$drift_x, $y, $colors[$nColor],
                    $ffiles[$nFont], $str[$i].$str[$i+1].$str[$i+2]);
*/
        //$x += $i + rand(20,80);
        //$x += $leftlean ? $lastpos[2]- $lastpos[6] : $lastpos[4] - $lastpos[0] + 1;
        }
        
        for($i = 0; $i < intval($this->imgwidth * $this->imgheight / 70); $i ++) {
            //draw a pixel
            imagesetpixel($img, rand() % $this->imgwidth, rand() % $this->imgheight, $colors[1]);
        }
        
        $showimgfunc = "image" . $this->imgtype;
        $showimgfunc($img);
        
        //destroy image
        imagedestroy($img);
        return true;
    }
    
    private function generate_encrypt_string_iframe($pincode, $mix) {
        $source = $mix . date('ymdh') . "iframe";
        $source = md5($source);
        //add encryptstr=>pincode to memcache
        $pinobj = new PinStore();
        if (!$pinobj->add_pin($source, $pincode)) {
            return false;
        }
        return true;
    }
    
    /*fname:generate_image_pretty

     *func:generate pincode image which looks good

     *param:code

     *ret:boolean value

    */

    private function generate_image_rule( $code)
    {
        //files needed
        $ffiles[0] = "/usr/local/srv2/lib/X11/fonts/TTF/wqy-zenhei.ttf";
        $ffiles[1] = "/usr/local/srv2/lib/X11/fonts/TTF/wqy-zenhei.ttf";
        $ffiles[2] = "/usr/local/srv2/lib/X11/fonts/TTF/wqy-zenhei.ttf";
        $ffiles[3] = "/usr/local/srv2/lib/X11/fonts/TTF/wqy-zenhei.ttf";

        foreach ( $ffiles as $fontfile)
        {
            if ( !file_exists( $fontfile))
            {
                $this->_seterror( "Font file doesn't exist", -4);
                return false;
            }
        }

        $numArr = array('1'  => 1, '2'  => 2, '3'  => 3, '4'  => 4,
                        '5'  => 5, '6'  => 6, '7'  => 7, '8'  => 8,
                        '9'  => 9, '0'  => 0);

        $questionArr = array(
            'zh-cn' => array(
            	"2月14日是什么节？(3个字)"				=>	"情人节",
            	"5月1日是什么节？(3个字)"				=>	"劳动节",
            	"6月1日是什么节？(3个字)"				=>	"儿童节",
            	"10月1日是什么节？(3个字)"				=>	"国庆节",
            	"2010世博会举办城市？(2个字)"			=>	"上海",
            	"海南省会是？(2个字)"					=>	"海口",
				"广东省会是？(2个字)"					=>	"广州",
            	"陕西省会是？(2个字)"					=>	"西安",
            	"黑龙江省会是？(3个字)"					=>	"哈尔滨",
            	"辽宁省会是？(2个字)"					=>	"沈阳",
            	"河北省会是？(3个字)"					=>	"石家庄",
            	"福建省会是？(2个字)"					=>	"福州",
            	"湖南省会是？(2个字)"					=>	"长沙",
            	"湖北省会是？(2个字)"					=>	"武汉",
            	"英国首都是？(2个字)"					=>	"伦敦",
            	"法国首都是？(2个字)"					=>	"巴黎",
            	"俄罗斯首都是？(3个字)"					=>	"莫斯科",
            	"天上飞的是轮船还是飞机？(2个字)"		=>	"飞机",
            	"足球与篮球哪个用脚踢？(2个字)"		=>	"足球",
            	"绿豆芽是黄豆还是绿豆长的？(2个字)"		=>	"绿豆",
            	"酱油与香油哪个是咸的？(2个字)"			=>	"酱油",
            	"老虎在动物园还是植物园？(3个字)"		=>	"动物园",
            	"铁轨上跑的是火车还是自行车？(2个字)"	=>	"火车",
            	"北极与赤道哪里热？(2个字)"				=>	"赤道",
				"电灯与电话哪个可以打电话？(2个字)"		=>	"电话",
				"手表与拖鞋哪个用于看时间？(2个字)"		=>	"手表",
				"大象还是蚂蚁的鼻子很长？(2个字)"			=>	"大象",
				"斑马与鳄鱼谁会潜水？(2个字)"			=>	"鳄鱼",
				"吃竹子的是鲸鱼还是熊猫？(2个字)"			=>	"熊猫",
				"北极熊与老虎谁长得像猫？(2个字)"		=>	"老虎",
				"香蕉与苹果哪个长得像地球？(2个字)"		=>	"苹果",
				"火炉与冰箱哪个可以冷冻食品？(2个字)"	=>	"冰箱",
				"书包与钱包哪个用于装书？(2个字)"		=>	"书包",
				
            ),
            'zh-tw' => array(
				"足球與籃球，哪個用腳踢？(2個字)"			=> "足球",
				"綠豆芽，是黃豆還是綠豆長的？(2個字)"		=> "綠豆",
				"老虎，在動物園還是植物園？(3個字)"		=> "動物園",
				"北極與赤道，哪裡熱？(2個字)"				=> "赤道",
				"電燈與電話，哪個用於照明？(2個字)"		=> "電燈",
				"手錶與拖鞋，哪個用於看時間？(2個字)"		=> "手錶",
				"大象與螞蟻，誰的鼻子很長？(2個字)"		=> "大象",
				"斑馬與鱷魚，誰會潛水？(2個字)"			=> "鱷魚",
				"鯨魚與貓熊，誰吃竹子？(2個字)"			=> "貓熊",
				"北極熊與老虎，誰長得像貓？(2個字)"		=> "老虎",
				"香蕉與蘋果，哪個長得像地球？(2個字)"		=> "蘋果",
				"火爐與冰箱，哪個可以冷凍食品？(2個字)"	=> "冰箱",
				"書包與錢包，哪個用於裝書？(2個字)"		=> "書包", 
            ),
        );
        $randArr = array('num'       => $numArr,
                         'question'  => $questionArr,
                     );
        if($this->lang == 'zh-cn') {
        	$type = array_rand($randArr);
        }else {
        	$type = 'num';
        }
        $str= '';
        if($type == 'num')
        {
            $operatorArr = array('+', '-', '*');

			$randkey = rand(0,2);				
			$operator = $operatorArr[$randkey];
            $equalStr = ' = ?';

            switch($operator)
            {
                case '-' :						
					$num1 = rand(16,30);
					$num2 = rand(1,15);
					$str = $num1 . " - " . $num2 .  $equalStr;						
					$pincode = (int)$num1-(int)$num2;
					break;
                case '*' :

				  $num1 = rand(1,9);
				  $num2 = rand(1,9);
				  $str = $num1 . " * " . $num2 .  $equalStr;
				  $pincode = (int)$num1*(int)$num2;
                  break;
                case '+' :
				  $num1 = rand(1,20);
				  $num2 = rand(1,20);
				  $str = $num1 . " + " . $num2 .  $equalStr;
                  $pincode = (int)$num1+(int)$num2;
                  break;
                default:
                    break;
            }
        }
        else
        {
            $randKey = array_rand($questionArr[$this->lang]);
            $str = $randKey;
            $pincode = array('zh-cn' => $questionArr['zh-cn'][$randKey],'zh-tw' => $questionArr['zh-tw'][$randKey]);
        }

        if (!$this->generate_encrypt_string( $pincode))
        {
            return false;

        }

        //print http header
        header("Set-Cookie: ULOGIN_IMG=".

            $this->encryptstr."; domain=exp.com; path=/");

        //set-cookie must be printed befor this
        header("Content-type: image/".$this->imgtype."\n\n");
        //omit return value

        //create image
        $img = imagecreate( $this->imgwidth, $this->imgheight );

        //get the index or the closest value of assigned color
        $colors[0] = imagecolorresolve( $img, 255, 255, 255);   //white
        $colors[1] = imagecolorresolve( $img, 0, 0, 0); //black
        $colors[2] = imagecolorresolve( $img, 9, 9, 53);
        $colors[3] = imagecolorresolve( $img, 53, 9, 9);
        $colors[4] = imagecolorresolve( $img, 10, 53, 10);
        $colors[5] = imagecolorresolve( $img, 53, 52, 58);
        $colors[6] = imagecolorresolve( $img, 41, 39, 29);
        $colors[7] = imagecolorresolve( $img, 41, 44, 14);
        $colors[8] = imagecolorresolve( $img, 16, 51, 54);
        $colors[9] = imagecolorresolve( $img, 34, 54, 27);
        $colors[10] = imagecolorresolve( $img, 71, 33, 16);

        $x = 5;
        $y = 20;

        $fakeimg = imagecreate( $this->imgwidth, $this->imgheight );

        //draw some confusion lines
        for ( $i = 0; $i < strlen( $str); $i++)
        {
            imagesetthickness ($img, 3*$i);
            $line_color = imagecolorallocate ($img, rand(150,255), rand(150, 255), rand(150,255));

            //draw a line

            imageline( $img, rand()%$this->imgwidth,
                    rand()%$this->imgheight,
                    rand()%$this->imgwidth,
                    rand()%$this->imgheight,$line_color);
        }

        for ( $i = 0; $i < mb_strlen( $str); $i++)
        {
            $size = $this->getrand(10, 15);
            $angle = $this->getrand( -1500, 1500) * M_PI / 180;
            $nFont = $this->getrand(0, sizeof( $ffiles) - 1);

            //can not user colors[0](white), number won't be shown well in white
            $nColor = $this->getrand(1, sizeof( $colors) - 1);

            $lastpos = array_fill( 0, 7, 0);
            //draw virtually

            $word = mb_substr($str, $i, 1 , 'UTF-8');
            /*
            $lastpos = imagettftext( $fakeimg, $size, $angle, 0, 0, 0, $ffiles[$nFont], $word);
            if ( $lastpos[0] > $lastpos[6])
            {
                $leftlean = TRUE;
            }
            else
            {
                $leftlean = FALSE;
            }
            $drift_x = $leftlean ? $lastpos[0] - $lastpos[6] : 0;
            */
            //draw a real number of pincode

            // write double chars by chinese
            //$x +=  $i + rand(20,35);
            if($i != 0) {
               //$x += $i + rand(15,20);
               $x += $i + 16;
            }
            $y = rand(20,25);

            $size = ($type == 'num')? rand(18,22) : rand(14,18);
            $angle = 0;

            $lastpos = imagettftext( $img, $size, $angle, $x, $y, $colors[$nColor], $ffiles[$nFont], $word);
/*
            $lastpos = imagettftext( $img, $size, $angle,

                    $x+$drift_x, $y, $colors[$nColor],

                    $ffiles[$nFont], $str[$i].$str[$i+1].$str[$i+2]);
*/
                    //$x += $i + rand(20,80);

                    //$x += $leftlean ? $lastpos[2]- $lastpos[6] : $lastpos[4] - $lastpos[0] + 1;


        }
        for ( $i=0; $i < intval($this->imgwidth*$this->imgheight/70); $i++)
        {
            //draw a pixel
            imagesetpixel( $img, rand()%$this->imgwidth,

                rand()%$this->imgheight, $colors[1]);
        }
        $showimgfunc = "image".$this->imgtype;
        $showimgfunc( $img);

        //destroy image
        imagedestroy ($img);
        return true;
    }
}
?>