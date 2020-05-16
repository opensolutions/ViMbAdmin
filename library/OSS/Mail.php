<?php

/*

    $pParams['From'] = array('Name' => 'Johhny', 'Email' => 'johnny@email.com');

    $pParams['From'] = 'johnny@email.com';

    $pParams['ReturnTo'] = 'johnny@email.com'; // only Email is used

    $pParams['ReturnTo'] = array('Email' => 'johnny@email.com'); // only Email is used

    $pParams['ReturnTo'] = array('Name' => 'Johhny', 'Email' => 'johnny@email.com'); // only Email is used

    $pParams['To'] = 'johnny@email.com';

    $pParams['To'] = array('Email' => 'johnny@email.com');

    $pParams['To'] = array('Name' => 'Johhny', 'Email' => 'johnny@email.com');

    $pParams['To'] = array(
                            'johnny@email.com',
                            array('Name' => 'Johhny', 'Email' => 'johnny@email.com'),
                            array('Email' => 'johnny@email.com')
                        );

    $pParams['Cc'] = 'johnny@email.com';

    $pParams['Cc'] = array('Email' => 'johnny@email.com');

    $pParams['Cc'] = array('Name' => 'Johhny', 'Email' => 'johnny@email.com');

    $pParams['Cc'] = array(
                            'johnny@email.com',
                            array('Email' => 'johnny@email.com'),
                            array('Name' => 'Johhny', 'Email' => 'johnny@email.com')
                        );

    $pParams['Bcc'] = 'johnny@email.com';

    $pParams['Bcc'] = array('Email' => 'johnny@email.com');

    $pParams['Bcc'] = array('Name' => 'Johhny', 'Email' => 'johnny@email.com');

    $pParams['Bcc'] = array(
                            'johnny@email.com',
                            array('Email' => 'johnny@email.com'),
                            array('Name' => 'Johhny', 'Email' => 'johnny@email.com')
                        );

    $pParams['Attachment'] = 'path/to/dir/thefile.ext';

    $pParams['Attachment'] = array(
                                    'path/to/dir/thefile.ext',
                                    'path/to/dir/thefile.ext',
                                    'path/to/dir/thefile.ext'
                                );

    $pParams['TextBody'] = 'test email';

    $pParams['HtmlBody'] = '<b><i>test email</i></b>';

    $pParams['Subject'] = 'test';

    $pParams['Charset'] = 'iso-8859-1';

*/

/**
 * OSS Framework
 *
 * This file is part of the "OSS Framework" - a library of tools, utilities and
 * extensions to the Zend Framework V1.x used for PHP application development.
 *
 * Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * All rights reserved.
 *
 * Open Source Solutions Limited is a company registered in Dublin,
 * Ireland with the Companies Registration Office (#438231). We
 * trade as Open Solutions with registered business name (#329120).
 *
 * Contact: Barry O'Donovan - info (at) opensolutions (dot) ie
 *          http://www.opensolutions.ie/
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * It is also available through the world-wide-web at this URL:
 *     http://www.opensolutions.ie/licenses/new-bsd
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@opensolutions.ie so we can send you a copy immediately.
 *
 * @category   OSS
 * @package    OSS_Mail
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 * @link       http://www.opensolutions.ie/ Open Source Solutions Limited
 * @author     Barry O'Donovan <barry@opensolutions.ie>
 * @author     The Skilled Team of PHP Developers at Open Solutions <info@opensolutions.ie>
 */

/**
 * @category   OSS
 * @package    OSS_Mail
 * @copyright  Copyright (c) 2007 - 2012, Open Source Solutions Limited, Dublin, Ireland
 * @license    http://www.opensolutions.ie/licenses/new-bsd New BSD License
 */
class OSS_Mail extends Zend_Mail
{

    const ERR_INSUFFICIENT_DATA = 'You must have a sender, a recipient and a message body to send an e-mail.';


