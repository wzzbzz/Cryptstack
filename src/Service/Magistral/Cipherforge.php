<?php

namespace App\Service\Magistral;


class Cipherforge
{
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
        $key = strtoupper($key);
        $keyLength = strlen($key);
        $result = '';
        $text = strtoupper($text);
        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            if (ctype_alpha($char)) {
                $keyChar = $key[$i % $keyLength];
                $keyShift = ord($keyChar) - ord('A');
                $shift = $encode == 1 ? $keyShift : -$keyShift;
                $charCode = ord($char) - ord('A');
                $newCharCode = ($charCode + $shift + 26) % 26;
                $char = chr($newCharCode + ord('A'));
            }
            $result .= $char;
        }
        return $result;
    }

    // --- HARD: Rail Fence Cipher (3 Rails) + Caesar Shift (Shift of 5) ---
    public function railFenceCaesar($text, $encode = 1)
    {
        $shift = 5;
        if ($encode == 1) {
            // Encode: Rail Fence -> Caesar
            $railText = $this->railFence($text, 3, 1);
            return $this->caesarShiftCustom($railText, $shift, 1);
        } else {
            // Decode: Caesar -> Rail Fence
            $caesarText = $this->caesarShiftCustom($text, $shift, -1);
            return $this->railFence($caesarText, 3, -1);
        }
    }

    private function railFence($text, $rails, $encode)
    {
        if ($encode == 1) {
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
        $shift = $encode == 1 ? $shift : -$shift;
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
        if ($encode == 1) {
            // Encode: Playfair -> XOR
            $playfairText = $this->playfair($text, $key, 1);
            return $this->applyXOR($playfairText, 0x55);
        } else {
            // Decode: XOR -> Playfair
            $xorText = $this->applyXOR($text, 0x55);
            return $this->playfair($xorText, $key, -1);
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
                $result .= $keySquare[$rowA][($colA + ($encode == 1 ? 1 : 4)) % 5];
                $result .= $keySquare[$rowB][($colB + ($encode == 1 ? 1 : 4)) % 5];
            } elseif ($colA == $colB) {
                $result .= $keySquare[($rowA + ($encode == 1 ? 1 : 4)) % 5][$colA];
                $result .= $keySquare[($rowB + ($encode == 1 ? 1 : 4)) % 5][$colB];
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
        if ($encode == 1) {
            // Encode: AES-128 -> Base64 -> Null Cipher
            $aesText = $this->aesEncrypt($text, $key);
            $base64Text = base64_encode($aesText);
            return $this->nullCipher($base64Text, $nullKey, 1);
        } else {
            // Decode: Null Cipher -> Base64 -> AES-128
            $nullText = $this->nullCipher($text, $nullKey, -1);
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
        if ($encode == 1) {
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

    // --- NEW EASY: Atbash Cipher (Reverse Alphabet) ---
    public function atbash($text, $encode = 1)
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $reversed = strrev($alphabet);
        $result = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $char = strtoupper($text[$i]);
            if (ctype_alpha($char)) {
                $pos = strpos($alphabet, $char);
                $result .= $pos !== false ? $reversed[$pos] : $char;
            } else {
                $result .= $char;
            }
        }
        return $result;
    }

    // --- NEW MEDIUM: Gronsfeld Cipher (Key: 2, 4, 6, 8) ---
    public function gronsfeld($text, $encode = 1)
    {
        $key = [2, 4, 6, 8];
        $result = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $char = strtoupper($text[$i]);
            if (ctype_alpha($char)) {
                $shift = $key[$i % count($key)];
                $shift = $encode == 1 ? $shift : -$shift;
                $offset = ord('A');
                $char = chr(($offset + (ord($char) - $offset + $shift + 26) % 26));
            }
            $result .= $char;
        }
        return $result;
    }

    // --- NEW HARD: Straddle Cipher (Key: "CRYPTOGRAPHY", Checkerboard) ---
    public function straddle($text, $encode = 1)
    {
        $key = "CRYPTOGRAPHY";
        $key = strtoupper(preg_replace('/[^A-Z]/', '', $key));
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $keyMap = [];
        $remaining = '';
        // Build key map
        for ($i = 0; $i < strlen($key); $i++) {
            $keyMap[$key[$i]] = $i + 1;
        }
        // Assign remaining letters
        $counter = 11;
        for ($i = 0; $i < strlen($alphabet); $i++) {
            if (!isset($keyMap[$alphabet[$i]])) {
                $keyMap[$alphabet[$i]] = $counter++;
            }
        }
        // Encode/Decode
        if ($encode == 1) {
            $result = '';
            for ($i = 0; $i < strlen($text); $i++) {
                $char = strtoupper($text[$i]);
                if (isset($keyMap[$char])) {
                    $result .= $keyMap[$char] . ' ';
                }
            }
            return trim($result);
        } else {
            $numbers = explode(' ', $text);
            $result = '';
            foreach ($numbers as $num) {
                foreach ($keyMap as $letter => $value) {
                    if ($value == $num) {
                        $result .= $letter;
                        break;
                    }
                }
            }
            return $result;
        }
    }

    // --- NEW VERY HARD: Four-Square Cipher (Keys: "HELLO", "WORLD") ---
    public function fourSquare($text, $encode = 1)
    {
        $key1 = "HELLO";
        $key2 = "WORLD";
        $text = strtoupper(preg_replace('/[^A-Z]/', '', $text));
        $square1 = $this->buildSquare($key1);
        $square2 = $this->buildSquare($key2);
        $result = '';
        for ($i = 0; $i < strlen($text); $i += 2) {
            $a = $text[$i];
            $b = $text[$i + 1] ?? 'X';
            $rowA = $colA = $rowB = $colB = 0;
            // Find positions in squares
            for ($row = 0; $row < 5; $row++) {
                for ($col = 0; $col < 5; $col++) {
                    if ($square1[$row][$col] == $a) {
                        $rowA = $row;
                        $colA = $col;
                    }
                    if ($square2[$row][$col] == $b) {
                        $rowB = $row;
                        $colB = $col;
                    }
                }
            }
            if ($encode == 1) {
                $result .= $square1[$rowA][$colB] . $square2[$rowB][$colA];
            } else {
                $result .= $square1[$rowA][$colB] . $square2[$rowB][$colA];
            }
        }
        return $result;
    }

    private function buildSquare($key)
    {
        $key = str_replace('J', 'I', strtoupper($key));
        $alphabet = 'ABCDEFGHIKLMNOPQRSTUVWXYZ';
        $square = array_fill(0, 5, array_fill(0, 5, ''));
        $used = [];
        $row = $col = 0;
        // Fill key
        for ($i = 0; $i < strlen($key); $i++) {
            if (!in_array($key[$i], $used)) {
                $square[$row][$col] = $key[$i];
                $used[] = $key[$i];
                $col++;
                if ($col == 5) {
                    $col = 0;
                    $row++;
                }
            }
        }
        // Fill remaining letters
        for ($i = 0; $i < strlen($alphabet); $i++) {
            if (!in_array($alphabet[$i], $used)) {
                $square[$row][$col] = $alphabet[$i];
                $col++;
                if ($col == 5) {
                    $col = 0;
                    $row++;
                }
            }
        }
        return $square;
    }

    // --- NEW EXTREME: Chaocipher (Custom Implementation) ---
    public function chaocipher($text, $encode = 1)
    {
        $leftAlphabet = 'PTLNBQDCOFUGKHZEVJMYAXWRIS';
        $rightAlphabet = 'ABSCDFGHIJKLMNOPQRSTUVWXYZ';
        $text = strtoupper(preg_replace('/[^A-Z]/', '', $text));
        $result = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            if ($encode == 1) {
                // Encode
                $pos = strpos($leftAlphabet, $char);
                $result .= $rightAlphabet[$pos];
                // Permute left alphabet
                $leftAlphabet = substr($leftAlphabet, $pos) .
                                substr($leftAlphabet, 0, $pos);
                $leftAlphabet = substr($leftAlphabet, 1) . $leftAlphabet[0];
                // Permute right alphabet
                $rightAlphabet = substr($rightAlphabet, $pos) .
                                 substr($rightAlphabet, 0, $pos);
                $rightAlphabet = substr($rightAlphabet, 1) . $rightAlphabet[0];
                // Insert permuted characters
                $leftAlphabet = $this->insertPermuted($leftAlphabet, $rightAlphabet[0]);
                $rightAlphabet = $this->insertPermuted($rightAlphabet, $leftAlphabet[1]);
            } else {
                // Decode (reverse process)
                $pos = strpos($rightAlphabet, $char);
                $result .= $leftAlphabet[$pos];
                // Reverse permute right alphabet
                $rightAlphabet = substr($rightAlphabet, $pos) .
                                 substr($rightAlphabet, 0, $pos);
                $rightAlphabet = substr($rightAlphabet, 1) . $rightAlphabet[0];
                // Reverse permute left alphabet
                $leftAlphabet = substr($leftAlphabet, $pos) .
                                substr($leftAlphabet, 0, $pos);
                $leftAlphabet = substr($leftAlphabet, 1) . $leftAlphabet[0];
                // Reverse insert permuted characters
                $leftAlphabet = $this->reverseInsertPermuted($leftAlphabet, $rightAlphabet[0]);
                $rightAlphabet = $this->reverseInsertPermuted($rightAlphabet, $leftAlphabet[1]);
            }
        }
        return $result;
    }

    private function insertPermuted($alphabet, $char)
    {
        $pos = strpos($alphabet, $char);
        return $char . substr($alphabet, 0, $pos) . substr($alphabet, $pos + 1);
    }

    private function reverseInsertPermuted($alphabet, $char)
    {
        $pos = strpos($alphabet, $char);
        return substr($alphabet, 1, $pos) . $char . substr($alphabet, $pos + 1) . $alphabet[0];
    }
}
?>
