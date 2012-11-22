<?php
/**
 * Bigace - a PHP and MySQL based Web CMS.
 *
 * LICENSE
 *
 * This source file is subject to the new GNU General Public License
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://www.bigace.de/license.html
 *
 * Bigace is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Keleo (http://www.keleo.de)
 * @license    http://www.bigace.de/license.html     GNU Public License
 * @version    $Id$
 */

/**
 * Translation file for the Installer: Turkish
 *
 * @author     ostin
 * @category   Bigace
 * @copyright  Copyright (c) 2009-2010 Bigace Community
 * @license    http://www.bigace.de/license.html     GNU Public License
 */

return array(
    'browser-title'         => 'BIGACE Web CMS Installation',
    'thanks'                => 'Bigace içerik yönetimini seçtiginiz için tesekkürler!',

    // Navigation
    'menu_title'            => 'Sistem Yükleme',
    'menu_step_checkup'     => 'Sistem Kontrolü',
    'menu_step_core'        => 'Yükleme',
    'menu_step_community'   => 'Web Sayfasi olustur',
    'menu_step_finish'      => 'Yükleme tamamlandi',

    // Welcome Screen
    'install_begin'         => 'Yüklemeyi başlat',
    'introduction'          => 'Tanitim',

    // Form Tooltip
    'form_tip_close'        => 'Kapat',
    'form_tip_hide'         => 'Bu mesajı tekrar gösterme',

    // LANGUAGES - chooser and names for languages
    'language_choose'       => 'Dil Seçimi',
    'language_text'         => 'Yükleme sirasinda kullanilacak dili seçin.',
    'language_button'       => 'Dili Degistir',

    'failure'               => 'Hatalar Olustu',
    'new'                   => 'Yeni',
    'old'                   => 'Eski',
    'successfull'           => 'Basarili',
    'next'                  => 'Ileri',
    'state_no_db'           => 'Veri tabani yüklenemez!',
    'state_not_all_db'      => 'Veritabani yüklemesi tamamlanamadi!',
    'state_installed'       => 'Sistem çekirdek bilesenleri basariyla yüklendi!',
    'help_title'            => 'Yardim',
    'help_text'             => 'Her adimda gerekli yardimi görebilmek için, mouse nizi her giris satirinin arkasinda bulunan yardim simgesinin üzerine getirin. Kisa bir yardim mesaji görüntülenecektir.<br>Örnek için takib eden simgenin üzerine gelin:',
    'help_demo'             => 'Yardim mesajlarini görüntülemek için dogru yol :)!',
    'db_install'            => 'Içerik yönetim sistemini yükle',
    'cid_install'           => 'Web sayfasi ayarlari',
    'install_finish'        => 'Yükleme tamamlandi',

    // Translation for the System installation Dialog
    'db_value_title'        => 'Veritabani baglantisi',
    'ext_value_title'       => 'Sistem yapilandirmasi',
    'db_type'               => 'Veritabani türü',
    'db_host'               => 'Sunucu/Ana makine',
    'db_database'           => 'Veri tabani',
    'db_user'               => 'Kullanici',
    'db_password'           => 'Sifre',
    'db_prefix'             => 'Tablo Baslangici',
    'mod_rewrite'           => 'Apache MOD-Rewrite',
    'mod_rewrite_yes'       => 'Modul active / Usage possible (.htaccess)',
    'mod_rewrite_no'        => 'Not possible / Do not know',
    'base_dir'              => 'Ana Dizin',

    // Translation for the System Installation Help Images
    'base_dir_help'         => 'Yüklemenin yapilacagi anadizinin adini girin. Eger sunucun ana dizinine yükleme yaptiysaniz bos birakin örn (http://www.sitenizinadi.com/). <br><b>Otomatik hesaplanan deger dogru olmalidir!</b><br>Dosya yolu slash (/) ile baslayamaz ancak slash (/) ile Bitmelidir. Örnegin &quot;http://www.sitenizinadi.com/cms/&quot;, belirlenen <b>cms/</b> dogru olmak zorunda.',
    'mod_rewrite_help'      => '<b>Bu ayar basir URL leri hazirlar!</b><br/>Lütfen dogru yapilandirmalari seçtiginizden emin olun. Eger yanlis ayarlamalar yaparsaniz , sayfaniz gezilebilir olmaz. Emin degilseniz lütfen bos birakin!',
    'db_password'           => 'Sifre',
    'def_language'          => 'Varsayilan Dil',
    'def_language_help'     => 'Içerik Yönetim sisteminiz için varsayilan dili seçin.',
    'db_type_help'          => 'Kullanacaginiz veritabani türünü seçin.<br> Yükleme listede yeralan tüm veritabani sistemlerini destekler, çekirdek bilesen sistemi<b>Suan i,çin sadece MySQL veritabanini destekler</b> .<br>Eger MySQL disinda bir veri tabani kullanmaya karar verirseniz HIÇBIR SORUMLULUK BIZE AIT DEGILDIR!',
    'db_host_help'          => 'Veritabani uygulamanizin yüklendigi anamkine bilgisini girin ( <b>localhost</b> yazmayi deneyin genelde oradadir :)!).',
    'db_database_help'      => 'Veritabani adini yazin (PhpMyadminde sol tarafta görünen isim).',
    'db_user_help'          => 'veritabani üzerinde yazma yetkisine sahip kullanici adini girin.',
    'db_prefix_help'        => 'Veritabani tablo önekini belirleyin. Benzersiz bir isim kullanmaya çalisinki herzaman kolayca ayirt edilebilsin. Eger bunun ne anlama geldigini bilmiyorsaniz lütfen varsayilan degeri kullanin.',
    'db_password_help'      => 'Yukariya yazdiginiz kullanici için sifre girin.',
    'htaccess_security'     => 'Apache .htaccess Özelligi',
    'htaccess_security_yes' => 'Mevcut dosyanin üzerine yazmayi etkinlestirin (.htaccess)',
    'htaccess_security_no'  => 'Mümkün degil / Ne oldugunu bilmiyorum',

    // Translation for community installation: first dialog
    'error_enter_domain'    => 'Web sayfanizin kurulmasi için geçerli bir URL girin',
    'error_enter_adminuser' => 'Yönetici hesabi için en az 4 karakterden olusn bir isim girin',
    'error_enter_adminpass' => 'Lütfen yönetici için en az 4 karakterden olusan(8 karakter ve üzeri tavsiye edilir) bir sifre olusturun.',
    'cid_domain'            => 'Domain',
    'cid_domain_help'       => 'Yeni web sayfanizza tanimlanacak olan domain i girin. Otomatik belirlenen seçenek genelde dogrudur .<br><b>NOT: Sunucu yolu veya slash le biten birseyler eklemeyin!</b>',
    'sitename'              => 'Site adi',
    'sitename_help'         => 'Site basligini girin. Sablonlarda kullanilabilir ve yönetim paneli kullanilarak çok kolay bir sekilde degistirilebilir.',
    'webmastermail'         => 'E-posta Adresi',
    'webmastermail_help'    => 'Yöetici hesabui için e-posta adresi girin.',
    'bigace_admin'          => 'Kullanici adi',
    'bigace_password'       => 'Sifre',
    'bigace_check'          => 'Sifre (tekrar)',
    'bigace_admin_help'     => 'Yöenetici hesabi içi kullanici adi girin. Bu yöetici hesabi tüm yetkilere sahip olacaktir.',
    'bigace_password_help'  => 'Yönetici hesabi için sifre belirleyin.',
    'bigace_check_help'     => 'Lütfen sifrenizi dogrulayin eger sifreniz dogrulanamazsa tekrar bu ekrana döneceksiniz.',
    'create_files'          => 'Dosya Sistemi olusturuluyor',
    'save_cconfig'          => 'Sayfa yapilandirmasini kaydet',
    'added_consumer'        => 'Eklenen site',
    'added_consumer'        => 'Mevcut site eklendi',
    'community_exists'      => 'Eklenen domain için bir yükleme zaten var lütfen baska bir domain girin.',
    'check_reload'          => 'Önkontrolü tekrar yap',
    'check_up'              => 'Önkonrol',
    'check_yes'             => 'Evet',
    'check_no'              => 'Hayir',
    'check_on'              => 'Açik',
    'check_off'             => 'Kapali',
    'check_status'          => 'Durum',
    'check_setting'         => 'Yapilandirma',
    'check_recommended'     => 'Tavsiye edilen',
    'check_install_help'    => 'Eger bayraklardan biri veya birkaçi kirmiziysa Apache ve PHP yapilandirmanizi düzeltmeniz veya gerekli ayarömalalari yapmaniz gerekir. Yapilmamasi durumunda yükleme tamamlanayabilir veya yanlis/eksik yükleme yapilabilir bu durumda sorumluluk bana ait degildir.',
    'check_settings_title'  => 'Tavsiye edilen yapilandirmalar',
    'check_settings_help'   => 'BIGACE in saglikli çalismasi için takip eden ayarlamalar yapilmak zorundadir. <br><br>TIçerik yönetim sistemi yapilandirmalarin bazilarinin eksik veya hatali olmasi halindede çalisacaktir. Ancak tavsiyemiz yüklemeye geçmeden önce mevcut problermlerin giderilmesi yönünde olacaktir.',
    'check_files_title'     => 'Dosya ve dizin yetkileri',
    'check_files_help'      => 'BIGACE in dogru çalismasi gösterilen doya ve dizinlerde bazi yazma yetkilerine gereksinim duyar &amp; . Eger kirmizi isaret görüyorsaniz yüklemeye geçmeden önce bu dosya veya dizinlerin yazma izinlerini düzeltmeniz gerekecektir.<br><br>'.
                               'Please read the wiki article: <a href="http://wiki.bigace.de/bigace:administration:filepermissions" target="_blank">File permissions</a> for help.',
    'config_admin'          => 'Yönetici hesabi',

    'community_install_bad' 	=> 'Kurulum sirasinda sorun olustu.',
    'community_install_infos'   => 'Sistem mesajlarini gösterir...',
    'required_settings_title'   => 'Zorumlu yapilandirmalar',
    'community_install_good'    => '
        <p>Tebrikler yükleme süreci tamamlandi!</p>
        <p>BIGACE le ilgili yardima veya destege ihtiyaç duydugunda unutmaki burda <a href="http://forum.bigace.de" target="_blank">yardim bulabilirsdin</a> Eger istersen :).
        <p>Yükleme dizini hala sunucuda bulunuyor güvenliginiz açisindan tamamen silinmesini tavsiye ediyoruz.</p>
        <p>Simdi <a href="../../">Yeni kurulan web sitene göz atip</a> kullanmaya baslayabilirsin. Yönetim paneline girmeden önce sisteme giris yaptiginizdan emin olmalisiniz.</p>
        <br />
        <p>Bol sans!</p>
        <br /><br />
        <p><a href="../../">Yeni web sayfanizi ziyaret edin</a></p>',

    'error_db_connect'      => 'Veritaani sunucusuna baglanilamadi',
    'error_db_select'       => 'Veritabani seçilemedi',
    'error_db_create'       => 'Veritabani olusturulamadi.',
    'error_read_dir'        => 'Dizin okunamadi',
    'error_created_dir'     => 'Dizin olusturulamadi',
    'error_removed_dir'     => 'Dizin silinemedi',
    'error_copied_file'     => 'Dosya kopyalanamadi',
    'error_remove_file'     => 'Dosya silinemedi',
    'error_close_file'      => 'Dosya kapatilamadi',
    'error_open_file'       => 'Hata: Dosya açilamadi',
    'error_db_statement'    => 'Veritabanainda hata',
    'error_open_cconfig'    => 'Site yapilandirma dosyasi açilamadi',
    'error_double_cconfig'  => 'Hata: Site zaten avar!',
);