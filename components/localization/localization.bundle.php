<?php

namespace Bundle;

use Core\Template;
use Core\Response;

class Localization extends DBObject implements DBTableObject {

    public function getId(){
        return $this->getField('id');
    }

    public function getCode(){
        return $this->getField('code');
    }

    public function setCode($code){
        $this->setField('code', $code);
    }

    public function setLocaleString($langCode, $string){
        $this->setField($langCode, $string);
    }

    public static function GetLocales($language, array $codes){
        $locales = [];
        foreach($codes as $code){
            $locales[] = "'$code'";
        }
        if(count($locales) > 0){
            $locales = implode(',', $locales);
            $locales = self::Select([
                'code' => "IN ($locales)"
            ]);
            return self::SerializeSet($locales, 'code');
        } else return [];
    }

    public static function GetLocaleString($language, $code){
        $str = self::Select(['code' => $code], 1);
        return $str->getField($language);
    }

    private static $fields = [
        'id' => TYPE_DB_NUMERIC | TYPE_DB_PRIMARY,
        'code' => TYPE_DB_TEXT,
    ];

    private static $defaultLanguage = DEFAULT_LANGUAGE;

    private static $supported = [
        'af' => 'af', //Afrikaans
        'sq' => 'sq', //Albanian
        'am' => 'am', //Amharic
        'ar' => 'ar-ae', //Arabic - United Arab Emirates
        'hy' => 'hy', //Armenian
        'as' => 'as', //Assamese
        'az' => 'az-az', //Azeri
        'eu' => 'eu', //Basque
        'be' => 'be', //Belarussian
        'bn' => 'bn', //Bengali
        'bs' => 'bs', //Bosnian
        'bg' => 'bg', //Bulgarian
        'my' => 'my', //Burmese
        'ca' => 'ca', //Catalan
        'zh' => 'zh-cn', //Chinese
        'hr' => 'hr', //Croatian
        'cs' => 'cs', //Czech
        'da' => 'da', //Danish
        'dv' => 'dv', //Dhivehi - Maldivan
        'nl' => 'nl-nl', //Dutch - Netherlands
        'en' => 'en-us', //English - US
        'et' => 'et', //Estonian
        'mk' => 'mk', //Makedonian
        'fo' => 'fo', //Faroese
        'fa' => 'fa', //Farsi - Persian
        'fi' => 'fi', //Finnish
        'fr' => 'fr-fr', //French
        'gd' => 'gd', //Gaelic - Scotland
        'gl' => 'gl', //Galician
        'ka' => 'ka', //Georgian
        'de' => 'de-de', //German
        'el' => 'el', //Greek
        'gn' => 'gn', //Guarani - Paraguay
        'gu' => 'gu', //Gujarati
        'he' => 'he', //Hebrew
        'hi' => 'hi', //Hindi
        'hu' => 'hu', //Hungarian
        'is' => 'is', //Icelandic
        'id' => 'id', //Indonesian
        'it' => 'it-it', //Italian - Italy
        'ja' => 'ja', //Japanese
        'kn' => 'kn', //Kannada
        'ks' => 'ks', //Kashmiri
        'kk' => 'kk', //Kazakh
        'km' => 'km', //Khmer
        'ko' => 'ko', //Korean
        'lo' => 'lo', //Lao
        'la' => 'la', //Latin
        'lv' => 'lv', //Latvian
        'lt' => 'lt', //Lithuanian
        'ms' => 'ms-my', //Malay - Malaysia
        'ml' => 'ml', //Malaylam
        'mt' => 'mt', //Maltese
        'mi' => 'mi', //Maori
        'mr' => 'mr', //Marathi
        'mn' => 'mn', //Mongolian
        'ne' => 'ne', //Nepali
        'nb' => 'no-no', //Norwegian - Bokml
        'nn' => 'no-no', //Norwegian - Nynorsk
        'or' => 'or', //Oriya
        'pl' => 'pl', //Polish
        'pt' => 'pt', //Portugese - Portugal
        'pa' => 'pa', //Punjabi
        'rm' => 'rm', //Raeto-Romance
        'ro' => 'ro', //Romanian
        'ru' => 'ru-ru',
        'sa' => 'sa', //Sanskrit
        'sr' => 'sr-sp', //Serbian
        'tn' => 'tn', //Setsuana
        'sd' => 'sd', //Sindhi
        'si' => 'si', //Sinhala
        'sk' => 'sk', //Slovak
        'sl' => 'sl', //Slovenian
        'so' => 'so', //Somali
        'sb' => 'sb', //Sorbian
        'es' => 'es-es', //Spanish - Traditional
        'sw' => 'sw', //Swahili
        'sv' => 'sv-se', //Swedish - Sweden
        'tg' => 'tg', //Tajik
        'ta' => 'ta', //Tamil
        'tt' => 'tt', //Tatar
        'te' => 'te', //Telugu
        'th' => 'th', //Thai
        'bo' => 'bo', //Tibetan
        'ts' => 'ts', //Tsonga
        'tr' => 'tr', //Turkish
        'tk' => 'tk', //Turkmen
        'uk' => 'uk', //Ukrainian
        'ur' => 'ur', //Urdu
        'uz' => 'uz-uz', //Uzbek
        'vi' => 'vi', //Vietnamese
        'cy' => 'cy', //Welsh
        'xh' => 'xh', //Xhosa
        'yi' => 'yi', //Yiddish
        'zu' => 'zu', //Zulu
    ];