    /**
     * Array of mime types
     *
     * @var array
     */
    protected $_mimeTypes = array(
        '323' => 'text/h323',
        '3dmf' => 'x-world/x-3dmf',
        '3dm' => 'x-world/x-3dmf',
        '7z' => 'application/x-7z-compressed',
        'aab' => 'application/x-authorware-bin',
        'aam' => 'application/x-authorware-map',
        'aas' => 'application/x-authorware-seg',
        'abc' => 'text/vnd.abc',
        'acgi' => 'text/html',
        'acx' => 'application/internet-property-stream',
        'afl' => 'video/animaflex',
        'ai' => 'application/postscript',
        'aif' => 'audio/aiff',
        'aifc' => 'audio/aiff',
        'aiff' => 'audio/aiff',
        'aim' => 'application/x-aim',
        'aip' => 'text/x-audiosoft-intra',
        'ani' => 'application/x-navi-animation',
        'aos' => 'application/x-nokia-9000-communicator-add-on-software',
        'aps' => 'application/mime',
        'arj' => 'application/arj',
        'art' => 'image/x-jg',
        'asc' => 'text/plain',
        'asf' => 'video/x-ms-asf',
        'asm' => 'text/x-asm',
        'asp' => 'text/asp',
        'asr' => 'video/x-ms-asf',
        'asx' => 'video/x-ms-asf',
        'atom' => 'application/atom+xml',
        'au' => 'audio/basic',
        'au' => 'audio/x-au',
        'avi' => 'video/avi',
        'avs' => 'video/avs-video',
        'axs' => 'application/olescript',
        'bas' => 'text/plain',
        'bcpio' => 'application/x-bcpio',
        'bin' => 'application/x-binary',
        'bm' => 'image/bmp',
        'bmp' => 'image/bmp',
        'boo' => 'application/book',
        'book' => 'application/book',
        'boz' => 'application/x-bzip2',
        'bsh' => 'application/x-bsh',
        'bz2' => 'application/x-bzip2',
        'bz' => 'application/x-bzip',
        'cat' => 'application/vnd.ms-pki.seccat',
        'ccad' => 'application/clariscad',
        'cco' => 'application/x-cocoa',
        'cc' => 'text/plain',
        'cdf' => 'application/cdf',
        'cer' => 'application/pkix-cert',
        'cgm' => 'image/cgm',
        'cha' => 'application/x-chat',
        'chat' => 'application/x-chat',
        'class' => 'application/java',
        'clp' => 'application/x-msclip',
        'cmx' => 'image/x-cmx',
        'cod' => 'image/cis-cod',
        'com' => 'text/plain',
        'conf' => 'text/plain',
        'cpio' => 'application/x-cpio',
        'cpp' => 'text/plain',
        'cpt' => 'application/x-cpt',
        'crd' => 'application/x-mscardfile',
        'crl' => 'application/pkcs-crl',
        'crt' => 'application/pkix-cert',
        'csh' => 'text/x-script.csh',
        'css' => 'text/css',
        'c++' => 'text/plain',
        'c' => 'text/plain',
        'cxx' => 'text/plain',
        'dcr' => 'application/x-director',
        'deepv' => 'application/x-deepv',
        'def' => 'text/plain',
        'der' => 'application/x-x509-ca-cert',
        'dif' => 'video/x-dv',
        'dir' => 'application/x-director',
        'djv' => 'image/vnd.djvu',
        'djvu' => 'image/vnd.djvu',
        'dll' => 'application/x-msdownload',
        'dl' => 'video/dl',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'dot' => 'application/msword',
        'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
        'dp' => 'application/commonground',
        'drw' => 'application/drafting',
        'dtd' => 'application/xml-dtd',
        'dvi' => 'application/x-dvi',
        'dv' => 'video/x-dv',
        'dwf' => 'model/vnd.dwf',
        'dwg' => 'image/vnd.dwg',
        'dxf' => 'image/vnd.dwg',
        'dxr' => 'application/x-director',
        'elc' => 'application/x-elc',
        'el' => 'text/x-script.elisp',
        'env' => 'application/x-envoy',
        'eps' => 'application/postscript',
        'es' => 'application/x-esrehber',
        'etx' => 'text/x-setext',
        'evy' => 'application/envoy',
        'ez' => 'application/andrew-inset',
        'f77' => 'text/x-fortran',
        'f90' => 'text/plain',
        'fdf' => 'application/vnd.fdf',
        'fif' => 'image/fif',
        'fli' => 'video/fli',
        'flo' => 'image/florian',
        'flr' => 'x-world/x-vrml',
        'flx' => 'text/vnd.fmi.flexstor',
        'fmf' => 'video/x-atomic3d-feature',
        'for' => 'text/plain',
        'fpx' => 'image/vnd.fpx',
        'frl' => 'application/freeloader',
        'f' => 'text/plain',
        'funk' => 'audio/make',
        'g3' => 'image/g3fax',
        'gif' => 'image/gif',
        'gl' => 'video/gl',
        'gram' => 'application/srgs',
        'grxml' => 'application/srgs+xml',
        'gsd' => 'audio/x-gsm',
        'gsm' => 'audio/x-gsm',
        'gsp' => 'application/x-gsp',
        'gss' => 'application/x-gss',
        'gtar' => 'application/x-gtar',
        'g' => 'text/plain',
        'gz' => 'application/x-gzip',
        'gzip' => 'application/x-gzip',
        'hdf' => 'application/x-hdf',
        'help' => 'application/x-helpfile',
        'hgl' => 'application/vnd.hp-hpgl',
        'hh' => 'text/plain',
        'hlb' => 'text/x-script',
        'hlp' => 'application/winhlp',
        'hpg' => 'application/vnd.hp-hpgl',
        'hpgl' => 'application/vnd.hp-hpgl',
        'hqx' => 'application/binhex',
        'hta' => 'application/hta',
        'htc' => 'text/x-component',
        'h' => 'text/plain',
        'htmls' => 'text/html',
        'html' => 'text/html',
        'htm' => 'text/html',
        'htt' => 'text/webviewhtml',
        'htx' => 'text/html',
        'ice' => 'x-conference/x-cooltalk',
        'ico' => 'image/x-icon',
        'ics' => 'text/calendar',
        'idc' => 'text/plain',
        'ief' => 'image/ief',
        'iefs' => 'image/ief',
        'ifb' => 'text/calendar',
        'iges' => 'application/iges',
        'igs' => 'application/iges',
        'iii' => 'application/x-iphone',
        'ima' => 'application/x-ima',
        'imap' => 'application/x-httpd-imap',
        'inf' => 'application/inf',
        'ins' => 'application/x-internet-signup',
        'ip' => 'application/x-ip2',
        'isp' => 'application/x-internet-signup',
        'isu' => 'video/x-isvideo',
        'it' => 'audio/it',
        'iv' => 'application/x-inventor',
        'ivr' => 'i-world/i-vrml',
        'ivy' => 'application/x-livescreen',
        'jam' => 'audio/x-jam',
        'java' => 'text/plain',
        'jav' => 'text/plain',
        'jav' => 'text/x-java-source',
        'jcm' => 'application/x-java-commerce',
        'jfif' => 'image/jpeg',
        'jfif-tbnl' => 'image/jpeg',
        'jnlp' => 'application/x-java-jnlp-file',
        'jp2' => 'image/jp2',
        'jpeg' => 'image/jpeg',
        'jpe' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'jps' => 'image/x-jps',
        'js' => 'text/javascript',
        'jut' => 'image/jutvision',
        'kar' => 'audio/midi',
        'ksh' => 'application/x-ksh',
        'la' => 'audio/nspaudio',
        'lam' => 'audio/x-liveaudio',
        'latex' => 'application/x-latex',
        'lha' => 'application/lha',
        'list' => 'text/plain',
        'lma' => 'audio/nspaudio',
        'log' => 'text/plain',
        'lsf' => 'video/x-la-asf',
        'lsp' => 'application/x-lisp',
        'lst' => 'text/plain',
        'lsx' => 'video/x-la-asf',
        'ltx' => 'application/x-latex',
        'lzh' => 'application/x-lzh',
        'lzx' => 'application/lzx',
        'lzx' => 'application/x-lzx',
        'm13' => 'application/x-msmediaview',
        'm14' => 'application/x-msmediaview',
        'm1v' => 'video/mpeg',
        'm2a' => 'audio/mpeg',
        'm2v' => 'video/mpeg',
        'm3u' => 'audio/x-mpegurl',
        'm4a' => 'audio/mp4a-latm',
        'm4b' => 'audio/mp4a-latm',
        'm4p' => 'audio/mp4a-latm',
        'm4u' => 'video/vnd.mpegurl',
        'm4v' => 'video/x-m4v',
        'mac' => 'image/x-macpaint',
        'man' => 'application/x-troff-man',
        'map' => 'application/x-navimap',
        'mar' => 'text/plain',
        'mathml' => 'application/mathml+xml',
        'mbd' => 'application/mbedlet',
        'mc$' => 'application/x-magic-cap-package-1.0',
        'mcd' => 'application/mcad',
        'mcf' => 'text/mcf',
        'mcp' => 'application/netmc',
        'mdb' => 'application/x-msaccess',
        'me' => 'application/x-troff-me',
        'mesh' => 'model/mesh',
        'mht' => 'message/rfc822',
        'mhtml' => 'message/rfc822',
        'mid' => 'audio/midi',
        'midi' => 'audio/midi',
        'mif' => 'application/vnd.mif',
        'mime' => 'message/rfc822',
        'mjf' => 'audio/x-vnd.audioexplosion.mjuicemediafile',
        'mjpg' => 'video/x-motion-jpeg',
        'mm' => 'application/base64',
        'mme' => 'application/base64',
        'mny' => 'application/x-msmoney',
        'mod' => 'audio/mod',
        'moov' => 'video/quicktime',
        'movie' => 'video/x-sgi-movie',
        'mov' => 'video/quicktime',
        'mp2' => 'video/mpeg',
        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
        'mpa' => 'audio/mpeg',
        'mpc' => 'application/x-project',
        'mpeg' => 'video/mpeg',
        'mpe' => 'video/mpeg',
        'mpga' => 'audio/mpeg',
        'mpg' => 'video/mpeg',
        'mpp' => 'application/vnd.ms-project',
        'mpt' => 'application/x-project',
        'mpv2' => 'video/mpeg',
        'mpv' => 'application/x-project',
        'mpx' => 'application/x-project',
        'mrc' => 'application/marc',
        'ms' => 'application/x-troff-ms',
        'msh' => 'model/mesh',
        'm' => 'text/plain',
        'mvb' => 'application/x-msmediaview',
        'mv' => 'video/x-sgi-movie',
        'mxu' => 'video/vnd.mpegurl',
        'my' => 'audio/make',
        'mzz' => 'application/x-vnd.audioexplosion.mzz',
        'nap' => 'image/naplps',
        'naplps' => 'image/naplps',
        'nc' => 'application/x-netcdf',
        'ncm' => 'application/vnd.nokia.configuration-message',
        'niff' => 'image/x-niff',
        'nif' => 'image/x-niff',
        'nix' => 'application/x-mix-transfer',
        'nsc' => 'application/x-conference',
        'nvd' => 'application/x-navidoc',
        'nws' => 'message/rfc822',
        'oda' => 'application/oda',
        'ogg' => 'application/ogg',
        'omc' => 'application/x-omc',
        'omcd' => 'application/x-omcdatamaker',
        'omcr' => 'application/x-omcregerator',
        'p10' => 'application/pkcs10',
        'p12' => 'application/pkcs-12',
        'p7a' => 'application/x-pkcs7-signature',
        'p7b' => 'application/x-pkcs7-certificates',
        'p7c' => 'application/pkcs7-mime',
        'p7m' => 'application/pkcs7-mime',
        'p7r' => 'application/x-pkcs7-certreqresp',
        'p7s' => 'application/pkcs7-signature',
        'part' => 'application/pro_eng',
        'pas' => 'text/pascal',
        'pbm' => 'image/x-portable-bitmap',
        'pcl' => 'application/vnd.hp-pcl',
        'pct' => 'image/pict',
        'pcx' => 'image/x-pcx',
        'pdb' => 'chemical/x-pdb',
        'pdf' => 'application/pdf',
        'pfunk' => 'audio/make',
        'pfx' => 'application/x-pkcs12',
        'pgm' => 'image/x-portable-graymap',
        'pgn' => 'application/x-chess-pgn',
        'pic' => 'image/pict',
        'pict' => 'image/pict',
        'pkg' => 'application/x-newton-compatible-pkg',
        'pko' => 'application/vnd.ms-pki.pko',
        'pl' => 'text/plain',
        'pl' => 'text/x-script.perl',
        'plx' => 'application/x-pixclscript',
        'pm4' => 'application/x-pagemaker',
        'pm5' => 'application/x-pagemaker',
        'pma' => 'application/x-perfmon',
        'pmc' => 'application/x-perfmon',
        'pm' => 'image/x-xpixmap',
        'pml' => 'application/x-perfmon',
        'pmr' => 'application/x-perfmon',
        'pm' => 'text/x-script.perl-module',
        'pmw' => 'application/x-perfmon',
        'png' => 'image/png',
        'pnm' => 'image/x-portable-anymap',
        'pntg' => 'image/x-macpaint',
        'pnt' => 'image/x-macpaint',
        'pot' => 'application/mspowerpoint',
        'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'pov' => 'model/x-pov',
        'ppa' => 'application/vnd.ms-powerpoint',
        'ppm' => 'image/x-portable-pixmap',
        'pps' => 'application/mspowerpoint',
        'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
        'ppt' => 'application/powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'ppz' => 'application/mspowerpoint',
        'pre' => 'application/x-freelance',
        'prf' => 'application/pics-rules',
        'prt' => 'application/pro_eng',
        'ps' => 'application/postscript',
        'p' => 'text/x-pascal',
        'pub' => 'application/x-mspublisher',
        'pvu' => 'paleovu/x-pv',
        'pwz' => 'application/vnd.ms-powerpoint',
        'pyc' => 'applicaiton/x-bytecode.python',
        'py' => 'text/x-script.phyton',
        'qcp' => 'audio/vnd.qcelp',
        'qd3d' => 'x-world/x-3dmf',
        'qd3' => 'x-world/x-3dmf',
        'qif' => 'image/x-quicktime',
        'qtc' => 'video/x-qtc',
        'qtif' => 'image/x-quicktime',
        'qti' => 'image/x-quicktime',
        'qt' => 'video/quicktime',
        'ra' => 'audio/x-realaudio',
        'ram' => 'audio/x-pn-realaudio',
        'rar' => 'application/x-rar-compressed',
        'ras' => 'image/cmu-raster',
        'rast' => 'image/cmu-raster',
        'rdf' => 'application/rdf+xml',
        'rexx' => 'text/x-script.rexx',
        'rf' => 'image/vnd.rn-realflash',
        'rgb' => 'image/x-rgb',
        'rm' => 'application/vnd.rn-realmedia',
        'rmi' => 'audio/mid',
        'rmm' => 'audio/x-pn-realaudio',
        'rmp' => 'audio/x-pn-realaudio',
        'rng' => 'application/ringing-tones',
        'rnx' => 'application/vnd.rn-realplayer',
        'roff' => 'application/x-troff',
        'rp' => 'image/vnd.rn-realpix',
        'rpm' => 'audio/x-pn-realaudio-plugin',
        'rtf' => 'text/richtext',
        'rt' => 'text/richtext',
        'rtx' => 'text/richtext',
        'rtx' => 'text/richtext',
        'rv' => 'video/vnd.rn-realvideo',
        's3m' => 'audio/s3m',
        'sbk' => 'application/x-tbook',
        'scd' => 'application/x-msschedule',
        'scm' => 'application/x-lotusscreencam',
        'sct' => 'text/scriptlet',
        'sdml' => 'text/plain',
        'sdp' => 'application/sdp',
        'sdr' => 'application/sounder',
        'sea' => 'application/sea',
        'set' => 'application/set',
        'setpay' => 'application/set-payment-initiation',
        'setreg' => 'application/set-registration-initiation',
        'sgml' => 'text/sgml',
        'sgm' => 'text/sgml',
        'sh' => 'application/x-sh',
        'shar' => 'application/x-shar',
        'sh' => 'text/x-script.sh',
        'shtml' => 'text/html',
        'sid' => 'audio/x-psid',
        'silo' => 'model/mesh',
        'sit' => 'application/x-sit',
        'sitx' => 'application/x-stuffitx',
        'skd' => 'application/x-koan',
        'skm' => 'application/x-koan',
        'skp' => 'application/x-koan',
        'skt' => 'application/x-koan',
        'sl' => 'application/x-seelogo',
        'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
        'smi' => 'application/smil',
        'smil' => 'application/smil',
        'snd' => 'audio/basic',
        'sol' => 'application/solids',
        'spc' => 'text/x-speech',
        'spl' => 'application/futuresplash',
        'spr' => 'application/x-sprite',
        'sprite' => 'application/x-sprite',
        'src' => 'application/x-wais-source',
        'ssi' => 'text/x-server-parsed-html',
        'ssm' => 'application/streamingmedia',
        'sst' => 'application/vnd.ms-pkicertstore',
        'step' => 'application/step',
        's' => 'text/x-asm',
        'stl' => 'application/sla',
        'stm' => 'text/html',
        'stp' => 'application/step',
        'sv4cpio' => 'application/x-sv4cpio',
        'sv4crc' => 'application/x-sv4crc',
        'svf' => 'image/vnd.dwg',
        'svg' => 'image/svg+xml',
        'svr' => 'x-world/x-svr',
        'swf' => 'application/x-shockwave-flash',
        'talk' => 'text/x-speech',
        't' => 'application/x-troff',
        'tar' => 'application/x-tar',
        'tbk' => 'application/toolbook',
        'tcl' => 'application/x-tcl',
        'tcsh' => 'text/x-script.tcsh',
        'tex' => 'application/x-tex',
        'texi' => 'application/x-texinfo',
        'texinfo' => 'application/x-texinfo',
        'text' => 'text/plain',
        'tgz' => 'application/x-compressed',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'tr' => 'application/x-troff',
        'trm' => 'application/x-msterminal',
        'tsi' => 'audio/tsp-audio',
        'tsp' => 'audio/tsplayer',
        'tsv' => 'text/tab-separated-values',
        'turbot' => 'image/florian',
        'txt' => 'text/plain',
        'uil' => 'text/x-uil',
        'uls' => 'text/iuls',
        'unis' => 'text/uri-list',
        'uni' => 'text/uri-list',
        'unv' => 'application/i-deas',
        'uris' => 'text/uri-list',
        'uri' => 'text/uri-list',
        'ustar' => 'application/x-ustar',
        'ustar' => 'multipart/x-ustar',
        'uue' => 'text/x-uuencode',
        'uu' => 'text/x-uuencode',
        'vcd' => 'application/x-cdlink',
        'vcf' => 'text/x-vcard',
        'vcs' => 'text/x-vcalendar',
        'vda' => 'application/vda',
        'vdo' => 'video/vdo',
        'vew' => 'application/groupwise',
        'vivo' => 'video/vivo',
        'viv' => 'video/vivo',
        'vmd' => 'application/vocaltec-media-desc',
        'vmf' => 'application/vocaltec-media-file',
        'voc' => 'audio/voc',
        'vos' => 'video/vosaic',
        'vox' => 'audio/voxware',
        'vqe' => 'audio/x-twinvq-plugin',
        'vqf' => 'audio/x-twinvq',
        'vql' => 'audio/x-twinvq-plugin',
        'vrml' => 'application/x-vrml',
        'vrt' => 'x-world/x-vrt',
        'vsd' => 'application/x-visio',
        'vst' => 'application/x-visio',
        'vsw' => 'application/x-visio',
        'vxml' => 'application/voicexml+xml',
        'w60' => 'application/wordperfect6.0',
        'w61' => 'application/wordperfect6.1',
        'w6w' => 'application/msword',
        'wav' => 'audio/wav',
        'wb1' => 'application/x-qpro',
        'wbmp' => 'image/vnd.wap.wbmp',
        'wbmxl' => 'application/vnd.wap.wbxml',
        'wcm' => 'application/vnd.ms-works',
        'wdb' => 'application/vnd.ms-works',
        'web' => 'application/vnd.xara',
        'wiz' => 'application/msword',
        'wk1' => 'application/x-123',
        'wks' => 'application/vnd.ms-works',
        'wmf' => 'windows/metafile',
        'wmlc' => 'application/vnd.wap.wmlc',
        'wmlsc' => 'application/vnd.wap.wmlscriptc',
        'wmls' => 'text/vnd.wap.wmlscript',
        'wml' => 'text/vnd.wap.wml',
        'word' => 'application/msword',
        'wp5' => 'application/wordperfect',
        'wp6' => 'application/wordperfect',
        'wp' => 'application/wordperfect',
        'wpd' => 'application/wordperfect',
        'wps' => 'application/vnd.ms-works',
        'wq1' => 'application/x-lotus',
        'wri' => 'application/mswrite',
        'wrl' => 'model/vrml',
        'wrz' => 'model/vrml',
        'wsc' => 'text/scriplet',
        'wsrc' => 'application/x-wais-source',
        'wtk' => 'application/x-wintalk',
        'xaf' => 'x-world/x-vrml',
        'xbm' => 'image/xbm',
        'xdr' => 'video/x-amt-demorun',
        'xgz' => 'xgl/drawing',
        'xht' => 'application/xhtml+xml',
        'xhtm' => 'application/xhtml+xml',
        'xhtml' => 'application/xhtml+xml',
        'xif' => 'image/vnd.xiff',
        'xla' => 'application/excel',
        'xl' => 'application/excel',
        'xlb' => 'application/excel',
        'xlc' => 'application/excel',
        'xld' => 'application/excel',
        'xlk' => 'application/excel',
        'xll' => 'application/excel',
        'xll' => 'application/x-excel',
        'xlm' => 'application/excel',
        'xls' => 'application/excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xlt' => 'application/excel',
        'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
        'xlv' => 'application/excel',
        'xlw' => 'application/excel',
        'xm' => 'audio/xm',
        'xml' => 'text/xml',
        'xmz' => 'xgl/movie',
        'xof' => 'x-world/x-vrml',
        'xpix' => 'application/x-vnd.ls-xpix',
        'xpm' => 'image/xpm',
        'x-png' => 'image/png',
        'xslt' => 'application/xslt+xml',
        'xsl' => 'text/xml',
        'xsr' => 'video/x-amt-showrun',
        'xul' => 'application/vnd.mozilla.xul+xml',
        'xwd' => 'image/x-xwd',
        'xwd' => 'image/x-xwindowdump',
        'xyz' => 'chemical/x-xyz',
        'z' => 'application/x-compressed',
        'zip' => 'application/zip',
        'zsh' => 'text/x-script.zsh'
    );


