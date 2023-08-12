<?php


require_once realpath('../PHPMailerAutoload.php');


class PHPMailerTest extends PHPUnit_Framework_TestCase
{
    
    public $Mail;

    
    public $Host = '';

    
    public $ChangeLog = array();

    
    public $NoteLog = array();

    
    private $pids = array();

    
    const INCLUDE_DIR = '../';

    
    public function setUp()
    {
        if (file_exists('testbootstrap.php')) {
            include 'testbootstrap.php'; 
        }
        $this->Mail = new PHPMailer;
        $this->Mail->SMTPDebug = 3; 
        $this->Mail->Priority = 3;
        $this->Mail->Encoding = '8bit';
        $this->Mail->CharSet = 'iso-8859-1';
        if (array_key_exists('mail_from', $_REQUEST)) {
            $this->Mail->From = $_REQUEST['mail_from'];
        } else {
            $this->Mail->From = 'unit_test@phpmailer.example.com';
        }
        $this->Mail->FromName = 'Unit Tester';
        $this->Mail->Sender = '';
        $this->Mail->Subject = 'Unit Test';
        $this->Mail->Body = '';
        $this->Mail->AltBody = '';
        $this->Mail->WordWrap = 0;
        if (array_key_exists('mail_host', $_REQUEST)) {
            $this->Mail->Host = $_REQUEST['mail_host'];
        } else {
            $this->Mail->Host = 'mail.example.com';
        }
        if (array_key_exists('mail_port', $_REQUEST)) {
            $this->Mail->Port = $_REQUEST['mail_port'];
        } else {
            $this->Mail->Port = 25;
        }
        $this->Mail->Helo = 'localhost.localdomain';
        $this->Mail->SMTPAuth = false;
        $this->Mail->Username = '';
        $this->Mail->Password = '';
        $this->Mail->addReplyTo('no_reply@phpmailer.example.com', 'Reply Guy');
        $this->Mail->Sender = 'unit_test@phpmailer.example.com';
        if (strlen($this->Mail->Host) > 0) {
            $this->Mail->Mailer = 'smtp';
        } else {
            $this->Mail->Mailer = 'mail';
        }
        if (array_key_exists('mail_to', $_REQUEST)) {
            $this->setAddress($_REQUEST['mail_to'], 'Test User', 'to');
        }
        if (array_key_exists('mail_cc', $_REQUEST) and strlen($_REQUEST['mail_cc']) > 0) {
            $this->setAddress($_REQUEST['mail_cc'], 'Carbon User', 'cc');
        }
    }

    
    public function tearDown()
    {
        
        $this->Mail = null;
        $this->ChangeLog = array();
        $this->NoteLog = array();

        foreach ($this->pids as $pid) {
            $p = escapeshellarg($pid);
            shell_exec("ps $p && kill -TERM $p");
        }
    }


    
    public function buildBody()
    {
        $this->checkChanges();

        
        if ($this->Mail->ContentType == 'text/html' || strlen($this->Mail->AltBody) > 0) {
            $eol = "<br>\r\n";
            $bullet_start = '<li>';
            $bullet_end = "</li>\r\n";
            $list_start = "<ul>\r\n";
            $list_end = "</ul>\r\n";
        } else {
            $eol = "\r\n";
            $bullet_start = ' - ';
            $bullet_end = "\r\n";
            $list_start = '';
            $list_end = '';
        }

        $ReportBody = '';

        $ReportBody .= '---------------------' . $eol;
        $ReportBody .= 'Unit Test Information' . $eol;
        $ReportBody .= '---------------------' . $eol;
        $ReportBody .= 'phpmailer version: ' . $this->Mail->Version . $eol;
        $ReportBody .= 'Content Type: ' . $this->Mail->ContentType . $eol;
        $ReportBody .= 'CharSet: ' . $this->Mail->CharSet . $eol;

        if (strlen($this->Mail->Host) > 0) {
            $ReportBody .= 'Host: ' . $this->Mail->Host . $eol;
        }

        
        $attachments = $this->Mail->getAttachments();
        if (count($attachments) > 0) {
            $ReportBody .= 'Attachments:' . $eol;
            $ReportBody .= $list_start;
            foreach ($attachments as $attachment) {
                $ReportBody .= $bullet_start . 'Name: ' . $attachment[1] . ', ';
                $ReportBody .= 'Encoding: ' . $attachment[3] . ', ';
                $ReportBody .= 'Type: ' . $attachment[4] . $bullet_end;
            }
            $ReportBody .= $list_end . $eol;
        }

        
        if (count($this->ChangeLog) > 0) {
            $ReportBody .= 'Changes' . $eol;
            $ReportBody .= '-------' . $eol;

            $ReportBody .= $list_start;
            for ($i = 0; $i < count($this->ChangeLog); $i++) {
                $ReportBody .= $bullet_start . $this->ChangeLog[$i][0] . ' was changed to [' .
                    $this->ChangeLog[$i][1] . ']' . $bullet_end;
            }
            $ReportBody .= $list_end . $eol . $eol;
        }

        
        if (count($this->NoteLog) > 0) {
            $ReportBody .= 'Notes' . $eol;
            $ReportBody .= '-----' . $eol;

            $ReportBody .= $list_start;
            for ($i = 0; $i < count($this->NoteLog); $i++) {
                $ReportBody .= $bullet_start . $this->NoteLog[$i] . $bullet_end;
            }
            $ReportBody .= $list_end;
        }

        
        $this->Mail->Body .= $eol . $ReportBody;
    }

    
    public function checkChanges()
    {
        if ($this->Mail->Priority != 3) {
            $this->addChange('Priority', $this->Mail->Priority);
        }
        if ($this->Mail->Encoding != '8bit') {
            $this->addChange('Encoding', $this->Mail->Encoding);
        }
        if ($this->Mail->CharSet != 'iso-8859-1') {
            $this->addChange('CharSet', $this->Mail->CharSet);
        }
        if ($this->Mail->Sender != '') {
            $this->addChange('Sender', $this->Mail->Sender);
        }
        if ($this->Mail->WordWrap != 0) {
            $this->addChange('WordWrap', $this->Mail->WordWrap);
        }
        if ($this->Mail->Mailer != 'mail') {
            $this->addChange('Mailer', $this->Mail->Mailer);
        }
        if ($this->Mail->Port != 25) {
            $this->addChange('Port', $this->Mail->Port);
        }
        if ($this->Mail->Helo != 'localhost.localdomain') {
            $this->addChange('Helo', $this->Mail->Helo);
        }
        if ($this->Mail->SMTPAuth) {
            $this->addChange('SMTPAuth', 'true');
        }
    }

    
    public function addChange($sName, $sNewValue)
    {
        $this->ChangeLog[] = array($sName, $sNewValue);
    }

    
    public function addNote($sValue)
    {
        $this->NoteLog[] = $sValue;
    }

    
    public function setAddress($sAddress, $sName = '', $sType = 'to')
    {
        switch ($sType) {
            case 'to':
                return $this->Mail->addAddress($sAddress, $sName);
            case 'cc':
                return $this->Mail->addCC($sAddress, $sName);
            case 'bcc':
                return $this->Mail->addBCC($sAddress, $sName);
        }
        return false;
    }

    
    public function testBootstrap()
    {
        $this->assertTrue(
            file_exists('testbootstrap.php'),
            'Test config params missing - copy testbootstrap.php to testbootstrap-dist.php and change as appropriate'
        );
    }

    
    public function testAuthCRAMMD5()
    {
        $this->Mail->Host = 'hostname';
        $this->Mail->Port = 587;
        $this->Mail->SMTPAuth = true;
        $this->Mail->SMTPSecure = 'tls';
        $this->Mail->AuthType = 'CRAM-MD5';
        $this->Mail->Username = 'username';
        $this->Mail->Password = 'password';
        $this->Mail->Body = 'Test body';
        $this->Mail->Subject .= ': Auth CRAM-MD5';
        $this->Mail->From = 'from@example.com';
        $this->Mail->Sender = 'from@example.com';
        $this->Mail->clearAllRecipients();
        $this->Mail->addAddress('user@example.com');
        
    }

    
    public function testValidate()
    {
        $validaddresses = array(
            'first@iana.org',
            'first.last@iana.org',
            '1234567890123456789012345678901234567890123456789012345678901234@iana.org',
            '"first\"last"@iana.org',
            '"first@last"@iana.org',
            '"first\last"@iana.org',
            'first.last@[12.34.56.78]',
            'first.last@[IPv6:::12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333::4444:12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:12.34.56.78]',
            'first.last@[IPv6:::1111:2222:3333:4444:5555:6666]',
            'first.last@[IPv6:1111:2222:3333::4444:5555:6666]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666::]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777:8888]',
            'first.last@x23456789012345678901234567890123456789012345678901234567890123.iana.org',
            'first.last@3com.com',
            'first.last@123.iana.org',
            '"first\last"@iana.org',
            'first.last@[IPv6:1111:2222:3333::4444:5555:12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333::4444:5555:6666:7777]',
            'first.last@example.123',
            'first.last@com',
            '"Abc\@def"@iana.org',
            '"Fred\ Bloggs"@iana.org',
            '"Joe.\Blow"@iana.org',
            '"Abc@def"@iana.org',
            'user+mailbox@iana.org',
            'customer/department=shipping@iana.org',
            '$A12345@iana.org',
            '!def!xyz%abc@iana.org',
            '_somename@iana.org',
            'dclo@us.ibm.com',
            'peter.piper@iana.org',
            '"Doug \"Ace\" L."@iana.org',
            'test@iana.org',
            'TEST@iana.org',
            '1234567890@iana.org',
            'test+test@iana.org',
            'test-test@iana.org',
            't*est@iana.org',
            '+1~1+@iana.org',
            '{_test_}@iana.org',
            '"[[ test ]]"@iana.org',
            'test.test@iana.org',
            '"test.test"@iana.org',
            'test."test"@iana.org',
            '"test@test"@iana.org',
            'test@123.123.123.x123',
            'test@123.123.123.123',
            'test@[123.123.123.123]',
            'test@example.iana.org',
            'test@example.example.iana.org',
            '"test\test"@iana.org',
            'test@example',
            '"test\blah"@iana.org',
            '"test\blah"@iana.org',
            '"test\"blah"@iana.org',
            'customer/department@iana.org',
            '_Yosemite.Sam@iana.org',
            '~@iana.org',
            '"Austin@Powers"@iana.org',
            'Ima.Fool@iana.org',
            '"Ima.Fool"@iana.org',
            '"Ima Fool"@iana.org',
            '"first"."last"@iana.org',
            '"first".middle."last"@iana.org',
            '"first".last@iana.org',
            'first."last"@iana.org',
            '"first"."middle"."last"@iana.org',
            '"first.middle"."last"@iana.org',
            '"first.middle.last"@iana.org',
            '"first..last"@iana.org',
            '"first\"last"@iana.org',
            'first."mid\dle"."last"@iana.org',
            '"test blah"@iana.org',
            '(foo)cal(bar)@(baz)iamcal.com(quux)',
            'cal@iamcal(woo).(yay)com',
            'cal(woo(yay)hoopla)@iamcal.com',
            'cal(foo\@bar)@iamcal.com',
            'cal(foo\)bar)@iamcal.com',
            'first().last@iana.org',
            'pete(his account)@silly.test(his host)',
            'c@(Chris\'s host.)public.example',
            'jdoe@machine(comment). example',
            '1234 @ local(blah) .machine .example',
            'first(abc.def).last@iana.org',
            'first(a"bc.def).last@iana.org',
            'first.(")middle.last(")@iana.org',
            'first(abc\(def)@iana.org',
            'first.last@x(1234567890123456789012345678901234567890123456789012345678901234567890).com',
            'a(a(b(c)d(e(f))g)h(i)j)@iana.org',
            'name.lastname@domain.com',
            'a@b',
            'a@bar.com',
            'aaa@[123.123.123.123]',
            'a@bar',
            'a-b@bar.com',
            '+@b.c',
            '+@b.com',
            'a@b.co-foo.uk',
            '"hello my name is"@stutter.com',
            '"Test \"Fail\" Ing"@iana.org',
            'valid@about.museum',
            'shaitan@my-domain.thisisminekthx',
            'foobar@192.168.0.1',
            '"Joe\Blow"@iana.org',
            'HM2Kinsists@(that comments are allowed)this.is.ok',
            'user%uucp!path@berkeley.edu',
            'first.last @iana.org',
            'cdburgess+!#$%&\'*-/=?+_{}|~test@gmail.com',
            'first.last@[IPv6:::a2:a3:a4:b1:b2:b3:b4]',
            'first.last@[IPv6:a1:a2:a3:a4:b1:b2:b3::]',
            'first.last@[IPv6:::]',
            'first.last@[IPv6:::b4]',
            'first.last@[IPv6:::b3:b4]',
            'first.last@[IPv6:a1::b4]',
            'first.last@[IPv6:a1::]',
            'first.last@[IPv6:a1:a2::]',
            'first.last@[IPv6:0123:4567:89ab:cdef::]',
            'first.last@[IPv6:0123:4567:89ab:CDEF::]',
            'first.last@[IPv6:::a3:a4:b1:ffff:11.22.33.44]',
            'first.last@[IPv6:::a2:a3:a4:b1:ffff:11.22.33.44]',
            'first.last@[IPv6:a1:a2:a3:a4::11.22.33.44]',
            'first.last@[IPv6:a1:a2:a3:a4:b1::11.22.33.44]',
            'first.last@[IPv6:a1::11.22.33.44]',
            'first.last@[IPv6:a1:a2::11.22.33.44]',
            'first.last@[IPv6:0123:4567:89ab:cdef::11.22.33.44]',
            'first.last@[IPv6:0123:4567:89ab:CDEF::11.22.33.44]',
            'first.last@[IPv6:a1::b2:11.22.33.44]',
            'test@test.com',
            'test@xn--example.com',
            'test@example.com'
        );
        $invalidaddresses = array(
            'first.last@sub.do,com',
            'first\@last@iana.org',
            '123456789012345678901234567890123456789012345678901234567890' .
                '@12345678901234567890123456789012345678901234 [...]',
            'first.last',
            '12345678901234567890123456789012345678901234567890123456789012345@iana.org',
            '.first.last@iana.org',
            'first.last.@iana.org',
            'first..last@iana.org',
            '"first"last"@iana.org',
            '"""@iana.org',
            '"\"@iana.org',
            
            'first\@last@iana.org',
            'first.last@',
            'x@x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.' .
                'x23456789.x23456789.x23456789.x23 [...]',
            'first.last@[.12.34.56.78]',
            'first.last@[12.34.56.789]',
            'first.last@[::12.34.56.78]',
            'first.last@[IPv5:::12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777:12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777:8888:9999]',
            'first.last@[IPv6:1111:2222::3333::4444:5555:6666]',
            'first.last@[IPv6:1111:2222:333x::4444:5555]',
            'first.last@[IPv6:1111:2222:33333::4444:5555]',
            'first.last@-xample.com',
            'first.last@exampl-.com',
            'first.last@x234567890123456789012345678901234567890123456789012345678901234.iana.org',
            'abc\@def@iana.org',
            'abc\@iana.org',
            'Doug\ \"Ace\"\ Lovell@iana.org',
            'abc@def@iana.org',
            'abc\@def@iana.org',
            'abc\@iana.org',
            '@iana.org',
            'doug@',
            '"qu@iana.org',
            'ote"@iana.org',
            '.dot@iana.org',
            'dot.@iana.org',
            'two..dot@iana.org',
            '"Doug "Ace" L."@iana.org',
            'Doug\ \"Ace\"\ L\.@iana.org',
            'hello world@iana.org',
            
            'gatsby@f.sc.ot.t.f.i.tzg.era.l.d.',
            'test.iana.org',
            'test.@iana.org',
            'test..test@iana.org',
            '.test@iana.org',
            'test@test@iana.org',
            'test@@iana.org',
            '-- test --@iana.org',
            '[test]@iana.org',
            '"test"test"@iana.org',
            '()[]\;:,><@iana.org',
            'test@.',
            'test@example.',
            'test@.org',
            'test@12345678901234567890123456789012345678901234567890123456789012345678901234567890' .
                '12345678901234567890 [...]',
            'test@[123.123.123.123',
            'test@123.123.123.123]',
            'NotAnEmail',
            '@NotAnEmail',
            '"test"blah"@iana.org',
            '.wooly@iana.org',
            'wo..oly@iana.org',
            'pootietang.@iana.org',
            '.@iana.org',
            'Ima Fool@iana.org',
            'phil.h\@\@ck@haacked.com',
            'foo@[\1.2.3.4]',
            
            'first\last@iana.org',
            'Abc\@def@iana.org',
            'Fred\ Bloggs@iana.org',
            'Joe.\Blow@iana.org',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:12.34.567.89]',
            '{^c\@**Dog^}@cartoon.com',
            
            'cal(foo(bar)@iamcal.com',
            'cal(foo)bar)@iamcal.com',
            'cal(foo\)@iamcal.com',
            'first(12345678901234567890123456789012345678901234567890)last@(1234567890123456789' .
                '01234567890123456789012 [...]',
            'first(middle)last@iana.org',
            'first(abc("def".ghi).mno)middle(abc("def".ghi).mno).last@(abc("def".ghi).mno)example' .
                '(abc("def".ghi).mno). [...]',
            'a(a(b(c)d(e(f))g)(h(i)j)@iana.org',
            '.@',
            '@bar.com',
            '@@bar.com',
            'aaa.com',
            'aaa@.com',
            'aaa@.123',
            'aaa@[123.123.123.123]a',
            'aaa@[123.123.123.333]',
            'a@bar.com.',
            'a@-b.com',
            'a@b-.com',
            '-@..com',
            '-@a..com',
            'invalid@about.museum-',
            'test@...........com',
            '"Unicode NULL' . chr(0) . '"@char.com',
            'Unicode NULL' . chr(0) . '@char.com',
            'first.last@[IPv6::]',
            'first.last@[IPv6::::]',
            'first.last@[IPv6::b4]',
            'first.last@[IPv6::::b4]',
            'first.last@[IPv6::b3:b4]',
            'first.last@[IPv6::::b3:b4]',
            'first.last@[IPv6:a1:::b4]',
            'first.last@[IPv6:a1:]',
            'first.last@[IPv6:a1:::]',
            'first.last@[IPv6:a1:a2:]',
            'first.last@[IPv6:a1:a2:::]',
            'first.last@[IPv6::11.22.33.44]',
            'first.last@[IPv6::::11.22.33.44]',
            'first.last@[IPv6:a1:11.22.33.44]',
            'first.last@[IPv6:a1:::11.22.33.44]',
            'first.last@[IPv6:a1:a2:::11.22.33.44]',
            'first.last@[IPv6:0123:4567:89ab:cdef::11.22.33.xx]',
            'first.last@[IPv6:0123:4567:89ab:CDEFF::11.22.33.44]',
            'first.last@[IPv6:a1::a4:b1::b4:11.22.33.44]',
            'first.last@[IPv6:a1::11.22.33]',
            'first.last@[IPv6:a1::11.22.33.44.55]',
            'first.last@[IPv6:a1::b211.22.33.44]',
            'first.last@[IPv6:a1::b2::11.22.33.44]',
            'first.last@[IPv6:a1::b3:]',
            'first.last@[IPv6::a2::b4]',
            'first.last@[IPv6:a1:a2:a3:a4:b1:b2:b3:]',
            'first.last@[IPv6::a2:a3:a4:b1:b2:b3:b4]',
            'first.last@[IPv6:a1:a2:a3:a4::b1:b2:b3:b4]',
            
            "(\r\n RCPT TO:user@example.com\r\n DATA \\\nSubject: spam10\\\n\r\n Hello,\r\n".
                " this is a spam mail.\\\n.\r\n QUIT\r\n ) a@example.net"
        );
        
