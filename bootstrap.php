<?php
/**
 * NOVIUS OS - Web OS for digital communication
 *
 * @copyright  2011 Novius
 * @license    GNU Affero General Public License v3 or (at your option) any later version
 *             http://www.gnu.org/licenses/agpl-3.0.html
 * @link http://www.novius-os.org
 */

if (NOS_ENTRY_POINT != 'admin') {
    return ;
}

// On login success
\Event::register('admin.loginSuccess', function () {
    $user = Session::user();
    \Novius\Loginhistory\Model_Login::add('login', array(
        'driver'    => 'nos',
        'method'    => 'form',
        'state'     => 'success',
        'login'     => $user->user_email,
        'user_id'   => $user->user_id,
    ));
});

// On login fail
\Event::register('admin.loginFail', function () {
    \Novius\Loginhistory\Model_Login::add('login', array(
        'driver'    => 'nos',
        'method'    => 'form',
        'state'     => 'fail',
        'login'     => \Input::post('email'),
    ));
});

// On autologin success
\Event::register('admin.loginSuccessWithCookie', function () {
    $user = Session::user();
    \Novius\Loginhistory\Model_Login::add('login', array(
        'driver'    => 'nos',
        'method'    => 'cookie',
        'state'     => 'success',
        'login'     => $user->user_email,
        'user_id'   => $user->user_id,
    ));
});

// On autologin fail
\Event::register('admin.loginFailWithCookie', function () {
    \Novius\Loginhistory\Model_Login::add('login', array(
        'driver'    => 'nos',
        'method'    => 'cookie',
        'state'     => 'fail',
        'user_id'   => Cookie::get('logged_user_id'),
    ));
});

\Event::register_function('admin.beforeLogin', function ($params) {
    $controller = \Arr::get($params, 'controllerInstance');
    $wait_failures_config = \Arr::get(\Config::load('novius_loginhistory::config', true), 'wait_after_admin_login_failures', []);
    $wait_failures_enabled = \Arr::get($wait_failures_config, 'enabled', true);
    $callback_is_whitelisted = \Arr::get($wait_failures_config, 'is_whitelisted');

    if ($wait_failures_enabled && is_callable($callback_is_whitelisted) && !$callback_is_whitelisted()) {
        $time_from = \Date::forge()
            ->modify('-'.\Arr::get($wait_failures_config, 'time_to_wait', 300).' seconds')
            ->format('mysql');

        $failures = \Novius\Loginhistory\Model_Login::query(array(
            'where' => array(
                array('logi_created_at', '>=', $time_from),
                'logi_driver' => 'nos',
                'logi_state'  => 'fail',
                'logi_action' => 'login',
                'logi_ip'     => \NC::remoteIp(),
            ),
        ))->count();

        if ($failures >= \Arr::get($wait_failures_config, 'attempts', 10)) {
            $error = __('You failed to login too many times. Please wait before trying again.');
            if (\Input::is_ajax()) {
                $controller->sendResponse(array('error' => $error));
            } else {
                \Asset::add_path('static/novius-os/admin/novius-os/');
                \Asset::css('login.css', array(), 'css');
                $response = \Response::forge();
                $controller->after($response);
                $controller->template->set(array(
                    'body' => \View::forge('admin/login', array(
                        'error' => $error,
                    ), false),
                ), false, false);
                $response->body($controller->template->render());
                $response->set_status(403);
                $response->send(true);
            }
            exit;
        }
    }
});
