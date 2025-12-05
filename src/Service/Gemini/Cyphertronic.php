<?php

namespace App\Service\Gemini;

class Cyphertronic {
    
    /**
     * The secret passphrase used to derive the prime sequence.
     * @var string
     */
    private $key = 'secret';

    /**
     * Constructor allows overriding the default key.
     */
    public function __construct($customKey = null) {
        if ($customKey !== null) {
            $this->key = $customKey;
        }
    }

    /**
     * Helper: Generates the next prime number after a given integer.
     * @param int $current
     * @return int
     */
    private function getNextPrime($current) {
        $num = $current + 1;
        while (true) {
            $isPrime = true;
            if ($num < 2) $isPrime = false;
            else {
                for ($i = 2; $i * $i <= $num; $i++) {
                    if ($num % $i == 0) {
                        $isPrime = false;
                        break;
                    }
                }
            }
            if ($isPrime) return $num;
            $num++;
        }
    }

    /**
     * Helper: Converts string key to a numeric seed.
     * Uses CRC32 to ensure a consistent integer is derived from the string.
     * @return int
     */
    private function getSeed() {
        // crc32 returns an integer checksum of the string
        return abs(crc32($this->key));
    }

    /**
     * The Core Logic: Prime Gap Cipher
     * @param string $text Input string
     * @param int $encode 1 for Encryption, -1 for Decryption
     * @return string
     */
    public function PrimeGapCipher($text, $encode = 1) {
        $output = "";
        
        // 1. Sanitize Input (A-Z only for this demo)
        $text = strtoupper(preg_replace('/[^a-zA-Z]/', '', $text));
        $length = strlen($text);
        
        // 2. Initialize Prime Sequence from Key
        $seed = $this->getSeed();
        $currentPrime = $this->getNextPrime($seed);
        
        for ($i = 0; $i < $length; $i++) {
            // Calculate Gap
            $nextPrime = $this->getNextPrime($currentPrime);
            $gap = $nextPrime - $currentPrime;
            
            // Advance Sequence
            $currentPrime = $nextPrime;
            
            // Perform Shift
            $charVal = ord($text[$i]) - 65; // 0-25
            
            // Math: (Char + (Gap * Direction)) Modulo 26
            $shifted = ($charVal + ($gap * $encode)) % 26;
            
            // Fix PHP negative modulo result
            if ($shifted < 0) {
                $shifted += 26;
            }
            
            $output .= chr($shifted + 65);
        }
        
        return $output;
    }
}

?>