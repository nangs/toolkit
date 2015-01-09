<?php
/**
 * This class contains useful helper functions.
 * This class will help you to avoid rewriting of most
 * used function and speed up the developing process.
 *
 * curl: Get any web pages content using Curl.
 * checkMail: Validate email address.
 * limitText: Limit the text by the number of characters/words.
 * random: Generate random strings.
 * slug: Generate SEO friendly slugs.
 * compare: Compare two string or passwords.
 * redirectTo: Redirect users to any page you want in a given number of seconds.
 * getYoutubeID: Extract the Youtube ID from an URL or embed code.
 * getYoutubeDetails: Get almost all details of a Youtube Video.
 * embedYoutube: Create embed code of a Youtube Video.
 * getVimeoDetails: Get almost all details of a Vimeo Video.
 * embedVimeo: Create embed code of a Vimeo Video.
 * uploadImage: Upload images to imgur.com using URL and ClientID.
 * gravatar: Get gravatar.com image using email address.
 * fbDetail: Get all details using Graph API v2.2 of a Facebook Page or User.
 * getTitle: Gray any website title and return it.
 * getMeta: Gray any website metas and return it.
 * showPdf: Show documents using Google Docs Viewer.
 * bbCodeToHtml: Convert bbCode strings to html.
 * downloadFile: Download an Image or file from a remote server and store.
 *
 */

namespace yuks\Toolkit;


class Toolkit {

    public $random_characters = 'abcdefghijklmnopqrstuvwxyz1234567890';
    public $random_letters = 'abcdefghijklmnopqrstuvwxyz';

    // The ClientID of the Imgur Application to upload images. You can get one over https://api.imgur.com/oauth2/addclient
    protected $imgurClientID = '';


    /**
     * Get any web pages content using Curl.
     *
     * @param string $url
     * @return mixed
     */
    public function curl($url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    /**
     * Validate email address.
     *
     * @param string $email Email Address
     * @return bool|string True if the email address is valid
     */
    public function checkMail($email){
        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
            return true; //Email address is valid
        }

        return false; //Email address is invalid
    }

    /**
     * Limit the text by the number of characters/words.
     *
     * @param string $text Text.
     * @param int $number Max number of characters/words.
     * @param bool $byCharacter Limit by characters.
     * @return string
     */
    public function limitText($text,$number,$byCharacter=false){
        $text = strip_tags($text);

        if($byCharacter == true)
            return mb_substr($text, 0, $number, "utf-8");

        $words = explode(' ', $text);

        if(count($words)<$number)
            return $text;

        $words = array_slice($words, 0, $number);
        $text = implode(' ', $words);

        return $text;
    }

    /**
     * Generate random strings
     *
     * @param int $characters Amount of characters
     * @param string|int $type Type of characters. 1, numeric or number for numbers, letter of letters, mixed for all.
     * @return null|string
     */
    public function random($characters,$type='mixed'){
        /*
         * If only numeric
         */
        if($type == 'numeric' || $type == 'number' || $type == 1){
            $output = null;
            for($i=0;$i<$characters;$i++){
                $output .= mt_rand(0,9);
            }
            return $output;
        }

        if($type == 'letter'){
            for($output = '', $cl = strlen($this->random_letters)-1, $i = 0; $i < $characters; $output .= $this->random_letters[mt_rand(0, $cl)], ++$i);
            return $output;
        }

        /*
         * If mixed characters are selected
         */
        if($type == 'mixed'){
            for ($output = '', $cl = strlen($this->random_characters)-1, $i = 0; $i < $characters; $output .= $this->random_characters[mt_rand(0, $cl)], ++$i);
            return $output;
        }
    }

    /**
     *  Generate SEO friendly slugs.
     *
     * @param $text
     * @return bool|mixed|string
     */
    public function slug($text){
        $text = preg_replace('|[^\\pL\d]+|u', '-', $text);
        $text = trim($text,'-');
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = strtolower($text);

        if(empty($text)){
            return 'na';
        }

        return $text;
    }

