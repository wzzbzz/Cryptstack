<?php


namespace App\Service\Grok;


class CipherforgeLibrary
{
    private $key;

    const ALPH_BASE = 32;   // space
    const ALPH_SIZE = 95;   // printable ASCII

    public function __construct($key = '')
    {
        $this->key = $key;
    }

    // =============================================
    // Grade 1 — SCINTILLA CIPHER (keyword monoalphabetic)
    // =============================================
    public function method1($text, $encode = 1)
    {
        if (empty($this->key)) {
            throw new \Exception("Scintilla demands a key!");
        }

        $spark = $this->buildSparkAlphabet();

        $result = '';

        if ($encode == 1) {
            // ENCRYPT
            for ($i = 0; $i < strlen($text); $i++) {
                $char = $text[$i];
                if (ctype_alpha($char)) {
                    $pos = ord(strtoupper($char)) - ord('A');
                    $sub = $spark[$pos];
                    $result .= ctype_upper($char) ? $sub : strtolower($sub);
                } else {
                    $result .= $char;
                }
            }
        } else {
            // DECRYPT
            $normal = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $reverseMap = array_flip(str_split($spark));

            for ($i = 0; $i < strlen($text); $i++) {
                $char = $text[$i];
                if (ctype_alpha($char)) {
                    $c = strtoupper($char);
                    $pos = $reverseMap[$c];
                    $orig = $normal[$pos];
                    $result .= ctype_upper($char) ? $orig : strtolower($orig);
                } else {
                    $result .= $char;
                }
            }
        }

        return $result;
    }

    private function buildSparkAlphabet()
    {
        $keyUpper = strtoupper($this->key);
        $used = [];
        $spark = '';

        for ($i = 0; $i < strlen($keyUpper); $i++) {
            $c = $keyUpper[$i];
            if (ctype_alpha($c) && !isset($used[$c])) {
                $spark .= $c;
                $used[$c] = true;
            }
        }

        for ($o = ord('A'); $o <= ord('Z'); $o++) {
            $c = chr($o);
            if (!isset($used[$c])) {
                $spark .= $c;
            }
        }

        return $spark; // always 26 letters
    }

    // =============================================
    // Grade 2 — EMBER CIPHER (classic Vigenère)
    // =============================================
    public function method2($text, $encode = 1)
    {
        $cleanKey = '';
        for ($i = 0; $i < strlen($this->key); $i++) {
            $c = strtoupper($this->key[$i]);
            if (ctype_alpha($c)) {
                $cleanKey .= $c;
            }
        }

        if ($cleanKey === '') {
            throw new Exception("Ember requires at least one letter in the key!");
        }

        $keyLen = strlen($cleanKey);
        $result = '';
        $keyIndex = 0;

        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            if (ctype_alpha($char)) {
                $isUpper = ctype_upper($char);
                $plainPos = ord(strtoupper($char)) - ord('A');

                $keyShift = ord($cleanKey[$keyIndex % $keyLen]) - ord('A');

                if ($encode == 1) {
                    $cipherPos = ($plainPos + $keyShift) % 26;
                } else {
                    $cipherPos = ($plainPos - $keyShift + 26) % 26;
                }

                $cipherChar = chr($cipherPos + ord('A'));
                $result .= $isUpper ? $cipherChar : strtolower($cipherChar);

                $keyIndex++;
            } else {
                $result .= $char;
            }
        }

        return $result;
    }

    // =============================================
    // Grade 3 — BLAZE CIPHER (autokey Vigenère)
    // =============================================
    public function method3($text, $encode = 1)
    {
        $priming = preg_replace('/[^A-Z]/', '', strtoupper($this->key));
        if ($priming === '') {
            throw new Exception("Blaze needs a priming key with letters!");
        }

        $result = '';
        $keyStream = $priming;

        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];

            if (ctype_alpha($char)) {
                $isUpper = ctype_upper($char);
                $p = ord(strtoupper($char)) - 65;

                $kChar = $keyStream[0];
                $keyStream = substr($keyStream, 1);
                $k = ord($kChar) - 65;

                if ($encode == 1) {
                    $c = ($p + $k) % 26;
                    $newChar = chr($c + 65);
                    $keyStream .= strtoupper($char);            // feed plaintext
                } else {
                    $c = ($p - $k + 26) % 26;
                    $newChar = chr($c + 65);
                    $keyStream .= $newChar;                     // feed recovered plaintext
                }

                $result .= $isUpper ? $newChar : strtolower($newChar);
            } else {
                $result .= $char;
            }
        }

        return $result;
    }

    // =============================================
    // Grade 4 — CONFLAGRATION CIPHER (full-ASCII ciphertext-autokey)
    // =============================================
    public function method4($text, $encode = 1)
    {
        if ($this->key === '') {
            throw new Exception("Conflagration demands a priming key!");
        }

        $result = '';
        $keyStream = $this->key;

        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            $pos = ord($char) - self::ALPH_BASE;
            $pos = ($pos < 0 ? $pos + 256 : $pos) % self::ALPH_SIZE;

            $kChar = $keyStream[0];
            $keyStream = substr($keyStream, 1);
            $kShift = ord($kChar) % self::ALPH_SIZE;

            if ($encode == 1) {
                $newPos = ($pos + $kShift) % self::ALPH_SIZE;
            } else {
                $newPos = ($pos - $kShift + self::ALPH_SIZE) % self::ALPH_SIZE;
            }

            $newChar = chr($newPos + self::ALPH_BASE);
            $result .= $newChar;

            $keyStream .= ($encode == 1 ? $newChar : $char); // feed ciphertext in both directions
        }

        return $result;
    }

    // =============================================
    // Grade 5 — SUPERNOVA CIPHER (SHA-512 counter-mode stream cipher)
    // =============================================
    private function generateKeystream($length)
    {
        if ($this->key === '') {
            throw new Exception("Supernova cannot ignite without fuel!");
        }

        $keystream = '';
        $counter = 0;

        while (strlen($keystream) < $length) {
            $context = $this->key . ':' . $counter;
            $block = hash('sha512', $context, true);
            $keystream .= $block;
            $counter++;
        }

        return substr($keystream, 0, $length);
    }

    public function method5($text, $encode = 1)
    {
        $keystream = $this->generateKeystream(strlen($text));

        $result = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $result .= chr(ord($text[$i]) ^ ord($keystream[$i]));
        }

        return $result;
    }

    // =============================================
    // Classic — VIGENÈRE (named exactly as requested)
    // =============================================
    public function vigeneresCipher($text, $encode = 1)
    {
        return $this->method2($text, $encode); // true historical Vigenère = Ember Cipher
    }
}