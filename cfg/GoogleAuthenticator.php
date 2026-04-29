<?php
// PHPGangsta Google Authenticator Class
// Source: https://github.com/PHPGangsta/GoogleAuthenticator

class GoogleAuthenticator
{
    protected $_codeLength = 6;

    public function createSecret($secretLength = 16)
    {
        $validChars = $this->_getBase32LookupTable();
        
        // Random secret generation
        if (function_exists('random_bytes')) {
            $secret = '';
            $rnd = random_bytes($secretLength);
            for ($i = 0; $i < $secretLength; ++$i) {
                $secret .= $validChars[ord($rnd[$i]) & 31];
            }
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $secret = '';
            $rnd = openssl_random_pseudo_bytes($secretLength);
            for ($i = 0; $i < $secretLength; ++$i) {
                $secret .= $validChars[ord($rnd[$i]) & 31];
            }
        } else {
            $secret = '';
            for ($i = 0; $i < $secretLength; ++$i) {
                $secret .= $validChars[array_rand($validChars)];
            }
        }
        return $secret;
    }

    public function getCode($secret, $time = null)
    {
        if ($time === null) {
            $time = floor(time() / 30);
        }

        $base32 = new FixedBitNotation(5, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567', TRUE, TRUE);
        $secret = $base32->decode($secret);

        $time = pack("N", $time);
        $time = str_pad($time, 8, chr(0), STR_PAD_LEFT);

        $hash = hash_hmac('sha1', $time, $secret, true);
        $offset = ord(substr($hash, -1));
        $code = (int)(
            (ord(substr($hash, $offset, 1)) & 0x7f) << 24 |
            (ord(substr($hash, $offset + 1, 1)) & 0xff) << 16 |
            (ord(substr($hash, $offset + 2, 1)) & 0xff) << 8 |
            (ord(substr($hash, $offset + 3, 1)) & 0xff)
        );

        $code = $code % pow(10, $this->_codeLength);
        return str_pad($code, $this->_codeLength, '0', STR_PAD_LEFT);
    }

    public function verifyCode($secret, $code, $discrepancy = 1, $currentTimeSlice = null)
    {
        if ($currentTimeSlice === null) {
            $currentTimeSlice = floor(time() / 30);
        }

        if (strlen($code) != 6) {
            return false;
        }

        for ($i = -$discrepancy; $i <= $discrepancy; ++$i) {
            $calculatedCode = $this->getCode($secret, $currentTimeSlice + $i);
            if ($this->timingSafeEquals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    public function setCodeLength($length)
    {
        $this->_codeLength = $length;
        return $this;
    }

    protected function _getBase32LookupTable()
    {
        return array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
            'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
            '='  // padding char
        );
    }

    private function timingSafeEquals($safeString, $userString)
    {
        if (function_exists('hash_equals')) {
            return hash_equals($safeString, $userString);
        }
        $safeLen = strlen($safeString);
        $userLen = strlen($userString);

        if ($userLen != $safeLen) {
            return false;
        }

        $result = 0;

        for ($i = 0; $i < $userLen; ++$i) {
            $result |= (ord($safeString[$i]) ^ ord($userString[$i]));
        }

        return $result === 0;
    }
}

// Helper class for Base32 decoding if not available
class FixedBitNotation
{
    protected $_chars;
    protected $_bits;
    protected $_rightPadFinalBits;
    protected $_padFinalGroup;
    protected $_padChar;

    public function __construct($bits, $chars = NULL, $rightPadFinalBits = FALSE, $padFinalGroup = FALSE, $padChar = '=')
    {
        if (is_integer($bits) && $bits >= 1 && $bits <= 8) {
            $this->_bits = $bits;
        } else {
            $this->_bits = 5;
        }
        
        $this->_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $this->_rightPadFinalBits = $rightPadFinalBits;
        $this->_padFinalGroup = $padFinalGroup;
        $this->_padChar = $padChar;
    }

    public function decode($string)
    {
        if (strlen($string) == 0) return '';
        
        $string = strtoupper($string);
        $data = '';
        $buffer = 0;
        $bufferSize = 0;
        
        for ($i = 0; $i < strlen($string); $i++) {
            $char = $string[$i];
            $index = strpos($this->_chars, $char);
            
            if ($index === false && $char != $this->_padChar) {
                continue; 
            }
            
            if ($char == $this->_padChar) {
                continue;
            }
            
            $buffer = ($buffer << $this->_bits) | $index;
            $bufferSize += $this->_bits;
            
            if ($bufferSize >= 8) {
                $bufferSize -= 8;
                $data .= chr(($buffer >> $bufferSize) & 0xFF);
            }
        }
        
        return $data;
    }
}
?>