    /**
     * Constructor
     *
     * Calls Zend_Mail::__construct().
     *
     * @param string $pCharSet default 'UTF-8'
     * @return void
     */
    public function __construct( $charSet='UTF-8' )
    {
        parent::__construct( $charSet );
    }


    /**
     * This method takes an array of parameters, and sends out the email. Throws a Zend_Mail_Exception on error. Returns with a Zend_Mail object on success.
     * The images in the HtmlBody will automatically be embedded. 
     *
     * @param array $params
     * @return object Zend_Mail
     * @throws Zend_Mail_Exception
     * @return void
     */
    public function sendMail( $params )
    {
        if (
                !isset( $params['From'] ) || !isset( $params['To'] ) ||
                ( ( isset( $params['TextBody'] ) || isset( $params['HtmlBody'] ) ) == false )
            )
        {
            throw new Zend_Mail_Exception( OSS_Mail::ERR_INSUFFICIENT_DATA );
        }

        if( !isset( $params['Charset'] ) )
                $params['Charset'] = 'UTF-8';

        if( !in_array( mb_strtoupper( $params['Charset']), array( '', 'UTF-8' ) ) )
                $this->_charset = $params['Charset'];

        if( !isset( $params['TextBody'] ) )
                $this->setBodyText( $params['TextBody'], null, Zend_Mime::TYPE_TEXT );

        if( !isset( $params['HtmlBody'] ) )
                $this->setBodyHtml( $params['HtmlBody'], null, Zend_Mime::TYPE_TEXT );

        $this->setSubject( htmlspecialchars_decode( $params['Subject'] ) );

        if( is_array( $params['From'] ) )
        {
            if( isset( $params['From']['Email'] ) )
                $this->setFrom( $params['From']['Email'], $this->amendstring( $params['From']['Name'] ) );
        }
        else
        {
            $this->setFrom( $this->amendstring( $params['From'] ) );
        }

        if( isset( $params['ReturnTo'] ) )
        {
            if( is_array( $params['ReturnTo'] ) )
            {
                if( isset( $params['ReturnTo']['Email'] ) )
                        $this->setReturnPath( $this->amendstring( $params['ReturnTo']['Email'] ) );
            }
            else
            {
                $this->setReturnPath( $this->amendstring( $params['ReturnTo'] ) );
            }
        }

        if( !is_array($params['To'] ) )
        {
            $this->addTo( $this->amendstring( $params['To'] ) );
        }
        else if( is_array( $params['To'] ) && isset( $params['To']['Email'] ) )
        {
            if( isset( $params['To']['Name'] ) )
            {
                $this->addTo( $params['To']['Email'], $this->amendstring( $params['To']['Name'] ) );
            }
            else
            {
                $this->addTo( $this->amendstring( $params['To']['Email'] ) );
            }
        }
        else
        {
            $firstTo = true;
            foreach( $params['To'] as $to )
            {
                if( !is_array( $to ) )
                {
                    if( $firstTo  )
                    {
                        $this->addTo( $this->amendstring( $to ) );
                        $firstTo = false;
                    }
                    else
                    {
                        $this->addCc( $this->amendstring( $to ) );
                    }
                }
                else
                {
                    if( isset( $to['Email']) )
                    {
                        if ($firstTo )
                        {
                            $this->addTo( $to['Email'], $this->amendstring( $to['Name'] ) );
                            $firstTo = false;
                        }
                        else
                        {
                            $this->addCc( $to['Email'], $this->amendstring( $to['Name'] ) );
                        }
                    }
                }
            }
        }

        if( isset( $params['Cc'] ) )
        {
            if( !is_array( $params['Cc'] ) )
            {
                $this->addCc( $this->amendstring( $params['Cc'] ) );
            }
            else if( is_array( $params['Cc'] ) && isset( $params['Cc']['Email'] ) )
            {
                if( isset( $params['Cc']['Name'] ) )
                {
                    $this->addCc( $params['Cc']['Email'], $this->amendstring( $params['Cc']['Name'] ) );
                }
                else
                {
                    $this->addCc( $this->amendstring( $params['Cc']['Email'] ) );
                }
            }
            else
            {
                foreach( $params['Cc'] as $cc )
                {
                    if( is_array( $cc ) )
                    {
                        $this->addCc($this->amendstring( $cc ) );
                    }
                    else
                    {
                        if( isset( $cc['Email'] ) )
                                $this->addCc( $cc['Email'], $this->amendstring( $cc['Name'] ) );
                    }
                }
            }
        }

        if( isset( $params['Bcc'] ) )
        {
            if( !is_array( $params['Bcc'] ) )
            {
                $this->addBcc( htmlspecialchars_decode( $params['Bcc'] ) );
            }
            else if( is_array( $params['Bcc'] ) && isset( $params['Bcc']['Email'] ) )
            {
                $this->addBcc( $this->amendstring( $params['Bcc']['Email'] ) );
            }
            else
            {
                foreach( $params['Bcc'] as $bcc)
                {
                    if( !is_array( $bcc ) )
                    {
                        $this->addBcc( $this->amendstring( $bcc ) );
                    }
                    else
                    {
                        if( isset( $bcc['Email'] ) )
                                $this->addBcc( $this->amendstring( $bcc['Email'] ) );
                    }
                }
            }
        }

        // embed html images inline
        if( isset( $params['HtmlBody'] ) && $params['HtmlBody'] != '' )
        {
            $matchCount = preg_match_all("/<img.*?src=\"(.*?)\".*/iu", $params['HtmlBody'], $matches);

            if( sizeof( $matches[1] ) > 0 ) // if there is any
            {
                $this->setType( Zend_Mime::MULTIPART_RELATED );

                $matches = array_unique( $matches[1] );

                foreach( $matches as $fname )
                        $this->attachFile( $fileName, true );

                //$params['HtmlBody'] = $this->getBodyHtml(true);  // $params must be &$params to make sense
            }
        }

        if( !empty( $params['Attachment'] ) )
        {
            if( !is_array( $params['Attachment'] ) )
                $params['Attachment'] = array( $params['Attachment'] );

            foreach( $params['Attachment'] as $attachFile )
                $this->attachFile( $attachFile, false );
        }

        //OSS_Debug::prr( $this ); die();

        return $this->send();
    } // function SendEmail


