<?php
/**
 * NOVIUS OS - Web OS for digital communication
 *
 * @copyright  2011 Novius
 * @license    GNU Affero General Public License v3 or (at your option) any later version
 *             http://www.gnu.org/licenses/agpl-3.0.html
 * @link http://www.novius-os.org
 */

return array(
    'callback_ip'   => function() {
        return Input::ip();
    },

    /**
     * After login failures on the back-office, forces the user to wait X seconds before re-trying
     */
    'wait_after_admin_login_failures' => array(
        'enabled' => true,
        // Number of attemps before being blocked.
        'attempts' => 10,
        // Time to wait (seconds)
        'time_to_wait' => 300,
    ),
);
