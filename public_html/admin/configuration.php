<?php
// +--------------------------------------------------------------------------+
// | glFusion CMS                                                             |
// +--------------------------------------------------------------------------+
// | configuration.php                                                        |
// |                                                                          |
// | Loads the administration UI and sends input to config.class              |
// +--------------------------------------------------------------------------+
// | $Id::                                                                   $|
// +--------------------------------------------------------------------------+
// |                                                                          |
// | Based on the Geeklog CMS                                                 |
// | Copyright (C) 2007-2008 by the following authors:                        |
// |                                                                          |
// | Authors: Aaron Blankstein  - kantai AT gmail DOT com                     |
// +--------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software Foundation,  |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.          |
// |                                                                          |
// +--------------------------------------------------------------------------+

require_once '../lib-common.php';
require_once 'auth.inc.php';

$conf_group = $inputHandler->getVar('strict','conf_group','post','Core');

$config = config::get_instance();

/**
* Helper function: Provide language dropdown
*
* @return   Array   Array of (filename, displayname) pairs
*
* @note     Note that key/value are being swapped!
*
*/
function configmanager_select_language_helper()
{
    global $_CONF;

    return array_flip(MBYTE_languageList($_CONF['default_charset']));
}

/**
* Helper function: Provide themes dropdown
*
* @return   Array   Array of (filename, displayname) pairs
*
* @note     Beautifying code duplicated from usersettings.php
*
*/
function configmanager_select_theme_helper()
{
    $themes = array();

    $themeFiles = COM_getThemes(true);

    usort($themeFiles,
          create_function('$a,$b', 'return strcasecmp($a,$b);'));

    foreach ($themeFiles as $theme) {
        $words = explode ('_', $theme);
        $bwords = array ();
        foreach ($words as $th) {
            if ((strtolower ($th{0}) == $th{0}) &&
                (strtolower ($th{1}) == $th{1})) {
                $bwords[] = strtoupper ($th{0}) . substr ($th, 1);
            } else {
                $bwords[] = $th;
            }
        }

        $themes[implode(' ', $bwords)] = $theme;
    }
    return $themes;
}

