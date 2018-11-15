<?php

if (defined('ROOT_PATH')) {
    $error = false;
    // ค่าติดตั้งฐานข้อมูล
    $db_config = include ROOT_PATH.'settings/database.php';
    $db_config = $db_config['mysql'];
    try {
        $options = array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );
        $dbdriver = empty($db_config['dbdriver']) ? 'mysql' : $db_config['dbdriver'];
        $hostname = empty($db_config['hostname']) ? 'localhost' : $db_config['hostname'];
        $conn = new \PDO($dbdriver.':host='.$hostname.';dbname='.$db_config['dbname'], $db_config['username'], $db_config['password'], $options);
    } catch (\PDOException $e) {
        $error = true;
        echo '<h2>ความผิดพลาดในการเชื่อมต่อกับฐานข้อมูล</h2>';
        echo '<p class=warning>ไม่สามารถเชื่อมต่อกับฐานข้อมูลของคุณได้ในขณะนี้</p>';
        echo '<p>อาจเป็นไปได้ว่า</p>';
        echo '<ol>';
        echo '<li>เซิร์ฟเวอร์ของฐานข้อมูลของคุณไม่สามารถใช้งานได้ในขณะนี้</li>';
        echo '<li>ค่ากำหนดของฐานข้อมูลไม่ถูกต้อง (ตรวจสอบไฟล์ settings/database.php)</li>';
        echo '<li>ไม่พบฐานข้อมูลที่ต้องการติดตั้ง</li>';
        echo '<li class="incorrect">'.$e->getMessage().'</li>';
        echo '</ol>';
        echo '<p>หากคุณไม่สามารถดำเนินการแก้ไขข้อผิดพลาดด้วยตัวของคุณเองได้ ให้ติดต่อผู้ดูแลระบบเพื่อขอข้อมูลที่ถูกต้อง หรือ ลองติดตั้งใหม่</p>';
        echo '<p><a href="index.php?step=1" class="button large pink">กลับไปลองใหม่</a></p>';
    }
    if (!$error) {
        // เชื่อมต่อฐานข้อมูลสำเร็จ
        $content[] = '<li class="correct">เชื่อมต่อฐานข้อมูลสำเร็จ</li>';
        try {
            // ตาราง user
            $table = $db_config['prefix'].'_user';
            if (!fieldExists($conn, $table, 'social')) {
                $conn->query("ALTER TABLE `$table` CHANGE `fb` `social` TINYINT(1) NOT NULL DEFAULT '0'");
            }
            if (!fieldExists($conn, $table, 'token')) {
                $conn->query("ALTER TABLE `$table` ADD `token` VARCHAR(50) NULL AFTER `password`");
            }
            $conn->query("ALTER TABLE `$table` CHANGE `password` `password` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
            $content[] = '<li class="correct">ปรับปรุงตาราง `'.$table.'` สำเร็จ</li>';
            // บันทึก settings/config.php
            $config['version'] = $new_config['version'];
            $f = save($config, ROOT_PATH.'settings/config.php');
            $content[] = '<li class="'.($f ? 'correct' : 'incorrect').'">บันทึก <b>config.php</b> ...</li>';
        } catch (\PDOException $e) {
            $content[] = '<li class="incorrect">'.$e->getMessage().'</li>';
        }
        if (!$error) {
            echo '<h2>ปรับรุ่นเรียบร้อย</h2>';
            echo '<p>การปรับรุ่นได้ดำเนินการเสร็จเรียบร้อยแล้ว หากคุณต้องการความช่วยเหลือในการใช้งาน คุณสามารถ ติดต่อสอบถามได้ที่ <a href="https://www.kotchasan.com" target="_blank">https://www.kotchasan.com</a></p>';
            echo '<ul>'.implode('', $content).'</ul>';
            echo '<p class=warning>กรุณาลบไดเร็คทอรี่ <em>install/</em> ออกจาก Server ของคุณ</p>';
            echo '<p>คุณควรปรับ chmod ให้ไดเร็คทอรี่ <em>datas/</em> และ <em>settings/</em> (และไดเร็คทอรี่อื่นๆที่คุณได้ปรับ chmod ไว้ก่อนการปรับรุ่น) ให้เป็น 644 ก่อนดำเนินการต่อ (ถ้าคุณได้ทำการปรับ chmod ไว้ด้วยตัวเอง)</p>';
            echo '<p><a href="../index.php" class="button large admin">เข้าระบบ</a></p>';
        } else {
            echo '<h2>ปรับรุ่นไม่สำเร็จ</h2>';
            echo '<p>การปรับรุ่นยังไม่สมบูรณ์ ลองตรวจสอบข้อผิดพลาดที่เกิดขึ้นและแก้ไขดู หากคุณต้องการความช่วยเหลือการติดตั้ง คุณสามารถ ติดต่อสอบถามได้ที่ <a href="https://www.kotchasan.com" target="_blank">https://www.kotchasan.com</a></p>';
            echo '<ul>'.implode('', $content).'</ul>';
            echo '<p><a href="." class="button large admin">ลองใหม่</a></p>';
        }
    }
}
/**
 * ตรวจสอบฟิลด์.
 *
 * @param resource $conn
 * @param string   $table_name
 * @param string   $field
 *
 * @return bool
 */
function fieldExists($conn, $table_name, $field)
{
    $query = $conn->query("SHOW COLUMNS FROM `$table_name` LIKE '$field'");
    $result = $query->fetchAll(\PDO::FETCH_ASSOC);

    return empty($result) ? false : true;
}

/**
 * @param $config
 * @param $file
 */
function save($config, $file)
{
    $f = @fopen($file, 'wb');
    if ($f !== false) {
        if (!preg_match('/^.*\/([^\/]+)\.php?/', $file, $match)) {
            $match[1] = 'config';
        }
        fwrite($f, '<'."?php\n/* $match[1].php */\nreturn ".var_export((array) $config, true).';');
        fclose($f);

        return true;
    } else {
        return false;
    }
}