        $unicodeaddresses = array(
            'first.last@bücher.ch',
            'first.last@кто.рф',
            'first.last@phplíst.com',
        );
        $asciiaddresses = array(
            'first.last@xn--bcher-kva.ch',
            'first.last@xn--j1ail.xn--p1ai',
            'first.last@xn--phplst-6va.com',
        );
        $goodfails = array();
        foreach (array_merge($validaddresses, $asciiaddresses) as $address) {
            if (!PHPMailer::validateAddress($address)) {
                $goodfails[] = $address;
            }
        }
        $badpasses = array();
        foreach (array_merge($invalidaddresses, $unicodeaddresses) as $address) {
            if (PHPMailer::validateAddress($address)) {
                $badpasses[] = $address;
            }
        }
        $err = '';
        if (count($goodfails) > 0) {
            $err .= "Good addresses that failed validation:\n";
            $err .= implode("\n", $goodfails);
        }
        if (count($badpasses) > 0) {
            if (!empty($err)) {
                $err .= "\n\n";
            }
            $err .= "Bad addresses that passed validation:\n";
            $err .= implode("\n", $badpasses);
        }
        $this->assertEmpty($err, $err);
        
        $this->assertTrue(PHPMailer::validateAddress('test@example.com', 'auto'));
        $this->assertFalse(PHPMailer::validateAddress('test@example.com.', 'auto'));
        $this->assertTrue(PHPMailer::validateAddress('test@example.com', 'pcre'));
        $this->assertFalse(PHPMailer::validateAddress('test@example.com.', 'pcre'));
        $this->assertTrue(PHPMailer::validateAddress('test@example.com', 'pcre8'));
        $this->assertFalse(PHPMailer::validateAddress('test@example.com.', 'pcre8'));
        $this->assertTrue(PHPMailer::validateAddress('test@example.com', 'php'));
        $this->assertFalse(PHPMailer::validateAddress('test@example.com.', 'php'));
        $this->assertTrue(PHPMailer::validateAddress('test@example.com', 'noregex'));
        $this->assertFalse(PHPMailer::validateAddress('bad', 'noregex'));
    }

    
    public function testCustomValidator()
    {
        
        $this->assertTrue(
            PHPMailer::validateAddress(
                'user@example.com',
                function ($address) {
                    return (strpos($address, '@') !== false);
                }
            ),
            'Custom validator false negative'
        );
        $this->assertFalse(
            PHPMailer::validateAddress(
                'userexample.com',
                function ($address) {
                    return (strpos($address, '@') !== false);
                }
            ),
            'Custom validator false positive'
        );
        
        PHPMailer::$validator = function ($address) {
            return ('user@example.com' === $address);
        };
        $this->assertTrue(
            $this->Mail->addAddress('user@example.com'),
            'Custom default validator false negative'
        );
        $this->assertFalse(
            
            $this->Mail->addAddress('bananas@example.com'),
            'Custom default validator false positive'
        );
        
        PHPMailer::$validator = 'php';
        $this->assertFalse(
            
            $this->Mail->addAddress('first.last@example.123'),
            'PHP validator not behaving as expected'
        );
    }

    
    public function testWordWrap()
    {
        $this->Mail->WordWrap = 40;
        $my_body = str_repeat(
            'Here is the main body of this message.  It should ' .
            'be quite a few lines.  It should be wrapped at ' .
            '40 characters.  Make sure that it is. ',
            10
        );
        $nBodyLen = strlen($my_body);
        $my_body .= "\n\nThis is the above body length: " . $nBodyLen;

        $this->Mail->Body = $my_body;
        $this->Mail->Subject .= ': Wordwrap';

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    
    public function testWordWrapMultibyte()
    {
        $this->Mail->WordWrap = 40;
        $my_body = str_repeat(
            '飛兒樂 團光茫 飛兒樂 團光茫 飛兒樂 團光茫 飛兒樂 團光茫 ' .
            '飛飛兒樂 團光茫兒樂 團光茫飛兒樂 團光飛兒樂 團光茫飛兒樂 團光茫兒樂 團光茫 ' .
            '飛兒樂 團光茫飛兒樂 團飛兒樂 團光茫光茫飛兒樂 團光茫. ',
            10
        );
        $nBodyLen = strlen($my_body);
        $my_body .= "\n\nThis is the above body length: " . $nBodyLen;

        $this->Mail->Body = $my_body;
        $this->Mail->Subject .= ': Wordwrap multibyte';

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    
    public function testLowPriority()
    {
        $this->Mail->Priority = 5;
        $this->Mail->Body = 'Here is the main body.  There should be ' .
            'a reply to address in this message.';
        $this->Mail->Subject .= ': Low Priority';
        $this->Mail->addReplyTo('nobody@nobody.com', 'Nobody (Unit Test)');

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    
    public function testMultiplePlainFileAttachment()
    {
        $this->Mail->Body = 'Here is the text body';
        $this->Mail->Subject .= ': Plain + Multiple FileAttachments';

        if (!$this->Mail->addAttachment(realpath(self::INCLUDE_DIR . 'examples/images/phpmailer.png'))) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        if (!$this->Mail->addAttachment(__FILE__, 'test.txt')) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    
    public function testPlainStringAttachment()
    {
        $this->Mail->Body = 'Here is the text body';
        $this->Mail->Subject .= ': Plain + StringAttachment';

        $sAttachment = 'These characters are the content of the ' .
            "string attachment.\nThis might be taken from a " .
            'database or some other such thing. ';

        $this->Mail->addStringAttachment($sAttachment, 'string_attach.txt');

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    
    public function testQuotedPrintable()
    {
        $this->Mail->Body = 'Here is the main body';
        $this->Mail->Subject .= ': Plain + Quoted-printable';
        $this->Mail->Encoding = 'quoted-printable';

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);

        
        $t = file_get_contents(__FILE__); 
        
        $t = str_replace(array("\r\n", "\r"), "\n", $t);
        $this->assertEquals(
            $t,
            quoted_printable_decode($this->Mail->encodeQP($t)),
            'Quoted-Printable encoding round-trip failed'
        );
        $this->assertEquals(
            $this->Mail->encodeQP($t),
            $this->Mail->encodeQPphp($t),
            'Quoted-Printable BC wrapper failed'
        );
        
        $t = str_replace("\n", "\r\n", $t);
        $this->assertEquals(
            $t,
            quoted_printable_decode($this->Mail->encodeQP($t)),
            'Quoted-Printable encoding round-trip failed (Windows line breaks)'
        );
    }

    
    public function testHtml()
    {
        $this->Mail->isHTML(true);
        $this->Mail->Subject .= ": HTML only";

        $this->Mail->Body = <<<EOT
<html>
    <head>
        <title>HTML email test</title>
    </head>
    <body>
        <h1>PHPMailer does HTML!</h1>
        <p>This is a <strong>test message</strong> written in HTML.<br>
        Go to <a href="https://github.com/PHPMailer/PHPMailer/">https://github.com/PHPMailer/PHPMailer/</a>
        for new versions of PHPMailer.</p>
        <p>Thank you!</p>
    </body>
</html>
EOT;
        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        $msg = $this->Mail->getSentMIMEMessage();
        $this->assertNotContains("\r\n\r\nMIME-Version:", $msg, 'Incorrect MIME headers');
    }

    
    public function testHtmlIso8859()
    {
        $this->Mail->isHTML(true);
        $this->Mail->Subject .= ": ISO-8859-1 HTML";
        $this->Mail->CharSet = 'iso-8859-1';

        
        $content = file_get_contents(realpath('../examples/contents.html'));
       
        $check = base64_decode('6eju/OfF8ebf');
        
        $this->Mail->msgHTML(
            mb_convert_encoding(
                $content,
                "ISO-8859-1",
                mb_detect_encoding($content, "UTF-8, ISO-8859-1, ISO-8859-15", true)
            ),
            realpath(self::INCLUDE_DIR . 'examples')
        );
        $this->buildBody();
        $this->assertTrue(
            strpos($this->Mail->Body, $check) !== false,
            'ISO message body does not contain expected text'
        );
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    
    public function testHtmlUtf8()
    {
        $this->Mail->isHTML(true);
        $this->Mail->Subject .= ": UTF-8 HTML";
        $this->Mail->CharSet = 'UTF-8';

        $this->Mail->Body = <<<EOT
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>HTML email test</title>
    </head>
    <body>
        <p>Russian text: Пустое тело сообщения</p>
    </body>
</html>
EOT;
        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        $msg = $this->Mail->getSentMIMEMessage();
        $this->assertNotContains("\r\n\r\nMIME-Version:", $msg, 'Incorrect MIME headers');
    }

    
    public function testUtf8WithEmbeddedImage()
    {
        $this->Mail->isHTML(true);
        $this->Mail->Subject .= ": UTF-8 with embedded image";
        $this->Mail->CharSet = 'UTF-8';

        $this->Mail->Body = <<<EOT
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>HTML email test</title>
    </head>
    <body>
        <p>Russian text: Пустое тело сообщения</p>
        Embedded Image: <img alt="phpmailer" src="cid:my-attach">
    </body>
</html>
EOT;
        $this->Mail->addEmbeddedImage(
            realpath(self::INCLUDE_DIR . 'examples/images/phpmailer.png'),
            'my-attach',
            'phpmailer.png',
            'base64',
            'image/png'
        );
        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    
    public function testPlainUtf8()
    {
        $this->Mail->isHTML(false);
        $this->Mail->Subject .= ": UTF-8 plain text";
        $this->Mail->CharSet = 'UTF-8';

        $this->Mail->Body = <<<EOT
Russian text: Пустое тело сообщения
EOT;
        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        $msg = $this->Mail->getSentMIMEMessage();
        $this->assertNotContains("\r\n\r\nMIME-Version:", $msg, 'Incorrect MIME headers');
    }

    
    public function testMsgHTML()
    {
        $message = file_get_contents(realpath(self::INCLUDE_DIR . 'examples/contentsutf8.html'));
        $this->Mail->CharSet = 'utf-8';
        $this->Mail->Body = '';
        $this->Mail->AltBody = '';
        
        $this->Mail->msgHTML($message, realpath(self::INCLUDE_DIR . 'examples'));
        $this->Mail->Subject .= ': msgHTML';

        $this->assertNotEmpty($this->Mail->Body, 'Body not set by msgHTML');
        $this->assertNotEmpty($this->Mail->AltBody, 'AltBody not set by msgHTML');
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);

        
        $this->Mail->AltBody = '';
        $this->Mail->msgHTML($message, realpath(self::INCLUDE_DIR . 'examples'), function ($html) {
            return strtoupper(strip_tags($html));
        });
        $this->Mail->Subject .= ' + custom html2text';
        $this->assertNotEmpty($this->Mail->AltBody, 'Custom AltBody not set by msgHTML');

        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    
    public function testHTMLAttachment()
    {
        $this->Mail->Body = 'This is the <strong>HTML</strong> part of the email.';
        $this->Mail->Subject .= ': HTML + Attachment';
        $this->Mail->isHTML(true);

        if (!$this->Mail->addAttachment(
            realpath(self::INCLUDE_DIR . 'examples/images/phpmailer_mini.png'), 'phpmailer_mini.png')
        ) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        
        $this->assertFalse($this->Mail->addAttachment(__FILE__ . md5(microtime()), 'nonexistent_file.txt'));

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    
    public function testHTMLStringEmbedNoName()
    {
        $this->Mail->Body = 'This is the <strong>HTML</strong> part of the email.';
        $this->Mail->Subject .= ': HTML + unnamed embedded image';
        $this->Mail->isHTML(true);

        if (!$this->Mail->addStringEmbeddedImage(
            file_get_contents(realpath(self::INCLUDE_DIR . 'examples/images/phpmailer_mini.png')),
            md5('phpmailer_mini.png').'@phpmailer.0',
            '', 
            'base64',
            'image/png',
            'inline')
        ) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    
    public function testHTMLMultiAttachment()
    {
        $this->Mail->Body = 'This is the <strong>HTML</strong> part of the email.';
        $this->Mail->Subject .= ': HTML + multiple Attachment';
        $this->Mail->isHTML(true);

        if (!$this->Mail->addAttachment(realpath(self::INCLUDE_DIR . 'examples/images/phpmailer_mini.png'), 'phpmailer_mini.png')) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        if (!$this->Mail->addAttachment(realpath(self::INCLUDE_DIR . 'examples/images/phpmailer.png'), 'phpmailer.png')) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    
    public function testEmbeddedImage()
    {
        $this->Mail->Body = 'Embedded Image: <img alt="phpmailer" src="cid:my-attach">' .
            'Here is an image!';
        $this->Mail->Subject .= ': Embedded Image';
        $this->Mail->isHTML(true);

        if (!$this->Mail->addEmbeddedImage(
            realpath(self::INCLUDE_DIR . 'examples/images/phpmailer.png'),
            'my-attach',
            'phpmailer.png',
            'base64',
            'image/png'
        )
        ) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        $this->Mail->addEmbeddedImage('thisfiledoesntexist', 'xyz'); 
        $this->Mail->addEmbeddedImage(__FILE__, '123'); 
    }

    
    public function testMultiEmbeddedImage()
    {
        $this->Mail->Body = 'Embedded Image: <img alt="phpmailer" src="cid:my-attach">' .
            'Here is an image!</a>';
        $this->Mail->Subject .= ': Embedded Image + Attachment';
        $this->Mail->isHTML(true);

        if (!$this->Mail->addEmbeddedImage(
            realpath(self::INCLUDE_DIR . 'examples/images/phpmailer.png'),
            'my-attach',
            'phpmailer.png',
            'base64',
            'image/png'
        )
        ) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        if (!$this->Mail->addAttachment(__FILE__, 'test.txt')) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    
    public function testAltBody()
    {
        $this->Mail->Body = 'This is the <strong>HTML</strong> part of the email.';
        $this->Mail->AltBody = 'Here is the text body of this message.  ' .
            'It should be quite a few lines.  It should be wrapped at the ' .
            '40 characters.  Make sure that it is.';
        $this->Mail->WordWrap = 40;
        $this->addNote('This is a mulipart alternative email');
        $this->Mail->Subject .= ': AltBody + Word Wrap';

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    
    public function testAltBodyAttachment()
    {
        $this->Mail->Body = 'This is the <strong>HTML</strong> part of the email.';
        $this->Mail->AltBody = 'This is the text part of the email.';
        $this->Mail->Subject .= ': AltBody + Attachment';
        $this->Mail->isHTML(true);

        if (!$this->Mail->addAttachment(__FILE__, 'test_attach.txt')) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    
    public function testIcal()
    {
        
        require_once realpath(self::INCLUDE_DIR . 'extras/EasyPeasyICS.php');
        $ICS = new EasyPeasyICS("PHPMailer test calendar");
        $this->assertNotEmpty(
            $ICS->addEvent(
                strtotime('tomorrow 10:00 Europe/Paris'),
                strtotime('tomorrow 11:00 Europe/Paris'),
                'PHPMailer iCal test',
                'A test of PHPMailer iCal support',
                'https://github.com/PHPMailer/PHPMailer'
            ),
            'Generated event string is empty'
        );
        $ICS->addEvent(
            strtotime('tomorrow 10:00 Europe/Paris'),
            strtotime('tomorrow 11:00 Europe/Paris'),
            'PHPMailer iCal test',
            'A test of PHPMailer iCal support',
            'https://github.com/PHPMailer/PHPMailer'
        );
        $events = $ICS->getEvents();
        $this->assertEquals(2, count($events), 'Event count mismatch');
        $ICS->clearEvents();
        $events = $ICS->getEvents();
        $this->assertEquals(0, count($events), 'Event clearing failed');
        $ICS->setName('test');
        $this->assertEquals('test', $ICS->getName(), 'Setting ICS name failed');
        $this->assertNotEmpty($ICS->render(false), 'Empty calendar');
        $this->Mail->Body = 'This is the <strong>HTML</strong> part of the email.';
        $this->Mail->AltBody = 'This is the text part of the email.';
        $this->Mail->Subject .= ': iCal';
        $this->Mail->isHTML(true);
        $this->buildBody();
        $this->Mail->Ical = $ICS->render(false);
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    
    public function testMultipleSend()
    {
        $this->Mail->Body = 'Sending two messages without keepalive';
        $this->buildBody();
        $subject = $this->Mail->Subject;

        $this->Mail->Subject = $subject . ': SMTP 1';
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);

        $this->Mail->Subject = $subject . ': SMTP 2';
        $this->Mail->Sender = 'blah@example.com';
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    
    public function testSendmailSend()
    {
        $this->Mail->Body = 'Sending via sendmail';
        $this->buildBody();
        $subject = $this->Mail->Subject;

        $this->Mail->Subject = $subject . ': sendmail';
        $this->Mail->isSendmail();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    
    public function testQmailSend()
    {
        
        if (file_exists('/var/qmail/bin/qmail-inject')) {
            $this->Mail->Body = 'Sending via qmail';
            $this->BuildBody();
            $subject = $this->Mail->Subject;

            $this->Mail->Subject = $subject . ': qmail';
            $this->Mail->IsQmail();
            $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
        } else {
            $this->markTestSkipped('Qmail is not installed');
        }
    }

    
    public function testMailSend()
    {
        $sendmail = ini_get('sendmail_path');
       
        if (strpos($sendmail, '/') === false) {
            ini_set('sendmail_path', '/usr/sbin/sendmail -t -i ');
        }
        $this->Mail->Body = 'Sending via mail()';
        $this->buildBody();

        $this->Mail->Subject = $this->Mail->Subject . ': mail()';
        $this->Mail->isMail();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        $msg = $this->Mail->getSentMIMEMessage();
        $this->assertNotContains("\r\n\r\nMIME-Version:", $msg, 'Incorrect MIME headers');
    }

    
    public function testEmptyBody()
    {
        $this->buildBody();
        $this->Mail->Body = '';
        $this->Mail->Subject = $this->Mail->Subject . ': Empty Body';
        $this->Mail->isMail();
        $this->Mail->AllowEmpty = true;
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        $this->Mail->AllowEmpty = false;
        $this->assertFalse($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    
    public function testLongBody()
    {
        $oklen = str_repeat(str_repeat('0', PHPMailer::MAX_LINE_LENGTH) . PHPMailer::CRLF, 2);
        $badlen = str_repeat(str_repeat('1', PHPMailer::MAX_LINE_LENGTH + 1) . PHPMailer::CRLF, 2);

        $this->Mail->Body = "This message contains lines that are too long.".
            PHPMailer::CRLF . $oklen . $badlen . $oklen;
        $this->assertTrue(
            PHPMailer::hasLineLongerThanMax($this->Mail->Body),
            'Test content does not contain long lines!'
        );
        $this->Mail->isHTML();
        $this->buildBody();
        $this->Mail->AltBody = $this->Mail->Body;
        $this->Mail->Encoding = '8bit';
        $this->Mail->preSend();
        $message = $this->Mail->getSentMIMEMessage();
        $this->assertFalse(PHPMailer::hasLineLongerThanMax($message), 'Long line not corrected.');
        $this->assertContains(
            'Content-Transfer-Encoding: quoted-printable',
            $message,
            'Long line did not cause transfer encoding switch.'
        );
    }

    
    public function testShortBody()
    {
        $oklen = str_repeat(str_repeat('0', PHPMailer::MAX_LINE_LENGTH) . PHPMailer::CRLF, 10);

        $this->Mail->Body = "This message does not contain lines that are too long.".
            PHPMailer::CRLF . $oklen;
        $this->assertFalse(
            PHPMailer::hasLineLongerThanMax($this->Mail->Body),
            'Test content contains long lines!'
        );
        $this->buildBody();
        $this->Mail->Encoding = '8bit';
        $this->Mail->preSend();
        $message = $this->Mail->getSentMIMEMessage();
        $this->assertFalse(PHPMailer::hasLineLongerThanMax($message), 'Long line not corrected.');
        $this->assertNotContains(
            'Content-Transfer-Encoding: quoted-printable',
            $message,
            'Short line caused transfer encoding switch.'
        );
    }

    
    public function testSmtpKeepAlive()
    {
        $this->Mail->Body = 'SMTP keep-alive test.';
        $this->buildBody();
        $subject = $this->Mail->Subject;

        $this->Mail->SMTPKeepAlive = true;
        $this->Mail->Subject = $subject . ': SMTP keep-alive 1';
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);

        $this->Mail->Subject = $subject . ': SMTP keep-alive 2';
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        $this->Mail->smtpClose();
    }

    
    public function testDenialOfServiceAttack()
    {
        $this->Mail->Body = 'This should no longer cause a denial of service.';
        $this->buildBody();

        $this->Mail->Subject = substr(str_repeat('0123456789', 100), 0, 998);
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    
    public function testDenialOfServiceAttack2()
    {
        
        $this->Mail->Encoding = '1234567890123456789012345678901234567890123456789012345678901234567890';
        
        $this->Mail->wrapText(str_repeat('This should no longer cause a denial of service. ', 30), 0);
    }

    
    public function testError()
    {
        $this->Mail->Subject .= ': Error handling test - this should be sent ok';
        $this->buildBody();
        $this->Mail->clearAllRecipients(); 
        $this->assertTrue($this->Mail->isError() == false, 'Error found');
        $this->assertTrue($this->Mail->send() == false, 'send succeeded');
        $this->assertTrue($this->Mail->isError(), 'No error found');
        $this->assertEquals('You must provide at least one recipient email address.', $this->Mail->ErrorInfo);
        $this->Mail->addAddress($_REQUEST['mail_to']);
        $this->assertTrue($this->Mail->send(), 'send failed');
    }

    
    public function testAddressing()
    {
        $this->assertFalse($this->Mail->addAddress(''), 'Empty address accepted');
        $this->assertFalse($this->Mail->addAddress('', 'Nobody'), 'Empty address with name accepted');
        $this->assertFalse($this->Mail->addAddress('a@example..com'), 'Invalid address accepted');
        $this->assertTrue($this->Mail->addAddress('a@example.com'), 'Addressing failed');
        $this->assertFalse($this->Mail->addAddress('a@example.com'), 'Duplicate addressing failed');
        $this->assertTrue($this->Mail->addCC('b@example.com'), 'CC addressing failed');
        $this->assertFalse($this->Mail->addCC('b@example.com'), 'CC duplicate addressing failed');
        $this->assertFalse($this->Mail->addCC('a@example.com'), 'CC duplicate addressing failed (2)');
        $this->assertTrue($this->Mail->addBCC('c@example.com'), 'BCC addressing failed');
        $this->assertFalse($this->Mail->addBCC('c@example.com'), 'BCC duplicate addressing failed');
        $this->assertFalse($this->Mail->addBCC('a@example.com'), 'BCC duplicate addressing failed (2)');
        $this->assertTrue($this->Mail->addReplyTo('a@example.com'), 'Replyto Addressing failed');
        $this->assertFalse($this->Mail->addReplyTo('a@example..com'), 'Invalid Replyto address accepted');
        $this->assertTrue($this->Mail->setFrom('a@example.com', 'some name'), 'setFrom failed');
        $this->assertFalse($this->Mail->setFrom('a@example.com.', 'some name'), 'setFrom accepted invalid address');
        $this->Mail->Sender = '';
        $this->Mail->setFrom('a@example.com', 'some name', true);
        $this->assertEquals($this->Mail->Sender, 'a@example.com', 'setFrom failed to set sender');
        $this->Mail->Sender = '';
        $this->Mail->setFrom('a@example.com', 'some name', false);
        $this->assertEquals($this->Mail->Sender, '', 'setFrom should not have set sender');
        $this->Mail->clearCCs();
        $this->Mail->clearBCCs();
        $this->Mail->clearReplyTos();
    }

    
    public function testAddressSplitting()
    {
        
        $this->assertCount(
            2,
            $this->Mail->parseAddresses(
                'Joe User <joe@example.com>, Jill User <jill@example.net>'
            ),
            'Failed to recognise address list (IMAP parser)'
        );
        $this->assertEquals(
            array(
                array("name" => 'Joe User', 'address' => 'joe@example.com'),
                array("name" => 'Jill User', 'address' => 'jill@example.net'),
                array("name" => '', 'address' => 'frank@example.com'),
            ),
            $this->Mail->parseAddresses(
                'Joe User <joe@example.com>,'
                    . 'Jill User <jill@example.net>,'
                    . 'frank@example.com,'
            ),
            'Parsed addresses'
        );
        
        $this->assertCount(
            2,
            $this->Mail->parseAddresses(
                'Joe User <joe@example.com>, Jill User <jill@example.net>',
                false
            ),
            'Failed to recognise address list'
        );
        
        $this->assertNotEmpty(
            $this->Mail->parseAddresses(
                'Joe User <joe@example.com>',
                false
            ),
            'Failed to recognise single address'
        );
        
        $this->assertNotEmpty(
            $this->Mail->parseAddresses(
                'Tim "The Book" O\'Reilly <foo@example.com>'
            ),
            'Failed to recognise quoted name (IMAP)'
        );
        
        $this->assertNotEmpty(
            $this->Mail->parseAddresses(
                'Tim "The Book" O\'Reilly <foo@example.com>',
                false
            ),
            'Failed to recognise quoted name'
        );
        
        $this->assertNotEmpty(
            $this->Mail->parseAddresses(
                'Joe User <joe@example.com>'
            ),
            'Failed to recognise single address (IMAP)'
        );
        
        $this->assertNotEmpty(
            $this->Mail->parseAddresses(
                'joe@example.com',
                false
            ),
            'Failed to recognise unnamed address'
        );
        
        $this->assertNotEmpty(
            $this->Mail->parseAddresses(
                'joe@example.com'
            ),
            'Failed to recognise unnamed address (IMAP)'
        );
        
        $this->assertEmpty(
            $this->Mail->parseAddresses(
                'Joe User <joe@example.com.>, Jill User <jill.@example.net>'
            ),
            'Failed to recognise invalid addresses (IMAP)'
        );
        
        $this->assertEmpty(
            $this->Mail->parseAddresses(
                'Joe User <joe@example.com.>, Jill User <jill.@example.net>',
                false
            ),
            'Failed to recognise invalid addresses'
        );
    }

    
    public function testAddressEscaping()
    {
        $this->Mail->Subject .= ': Address escaping';
        $this->Mail->clearAddresses();
        $this->Mail->addAddress('foo@example.com', 'Tim "The Book" O\'Reilly');
        $this->Mail->Body = 'Test correct escaping of quotes in addresses.';
        $this->buildBody();
        $this->Mail->preSend();
        $b = $this->Mail->getSentMIMEMessage();
        $this->assertTrue((strpos($b, 'To: "Tim \"The Book\" O\'Reilly" <foo@example.com>') !== false));
    }

    
    public function testMIMEStructure()
    {
        $this->Mail->Subject .= ': MIME structure';
        $this->Mail->Body = '<h3>MIME structure test.</h3>';
        $this->Mail->AltBody = 'MIME structure test.';
        $this->buildBody();
        $this->Mail->preSend();
        $this->assertRegExp(
            "/Content-Transfer-Encoding: 8bit\r\n\r\n".
            "This is a multi-part message in MIME format./",
            $this->Mail->getSentMIMEMessage(),
            'MIME structure broken'
        );
    }

    
    public function testBCCAddressing()
    {
        $this->Mail->Subject .= ': BCC-only addressing';
        $this->buildBody();
        $this->Mail->clearAllRecipients();
        $this->assertTrue($this->Mail->addBCC('a@example.com'), 'BCC addressing failed');
        $this->assertTrue($this->Mail->send(), 'send failed');
    }

    
    public function testEncodings()
    {
        $this->Mail->CharSet = 'iso-8859-1';
        $this->assertEquals(
            '=A1Hola!_Se=F1or!',
            $this->Mail->encodeQ("\xa1Hola! Se\xf1or!", 'text'),
            'Q Encoding (text) failed'
        );
        $this->assertEquals(
            '=A1Hola!_Se=F1or!',
            $this->Mail->encodeQ("\xa1Hola! Se\xf1or!", 'comment'),
            'Q Encoding (comment) failed'
        );
        $this->assertEquals(
            '=A1Hola!_Se=F1or!',
            $this->Mail->encodeQ("\xa1Hola! Se\xf1or!", 'phrase'),
            'Q Encoding (phrase) failed'
        );
        $this->Mail->CharSet = 'UTF-8';
        $this->assertEquals(
            '=C2=A1Hola!_Se=C3=B1or!',
            $this->Mail->encodeQ("\xc2\xa1Hola! Se\xc3\xb1or!", 'text'),
            'Q Encoding (text) failed'
        );
        
        $this->assertEquals(
            'Nov=C3=A1=3D',
            $this->Mail->encodeQ("Nov\xc3\xa1=", 'text'),
            'Q Encoding (text) failed 2'
        );
    }

    
    public function testBase64()
    {
        $this->Mail->Subject .= ': Base-64 encoding';
        $this->Mail->Encoding = 'base64';
        $this->buildBody();
        $this->assertTrue($this->Mail->send(), 'Base64 encoding failed');
    }
    
    public function testSigning()
    {
        $this->Mail->Subject .= ': S/MIME signing';
        $this->Mail->Body = 'This message is S/MIME signed.';
        $this->buildBody();

        $dn = array(
            'countryName' => 'UK',
            'stateOrProvinceName' => 'Here',
            'localityName' => 'There',
            'organizationName' => 'PHP',
            'organizationalUnitName' => 'PHPMailer',
            'commonName' => 'PHPMailer Test',
            'emailAddress' => 'phpmailer@example.com'
        );
        $keyconfig = array(
            "digest_alg" => "sha256",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        $password = 'password';
        $certfile = 'certfile.pem';
        $keyfile = 'keyfile.pem';

        
        $pk = openssl_pkey_new($keyconfig);
        
        $csr = openssl_csr_new($dn, $pk);
        
        $cert = openssl_csr_sign($csr, null, $pk, 1);
        
        openssl_x509_export($cert, $certout);
        file_put_contents($certfile, $certout);
        
        openssl_pkey_export($pk, $pkeyout, $password);
        file_put_contents($keyfile, $pkeyout);

        $this->Mail->sign(
            $certfile,
            $keyfile,
            $password
        );
        $this->assertTrue($this->Mail->send(), 'S/MIME signing failed');

        $msg = $this->Mail->getSentMIMEMessage();
        $this->assertNotContains("\r\n\r\nMIME-Version:", $msg, 'Incorrect MIME headers');
        unlink($certfile);
        unlink($keyfile);
    }

    
    public function testSigningWithCA()
    {
        $this->Mail->Subject .= ': S/MIME signing with CA';
        $this->Mail->Body = 'This message is S/MIME signed with an extra CA cert.';
        $this->buildBody();

        $certprops = array(
            'countryName' => 'UK',
            'stateOrProvinceName' => 'Here',
            'localityName' => 'There',
            'organizationName' => 'PHP',
            'organizationalUnitName' => 'PHPMailer',
            'commonName' => 'PHPMailer Test',
            'emailAddress' => 'phpmailer@example.com'
        );
        $cacertprops = array(
            'countryName' => 'UK',
            'stateOrProvinceName' => 'Here',
            'localityName' => 'There',
            'organizationName' => 'PHP',
            'organizationalUnitName' => 'PHPMailer CA',
            'commonName' => 'PHPMailer Test CA',
            'emailAddress' => 'phpmailer@example.com'
        );
        $keyconfig = array(
            "digest_alg" => "sha256",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        $password = 'password';
        $cacertfile = 'cacertfile.pem';
        $cakeyfile = 'cakeyfile.pem';
        $certfile = 'certfile.pem';
        $keyfile = 'keyfile.pem';

        
        $capk = openssl_pkey_new($keyconfig);
       
        $csr = openssl_csr_new($cacertprops, $capk);
        
        $cert = openssl_csr_sign($csr, null, $capk, 1);
        
        openssl_x509_export($cert, $certout);
        file_put_contents($cacertfile, $certout);
        
        openssl_pkey_export($capk, $pkeyout, $password);
        file_put_contents($cakeyfile, $pkeyout);

        
        $pk = openssl_pkey_new($keyconfig);
        
        $csr = openssl_csr_new($certprops, $pk);
        
        $cert = openssl_csr_sign($csr, 'file://' . $cacertfile, $capk, 1);
        
        openssl_x509_export($cert, $certout);
        file_put_contents($certfile, $certout);
        
        openssl_pkey_export($pk, $pkeyout, $password);
        file_put_contents($keyfile, $pkeyout);

        $this->Mail->sign(
            $certfile,
            $keyfile,
            $password,
            $cacertfile
        );
        $this->assertTrue($this->Mail->send(), 'S/MIME signing with CA failed');
        unlink($cacertfile);
        unlink($cakeyfile);
        unlink($certfile);
        unlink($keyfile);
    }

    
    public function testDKIM()
    {
        $this->Mail->Subject .= ': DKIM signing';
        $this->Mail->Body = 'This message is DKIM signed.';
        $this->buildBody();
        $privatekeyfile = 'dkim_private.key';
        
        $pk = openssl_pkey_new(
            array(
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA
            )
        );
        openssl_pkey_export_to_file($pk, $privatekeyfile);
        $this->Mail->DKIM_domain = 'example.com';
        $this->Mail->DKIM_private = $privatekeyfile;
        $this->Mail->DKIM_selector = 'phpmailer';
        $this->Mail->DKIM_passphrase = ''; 
        $this->assertTrue($this->Mail->send(), 'DKIM signed mail failed');
        unlink($privatekeyfile);
    }

    
    public function testLineBreaks()
    {
        $unixsrc = "hello\nWorld\nAgain\n";
        $macsrc = "hello\rWorld\rAgain\r";
        $windowssrc = "hello\r\nWorld\r\nAgain\r\n";
        $mixedsrc = "hello\nWorld\rAgain\r\n";
        $target = "hello\r\nWorld\r\nAgain\r\n";
        $this->assertEquals($target, PHPMailer::normalizeBreaks($unixsrc), 'UNIX break reformatting failed');
        $this->assertEquals($target, PHPMailer::normalizeBreaks($macsrc), 'Mac break reformatting failed');
        $this->assertEquals($target, PHPMailer::normalizeBreaks($windowssrc), 'Windows break reformatting failed');
        $this->assertEquals($target, PHPMailer::normalizeBreaks($mixedsrc), 'Mixed break reformatting failed');
    }

    
    public function testLineLength()
    {
        $oklen = str_repeat(str_repeat('0', PHPMailer::MAX_LINE_LENGTH)."\r\n", 2);
        $badlen = str_repeat(str_repeat('1', PHPMailer::MAX_LINE_LENGTH + 1) . "\r\n", 2);
        $this->assertTrue(PHPMailer::hasLineLongerThanMax($badlen), 'Long line not detected (only)');
        $this->assertTrue(PHPMailer::hasLineLongerThanMax($oklen . $badlen), 'Long line not detected (first)');
        $this->assertTrue(PHPMailer::hasLineLongerThanMax($badlen . $oklen), 'Long line not detected (last)');
        $this->assertTrue(
            PHPMailer::hasLineLongerThanMax($oklen . $badlen . $oklen),
            'Long line not detected (middle)'
        );
        $this->assertFalse(PHPMailer::hasLineLongerThanMax($oklen), 'Long line false positive');
        $this->Mail->isHTML(false);
        $this->Mail->Subject .= ": Line length test";
        $this->Mail->CharSet = 'UTF-8';
        $this->Mail->Encoding = '8bit';
        $this->Mail->Body = $oklen . $badlen . $oklen . $badlen;
        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        $this->assertEquals('quoted-printable', $this->Mail->Encoding, 'Long line did not override transfer encoding');
    }

    
    public function testMessageID()
    {
        $this->Mail->Body = 'Test message ID.';
        $id = md5(12345);
        $this->Mail->MessageID = $id;
        $this->buildBody();
        $this->Mail->preSend();
        $lastid = $this->Mail->getLastMessageID();
        $this->assertNotEquals($lastid, $id, 'Invalid Message ID allowed');
        $id = '<'.md5(12345).'@example.com>';
        $this->Mail->MessageID = $id;
        $this->buildBody();
        $this->Mail->preSend();
        $lastid = $this->Mail->getLastMessageID();
        $this->assertEquals($lastid, $id, 'Custom Message ID not used');
        $this->Mail->MessageID = '';
        $this->buildBody();
        $this->Mail->preSend();
        $lastid = $this->Mail->getLastMessageID();
        $this->assertRegExp('/^<.*@.*>$/', $lastid, 'Invalid default Message ID');
    }

    
    public function testMiscellaneous()
    {
        $this->assertEquals('application/pdf', PHPMailer::_mime_types('pdf'), 'MIME TYPE lookup failed');
        $this->Mail->addCustomHeader('SomeHeader: Some Value');
        $this->Mail->clearCustomHeaders();
        $this->Mail->clearAttachments();
        $this->Mail->isHTML(false);
        $this->Mail->isSMTP();
        $this->Mail->isMail();
        $this->Mail->isSendmail();
        $this->Mail->isQmail();
        $this->Mail->setLanguage('fr');
        $this->Mail->Sender = '';
        $this->Mail->createHeader();
        $this->assertFalse($this->Mail->set('x', 'y'), 'Invalid property set succeeded');
        $this->assertTrue($this->Mail->set('Timeout', 11), 'Valid property set failed');
        $this->assertTrue($this->Mail->set('AllowEmpty', null), 'Null property set failed');
        $this->assertTrue($this->Mail->set('AllowEmpty', false), 'Valid property set of null property failed');
        
        $a = '/mnt/files/飛兒樂 團光茫.mp3';
        $q = PHPMailer::mb_pathinfo($a);
        $this->assertEquals($q['dirname'], '/mnt/files', 'UNIX dirname not matched');
        $this->assertEquals($q['basename'], '飛兒樂 團光茫.mp3', 'UNIX basename not matched');
        $this->assertEquals($q['extension'], 'mp3', 'UNIX extension not matched');
        $this->assertEquals($q['filename'], '飛兒樂 團光茫', 'UNIX filename not matched');
        $this->assertEquals(
            PHPMailer::mb_pathinfo($a, PATHINFO_DIRNAME),
            '/mnt/files',
            'Dirname path element not matched'
        );
        $this->assertEquals(PHPMailer::mb_pathinfo($a, 'filename'), '飛兒樂 團光茫', 'Filename path element not matched');
        $a = 'c:\mnt\files\飛兒樂 團光茫.mp3';
        $q = PHPMailer::mb_pathinfo($a);
        $this->assertEquals($q['dirname'], 'c:\mnt\files', 'Windows dirname not matched');
        $this->assertEquals($q['basename'], '飛兒樂 團光茫.mp3', 'Windows basename not matched');
        $this->assertEquals($q['extension'], 'mp3', 'Windows extension not matched');
        $this->assertEquals($q['filename'], '飛兒樂 團光茫', 'Windows filename not matched');
    }
    public function testBadSMTP()
    {
        $this->Mail->smtpConnect();
        $smtp = $this->Mail->getSMTPInstance();
        $this->assertFalse($smtp->mail("somewhere\nbad"), 'Bad SMTP command containing breaks accepted');
    }

    
    public function testCustomHeaderGetter()
    {
        $this->Mail->addCustomHeader('foo', 'bar');
        $this->assertEquals(array(array('foo', 'bar')), $this->Mail->getCustomHeaders());

        $this->Mail->addCustomHeader('foo', 'baz');
        $this->assertEquals(array(
            array('foo', 'bar'),
            array('foo', 'baz')
        ), $this->Mail->getCustomHeaders());

        $this->Mail->clearCustomHeaders();
        $this->assertEmpty($this->Mail->getCustomHeaders());

        $this->Mail->addCustomHeader('yux');
        $this->assertEquals(array(array('yux')), $this->Mail->getCustomHeaders());

        $this->Mail->addCustomHeader('Content-Type: application/json');
        $this->assertEquals(array(
            array('yux'),
            array('Content-Type', ' application/json')
        ), $this->Mail->getCustomHeaders());
    }

    
    public function testConfirmReadingTo()
    {
        $this->Mail->CharSet = 'utf-8';
        $this->buildBody();

        $this->Mail->ConfirmReadingTo = 'test@example..com';  
        $this->assertFalse($this->Mail->send(), $this->Mail->ErrorInfo);

        $this->Mail->ConfirmReadingTo = ' test@example.com';  
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        $this->assertEquals(
            'test@example.com',
            $this->Mail->ConfirmReadingTo,
            'Unexpected read receipt address');

        $this->Mail->ConfirmReadingTo = 'test@françois.ch';  
        if ($this->Mail->idnSupported()) {
            $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
            $this->assertEquals(
                'test@xn--franois-xxa.ch',
                $this->Mail->ConfirmReadingTo,
                'IDN address not converted to punycode');
        } else {
            $this->assertFalse($this->Mail->send(), $this->Mail->ErrorInfo);
        }
    }

    
    public function testConvertEncoding()
    {
        if (!$this->Mail->idnSupported()) {
            $this->markTestSkipped('intl and/or mbstring extensions are not available');
        }

        $this->Mail->clearAllRecipients();
        $this->Mail->clearReplyTos();

        
        $domain = '@' . mb_convert_encoding('françois.ch', 'ISO-8859-1', 'UTF-8');
        $this->Mail->addAddress('test' . $domain);
        $this->Mail->addCC('test+cc' . $domain);
        $this->Mail->addBCC('test+bcc' . $domain);
        $this->Mail->addReplyTo('test+replyto' . $domain);

        
        $this->assertEmpty($this->Mail->getToAddresses(), 'Bad "to" recipients');
        $this->assertEmpty($this->Mail->getCcAddresses(), 'Bad "cc" recipients');
        $this->assertEmpty($this->Mail->getBccAddresses(), 'Bad "bcc" recipients');
        $this->assertEmpty($this->Mail->getReplyToAddresses(), 'Bad "reply-to" recipients');

        
        $this->Mail->clearBCCs();

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);

        
        $domain = $this->Mail->punyencodeAddress($domain);
        $this->assertEquals(
            array(array('test' . $domain, '')),
            $this->Mail->getToAddresses(),
            'Bad "to" recipients');
        $this->assertEquals(
            array(array('test+cc' . $domain, '')),
            $this->Mail->getCcAddresses(),
            'Bad "cc" recipients');
        $this->assertEmpty($this->Mail->getBccAddresses(), 'Bad "bcc" recipients');
        $this->assertEquals(
            array('test+replyto' . $domain => array('test+replyto' . $domain, '')),
            $this->Mail->getReplyToAddresses(),
            'Bad "reply-to" addresses');
    }

    
    public function testDuplicateIDNRemoved()
    {
        if (!$this->Mail->idnSupported()) {
            $this->markTestSkipped('intl and/or mbstring extensions are not available');
        }

        $this->Mail->clearAllRecipients();
        $this->Mail->clearReplyTos();

        $this->Mail->CharSet = 'utf-8';

        $this->assertTrue($this->Mail->addAddress('test@françois.ch'));
        $this->assertFalse($this->Mail->addAddress('test@françois.ch'));
        $this->assertTrue($this->Mail->addAddress('test@FRANÇOIS.CH'));
        $this->assertFalse($this->Mail->addAddress('test@FRANÇOIS.CH'));
        $this->assertTrue($this->Mail->addAddress('test@xn--franois-xxa.ch'));
        $this->assertFalse($this->Mail->addAddress('test@xn--franois-xxa.ch'));
        $this->assertFalse($this->Mail->addAddress('test@XN--FRANOIS-XXA.CH'));

        $this->assertTrue($this->Mail->addReplyTo('test+replyto@françois.ch'));
        $this->assertFalse($this->Mail->addReplyTo('test+replyto@françois.ch'));
        $this->assertTrue($this->Mail->addReplyTo('test+replyto@FRANÇOIS.CH'));
        $this->assertFalse($this->Mail->addReplyTo('test+replyto@FRANÇOIS.CH'));
        $this->assertTrue($this->Mail->addReplyTo('test+replyto@xn--franois-xxa.ch'));
        $this->assertFalse($this->Mail->addReplyTo('test+replyto@xn--franois-xxa.ch'));
        $this->assertFalse($this->Mail->addReplyTo('test+replyto@XN--FRANOIS-XXA.CH'));

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);

        
        $this->assertEquals(
            1,
            count($this->Mail->getToAddresses()),
            'Bad count of "to" recipients');
        $this->assertEquals(
            1,
            count($this->Mail->getReplyToAddresses()),
            'Bad count of "reply-to" addresses');
    }

    
    public function testPopBeforeSmtpGood()
    {
        
        $pid = shell_exec('nohup ./runfakepopserver.sh >/dev/null 2>/dev/null & printf "%u" $!');
        $this->pids[] = $pid;

        sleep(2);
        
        $this->assertTrue(
            POP3::popBeforeSmtp('localhost', 1100, 10, 'user', 'test', $this->Mail->SMTPDebug),
            'POP before SMTP failed'
        );
        
        shell_exec('kill -TERM ' . escapeshellarg($pid));
        sleep(2);
    }

    
    public function testPopBeforeSmtpBad()
    {
        
        $pid = shell_exec('nohup ./runfakepopserver.sh 1101 >/dev/null 2>/dev/null & printf "%u" $!');
        $this->pids[] = $pid;

        sleep(2);
        
        $this->assertFalse(
            POP3::popBeforeSmtp('localhost', 1101, 10, 'user', 'xxx', $this->Mail->SMTPDebug),
            'POP before SMTP should have failed'
        );
        shell_exec('kill -TERM ' . escapeshellarg($pid));
        sleep(2);
    }

    
    public function testSmtpConnect()
    {
        $this->Mail->SMTPDebug = 4; 
        $this->assertTrue($this->Mail->smtpConnect(), 'SMTP single connect failed');
        $this->Mail->smtpClose();
        $this->Mail->Host = "ssl://localhost:12345;tls://localhost:587;10.10.10.10:54321;localhost:12345;10.10.10.10";
        $this->assertFalse($this->Mail->smtpConnect(), 'SMTP bad multi-connect succeeded');
        $this->Mail->smtpClose();
        $this->Mail->Host = "localhost:12345;10.10.10.10:54321;" . $_REQUEST['mail_host'];
        $this->assertTrue($this->Mail->smtpConnect(), 'SMTP multi-connect failed');
        $this->Mail->smtpClose();
        $this->Mail->Host = " localhost:12345 ; " . $_REQUEST['mail_host'] . ' ';
        $this->assertTrue($this->Mail->smtpConnect(), 'SMTP hosts with stray spaces failed');
        $this->Mail->smtpClose();
        $this->Mail->Host = $_REQUEST['mail_host'];
        
        $this->assertTrue(
            $this->Mail->smtpConnect(array('ssl' => array('verify_depth' => 10))),
            'SMTP connect with options failed'
        );
    }
}


