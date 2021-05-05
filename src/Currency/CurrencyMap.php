<?php declare(strict_types=1);

namespace App\Currency;

use App\Exception\RuntimeException;

class CurrencyMap
{
    public const CHOICES = [
        // source https://quickbooks.intuit.com/community/Help-Articles/List-of-supported-currencies/td-p/185442
        'AED' => 'UAE Dirham',
        'AFN' => 'Afghan Afghani',
        'ALL' => 'Albanian Lek',
        'AMD' => 'Armenian Dram',
        'ANG' => 'Dutch Guilder',
        'AOA' => 'Angolan Kwanza',
        'ARS' => 'Argentine Peso',
        'AUD' => 'Australian Dollar',
        'AWG' => 'Aruban Florin',
        'AZN' => 'Azerbaijan Manat',
        'BAM' => 'Bosnian Mark',
        'BBD' => 'Barbadian Dollar',
        'BDT' => 'Bangladeshi Taka',
        'BGN' => 'Bulgarian Lev',
        'BHD' => 'Bahraini Dinar',
        'BIF' => 'Burundi Franc',
        'BMD' => 'Bermuda Dollar',
        'BND' => 'Brunei Dollar',
        'BOB' => 'Bolivian Boliviano',
        'BRL' => 'Brazilian Real',
        'BSD' => 'Bahamian Dollar',
        'BTC' => 'Bitcoin',
        'BTN' => 'Bhutanese Hgultrum',
        'BWP' => 'Botswana Pula',
        'BYN' => 'Belarussian Ruble',
        'BZD' => 'Belizean Dollar',
        'CAD' => 'Canadian Dollar',
        'CDF' => 'Congolese Franc',
        'CHK' => 'Swiss Franc',
        'CLP' => 'Chilean Peso',
        'CRC' => 'Costa Rica Colon',
        'CUP' => 'Cuban Peso',
        'CVE' => 'Cape Verde Escudo',
        'CZK' => 'Czech Koruna',
        'DJF' => 'Djibouti Franc',
        'DKK' => 'Danish Krone',
        'DOP' => 'Dominican Peso',
        'DZD' => 'Algerian Dinar',
        'EGP' => 'Egyptian Pound',
        'ERN' => 'Eritrean Nakfa',
        'EUR' => 'Euro',
        'ETB' => 'Ethiopian Birr',
        'ETH' => 'Ethereum',
        'FJD' => 'Fiji Dollar',
        'FKP' => 'Falkland Islands Pound',
        'GBP' => 'British Pound Sterling',
        'GEL' => 'Georgian Lari',
        'GHS' => 'Ghanaian Cedi',
        'GIP' => 'Gibraltar Pound',
        'GMD' => 'Gambian Dalasi',
        'GNF' => 'Guinea Franc',
        'GTQ' => 'Guatemalan Quetzal',
        'GYD' => 'Guyana Dollar',
        'HKD' => 'Hong Kong Dollar',
        'HNL' => 'Honduran Lempira',
        'HRK' => 'Croatian Kuna',
        'HTG' => 'Haiti Gourde',
        'HUF' => 'Hungarian Forint',
        'IDR' => 'Indonesian Rupiah',
        'ILS' => 'Israeli Shekel',
        'INR' => 'Indian Rupee',
        'IQD' => 'Iraqi Dinar',
        'IRR' => 'Iranian Rial',
        'ISK' => 'Iceland Krona',
        'JMD' => 'Jamaican Dollar',
        'JOD' => 'Jordanian Dinar',
        'JPY' => 'Japanese Yen',
        'KES' => 'Kenyan Shilling',
        'KGS' => 'Kyrgyzstani Som',
        'KHR' => 'Cambodian Riel',
        'KMF' => 'Comoro Franc',
        'KPW' => 'North Korean Won',
        'KRW' => 'South Korean Won',
        'KWD' => 'Kuwaiti Dinar',
        'KTD' => 'Cayman Islands Dollar',
        'KZT' => 'Kazakhstan Tenge',
        'LAK' => 'Lao Kip',
        'LBP' => 'Lebanese Pound',
        'LKR' => 'Sri Lankan Rupee',
        'LRD' => 'Liberian Dollar',
        'LSL' => 'Lesotho Loti',
        'LTC' => 'Litecoin',
        'LYD' => 'Libyan Dinar',
        'MAD' => 'Moroccan Dirham',
        'MDL' => 'Moldovan Leu',
        'MGA' => 'Malagasy Ariary',
        'MKD' => 'Macedonian Denar',
        'MMK' => 'Myanmar Kyat',
        'MNT' => 'Mongolian Tugrik',
        'MOP' => 'Macanese Pataca',
        'MRO' => 'Mauritanian Ouguiya',
        'MUR' => 'Mauritius Rupee',
        'MVR' => 'Maldives Rufiyaa',
        'MWK' => 'Malawian Kwacha',
        'MXN' => 'Mexican Peso',
        'MYR' => 'Malaysian Ringgit',
        'MZN' => 'Mozambique Metical',
        'NAD' => 'Namibian Dollar',
        'NGN' => 'Nigerian Naira',
        'NIO' => 'Nicaragua Cordoba',
        'NOK' => 'Norwegian Krone',
        'NPR' => 'Nepalese Rupee',
        'NZD' => 'New Zealand Dollar',
        'OMR' => 'Omani Rial',
        'PAB' => 'Panama Balboa',
        'PEN' => 'Peruvian Nuevo Sol',
        'PGK' => 'Papua New Guinean Kina',
        'PHP' => 'Philippine Peso',
        'PKR' => 'Pakistani Rupee',
        'PLN' => 'Polish Zloty',
        'PYG' => 'Paraguayan Guarani',
        'QAR' => 'Qatari Riyal',
        'RON' => 'Romanian Leu',
        'RSD' => 'Serbian Dinar',
        'RUB' => 'Russian Ruble/Rouble',
        'RWF' => 'Rwanda Franc',
        'SAR' => 'Saudi Riyal',
        'SBD' => 'Solomon Islands Dollar',
        'SCR' => 'Seychelles Rupee',
        'SDG' => 'Sudanese Pound',
        'SEK' => 'Swedish Krona',
        'SGD' => 'Singapore Dollar',
        'SHP' => 'St. Helena Pound',
        'SLL' => 'Sierra Leonean Leone',
        'SOS' => 'Somali Shilling',
        'SRD' => 'Surinam Dollar',
        'STD' => 'Sao Tome and Principe Dobra',
        'SVC' => 'El Salvador Colon',
        'SYP' => 'Syrian Pound',
        'SZL' => 'Swaziland Lilangeni',
        'THB' => 'Thai Baht',
        'TJS' => 'Tajikistani Somoni',
        'TMT' => 'Turkmenistani Manat',
        'TND' => 'Tunisian Dinar',
        'TOP' => 'Tonga Pa\'anga',
        'TRY' => 'Turkish Lira',
        'TTD' => 'Trinidad & Tobago Dollar',
        'TWD' => 'Taiwanese Dollar',
        'TZS' => 'Tanzanian Shilling',
        'UAH' => 'Ukrainian Hryvnia',
        'UGX' => 'Ugandan Shilling',
        'USD' => 'US Dollar',
        'UYU' => 'Uruguayan Peso',
        'UZS' => 'Uzbekistan Som',
        'VEF' => 'Venezuealan Bolivar Fuerte',
        'VND' => 'Vietnam Dong',
        'VUV' => 'Vanuatu Vatu',
        'WST' => 'Samoa Tala',
        'XAF' => 'CFA Franc',
        'XCD' => 'East Caribbean Dollar',
        'XOF' => 'CFA Franc',
        'XPF' => 'CFP Franc',
        'YER' => 'Yemeni Rial',
        'ZAR' => 'South African Rand',
        'ZMW' => 'Zambian Kwacha',
    ];

    const SYMBOLS = [
        'HKD' => 'HK$',
        'RUB' => '₽',
        'EUR' => '€',
        'HUF' => 'Ft',
        'BGN' => 'лв',
        'USD' => '$',
    ];

    public function getFormChoices(): array
    {
        $choices = [];
        foreach (self::CHOICES as $name => $label) {
            $label .= ' - '.$name;
            $choices[$label] = $name;
        }
        ksort($choices);
        return $choices;
    }

    public function findCurrency(?string $search): array
    {
        $search = $search ?? '';
        $search = strtolower($search);
        foreach (self::CHOICES as $name => $label) {
            if ($search === strtolower($label) ||
                $search === strtolower($name) ||
                $search === strtolower(self::SYMBOLS[$name] ?? '')
            ) {
                return [$name, $label];
            }
        }
        throw new RuntimeException("Unable to find currency for {$search}");
    }
}
