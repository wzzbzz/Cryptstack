<?php


namespace App\Service\chatGPT;


class CipherforgeSuite
{
    private $stack = ['lanternShuffle', 'crosshatchPermute', 'orbitalCascade'];
    private $key = '';


    public function __construct()
    {
    }

    public function setStack(array $stack)
    {
        $this->stack = $stack;
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
    /* ============================================================
     *  GRADE 1: Lantern Shuffle
     *  - Block size 4
     *  - Swap (0<->1) and (2<->3) inside each block
     *  - Self-inverse (same operation for encode/decode)
     * ========================================================== */
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
            [ $a11, 1,   2 ],
            [ 3,   $a22, 4 ],
            [ 5,   6,   $a33 ],
        ];
    }

    // 3x3 matrix inverse modulo m
    private static function invert3x3Mod(array $M, int $mod): array
    {
        // determinant
        $a = $M[0][0]; $b = $M[0][1]; $c = $M[0][2];
        $d = $M[1][0]; $e = $M[1][1]; $f = $M[1][2];
        $g = $M[2][0]; $h = $M[2][1]; $i = $M[2][2];

        $det =
            $a * ($e * $i - $f * $h) -
            $b * ($d * $i - $f * $g) +
            $c * ($d * $h - $e * $g);

        $det = self::mod($det, $mod);
        $detInv = self::modInverse($det, $mod);

        // adjugate (transpose of cofactor matrix)
        $cof00 =  ($e * $i - $f * $h);
        $cof01 = -($d * $i - $f * $g);
        $cof02 =  ($d * $h - $e * $g);

        $cof10 = -($b * $i - $c * $h);
        $cof11 =  ($a * $i - $c * $g);
        $cof12 = -($a * $h - $b * $g);

        $cof20 =  ($b * $f - $c * $e);
        $cof21 = -($a * $f - $c * $d);
        $cof22 =  ($a * $e - $b * $d);

        // adjugate = transpose of cofactors
        $adj = [
            [ $cof00, $cof10, $cof20 ],
            [ $cof01, $cof11, $cof21 ],
            [ $cof02, $cof12, $cof22 ],
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

}