    /**
     * Compare two string or passwords.
     *
     * @param int|string $first_password The first string or password
     * @param int|string $second_password The second string or password
     * @return bool
     */
    public function compare($first_password,$second_password){
        if($first_password == $second_password){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Redirect users to any page you want in a given number of seconds.
     *
     * @param string $url
     * @param int $seconds
     * @return string
     */
    public function redirectTo($url,$seconds=0){
        $output = '<meta http-equiv="refresh" content="'.$seconds.'; url='.$url.'" />';
        return $output;
    }

    /**
     * Extract the Youtube ID from an URL or embed code.
     *
     * @param string $url Youtube url or embed code
     * @return string Youtube ID
     */
    public function getYoutubeID($url){
        preg_match('#(?<=[v|vi]=)[a-zA-Z0-9-]+(?=&)|(?<=vi\/)[a-zA-Z0-9-]+|(?<=v\/)[^&\n]+|(?<=[v|vi]=)[^&\n]+|(?<=embed\/)[^"&\n]+|(?<=youtu.be\/)[^&\n/]+#',$url,$match);
        $youtube_id = trim($match[0]);
        return $youtube_id;
    }

    /**
     * Get title, author, publish date, duration, updated date, view count,
     * number of likes, number of dislikes and thumbnails of a Youtube Video.
     *
     * @param string $youtubeID ID of the Youtube Video
     * @param bool $outputArray True to return array, false to return object
     * @return object|array
     */
    public function getYoutubeDetails($youtubeID, $outputArray=false){
        $output = json_decode($this->curl('http://gdata.youtube.com/feeds/api/videos/'.$youtubeID.'?v=2&alt=json'), true);

        $youtube['title']       = $output['entry']['title']['$t'];
        $youtube['author']      = $output['entry']['author'][0]['name']['$t'];
        $youtube['published']   = $output['entry']['published']['$t'];
        $youtube['duration']    = $output['entry']['media$group']['yt$duration']['seconds'];
        $youtube['updated']     = $output['entry']['updated']['$t'];
        $youtube['viewCount']   = $output['entry']['yt$statistics']['viewCount'];
        $youtube['numLikes']    = $output['entry']['yt$rating']['numLikes'];
        $youtube['numDislikes'] = $output['entry']['yt$rating']['numDislikes'];
        $youtube['thumbnails']  = $output['entry']['media$group']['media$thumbnail'];
        if($outputArray == false)
            $youtube = json_decode(json_encode($youtube), FALSE);

        return $youtube;
    }

    /**
     * Create embed code of a Youtube Video.
     *
     * @param $youtubeID ID of the Youtube Video
     * @param string $width Default 640
     * @param string $height Default 360
     * @param string $theme Dark or light theme can be used. Default 'dark'
     * @param bool $autoPlay True to auto play the video. Default false
     * @param bool $playerControls True to show player controls. Default true
     * @param bool $showDetails True to show title and other details of the video. Default true
     * @param bool $showSuggested True to show suggested videos at the end of the video. Default false
     * @return string Iframe embed code
     */
    public function embedYoutube($youtubeID, $width='640', $height='360', $theme='dark', $autoPlay=false, $playerControls=true, $showDetails=true, $showSuggested=false){
        $url  = '//www.youtube-nocookie.com/embed/'.trim($youtubeID).'?';
        $url .= 'theme='.$theme.'&';
        if($autoPlay == true)
            $url .= 'autoplay=1&';
        if($playerControls == false)
            $url .= 'controls=0&';
        if($showDetails == false)
            $url .= 'showinfo=0&';
        if($showSuggested == false)
            $url .= 'rel=0&';

        $embedCode = '<iframe width="'.$width.'" height="'.$height.'" src="'.$url.'" frameborder="0" allowfullscreen></iframe>';

        return $embedCode;
    }

    /**
     * Get title, username, upload date, duration, view count,
     * thumbnails etc. of a Vimeo Video.
     *
     * @param string $vimeoID ID of the Vimeo Video
     * @param bool $outputArray True to return array, false to return object
     * @return object|array
     */
    public function getVimeoDetails($vimeoID, $outputArray=false){
        $output = json_decode($this->curl('http://vimeo.com/api/v2/video/'.$vimeoID.'.json'), true);
        if($outputArray == false)
            $vimeo = json_decode(json_encode($output), FALSE);

        return $vimeo;
    }

    /**
     * Create embed code of a Vimeo Video.
     *
     * @param string $vimeoID ID of the Vimeo Video
     * @param string $width Default 500
     * @param string $height Default 281
     * @param bool $autoPlay True to auto play the video. Default false
     * @param null $color Specify the color of the video controls. Defaults to 00adef. Make sure that you don’t include the #. Default null
     * @param bool $byline Show the user’s byline on the video. Default false
     * @param bool $portrait Show the user’s portrait on the video. Default false
     * @param bool $title Show the title on the video. Default false
     * @return string
     */
    public function embedVimeo($vimeoID, $width='500', $height='281', $autoPlay=false, $color=null, $byline=false, $portrait=false, $title=false){
        $url = '//player.vimeo.com/video/'.$vimeoID.'/?';
        if($autoPlay == true)
            $url .= 'autoplay=1&';
        if(!empty($color))
            $url .= 'color='.$color.'&';
        if($byline == false)
            $url .= 'byline=0&';
        if($portrait == false)
            $url .= 'portrait=0&';
        if($title == false)
            $url .= 'title=0&';

        $embedCode = '<iframe src="'.$url.'" width="'.$width.'" height="'.$height.'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';

        return $embedCode;
    }

    /**
     * Upload images to imgur.com using URL and ClientID.
     *
     * @param string $imageUrl The image URL.
     * @param null $clientID The ClientID on imgur.com. When null $imgurClientID will be used. Default null
     * @return bool|string False when something went wrong else returns URL of the uploaded image.
     * @throws \Exception
     */
    public function uploadImage($imageUrl, $clientID=null){
        if(empty($clientID) && empty($this->imgurClientID))
            throw new \Exception("clientID needed.");

        if(empty($clientID))
            $clientID = $this->imgurClientID;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image.json');
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Authorization: Client-ID ' . $clientID));
        curl_setopt($ch, CURLOPT_POSTFIELDS, array( 'image' => $imageUrl));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $response = json_decode($response);
        curl_close ($ch);

        if($response->success == false)
            return false;

        return $response->data->link;
    }

    /**
     * Get gravatar.com image using email address.
     *
     * @param string $email Email address of the gravatar profile.
     * @param int $size Image size in pixels. Default 80
     * @return string Gravatar image URL.
     * @throws \Exception
     */
    public function gravatar($email, $size=80){
        if(!$this->checkMail(trim($email)))
            throw new \Exception("Invalied email address");

        $hash = md5(strtolower(trim($email)));

        return 'http://www.gravatar.com/avatar/'.$hash.'?size='.$size;
    }

    /**
     * Get all details using Graph API v2.2 of a Facebook Page or User.
     *
     * @param string $pageID ID of the Facebook Page or User.
     * @param bool $outputArray True to return array, false to return object
     * @return array|object
     */
    public function fbDetail($pageID, $outputArray=false){
        $output = $this->curl('http://graph.facebook.com/'.$pageID);
        $output = json_decode($output);

        if($outputArray == false)
            $output = json_decode(json_encode($output), FALSE);

        return $output;
    }

    /**
     * Gray any website title and return it.
     *
     * @param string $url
     * @return mixed
     */
    public function getTitle($url){
        $content = $this->curl($url);
        preg_match('#<title>(.*)</title>#',$content,$match);
        return $match[1];
    }

    /**
     * Gray any website metas and return it.
     *
     * @param string $name
     * @param string $url
     * @return string|null
     */
    public function getMeta($name, $url){
        $metas = get_meta_tags($url);

        if(isset($metas[$name]))
            return $metas[$name];

        return NULL;
    }

    /**
     * Show documents using Google Docs Viewer.
     * It can be used with local or external links.
     *
     * @param string $source
     * @param int $width
     * @param int $height
     * @return string string
     */
    public function showPdf($source, $width=640, $height=480){
        $content = '<iframe src="http://docs.google.com/gview?url='.$source.'&embedded=true" style="width:'.$width.'px; height:'.$height.'px;" frameborder="0"></iframe>';

        return $content;
    }

    /**
     * Convert bbCode strings to html.
     *
     * @param string $content
     * @return string
     */
    public function bbCodeToHtml($content){
        $bbcode = [
            '#\[img\](https?://.*?\.(?:jpg|jpeg|gif|png|bmp))\[/img\]#s',
            '#\[quote\](.*?)\[/quote\]#s',
            '#\[b\](.*?)\[/b\]#s',
            '#\[size=(.*?)\](.*?)\[/size\]#s',
            '#\[i\](.*?)\[/i\]#s',
            '#\[url\]((?:ftp|https?)://.*?)\[/url\]#s',
            '#\[u\](.*?)\[/u\]#s',
            '#\[color=(.*?)\](.*?)\[/color\]#s'
        ];

        // Replace bbcodes with this HTML tags
        $replace = [
            '<img src="$1" alt="" />',
            '<pre>$1</'.'pre>',
            '<b>$1</b>',
            '<span style="font-size:$1px;">$2</span>',
            '<i>$1</i>',
            '<a href="$1">$1</a>',
            '<span style="text-decoration:underline;">$1</span>',
            '<span style="color:$1;">$2</span>'
        ];

        $content = preg_replace($bbcode,$replace,$content);

        return $content;
    }


    /**
     * Download an Image or file from a remote server and store.
     *
     * @param string $url Image URL
     * @param string $directory Save path default images/deals
     * @param string $name Rename image to this name. Default NULL
     * @return bool|string
     */
    function downloadFile($url, $directory='upload', $name=NULL)
    {
        $pathParts = pathinfo($url);
        $fileExtension = $pathParts['extension'];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, ""); //To get Gzipped Images
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_exec($ch);
        $rawdata=curl_exec($ch);
        curl_close ($ch);

        if(isset($name)){
            $fileName = $name;
        }else{
            $fileName = rand(0,999).rand(0,999).rand(0,999).rand(0,999).rand(0,999).rand(0,999).rand(0,999);
        }

        if(file_exists($directory.'/'.$fileName.'.'.$fileExtension)){
            $fileName .= $this->slug(microtime());
        }

        $fp = fopen($directory.'/'.$fileName.'.'.$fileExtension,'w');
        fwrite($fp, $rawdata);
        fclose($fp);

        if(file_exists($directory.'/'.$fileName.'.'.$fileExtension))
            return true;

        return false;
    }
}