    /**
    * Attaches or embeds a file to/into an email. Embedding the images into an HTML letter happens automatically.
    *
    * @param string $filePath the path to the file to attach
    * @param boolean $embed default false if true then the file will be embedded instead of attached
    * @return boolean
    */
    public function attachFile( $filePath, $embed = false )
    {
        if( $filePath == '' || $filePath == array() )
                return true;                                            //[FIXME] I thing here should be false

        if( !@is_readable( $filePath ) )
        {
            $filePath = OSS_String::mb_str_replace( Zend_Controller_Front::getInstance()->getBaseUrl() . '/', '', $filePath );
        }

        if( @is_readable( $filePath ) )
        {
            $pathInfo = pathinfo( $filePath );

            $attachment = $this->createAttachment( @file_get_contents( $filePath ) );
            $attachment->type = $this->getMimeByExtension( $pathInfo['extension'] );
            $attachment->encoding = Zend_Mime::ENCODING_BASE64;

            if( $embed == false)
            {
                $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                $attachment->filename = basename( $filePath );
            }
            else
            {
                $attachment->disposition = Zend_Mime::DISPOSITION_INLINE;
                $attachment->id = 'cid_' . md5_file( $filePath );
                $this->setBodyHtml( OSS_String::mb_str_replace( $filePath, "cid:{$attachment->id}", $this->getBodyHtml( true ) ) );
            }

            return true;
        }

        return false;
    }


    /**
    * Takes a file extension WITHOUT the leading dot and returns with the corresponding MIME type.
    * Please note that a good few file formats have more than one MIME types, depending on the OS or the browser, etc, but this method only returns with the "best", most compatible one.
    * Returns with "application/octet-stream" if did not find a matching MIME type.
    *
    * @param string $extension
    * @return string
    */
    public function getMimeByExtension( $extension )
    {
        $extension = trim( mb_strtolower( $extension ) );

        if( array_key_exists( $extension, $this->_mimeTypes ) )
            return $this->_mimeTypes[$pExtension];
        else
            return 'application/octet-stream';
    }


    /**
    * Takes a string and returns with a more RFC compatible version of it. Takes care of the UTF-8 characters and most importantly the double quotes, which can cause
    * serious issues if appear in from, to, cc, bcc or returnto. Automatically called by send() where necessary.
    *
    * @param string $string
    * @return string
    */
    public function amendString( $string )
    {
        return str_replace( array( '"' ), array( '\"' ), htmlspecialchars_decode( $string ) );
    }

}    // class

