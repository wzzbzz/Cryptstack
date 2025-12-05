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



    // a simple pseudorandom number generator based on an input string.
    public function generateX($str, $x)
    {
        // this junks things up a bit more, for no reason
        $x = $this->sum_ord($str) ^ $x;


        $strray = str_split($str);
        $total = 0;
        foreach ($strray as $chr) {
            $total += ord($chr);
        }
        return $total % $x + 1;
    }

    public function sum_ord($text)
    {
        $total = 0;
        $strray = str_split($text);
        foreach ($strray as $chr) {
            $total += ord($chr);
        }
        return $total;
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

    public function SwappyBird($text, $encode)
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

    public function Splitter(string $text, int $encode = 1): string
    {
        return $this->split_deck($text, $encode);
    }

    public function straight_shuffle($text, $encode = 1)
    {
        $strray = str_split($text);

        $even = [];
        $odd = [];

        if ($encode == 1) {

            // --- ENCODE ---
            for ($i = 0; $i < count($strray); $i++) {
                if ($i % 2 == 0) {
                    $even[] = $strray[$i];
                } else {
                    $odd[] = $strray[$i];
                }
            }

            $shuffled = array_merge($even, $odd);
        } else {

            // --- DECODE (inverse) ---

            $n = count($strray);
            $even_count = intdiv($n + 1, 2);  // ceil(n/2)
            $odd_count  = intdiv($n, 2);      // floor(n/2)

            $evens = array_slice($strray, 0, $even_count);
            $odds  = array_slice($strray, $even_count);

            $shuffled = [];
            $ei = $oi = 0;

            for ($i = 0; $i < $n; $i++) {
                if ($i % 2 == 0) {
                    $shuffled[] = $evens[$ei++];
                } else {
                    $shuffled[] = $odds[$oi++];
                }
            }
        }

        return implode("", $shuffled);
    }

    public function GiveItToMeStraight(string $text, int $encode = 1): string
    {
        return $this->straight_shuffle($text, $encode);
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

    public function Chakra(string $text, int $encode = 1): string
    {
        return $this->shell_shock($text);
    }

    /**
     * Implements the Caesar Cipher for a given text and shift amount.
     *
     * @param string $text The plaintext or ciphertext to process.
     * @param int $shift The number of positions to shift (the key).
     * @param bool $encode If true (default), the text is encoded (shifted forward).
     * If false, the text is decoded (shifted backward).
     * @return string The resulting ciphertext or plaintext.
     */
    public function caesar(string $text,  bool $encode = true): string
    {
        // Compute shift from key
        $shift = 0;
        foreach (str_split($this->key) as $char) {
            $shift += ord($char);
        }


        // Normalize shift safely
        $shift = ($shift % 26 + 26) % 26;


        // Reverse shift for decoding
        if (!$encode) {
            $shift = (26 - $shift) % 26;
        }

        // Cache repeated values
        $A = ord('A');
        $a = ord('a');
        $len = strlen($text);

        for ($i = 0; $i < $len; $i++) {
            $char = $text[$i];

            if (ctype_upper($char)) {
                $new = ((ord($char) - $A + $shift) % 26) + $A;
                $result .= chr($new);
            } elseif (ctype_lower($char)) {
                $new = ((ord($char) - $a + $shift) % 26) + $a;
                $result .= chr($new);
            } else {
                $result .= $char;
            }
        }

        return $result;
    }

    public function hailCaesar(string $text, int $shift, int $encode = 1): string
    {
        if ($encode == 1) {
            return $this->caesar($text, $shift, true);
        } else {
            return $this->caesar($text, $shift, false);
        }
    }

    // no clue what you do.  recursive shuffle? 
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

    function bandit_og($text, $encode = 1)
    {

        $offset = $this->generateX($this->key, (94));
        $subj = str_split($text);

        $crypt = array();

        foreach ($subj as $idx => $char) {


            // were these #s doing anything. I think they were....but I can't explain it. I'll have to see how it goes with elBandito Nueveo
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

    public function elBandito($text, $encode = 1)
    {
        return $this->bandit_og($text, $encode);
    }


    function bandit_2025($text, $encode = 1)
    {

        $offset = $this->generateX($this->key, (94));
        $subj = str_split($text);

        $crypt = array();

        foreach ($subj as $idx => $char) {

            //removed the weird constants that shrank the keyspace.
            $new_code = ord($char) + ($idx  + $offset) * $encode;
            // Force $new_code to printable ASCII (32..126)
            while ($new_code > 126) {
                $new_code -= 95;
            }
            while ($new_code < 32) {
                $new_code += 95;
            }

            $crypt[] = chr($new_code);
        }
        $crypt_str = implode("", $crypt);
        return $crypt_str;
    }

    public function elBanditoNuevo($text, $encode = 1)
    {
        return $this->bandit_2025($text, $encode);
    }

    // I thought this would be more secure than it is;  it's a mild improvement on bandit alone.
    public function BillyTheKid($text, $encode = 1)
    {
        $text = $this->bandit_og($text, $encode);
        $this->key = strrev($this->key);
        $text = strrev($text);
        $text = $this->bandit_og($text, $encode);
        return $text;
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

    public function CutiePie($text, $encode = 1)
    {
        return $this->pi_shuffle($text, $encode);
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

    public function RotoRooter($text, $encode = 1)
    {
        return $this->rot13($text, $encode);
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



    /******************* GEMINI CREATED CIPHERS *******************/

    public function GeminiHypothesis($text, $encode = 1)
    {
        if ($encode == 1) {
            return self::expand($text);
        } else {
            return self::collapse($text);
        }
    }

    public static function expand($text)
    {
        $result = '';
        $prefixes = ['5', 'E', '[', 'k'];

        $length = strlen($text);
        for ($i = 0; $i < $length; $i++) {
            $ord = ord($text[$i]);

            // The "Gemini Hypothesis" logic
            $prefix = $prefixes[$ord % 4];
            $middle = "23";

            // Map ASCII range to Suffix
            // We start mapping at Space(32) -> '@'(64)
            if ($ord >= 32) {
                $suffixVal = 64 + floor(($ord - 32) / 4);
                $suffix = chr($suffixVal);
            } else {
                $suffix = '?';
            }

            $result .= $prefix . $middle . $suffix;
        }
        return $result;
    }

    /**
     * COLLAPSES the text (4 chars -> 1 char).
     */
    public static function collapse($text)
    {
        $result = '';
        $length = strlen($text);

        // Step through 4 chars at a time
        for ($i = 0; $i < $length; $i += 4) {
            $block = substr($text, $i, 4);
            if (strlen($block) < 4) break;

            $prefix = $block[0];
            // We ignore the middle "23" (indices 1 and 2)
            $suffix = $block[3];

            // Reverse Suffix
            $groupIndex = ord($suffix) - 64;
            $baseOrd = ($groupIndex * 4) + 32;

            // Reverse Prefix to get the remainder
            $offset = 0;
            switch ($prefix) {
                case '5':
                    $offset = 0;
                    break;
                case 'E':
                    $offset = 1;
                    break;
                case '[':
                    $offset = 2;
                    break;
                case 'k':
                    $offset = 3;
                    break;
            }

            $finalOrd = $baseOrd + $offset;
            $result .= chr($finalOrd);
        }
        return $result;
    }

    /**
     * Encrypts a message using the Case-Preserving CPSV Cipher.
     * Preserves the casing of the original letters.
     */
    function cpsv_encrypt_case_preserving(string $plaintext, string $baseKey): string
    {
        $N = 26;
        $baseKeyLen = strlen($baseKey);
        $ciphertext = '';
        $cumulativeShift = 0;
        $keyIndex = 0;

        for ($i = 0; $i < strlen($plaintext); $i++) {
            $P_char = $plaintext[$i];

            if (ctype_alpha($P_char)) {
                // Check if original was lowercase
                $isLower = ctype_lower($P_char);

                // Normalize to 0-25 for math
                $P_val = ord(strtoupper($P_char)) - ord('A');
                $K_base_val = ord(strtoupper($baseKey[$keyIndex % $baseKeyLen])) - ord('A');

                // Calculate Effective Key
                $K_i_val = ($K_base_val + $cumulativeShift) % $N;

                // Calculate Cipher Value
                $C_val = ($P_val + $K_i_val) % $N;

                // Convert back to char
                $C_char = chr($C_val + ord('A'));

                // Restore case
                if ($isLower) {
                    $C_char = strtolower($C_char);
                }

                $ciphertext .= $C_char;

                // Sync Update (using 0-25 values)
                $cumulativeShift = ($P_val + $K_i_val) % $N;
                $keyIndex++;
            } else {
                $ciphertext .= $P_char;
            }
        }

        return $ciphertext;
    }

    /**
     * Decrypts a message using the Case-Preserving CPSV Cipher.
     * Restores the original casing based on the ciphertext casing.
     */
    function cpsv_decrypt_case_preserving(string $ciphertext, string $baseKey): string
    {
        $N = 26;
        $baseKeyLen = strlen($baseKey);
        $plaintext = '';
        $cumulativeShift = 0;
        $keyIndex = 0;

        for ($i = 0; $i < strlen($ciphertext); $i++) {
            $C_char = $ciphertext[$i];

            if (ctype_alpha($C_char)) {
                // Check if cipher char is lowercase (preserves case info)
                $isLower = ctype_lower($C_char);

                // Normalize to 0-25 for math
                $C_val = ord(strtoupper($C_char)) - ord('A');
                $K_base_val = ord(strtoupper($baseKey[$keyIndex % $baseKeyLen])) - ord('A');

                // Calculate Effective Key
                $K_i_val = ($K_base_val + $cumulativeShift) % $N;

                // Calculate Plain Value
                $P_val = (($C_val - $K_i_val) % $N + $N) % $N;

                // Convert back to char
                $P_char = chr($P_val + ord('A'));

                // Restore case
                if ($isLower) {
                    $P_char = strtolower($P_char);
                }

                $plaintext .= $P_char;

                // Sync Update (must match encryption math exactly)
                $cumulativeShift = ($P_val + $K_i_val) % $N;
                $keyIndex++;
            } else {
                $plaintext .= $C_char;
            }
        }

        return $plaintext;
    }


    function GeminiCPSV($text, $encode = 1)
    {
        $baseKey = "GEMINI"; // Example base key, can be parameterized as needed

        if ($encode == 1) {
            return $this->cpsv_encrypt_case_preserving($text, $baseKey);
        } else {
            return $this->cpsv_decrypt_case_preserving($text, $baseKey);
        }
    }



    /**
     * CPSV-2: Case-Preserving Stream Cipher with Nonlinear Feedback
     *
     * - Only A–Z / a–z are encrypted.
     * - Non-letters are passed through unchanged.
     * - Casing is preserved.
     * - Uses a nonlinear S-box + positional salt + feedback.
     */

    /**
     * Fixed S-box: a permutation of 0..25
     * (You can change this, but keep it a true permutation to maintain reversibility.)
     */
    function cpsv2_get_sbox(): array
    {
        return [
            19,
            0,
            7,
            4,
            2,
            21,
            1,
            9,
            14,
            5,
            3,
            23,
            18,
            8,
            24,
            12,
            20,
            6,
            22,
            15,
            25,
            16,
            10,
            11,
            17,
            13
        ];
    }

    /**
     * Inverse S-box for potential future use (not strictly needed here),
     * but handy if you want to reason about internal structure.
     */
    function cpsv2_get_inv_sbox(): array
    {
        $sbox = $this->cpsv2_get_sbox();
        $inv  = array_fill(0, 26, 0);
        foreach ($sbox as $i => $v) {
            $inv[$v] = $i;
        }
        return $inv;
    }

    /**
     * Normalize positive modulo 26.
     */
    function cpsv2_mod26(int $x): int
    {
        $r = $x % 26;
        if ($r < 0) {
            $r += 26;
        }
        return $r;
    }

    /**
     * Encrypts a message using CPSV-2.
     * Preserves the casing of the original letters.
     */
    function cpsv2_encrypt_case_preserving(string $plaintext, string $baseKey): string
    {
        $N          = 26;
        $sbox       = $this->cpsv2_get_sbox();
        $ciphertext = '';

        // Prepare key as 0-25 values (uppercase)
        $baseKey = strtoupper($baseKey);
        $baseKeyLen = strlen($baseKey);
        if ($baseKeyLen === 0) {
            throw new \InvalidArgumentException("Base key must not be empty.");
        }

        $keyValues = [];
        for ($i = 0; $i < $baseKeyLen; $i++) {
            $ch = $baseKey[$i];
            if (!ctype_alpha($ch)) {
                throw new \InvalidArgumentException("Base key must contain only A-Z letters.");
            }
            $keyValues[$i] = ord($ch) - ord('A');
        }

        $cum = 0; // cumulative state in 0..25

        $len = strlen($plaintext);
        for ($pos = 0; $pos < $len; $pos++) {
            $P_char = $plaintext[$pos];

            if (ctype_alpha($P_char)) {
                $isLower = ctype_lower($P_char);
                $P_val   = ord(strtoupper($P_char)) - ord('A');

                // Base key value for this position (positional salt uses pos, not letter index)
                $baseVal = $keyValues[$pos % $baseKeyLen];

                // Nonlinear effective key with positional salt
                $tmp   = $this->cpsv2_mod26($baseVal + $cum + 7 * $pos);
                $K_eff = $sbox[$tmp];

                // Cipher value
                $C_val = $this->cpsv2_mod26($P_val + $K_eff);

                // Update state
                $cum = $this->cpsv2_mod26($cum + $C_val + $K_eff);

                // Convert to char
                $C_char = chr($C_val + ord('A'));
                if ($isLower) {
                    $C_char = strtolower($C_char);
                }
                $ciphertext .= $C_char;
            } else {
                // Non-alpha: pass through, no state change
                $ciphertext .= $P_char;
            }
        }

        return $ciphertext;
    }

    /**
     * Decrypts a message using CPSV-2.
     * Restores the original casing based on the ciphertext casing.
     */
    function cpsv2_decrypt_case_preserving(string $ciphertext, string $baseKey): string
    {
        $N         = 26;
        $sbox      = $this->cpsv2_get_sbox();
        $plaintext = '';

        // Prepare key as 0-25 values (uppercase)
        $baseKey = strtoupper($baseKey);
        $baseKeyLen = strlen($baseKey);
        if ($baseKeyLen === 0) {
            throw new \InvalidArgumentException("Base key must not be empty.");
        }

        $keyValues = [];
        for ($i = 0; $i < $baseKeyLen; $i++) {
            $ch = $baseKey[$i];
            if (!ctype_alpha($ch)) {
                throw new \InvalidArgumentException("Base key must contain only A-Z letters.");
            }
            $keyValues[$i] = ord($ch) - ord('A');
        }

        $cum = 0; // cumulative state

        $len = strlen($ciphertext);
        for ($pos = 0; $pos < $len; $pos++) {
            $C_char = $ciphertext[$pos];

            if (ctype_alpha($C_char)) {
                $isLower = ctype_lower($C_char);
                $C_val   = ord(strtoupper($C_char)) - ord('A');

                $baseVal = $keyValues[$pos % $baseKeyLen];

                // Recompute effective key (same as in encryption)
                $tmp   = $this->cpsv2_mod26($baseVal + $cum + 7 * $pos);
                $K_eff = $sbox[$tmp];

                // Recover plaintext value
                $P_val = $this->cpsv2_mod26($C_val - $K_eff);

                // Update state (must match encryption)
                $cum = $this->cpsv2_mod26($cum + $C_val + $K_eff);

                $P_char = chr($P_val + ord('A'));
                if ($isLower) {
                    $P_char = strtolower($P_char);
                }
                $plaintext .= $P_char;
            } else {
                $plaintext .= $C_char;
            }
        }

        return $plaintext;
    }

    function chatGPT_CPSV2($text, $encode = 1)
    {
        $baseKey = $this->key; // Example base key, can be parameterized as needed

        if ($encode == 1) {
            return $this->cpsv2_encrypt_case_preserving($text, $baseKey);
        } else {
            return $this->cpsv2_decrypt_case_preserving($text, $baseKey);
        }
    }

    /**
     * Modulo 26 that handles negative numbers correctly
     */
    private static function mod26(int $x): int
    {
        $r = $x % 26;
        return $r < 0 ? $r + 26 : $r;
    }

    /**
     * The Key Scheduling Algorithm (KSA)
     * Creates a custom permutation of 0-25 based on the password.
     */
    private static function initState(string $key): array
    {
        // 1. Initialize state identity (0..25)
        $S = range(0, 25);

        // 2. Convert key to 0-25 values
        $key = strtoupper($key);
        $keyLen = strlen($key);
        $K = [];
        for ($i = 0; $i < $keyLen; $i++) {
            if (ctype_alpha($key[$i])) {
                $K[] = ord($key[$i]) - ord('A');
            }
        }

        if (empty($K)) {
            throw new \Exception("Key must contain at least one letter.");
        }

        // 3. Scramble the state using the Key
        $j = 0;
        $cleanKeyLen = count($K);
        for ($i = 0; $i < 26; $i++) {
            // Include Key and S[i] in the mix
            $j = self::mod26($j + $S[$i] + $K[$i % $cleanKeyLen]);

            // Swap S[i] and S[j]
            $temp = $S[$i];
            $S[$i] = $S[$j];
            $S[$j] = $temp;
        }

        return $S;
    }

    /**
     * Pseudo-Random Generation Algorithm (PRGA)
     * adapted for Mod 26 alphabets.
     */
    public static function crypt(string $input, string $key, bool $decrypt = false): string
    {
        $S = self::initState($key); // Initial Permutation
        $i = 0;
        $j = 0;
        $output = '';
        $len = strlen($input);

        for ($pos = 0; $pos < $len; $pos++) {
            $char = $input[$pos];

            // Only process letters
            if (ctype_alpha($char)) {
                $isLower = ctype_lower($char);

                // 1. Advance the state logic
                $i = ($i + 1) % 26;
                $j = ($j + $S[$i]) % 26;

                // 2. Dynamic Swap (The "Mixing")
                $temp  = $S[$i];
                $S[$i] = $S[$j];
                $S[$j] = $temp;

                // 3. Generate Keystream Value (K)
                $t = ($S[$i] + $S[$j]) % 26;
                $K = $S[$t];

                // 4. Transform Character
                $val = ord(strtoupper($char)) - ord('A');

                if ($decrypt) {
                    $result = self::mod26($val - $K);
                } else {
                    $result = self::mod26($val + $K);
                }

                // 5. Restore Case
                $outChar = chr($result + ord('A'));
                $output .= $isLower ? strtolower($outChar) : $outChar;
            } else {
                // Pass non-alphas through
                $output .= $char;
            }
        }

        return $output;
    }

    public function geminiMod26StreamCipher($text, $encode = 1)
    {
        if ($encode == 1) {
            return self::crypt($text, $this->key, false);
        } else {
            return self::crypt($text, $this->key, true);
        }
    }

    /**
     * CPSV-3: Dual-State Nonlinear Stream Cipher
     * - Case-preserving
     * - Nonletters pass unchanged
     * - Uses two cumulative states (cumA, cumB)
     * - Nonlinear S-box
     * - Alphabetic positions use nonlinear salt
     */

    function cpsv3_get_sbox(): array
    {
        return [
            19,
            0,
            7,
            4,
            2,
            21,
            1,
            9,
            14,
            5,
            3,
            23,
            18,
            8,
            24,
            12,
            20,
            6,
            22,
            15,
            25,
            16,
            10,
            11,
            17,
            13
        ];
    }

    function cpsv3_mod26(int $x): int
    {
        $r = $x % 26;
        return $r < 0 ? $r + 26 : $r;
    }

    function cpsv3_encrypt_case_preserving(string $plaintext, string $baseKey): string
    {
        $sbox = $this->cpsv3_get_sbox();
        $ciphertext = '';

        $baseKey = strtoupper($baseKey);
        $keyLen = strlen($baseKey);
        if ($keyLen === 0) {
            throw new \InvalidArgumentException("Base key must not be empty.");
        }

        $keyVals = [];
        for ($i = 0; $i < $keyLen; $i++) {
            $ch = $baseKey[$i];
            if (!ctype_alpha($ch)) {
                throw new \InvalidArgumentException("Base key must be A-Z only.");
            }
            $keyVals[$i] = ord($ch) - ord('A');
        }

        // Two nonlinear states
        $cumA = 0;
        $cumB = 0;

        $len = strlen($plaintext);

        for ($pos = 0; $pos < $len; $pos++) {
            $ch = $plaintext[$pos];

            if (!ctype_alpha($ch)) {
                $ciphertext .= $ch;
                continue;
            }

            $isLower = ctype_lower($ch);
            $P_val = ord(strtoupper($ch)) - ord('A');

            $baseVal = $keyVals[$pos % $keyLen];

            // Nonlinear effective key
            $tmp = $this->cpsv3_mod26($baseVal + $cumA + 5 * $pos + $sbox[$cumB]);
            $K_eff = $sbox[$tmp];

            $C_val = $this->cpsv3_mod26($P_val + $K_eff);

            // Update states
            $cumA = $this->cpsv3_mod26($cumA + $C_val);
            $cumB = $this->cpsv3_mod26($cumB + $K_eff + $pos);
            // Output
            $out = chr($C_val + ord('A'));
            if ($isLower) {
                $out = strtolower($out);
            }

            $ciphertext .= $out;
        }

        return $ciphertext;
    }

    function cpsv3_decrypt_case_preserving(string $ciphertext, string $baseKey): string
    {
        $sbox = $this->cpsv3_get_sbox();
        $plaintext = '';

        $baseKey = strtoupper($baseKey);
        $keyLen = strlen($baseKey);
        if ($keyLen === 0) {
            throw new \InvalidArgumentException("Base key must not be empty.");
        }

        $keyVals = [];
        for ($i = 0; $i < $keyLen; $i++) {
            $ch = $baseKey[$i];
            if (!ctype_alpha($ch)) {
                throw new \InvalidArgumentException("Base key must be A-Z only.");
            }
            $keyVals[$i] = ord($ch) - ord('A');
        }

        $cumA = 0;
        $cumB = 0;

        $len = strlen($ciphertext);

        for ($pos = 0; $pos < $len; $pos++) {
            $ch = $ciphertext[$pos];

            if (!ctype_alpha($ch)) {
                $plaintext .= $ch;
                continue;
            }

            $isLower = ctype_lower($ch);
            $C_val = ord(strtoupper($ch)) - ord('A');

            $baseVal = $keyVals[$pos % $keyLen];

            // Recompute nonlinear key
            $tmp = $this->cpsv3_mod26($baseVal + $cumA + 5 * $pos + $sbox[$cumB]);
            $K_eff = $sbox[$tmp];

            $P_val = $this->cpsv3_mod26($C_val - $K_eff);

            // Update both states
            $cumA = $this->cpsv3_mod26($cumA + $C_val);
            $cumB = $this->cpsv3_mod26($cumB + $K_eff + $pos);
            $out = chr($P_val + ord('A'));
            if ($isLower) {
                $out = strtolower($out);
            }

            $plaintext .= $out;
        }

        return $plaintext;
    }
    function chatGPTCPSV3($text, $encode = 1)
    {
        $baseKey = $this->key; // Example base key, can be parameterized as needed

        if ($encode == 1) {
            return $this->cpsv3_encrypt_case_preserving($text, $baseKey);
        } else {
            return $this->cpsv3_decrypt_case_preserving($text, $baseKey);
        }
    }


    /* 
    by Grok 
    Does Not Work. 
    */

    function ThueMorse($text, $encode = 1)
    {
        $n = strlen($text);
        if ($n === 0) return '';

        // Generate Thue-Morse sequence t[0..n-1]
        $tm = [];
        for ($i = 0; $i < $n; $i++) {
            $popcount = substr_count(decbin($i), '1');
            $tm[] = $popcount % 2;
        }

        // Collect positions for 0 and 1
        $pos0 = [];
        $pos1 = [];
        for ($i = 0; $i < $n; $i++) {
            if ($tm[$i] === 0) {
                $pos0[] = $i;
            } else {
                $pos1[] = $i;
            }
        }
        $num0 = count($pos0);

        if ($encode) {
            // Encode: 0-pos chars first, then 1-pos
            $result = '';
            for ($j = 0; $j < $num0; $j++) {
                $result .= $text[$pos0[$j]];
            }
            for ($j = 0; $j < ($n - $num0); $j++) {
                $result .= $text[$pos1[$j]];
            }
            return $result;
        } else {
            // Decode: First num0 chars to 0-pos, rest to 1-pos
            $pt = array_fill(0, $n, '');  // Pre-allocate array
            $ct = str_split($text);
            for ($j = 0; $j < $num0; $j++) {
                $pt[$pos0[$j]] = $ct[$j];
            }
            for ($j = 0; $j < ($n - $num0); $j++) {
                $pt[$pos1[$j]] = $ct[$num0 + $j];
            }
            return implode('', $pt);
        }
    }

    // Example usage:
    // echo ThueMorse("ABCD", 1);  // Output: "ADBC"
    // echo ThueMorse("ADBC", 0);  // Output: "ABCD"


    public static function lanternShuffle(string $text, int $encode = 1): string
    {
        $chars = str_split($text);
        $n = count($chars);

        for ($i = 0; $i < $n; $i += 4) {
            // swap 0<->1
            if ($i + 1 < $n) {
                $tmp = $chars[$i];
                $chars[$i] = $chars[$i + 1];
                $chars[$i + 1] = $tmp;
            }
            // swap 2<->3
            if ($i + 3 < $n) {
                $tmp = $chars[$i + 2];
                $chars[$i + 2] = $chars[$i + 3];
                $chars[$i + 3] = $tmp;
            }
        }

        return implode('', $chars);
    }

    /* ============================================================
     *  GRADE 2: Crosshatch Permute
     *  - i mod 4 == 0 -> ASCII +1
     *  - i mod 4 == 1 -> ASCII -1
     *  - i mod 4 == 2 -> unchanged
     *  - i mod 4 == 3 -> flip case (letters only)
     * ========================================================== */
    public static function crosshatchPermute(string $text, int $encode = 1): string
    {
        $chars = str_split($text);
        $n = count($chars);

        for ($i = 0; $i < $n; $i++) {
            $ch = $chars[$i];
            $mode = $i % 4;

            if ($encode === 1) {
                switch ($mode) {
                    case 0: // +1
                        $chars[$i] = chr(ord($ch) + 1);
                        break;
                    case 1: // -1
                        $chars[$i] = chr(ord($ch) - 1);
                        break;
                    case 2: // unchanged
                        break;
                    case 3: // flip case if alpha
                        if (ctype_alpha($ch)) {
                            if (ctype_upper($ch)) {
                                $chars[$i] = strtolower($ch);
                            } else {
                                $chars[$i] = strtoupper($ch);
                            }
                        }
                        break;
                }
            } else {
                // decode: inverse operations
                switch ($mode) {
                    case 0: // -1
                        $chars[$i] = chr(ord($ch) - 1);
                        break;
                    case 1: // +1
                        $chars[$i] = chr(ord($ch) + 1);
                        break;
                    case 2: // unchanged
                        break;
                    case 3: // flip case again
                        if (ctype_alpha($ch)) {
                            if (ctype_upper($ch)) {
                                $chars[$i] = strtolower($ch);
                            } else {
                                $chars[$i] = strtoupper($ch);
                            }
                        }
                        break;
                }
            }
        }

        return implode('', $chars);
    }

    /* ============================================================
     *  GRADE 3: Orbital Cascade
     *
     *  Encryption:
     *    Shift[0] = ASCII(P[0]) mod 7
     *    Shift[i] = (Shift[i-1] + ASCII(P[i])) mod 26
     *    If P[i] is a letter -> C[i] = ROT(P[i], Shift[i])
     *    Else C[i] = P[i] (unchanged) but still affects Shift[i]
     *
     *  Decryption:
     *    Reconstruct P[i] sequentially.
     *    - For non-letters: P[i] = C[i]; Shift based on ASCII.
     *    - For letters: brute-force candidate P[i] in [A-Za-z],
     *      test Shift formula + ROT match.
     * ========================================================== */
    public static function orbitalCascade(string $text, int $encode = 1): string
    {
        if ($encode === 1) {
            return self::orbitalCascadeEncrypt($text);
        } else {
            return self::orbitalCascadeDecrypt($text);
        }
    }

    private static function orbitalCascadeEncrypt(string $text): string
    {
        $n = strlen($text);
        if ($n === 0) {
            return '';
        }

        $chars = str_split($text);
        $shiftArr = array_fill(0, $n, 0);

        // i = 0
        $shiftArr[0] = ord($chars[0]) % 7;

        // output array
        $out = [];

        for ($i = 0; $i < $n; $i++) {
            if ($i > 0) {
                $shiftArr[$i] = ($shiftArr[$i - 1] + ord($chars[$i])) % 26;
            }

            $ch = $chars[$i];

            if (ctype_alpha($ch)) {
                $out[$i] = self::rotAlpha($ch, $shiftArr[$i]);
            } else {
                $out[$i] = $ch;
            }
        }

        return implode('', $out);
    }

    private static function orbitalCascadeDecrypt(string $text): string
    {
        $n = strlen($text);
        if ($n === 0) {
            return '';
        }

        $C = str_split($text);
        $P = array_fill(0, $n, '');
        $shiftArr = array_fill(0, $n, 0);

        // i = 0
        if (ctype_alpha($C[0])) {
            // brute force P[0]
            $found = false;
            foreach (self::letterRange() as $candidate) {
                $shift0 = ord($candidate) % 7;
                if (self::rotAlpha($candidate, $shift0) === $C[0]) {
                    $P[0] = $candidate;
                    $shiftArr[0] = $shift0;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                // fallback: treat as unchanged
                $P[0] = $C[0];
                $shiftArr[0] = ord($P[0]) % 7;
            }
        } else {
            $P[0] = $C[0];
            $shiftArr[0] = ord($P[0]) % 7;
        }

        // i > 0
        for ($i = 1; $i < $n; $i++) {
            $chC = $C[$i];
            $prevShift = $shiftArr[$i - 1];

            if (!ctype_alpha($chC)) {
                // non-letter, passes unchanged and updates shift
                $P[$i] = $chC;
                $shiftArr[$i] = ($prevShift + ord($P[$i])) % 26;
            } else {
                // brute-force letter
                $found = false;
                foreach (self::letterRange() as $candidate) {
                    $shift = ($prevShift + ord($candidate)) % 26;
                    if (self::rotAlpha($candidate, $shift) === $chC) {
                        $P[$i] = $candidate;
                        $shiftArr[$i] = $shift;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    // fallback: assume no rotation
                    $P[$i] = $chC;
                    $shiftArr[$i] = ($prevShift + ord($P[$i])) % 26;
                }
            }
        }

        return implode('', $P);
    }

    private static function letterRange(): array
    {
        static $letters = null;
        if ($letters === null) {
            $letters = [];
            for ($c = ord('A'); $c <= ord('Z'); $c++) {
                $letters[] = chr($c);
            }
            for ($c = ord('a'); $c <= ord('z'); $c++) {
                $letters[] = chr($c);
            }
        }
        return $letters;
    }

    private static function rotAlpha(string $ch, int $shift): string
    {
        if (ctype_upper($ch)) {
            $p = ord($ch) - ord('A');
            return chr(ord('A') + (($p + $shift) % 26));
        }
        if (ctype_lower($ch)) {
            $p = ord($ch) - ord('a');
            return chr(ord('a') + (($p + $shift) % 26));
        }
        return $ch;
    }

    /* ============================================================
     *  GRADE 4: Cycladic Staircase Cipher (CSC)
     *  - Stateful cumulative substitution + even/odd transposition
     *  - Keyword + numeric seed
     * ========================================================== */
    public static function CSC(string $text, int $encode = 1, string $keyword = "SLIMRULES", string $seed = "314159"): string
    {
        // Base shift from keyword
        $sum = 0;
        for ($i = 0; $i < strlen($keyword); $i++) {
            $sum += ord(strtoupper($keyword[$i]));
        }
        $B = $sum % 26;

        // Digits from seed
        $digits = str_split($seed);
        $m = count($digits);

        $n = strlen($text);
        $Delta = [];
        for ($i = 0; $i < $n; $i++) {
            $Delta[$i] = intval($digits[$i % $m]);
        }

        $C = array_fill(0, $n, 0);
        if ($n > 0) {
            $C[0] = ($B + $Delta[0]) % 26;
            for ($i = 1; $i < $n; $i++) {
                $C[$i] = ($C[$i - 1] + $Delta[$i]) % 26;
            }
        }

        if ($encode === 1) {
            // Step 1: substitution
            $T = str_split($text);
            for ($i = 0; $i < $n; $i++) {
                if (ctype_alpha($T[$i])) {
                    $T[$i] = self::shiftLetterCSC($T[$i], $C[$i]);
                }
            }

            // Step 2: even/odd shuffle
            $even = [];
            $odd = [];
            for ($i = 0; $i < $n; $i++) {
                if ($i % 2 === 0) {
                    $even[] = $T[$i];
                } else {
                    $odd[] = $T[$i];
                }
            }
            return implode('', $even) . implode('', $odd);
        } else {
            // decode
            $cipherChars = str_split($text);
            $n = count($cipherChars);

            $evenCount = intdiv($n + 1, 2);
            $E = array_slice($cipherChars, 0, $evenCount);
            $O = array_slice($cipherChars, $evenCount);

            $T = array_fill(0, $n, '');
            $ei = 0;
            $oi = 0;
            for ($i = 0; $i < $n; $i++) {
                if ($i % 2 === 0) {
                    $T[$i] = $E[$ei++];
                } else {
                    $T[$i] = $O[$oi++];
                }
            }

            // Undo substitution
            for ($i = 0; $i < $n; $i++) {
                if (ctype_alpha($T[$i])) {
                    $T[$i] = self::unshiftLetterCSC($T[$i], $C[$i]);
                }
            }

            return implode('', $T);
        }
    }

    private static function shiftLetterCSC(string $ch, int $shift): string
    {
        if (ctype_upper($ch)) {
            $p = ord($ch) - ord('A');
            return chr(ord('A') + (($p + $shift) % 26));
        }
        if (ctype_lower($ch)) {
            $p = ord($ch) - ord('a');
            return chr(ord('a') + (($p + $shift) % 26));
        }
        return $ch;
    }

    private static function unshiftLetterCSC(string $ch, int $shift): string
    {
        if (ctype_upper($ch)) {
            $c = ord($ch) - ord('A');
            return chr(ord('A') + (($c - $shift + 26) % 26));
        }
        if (ctype_lower($ch)) {
            $c = ord($ch) - ord('a');
            return chr(ord('a') + (($c - $shift + 26) % 26));
        }
        return $ch;
    }

    /* ============================================================
     *  GRADE 5: Astraglyph Matrix Cipher
     *
     *  Practical adaptation:
     *  - Use base64 alphabet (64 chars) as domain/range.
     *  - Modulus = 64
     *  - Text must be restricted to base64 chars; you can pre-base64
     *    encode arbitrary binary/text before using this.
     *
     *  Matrix for block i (0-based):
     *    [ (K+i)   1       2   ]   all taken mod 64
     *    [ 3     (K+2i)    4   ]
     *    [ 5       6     (K+3i)]
     *
     *  Encrypt:
     *    - Map chars to indices 0..63.
     *    - Group into triples [x,y,z], pad with 'A' (0) if needed.
     *    - O_i = M_i * V_i (mod 64).
     *    - Map back to chars.
     *
     *  Decrypt:
     *    - Rebuild M_i, invert mod 64, V_i = M_i^{-1} * O_i (mod 64).
     * ========================================================== */
    public static function astraglyphMatrix(string $text, int $encode = 1, int $key = 7): string
    {
        $alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
        $mod = 64;

        if ($encode === 1) {
            // Map to indices
            $indices = [];
            $len = strlen($text);
            for ($i = 0; $i < $len; $i++) {
                $ch = $text[$i];
                $pos = strpos($alphabet, $ch);
                if ($pos === false) {
                    throw new \InvalidArgumentException("Character '$ch' not in Astraglyph base64 alphabet.");
                }
                $indices[] = $pos;
            }

            // Pad to multiple of 3 with 'A' -> 0
            while (count($indices) % 3 !== 0) {
                $indices[] = 0;
            }

            $outIdx = [];
            $blocks = intdiv(count($indices), 3);

            for ($b = 0; $b < $blocks; $b++) {
                $x = $indices[$b * 3];
                $y = $indices[$b * 3 + 1];
                $z = $indices[$b * 3 + 2];

                $M = self::astraglyphMatrixForBlock($key, $b, $mod);

                // O = M * V mod 64
                $ox = ($M[0][0] * $x + $M[0][1] * $y + $M[0][2] * $z) % $mod;
                $oy = ($M[1][0] * $x + $M[1][1] * $y + $M[1][2] * $z) % $mod;
                $oz = ($M[2][0] * $x + $M[2][1] * $y + $M[2][2] * $z) % $mod;

                $outIdx[] = $ox;
                $outIdx[] = $oy;
                $outIdx[] = $oz;
            }

            // Map back to chars
            $outChars = array_map(function ($i) use ($alphabet) {
                return $alphabet[$i];
            }, $outIdx);

            return implode('', $outChars);
        } else {
            // Decode
            $indices = [];
            $len = strlen($text);
            if ($len % 3 !== 0) {
                throw new \InvalidArgumentException("Astraglyph ciphertext length must be multiple of 3.");
            }
            for ($i = 0; $i < $len; $i++) {
                $ch = $text[$i];
                $pos = strpos($alphabet, $ch);
                if ($pos === false) {
                    throw new \InvalidArgumentException("Character '$ch' not in Astraglyph base64 alphabet.");
                }
                $indices[] = $pos;
            }

            $blocks = intdiv(count($indices), 3);
            $plainIdx = [];

            for ($b = 0; $b < $blocks; $b++) {
                $ox = $indices[$b * 3];
                $oy = $indices[$b * 3 + 1];
                $oz = $indices[$b * 3 + 2];

                $M = self::astraglyphMatrixForBlock($key, $b, $mod);
                $Minv = self::invert3x3Mod($M, $mod);

                // V = M^{-1} * O mod 64
                $x = ($Minv[0][0] * $ox + $Minv[0][1] * $oy + $Minv[0][2] * $oz) % $mod;
                $y = ($Minv[1][0] * $ox + $Minv[1][1] * $oy + $Minv[1][2] * $oz) % $mod;
                $z = ($Minv[2][0] * $ox + $Minv[2][1] * $oy + $Minv[2][2] * $oz) % $mod;

                $plainIdx[] = $x;
                $plainIdx[] = $y;
                $plainIdx[] = $z;
            }

            $outChars = array_map(function ($i) use ($alphabet) {
                return $alphabet[$i];
            }, $plainIdx);

            return implode('', $outChars);
        }
    }

    private static function astraglyphMatrixForBlock(int $key, int $blockIndex, int $mod): array
    {
        // Using simple increasing entries; all mod 64.
        $a11 = ($key + $blockIndex) % $mod;
        $a22 = ($key + 2 * $blockIndex) % $mod;
        $a33 = ($key + 3 * $blockIndex) % $mod;

        return [
            [$a11, 1,   2],
            [3,   $a22, 4],
            [5,   6,   $a33],
        ];
    }

    // 3x3 matrix inverse modulo m
    private static function invert3x3Mod(array $M, int $mod): array
    {
        // determinant
        $a = $M[0][0];
        $b = $M[0][1];
        $c = $M[0][2];
        $d = $M[1][0];
        $e = $M[1][1];
        $f = $M[1][2];
        $g = $M[2][0];
        $h = $M[2][1];
        $i = $M[2][2];

        $det =
            $a * ($e * $i - $f * $h) -
            $b * ($d * $i - $f * $g) +
            $c * ($d * $h - $e * $g);

        $det = self::mod($det, $mod);
        $detInv = self::modInverse($det, $mod);

        // adjugate (transpose of cofactor matrix)
        $cof00 =  ($e * $i - $f * $h);
        $cof01 = - ($d * $i - $f * $g);
        $cof02 =  ($d * $h - $e * $g);

        $cof10 = - ($b * $i - $c * $h);
        $cof11 =  ($a * $i - $c * $g);
        $cof12 = - ($a * $h - $b * $g);

        $cof20 =  ($b * $f - $c * $e);
        $cof21 = - ($a * $f - $c * $d);
        $cof22 =  ($a * $e - $b * $d);

        // adjugate = transpose of cofactors
        $adj = [
            [$cof00, $cof10, $cof20],
            [$cof01, $cof11, $cof21],
            [$cof02, $cof12, $cof22],
        ];

        // Multiply adjugate by detInv mod m
        $inv = [];
        for ($r = 0; $r < 3; $r++) {
            $inv[$r] = [];
            for ($c = 0; $c < 3; $c++) {
                $val = self::mod($adj[$r][$c], $mod);
                $inv[$r][$c] = self::mod($val * $detInv, $mod);
            }
        }

        return $inv;
    }

    private static function mod(int $x, int $m): int
    {
        $r = $x % $m;
        if ($r < 0) {
            $r += $m;
        }
        return $r;
    }

    private static function modInverse(int $a, int $m): int
    {
        $a = self::mod($a, $m);
        if ($a === 0) {
            throw new \RuntimeException("No modular inverse for 0 mod $m.");
        }

        // Extended Euclid
        $m0 = $m;
        $x0 = 0;
        $x1 = 1;

        while ($a > 1) {
            $q = intdiv($a, $m);
            $t = $m;

            $m = $a % $m;
            $a = $t;
            $t = $x0;

            $x0 = $x1 - $q * $x0;
            $x1 = $t;
        }

        if ($x1 < 0) {
            $x1 += $m0;
        }

        return $x1;
    }


    // --- EASY: Caesar Shift (Shift of 3) ---
    public function caesarShift($text, $encode = 1)
    {
        $shift = 3;
        $result = '';
        $shift = $encode == 1 ? $shift : -$shift;
        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            if (ctype_alpha($char)) {
                $offset = ord(ctype_upper($char) ? 'A' : 'a');
                $char = chr(($offset + (ord($char) - $offset + $shift + 26) % 26));
            }
            $result .= $char;
        }
        return $result;
    }

    // --- MEDIUM: Vigenère Cipher (Key: "CRYPTO") ---
    public function vigeneresCipher($text, $encode = 1)
    {
        $key = "CRYPTO";
        $keyLength = strlen($key);
        $result = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            if (ctype_alpha($char)) {
                $keyChar = $key[$i % $keyLength];
                $keyShift = ord(ctype_upper($keyChar) ? $keyChar : strtolower($keyChar)) - ord(ctype_upper($keyChar) ? 'A' : 'a');
                $offset = ord(ctype_upper($char) ? 'A' : 'a');
                $shift = $encode ? $keyShift : -$keyShift;
                $char = chr(($offset + (ord($char) - $offset + $shift + 26) % 26));
            }
            $result .= $char;
        }
        return $result;
    }

    // --- HARD: Rail Fence Cipher (3 Rails) + Caesar Shift (Shift of 5) ---
    public function railFenceCaesar($text, $encode = 1)
    {
        $shift = 5;
        if ($encode) {
            // Encode: Rail Fence -> Caesar
            $railText = $this->railFence($text, 3, 1);
            return $this->caesarShiftCustom($railText, $shift, 1);
        } else {
            // Decode: Caesar -> Rail Fence
            $caesarText = $this->caesarShiftCustom($text, $shift, 0);
            return $this->railFence($caesarText, 3, 0);
        }
    }

    private function railFence($text, $rails, $encode)
    {
        if ($encode) {
            $result = '';
            $railStrings = array_fill(0, $rails, '');
            $rail = 0;
            $direction = 1;
            for ($i = 0; $i < strlen($text); $i++) {
                $railStrings[$rail] .= $text[$i];
                $rail += $direction;
                if ($rail == $rails - 1 || $rail == 0) {
                    $direction *= -1;
                }
            }
            return implode('', $railStrings);
        } else {
            $railLengths = array_fill(0, $rails, 0);
            $rail = 0;
            $direction = 1;
            for ($i = 0; $i < strlen($text); $i++) {
                $railLengths[$rail]++;
                $rail += $direction;
                if ($rail == $rails - 1 || $rail == 0) {
                    $direction *= -1;
                }
            }
            $railsText = str_split($text, $railLengths[0]);
            $railsText[1] = substr($text, $railLengths[0], $railLengths[1]);
            $railsText[2] = substr($text, $railLengths[0] + $railLengths[1]);

            $result = '';
            $rail = 0;
            $direction = 1;
            $pos = array(0, 0, 0);
            for ($i = 0; $i < strlen($text); $i++) {
                $result .= $railsText[$rail][$pos[$rail]];
                $pos[$rail]++;
                $rail += $direction;
                if ($rail == $rails - 1 || $rail == 0) {
                    $direction *= -1;
                }
            }
            return $result;
        }
    }

    private function caesarShiftCustom($text, $shift, $encode)
    {
        $shift = $encode ? $shift : -$shift;
        $result = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            if (ctype_alpha($char)) {
                $offset = ord(ctype_upper($char) ? 'A' : 'a');
                $char = chr(($offset + (ord($char) - $offset + $shift + 26) % 26));
            }
            $result .= $char;
        }
        return $result;
    }

    // --- VERY HARD: Playfair Cipher (Key: "MONARCHY") + XOR Mask (0x55) ---
    public function playfairXOR($text, $encode = 1)
    {
        $key = "MONARCHY";
        if ($encode) {
            // Encode: Playfair -> XOR
            $playfairText = $this->playfair($text, $key, 1);
            return $this->applyXOR($playfairText, 0x55);
        } else {
            // Decode: XOR -> Playfair
            $xorText = $this->applyXOR($text, 0x55);
            return $this->playfair($xorText, $key, 0);
        }
    }

    private function playfair($text, $key, $encode)
    {
        $key = strtoupper(preg_replace('/[^A-Z]/', '', $key));
        $keySquare = $this->generateKeySquare($key);
        $text = strtoupper(preg_replace('/[^A-Z]/', '', $text));
        $text = $this->preparePlayfairText($text);
        $result = '';
        for ($i = 0; $i < strlen($text); $i += 2) {
            $a = $text[$i];
            $b = $text[$i + 1];
            $rowA = $colA = $rowB = $colB = 0;
            for ($row = 0; $row < 5; $row++) {
                for ($col = 0; $col < 5; $col++) {
                    if ($keySquare[$row][$col] == $a) {
                        $rowA = $row;
                        $colA = $col;
                    }
                    if ($keySquare[$row][$col] == $b) {
                        $rowB = $row;
                        $colB = $col;
                    }
                }
            }
            if ($rowA == $rowB) {
                $result .= $keySquare[$rowA][($colA + ($encode ? 1 : 4)) % 5];
                $result .= $keySquare[$rowB][($colB + ($encode ? 1 : 4)) % 5];
            } elseif ($colA == $colB) {
                $result .= $keySquare[($rowA + ($encode ? 1 : 4)) % 5][$colA];
                $result .= $keySquare[($rowB + ($encode ? 1 : 4)) % 5][$colB];
            } else {
                $result .= $keySquare[$rowA][$colB];
                $result .= $keySquare[$rowB][$colA];
            }
        }
        return $result;
    }

    private function generateKeySquare($key)
    {
        $key = str_replace('J', 'I', $key);
        $keySquare = array();
        $used = array();
        $row = $col = 0;
        for ($i = 0; $i < strlen($key); $i++) {
            if (!in_array($key[$i], $used)) {
                $keySquare[$row][$col] = $key[$i];
                $used[] = $key[$i];
                $col++;
                if ($col == 5) {
                    $col = 0;
                    $row++;
                }
            }
        }
        for ($c = ord('A'); $c <= ord('Z'); $c++) {
            if ($c == ord('J')) continue;
            $char = chr($c);
            if (!in_array($char, $used)) {
                $keySquare[$row][$col] = $char;
                $col++;
                if ($col == 5) {
                    $col = 0;
                    $row++;
                }
            }
        }
        return $keySquare;
    }

    private function preparePlayfairText($text)
    {
        $text = preg_replace('/J/', 'I', $text);
        $result = '';
        for ($i = 0; $i < strlen($text); $i += 2) {
            if ($i + 1 >= strlen($text)) {
                $result .= $text[$i] . 'X';
            } elseif ($text[$i] == $text[$i + 1]) {
                $result .= $text[$i] . 'X' . $text[$i + 1];
            } else {
                $result .= $text[$i] . $text[$i + 1];
            }
        }
        return $result;
    }

    private function applyXOR($text, $mask)
    {
        $result = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $result .= chr(ord($text[$i]) ^ $mask);
        }
        return $result;
    }

    // --- EXTREME: Hybrid Cipher (AES-128 + Base64 + Null Cipher) ---
    public function hybridExtreme($text, $encode = 1)
    {
        $key = "CIPHERFORGE12345"; // 16 bytes for AES-128
        $nullKey = "THEQUICKBROWNFOX";
        if ($encode) {
            // Encode: AES-128 -> Base64 -> Null Cipher
            $aesText = $this->aesEncrypt($text, $key);
            $base64Text = base64_encode($aesText);
            return $this->nullCipher($base64Text, $nullKey, 1);
        } else {
            // Decode: Null Cipher -> Base64 -> AES-128
            $nullText = $this->nullCipher($text, $nullKey, 0);
            $aesText = base64_decode($nullText);
            return $this->aesDecrypt($aesText, $key);
        }
    }

    private function aesEncrypt($text, $key)
    {
        return openssl_encrypt($text, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, str_repeat("\0", 16));
    }

    private function aesDecrypt($text, $key)
    {
        return openssl_decrypt($text, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, str_repeat("\0", 16));
    }

    private function nullCipher($text, $key, $encode)
    {
        if ($encode) {
            // Embed $text into $key using null cipher (simple example: every 2nd letter)
            $result = '';
            $textPtr = 0;
            for ($i = 0; $i < strlen($key); $i++) {
                $result .= $key[$i];
                if ($i % 2 == 0 && $textPtr < strlen($text)) {
                    $result .= $text[$textPtr];
                    $textPtr++;
                }
            }
            return $result;
        } else {
            // Extract $text from $key
            $result = '';
            for ($i = 0; $i < strlen($key); $i++) {
                if ($i % 2 == 1 && $i + 1 < strlen($text)) {
                    $result .= $text[$i + 1];
                }
            }
            return $result;
        }
    }
}