if ( !function_exists('timezone_identifiers_list') ) {
    function timezone_identifiers_list() {
        return array(
            0 => "Africa/Abidjan",
            1 => "Africa/Accra",
            2 => "Africa/Addis_Ababa",
            3 => "Africa/Algiers",
            4 => "Africa/Asmara",
            5 => "Africa/Asmera",
            6 => "Africa/Bamako",
            7 => "Africa/Bangui",
            8 => "Africa/Banjul",
            9 => "Africa/Bissau",
            10 => "Africa/Blantyre",
            11 => "Africa/Brazzaville",
            12 => "Africa/Bujumbura",
            13 => "Africa/Cairo",
            14 => "Africa/Casablanca",
            15 => "Africa/Ceuta",
            16 => "Africa/Conakry",
            17 => "Africa/Dakar",
            18 => "Africa/Dar_es_Salaam",
            19 => "Africa/Djibouti",
            20 => "Africa/Douala",
            21 => "Africa/El_Aaiun",
            22 => "Africa/Freetown",
            23 => "Africa/Gaborone",
            24 => "Africa/Harare",
            25 => "Africa/Johannesburg",
            26 => "Africa/Kampala",
            27 => "Africa/Khartoum",
            28 => "Africa/Kigali",
            29 => "Africa/Kinshasa",
            30 => "Africa/Lagos",
            31 => "Africa/Libreville",
            32 => "Africa/Lome",
            33 => "Africa/Luanda",
            34 => "Africa/Lubumbashi",
            35 => "Africa/Lusaka",
            36 => "Africa/Malabo",
            37 => "Africa/Maputo",
            38 => "Africa/Maseru",
            39 => "Africa/Mbabane",
            40 => "Africa/Mogadishu",
            41 => "Africa/Monrovia",
            42 => "Africa/Nairobi",
            43 => "Africa/Ndjamena",
            44 => "Africa/Niamey",
            45 => "Africa/Nouakchott",
            46 => "Africa/Ouagadougou",
            47 => "Africa/Porto-Novo",
            48 => "Africa/Sao_Tome",
            49 => "Africa/Timbuktu",
            50 => "Africa/Tripoli",
            51 => "Africa/Tunis",
            52 => "Africa/Windhoek",
            53 => "America/Adak",
            54 => "America/Anchorage",
            55 => "America/Anguilla",
            56 => "America/Antigua",
            57 => "America/Araguaina",
            58 => "America/Argentina/Buenos_Aires",
            59 => "America/Argentina/Catamarca",
            60 => "America/Argentina/ComodRivadavia",
            61 => "America/Argentina/Cordoba",
            62 => "America/Argentina/Jujuy",
            63 => "America/Argentina/La_Rioja",
            64 => "America/Argentina/Mendoza",
            65 => "America/Argentina/Rio_Gallegos",
            66 => "America/Argentina/San_Juan",
            67 => "America/Argentina/Tucuman",
            68 => "America/Argentina/Ushuaia",
            69 => "America/Aruba",
            70 => "America/Asuncion",
            71 => "America/Atikokan",
            72 => "America/Atka",
            73 => "America/Bahia",
            74 => "America/Barbados",
            75 => "America/Belem",
            76 => "America/Belize",
            77 => "America/Blanc-Sablon",
            78 => "America/Boa_Vista",
            79 => "America/Bogota",
            80 => "America/Boise",
            81 => "America/Buenos_Aires",
            82 => "America/Cambridge_Bay",
            83 => "America/Campo_Grande",
            84 => "America/Cancun",
            85 => "America/Caracas",
            86 => "America/Catamarca",
            87 => "America/Cayenne",
            88 => "America/Cayman",
            89 => "America/Chicago",
            90 => "America/Chihuahua",
            91 => "America/Coral_Harbour",
            92 => "America/Cordoba",
            93 => "America/Costa_Rica",
            94 => "America/Cuiaba",
            95 => "America/Curacao",
            96 => "America/Danmarkshavn",
            97 => "America/Dawson",
            98 => "America/Dawson_Creek",
            99 => "America/Denver",
            100 => "America/Detroit",
            101 => "America/Dominica",
            102 => "America/Edmonton",
            103 => "America/Eirunepe",
            104 => "America/El_Salvador",
            105 => "America/Ensenada",
            106 => "America/Fort_Wayne",
            107 => "America/Fortaleza",
            108 => "America/Glace_Bay",
            109 => "America/Godthab",
            110 => "America/Goose_Bay",
            111 => "America/Grand_Turk",
            112 => "America/Grenada",
            113 => "America/Guadeloupe",
            114 => "America/Guatemala",
            115 => "America/Guayaquil",
            116 => "America/Guyana",
            117 => "America/Halifax",
            118 => "America/Havana",
            119 => "America/Hermosillo",
            120 => "America/Indiana/Indianapolis",
            121 => "America/Indiana/Knox",
            122 => "America/Indiana/Marengo",
            123 => "America/Indiana/Petersburg",
            124 => "America/Indiana/Vevay",
            125 => "America/Indiana/Vincennes",
            126 => "America/Indiana/Winamac",
            127 => "America/Indianapolis",
            128 => "America/Inuvik",
            129 => "America/Iqaluit",
            130 => "America/Jamaica",
            131 => "America/Jujuy",
            132 => "America/Juneau",
            133 => "America/Kentucky/Louisville",
            134 => "America/Kentucky/Monticello",
            135 => "America/Knox_IN",
            136 => "America/La_Paz",
            137 => "America/Lima",
            138 => "America/Los_Angeles",
            139 => "America/Louisville",
            140 => "America/Maceio",
            141 => "America/Managua",
            142 => "America/Manaus",
            143 => "America/Martinique",
            144 => "America/Mazatlan",
            145 => "America/Mendoza",
            146 => "America/Menominee",
            147 => "America/Merida",
            148 => "America/Mexico_City",
            149 => "America/Miquelon",
            150 => "America/Moncton",
            151 => "America/Monterrey",
            152 => "America/Montevideo",
            153 => "America/Montreal",
            154 => "America/Montserrat",
            155 => "America/Nassau",
            156 => "America/New_York",
            157 => "America/Nipigon",
            158 => "America/Nome",
            159 => "America/Noronha",
            160 => "America/North_Dakota/Center",
            161 => "America/North_Dakota/New_Salem",
            162 => "America/Panama",
            163 => "America/Pangnirtung",
            164 => "America/Paramaribo",
            165 => "America/Phoenix",
            166 => "America/Port-au-Prince",
            167 => "America/Port_of_Spain",
            168 => "America/Porto_Acre",
            169 => "America/Porto_Velho",
            170 => "America/Puerto_Rico",
            171 => "America/Rainy_River",
            172 => "America/Rankin_Inlet",
            173 => "America/Recife",
            174 => "America/Regina",
            175 => "America/Resolute",
            176 => "America/Rio_Branco",
            177 => "America/Rosario",
            178 => "America/Santiago",
            179 => "America/Santo_Domingo",
            180 => "America/Sao_Paulo",
            181 => "America/Scoresbysund",
            182 => "America/Shiprock",
            183 => "America/St_Johns",
            184 => "America/St_Kitts",
            185 => "America/St_Lucia",
            186 => "America/St_Thomas",
            187 => "America/St_Vincent",
            188 => "America/Swift_Current",
            189 => "America/Tegucigalpa",
            190 => "America/Thule",
            191 => "America/Thunder_Bay",
            192 => "America/Tijuana",
            193 => "America/Toronto",
            194 => "America/Tortola",
            195 => "America/Vancouver",
            196 => "America/Virgin",
            197 => "America/Whitehorse",
            198 => "America/Winnipeg",
            199 => "America/Yakutat",
            200 => "America/Yellowknife",
            201 => "Antarctica/Casey",
            202 => "Antarctica/Davis",
            203 => "Antarctica/DumontDUrville",
            204 => "Antarctica/Mawson",
            205 => "Antarctica/McMurdo",
            206 => "Antarctica/Palmer",
            207 => "Antarctica/Rothera",
            208 => "Antarctica/South_Pole",
            209 => "Antarctica/Syowa",
            210 => "Antarctica/Vostok",
            211 => "Arctic/Longyearbyen",
            212 => "Asia/Aden",
            213 => "Asia/Almaty",
            214 => "Asia/Amman",
            215 => "Asia/Anadyr",
            216 => "Asia/Aqtau",
            217 => "Asia/Aqtobe",
            218 => "Asia/Ashgabat",
            219 => "Asia/Ashkhabad",
            220 => "Asia/Baghdad",
            221 => "Asia/Bahrain",
            222 => "Asia/Baku",
            223 => "Asia/Bangkok",
            224 => "Asia/Beirut",
            225 => "Asia/Bishkek",
            226 => "Asia/Brunei",
            227 => "Asia/Calcutta",
            228 => "Asia/Choibalsan",
            229 => "Asia/Chongqing",
            230 => "Asia/Chungking",
            231 => "Asia/Colombo",
            232 => "Asia/Dacca",
            233 => "Asia/Damascus",
            234 => "Asia/Dhaka",
            235 => "Asia/Dili",
            236 => "Asia/Dubai",
            237 => "Asia/Dushanbe",
            238 => "Asia/Gaza",
            239 => "Asia/Harbin",
            240 => "Asia/Hong_Kong",
            241 => "Asia/Hovd",
            242 => "Asia/Irkutsk",
            243 => "Asia/Istanbul",
            244 => "Asia/Jakarta",
            245 => "Asia/Jayapura",
            246 => "Asia/Jerusalem",
            247 => "Asia/Kabul",
            248 => "Asia/Kamchatka",
            249 => "Asia/Karachi",
            250 => "Asia/Kashgar",
            251 => "Asia/Katmandu",
            252 => "Asia/Krasnoyarsk",
            253 => "Asia/Kuala_Lumpur",
            254 => "Asia/Kuching",
            255 => "Asia/Kuwait",
            256 => "Asia/Macao",
            257 => "Asia/Macau",
            258 => "Asia/Magadan",
            259 => "Asia/Makassar",
            260 => "Asia/Manila",
            261 => "Asia/Muscat",
            262 => "Asia/Nicosia",
            263 => "Asia/Novosibirsk",
            264 => "Asia/Omsk",
            265 => "Asia/Oral",
            266 => "Asia/Phnom_Penh",
            267 => "Asia/Pontianak",
            268 => "Asia/Pyongyang",
            269 => "Asia/Qatar",
            270 => "Asia/Qyzylorda",
            271 => "Asia/Rangoon",
            272 => "Asia/Riyadh",
            273 => "Asia/Saigon",
            274 => "Asia/Sakhalin",
            275 => "Asia/Samarkand",
            276 => "Asia/Seoul",
            277 => "Asia/Shanghai",
            278 => "Asia/Singapore",
            279 => "Asia/Taipei",
            280 => "Asia/Tashkent",
            281 => "Asia/Tbilisi",
            282 => "Asia/Tehran",
            283 => "Asia/Tel_Aviv",
            284 => "Asia/Thimbu",
            285 => "Asia/Thimphu",
            286 => "Asia/Tokyo",
            287 => "Asia/Ujung_Pandang",
            288 => "Asia/Ulaanbaatar",
            289 => "Asia/Ulan_Bator",
            290 => "Asia/Urumqi",
            291 => "Asia/Vientiane",
            292 => "Asia/Vladivostok",
            293 => "Asia/Yakutsk",
            294 => "Asia/Yekaterinburg",
            295 => "Asia/Yerevan",
            296 => "Atlantic/Azores",
            297 => "Atlantic/Bermuda",
            298 => "Atlantic/Canary",
            299 => "Atlantic/Cape_Verde",
            300 => "Atlantic/Faeroe",
            301 => "Atlantic/Faroe",
            302 => "Atlantic/Jan_Mayen",
            303 => "Atlantic/Madeira",
            304 => "Atlantic/Reykjavik",
            305 => "Atlantic/South_Georgia",
            306 => "Atlantic/St_Helena",
            307 => "Atlantic/Stanley",
            308 => "Australia/ACT",
            309 => "Australia/Adelaide",
            310 => "Australia/Brisbane",
            311 => "Australia/Broken_Hill",
            312 => "Australia/Canberra",
            313 => "Australia/Currie",
            314 => "Australia/Darwin",
            315 => "Australia/Eucla",
            316 => "Australia/Hobart",
            317 => "Australia/LHI",
            318 => "Australia/Lindeman",
            319 => "Australia/Lord_Howe",
            320 => "Australia/Melbourne",
            321 => "Australia/North",
            322 => "Australia/NSW",
            323 => "Australia/Perth",
            324 => "Australia/Queensland",
            325 => "Australia/South",
            326 => "Australia/Sydney",
            327 => "Australia/Tasmania",
            328 => "Australia/Victoria",
            329 => "Australia/West",
            330 => "Australia/Yancowinna",
            331 => "Brazil/Acre",
            332 => "Brazil/DeNoronha",
            333 => "Brazil/East",
            334 => "Brazil/West",
            335 => "Canada/Atlantic",
            336 => "Canada/Central",
            337 => "Canada/East-Saskatchewan",
            338 => "Canada/Eastern",
            339 => "Canada/Mountain",
            340 => "Canada/Newfoundland",
            341 => "Canada/Pacific",
            342 => "Canada/Saskatchewan",
            343 => "Canada/Yukon",
            344 => "CET",
            345 => "Chile/Continental",
            346 => "Chile/EasterIsland",
            347 => "CST6CDT",
            348 => "Cuba",
            349 => "EET",
            350 => "Egypt",
            351 => "Eire",
            352 => "EST",
            353 => "EST5EDT",
            354 => "Etc/GMT",
            355 => "Etc/GMT+0",
            356 => "Etc/GMT+1",
            357 => "Etc/GMT+10",
            358 => "Etc/GMT+11",
            359 => "Etc/GMT+12",
            360 => "Etc/GMT+2",
            361 => "Etc/GMT+3",
            362 => "Etc/GMT+4",
            363 => "Etc/GMT+5",
            364 => "Etc/GMT+6",
            365 => "Etc/GMT+7",
            366 => "Etc/GMT+8",
            367 => "Etc/GMT+9",
            368 => "Etc/GMT-0",
            369 => "Etc/GMT-1",
            370 => "Etc/GMT-10",
            371 => "Etc/GMT-11",
            372 => "Etc/GMT-12",
            373 => "Etc/GMT-13",
            374 => "Etc/GMT-14",
            375 => "Etc/GMT-2",
            376 => "Etc/GMT-3",
            377 => "Etc/GMT-4",
            378 => "Etc/GMT-5",
            379 => "Etc/GMT-6",
            380 => "Etc/GMT-7",
            381 => "Etc/GMT-8",
            382 => "Etc/GMT-9",
            383 => "Etc/GMT0",
            384 => "Etc/Greenwich",
            385 => "Etc/UCT",
            386 => "Etc/Universal",
            387 => "Etc/UTC",
            388 => "Etc/Zulu",
            389 => "Europe/Amsterdam",
            390 => "Europe/Andorra",
            391 => "Europe/Athens",
            392 => "Europe/Belfast",
            393 => "Europe/Belgrade",
            394 => "Europe/Berlin",
            395 => "Europe/Bratislava",
            396 => "Europe/Brussels",
            397 => "Europe/Bucharest",
            398 => "Europe/Budapest",
            399 => "Europe/Chisinau",
            400 => "Europe/Copenhagen",
            401 => "Europe/Dublin",
            402 => "Europe/Gibraltar",
            403 => "Europe/Guernsey",
            404 => "Europe/Helsinki",
            405 => "Europe/Isle_of_Man",
            406 => "Europe/Istanbul",
            407 => "Europe/Jersey",
            408 => "Europe/Kaliningrad",
            409 => "Europe/Kiev",
            410 => "Europe/Lisbon",
            411 => "Europe/Ljubljana",
            412 => "Europe/London",
            413 => "Europe/Luxembourg",
            414 => "Europe/Madrid",
            415 => "Europe/Malta",
            416 => "Europe/Mariehamn",
            417 => "Europe/Minsk",
            418 => "Europe/Monaco",
            419 => "Europe/Moscow",
            420 => "Europe/Nicosia",
            421 => "Europe/Oslo",
            422 => "Europe/Paris",
            423 => "Europe/Podgorica",
            424 => "Europe/Prague",
            425 => "Europe/Riga",
            426 => "Europe/Rome",
            427 => "Europe/Samara",
            428 => "Europe/San_Marino",
            429 => "Europe/Sarajevo",
            430 => "Europe/Simferopol",
            431 => "Europe/Skopje",
            432 => "Europe/Sofia",
            433 => "Europe/Stockholm",
            434 => "Europe/Tallinn",
            435 => "Europe/Tirane",
            436 => "Europe/Tiraspol",
            437 => "Europe/Uzhgorod",
            438 => "Europe/Vaduz",
            439 => "Europe/Vatican",
            440 => "Europe/Vienna",
            441 => "Europe/Vilnius",
            442 => "Europe/Volgograd",
            443 => "Europe/Warsaw",
            444 => "Europe/Zagreb",
            445 => "Europe/Zaporozhye",
            446 => "Europe/Zurich",
            447 => "Factory",
            448 => "GB",
            449 => "GB-Eire",
            450 => "GMT",
            451 => "GMT+0",
            452 => "GMT-0",
            453 => "GMT0",
            454 => "Greenwich",
            455 => "Hongkong",
            456 => "HST",
            457 => "Iceland",
            458 => "Indian/Antananarivo",
            459 => "Indian/Chagos",
            460 => "Indian/Christmas",
            461 => "Indian/Cocos",
            462 => "Indian/Comoro",
            463 => "Indian/Kerguelen",
            464 => "Indian/Mahe",
            465 => "Indian/Maldives",
            466 => "Indian/Mauritius",
            467 => "Indian/Mayotte",
            468 => "Indian/Reunion",
            469 => "Iran",
            470 => "Israel",
            471 => "Jamaica",
            472 => "Japan",
            473 => "Kwajalein",
            474 => "Libya",
            475 => "MET",
            476 => "Mexico/BajaNorte",
            477 => "Mexico/BajaSur",
            478 => "Mexico/General",
            479 => "MST",
            480 => "MST7MDT",
            481 => "Navajo",
            482 => "NZ",
            483 => "NZ-CHAT",
            484 => "Pacific/Apia",
            485 => "Pacific/Auckland",
            486 => "Pacific/Chatham",
            487 => "Pacific/Easter",
            488 => "Pacific/Efate",
            489 => "Pacific/Enderbury",
            490 => "Pacific/Fakaofo",
            491 => "Pacific/Fiji",
            492 => "Pacific/Funafuti",
            493 => "Pacific/Galapagos",
            494 => "Pacific/Gambier",
            495 => "Pacific/Guadalcanal",
            496 => "Pacific/Guam",
            497 => "Pacific/Honolulu",
            498 => "Pacific/Johnston",
            499 => "Pacific/Kiritimati",
            500 => "Pacific/Kosrae",
            501 => "Pacific/Kwajalein",
            502 => "Pacific/Majuro",
            503 => "Pacific/Marquesas",
            504 => "Pacific/Midway",
            505 => "Pacific/Nauru",
            506 => "Pacific/Niue",
            507 => "Pacific/Norfolk",
            508 => "Pacific/Noumea",
            509 => "Pacific/Pago_Pago",
            510 => "Pacific/Palau",
            511 => "Pacific/Pitcairn",
            512 => "Pacific/Ponape",
            513 => "Pacific/Port_Moresby",
            514 => "Pacific/Rarotonga",
            515 => "Pacific/Saipan",
            516 => "Pacific/Samoa",
            517 => "Pacific/Tahiti",
            518 => "Pacific/Tarawa",
            519 => "Pacific/Tongatapu",
            520 => "Pacific/Truk",
            521 => "Pacific/Wake",
            522 => "Pacific/Wallis",
            523 => "Pacific/Yap",
            524 => "Poland",
            525 => "Portugal",
            526 => "PRC",
            527 => "PST8PDT",
            528 => "ROC",
            529 => "ROK",
            530 => "Singapore",
            531 => "Turkey",
            532 => "UCT",
            533 => "Universal",
            534 => "US/Alaska",
            535 => "US/Aleutian",
            536 => "US/Arizona",
            537 => "US/Central",
            538 => "US/East-Indiana",
            539 => "US/Eastern",
            540 => "US/Hawaii",
            541 => "US/Indiana-Starke",
            542 => "US/Michigan",
            543 => "US/Mountain",
            544 => "US/Pacific",
            545 => "US/Pacific-New",
            546 => "US/Samoa",
            547 => "UTC",
            548 => "W-SU",
            549 => "WET",
            550 => "Zulu");
    }
}

