<?php
/**
 * NOVIUS OS - Web OS for digital communication
 *
 * @copyright  2011 Novius
 * @license    GNU Affero General Public License v3 or (at your option) any later version
 *             http://www.gnu.org/licenses/agpl-3.0.html
 * @link http://www.novius-os.org
 */

namespace Novius\Loginhistory;

use Fuel\Core\DB;

class Task_Cleaning extends \Task
{
    protected static $title = 'Login history cleaning';

    /**
     * Clean log in login history older than a delay set in config
     * @return mixed
     */
    public function run()
    {
        $cleaning_task_delay = \Arr::get(\Config::load('novius_loginhistory::config', true), 'cleaning_task_delay', '-1 year');

        d('Cleaning lines of login history older than '.$cleaning_task_delay);
        $limit = \Date::forge()->modify($cleaning_task_delay);

        $count = DB::delete(Model_Login::table())
            ->where('logi_created_at', '<', $limit)
            ->execute();

        return $this->success($count.' line(s) deleted from login history');
    }
}
