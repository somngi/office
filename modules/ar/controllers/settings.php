<?php
/**
 * @filesource modules/ar/controllers/settings.php
 *
 * @see http://www.kotchasan.com/
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Ar\Settings;

use Gcms\Login;
use Kotchasan\Config;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=ar-settings.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * ตั้งค่าโมดูล.
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // ข้อความ title bar
        $this->title = Language::get('AR settings');
        // เลือกเมนู
        $this->menu = 'settings';
        // สามารถตั้งค่าระบบได้
        if (Login::checkPermission(Login::isMember(), 'can_config')) {
            // แสดงผล
            $section = Html::create('section');
            // breadcrumbs
            $breadcrumbs = $section->add('div', array(
                'class' => 'breadcrumbs',
            ));
            $ul = $breadcrumbs->add('ul');
            $ul->appendChild('<li><span class="icon-settings">{LNG_Settings}</span></li>');
            $ul->appendChild('<li><span>{LNG_Account Receivable}</span></li>');
            $ul->appendChild('<li><span>{LNG_Settings}</span></li>');
            $section->add('header', array(
                'innerHTML' => '<h2 class="icon-office">'.$this->title.'</h2>',
            ));
            // โหลด config
            $config = Config::load(ROOT_PATH.'settings/config.php');
            // แสดงฟอร์ม
            $section->appendChild(createClass('Ar\Settings\View')->render($config));

            return $section->render();
        }
        // 404
        return \Index\Error\Controller::execute($this);
    }
}
