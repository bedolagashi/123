<?php


require_once '../PHPMailerAutoload.php';


class PHPMailerLangTest extends PHPUnit_Framework_TestCase
{
    
    public $Mail;

    
    public $INCLUDE_DIR = '../';

    
    public function setUp()
    {
        $this->Mail = new PHPMailer;
    }

    
    public function testTranslations()
    {
        $this->Mail->setLanguage('en');
        $definedStrings = $this->Mail->getTranslations();
        $err = '';
        foreach (new DirectoryIterator('../language') as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            $matches = array();
           
            if (preg_match('/^phpmailer\.lang-([a-z_]{2,})\.php$/', $fileInfo->getFilename(), $matches)) {
                $lang = $matches[1]; 
                $PHPMAILER_LANG = array(); 
                include $fileInfo->getPathname(); 
                $missing = array_diff(array_keys($definedStrings), array_keys($PHPMAILER_LANG));
                $extra = array_diff(array_keys($PHPMAILER_LANG), array_keys($definedStrings));
                if (!empty($missing)) {
                    $err .= "\nMissing translations in $lang: " . implode(', ', $missing);
                }
                if (!empty($extra)) {
                    $err .= "\nExtra translations in $lang: " . implode(', ', $extra);
                }
            }
        }
        $this->assertEmpty($err, $err);
    }
}
