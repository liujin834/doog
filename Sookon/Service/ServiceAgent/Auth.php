<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2014/11/4
 * Time: 12:15
 */

namespace Sookon\Service\ServiceAgent;

use Sookon\Helpers\Auth as AuthHelper;
use Sookon\Member\Cookie;

class Auth extends AuthHelper{

    public function getCookieCtl()
    {
        return new Cookie();
    }

} 