function configmanager_select_timezone_helper()
{
    $all = timezone_identifiers_list();

    foreach($all AS $zone) {
        $zones[$zone] = $zone;
    }
    return $zones;
}



/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_path_html_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_path_log_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_path_language_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_backup_path_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_path_data_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_path_images_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_path_pear_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}

/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_path_themes_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] != '/' ) {
        return $value . '/';
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_site_url_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] == '/' ) {
        return (substr($value,0,strlen($value)-1));
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_site_admin_url_validate($value)
{
    $value = trim($value);
    if ( $value[strlen($value)-1] == '/' ) {
        return (substr($value,0,strlen($value)-1));
    }
    return $value;
}
/**
* Validate function: Validate input
*
* @return   string      validated ata
*
*/

function configmanager_rdf_file_validate($value)
{
    $value = trim($value);
    return $value;
}

$tokenstate = SEC_checkToken();

// MAIN
if (array_key_exists('set_action', $_POST) && $tokenstate){
    if (SEC_inGroup('Root')) {
        if ($_POST['set_action'] == 'restore') {
            $config->restore_param($_POST['name'], $conf_group);
        } elseif ($_POST['set_action'] == 'unset') {
            $config->unset_param($_POST['name'], $conf_group);
        }
    }
}

if (array_key_exists('form_submit', $_POST) && $tokenstate) {
    $result = null;
    if (! array_key_exists('form_reset', $_POST)) {
        $result = $config->updateConfig($_POST, $conf_group);
        CTL_clearCache();
        /*
         * An ugly hack to get the proper theme selected
         */
        if( $_CONF['allow_user_themes'] == 1 )
        {
            if( isset( $_COOKIE[$_CONF['cookie_theme']] ) && empty( $_USER['theme'] ))
            {
                $theme = $inputHandler->getVar('filename',$_CONF['cookie_theme'],'cookie','');
//                $theme = COM_sanitizeFilename($_COOKIE[$_CONF['cookie_theme']], true);
                if( is_dir( $_CONF['path_themes'] . $theme ))
                {
                    $_USER['theme'] = $theme;
                }
            }

            if( !empty( $_USER['theme'] ))
            {
                if( is_dir( $_CONF['path_themes'] . $_USER['theme'] ))
                {
                    $_CONF['theme'] = $_USER['theme'];
                    $_CONF['path_layout'] = $_CONF['path_themes'] . $_CONF['theme'] . '/';
                    $_CONF['layout_url'] = $_CONF['site_url'] . '/layout/' . $_CONF['theme'];
                }
                else
                {
                    $_USER['theme'] = $_CONF['theme'];
                }
            }
        }
    }
    echo $config->get_ui($conf_group, $_POST['sub_group'], $result);
} else {
    echo $config->get_ui($conf_group, array_key_exists('subgroup', $_POST) ?
                         $_POST['subgroup'] : null);
}

?>