    public static function SupportedLanguages(){
        return self::$supported;
    }

    public static function GetLanguageCode($language){
        if(isset(self::$supported[$language]))
            return self::$supported[$language];
        else
            throw new \Exception('Language "'.$language.'" has no appropriate language code set so not supported');
    }

    public static function ClientLanguage($set = null){
        if(!$set){
            @$lang = $_SESSION['client_language'];
            if($lang) return $lang;
            else {
                var_dump($_SESSION['client_language']);
                $_SESSION['client_language'] = DEFAULT_LANGUAGE;
                return DEFAULT_LANGUAGE;
            }
        }
        $_SESSION['client_language'] = $set;
        return $set;
    }

    public static function Preset($language){
        if(!isset(self::$supported[$language])){
            $language = DEFAULT_LANGUAGE;
        }
        if(!isset(self::$fields[$language])){
            self::$fields[$language] = TYPE_DB_TEXT;
        }
        self::$defaultLanguage = $language;
    }

    public static function Localize($content){
        $language = Template::GetLanguage();
        self::Preset($language);
        self::ClientLanguage(Template::GetLanguage());
        Template::SetGlobal('lang', $language);
        Response::Language(self::GetLanguageCode($language));
        $codes = [];
        preg_match_all('/\{\@[A-Za-z0-9_]+\}/', $content, $codes);
        $codes = $codes[0];
        foreach($codes as $idx=>$code){
            $codes[$idx] = str_replace('{@', '', str_replace('}', '', $code));
        }
        $locales = self::GetLocales(self::$defaultLanguage, $codes);
        $replace = [];
        foreach($locales as $idx=>$locale){
            @$replace['@'.$idx] = $locale[self::$defaultLanguage];
        }
        return Template::Replace($replace, $content);
    }

    public function __construct($obj = null){
        parent::__construct(self::$fields, 'locale', $obj);
    }

    public static function Init(){
        self::__init(self::$fields, 'locale');
    }

    public static function Count($rules = []){
        return parent::__selectCount($rules, 'locale');
    }

    public static function Delete($rules){
        parent::__delete($rules, 'locale');
    }

    public static function Select($rules, $count = null, $start = 0){
        return parent::__select($rules, 'locale', Localization::class, $count, $start);
    }

}