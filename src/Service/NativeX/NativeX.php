<?php

/** FOR YOUR EYES ONLY **/
/** NativeX Encryption Tools **/
/** By jim williams **/
/** 11/16/2013 **/

namespace App\Service\NativeX;

class NativeX
{


    public $config = array();
    public $x;
    public $keysarray = array();
    public $stack = array();
    public $teaser = "Impressive, but your message is in another castle! --->";
    public $output = "text";
    public $key = "defaultkey";

    private string $pi10000;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->pi10000 = $config['pi'];
        $this->stack = array_map('trim', explode(",", $config['stack']));
        if (isset($config['key']) && !empty($config['key'])) {
            $this->key = $config['key'];
        }
        
    }


    // these are legacy stacks, used in the old code extensively.
    public function classic_encode($text, $base64 = true, $teaser = false, $lockdown = false)
    {
        $text = base64_encode($text);
        $text = $this->bandit($text, 1);
        $text = $this->shell_shock($text, 1);
        $text = $this->straight_shuffle($text, 1);
        $text = base64_encode($text);
        $text = $this->shell_shock($text, 1);
        $text = $this->straight_shuffle($text, 1);

        return $text;
    }

    public function classic_decode($text, $base64 = true, $teaser = false, $lockdown = false)
    {
        $text = $this->straight_shuffle($text, -1);
        $text = $this->shell_shock($text, -1);
        $text = base64_decode($text);
        $text = $this->straight_shuffle($text, -1);
        $text = $this->shell_shock($text, -1);
        $text = $this->bandit($text, -1);
        $text = base64_decode($text);

        $text = $this->ensureUTF8($text);

        switch ($this->output) {
            case "image":

            case "text":
            default:
                $return = $text;
                break;
        }
        return $return;
    }


    public function password($count)
    {
        for ($i = 1; $i <= 3; $i++) {
            $x = $modulo % $i;
            $text = $this->encode($this->config['password']);
        }
        return substr(sha1($text), 0, $this->config["passwordlength"]);
    }


    public function generateX($str, $x)
    {
        //x should be a power of 2
        //how to enforce it?
        // like this :  $x is the exponent
        // power of 2 wasn't even working - this is XOR...and it does.  Happy Accidents!

        $x = 2 ^ $x;
        $strray = str_split($str);
        $total = 0;
        foreach ($strray as $chr) {
            $total += ord($chr);
        }
        return $total % $x + 1;
    }

    public function reverse_string($text)
    {
        return strrev($text);
    }

    public function swap_tip_x($text)
    {
        $arr = str_split(base64_encode($text));
        $nugget = array_shift($arr);
        $arr[] = $nugget;
        return implode("", $arr);
    }

    public function swap_tip_y($text)
    {
        $arr = str_split($text);
        $nugget = array_pop($arr);
        $arr = array_merge(array($nugget), $arr);
        return base64_decode(implode("", $arr));
    }

    public function split_deck_x($text)
    {
        $top = substr($text, 0, floor(strlen($text) / 2));
        $bottom = substr($text, (floor(strlen($text) / 2)));
        return $bottom . $top;
    }

    public function split_deck_y($text)
    {
        $top = substr($text, 0, ceil(strlen($text) / 2));
        $bottom = substr($text, (ceil(strlen($text) / 2)));
        return $bottom . $top;
    }

    public function swap_tip($text, $encode)
    {
        if ($encode == 1) {
            return $this->swap_tip_x($text);
        } else {
            return $this->swap_tip_y($text);
        }
    }

    public function split_deck($text, $encode)
    {
        if ($encode == 1) {
            return $this->split_deck_x($text);
        } else {
            return $this->split_deck_y($text);
        }
    }

    public function straight_shuffle($text, $encode = 1)
    {

        $strray = str_split($text);

        $even = array();
        $odd = array();

        if ($encode == 1) {
            for ($i = 0; $i < count($strray); $i++) {
                if ($i % 2 == 0) {
                    $even[] = $strray[$i];
                } else {
                    $odd[] = $strray[$i];
                }
            }
            $shuffled = array_merge($even, $odd);
        } else {
            $split = round(count($strray) / 2);

            $even =  array_slice($strray, 0, $split);

            $odd = array_slice($strray, $split);
            $shuffled = array();
            for ($i = 0; $i < count($even); $i++) {
                $shuffled[] = $even[$i];
                $shuffled[] = $odd[$i];
            }
        }

        return implode("", $shuffled);
    }


    function shell_shock(string $text): string
    {
        $chars = str_split($text);
        $n     = count($chars);
        $half  = intdiv($n, 2);

        // Swap mirrored characters when index is odd
        for ($i = 1; $i < $half; $i += 2) {
            $j = $n - $i - 1;

            // swap chars[$i] and chars[$j]
            [$chars[$i], $chars[$j]] = [$chars[$j], $chars[$i]];
        }

        return implode('', $chars);
    }


    function r($str, $encode = 1)
    {

        if (strlen($str) == 1) {
            return $str;
        }
        // split the deck in two.			
        $top = substr($str, 0, floor(strlen($str) / 2));
        $bottom = substr($str, (floor(strlen($str) / 2)));

        if ($encode == 1) {
            $top = $this->x_shuffle($top, $bottom, $encode);
            $top = $this->r($top, $encode);
            $bottom = $this->r($bottom, $encode);
            $str = $top . $bottom;
        } else {
            $top = $this->r($top, $encode);
            $bottom = $this->r($bottom, $encode);
            $top = $this->x_shuffle($top, $bottom, $encode);
            $str = $top . $bottom;
        }

        return $str;
    }

    function banditX($text, $encode = 1, $passes = 1)
    {

        for ($i = 0; $i < $passes; $i++) {
            if (1 == $encode) {
                for ($i = 0; $i < $passes; $i++) {
                    //$text = $this->bandit($text,$encode);
                    $text = $this->r($text, $encode);
                    //$text = $this->bandit($text,$encode);
                }
            } else {
                //$text = $this->bandit($text,$encode);
                $text = $this->r($text, $encode);
                //$text = $this->bandit($text,$encode);
            }
        }
        return $text;
    }

    function base64($text, $encode = 1)
    {
        if ($encode == 1) {
            return base64_encode($text);
        } else {
            return base64_decode($text);
        }
    }

    function bandit($text, $encode = 1)
    {

        $offset = $this->generateX($this->key, (94));
        $subj = str_split($text);

        $crypt = array();

        foreach ($subj as $idx => $char) {

            $new_code = ord($char) + ((($idx ^ (strlen($text) % 23)) % (17)) + $offset) * $encode;
            if ($new_code > 126) {
                $new_code = $new_code - 95;
            }
            if ($new_code < 32) {
                $new_code = 95 + $new_code;
            }

            $crypt[] = chr($new_code);
        }
        $crypt_str = implode("", $crypt);
        return $crypt_str;
    }

    function john11($text, $encode = 1)
    {

        $corpus = "In the beginning was the Word, and the word was with god, and the word was God";
        return  $this->x_shuffle($text, $corpus, $encode);
    }

    function x_shuffle($text, $corpus, $encode = 1)
    {

        if (strlen($corpus) < strlen($text)) {
            die("corpus insufficient");
        }
        $subj     = str_split($text);
        $map     = str_split($corpus);
        $crypt     = array();
        $base    = $this->generateX($this->key, strlen($corpus) - strlen($text));

        foreach ($subj as $ix => $char) {
            $flip = ($ix % 2 == 0) ? 1 : -1;
            $spin = (ord($map[$base + $ix]) - 32) * $encode * $flip;
            $new_code = ord($char) + $spin;;
            if ($new_code > 126) {
                $new_code = $new_code - 95;
            }
            if ($new_code < 32) {
                $new_code = $new_code + 95;
            }
            $crypt[] = chr($new_code);
        }
        $crypt_str = implode("", $crypt);
        return $crypt_str;
    }

    public function pi_shuffle($text, $encode = 1)
    {

        $crypt = array();
        $subj = str_split($text);
        $pi = str_split($this->pi10000);

        $base = $this->generateX($this->key, (1024));
        foreach ($subj as $idx => $char) {
            if ($idx == 6) {
            }
            $flip = ($idx % 2 == 0) ? 1 : -1;

            $new_code = ord($char) + $pi[$base + $idx] * $encode * $flip;

            if ($new_code > 126) {
                $new_code = $new_code - 95;
            }
            if ($new_code < 32) {

                $new_code = 95 + $new_code;
            }

            $crypt[] = chr($new_code);
        }

        $crypt_str = implode("", $crypt);

        return $crypt_str;
    }


    public function rot13($text, $encode = 1)
    {
        $subj = str_split($text);
        $crypt = array();

        foreach ($subj as $char) {
            $code = ord($char);
            if ($code >= 65 && $code <= 90) {
                // Uppercase A-Z
                $new_code = (($code - 65 + 13 * $encode) % 26) + 65;
            } elseif ($code >= 97 && $code <= 122) {
                // Lowercase a-z
                $new_code = (($code - 97 + 13 * $encode) % 26) + 97;
            } else {
                // Non-alphabetic characters remain unchanged
                $new_code = $code;
            }
            $crypt[] = chr($new_code);
        }

        return implode("", $crypt);
    }

    public function rotPosition($text, $encode = 1)
    {
        $subj = str_split($text);
        $crypt = array();

        foreach ($subj as $idx => $char) {
            $code = ord($char);
            if ($code >= 32 && $code <= 126) {
                $new_code = (($code - 32 + ($idx + 1) * $encode) % 95) + 32;
            } else {
                $new_code = $code;
            }
            $crypt[] = chr($new_code);
        }

        return implode("", $crypt);
    }

    public function stack($text, $encode = 1)
    {
        $stack = $this->stack;

        if ($encode == 1) {
            foreach ($stack as $method) {
                $text = $this->$method($text, 1);
            }
        } else {
            $stack = array_reverse($stack);
            foreach ($stack as $method) {
                $text = $this->$method($text, -1);
            }
        }
        return $text;
    }

    function setKey($key)
    {
        $this->key = $key;
    }

    public function set_output($output)
    {
        $this->output = $output;
    }
    public function get_output()
    {
        return $this->output;
    }

    /**
     * @author   "Sebastián Grignoli" <grignoli@framework2.com.ar>
     * @package  Encoding
     * @version  1.1
     * @link     http://www.framework2.com.ar/dzone/forceUTF8-es/
     * @example  http://www.framework2.com.ar/dzone/forceUTF8-es/
     */


    function make_wrapped_txt($txt, $color = 000000, $space = 4, $font = 4, $w = 300)
    {
        if (strlen($color) != 6) $color = 000000;
        $int = hexdec($color);
        $h = imagefontheight($font);
        $fw = imagefontwidth($font);
        $txt = explode("\n", wordwrap($txt, ($w / $fw), "\n"));
        $lines = count($txt);
        $im = imagecreate($w, (($h * $lines) + ($lines * $space)));
        $bg = imagecolorallocate($im, 255, 255, 255);
        $color = imagecolorallocate($im, 0xFF & ($int >> 0x10), 0xFF & ($int >> 0x8), 0xFF & $int);
        $y = 0;
        foreach ($txt as $text) {
            $x = (($w - ($fw * strlen($text))) / 2);
            imagestring($im, $font, $x, $y, $text, $color);
            $y += ($h + $space);
        }

        return $im;
    }


    function ensureUTF8($string)
    {
        // Check if the string is already in valid UTF-8 encoding
        if (mb_check_encoding($string, 'UTF-8')) {
            return $string;
        }

        // Convert to UTF-8 if not already valid
        return mb_convert_encoding($string, 'UTF-8', 'auto');
    }


    public static function build(ConfigLoader $loader): self
    {
        $config = $loader->load();
        return new self($config);
    }
}
