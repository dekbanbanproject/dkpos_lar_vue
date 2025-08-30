<?php

namespace App\Support;

/**
 * สร้าง EMVCo payload สำหรับ PromptPay (Merchant Presented)
 * รองรับ: เบอร์มือถือ, เลขบัตรประชาชน, eWallet ID
 */
class PromptPay
{
    public static function mobile(string $mobileE164, float $amount, string $ref1 = null, string $merchant = 'BAKERY', string $city = 'BANGKOK'): string
    {
        // mobileE164 ต้องขึ้นต้นด้วย 66… เช่น 6691xxxxxxx
        $idType = '01'; // 01=Mobile, 02=TaxID, 03=eWallet
        return self::build($idType, $mobileE164, $amount, $ref1, $merchant, $city);
    }

    public static function taxId(string $tid, float $amount, string $ref1 = null, string $merchant = 'BAKERY', string $city = 'BANGKOK'): string
    {
        return self::build('02', $tid, $amount, $ref1, $merchant, $city);
    }

    public static function eWallet(string $ewid, float $amount, string $ref1 = null, string $merchant = 'BAKERY', string $city = 'BANGKOK'): string
    {
        return self::build('03', $ewid, $amount, $ref1, $merchant, $city);
    }

    private static function build(string $idType, string $account, float $amount, ?string $ref1, string $merchant, string $city): string
    {
        // EMV TLV helper
        $T = fn($id,$val) => $id . str_pad(strlen($val),2,'0',STR_PAD_LEFT) . $val;

        // 00: Payload Format Indicator = "01"
        $payload = $T('00','01');

        // 01: Point of Initiation Method = "12" (dynamic)
        $payload .= $T('01','12');

        // 29: Merchant Account Information - PromptPay
        // 00 AID = A000000677010111
        // 01 mobile / 02 tax / 03 eWallet
        $mai  = $T('00','A000000677010111');
        $mai .= $T($idType, $account);  // E164 mobile (66...), Tax ID (13), eWallet (15)
        if ($ref1) $mai .= $T('05', substr($ref1,0,25)); // Ref1 (optional) — keep <=25 chars
        $payload .= $T('29',$mai);

        // 52 Merchant Category Code - 0000 (default)
        $payload .= $T('52','0000');

        // 53 Transaction Currency - 764 (THB)
        $payload .= $T('53','764');

        // 54 Transaction Amount
        $payload .= $T('54', number_format($amount,2,'.',''));

        // 58 Country Code
        $payload .= $T('58','TH');

        // 59 Merchant Name (<=25)
        $payload .= $T('59', substr(strtoupper($merchant),0,25));

        // 60 Merchant City
        $payload .= $T('60', substr(strtoupper($city),0,15));

        // 63 CRC (placeholder "6304" + CRC16-CCITT-FALSE)
        $payloadWithCrc = $payload . '6304';
        $crc = strtoupper(self::crc16($payloadWithCrc));
        return $payloadWithCrc . $crc;
    }

    // CRC-16/CCITT-FALSE
    private static function crc16(string $s): string
    {
        $crc = 0xFFFF;
        $length = strlen($s);
        for ($c = 0; $c < $length; $c++) {
            $crc ^= ord($s[$c]) << 8;
            for ($i = 0; $i < 8; $i++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) : ($crc << 1);
                $crc &= 0xFFFF;
            }
        }
        return str_pad(dechex($crc), 4, '0', STR_PAD_LEFT);
    }

    /** แปลง 08xxxxxxxx เป็น E164 66xxxxxxxxx */
    public static function toE164Mobile(string $mobile): string
    {
        $d = preg_replace('/\D/','',$mobile);
        if (str_starts_with($d,'0')) $d = '66' . substr($d,1);
        if (str_starts_with($d,'66') === false) $d = '66'.$d;
        return $d;
    }
}
